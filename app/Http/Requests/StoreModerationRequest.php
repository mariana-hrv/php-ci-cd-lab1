<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\ModerationRecord;
use Illuminate\Foundation\Http\FormRequest;

class StoreModerationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(
            'create',
            ModerationRecord::class
        ) ?? false;
    }

    public function rules(): array
    {
        return [
            'listing_id' => [
                'required',
                'integer',
                'exists:listings,id',
            ],
            'decision' => [
                'required',
                'in:approved,rejected',
            ],
            'comment' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ];
    }
}
