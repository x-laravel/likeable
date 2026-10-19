<?php

namespace XLaravel\Likeable\Tests;

use Illuminate\Console\Scheduling\Event as ScheduledEvent;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use XLaravel\Likeable\Models\Like;
use XLaravel\Likeable\Tests\Fixtures\Post;
use XLaravel\Likeable\Tests\Fixtures\User;

class PruningTest extends TestCase
{
    public function test_likes_of_models_that_no_longer_exist_are_pruned(): void
    {
        $user = User::query()->create(['name' => 'Ali']);
        $gone = Post::query()->create(['title' => 'Rock']);
        $kept = Post::query()->create(['title' => 'Jazz']);
        $trashed = Post::query()->create(['title' => 'Blues']);
        $user->like($gone);
        $user->like($kept);
        $user->like($trashed);
        DB::table('posts')->where('id', $gone->id)->delete();
        $trashed->delete();

        $this->prune();

        $this->assertSame(0, $gone->likes()->count());
        $this->assertSame(1, $kept->likes()->count());
        $this->assertSame(1, $trashed->likes()->count());
    }

    public function test_likes_of_likers_that_no_longer_exist_are_pruned(): void
    {
        $gone = User::query()->create(['name' => 'Ali']);
        $kept = User::query()->create(['name' => 'Ayşe']);
        $post = Post::query()->create(['title' => 'Rock']);
        $gone->like($post);
        $kept->like($post);
        DB::table('users')->where('id', $gone->id)->delete();

        $this->prune();

        $this->assertEquals([$kept->id], $post->likes()->pluck('liker_id')->all());
    }

    public function test_unknown_types_are_left_alone(): void
    {
        $user = User::query()->create(['name' => 'Ali']);
        Like::query()->create(['liker_type' => $user->getMorphClass(), 'liker_id' => $user->id, 'likeable_type' => 'missing', 'likeable_id' => 1]);

        $this->prune();

        $this->assertSame(1, Like::query()->count());
    }

    public function test_pruning_is_scheduled_daily_in_chunks_of_a_thousand(): void
    {
        $event = $this->scheduledPruning();

        $this->assertNotNull($event);
        $this->assertSame('0 0 * * *', $event->expression);
        $this->assertStringContainsString('--chunk=1000', (string) $event->command);
    }

    public function test_the_chunk_size_can_be_configured(): void
    {
        config(['likeable.prune.chunk' => 250]);

        $this->assertStringContainsString('--chunk=250', (string) $this->scheduledPruning()?->command);
    }

    public function test_the_schedule_accepts_a_cron_expression(): void
    {
        config(['likeable.prune.schedule' => '30 3 * * *']);

        $this->assertSame('30 3 * * *', $this->scheduledPruning()?->expression);
    }

    public function test_the_schedule_accepts_a_frequency_with_arguments(): void
    {
        config(['likeable.prune.schedule' => 'weeklyOn:1,04:00']);

        $this->assertSame('0 4 * * 1', $this->scheduledPruning()?->expression);
    }

    public function test_an_unknown_frequency_is_refused(): void
    {
        config(['likeable.prune.schedule' => 'sometimes']);

        $this->expectException(InvalidArgumentException::class);

        $this->scheduledPruning();
    }

    public function test_the_schedule_can_be_turned_off(): void
    {
        config(['likeable.prune.enabled' => false]);

        $this->assertNull($this->scheduledPruning());
    }

    protected function prune(): void
    {
        $this->assertSame(0, Artisan::call('model:prune', ['--model' => [Like::class]]));
    }

    protected function scheduledPruning(): ?ScheduledEvent
    {
        $this->app?->forgetInstance(Schedule::class);

        $events = $this->app?->make(Schedule::class)->events() ?? [];

        foreach ($events as $event) {
            if (str_contains((string) $event->command, 'model:prune') && str_contains((string) $event->command, 'Like')) {
                return $event;
            }
        }

        return null;
    }
}
