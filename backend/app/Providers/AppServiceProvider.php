<?php

namespace HiEvents\Providers;

use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use HiEvents\DomainObjects\EventDomainObject;
use HiEvents\DomainObjects\OrganizerDomainObject;
use HiEvents\Models\Event;
use HiEvents\Models\Organizer;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Stripe\StripeClient;
use Razorpay\Api\Api as RazorpayApi;

class AppServiceProvider extends ServiceProvider
{
    public const MAIL_RATE_LIMIT_PER_SECOND = 'mail-rate-limit-per-second';

    public function register(): void
    {
        $this->bindDoctrineConnection();
        $this->bindRazorpay();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->handleHttpsEnforcing();

        $this->handleQueryLogging();

        $this->disableLazyLoading();

        $this->registerMorphMaps();

        $this->registerJobRateLimiters();
    }

    private function registerJobRateLimiters(): void
    {
        RateLimiter::for(
            name: self::MAIL_RATE_LIMIT_PER_SECOND,
            callback: static fn(ShouldQueue $job) => Limit::perMinute(
                maxAttempts: config('mail.rate_limit_per_second')
            )
        );
    }

    private function bindDoctrineConnection(): void
    {
        if ($this->app->environment('production')) {
            return;
        }

        $this->app->bind(
            AbstractSchemaManager::class,
            function () {
                $config = new Configuration();

                $connectionParams = [
                    'dbname' => config('database.connections.pgsql.database'),
                    'user' => config('database.connections.pgsql.username'),
                    'password' => config('database.connections.pgsql.password'),
                    'host' => config('database.connections.pgsql.host'),
                    'driver' => 'pdo_pgsql',
                ];

                return DriverManager::getConnection($connectionParams, $config)->createSchemaManager();
            }
        );
    }

    private function bindRazorpay(): void
    {
        if (!config('services.razorpay.secret_key')) {
            logger()?->debug('Razorpay key is not set in the configuration file. Payment processing will not work.');
            return;
        }

        $this->app->bind(
            RazorpayApi::class,
            fn() => new RazorpayApi(
                config('services.razorpay.public_key'),
                config('services.razorpay.secret_key')
            )
        );

    }

    /**
     * @return void
     */
    private function handleQueryLogging(): void
    {
        if (env('APP_DEBUG') === true && env('APP_LOG_QUERIES') === true && !app()->isProduction()) {
            DB::listen(
                static function ($query) {
                    File::append(
                        storage_path('/logs/query.log'),
                        $query->sql . ' [' . implode(', ', $query->bindings) . ']' . PHP_EOL
                    );
                }
            );
        }
    }

    private function handleHttpsEnforcing(): void
    {
        if ($this->app->environment('local')) {
            URL::forceScheme('https');
            URL::forceRootUrl(config('app.url'));
        }
    }

    private function registerMorphMaps(): void
    {
        Relation::enforceMorphMap([
            EventDomainObject::class => Event::class,
            OrganizerDomainObject::class => Organizer::class,
        ]);
    }

    private function disableLazyLoading(): void
    {
        Model::preventLazyLoading(!app()->isProduction());
    }
}
