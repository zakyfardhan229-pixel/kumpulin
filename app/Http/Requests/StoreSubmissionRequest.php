<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSubmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'nama_lengkap' => ['required', 'string', 'max:150'],
            'kelas' => ['required', 'string', 'max:50'],
            'jurusan' => ['required', 'string', 'max:100'],
            'catatan' => ['nullable', 'string', 'max:1000'],
            'file' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'confirm_resubmit' => ['sometimes', 'accepted'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'file.image' => 'File harus berupa gambar.',
            'file.mimes' => 'Format gambar harus JPG, JPEG, PNG, atau WEBP.',
            'file.max' => 'Ukuran gambar maksimal 10 MB.',
        ];
    }
}
