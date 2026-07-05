<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = \App\Models\User::find($this->tech_office_id);
        return $user && $user->role === 'tech_office';
    }

    public function rules(): array
    {
        return [
            'zzz_code' => 'required|string|max:255',
            'tow' => 'required|string',
            'stow' => 'required|string',
            'sstow' => 'required|string',
            'planned_qty' => 'required|numeric',
            'planned_man_day' => 'required|numeric',
            'hom_id' => 'required|exists:users,id',
            'tech_office_id' => 'required|exists:users,id',
            'due_date' => 'required|date',
        ];
    }
}
