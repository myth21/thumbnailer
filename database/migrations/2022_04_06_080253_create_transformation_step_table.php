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
        Schema::create('transformation_step', function(Blueprint $table) {
            $table->id();
            $table->string('code', 50);
            $table->tinyInteger('sorting')->default(1);
            $table->string('method', 50);
            $table->json('params')->nullable();

            $table->foreign('code')
                ->references('code')
                ->on('transformation')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }

    /**
     * Отменить миграцию.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('transformation_step', function(Blueprint $table) {
            $table->dropForeign('transformation_step_code_foreign');
        });
        Schema::dropIfExists('transformation_step');
    }

};
