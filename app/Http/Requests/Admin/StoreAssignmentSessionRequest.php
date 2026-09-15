<?php

namespace App\Http\Requests\Admin;

use App\Models\SourceOfTruth;
use Illuminate\Foundation\Http\FormRequest;

class StoreAssignmentSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:150'],
            'subject' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:2000'],
            'assignment_date' => ['required', 'date'],
            'questions' => ['required', 'array', 'min:1', 'max:50'],
            'questions.*.question' => ['required', 'string', 'max:1000'],
            'questions.*.expected' => ['required', 'string', 'max:2000'],
            'questions.*.validation_type' => ['required', 'string', 'in:'.implode(',', SourceOfTruth::VALIDATION_TYPES)],
            'questions.*.required_concepts' => ['nullable', 'array'],
            'questions.*.required_concepts.*' => ['string', 'max:100'],
        ];
    }

    protected function prepareForValidation(): void
    {
        // Normalize before validation so rules run against the stored shape.
        // The HTML form submits required_concepts as a comma-separated string;
        // array input is accepted as-is.
        $this->merge([
            'questions' => collect($this->input('questions', []))
                ->values()
                ->map(function ($q) {
                    $concepts = $q['required_concepts'] ?? [];
                    if (is_string($concepts)) {
                        $concepts = explode(',', $concepts);
                    }

                    return [
                        'question' => trim($q['question'] ?? ''),
                        'expected' => trim($q['expected'] ?? ''),
                        'validation_type' => $q['validation_type'] ?? null,
                        'required_concepts' => collect(is_array($concepts) ? $concepts : [])
                            ->map(fn ($c) => trim((string) $c))
                            ->filter()
                            ->values()
                            ->all(),
                    ];
                })
                ->all(),
        ]);
    }
}
