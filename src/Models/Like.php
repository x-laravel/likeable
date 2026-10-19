<?php

namespace XLaravel\Likeable\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use XLaravel\Likeable\Events\Liked;
use XLaravel\Likeable\Events\Unliked;
use XLaravel\Likeable\Support\LikeableConfig;
use XLaravel\Likeable\Support\MissingMorphTargets;

/**
 * @property int $id
 * @property string $liker_type
 * @property int|string $liker_id
 * @property string $likeable_type
 * @property int|string $likeable_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Like extends Model
{
    use MassPrunable;

    protected $fillable = [
        'liker_id',
        'liker_type',
        'likeable_id',
        'likeable_type',
    ];

    protected static function booted(): void
    {
        static::created(function (Like $like) {
            Event::dispatch(new Liked($like));
        });

        static::deleted(function (Like $like) {
            Event::dispatch(new Unliked($like));
        });
    }

    /**
     * @return Builder<static>
     */
    public function prunable(): Builder
    {
        return static::query()
            ->where(fn (Builder $query) => MissingMorphTargets::where($query, 'likeable'))
            ->orWhere(fn (Builder $query) => MissingMorphTargets::where($query, 'liker'));
    }

    public function getTable(): string
    {
        return LikeableConfig::likeTable();
    }

    public function getConnectionName(): ?string
    {
        return LikeableConfig::connection() ?? parent::getConnectionName();
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function liker(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function likeable(): MorphTo
    {
        return $this->morphTo();
    }
}
