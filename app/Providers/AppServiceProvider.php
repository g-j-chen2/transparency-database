<?php

namespace App\Providers;

use App\Models\Statement;
use GuzzleHttp\Client;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use loophp\psr17\Psr17;
use loophp\psr17\Psr17Interface;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Client\ClientInterface;
use App\Models\PersonalAccessToken;
use Laravel\Sanctum\Sanctum;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->bind(
            ClientInterface::class,
            function(Application $app): ClientInterface {
                //or whatever client you want
                return new Client();
            }
        );
        $this->app->bind(
            Psr17Interface::class,
            function(Application $app): Psr17Interface {
                $psr17Factory = new Psr17Factory();

                //or whatever psr17 you want
                return new Psr17(
                    $psr17Factory,
                    $psr17Factory,
                    $psr17Factory,
                    $psr17Factory,
                    $psr17Factory,
                    $psr17Factory
                );
            }
        );
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        Statement::disableSearchSyncing();

        Sanctum::usePersonalAccessTokenModel(
            PersonalAccessToken::class
        );

        Blade::withoutDoubleEncoding();
        view()->share('ecl_init', true);

        // Analytics Float Format
        Blade::directive('aff', static function (string $expression) {
            return "<?php echo number_format(floatval($expression), 2, '.', '&nbsp;'); ?>";
        });
        // Analytics Int Format
        Blade::directive('aif', static function (string $expression) {
            return "<?php echo number_format(intval($expression), 0, '', '&nbsp;'); ?>";
        });
        RateLimiter::for('reindexing', static function (object $job) {
            return Limit::perMinute(400);
        });
    }
}
