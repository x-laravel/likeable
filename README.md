# Likeable for Laravel

<p align="center">
<a href="https://github.com/X-Laravel/likeable/actions/workflows/tests.yml"><img src="https://github.com/X-Laravel/likeable/actions/workflows/tests.yml/badge.svg" alt="Tests"></a>
<a href="https://packagist.org/packages/x-laravel/likeable" rel="nofollow"><img src="https://img.shields.io/packagist/v/x-laravel/likeable" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/x-laravel/likeable" rel="nofollow"><img src="https://img.shields.io/packagist/php-v/x-laravel/likeable" alt="PHP Version"></a>
<a href="https://packagist.org/packages/x-laravel/likeable" rel="nofollow"><img src="https://img.shields.io/packagist/dt/x-laravel/likeable" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/x-laravel/likeable" rel="nofollow"><img src="https://img.shields.io/packagist/l/x-laravel/likeable" alt="License"></a>
</p>

Polymorphic likes for Eloquent. Any model can be liked, and any model can like, so users, pages or
teams can all leave likes. A liker likes a model once; liking it again changes nothing.

## Requirements

| Package version | PHP | Laravel |
|-----------------|-----|---------|
| 1.x | 8.4+ | 13 |

## Installation

```bash
composer require x-laravel/likeable
php artisan vendor:publish --tag=likeable-migrations
php artisan migrate
```

Publish the config to change the defaults:

```bash
php artisan vendor:publish --tag=likeable-config
```

## Preparing the Models

Implement `CanLike` and use `Liker` on the model that likes. Implement `HasLikes` and use `Likeable`
on every model that can be liked; only those models are accepted:

```php
use XLaravel\Likeable\Concerns\Likeable;
use XLaravel\Likeable\Concerns\Liker;
use XLaravel\Likeable\Contracts\CanLike;
use XLaravel\Likeable\Contracts\HasLikes;

class User extends Authenticatable implements CanLike
{
    use Liker;
}

class Post extends Model implements HasLikes
{
    use Likeable;
}
```

The `liker_id` and `likeable_id` columns follow Laravel's morph key type, so set
`Schema::defaultMorphKeyType('uuid')` or `'ulid'` before migrating if your models use them. If the
liked models mix key types, such as integer and ULID keys, publish the migration and make
`likeable_id` a string column.

## Configuration

| Key | Default | Meaning |
|-----|---------|---------|
| `liker_model` | `null` | Model `likers()` returns when called without one; null uses the `users` auth provider's model |
| `connection` | `null` | Connection of the like table |
| `like.table` | `'likes'` | Name of the like table |
| `like.model` | `Like::class` | Like model; must extend the package's |
| `prune.enabled` | `true` | Schedule the pruning of left-behind likes |
| `prune.schedule` | `'daily'` | A cron expression, or a frequency method with its arguments: `'dailyAt:03:30'`, `'twiceDaily:1,13'`, `'weeklyOn:1,04:00'` |
| `prune.chunk` | `1000` | Rows deleted by each pruning query |

## Liking

```php
$user->like($post);      // returns the Like, the existing one when already liked
$user->unlike($post);    // true when a like was removed
$user->hasLiked($post);
```

## Listing

```php
$post->likes;                         // every like, from every kind of liker
$post->likers()->paginate(20);        // likers of the default liker model
$post->likers(Page::class)->get();    // likers of another model
$post->isLikedBy($user);              // false for null, so a guest can be passed

$user->givenLikes;                    // every like the user left
$user->likedItems(Post::class)->get();
```

Show counts and the liked state of a whole list in one query:

```php
Post::query()
    ->withCount('likers')
    ->withLikedBy($request->user())   // adds is_liked; false for every row when null
    ->with(['likers' => fn ($query) => $query->take(3)])
    ->paginate();
```

## Deleting and Pruning

When a liked model or a liker is deleted, its likes are deleted with it. Soft-deleted models keep
their likes until they are force deleted.

Deletes that skip model events, such as bulk queries, database cascades or raw SQL, leave likes
behind. The package schedules `model:prune`, daily by default, to remove likes whose liked model or
liker no longer exists. Run it by hand with:

```bash
php artisan model:prune --model="XLaravel\Likeable\Models\Like"
```

Pruning skips types that no longer resolve to a model and models on another connection.

## Reacting to Likes

`XLaravel\Likeable\Events\Liked` is dispatched for a new like and `Unliked` when a like is removed.
Both carry the `Like` and are dispatched after the surrounding transaction commits. Liking a model
that is already liked dispatches nothing.

Likes removed together with their model or liker, or by pruning, dispatch nothing.

## Testing

```bash
composer install
composer test
composer analyse
```

## License

Open source software licensed under the [MIT license](LICENSE).
