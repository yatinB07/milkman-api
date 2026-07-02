<?php

namespace Tests\Feature\Foundation;

use Dedoc\Scramble\Http\Middleware\RestrictedDocsAccess;
use Tests\TestCase;

class OpenApiDocumentationTest extends TestCase
{
    public function test_openapi_document_includes_current_catalog_and_admin_routes(): void
    {
        $this->withoutMiddleware(RestrictedDocsAccess::class);

        $response = $this->getJson('/docs/api.json');

        $response->assertOk();

        $paths = array_keys($response->json('paths'));

        $this->assertContains('/public/categories', $paths);
        $this->assertContains('/public/stores', $paths);
        $this->assertContains('/public/stores/{store}/products', $paths);
        $this->assertContains('/customer/addresses', $paths);
        $this->assertContains('/customer/addresses/{address}', $paths);
        $this->assertContains('/customer/favorites', $paths);
        $this->assertContains('/customer/favorites/toggle', $paths);
        $this->assertContains('/customer/notifications', $paths);
        $this->assertContains('/customer/wallet-transactions', $paths);
        $this->assertContains('/customer/wallet/top-ups', $paths);
        $this->assertContains('/admin/banners', $paths);
        $this->assertContains('/admin/banners/{banner}', $paths);
        $this->assertContains('/admin/categories', $paths);
        $this->assertContains('/admin/categories/{category}', $paths);
        $this->assertContains('/admin/customers', $paths);
        $this->assertContains('/admin/customers/{customer}', $paths);
        $this->assertContains('/admin/customer-addresses', $paths);
        $this->assertContains('/admin/customer-addresses/{customerAddress}', $paths);
        $this->assertContains('/admin/customer-notifications', $paths);
        $this->assertContains('/admin/customer-notifications/{customerNotification}', $paths);
        $this->assertContains('/admin/favorites', $paths);
        $this->assertContains('/admin/favorites/{favorite}', $paths);
        $this->assertContains('/admin/wallet-transactions', $paths);
        $this->assertContains('/admin/wallet-transactions/{walletTransaction}', $paths);
        $this->assertContains('/admin/payout-requests', $paths);
        $this->assertContains('/admin/payout-requests/{payoutRequest}', $paths);
        $this->assertContains('/admin/cash-collections', $paths);
        $this->assertContains('/admin/cash-collections/{cashCollection}', $paths);
        $this->assertContains('/admin/orders', $paths);
        $this->assertContains('/admin/orders/{order}', $paths);
        $this->assertContains('/admin/order-items', $paths);
        $this->assertContains('/admin/order-items/{orderItem}', $paths);
        $this->assertContains('/admin/subscription-orders', $paths);
        $this->assertContains('/admin/subscription-orders/{subscriptionOrder}', $paths);
        $this->assertContains('/admin/subscription-order-items', $paths);
        $this->assertContains('/admin/subscription-order-items/{subscriptionOrderItem}', $paths);
        $this->assertContains('/admin/stores', $paths);
        $this->assertContains('/admin/stores/{store}', $paths);
        $this->assertContains('/admin/riders', $paths);
        $this->assertContains('/admin/riders/{rider}', $paths);
        $this->assertContains('/admin/store-categories', $paths);
        $this->assertContains('/admin/store-categories/{storeCategory}', $paths);
        $this->assertContains('/admin/products', $paths);
        $this->assertContains('/admin/products/{product}', $paths);
        $this->assertContains('/admin/product-variants', $paths);
        $this->assertContains('/admin/product-variants/{productVariant}', $paths);
        $this->assertContains('/admin/product-images', $paths);
        $this->assertContains('/admin/product-images/{productImage}', $paths);
        $this->assertContains('/admin/store-gallery-images', $paths);
        $this->assertContains('/admin/store-gallery-images/{storeGalleryImage}', $paths);
        $this->assertContains('/admin/store-notifications', $paths);
        $this->assertContains('/admin/store-notifications/{storeNotification}', $paths);
        $this->assertContains('/admin/rider-notifications', $paths);
        $this->assertContains('/admin/rider-notifications/{riderNotification}', $paths);
        $this->assertContains('/admin/delivery-options', $paths);
        $this->assertContains('/admin/delivery-options/{deliveryOption}', $paths);
        $this->assertContains('/admin/time-slots', $paths);
        $this->assertContains('/admin/time-slots/{timeSlot}', $paths);
        $this->assertContains('/admin/coupons', $paths);
        $this->assertContains('/admin/coupons/{coupon}', $paths);
        $this->assertContains('/admin/faqs', $paths);
        $this->assertContains('/admin/faqs/{faq}', $paths);
        $this->assertContains('/admin/pages', $paths);
        $this->assertContains('/admin/pages/{page}', $paths);
        $this->assertContains('/admin/payment-methods', $paths);
        $this->assertContains('/admin/payment-methods/{paymentMethod}', $paths);
        $this->assertContains('/admin/zones', $paths);
        $this->assertContains('/admin/zones/{zone}', $paths);
        $this->assertContains('/admin/settings', $paths);
        $this->assertContains('/admin/settings/{setting}', $paths);
        $this->assertContains('/admin/milk-data', $paths);
        $this->assertContains('/admin/milk-data/{milkData}', $paths);
    }
}
