<?php

namespace XLaravel\Likeable\Events;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use XLaravel\Likeable\Models\Like;

final readonly class Liked implements ShouldDispatchAfterCommit
{
    public function __construct(
        public Like $like,
    ) {
    }
}
