<?php

namespace Tests\Unit\Actions;

use App\Actions\Admin\Banners\ListBannersAction;
use App\Data\Admin\ListQueryData;
use App\Repositories\BannerRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class AdminCatalogActionTest extends TestCase
{
    public function test_list_banners_action_passes_query_data_to_repository(): void
    {
        $paginator = new LengthAwarePaginator([], 0, 5);

        $banners = $this->createMock(BannerRepository::class);
        $banners
            ->expects($this->once())
            ->method('paginate')
            ->with('milk', 5)
            ->willReturn($paginator);

        $action = new ListBannersAction($banners);

        $this->assertSame($paginator, $action->execute(new ListQueryData('milk', 5)));
    }
}
