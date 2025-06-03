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
        Schema::create('medicines', function (Blueprint $table) {
            $table->id();
            $table->text('generic_name');
            $table->text('brand_names')->nullable(); // comma-separated or JSON
            $table->json('uses')->nullable();
            $table->text('dosage')->nullable();
            $table->text('side_effects')->nullable();
            $table->json('precautions')->nullable();
            $table->integer('user_id')->nullable(); //->constrained()->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes(); // optional for recovery
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medicines');
    }
};
