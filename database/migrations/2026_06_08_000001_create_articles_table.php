<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('summary');
            $table->longText('content');
            $table->string('image_url')->nullable();
            $table->string('author')->default('SOC Team Contributor');
            $table->date('published_date');
            $table->string('category');
            $table->json('tags')->nullable();
            $table->timestamps();

            $table->index(['category', 'published_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
