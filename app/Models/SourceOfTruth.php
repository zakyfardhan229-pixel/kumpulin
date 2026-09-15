<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SourceOfTruth extends Model
{
    use HasFactory;

    public const VALIDATION_EXACT = 'exact';

    public const VALIDATION_SEMANTIC = 'semantic';

    public const VALIDATION_VISUAL = 'visual';

    public const VALIDATION_CHECKLIST = 'checklist';

    public const VALIDATION_TYPES = [
        self::VALIDATION_EXACT,
        self::VALIDATION_SEMANTIC,
        self::VALIDATION_VISUAL,
        self::VALIDATION_CHECKLIST,
    ];

    protected $fillable = [
        'assignment_session_id',
        'version',
        'questions',
    ];

    protected function casts(): array
    {
        return [
            'questions' => 'array',
        ];
    }

    /** @return BelongsTo<AssignmentSession, $this> */
    public function session(): BelongsTo
    {
        return $this->belongsTo(AssignmentSession::class, 'assignment_session_id');
    }
}
