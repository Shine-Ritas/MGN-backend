<?php

namespace App\Services\Report;

use App\Models\Mogou;

class ChapterReport
{
    protected mixed $submogouCollection;

    public function __construct(protected Mogou $mogou)
    {
        $this->mogou = $mogou;
        $this->submogouCollection = $mogou->subMogous($mogou->rotation_key);
    }

    public function getTotalViews(): int
    {
        return (int) $this->submogouCollection->sum('views');
    }

    public function getTotalChapters(): int
    {
        return $this->submogouCollection->count();
    }
}
