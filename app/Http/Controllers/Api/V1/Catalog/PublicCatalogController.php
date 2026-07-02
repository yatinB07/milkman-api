<?php

namespace App\Http\Controllers\Api\V1\Catalog;

use App\Actions\Catalog\GetPublicStoreDetailAction;
use App\Actions\Catalog\ListPublicCategoriesAction;
use App\Actions\Catalog\ListPublicStoreProductsAction;
use App\Actions\Catalog\ListPublicStoresAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Catalog\ListPublicCatalogRequest;
use App\Http\Resources\Catalog\CategoryResource;
use App\Http\Resources\Catalog\ProductResource;
use App\Http\Resources\Catalog\StoreDetailResource;
use App\Http\Resources\Catalog\StoreSummaryResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PublicCatalogController extends Controller
{
    public function categories(ListPublicCatalogRequest $request, ListPublicCategoriesAction $categories): AnonymousResourceCollection
    {
        return CategoryResource::collection($categories->execute($request->toData()));
    }

    public function stores(ListPublicCatalogRequest $request, ListPublicStoresAction $stores): AnonymousResourceCollection
    {
        return StoreSummaryResource::collection($stores->execute($request->toData()));
    }

    public function store(int $store, GetPublicStoreDetailAction $storeDetail): StoreDetailResource
    {
        return new StoreDetailResource($storeDetail->execute($store));
    }

    public function products(ListPublicCatalogRequest $request, int $store, ListPublicStoreProductsAction $products): AnonymousResourceCollection
    {
        return ProductResource::collection($products->execute($store, $request->toData()));
    }
}
