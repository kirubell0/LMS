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
        Schema::create('letters', function (Blueprint $table) {
            $table->id();
            $table->string('ref_no');
            $table->string('date')->nullable();
            $table->string('to')->nullable();
            $table->string('Subject');
            $table->text('body')->nullable();
            $table->string('cc')->nullable();
            $table->string('Approved_by')->nullable();
            $table->string('Approved_position')->nullable();
            $table->boolean('is_completed')->default(false);
            $table->foreignId('list_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('letters');
    }
};
