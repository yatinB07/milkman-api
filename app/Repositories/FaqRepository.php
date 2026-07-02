<?php

namespace App\Repositories;

use App\Exceptions\Catalog\FaqNotFoundException;
use App\Models\Faq;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class FaqRepository
{
    /** @return LengthAwarePaginator<int, Faq> */
    public function paginate(?string $search = null, int $perPage = 15): LengthAwarePaginator
    {
        return Faq::query()
            ->with('store')
            ->when($search, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('question', 'like', "%{$search}%")
                        ->orWhere('answer', 'like', "%{$search}%")
                        ->orWhereHas('store', function ($query) use ($search): void {
                            $query->where('title', 'like', "%{$search}%");
                        });
                });
            })
            ->orderBy('question')
            ->paginate($perPage);
    }

    /** @param array<string, mixed> $attributes */
    public function create(array $attributes): Faq
    {
        return Faq::query()->create($attributes)->load('store');
    }

    public function find(int $id): Faq
    {
        $faq = Faq::query()
            ->with('store')
            ->find($id);

        if (! $faq) {
            throw new FaqNotFoundException;
        }

        return $faq;
    }

    /** @param array<string, mixed> $attributes */
    public function update(Faq $faq, array $attributes): Faq
    {
        $faq->update($attributes);

        return $faq->refresh()->load('store');
    }

    public function delete(Faq $faq): void
    {
        $faq->delete();
    }
}
