<?php

namespace XLaravel\Likeable\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use InvalidArgumentException;
use XLaravel\Likeable\Models\Like;

/**
 * @internal
 */
final class LikeableConfig
{
    /**
     * @return class-string<Like>
     */
    public static function likeModel(): string
    {
        $model = Config::get('likeable.like.model') ?? Like::class;

        if (! is_string($model) || ! is_a($model, Like::class, true)) {
            throw new InvalidArgumentException('Set likeable.like.model to a model that extends ' . Like::class . '.');
        }

        return $model;
    }

    /**
     * @return class-string<Model>
     */
    public static function likerModel(): string
    {
        $model = Config::get('likeable.liker_model') ?? Config::get('auth.providers.users.model');

        if (! is_string($model) || ! is_subclass_of($model, Model::class)) {
            throw new InvalidArgumentException('Set likeable.liker_model to an Eloquent model.');
        }

        return $model;
    }

    public static function likeTable(): string
    {
        return self::string('like.table') ?: 'likes';
    }

    public static function connection(): ?string
    {
        return self::string('connection') ?: null;
    }

    protected static function string(string $key): string
    {
        $value = Config::get("likeable.{$key}");

        return is_string($value) ? trim($value) : '';
    }
}
