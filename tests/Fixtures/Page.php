<?php

namespace XLaravel\Likeable\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use XLaravel\Likeable\Concerns\Liker;
use XLaravel\Likeable\Contracts\CanLike;

/**
 * @property int $id
 * @property string $name
 */
class Page extends Model implements CanLike
{
    use Liker;

    public $timestamps = false;

    protected $guarded = [];
}
