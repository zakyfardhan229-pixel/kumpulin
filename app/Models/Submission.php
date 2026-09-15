<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Submission extends Model
{
    use HasFactory;

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_PENDING_REVIEW = 'pending_review';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_INCOMPLETE = 'incomplete';

    public const STATUS_REVISION_REQUIRED = 'revision_required';

    public const STATUSES = [
        self::STATUS_PROCESSING,
        self::STATUS_PENDING_REVIEW,
        self::STATUS_COMPLETED,
        self::STATUS_INCOMPLETE,
        self::STATUS_REVISION_REQUIRED,
    ];

    /** Final statuses can only be set through an admin review. */
    public const FINAL_STATUSES = [
        self::STATUS_COMPLETED,
        self::STATUS_INCOMPLETE,
        self::STATUS_REVISION_REQUIRED,
    ];

    protected $fillable = [
        'assignment_session_id',
        'submission_code',
        'nama_lengkap',
        'kelas',
        'jurusan',
        'catatan',
        'status',
    ];

    /** @return BelongsTo<AssignmentSession, $this> */
    public function session(): BelongsTo
    {
        return $this->belongsTo(AssignmentSession::class, 'assignment_session_id');
    }

    /** @return HasMany<SubmissionFile, $this> */
    public function files(): HasMany
    {
        return $this->hasMany(SubmissionFile::class);
    }

    /** @return HasMany<AiValidation, $this> */
    public function aiValidations(): HasMany
    {
        return $this->hasMany(AiValidation::class)->orderByDesc('id');
    }

    /** @return HasOne<AiValidation, $this> */
    public function latestAiValidation(): HasOne
    {
        return $this->hasOne(AiValidation::class)->ofMany('id', 'max');
    }

    /** @return HasMany<AdminReview, $this> */
    public function adminReviews(): HasMany
    {
        return $this->hasMany(AdminReview::class)->orderByDesc('id');
    }

    /** @return HasOne<AdminReview, $this> */
    public function latestReview(): HasOne
    {
        return $this->hasOne(AdminReview::class)->ofMany('id', 'max');
    }

    public function isFinal(): bool
    {
        return in_array($this->status, self::FINAL_STATUSES, true);
    }
}
