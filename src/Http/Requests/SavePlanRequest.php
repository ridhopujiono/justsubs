<?php

namespace Ridho\JustSubs\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Config;
use Illuminate\Validation\Rule;
use Ridho\JustSubs\Enums\IntervalUnit;

class SavePlanRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Authorization is handled by the middleware
    }

    public function rules()
    {
        $planId = $this->route('plan') ? $this->route('plan')->id : null;
        $plansTable = Config::get('justsubs.tables.plans', 'justsubs_plans');

        return [
            'name' => 'required|string|max:255',
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique($plansTable, 'slug')->ignore($planId),
            ],
            'description' => 'nullable|string|max:1000',
            'price' => 'required|integer|min:0',
            'currency' => 'required|string|size:3',
            'interval_count' => 'required|integer|min:1',
            'interval_unit' => ['required', Rule::enum(IntervalUnit::class)],
            'features' => 'nullable|string', // Will be parsed in controller
            'is_active' => 'boolean',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'is_active' => $this->has('is_active'),
        ]);
    }
}
