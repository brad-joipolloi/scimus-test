<?php

namespace App\Http\Requests;

use App\Package;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PackageStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('create', Package::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|string|unique:packages,name',
            'aspect_ratio' => [
                'required',
                'string',
                Rule::in(['16:9', '9:16']),
            ],
        ];
    }
}
