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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->string('provider')->nullable();
            $table->string('provider_id')->nullable();
            $table->string('provider_token')->nullable();
            $table->rememberToken();
            $table->timestamps();

            // Add unique constraint on provider and provider_id
            $table->unique(['provider', 'provider_id']);

            // Add not null constraint on provider_id if provider is given
            $table->string('provider_id')->nullable($value = true)->change();
            $table->string('provider')->nullable($value = true)->change();

            // Add not null constraint on password if provider is not given
            $table->string('password')->nullable($value = true)->change();
            $table->string('provider')->nullable($value = true)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};