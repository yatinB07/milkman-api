<?php

namespace App\Repositories;

use App\Exceptions\Catalog\CategoryNotFoundException;
use App\Models\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CategoryRepository
{
    /** @return LengthAwarePaginator<int, Category> */
    public function paginate(?string $search = null, int $perPage = 15): LengthAwarePaginator
    {
        return Category::query()
            ->when($search, function ($query, string $search): void {
                $query->where('title', 'like', "%{$search}%");
            })
            ->orderBy('title')
            ->paginate($perPage);
    }

    public function create(array $attributes): Category
    {
        return Category::query()->create($attributes);
    }

    public function find(int $id): Category
    {
        $category = Category::query()->find($id);

        if (! $category) {
            throw new CategoryNotFoundException;
        }

        return $category;
    }

    public function update(Category $category, array $attributes): Category
    {
        $category->update($attributes);

        return $category->refresh();
    }

    public function delete(Category $category): void
    {
        $category->delete();
    }
}
