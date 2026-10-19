<?php

namespace XLaravel\Likeable\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use XLaravel\Likeable\Concerns\Likeable;
use XLaravel\Likeable\Contracts\HasLikes;

/**
 * @property int $id
 * @property string $title
 */
class Post extends Model implements HasLikes
{
    use Likeable;
    use SoftDeletes;

    public $timestamps = false;

    protected $guarded = [];
}
