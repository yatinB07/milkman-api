<?php

namespace App\Repositories;

use App\Exceptions\Catalog\PageNotFoundException;
use App\Models\Page;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PageRepository
{
    /** @return LengthAwarePaginator<int, Page> */
    public function paginate(?string $search = null, int $perPage = 15): LengthAwarePaginator
    {
        return Page::query()
            ->when($search, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query
                        ->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->orderBy('title')
            ->paginate($perPage);
    }

    /** @param array<string, mixed> $attributes */
    public function create(array $attributes): Page
    {
        return Page::query()->create($attributes);
    }

    public function find(int $id): Page
    {
        $page = Page::query()->find($id);

        if (! $page) {
            throw new PageNotFoundException;
        }

        return $page;
    }

    /** @return LengthAwarePaginator<int, Page> */
    public function paginateActive(?string $search = null, int $perPage = 15): LengthAwarePaginator
    {
        return Page::query()
            ->where('is_active', true)
            ->when($search, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query
                        ->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->orderBy('title')
            ->paginate($perPage);
    }

    public function findActive(int $id): Page
    {
        $page = Page::query()
            ->where('is_active', true)
            ->find($id);

        if (! $page) {
            throw new PageNotFoundException;
        }

        return $page;
    }

    /** @param array<string, mixed> $attributes */
    public function update(Page $page, array $attributes): Page
    {
        $page->update($attributes);

        return $page->refresh();
    }

    public function delete(Page $page): void
    {
        $page->delete();
    }
}
