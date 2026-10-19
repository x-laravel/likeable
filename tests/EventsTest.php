<?php

namespace XLaravel\Likeable\Tests;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use XLaravel\Likeable\Events\Liked;
use XLaravel\Likeable\Events\Unliked;
use XLaravel\Likeable\Models\Like;
use XLaravel\Likeable\Tests\Fixtures\Post;
use XLaravel\Likeable\Tests\Fixtures\User;

class EventsTest extends TestCase
{
    #[Test]
    public function a_like_dispatches_liked_once(): void
    {
        $user = User::create(['name' => 'Ali']);
        $post = Post::create(['title' => 'Test Post']);

        Event::fake([Liked::class]);

        $like = $user->like($post);
        $user->like($post);

        Event::assertDispatchedTimes(Liked::class, 1);
        Event::assertDispatched(Liked::class, fn (Liked $event) => $event->like->is($like));
    }

    #[Test]
    public function an_unlike_dispatches_unliked(): void
    {
        $user = User::create(['name' => 'Ali']);
        $post = Post::create(['title' => 'Test Post']);
        $user->like($post);

        Event::fake([Unliked::class]);

        $user->unlike($post);
        $user->unlike($post);

        Event::assertDispatchedTimes(Unliked::class, 1);
    }

    #[Test]
    public function a_rolled_back_like_is_not_announced(): void
    {
        $user = User::create(['name' => 'Ali']);
        $heard = [];
        Event::listen(Liked::class, function (Liked $event) use (&$heard) {
            $heard[] = $event->like->likeable_id;
        });

        $kept = Post::create(['title' => 'Kept']);
        $rolledBack = Post::create(['title' => 'Rolled back']);

        DB::transaction(fn () => $user->like($kept));

        try {
            DB::transaction(function () use ($user, $rolledBack) {
                $user->like($rolledBack);

                throw new \RuntimeException();
            });
        } catch (\RuntimeException) {
        }

        $this->assertEquals([$kept->id], $heard);
    }

    #[Test]
    public function pruning_dispatches_no_unliked(): void
    {
        User::create(['name' => 'Ali'])->like(Post::create(['title' => 'Test Post']));
        DB::table('posts')->delete();

        Event::fake([Unliked::class]);

        Artisan::call('model:prune', ['--model' => [Like::class]]);

        $this->assertSame(0, Like::query()->count());
        Event::assertNotDispatched(Unliked::class);
    }
}
