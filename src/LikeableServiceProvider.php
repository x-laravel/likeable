<?php

namespace XLaravel\Likeable;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;
use XLaravel\Likeable\Support\LikeableConfig;

class LikeableServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/likeable.php', 'likeable');
    }

    public function boot(): void
    {
        $this->callAfterResolving(Schedule::class, $this->schedulePruning(...));

        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__ . '/../config/likeable.php' => $this->app->configPath('likeable.php'),
        ], 'likeable-config');

        $this->publishesMigrations([
            __DIR__ . '/../database/migrations' => $this->app->databasePath('migrations'),
        ], 'likeable-migrations');
    }

    private function schedulePruning(Schedule $schedule): void
    {
        if (! Config::boolean('likeable.prune.enabled', true)) {
            return;
        }

        $frequency = Config::string('likeable.prune.schedule', 'daily');

        $event = $schedule->command('model:prune', [
            '--model' => [LikeableConfig::likeModel()],
            '--chunk' => Config::integer('likeable.prune.chunk', 1000),
        ]);

        if (str_contains($frequency, ' ')) {
            $event->cron($frequency);

            return;
        }

        [$method, $arguments] = array_pad(explode(':', $frequency, 2), 2, '');

        if ($method === '' || ! method_exists($event, $method)) {
            throw new InvalidArgumentException("[{$frequency}] is not a schedule frequency.");
        }

        $event->{$method}(...($arguments === '' ? [] : explode(',', $arguments)));
    }
}
