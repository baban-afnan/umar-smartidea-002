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
        Schema::create('sme_datas', function (Illuminate\Database\Schema\Blueprint $table) {
            $table->id();
            $table->string('data_id')->nullable();
            $table->string('network')->nullable();
            $table->string('plan_type')->nullable();
            $table->string('amount')->nullable();
            $table->string('size')->nullable();
            $table->string('validity')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sme_datas');
    }
};
