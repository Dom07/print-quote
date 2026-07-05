<?php

namespace App\Http\Requests;

use App\Models\PricingItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EstimateSandboxRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'length' => ['required', 'numeric', 'gt:0'],
            'width' => ['required', 'numeric', 'gt:0'],
            'gsm' => ['required', 'numeric', 'gt:0'],
            'no_of_sheets' => ['required', 'numeric', 'integer', 'min:1'],
            'no_of_sheets_with_wastage' => ['required', 'numeric', 'integer', 'min:0'],
            'no_of_sheets_to_process' => ['required', 'numeric', 'integer', 'min:0'],
            'paper_pricing_item_id' => [
                'required',
                Rule::exists('pricing_items', 'id'),
                $this->pricingItemRule('paper'),
            ],
            'interest_pricing_item_id' => [
                'required',
                Rule::exists('pricing_items', 'id'),
                $this->pricingItemRule('interest-slabs'),
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'length' => 'length',
            'width' => 'width',
            'gsm' => 'GSM',
            'no_of_sheets' => 'no. of sheets',
            'no_of_sheets_with_wastage' => 'no. of sheets with wastage',
            'no_of_sheets_to_process' => 'no. of sheets to process',
            'paper_pricing_item_id' => 'paper rate',
            'interest_pricing_item_id' => 'interest slab',
        ];
    }

    private function pricingItemRule(string $categorySlug): callable
    {
        return function (string $attribute, mixed $value, callable $fail) use ($categorySlug): void {
            if (! PricingItem::query()
                ->whereKey($value)
                ->where('is_active', true)
                ->where('is_selectable', true)
                ->whereHas('pricingCategory', function ($query) use ($categorySlug) {
                    $query
                        ->where('slug', $categorySlug)
                        ->where('is_active', true);
                })
                ->exists()) {
                $fail('The selected :attribute is invalid.');
            }
        };
    }
}
