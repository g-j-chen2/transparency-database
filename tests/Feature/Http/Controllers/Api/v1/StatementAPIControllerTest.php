<?php

namespace Tests\Feature\Http\Controllers\Api\v1;

use App\Models\Platform;
use App\Models\Statement;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use JMac\Testing\Traits\AdditionalAssertions;
use Tests\TestCase;



class StatementAPIControllerTest extends TestCase
{
    use AdditionalAssertions, RefreshDatabase, WithFaker;

    private array $required_fields;
    private Statement $statement;


    protected function setUp(): void
    {
        parent::setUp();

        $this->required_fields = [
            'decision_visibility' => 'DECISION_VISIBILITY_CONTENT_DISABLED',
            'decision_ground' => 'DECISION_GROUND_ILLEGAL_CONTENT',
            'category' => 'STATEMENT_CATEGORY_FRAUD',
            'illegal_content_legal_ground' => 'foo',
            'illegal_content_explanation' => 'bar',
            'url' => 'https://www.test.com',
            'puid' => 'TK421',
            'territorial_scope' => ['BE', 'DE', 'FR'],
            'source_type' => 'SOURCE_ARTICLE_16',
            'source' => 'foo',
            'decision_facts' => 'decision and facts',
            'content_type' => ['CONTENT_TYPE_SYNTHETIC_MEDIA'],
            'automated_detection' => 'No',
            'automated_decision' => 'No',
            'application_date' => '2023-05-18-07'
        ];
    }


    /**
     * @test
     */
    public function api_statement_show_works()
    {
        $this->setUpFullySeededDatabase();
        $admin = $this->signInAsAdmin();
        $attributes = $this->required_fields;
        $attributes['user_id'] = $admin->id;
        $attributes['platform_id'] = $admin->platform_id;
        $this->statement = Statement::create($attributes);

        $response = $this->get(route('api.v1.statement.show', [$this->statement]), [
            'Accept' => 'application/json'
        ]);
        $response->assertStatus(Response::HTTP_OK);
        $this->assertEquals($this->statement->decision_ground, $response->json('decision_ground'));
        $this->assertEquals($this->statement->uuid, $response->json('uuid'));
    }

