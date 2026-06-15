<?php

namespace Tests\Feature\Schema;

use App\Models\CashCollection;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\CustomerNotification;
use App\Models\DeliveryOption;
use App\Models\Favorite;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentMethod;
use App\Models\PayoutRequest;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\Rider;
use App\Models\RiderNotification;
use App\Models\Store;
use App\Models\StoreCategory;
use App\Models\StoreGalleryImage;
use App\Models\StoreNotification;
use App\Models\SubscriptionOrder;
use App\Models\SubscriptionOrderItem;
use App\Models\TimeSlot;
use App\Models\WalletTransaction;
use App\Models\Zone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LegacyModelRelationshipTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_catalog_relationships_are_available(): void
    {
        $zone = Zone::create([
            'title' => 'North',
            'coordinates' => 'POLYGON((0 0, 0 1, 1 1, 1 0, 0 0))',
        ]);
        $store = Store::factory()->create(['zone_id' => $zone->id]);
        $category = StoreCategory::create(['store_id' => $store->id, 'title' => 'Milk']);
        $product = Product::create([
            'store_id' => $store->id,
            'store_category_id' => $category->id,
            'title' => 'Whole Milk',
        ]);

        ProductVariant::create(['store_id' => $store->id, 'product_id' => $product->id, 'title' => '1 L']);
        ProductImage::create(['store_id' => $store->id, 'product_id' => $product->id, 'image_path' => 'milk.jpg']);
        StoreGalleryImage::create(['store_id' => $store->id, 'image_path' => 'store.jpg']);
        DeliveryOption::create(['store_id' => $store->id, 'title' => 'Tomorrow']);
        TimeSlot::create(['store_id' => $store->id, 'starts_at' => '08:00:00', 'ends_at' => '10:00:00']);
        Coupon::create(['store_id' => $store->id, 'title' => 'Welcome', 'code' => 'WELCOME']);

        $this->assertTrue($store->zone->is($zone));
        $this->assertTrue($store->storeCategories->first()->is($category));
        $this->assertTrue($store->products->first()->is($product));
        $this->assertTrue($product->store->is($store));
        $this->assertTrue($product->storeCategory->is($category));
        $this->assertCount(1, $product->variants);
        $this->assertCount(1, $product->images);
        $this->assertCount(1, $store->galleryImages);
        $this->assertCount(1, $store->deliveryOptions);
        $this->assertCount(1, $store->timeSlots);
        $this->assertCount(1, $store->coupons);
    }

    public function test_customer_order_finance_and_notification_relationships_are_available(): void
    {
        $customer = Customer::factory()->create();
        $store = Store::factory()->create();
        $rider = Rider::factory()->for($store)->create();
        $paymentMethod = PaymentMethod::create(['title' => 'Cash']);
        $coupon = Coupon::create(['store_id' => $store->id, 'title' => 'Save', 'code' => 'SAVE']);

        $order = Order::create([
            'store_id' => $store->id,
            'customer_id' => $customer->id,
            'payment_method_id' => $paymentMethod->id,
            'coupon_id' => $coupon->id,
            'rider_id' => $rider->id,
            'address' => '123 Test Street',
            'customer_name' => $customer->name,
            'customer_mobile' => '5551234567',
            'status' => 'Pending',
        ]);
        $subscription = SubscriptionOrder::create([
            'store_id' => $store->id,
            'customer_id' => $customer->id,
            'payment_method_id' => $paymentMethod->id,
            'rider_id' => $rider->id,
            'address' => '123 Test Street',
            'customer_name' => $customer->name,
            'customer_mobile' => '5551234567',
            'status' => 'Pending',
        ]);

        OrderItem::create(['order_id' => $order->id, 'product_title' => 'Milk']);
        SubscriptionOrderItem::create(['subscription_order_id' => $subscription->id, 'product_title' => 'Milk']);
        CustomerAddress::create(['customer_id' => $customer->id, 'address' => '123 Test Street', 'type' => 'home']);
        Favorite::create(['customer_id' => $customer->id, 'store_id' => $store->id]);
        WalletTransaction::create(['customer_id' => $customer->id, 'message' => 'Wallet credit', 'type' => 'credit', 'amount' => 10, 'transacted_at' => now()]);
        PayoutRequest::create(['store_id' => $store->id, 'amount' => 10, 'status' => 'Pending', 'request_type' => 'UPI', 'requested_at' => now()]);
        CashCollection::create(['store_id' => $store->id, 'amount' => 10, 'message' => 'Cash collected', 'collected_at' => now()]);
        CustomerNotification::create(['customer_id' => $customer->id, 'notified_at' => now(), 'title' => 'Order', 'description' => 'Placed']);
        StoreNotification::create(['store_id' => $store->id, 'notified_at' => now(), 'title' => 'Order', 'description' => 'Placed']);
        RiderNotification::create(['rider_id' => $rider->id, 'notified_at' => now(), 'message' => 'Assigned']);

        $this->assertTrue($order->customer->is($customer));
        $this->assertTrue($order->store->is($store));
        $this->assertTrue($order->rider->is($rider));
        $this->assertTrue($order->paymentMethod->is($paymentMethod));
        $this->assertTrue($order->coupon->is($coupon));
        $this->assertCount(1, $order->items);
        $this->assertCount(1, $subscription->items);
        $this->assertCount(1, $customer->addresses);
        $this->assertCount(1, $customer->favorites);
        $this->assertCount(1, $customer->orders);
        $this->assertCount(1, $customer->subscriptionOrders);
        $this->assertCount(1, $customer->walletTransactions);
        $this->assertCount(1, $customer->customerNotifications);
        $this->assertCount(1, $store->payoutRequests);
        $this->assertCount(1, $store->cashCollections);
        $this->assertCount(1, $store->storeNotifications);
        $this->assertCount(1, $rider->riderNotifications);
    }

    public function test_standalone_reference_models_can_be_persisted(): void
    {
        $category = Category::create(['title' => 'Dairy']);

        $this->assertDatabaseHas('categories', ['id' => $category->id, 'title' => 'Dairy']);
    }
}
