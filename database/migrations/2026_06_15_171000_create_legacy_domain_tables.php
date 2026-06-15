<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admins', function (Blueprint $table): void {
            $table->string('username')->nullable()->unique()->after('id');
        });

        Schema::table('customers', function (Blueprint $table): void {
            $table->dateTime('registered_at')->nullable()->after('password');
        });

        Schema::table('riders', function (Blueprint $table): void {
            $table->text('image_path')->nullable()->after('store_id');
        });

        Schema::create('banners', function (Blueprint $table): void {
            $table->id();
            $table->text('image_path');
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('categories', function (Blueprint $table): void {
            $table->id();
            $table->string('title')->index();
            $table->text('image_path')->nullable();
            $table->text('cover_path')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('zones', function (Blueprint $table): void {
            $table->id();
            $table->string('title')->index();
            $table->boolean('is_active')->default(true)->index();
            $table->longText('coordinates');
            $table->string('alias')->nullable()->index();
            $table->timestamps();
        });

        Schema::table('stores', function (Blueprint $table): void {
            $table->foreignId('zone_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->text('image_path')->nullable()->after('title');
            $table->text('cover_image_path')->nullable()->after('image_path');
            $table->decimal('rating', 4, 2)->default(0)->after('cover_image_path');
            $table->text('slogan')->nullable()->after('rating');
            $table->string('slogan_title')->nullable()->after('slogan');
            $table->string('language_code', 12)->nullable()->after('slogan_title');
            $table->text('category_reference')->nullable()->after('language_code');
            $table->text('short_description')->nullable()->after('landmark');
            $table->longText('content_description')->nullable()->after('short_description');
            $table->unsignedInteger('registration_status')->default(1)->after('is_active');
            $table->unsignedInteger('charge_type')->default(0)->after('registration_status');
            $table->unsignedInteger('unit_kilometers')->nullable()->after('charge_type');
            $table->decimal('unit_price', 12, 2)->nullable()->after('unit_kilometers');
            $table->decimal('additional_price', 12, 2)->nullable()->after('unit_price');
            $table->string('bank_name')->nullable()->after('additional_price');
            $table->string('ifsc_code')->nullable()->after('bank_name');
            $table->string('receipt_name')->nullable()->after('ifsc_code');
            $table->string('account_number')->nullable()->after('receipt_name');
            $table->string('paypal_id')->nullable()->after('account_number');
            $table->string('upi_id')->nullable()->after('paypal_id');
            $table->text('cancel_policy')->nullable()->after('upi_id');
        });

        Schema::create('store_categories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->string('title')->index();
            $table->text('image_path')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('store_category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title')->index();
            $table->text('image_path')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('product_variants', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->decimal('subscribe_price', 12, 2)->default(0);
            $table->decimal('normal_price', 12, 2)->default(0);
            $table->string('title');
            $table->decimal('discount', 12, 2)->default(0);
            $table->boolean('is_out_of_stock')->default(false)->index();
            $table->boolean('is_subscription_required')->default(false)->index();
            $table->timestamps();
        });

        Schema::create('product_images', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->text('image_path');
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('store_gallery_images', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->text('image_path');
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('delivery_options', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->unsignedInteger('delivery_days')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('time_slots', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->time('starts_at');
            $table->time('ends_at');
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('coupons', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->text('image_path')->nullable();
            $table->string('title');
            $table->string('code')->index();
            $table->string('subtitle')->nullable();
            $table->date('expires_at')->nullable()->index();
            $table->decimal('minimum_amount', 12, 2)->default(0);
            $table->decimal('value', 12, 2)->default(0);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('faqs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->text('question');
            $table->text('answer');
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('pages', function (Blueprint $table): void {
            $table->id();
            $table->string('title')->index();
            $table->text('description');
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('payment_methods', function (Blueprint $table): void {
            $table->id();
            $table->string('title')->index();
            $table->text('image_path')->nullable();
            $table->json('attributes')->nullable();
            $table->string('subtitle')->nullable();
            $table->boolean('is_visible')->default(true)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('customer_addresses', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->text('address');
            $table->text('landmark')->nullable();
            $table->text('rider_instruction')->nullable();
            $table->string('type')->index();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->timestamps();
        });

        Schema::create('favorites', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('zone_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->unique(['customer_id', 'store_id']);
        });

        Schema::create('orders', function (Blueprint $table): void {
            $this->createOrderColumns($table, true);
        });

        Schema::create('order_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('quantity')->default(1);
            $table->string('product_title');
            $table->decimal('discount', 12, 2)->default(0);
            $table->text('image_path')->nullable();
            $table->decimal('price', 12, 2)->default(0);
            $table->string('variant_title')->nullable();
            $table->timestamps();
        });

        Schema::create('subscription_orders', function (Blueprint $table): void {
            $this->createOrderColumns($table, false);
        });

        Schema::create('subscription_order_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('subscription_order_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('quantity')->default(1);
            $table->string('product_title');
            $table->decimal('discount', 12, 2)->default(0);
            $table->text('image_path')->nullable();
            $table->decimal('price', 12, 2)->default(0);
            $table->string('variant_title')->nullable();
            $table->date('starts_at')->nullable()->index();
            $table->unsignedInteger('total_deliveries')->default(0);
            $table->longText('total_dates')->nullable();
            $table->longText('completed_dates')->nullable();
            $table->longText('selected_days')->nullable();
            $table->string('time_slot')->nullable();
            $table->timestamps();
        });

        Schema::create('customer_notifications', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->dateTime('notified_at')->index();
            $table->string('title');
            $table->text('description');
            $table->timestamps();
        });

        Schema::create('store_notifications', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->dateTime('notified_at')->index();
            $table->string('title');
            $table->text('description');
            $table->timestamps();
        });

        Schema::create('rider_notifications', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('rider_id')->constrained()->cascadeOnDelete();
            $table->dateTime('notified_at')->index();
            $table->string('title')->nullable();
            $table->text('message');
            $table->timestamps();
        });

        Schema::create('payout_requests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('status')->index();
            $table->text('proof_path')->nullable();
            $table->dateTime('requested_at')->index();
            $table->string('request_type')->index();
            $table->string('account_number')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('account_name')->nullable();
            $table->string('ifsc_code')->nullable();
            $table->string('upi_id')->nullable();
            $table->string('paypal_id')->nullable();
            $table->timestamps();
        });

        Schema::create('cash_collections', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->text('message');
            $table->dateTime('collected_at')->index();
            $table->timestamps();
        });

        Schema::create('wallet_transactions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->text('message');
            $table->string('type')->index();
            $table->decimal('amount', 12, 2);
            $table->dateTime('transacted_at')->index();
            $table->timestamps();
        });

        Schema::create('settings', function (Blueprint $table): void {
            $table->id();
            $table->string('web_name');
            $table->text('web_logo_path')->nullable();
            $table->string('timezone');
            $table->string('currency', 16);
            $table->foreignId('primary_store_id')->nullable()->constrained('stores')->nullOnDelete();
            $table->text('customer_onesignal_key')->nullable();
            $table->text('customer_onesignal_hash')->nullable();
            $table->text('delivery_onesignal_key')->nullable();
            $table->text('delivery_onesignal_hash')->nullable();
            $table->text('store_onesignal_key')->nullable();
            $table->text('store_onesignal_hash')->nullable();
            $table->decimal('signup_credit', 12, 2)->default(0);
            $table->decimal('referral_credit', 12, 2)->default(0);
            $table->boolean('show_dark_mode')->default(false);
            $table->text('google_maps_key')->nullable();
            $table->string('sms_type')->nullable();
            $table->text('message_auth_key')->nullable();
            $table->text('otp_template_id')->nullable();
            $table->text('twilio_account_sid')->nullable();
            $table->text('twilio_auth_token')->nullable();
            $table->string('twilio_number')->nullable();
            $table->text('otp_auth_token')->nullable();
            $table->timestamps();
        });

        Schema::create('milk_data', function (Blueprint $table): void {
            $table->id();
            $table->longText('data');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('milk_data');
        Schema::dropIfExists('settings');
        Schema::dropIfExists('wallet_transactions');
        Schema::dropIfExists('cash_collections');
        Schema::dropIfExists('payout_requests');
        Schema::dropIfExists('rider_notifications');
        Schema::dropIfExists('store_notifications');
        Schema::dropIfExists('customer_notifications');
        Schema::dropIfExists('subscription_order_items');
        Schema::dropIfExists('subscription_orders');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('favorites');
        Schema::dropIfExists('customer_addresses');
        Schema::dropIfExists('payment_methods');
        Schema::dropIfExists('pages');
        Schema::dropIfExists('faqs');
        Schema::dropIfExists('coupons');
        Schema::dropIfExists('time_slots');
        Schema::dropIfExists('delivery_options');
        Schema::dropIfExists('store_gallery_images');
        Schema::dropIfExists('product_images');
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('products');
        Schema::dropIfExists('store_categories');

        Schema::table('stores', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('zone_id');
            $table->dropColumn([
                'image_path',
                'cover_image_path',
                'rating',
                'slogan',
                'slogan_title',
                'language_code',
                'category_reference',
                'short_description',
                'content_description',
                'registration_status',
                'charge_type',
                'unit_kilometers',
                'unit_price',
                'additional_price',
                'bank_name',
                'ifsc_code',
                'receipt_name',
                'account_number',
                'paypal_id',
                'upi_id',
                'cancel_policy',
            ]);
        });

        Schema::dropIfExists('zones');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('banners');

        Schema::table('riders', function (Blueprint $table): void {
            $table->dropColumn('image_path');
        });

        Schema::table('customers', function (Blueprint $table): void {
            $table->dropColumn('registered_at');
        });

        Schema::table('admins', function (Blueprint $table): void {
            $table->dropUnique(['username']);
            $table->dropColumn('username');
        });
    }

    private function createOrderColumns(Blueprint $table, bool $includeOrderType): void
    {
        $table->id();
        $table->foreignId('store_id')->constrained()->cascadeOnDelete();
        $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
        $table->dateTime('ordered_at')->nullable()->index();
        $table->foreignId('payment_method_id')->nullable()->constrained()->nullOnDelete();
        $table->text('address');
        $table->text('landmark')->nullable();
        $table->decimal('delivery_charge', 12, 2)->default(0);
        $table->foreignId('coupon_id')->nullable()->constrained()->nullOnDelete();
        $table->decimal('coupon_amount', 12, 2)->default(0);
        $table->decimal('total', 12, 2)->default(0);
        $table->decimal('subtotal', 12, 2)->default(0);
        $table->string('transaction_id')->nullable()->index();
        $table->text('admin_note')->nullable();
        $table->unsignedInteger('admin_status')->default(0)->index();
        $table->foreignId('rider_id')->nullable()->constrained()->nullOnDelete();
        $table->decimal('wallet_amount', 12, 2)->default(0);
        $table->string('customer_name');
        $table->string('customer_mobile', 32);
        $table->string('status')->index();
        $table->text('rejection_comment')->nullable();
        $table->string('time_slot')->nullable();
        $table->string('order_type')->default($includeOrderType ? 'Delivery' : 'Subscription')->index();
        $table->boolean('is_rated')->default(false)->index();
        $table->dateTime('reviewed_at')->nullable();
        $table->unsignedInteger('total_rating')->default(0);
        $table->text('rating_text')->nullable();
        $table->decimal('commission_percent', 5, 2)->default(0);
        $table->decimal('store_charge', 12, 2)->default(0);
        $table->unsignedInteger('internal_status')->default(0)->index();
        $table->text('signature_path')->nullable();
        $table->timestamps();
    }
};
