<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use XLaravel\Likeable\Support\LikeableConfig;

return new class extends Migration
{
    public function up(): void
    {
        $likes = LikeableConfig::likeTable();

        Schema::connection($this->getConnection())->create($likes, function (Blueprint $table) use ($likes) {
            $table->id();
            $table->morphs('liker');
            $table->morphs('likeable');
            $table->timestamps();

            $table->unique(['liker_type', 'liker_id', 'likeable_type', 'likeable_id'], "{$likes}_liker_likeable_unique");
        });
    }

    public function down(): void
    {
        Schema::connection($this->getConnection())->dropIfExists(LikeableConfig::likeTable());
    }

    public function getConnection(): ?string
    {
        return LikeableConfig::connection();
    }
};
