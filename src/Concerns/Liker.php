<?php

namespace XLaravel\Likeable\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use LogicException;
use XLaravel\Likeable\Contracts\CanLike;
use XLaravel\Likeable\Contracts\HasLikes;
use XLaravel\Likeable\Models\Like;
use XLaravel\Likeable\Support\LikeableConfig;

/**
 * @mixin Model
 */
trait Liker
{
    public static function bootLiker(): void
    {
        static::deleted(function (Model $model) {
            /** @var Model&self $model */
            $model->forgetGivenLikesWhenGone();
        });
    }

    /**
     * @return MorphMany<Like, $this>
     */
    public function givenLikes(): MorphMany
    {
        return $this->morphMany(LikeableConfig::likeModel(), 'liker');
    }

    /**
     * @template TLikeable of Model
     *
     * @param  class-string<TLikeable>  $likeable
     * @return MorphToMany<TLikeable, $this>
     */
    public function likedItems(string $likeable): MorphToMany
    {
        return $this->morphedByMany($likeable, 'likeable', LikeableConfig::likeTable(), 'liker_id', 'likeable_id')
            ->wherePivot('liker_type', $this->getMorphClass())
            ->withTimestamps();
    }

    public function like(Model&HasLikes $likeable): Like
    {
        $this->ensureCanLike();

        return $this->givenLikes()->createOrFirst([
            'likeable_id' => $likeable->getKey(),
            'likeable_type' => $likeable->getMorphClass(),
        ]);
    }

    public function unlike(Model&HasLikes $likeable): bool
    {
        $like = $this->givenLikes()->whereMorphedTo('likeable', $likeable)->first();

        return (bool) $like?->delete();
    }

    public function hasLiked(Model&HasLikes $likeable): bool
    {
        return $this->givenLikes()->whereMorphedTo('likeable', $likeable)->exists();
    }

    protected function ensureCanLike(): void
    {
        if (! $this instanceof CanLike) {
            throw new LogicException(sprintf('Implement %s on [%s].', CanLike::class, static::class));
        }
    }

    protected function forgetGivenLikesWhenGone(): void
    {
        if ($this->exists) {
            return;
        }

        $this->givenLikes()->delete();
    }
}