    /**
     * @test
     */
    public function api_statement_show_requires_auth()
    {
        $this->setUpFullySeededDatabase();
        $attributes = $this->required_fields;
        $attributes['user_id'] = User::all()->random()->first()->id;
        $attributes['platform_id'] = Platform::all()->random()->first()->id;
        $this->statement = Statement::create($attributes);
        $response = $this->get(route('api.v1.statement.show', [$this->statement]), [
            'Accept' => 'application/json'
        ]);
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    /**
     * @test
     */
    public function api_statement_store_requires_auth()
    {
        $this->setUpFullySeededDatabase();
        // Not signing in.
        $this->assertCount(10, Statement::all());
        $response = $this->post(route('api.v1.statement.store'), $this->required_fields, [
            'Accept' => 'application/json'
        ]);
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    /**
     * @test
     */
    public function api_statement_store_works()
    {
        $this->setUpFullySeededDatabase();
        $user = $this->signInAsAdmin();

        $this->assertCount(10, Statement::all());
        $fields = array_merge($this->required_fields, [
            'application_date' => '2023-12-20-05',
            'end_date' => '2023-12-25-00',
        ]);
        $response = $this->post(route('api.v1.statement.store'), $fields, [
            'Accept' => 'application/json'
        ]);
        $response->assertStatus(Response::HTTP_CREATED);
        $this->assertCount(11, Statement::all());
        $statement = Statement::where('uuid', $response->json('uuid'))->first();
        $this->assertNotNull($statement);
        $this->assertEquals('API', $statement->method);
        $this->assertEquals($user->id, $statement->user->id);
        $this->assertInstanceOf(Carbon::class, $statement->application_date);
        $this->assertInstanceOf(Carbon::class, $statement->end_date);
    }

    /**
     * @test
     */
    public function api_statement_json_store_works()
    {
        $this->setUpFullySeededDatabase();
        $user = $this->signInAsAdmin();

        $this->assertCount(10, Statement::all());
        $fields = array_merge($this->required_fields, [
            'application_date' => '2023-07-15-06',
            'end_date' => '2023-07-21-23',
        ]);
        $object = new \stdClass();
        foreach ($fields as $key => $value) {
            $object->$key = $value;
        }
        $json = json_encode($object);
        $response = $this->call(
            'POST',
            route('api.v1.statement.store'),
            [],
            [],
            [],
            $headers = [
                'HTTP_CONTENT_LENGTH' => mb_strlen($json, '8bit'),
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json'
            ],
            $json
        );

        $response->assertStatus(Response::HTTP_CREATED);
        $this->assertCount(11, Statement::all());
        $statement = Statement::where('uuid', $response->json('uuid'))->first();
        $this->assertNotNull($statement);
        $this->assertEquals('API', $statement->method);
        $this->assertEquals($user->id, $statement->user->id);

        $this->assertInstanceOf(Carbon::class, $statement->application_date);
        $this->assertInstanceOf(Carbon::class, $statement->end_date);
        $this->assertNull($statement->decision_ground_reference_url);
    }



    /**
     * @test
     */
    public function application_date_must_be_correct_format()
    {
        $this->setUpFullySeededDatabase();
        $user = $this->signInAsAdmin();

        $date = Carbon::createFromDate(2023, 2, 5,);

        $this->assertCount(10, Statement::all());

        $application_date_in = date('Y-m-d-H');
        $end_date_in = date('Y-m-d-H', time() + (7 * 24 * 60 * 60));

        $fields = array_merge($this->required_fields, [
            'application_date' => $application_date_in,
            'end_date' => $end_date_in
        ]);
        $object = new \stdClass();
        foreach ($fields as $key => $value) {
            $object->$key = $value;
        }
        $json = json_encode($object);
        $response = $this->call(
            'POST',
            route('api.v1.statement.store'),
            [],
            [],
            [],
            $headers = [
                'HTTP_CONTENT_LENGTH' => mb_strlen($json, '8bit'),
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json'
            ],
            $json
        );

        $response->assertStatus(Response::HTTP_CREATED);
        $this->assertCount(11, Statement::all());
        $statement = Statement::where('uuid', $response->json('uuid'))->first();
        $this->assertNotNull($statement);
        $this->assertEquals('API', $statement->method);
        $this->assertEquals($user->id, $statement->user->id);

        $this->assertInstanceOf(Carbon::class, $statement->application_date);
        $this->assertInstanceOf(Carbon::class, $statement->end_date);

        $resource = $statement->toArray();
        $this->assertEquals($application_date_in, $resource['application_date']);
    }

    /**
     * @test
     */
    public function request_rejects_bad_dates()
    {
        $this->setUpFullySeededDatabase();
        $user = $this->signInAsAdmin();

        $application_date_in = '2023-4-4-4';

        $fields = array_merge($this->required_fields, [
            'application_date' => $application_date_in,
        ]);

        $response = $this->post(route('api.v1.statement.store'), $fields, [
            'Accept' => 'application/json'
        ]);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertEquals('The application date does not match the format Y-m-d-H.', $response->json('message'));
    }

    /**
     * @test
     */
    public function api_statement_store_rejects_bad_decision_ground_urls()
    {
        $this->setUpFullySeededDatabase();
        $user = $this->signInAsAdmin();

        $this->assertCount(10, Statement::all());
        $fields = array_merge($this->required_fields, [
            'decision_ground_reference_url' => 'notvalidurl',
        ]);
        $response = $this->post(route('api.v1.statement.store'), $fields, [
            'Accept' => 'application/json'
        ]);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertCount(10, Statement::all());
    }

    /**
     * @test
     */
    public function api_statement_store_accepts_google_decision_ground_urls()
    {
        $this->setUpFullySeededDatabase();
        $user = $this->signInAsAdmin();

        $this->assertCount(10, Statement::all());
        $fields = array_merge($this->required_fields, [
            'decision_ground_reference_url' => 'https://www.goodurl.com',
        ]);
        $response = $this->post(route('api.v1.statement.store'), $fields, [
            'Accept' => 'application/json'
        ]);
        $response->assertStatus(Response::HTTP_CREATED);
        $this->assertCount(11, Statement::all());
    }

    /**
     * @test
     */
    public function request_rejects_bad_countries()
    {
        $this->setUpFullySeededDatabase();
        $user = $this->signInAsAdmin();

        $fields = array_merge($this->required_fields, [
            'territorial_scope' => ['XY', 'ZZ'],
        ]);
        $response = $this->post(route('api.v1.statement.store'), $fields, [
            'Accept' => 'application/json'
        ]);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertEquals('The selected territorial scope is invalid.', $response->json('message'));
    }

    /**
     * @test
     */
    public function store_does_not_save_optional_fields_non_related_to_illegal_content()
    {
        $this->setUpFullySeededDatabase();
        $user = $this->signInAsAdmin();

        $extra_fields = [
            'incompatible_content_ground' => 'foobar',
            'incompatible_content_explanation' => 'foobar2',
        ];
        $fields = array_merge($this->required_fields, $extra_fields);
        $response = $this->post(route('api.v1.statement.store'), $fields, [
            'Accept' => 'application/json'
        ]);
        $response->assertStatus(Response::HTTP_CREATED);
        $statement = Statement::where('uuid', $response->json('uuid'))->first();
        $this->assertNull($statement->incompatible_content_ground);
        $this->assertNull($statement->incompatible_content_explanation);
    }


    /**
     * @test
     */
    public function store_does_not_save_optional_fields_non_related_to_incompatible_content()
    {
        $this->setUpFullySeededDatabase();
        $user = $this->signInAsAdmin();

        $extra_fields = [
            'decision_ground' => 'DECISION_GROUND_INCOMPATIBLE_CONTENT',
            'incompatible_content_ground' => 'foobar',
            'incompatible_content_explanation' => 'foobar2',
            'incompatible_content_illegal' => 'No',
        ];
        $fields = array_merge($this->required_fields, $extra_fields);
        $response = $this->post(route('api.v1.statement.store'), $fields, [
            'Accept' => 'application/json'
        ]);
        $response->assertStatus(Response::HTTP_CREATED);
        $statement = Statement::where('uuid', $response->json('uuid'))->first();
        $this->assertNull($statement->illegal_content_legal_ground);
        $this->assertNull($statement->illegal_content_explanation);
    }

    /**
     * @test
     */
    public function store_requires_url_but_does_not_force_url()
    {
        $this->setUpFullySeededDatabase();
        $user = $this->signInAsAdmin();

        $fields = array_merge($this->required_fields, [
            'url' => ''
        ]);

        $response = $this->post(route('api.v1.statement.store'), $fields, [
            'Accept' => 'application/json'
        ]);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $json = $response->json();
        $this->assertNotNull($json['errors']);
        $this->assertNotNull($json['errors']['url']);

        $fields = array_merge($this->required_fields, [
            'url' => 'not empty'
        ]);

        $response = $this->post(route('api.v1.statement.store'), $fields, [
            'Accept' => 'application/json'
        ]);
        $response->assertStatus(Response::HTTP_CREATED);


    }

    /**
     * @test
     */
    public function store_enforces_puid_uniqueness()
    {
        $this->setUpFullySeededDatabase();
        $user = $this->signInAsAdmin();

        $fields = array_merge($this->required_fields, [
            'puid' => ''
        ]);

        $response = $this->post(route('api.v1.statement.store'), $fields, [
            'Accept' => 'application/json'
        ]);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $json = $response->json();
        $this->assertNotNull($json['errors']);
        $this->assertNotNull($json['errors']['puid']);
        $this->assertEquals('The puid field is required.', $json['errors']['puid'][0]);


        // Now let's create one
        $response = $this->post(route('api.v1.statement.store'), $this->required_fields, [
            'Accept' => 'application/json'
        ]);
        $response->assertStatus(Response::HTTP_CREATED);

        $count_before = Statement::all()->count();

        // Check that a SQLITE error was caught and thrown...
        Log::shouldReceive('error')
           ->once()
           ->withArgs(function ($message) {
               return str_contains($message, 'Statement Creation Query Exception Thrown: SQLSTATE[23000]: Integrity constraint violation: 19 UNIQUE constraint failed: statements.platform_id, statements.puid');
           });

        // Let's do it again
        $response = $this->post(route('api.v1.statement.store'), $this->required_fields, [
            'Accept' => 'application/json'
        ]);

        $count_after = Statement::all()->count();

        $this->assertEquals($count_after, $count_before);
    }

    /**
     * @return void
     * @test
     */
    public function on_store_puid_is_shown_but_not_on_show()
    {
        $this->setUpFullySeededDatabase();
        $this->signInAsAdmin();

        $object = new \stdClass();
        foreach ($this->required_fields as $key => $value) {
            $object->$key = $value;
        }
        $json = json_encode($object);
        $response = $this->call(
            'POST',
            route('api.v1.statement.store'),
            [],
            [],
            [],
            $headers = [
                'HTTP_CONTENT_LENGTH' => mb_strlen($json, '8bit'),
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json'
            ],
            $json
        );

        $response->assertStatus(Response::HTTP_CREATED);
        // It shows on the store response
        $this->assertNotNull($response->json('puid'));
        $content = $response->content();
        $this->assertStringContainsString('"puid":', $content);


        // In the show call it should be not there or null
        $response = $this->call('GET', route('api.v1.statement.show', ['statement' => $response->json('uuid')]));
        $this->assertNull($response->json('puid'));
        $content = $response->content();
        $this->assertStringNotContainsString('"puid":', $content);

    }

    /**
     * @test
     */
    public function store_should_save_content_type_other()
    {
        $this->setUpFullySeededDatabase();
        $user = $this->signInAsAdmin();

        $extra_fields = [
            'content_type' => ['CONTENT_TYPE_APP','CONTENT_TYPE_OTHER'],
            'content_type_other' => 'foobar other',
        ];
        $fields = array_merge($this->required_fields, $extra_fields);

        $response = $this->post(route('api.v1.statement.store'), $fields, [
            'Accept' => 'application/json'
        ]);
        $response->assertStatus(Response::HTTP_CREATED);
        $statement = Statement::where('uuid', $response->json('uuid'))->first();
        $this->assertNotNull($statement->content_type);
        $this->assertNotNull($statement->content_type_other);
    }

    /**
     * @test
     */
    public function store_should_not_save_content_type_other()
    {
        $this->setUpFullySeededDatabase();
        $user = $this->signInAsAdmin();

        $extra_fields = [
            'content_type' => ['CONTENT_TYPE_AUDIO','CONTENT_TYPE_APP','CONTENT_TYPE_VIDEO'],
            'content_type_other' => 'foobar other',
        ];
        $fields = array_merge($this->required_fields, $extra_fields);

        $response = $this->post(route('api.v1.statement.store'), $fields, [
            'Accept' => 'application/json'
        ]);
        $response->assertStatus(Response::HTTP_CREATED);
        $statement = Statement::where('uuid', $response->json('uuid'))->first();
        $this->assertNotNull($statement->content_type);
        $this->assertNull($statement->content_type_other);
    }
}

