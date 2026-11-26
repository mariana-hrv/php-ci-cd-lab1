<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Listing;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateListingRequest extends FormRequest
{
    public function authorize(): bool
    {
        $listing = $this->route('listing');

        if (! $listing instanceof Listing) {
            $listing = Listing::findOrFail((int) $this->route('id'));
        }

        return $this->user()?->can('update', $listing) ?? false;
    }

    public function rules(): array
    {
        return [
            'price' => ['sometimes', 'required', 'numeric', 'gt:0'],
            'description' => ['nullable', 'string'],
            'status' => ['sometimes', Rule::in([
                'draft',
                'published',
                'archived',
            ])],
        ];
    }
}
