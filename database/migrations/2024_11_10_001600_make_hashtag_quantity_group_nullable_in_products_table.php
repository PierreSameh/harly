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
        Schema::table('products', function (Blueprint $table) {
            $table->integer('quantity')->nullable()->change();
            $table->string('group')->nullable()->change();
            $table->string('hashtag')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->integer('quantity')->nullable(false)->change();
            $table->string('group')->nullable(false)->change();
            $table->string('hashtag')->nullable(false)->change();
        });
    }
};
