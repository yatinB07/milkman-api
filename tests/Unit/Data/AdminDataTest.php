<?php

namespace Tests\Unit\Data;

use App\Data\Admin\BannerData;
use App\Data\Admin\CategoryData;
use App\Data\Admin\ListQueryData;
use Tests\TestCase;

class AdminDataTest extends TestCase
{
    public function test_list_query_data_normalizes_empty_search_and_default_page_size(): void
    {
        $query = ListQueryData::fromArray([
            'search' => '',
        ]);

        $this->assertNull($query->search);
        $this->assertSame(15, $query->perPage);
    }

    public function test_list_query_data_preserves_valid_search_and_page_size(): void
    {
        $query = ListQueryData::fromArray([
            'search' => 'milk',
            'per_page' => 25,
            'is_active' => 'false',
            'is_out_of_stock' => 'true',
        ]);

        $this->assertSame('milk', $query->search);
        $this->assertSame(25, $query->perPage);
        $this->assertFalse($query->isActive);
        $this->assertTrue($query->isOutOfStock);
    }

    public function test_banner_data_outputs_only_allowed_banner_attributes(): void
    {
        $data = BannerData::fromArray([
            'title' => 'Home banner',
            'image_path' => 'banners/home.png',
            'is_active' => false,
            'ignored' => 'value',
        ]);

        $this->assertSame([
            'title' => 'Home banner',
            'image_path' => 'banners/home.png',
            'is_active' => false,
        ], $data->toArray());
    }

    public function test_category_data_outputs_only_present_category_attributes(): void
    {
        $data = CategoryData::fromArray([
            'title' => 'Milk',
            'is_active' => true,
            'ignored' => 'value',
        ]);

        $this->assertSame([
            'title' => 'Milk',
            'is_active' => true,
        ], $data->toArray());
    }
}
