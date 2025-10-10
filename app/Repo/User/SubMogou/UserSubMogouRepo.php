<?php

namespace App\Repo\User\SubMogou;

use App\Models\Mogou;
use App\Models\SubMogou;
use Illuminate\Database\Eloquent\Collection;

class UserSubMogouRepo
{
    /**
     * Get latest chapters for a Mogou
     *
     * @return Collection<int, SubMogou>
     */
    public function getLatestChapters(Mogou $mogou, int $limit = 10): Collection
    {
        return $mogou->subMogous($mogou->rotation_key)
            ->select('id', 'title', 'slug', 'chapter_number', 'created_at', 'subscription_only', 'third_party_url', 'third_party_redirect')
            ->latest('chapter_number')
            ->limit($limit)
            ->get();
    }

    /**
     * Get all chapters for a Mogou
     *
     * @return Collection<int, SubMogou>
     */
    public function getAllChapters(Mogou $mogou): Collection
    {
        return $mogou->subMogous($mogou->rotation_key)
            ->select('id', 'title', 'slug', 'chapter_number', 'created_at', 'subscription_only', 'third_party_url', 'third_party_redirect')
            ->latest('chapter_number')
            ->get();
    }

    /**
     * Get all chapters summary (minimal data)
     *
     * @return Collection<int, SubMogou>
     */
    public function getAllChaptersSummary(Mogou $mogou): Collection
    {
        return $mogou->subMogous($mogou->rotation_key)
            ->select('id', 'title', 'slug', 'chapter_number')
            ->latest('chapter_number')
            ->get();
    }

    /**
     * Find chapter by slug
     */
    public function findChapterBySlug(Mogou $mogou, string $slug): SubMogou
    {
        return $mogou->subMogous($mogou->rotation_key)
            ->where('slug', $slug)
            ->firstOrFail();
    }

    /**
     * Find chapter by id
     */
    public function findChapterById(Mogou $mogou, int $id): SubMogou
    {
        return $mogou->subMogous($mogou->rotation_key)
            ->where('id', $id)
            ->firstOrFail();
    }

    /**
     * Get next chapter
     */
    public function getNextChapter(Mogou $mogou, SubMogou $currentChapter): ?SubMogou
    {
        return $mogou->subMogous($mogou->rotation_key)
            ->select('id', 'title', 'slug', 'chapter_number')
            ->where('chapter_number', '>', $currentChapter->chapter_number)
            ->oldest('chapter_number')
            ->first();
    }

    /**
     * Get previous chapter
     */
    public function getPreviousChapter(Mogou $mogou, SubMogou $currentChapter): ?SubMogou
    {
        return $mogou->subMogous($mogou->rotation_key)
            ->select('id', 'title', 'slug', 'chapter_number')
            ->where('chapter_number', '<', $currentChapter->chapter_number)
            ->latest('chapter_number')
            ->first();
    }
}
