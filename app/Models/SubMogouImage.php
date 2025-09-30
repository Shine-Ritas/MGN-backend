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

use App\Traits\DbPartition;
use Database\Factories\SubMogouImageFactory;
use HydraStorage\HydraStorage\Traits\HydraMedia;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Ritas\Lexorank\LexoRankTrait;

class SubMogouImage extends Model
{
    /** @use HasFactory<SubMogouImageFactory> */
    use DbPartition,HasFactory,HydraMedia,LexoRankTrait;

    protected $table = 'sub_mogou_images';

    protected string $partition_prefix = 'sub_mogou_images';

    protected static string $sortableField = 'position';

    protected string $baseTable = 'sub_mogou_images';

    /**
     * applySortableQuery
     *
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    protected static function applySortableQuery(Builder $query, SubMogouImage $model): Builder
    {
        $query->where('mogou_id', $model->mogou_id)
            ->where('sub_mogou_id', $model->sub_mogou_id);

        return $query;
    }

    protected static function boot(): void
    {
        parent::boot();

        static::dbConstructing();
    }

    protected $fillable = [
        'mogou_id',
        'sub_mogou_id',
        'path',
        'position',
    ];

    public function getPathAttribute(string $value): string
    {
        return $this->getMedia($value, "mogou/$this->mogou_id/$this->sub_mogou_id");
    }

    /**
     * subMogou
     *
     * @return BelongsTo<SubMogou, $this>
     */
    public function subMogou(): BelongsTo
    {
        return $this->belongsTo(SubMogou::class);
    }
}
