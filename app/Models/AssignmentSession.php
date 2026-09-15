<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AssignmentSession extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_CLOSED = 'closed';

    public const STATUS_ARCHIVED = 'archived';

    public const STATUSES = [
        self::STATUS_ACTIVE,
        self::STATUS_CLOSED,
        self::STATUS_ARCHIVED,
    ];

    protected $fillable = [
        'session_code',
        'title',
        'subject',
        'description',
        'assignment_date',
        'status',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'assignment_date' => 'date',
        ];
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** @return HasMany<SourceOfTruth, $this> */
    public function sourceOfTruths(): HasMany
    {
        return $this->hasMany(SourceOfTruth::class)->orderByDesc('version');
    }

    /** @return HasOne<SourceOfTruth, $this> */
    public function currentSourceOfTruth(): HasOne
    {
        return $this->hasOne(SourceOfTruth::class)->ofMany('version', 'max');
    }

    /** @return HasMany<Submission, $this> */
    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }
}
