<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('note_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('note_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->longText('body');
            $table->json('tags_snapshot')->nullable();
            $table->enum('change_type', ['created', 'updated', 'deleted'])->default('updated');
            $table->integer('version')->default(1);
            $table->timestamps();
            
            $table->index(['note_id', 'version']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('note_histories');
    }
};
