<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('type', 100);
            $table->string('brand', 100);
            $table->string('model', 100);
            $table->string('version', 100);
            $table->year('year_model');
            $table->year('year_build');
            $table->string('optionals', 255)->nullable();
            $table->integer('doors');
            $table->string('board', 8)->unique();
            $table->string('chassi', 255)->unique()->nullable();
            $table->string('transmission', 50);
            $table->integer('km');
            $table->text('description')->nullable();
            $table->boolean('sold')->default(false);
            $table->string('category', 100);
            $table->string('url_car', 255);
            $table->decimal('price', 10, 2);
            $table->string('color', 50);
            $table->string('fuel', 50);
            $table->json('photos');
            $table->timestamp('original_created_at')->nullable();
            $table->timestamp('original_updated_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
