<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', \Illuminate\Validation\Rule::unique(User::class)->ignore($this->user()->id)],
            'phone' => ['nullable', 'string', 'max:20', \Illuminate\Validation\Rule::unique(User::class)->ignore($this->user()->id)],
            'address' => ['nullable', 'string'],
            'nid_no' => ['nullable', 'string', 'max:20', \Illuminate\Validation\Rule::unique(User::class)->ignore($this->user()->id)],
            'joining_date' => ['nullable', 'date'],
            'photo' => ['nullable', 'image', 'max:2048'],
        ];
    }
}
