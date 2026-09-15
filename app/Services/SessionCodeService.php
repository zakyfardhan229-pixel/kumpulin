<?php

namespace App\Services;

use App\Models\AssignmentSession;
use DateTimeInterface;
use Illuminate\Support\Str;

class SessionCodeService
{
    /**
     * Generate a unique public session code, e.g. TSK-INF-290926-X82K.
     */
    public function generate(string $subject, DateTimeInterface $date): string
    {
        $subjectPart = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $subject) ?: 'TSK', 0, 3));
        $datePart = $date->format('dmy');

        do {
            $code = sprintf('TSK-%s-%s-%s', $subjectPart, $datePart, strtoupper(Str::random(4)));
        } while (AssignmentSession::where('session_code', $code)->exists());

        return $code;
    }
}
