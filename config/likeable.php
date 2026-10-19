<?php

use XLaravel\Likeable\Models\Like;

return [

    /*
    |--------------------------------------------------------------------------
    | Likers
    |--------------------------------------------------------------------------
    |
    | liker_model  Model that likers() returns when it is called without one.
    |              Null uses the model of the "users" authentication provider.
    |
    */

    'liker_model' => null,

    /*
    |--------------------------------------------------------------------------
    | Storage
    |--------------------------------------------------------------------------
    |
    | connection  Database connection of the like table. Null uses the
    |             default connection.
    | table       Name of the like table.
    | model       Eloquent model of the table. It must extend the package's
    |             own model: XLaravel\Likeable\Models\Like.
    |
    */

    'connection' => null,

    'like' => [
        'table' => 'likes',
        'model' => Like::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Pruning
    |--------------------------------------------------------------------------
    |
    | Likes whose liked model or liker no longer exists are left behind by
    | deletes that skip model events, such as bulk queries and database
    | cascades. The package schedules model:prune to remove them.
    |
    | enabled   Schedule the pruning.
    | schedule  When the pruning runs, written in one of two ways:
    |           A cron expression:
    |             "30 3 * * *"        every day at 03:30
    |
    |           The name of a Laravel schedule frequency method, with its
    |           arguments after a colon, separated by commas:
    |             "daily"             every day at 00:00
    |             "dailyAt:03:30"     every day at 03:30
    |             "twiceDaily:1,13"   every day at 01:00 and 13:00
    |             "weeklyOn:1,04:00"  every Monday at 04:00
    | chunk     Rows deleted by each query.
    |
    */

    'prune' => [
        'enabled' => true,
        'schedule' => 'daily',
        'chunk' => 1000,
    ],

];
