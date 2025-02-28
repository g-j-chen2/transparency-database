<?php

namespace Tests\Feature\Services;

use App\Models\DayArchive;
use App\Models\Statement;
use App\Services\DayArchiveService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DayArchiveServiceTest extends TestCase
{

    use RefreshDatabase;

    protected DayArchiveService $day_archive_service;
    private array $required_fields;

    public function setUp(): void
    {
        parent::setUp();
        $this->day_archive_service = app(DayArchiveService::class);
        $this->assertNotNull($this->day_archive_service);

        $this->required_fields = [
            'decision_visibility' => ['DECISION_VISIBILITY_CONTENT_DISABLED','DECISION_VISIBILITY_CONTENT_AGE_RESTRICTED'],
            'decision_ground' => 'DECISION_GROUND_ILLEGAL_CONTENT',
            'category' => 'STATEMENT_CATEGORY_ANIMAL_WELFARE',
            'illegal_content_legal_ground' => 'foo',
            'illegal_content_explanation' => 'bar',
            'puid' => 'TK421',
            'territorial_scope' => ['BE', 'DE', 'FR'],
            'source_type' => 'SOURCE_ARTICLE_16',
            'source_identity' => 'foo',
            'decision_facts' => 'decision and facts',
            'content_type' => ['CONTENT_TYPE_SYNTHETIC_MEDIA'],
            'automated_detection' => 'No',
            'automated_decision' => 'AUTOMATED_DECISION_PARTIALLY',
            'application_date' => '2023-05-18',
            'content_date' => '2023-05-18'
        ];
    }

    /**
     * @test
     */
    public function it_retrieves_global_list()
    {
        DayArchive::create([
            'date' => '2023-10-02',
            'total' => 1,
            'completed_at' => Carbon::now()
        ]);
        DayArchive::create([
            'date' => '2023-10-01',
            'total' => 2,
            'completed_at' => Carbon::now()
        ]);


        $list = $this->day_archive_service->globalList()->get();
        $this->assertCount(2, $list);

        // First one needs the 2
        $first = $list->first();
        $this->assertEquals('2023-10-02', $first->date->format('Y-m-d'));

        // Needs to be in the right order.
        $last = $list->last();
        $this->assertEquals('2023-10-01', $last->date->format('Y-m-d'));

    }

    /**
     * @test
     */
    public function gloabl_list_must_be_completed_day_archive()
    {
        DayArchive::create([
            'date' => '2023-10-02',
            'total' => 1
        ]);
        DayArchive::create([
            'date' => '2023-10-01',
            'total' => 2
        ]);


        $list = $this->day_archive_service->globalList()->get();
        $this->assertCount(0, $list);
    }

    /**
     * @return void
     * @test
     */
    public function it_starts_csvs_closes_makes_zips_and_sha1s_and_cleans_up(): void
    {
        $this->setUpFullySeededDatabase();
        $day_archives = $this->day_archive_service->buildStartingDayArchivesArray(Carbon::createFromDate(2023, 8, 8));
        $this->day_archive_service->startAllCsvFiles($day_archives);
        Storage::assertExists($day_archives[5]['file']);
        Storage::assertExists($day_archives[13]['file']);
        Storage::assertExists($day_archives[5]['filelight']);
        Storage::assertExists($day_archives[13]['filelight']);

        $content = Storage::get($day_archives[4]['file']);
        $headings = $this->day_archive_service->headings();
        $headings_string = implode(',', $headings) . "\n";
        $this->assertEquals($headings_string, $content);

        $content = Storage::get($day_archives[4]['filelight']);
        $headings = $this->day_archive_service->headingsLight();
        $headings_string = implode(',', $headings) . "\n";
        $this->assertEquals($headings_string, $content);

        $this->day_archive_service->closeAllCsvFiles($day_archives);
        $this->day_archive_service->generateZipsSha1sAndUpdate($day_archives);
        Storage::assertExists($day_archives[9]['zipfile']);
        Storage::assertExists($day_archives[11]['zipfile']);
        Storage::assertExists($day_archives[10]['zipfilesha1']);
        Storage::assertExists($day_archives[3]['zipfilesha1']);
        Storage::assertExists($day_archives[9]['zipfilelight']);
        Storage::assertExists($day_archives[11]['zipfilelight']);
        Storage::assertExists($day_archives[10]['zipfilelightsha1']);
        Storage::assertExists($day_archives[3]['zipfilelightsha1']);
        $this->day_archive_service->cleanUpCsvFiles($day_archives);
        Storage::assertMissing($day_archives[7]['file']);
        Storage::assertMissing($day_archives[18]['file']);
        Storage::assertMissing($day_archives[1]['file']);
        Storage::assertMissing($day_archives[3]['file']);
        Storage::assertMissing($day_archives[7]['filelight']);
        Storage::assertMissing($day_archives[18]['filelight']);
        Storage::assertMissing($day_archives[1]['filelight']);
        Storage::assertMissing($day_archives[3]['filelight']);
        $this->day_archive_service->cleanUpZipAndSha1Files($day_archives);
        Storage::assertMissing($day_archives[2]['zipfile']);
        Storage::assertMissing($day_archives[4]['zipfile']);
        Storage::assertMissing($day_archives[6]['zipfilesha1']);
        Storage::assertMissing($day_archives[8]['zipfilesha1']);
        Storage::assertMissing($day_archives[2]['zipfilelight']);
        Storage::assertMissing($day_archives[4]['zipfilelight']);
        Storage::assertMissing($day_archives[6]['zipfilelightsha1']);
        Storage::assertMissing($day_archives[8]['zipfilelightsha1']);
    }

    /**
     * @test
     */
    public function it_retrieves_an_archive_by_date(): void
    {
        DayArchive::create([
            'date' => '2023-10-02',
            'total' => 1
        ]);

        DayArchive::create([
            'date' => '2023-10-02',
            'total' => 5,
            'platform_id' => 5
        ]);

        DayArchive::create([
            'date' => '2023-10-01',
            'total' => 1
        ]);

        $dayarchive = $this->day_archive_service->getDayArchiveByDate(Carbon::createFromFormat('Y-m-d', '2023-10-02'));
        $this->assertNotNull($dayarchive);
        $this->assertEquals('2023-10-02', $dayarchive->date->format('Y-m-d'));
        $this->assertEquals(1, $dayarchive->total);
    }

    /**
     * @test
     * @throws Exception
     */
    public function it_creates_a_day_archive(): void
    {
        Log::shouldReceive('debug')
           ->with('There was no first or last id to base the day archives query from, so we fell back to the slow query');
        $result = $this->day_archive_service->createDayArchive(Carbon::yesterday());
        $this->assertTrue($result);
    }

    /**
     * @test
     * @throws Exception
     */
    public function it_does_not_allow_overwriting(): void
    {
        $day_archive = $this->day_archive_service->createDayArchive(Carbon::createFromDate(2023, 8, 8));
        $this->assertNotNull($day_archive);

        $this->expectExceptionMessage('A day archive for the date:');
        $this->day_archive_service->createDayArchive(Carbon::createFromDate(2023, 8, 8));
    }


    /**
     * @test
     * @throws Exception
     */
    public function it_ensures_the_date_is_in_the_past(): void
    {
        $this->expectExceptionMessage('When creating a day export you must supply a date in the past.');
        $this->day_archive_service->createDayArchive(Carbon::createFromFormat('Y-m-d', '2070-10-02'));
    }

    /**
     * @test
     * @return void
     */
    public function it_gets_the_first_id_from_date(): void
    {
        $this->setUpFullySeededDatabase();
        $admin = $this->signInAsAdmin();
        $fields_one = $this->required_fields;
        $fields_two = $this->required_fields;

        $fields_one['user_id'] = $admin->id;
        $fields_one['platform_id'] = $admin->platform->id;
        $fields_one['created_at'] = '2030-01-01 00:00:10'; // also prove the while loop works

        $fields_two['puid'] = 'TK422';
        $fields_two['user_id'] = $admin->id;
        $fields_two['platform_id'] = $admin->platform->id;
        $fields_two['created_at'] = '2030-01-01 00:00:10';

        $statement_one = Statement::create($fields_one);
        $statement_two = Statement::create($fields_two);

        $this->assertLessThan($statement_two->id, $statement_one->id);

        $first_id = $this->day_archive_service->getFirstIdOfDate(Carbon::createFromDate(2030, 1, 1));

        $this->assertEquals($statement_one->id, $first_id);
    }

    /**
     * @test
     * @return void
     */
    public function it_gets_zero_on_first(): void
    {
        $this->setUpFullySeededDatabase();
        $admin = $this->signInAsAdmin();

        $first_id = $this->day_archive_service->getFirstIdOfDate(Carbon::createFromDate(2030, 1, 1));

        $this->assertEquals(0, $first_id);
    }

    /**
     * @return void
     * @test
     */
    public function it_builds_a_nice_array_for_the_start_of_a_date(): void
    {
        $in = $this->day_archive_service->buildStartOfDateArray(Carbon::now());
        $this->assertNotNull($in);
        $this->assertCount(10, $in);
        $this->assertContains(Carbon::now()->format('Y-m-d 00:00:00'), $in);
        $this->assertContains(Carbon::now()->format('Y-m-d 00:00:09'), $in);
    }

    /**
     * @return void
     * @test
     */
    public function it_builds_a_nice_array_for_the_end_of_a_date(): void
    {
        $in = $this->day_archive_service->buildEndOfDateArray(Carbon::now());
        $this->assertNotNull($in);
        $this->assertCount(10, $in);
        $this->assertContains(Carbon::now()->format('Y-m-d 23:59:59'), $in);
        $this->assertContains(Carbon::now()->format('Y-m-d 23:59:50'), $in);
    }

    /**
     * @return void
     * @test
     */
    public function it_builds_a_starting_array(): void
    {
        $this->setUpFullySeededDatabase();
        $result = $this->day_archive_service->buildStartingDayArchivesArray(Carbon::yesterday());
        $this->assertNotNull($result);
        $this->assertCount(20, $result);
        $this->assertEquals('global', $result[0]['slug']);
        $this->assertCount(20, DayArchive::all());
    }

    /**
     * @test
     * @return void
     */
    public function it_gets_the_last_id_from_date()
    {
        $this->setUpFullySeededDatabase();
        $admin = $this->signInAsAdmin();
        $fields_one = $this->required_fields;
        $fields_two = $this->required_fields;

        $fields_one['user_id'] = $admin->id;
        $fields_one['platform_id'] = $admin->platform->id;
        $fields_one['created_at'] = '2030-01-01 23:59:40'; // also prove the while loop works

        $fields_two['puid'] = 'TK422';
        $fields_two['user_id'] = $admin->id;
        $fields_two['platform_id'] = $admin->platform->id;
        $fields_two['created_at'] = '2030-01-01 23:59:40';

        $statement_one = Statement::create($fields_one);
        $statement_two = Statement::create($fields_two);

        $this->assertLessThan($statement_two->id, $statement_one->id);

        $last_id = $this->day_archive_service->getLastIdOfDate(Carbon::createFromDate(2030, 1, 1));

        $this->assertEquals($statement_two->id, $last_id);
    }

    /**
     * @test
     * @return void
     */
    public function it_gets_zero_on_last()
    {
        $this->setUpFullySeededDatabase();
        $admin = $this->signInAsAdmin();

        $last_id = $this->day_archive_service->getLastIdOfDate(Carbon::createFromDate(2030, 1, 1));

        $this->assertEquals(0, $last_id);
    }
}
