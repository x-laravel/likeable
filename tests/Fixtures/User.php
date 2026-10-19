<?php

namespace XLaravel\Likeable\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use XLaravel\Likeable\Concerns\Likeable;
use XLaravel\Likeable\Concerns\Liker;
use XLaravel\Likeable\Contracts\CanLike;
use XLaravel\Likeable\Contracts\HasLikes;

/**
 * @property int $id
 * @property string $name
 */
class User extends Model implements CanLike, HasLikes
{
    use Likeable;
    use Liker;
    use SoftDeletes;

    public $timestamps = false;

    protected $guarded = [];
}
