<?php

namespace App\Http\Requests;

use App\Models\PricingItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

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
            'ups' => ['required', 'integer', 'min:1'],
            'no_of_sheets_with_wastage' => ['required', 'numeric', 'integer', 'min:0'],
            'no_of_sheets_to_process' => ['required', 'numeric', 'integer', 'min:0'],
            'printing_cost' => ['required', 'numeric', 'min:0'],
            'ink_cost' => ['required', 'numeric', 'min:0'],
            'foiling_cost' => ['nullable', 'numeric', 'min:0'],
            'needs_punching' => ['required', 'boolean'],
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
            'punching_pricing_item_id' => [
                'nullable',
                'required_if:needs_punching,1',
                Rule::exists('pricing_items', 'id'),
                $this->pricingItemRule('punching'),
            ],
            'needs_lamination' => ['required', 'boolean'],
            'window_labor_cost' => ['nullable', 'numeric', 'min:0'],
            'needs_lace_cost' => ['required', 'boolean'],
            'designing_cost' => ['nullable', 'numeric', 'min:0'],
            'punch_cost_job_type' => ['required', Rule::in(['repeat_job', 'new_job'])],
            'new_job_punch_cost' => ['nullable', 'required_if:punch_cost_job_type,new_job', 'numeric', 'min:0'],
            'expenses' => ['required', 'numeric', 'min:0'],
            'lamination_mode' => [
                'nullable',
                'required_if:needs_lamination,1',
                Rule::in(['front_only', 'both_sides']),
            ],
            'lamination_front_pricing_item_id' => [
                'nullable',
                'required_if:needs_lamination,1',
                Rule::exists('pricing_items', 'id'),
                $this->pricingItemRule('lamination', ['BOPP Lamination', 'Matte Lamination']),
            ],
            'lamination_back_pricing_item_id' => [
                'nullable',
                Rule::requiredIf(fn () => $this->boolean('needs_lamination')
                    && $this->input('lamination_mode') === 'both_sides'),
                Rule::exists('pricing_items', 'id'),
                $this->pricingItemRule('lamination', ['BOPP Lamination', 'Matte Lamination']),
            ],
            'needs_spot_uv' => ['required', 'boolean'],
            'spot_uv_pricing_item_id' => [
                'nullable',
                'required_if:needs_spot_uv,1',
                Rule::exists('pricing_items', 'id'),
                $this->pricingItemRule('spot-uv', ['Spot UV', 'Raised UV']),
            ],
            'needs_drip_off' => ['required', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'length' => 'length',
            'width' => 'width',
            'gsm' => 'GSM',
            'no_of_sheets' => 'no. of sheets',
            'ups' => 'ups',
            'no_of_sheets_with_wastage' => 'no. of sheets with wastage',
            'no_of_sheets_to_process' => 'no. of sheets to process',
            'printing_cost' => 'printing cost',
            'ink_cost' => 'ink cost',
            'foiling_cost' => 'foiling cost',
            'needs_punching' => 'need punching',
            'paper_pricing_item_id' => 'paper rate',
            'interest_pricing_item_id' => 'interest slab',
            'punching_pricing_item_id' => 'punching type',
            'needs_lamination' => 'need lamination',
            'window_labor_cost' => 'window & labor cost',
            'needs_lace_cost' => 'apply lace',
            'designing_cost' => 'designing cost',
            'punch_cost_job_type' => 'punch cost job type',
            'new_job_punch_cost' => 'new job punch cost',
            'expenses' => 'expenses',
            'lamination_mode' => 'lamination coverage',
            'lamination_front_pricing_item_id' => 'front side lamination type',
            'lamination_back_pricing_item_id' => 'back side lamination type',
            'needs_spot_uv' => 'need spot UV',
            'spot_uv_pricing_item_id' => 'UV type',
            'needs_drip_off' => 'need drip off',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (! $this->filled('no_of_sheets_to_process')) {
                return;
            }

            if ((int) $this->input('no_of_sheets_to_process') !== 0) {
                return;
            }

            if (! $this->boolean('needs_lamination')
                && ! $this->boolean('needs_spot_uv')
                && ! $this->boolean('needs_drip_off')) {
                return;
            }

            $validator->errors()->add(
                'no_of_sheets_to_process',
                'No. of sheets to process must be greater than 0 when a process option is selected.'
            );
        });
    }

    private function pricingItemRule(string $categorySlug, array $allowedNames = []): callable
    {
        return function (string $attribute, mixed $value, callable $fail) use ($categorySlug, $allowedNames): void {
            $query = PricingItem::query()
                ->whereKey($value)
                ->where('is_active', true)
                ->where('is_selectable', true)
                ->whereHas('pricingCategory', function ($query) use ($categorySlug) {
                    $query
                        ->where('slug', $categorySlug)
                        ->where('is_active', true);
                });

            if ($allowedNames !== []) {
                $query->whereIn('name', $allowedNames);
            }

            if (! $query->exists()) {
                $fail('The selected :attribute is invalid.');
            }
        };
    }
}
