<?php

namespace App\Http\Requests\Admin;

use App\Models\AssignmentSession;

class UpdateAssignmentSessionRequest extends StoreAssignmentSessionRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'status' => ['sometimes', 'string', 'in:'.implode(',', AssignmentSession::STATUSES)],
        ]);
    }
}
