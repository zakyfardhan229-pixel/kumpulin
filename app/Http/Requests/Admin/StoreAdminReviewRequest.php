<?php

namespace App\Http\Requests\Admin;

use App\Models\Submission;
use Illuminate\Foundation\Http\FormRequest;

class StoreAdminReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'decision' => ['required', 'string', 'in:'.implode(',', Submission::FINAL_STATUSES)],
            'note' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
