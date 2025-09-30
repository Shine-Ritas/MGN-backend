<?php
/**
 * Project: MGN-Backend
 * Owner: @Htet_Shine
 * Email: whoishsh@gmail.com
 * 
 * This file is part of the proprietary source code owned by @Htet_Shine.
 * Unauthorized copying, distribution, or modification is prohibited.
 */
namespace App\Models;

use Database\Factories\BaseSectionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BaseSection extends Model
{
    /** @use HasFactory<BaseSectionFactory> */
    use HasFactory;

    protected $guarded = [];

    /**
     * childSections
     *
     * @return HasMany<ChildSection,$this>
     */
    public function childSections(): HasMany
    {
        return $this->hasMany(ChildSection::class);
    }
}
