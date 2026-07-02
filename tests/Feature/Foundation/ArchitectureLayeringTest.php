<?php

namespace Tests\Feature\Foundation;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ArchitectureLayeringTest extends TestCase
{
    public function test_controllers_and_actions_do_not_build_eloquent_queries(): void
    {
        $files = [
            ...File::allFiles(app_path('Http/Controllers')),
            ...File::allFiles(app_path('Actions')),
        ];

        foreach ($files as $file) {
            $contents = File::get($file->getPathname());

            $this->assertStringNotContainsString('::query(', $contents, $file->getRelativePathname());
            $this->assertStringNotContainsString('::where(', $contents, $file->getRelativePathname());
            $this->assertStringNotContainsString('::create(', $contents, $file->getRelativePathname());
        }
    }

    public function test_current_persistence_workflows_have_repositories(): void
    {
        $this->assertFileExists(app_path('Repositories/IdentityRepository.php'));
        $this->assertFileExists(app_path('Repositories/CatalogRepository.php'));
        $this->assertFileExists(app_path('Repositories/BannerRepository.php'));
        $this->assertFileExists(app_path('Repositories/CategoryRepository.php'));
        $this->assertFileExists(app_path('Repositories/StoreCategoryRepository.php'));
        $this->assertFileExists(app_path('Repositories/ProductRepository.php'));
        $this->assertFileExists(app_path('Repositories/ProductVariantRepository.php'));
        $this->assertFileExists(app_path('Repositories/ProductImageRepository.php'));
        $this->assertFileExists(app_path('Repositories/StoreGalleryImageRepository.php'));
        $this->assertFileExists(app_path('Repositories/DeliveryOptionRepository.php'));
        $this->assertFileExists(app_path('Repositories/TimeSlotRepository.php'));
        $this->assertFileExists(app_path('Repositories/CouponRepository.php'));
        $this->assertFileExists(app_path('Repositories/FaqRepository.php'));
        $this->assertFileExists(app_path('Repositories/PageRepository.php'));
    }
}
