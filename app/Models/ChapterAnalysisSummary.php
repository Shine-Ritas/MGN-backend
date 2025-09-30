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

use Database\Factories\ChapterAnalysisSummaryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChapterAnalysisSummary extends Model
{
    /** @use HasFactory<ChapterAnalysisSummaryFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'sub_mogou_id', 'mogou_id', 'total_views', 'start_date', 'end_date',
    ];
}
