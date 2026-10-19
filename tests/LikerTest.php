<?php

namespace XLaravel\Likeable\Tests;

use LogicException;
use PHPUnit\Framework\Attributes\Test;
use XLaravel\Likeable\Models\Like;
use XLaravel\Likeable\Tests\Fixtures\Guest;
use XLaravel\Likeable\Tests\Fixtures\Post;
use XLaravel\Likeable\Tests\Fixtures\User;

class LikerTest extends TestCase
{
    #[Test]
    public function it_can_like_a_model(): void
    {
        $user = User::create(['name' => 'Ali']);
        $post = Post::create(['title' => 'Test Post']);

        $like = $user->like($post);

        $this->assertTrue($like->likeable()->is($post));
        $this->assertTrue($like->liker()->is($user));
        $this->assertTrue($user->hasLiked($post));
    }

    #[Test]
    public function liking_twice_keeps_one_like(): void
    {
        $user = User::create(['name' => 'Ali']);
        $post = Post::create(['title' => 'Test Post']);

        $first = $user->like($post);
        $second = $user->like($post);

        $this->assertTrue($first->is($second));
        $this->assertSame(1, Like::query()->count());
    }

    #[Test]
    public function it_can_unlike_a_model(): void
    {
        $user = User::create(['name' => 'Ali']);
        $post = Post::create(['title' => 'Test Post']);
        $user->like($post);

        $this->assertTrue($user->unlike($post));
        $this->assertFalse($user->unlike($post));
        $this->assertFalse($user->hasLiked($post));
    }

    #[Test]
    public function liked_items_returns_the_liked_models_of_a_type(): void
    {
        $user = User::create(['name' => 'Ali']);
        $liked = Post::create(['title' => 'Liked']);
        Post::create(['title' => 'Not liked']);
        $user->like($liked);
        $user->like(User::create(['name' => 'Ayşe']));

        $this->assertSame(['Liked'], $user->likedItems(Post::class)->pluck('title')->all());
    }

    #[Test]
    public function a_model_without_can_like_cannot_like(): void
    {
        $guest = Guest::create(['name' => 'Guest']);

        $this->expectException(LogicException::class);

        $guest->like(Post::create(['title' => 'Test Post']));
    }

    #[Test]
    public function deleting_the_liker_deletes_its_likes(): void
    {
        $user = User::create(['name' => 'Ali']);
        $other = User::create(['name' => 'Ayşe']);
        $post = Post::create(['title' => 'Test Post']);
        $user->like($post);
        $other->like($post);

        $user->forceDelete();

        $this->assertSame([$other->id], Like::query()->pluck('liker_id')->all());
    }

    #[Test]
    public function soft_deleting_the_liker_keeps_its_likes(): void
    {
        $user = User::create(['name' => 'Ali']);
        $user->like(Post::create(['title' => 'Test Post']));

        $user->delete();

        $this->assertSame(1, Like::query()->count());
    }

    #[Test]
    public function a_model_can_both_like_and_be_liked(): void
    {
        $fan = User::create(['name' => 'Ali']);
        $star = User::create(['name' => 'Ayşe']);

        $fan->like($star);

        $this->assertSame(1, $fan->givenLikes()->count());
        $this->assertSame(1, $star->likes()->count());
        $this->assertSame(0, $fan->likes()->count());
        $this->assertSame(0, $star->givenLikes()->count());
    }
}
