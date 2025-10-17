<?php

namespace App\Services\Mogou;

use App\Events\ChapterViewed;
use App\Models\Mogou;
use App\Models\SubMogou;
use App\Models\SubMogouImage;
use App\Models\User;
use App\Repo\Admin\SubMogouRepo\SubMogouImageRepo;
use App\Repo\User\Mogou\UserMogouRepo;
use App\Repo\User\SubMogou\UserSubMogouRepo;
use App\Services\ApplicationConfig\CacheApplicationConfigService;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class UserMogouService
{
    public function __construct(
        protected UserMogouRepo $mogouRepo,
        protected UserSubMogouRepo $subMogouRepo,
        protected SubMogouImageRepo $imageRepo,
        protected CacheApplicationConfigService $applicationConfigService
    ) {}

    /**
     * Get Mogou details with chapters and favorite status
     *
     * @return array<string, mixed>
     */
    public function getMogouDetails(string $slug, ?int $userId = null): array
    {
        $mogou = $this->mogouRepo->findBySlugWithCategories($slug);
        $chapters = $this->subMogouRepo->getLatestChapters($mogou, 10);
        $isFavorite = $this->checkIsFavorite($mogou->id, $userId);

        return [
            'mogou' => $mogou,
            'is_favorite' => $isFavorite,
            'chapters' => $chapters,
        ];
    }

    /**
     * Get all chapters for a Mogou
     *
     * @return Collection<int, SubMogou>
     */
    public function getAllChapters(string $slug): Collection
    {
        $mogou = $this->mogouRepo->findBySlug($slug);

        return $this->subMogouRepo->getAllChapters($mogou);
    }

    /**
     * Get related Mogous
     *
     * @return Collection<int, Mogou>
     */
    public function getRelatedMogous(string $slug, int $limit = 6): Collection
    {
        $mogou = $this->mogouRepo->findBySlug($slug);

        return $this->mogouRepo->getRelatedByCategories($mogou, $limit);
    }

    /**
     * Get chapter details with images, navigation, and intro/outro
     *
     * @return array<string, mixed>
     */
    public function getChapterDetails(string $mogouSlug, string $chapterSlug): array
    {
        $mogou = $this->mogouRepo->findBySlug(
            $mogouSlug,
            ['id', 'rotation_key', 'title', 'slug', 'cover']
        );

        $currentChapter = $this->subMogouRepo->findChapterBySlug($mogou, $chapterSlug);

        // Get chapter images
        $images = $this->getChapterImages($currentChapter, $mogou);
        $currentChapter['images'] = $images;

        // Get navigation chapters
        $allChapters = $this->subMogouRepo->getAllChaptersSummary($mogou);
        $nextChapter = $this->subMogouRepo->getNextChapter($mogou, $currentChapter);
        $previousChapter = $this->subMogouRepo->getPreviousChapter($mogou, $currentChapter);

        return [
            'current_chapter' => $currentChapter,
            'prev_chapter' => $previousChapter,
            'next_chapter' => $nextChapter,
            'all_chapters' => $allChapters,
            'mogou' => $mogou,
        ];
    }

    /**
     * Get chapter images with intro/outro if enabled
     *
     * @return Collection<int, SubMogouImage|array<string, mixed>>
     */
    protected function getChapterImages(SubMogou $chapter, Mogou $mogou): Collection
    {
        $images = $this->imageRepo->getImages($chapter, $mogou->rotation_key)->get();
        $applicationConfig = $this->applicationConfigService->getApplicationConfig();

        // Add intro/outro if prefix is active
        if ($applicationConfig->prefix_active == '1') {
            $intro = $this->createIntroImage($applicationConfig, $chapter, $mogou);
            $outro = $this->createOutroImage($applicationConfig, $chapter, $mogou, $images);

            $images->prepend($intro);
            $images->push($outro);
        }

        return $images;
    }

    /**
     * Create intro image array
     *
     * @return array<string, mixed>
     */
    protected function createIntroImage(mixed $config, SubMogou $chapter, Mogou $mogou): array
    {
        return [
            'id' => Str::uuid(),
            'path' => $config->intro_a,
            'sub_mogou_id' => $chapter->id,
            'mogou_id' => $mogou->id,
            'position' => 0,
        ];
    }

    /**
     * Create outro image array
     *
     * @param  Collection<int, SubMogouImage>  $images
     * @return array<string, mixed>
     */
    protected function createOutroImage(mixed $config, SubMogou $chapter, Mogou $mogou, Collection $images): array
    {
        return [
            'id' => Str::uuid(),
            'path' => $config->outro_a,
            'sub_mogou_id' => $chapter->id,
            'mogou_id' => $mogou->id,
            'position' => $images->last()?->position.'z',
        ];
    }

    /**
     * Record chapter view event
     */
    public function recordChapterView(int $mogouId, int $chapterId): void
    {
        $mogou = $this->mogouRepo->findById($mogouId);
        $chapter = $this->subMogouRepo->findChapterById($mogou, $chapterId);

        event(new ChapterViewed($chapter));
    }

    /**
     * Check if Mogou is in user's favorites
     */
    protected function checkIsFavorite(int $mogouId, ?int $userId): bool
    {
        if (! $userId) {
            return false;
        }

        $user = User::find($userId);
        if (! $user) {
            return false;
        }

        return $user->favorites()->where('mogou_id', $mogouId)->exists();
    }

    public function getRandomMogou(): Mogou
    {
        return $this->mogouRepo->getRandomMogou();
    }
}
