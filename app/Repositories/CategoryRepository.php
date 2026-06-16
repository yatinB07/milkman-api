<?php

namespace App\Repositories;

use App\Exceptions\Catalog\CategoryNotFoundException;
use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;

class CategoryRepository
{
    /** @return Collection<int, Category> */
    public function all(): Collection
    {
        return Category::query()
            ->orderBy('title')
            ->get();
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
