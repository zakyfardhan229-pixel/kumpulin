<?php

namespace App\Services;

use App\Models\Submission;
use Illuminate\Support\Str;

class SubmissionCodeService
{
    /**
     * Generate a unique public submission code, e.g. KMP-8F42A91X.
     */
    public function generate(): string
    {
        do {
            $code = 'KMP-'.strtoupper(Str::random(8));
        } while (Submission::where('submission_code', $code)->exists());

        return $code;
    }
}
