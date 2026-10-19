<?php

namespace XLaravel\Likeable\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use XLaravel\Likeable\Contracts\CanLike;
use XLaravel\Likeable\Models\Like;
use XLaravel\Likeable\Support\LikeableConfig;

/**
 * @mixin Model
 */
trait Likeable
{
    public static function bootLikeable(): void
    {
        static::deleted(function (Model $model) {
            /** @var Model&self $model */
            $model->forgetLikesWhenGone();
        });
    }

    /**
     * @return MorphMany<Like, $this>
     */
    public function likes(): MorphMany
    {
        return $this->morphMany(LikeableConfig::likeModel(), 'likeable');
    }

    /**
     * @param  class-string<Model>|null  $liker
     * @return MorphToMany<Model, $this>
     */
    public function likers(?string $liker = null): MorphToMany
    {
        $liker ??= LikeableConfig::likerModel();

        return $this->morphToMany($liker, 'likeable', LikeableConfig::likeTable(), 'likeable_id', 'liker_id')
            ->wherePivot('liker_type', (new $liker())->getMorphClass())
            ->withTimestamps();
    }

    public function isLikedBy((Model&CanLike)|null $liker): bool
    {
        if ($liker === null) {
            return false;
        }

        return $this->likes()->whereMorphedTo('liker', $liker)->exists();
    }

    /**
     * @param  Builder<static>  $query
     */
    public function scopeWithLikedBy(Builder $query, (Model&CanLike)|null $liker): void
    {
        $query->withExists(['likes as is_liked' => function (Builder $likes) use ($liker) {
            if ($liker === null) {
                $likes->whereRaw('1 = 0');

                return;
            }

            $likes->whereMorphedTo('liker', $liker);
        }]);
    }

    protected function forgetLikesWhenGone(): void
    {
        if ($this->exists) {
            return;
        }

        $this->likes()->delete();
    }
}
