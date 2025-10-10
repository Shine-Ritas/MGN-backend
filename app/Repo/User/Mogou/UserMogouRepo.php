<?php

namespace App\Repo\User\Mogou;

use App\Models\Mogou;
use Illuminate\Database\Eloquent\Collection;

class UserMogouRepo
{
    /**
     * Find Mogou by slug with relations
     */
    public function findBySlugWithCategories(string $slug): Mogou
    {
        return Mogou::where('slug', $slug)
            ->with('categories')
            ->firstOrFail()
            ->append('total_view_count');
    }

    /**
     * Find Mogou by slug
     */
    public function findBySlug(string $slug, array $select = ['*']): Mogou
    {
        return Mogou::select($select)
            ->where('slug', $slug)
            ->firstOrFail();
    }

    /**
     * Find Mogou by id
     */
    public function findById(int $id, array $select = ['*']): Mogou
    {
        return Mogou::select($select)
            ->where('id', $id)
            ->firstOrFail();
    }

    /**
     * Get related Mogous by categories
     *
     * @return Collection<int, Mogou>
     */
    public function getRelatedByCategories(Mogou $mogou, int $limit = 6): Collection
    {
        return Mogou::select('id', 'title', 'rotation_key', 'slug', 'author', 'cover', 'total_chapters')
            ->where('id', '!=', $mogou->id)
            ->whereHas('categories', function ($query) use ($mogou) {
                $query->whereIn('category_id', $mogou->categories->pluck('id'));
            })
            ->latest()
            ->limit($limit)
            ->get();
    }
}
