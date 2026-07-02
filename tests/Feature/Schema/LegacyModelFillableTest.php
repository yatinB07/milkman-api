<?php

namespace Tests\Feature\Schema;

use App\Models\Admin;
use App\Models\Banner;
use App\Models\CashCollection;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\CustomerNotification;
use App\Models\DeliveryOption;
use App\Models\Faq;
use App\Models\Favorite;
use App\Models\MilkData;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Page;
use App\Models\PaymentMethod;
use App\Models\PayoutRequest;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\Rider;
use App\Models\RiderNotification;
use App\Models\Setting;
use App\Models\Store;
use App\Models\StoreCategory;
use App\Models\StoreGalleryImage;
use App\Models\StoreNotification;
use App\Models\SubscriptionOrder;
use App\Models\SubscriptionOrderItem;
use App\Models\TimeSlot;
use App\Models\WalletTransaction;
use App\Models\Zone;
use Illuminate\Database\Eloquent\Model;
use Tests\TestCase;

class LegacyModelFillableTest extends TestCase
{
    public function test_legacy_covered_models_use_explicit_fillable_fields(): void
    {
        foreach ($this->expectedFillableFields() as $class => $expectedFields) {
            /** @var Model $model */
            $model = new $class;

            $this->assertSame(
                $expectedFields,
                $model->getFillable(),
                "Expected [{$class}] to declare an explicit fillable list."
            );
            $this->assertNotSame([], $model->getGuarded(), "Model [{$class}] must not use open guarded mass assignment.");
        }
    }

    /**
     * @return array<class-string<Model>, list<string>>
     */
    private function expectedFillableFields(): array
    {
        $orderFields = [
            'store_id',
            'customer_id',
            'ordered_at',
            'payment_method_id',
            'address',
            'landmark',
            'delivery_charge',
            'coupon_id',
            'coupon_amount',
            'total',
            'subtotal',
            'transaction_id',
            'admin_note',
            'admin_status',
            'rider_id',
            'wallet_amount',
            'customer_name',
            'customer_mobile',
            'status',
            'rejection_comment',
            'time_slot',
            'order_type',
            'is_rated',
            'reviewed_at',
            'total_rating',
            'rating_text',
            'commission_percent',
            'store_charge',
            'internal_status',
            'signature_path',
        ];

        return [
            Admin::class => ['name', 'username', 'email', 'password', 'is_active', 'email_verified_at'],
            Customer::class => ['name', 'profile_image_path', 'email', 'country_code', 'mobile', 'password', 'registered_at', 'referral_code', 'parent_referral_code', 'wallet_balance', 'is_active', 'email_verified_at'],
            Store::class => ['title', 'zone_id', 'image_path', 'cover_image_path', 'rating', 'slogan', 'slogan_title', 'language_code', 'category_reference', 'email', 'password', 'country_code', 'mobile', 'full_address', 'pincode', 'landmark', 'short_description', 'content_description', 'latitude', 'longitude', 'store_charge', 'delivery_charge', 'minimum_order_amount', 'commission_percent', 'opens_at', 'closes_at', 'is_pickup_enabled', 'is_active', 'registration_status', 'charge_type', 'unit_kilometers', 'unit_price', 'additional_price', 'bank_name', 'ifsc_code', 'receipt_name', 'account_number', 'paypal_id', 'upi_id', 'cancel_policy'],
            Rider::class => ['store_id', 'image_path', 'name', 'email', 'country_code', 'mobile', 'password', 'is_active', 'joined_at'],
            Banner::class => ['title', 'image_path', 'is_active'],
            Category::class => ['title', 'image_path', 'cover_path', 'is_active'],
            Zone::class => ['title', 'is_active', 'coordinates', 'alias'],
            StoreCategory::class => ['store_id', 'title', 'image_path', 'is_active'],
            Product::class => ['store_id', 'store_category_id', 'title', 'image_path', 'description', 'is_active'],
            ProductVariant::class => ['store_id', 'product_id', 'subscribe_price', 'normal_price', 'title', 'discount', 'is_out_of_stock', 'is_subscription_required'],
            ProductImage::class => ['store_id', 'product_id', 'image_path', 'is_active'],
            StoreGalleryImage::class => ['store_id', 'image_path', 'is_active'],
            DeliveryOption::class => ['store_id', 'title', 'delivery_days', 'is_active'],
            TimeSlot::class => ['store_id', 'starts_at', 'ends_at', 'is_active'],
            Coupon::class => ['store_id', 'image_path', 'title', 'code', 'subtitle', 'expires_at', 'minimum_amount', 'value', 'description', 'is_active'],
            Faq::class => ['store_id', 'question', 'answer', 'is_active'],
            Page::class => ['title', 'description', 'is_active'],
            PaymentMethod::class => ['title', 'image_path', 'attributes', 'subtitle', 'is_visible', 'is_active'],
            CustomerAddress::class => ['customer_id', 'address', 'landmark', 'rider_instruction', 'type', 'latitude', 'longitude'],
            Favorite::class => ['customer_id', 'store_id', 'zone_id'],
            Order::class => $orderFields,
            OrderItem::class => ['order_id', 'quantity', 'product_title', 'discount', 'image_path', 'price', 'variant_title'],
            SubscriptionOrder::class => $orderFields,
            SubscriptionOrderItem::class => ['subscription_order_id', 'quantity', 'product_title', 'discount', 'image_path', 'price', 'variant_title', 'starts_at', 'total_deliveries', 'total_dates', 'completed_dates', 'selected_days', 'time_slot'],
            CustomerNotification::class => ['customer_id', 'notified_at', 'title', 'description'],
            StoreNotification::class => ['store_id', 'notified_at', 'title', 'description'],
            RiderNotification::class => ['rider_id', 'notified_at', 'title', 'message'],
            PayoutRequest::class => ['store_id', 'amount', 'status', 'proof_path', 'requested_at', 'request_type', 'account_number', 'bank_name', 'account_name', 'ifsc_code', 'upi_id', 'paypal_id'],
            CashCollection::class => ['store_id', 'amount', 'message', 'collected_at'],
            WalletTransaction::class => ['customer_id', 'message', 'type', 'amount', 'transacted_at'],
            Setting::class => ['web_name', 'web_logo_path', 'timezone', 'currency', 'primary_store_id', 'customer_onesignal_key', 'customer_onesignal_hash', 'delivery_onesignal_key', 'delivery_onesignal_hash', 'store_onesignal_key', 'store_onesignal_hash', 'signup_credit', 'referral_credit', 'show_dark_mode', 'google_maps_key', 'sms_type', 'message_auth_key', 'otp_template_id', 'twilio_account_sid', 'twilio_auth_token', 'twilio_number', 'otp_auth_token'],
            MilkData::class => ['data'],
        ];
    }
}
