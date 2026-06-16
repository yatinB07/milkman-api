<?php

namespace Tests\Feature\Schema;

use App\Models\Banner;
use App\Models\CashCollection;
use App\Models\Category;
use App\Models\Coupon;
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
use App\Models\RiderNotification;
use App\Models\Setting;
use App\Models\StoreCategory;
use App\Models\StoreGalleryImage;
use App\Models\StoreNotification;
use App\Models\SubscriptionOrder;
use App\Models\SubscriptionOrderItem;
use App\Models\TimeSlot;
use App\Models\WalletTransaction;
use App\Models\Zone;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LegacyModelFactoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_legacy_domain_models_have_working_factories(): void
    {
        foreach ($this->domainModels() as $class) {
            /** @var Model $model */
            $model = $class::factory()->create();

            $this->assertTrue($model->exists, "Expected [{$class}] factory to create a persisted model.");
        }
    }

    /**
     * @return list<class-string<Model>>
     */
    private function domainModels(): array
    {
        return [
            Banner::class,
            Category::class,
            Zone::class,
            StoreCategory::class,
            Product::class,
            ProductVariant::class,
            ProductImage::class,
            StoreGalleryImage::class,
            DeliveryOption::class,
            TimeSlot::class,
            Coupon::class,
            Faq::class,
            Page::class,
            PaymentMethod::class,
            CustomerAddress::class,
            Favorite::class,
            Order::class,
            OrderItem::class,
            SubscriptionOrder::class,
            SubscriptionOrderItem::class,
            CustomerNotification::class,
            StoreNotification::class,
            RiderNotification::class,
            PayoutRequest::class,
            CashCollection::class,
            WalletTransaction::class,
            Setting::class,
            MilkData::class,
        ];
    }
}
