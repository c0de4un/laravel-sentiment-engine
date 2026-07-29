<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int                       $id
 * @property string                    $user_id
 * @property string                    $text_hash
 * @property string                    $text
 * @property string                    $sentiment
 * @property string                    $status
 * @property Carbon                    $created_at
 * @property Carbon                    $updated_at
 *
 * @method static AnalysisResult create(array $attributes = [])
 * @method static Builder<static>|AnalysisResult newModelQuery()
 * @method static Builder<static>|AnalysisResult newQuery()
 * @method static Builder<static>|AnalysisResult query()
 * @method static Builder<static>|AnalysisResult where($column, $value = null)
 * @method static AnalysisResult findOrFail($id)
 */
final class AnalysisResult extends Model
{
    protected $table = 'analysis_results';

    protected $fillable = [
        'user_id',
        'text_hash',
        'text',
        'sentiment',
        'status',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
