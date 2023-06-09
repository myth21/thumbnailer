<?php

declare(strict_types = 1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    /**
     * Запустить миграцию.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('transformation', function(Blueprint $table) {
            $table->string('code', 50)->primary();
            $table->string('group_code', 50);
            $table->string('title', 255);
            $table->text('description')->nullable();
        });
    }

    /**
     * Отменить миграцию.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('transformation');
    }

};
