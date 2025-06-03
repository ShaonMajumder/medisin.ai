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
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->string('generic_name', 255)->index();
            $table->text('brand_names')->nullable();
            $table->text('uses')->nullable();
            $table->text('dosage')->nullable();
            $table->text('side_effects')->nullable();
            $table->text('precautions')->nullable();
            $table->foreignId('shopkeeper_id')->constrained('users')->onDelete('cascade');
            $table->unsignedInteger('quantity')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stocks');
    }
};
