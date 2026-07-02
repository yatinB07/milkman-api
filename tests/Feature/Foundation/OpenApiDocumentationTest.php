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
        $this->assertContains('/admin/banners', $paths);
        $this->assertContains('/admin/banners/{banner}', $paths);
        $this->assertContains('/admin/categories', $paths);
        $this->assertContains('/admin/categories/{category}', $paths);
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
    }
}
