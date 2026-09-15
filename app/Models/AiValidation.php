<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiValidation extends Model
{
    use HasFactory;

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_ERROR = 'error';

    public const RESULT_LIKELY_COMPLETED = 'likely_completed';

    public const RESULT_LIKELY_INCOMPLETE = 'likely_incomplete';

    public const RESULT_UNCERTAIN = 'uncertain';

    public const RESULTS = [
        self::RESULT_LIKELY_COMPLETED,
        self::RESULT_LIKELY_INCOMPLETE,
        self::RESULT_UNCERTAIN,
    ];

    protected $fillable = [
        'submission_id',
        'model',
        'status',
        'result',
        'confidence',
        'analysis',
        'raw_response',
        'source_of_truth_version',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'analysis' => 'array',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function failed(): bool
    {
        return $this->status === self::STATUS_ERROR;
    }

    /** @return BelongsTo<Submission, $this> */
    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }
}
