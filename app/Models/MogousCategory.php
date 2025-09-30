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

use Database\Factories\MogousCategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MogousCategory extends Model
{
    /** @use HasFactory<MogousCategoryFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'mogou_id',
        'category_id',
    ];

    /**
     * mogou
     *
     * @return BelongsTo<Mogou, $this>
     */
    public function mogou(): BelongsTo
    {
        return $this->belongsTo(Mogou::class);
    }
}
