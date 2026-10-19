<?php

namespace XLaravel\Likeable\Tests;

use PHPUnit\Framework\Attributes\Test;
use XLaravel\Likeable\Models\Like;
use XLaravel\Likeable\Tests\Fixtures\Page;
use XLaravel\Likeable\Tests\Fixtures\Post;
use XLaravel\Likeable\Tests\Fixtures\User;

class LikeableTest extends TestCase
{
    #[Test]
    public function likers_returns_the_default_liker_model(): void
    {
        $post = Post::create(['title' => 'Test Post']);
        $ali = User::create(['name' => 'Ali']);
        $ali->like($post);
        Page::create(['name' => 'Fan Page'])->like($post);

        $likers = $post->likers()->get();

        $this->assertCount(1, $likers);
        $this->assertTrue($ali->is($likers->first()));
    }

    #[Test]
    public function likers_can_return_another_liker_model(): void
    {
        $post = Post::create(['title' => 'Test Post']);
        User::create(['name' => 'Ali'])->like($post);
        Page::create(['name' => 'Fan Page'])->like($post);

        $this->assertSame(['Fan Page'], $post->likers(Page::class)->pluck('name')->all());
    }

    #[Test]
    public function the_default_liker_model_can_be_configured(): void
    {
        config(['likeable.liker_model' => Page::class]);
        $post = Post::create(['title' => 'Test Post']);
        User::create(['name' => 'Ali'])->like($post);
        Page::create(['name' => 'Fan Page'])->like($post);

        $this->assertSame(['Fan Page'], $post->likers()->pluck('name')->all());
    }

    #[Test]
    public function likers_can_be_counted(): void
    {
        $post = Post::create(['title' => 'Test Post']);
        User::create(['name' => 'Ali'])->like($post);
        User::create(['name' => 'Ayşe'])->like($post);
        Page::create(['name' => 'Fan Page'])->like($post);

        $post = Post::query()->withCount(['likes', 'likers'])->firstOrFail();

        $this->assertSame(3, $post->getAttribute('likes_count'));
        $this->assertSame(2, $post->getAttribute('likers_count'));
    }

    #[Test]
    public function is_liked_by_tells_whether_a_liker_liked_the_model(): void
    {
        $post = Post::create(['title' => 'Test Post']);
        $ali = User::create(['name' => 'Ali']);
        $ayse = User::create(['name' => 'Ayşe']);
        $ali->like($post);

        $this->assertTrue($post->isLikedBy($ali));
        $this->assertFalse($post->isLikedBy($ayse));
        $this->assertFalse($post->isLikedBy(null));
    }

    #[Test]
    public function with_liked_by_adds_the_liked_state_in_one_query(): void
    {
        $ali = User::create(['name' => 'Ali']);
        $liked = Post::create(['title' => 'Liked']);
        Post::create(['title' => 'Not liked']);
        $ali->like($liked);

        $states = Post::query()->withLikedBy($ali)->orderBy('id')->pluck('is_liked', 'title')->map(fn (mixed $state) => (bool) $state)->all();
        $guest = Post::query()->withLikedBy(null)->pluck('is_liked')->map(fn (mixed $state) => (bool) $state)->all();

        $this->assertSame(['Liked' => true, 'Not liked' => false], $states);
        $this->assertSame([false, false], $guest);
    }

    #[Test]
    public function deleting_the_model_deletes_its_likes(): void
    {
        $user = User::create(['name' => 'Ali']);
        $post = Post::create(['title' => 'Test Post']);
        $other = Post::create(['title' => 'Other Post']);
        $user->like($post);
        $user->like($other);

        $post->forceDelete();

        $this->assertSame([$other->id], Like::query()->pluck('likeable_id')->all());
    }

    #[Test]
    public function soft_deleting_the_model_keeps_its_likes(): void
    {
        $post = Post::create(['title' => 'Test Post']);
        User::create(['name' => 'Ali'])->like($post);

        $post->delete();

        $this->assertSame(1, Like::query()->count());
    }
}
