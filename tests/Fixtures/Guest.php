<?php

namespace XLaravel\Likeable\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use XLaravel\Likeable\Concerns\Liker;

/**
 * @property int $id
 * @property string $name
 */
class Guest extends Model
{
    use Liker;

    public $timestamps = false;

    protected $table = 'users';

    protected $guarded = [];
}
