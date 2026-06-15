<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admins', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->boolean('is_active')->default(true)->index();
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('customers', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable()->unique();
            $table->string('country_code', 8)->nullable();
            $table->string('mobile', 32)->nullable()->index();
            $table->string('password');
            $table->string('referral_code')->nullable()->unique();
            $table->string('parent_referral_code')->nullable()->index();
            $table->decimal('wallet_balance', 12, 2)->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('stores', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('country_code', 8)->nullable();
            $table->string('mobile', 32)->nullable()->index();
            $table->text('full_address')->nullable();
            $table->string('pincode', 32)->nullable();
            $table->string('landmark')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->decimal('store_charge', 12, 2)->default(0);
            $table->decimal('delivery_charge', 12, 2)->default(0);
            $table->decimal('minimum_order_amount', 12, 2)->default(0);
            $table->decimal('commission_percent', 5, 2)->default(0);
            $table->time('opens_at')->nullable();
            $table->time('closes_at')->nullable();
            $table->boolean('is_pickup_enabled')->default(false);
            $table->boolean('is_active')->default(true)->index();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('riders', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('store_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('country_code', 8)->nullable();
            $table->string('mobile', 32)->nullable()->index();
            $table->string('password');
            $table->boolean('is_active')->default(true)->index();
            $table->date('joined_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('riders');
        Schema::dropIfExists('stores');
        Schema::dropIfExists('customers');
        Schema::dropIfExists('admins');
    }
};
