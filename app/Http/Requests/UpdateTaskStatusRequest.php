<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskStatusRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Detailed role validation inside TaskService
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'status' => 'required|string|in:draft,assigned,in_progress,pending_sc,pending_pm,approved,rejected',
            'user_id' => 'required|exists:users,id',
            'fact_qty' => 'nullable|numeric',
            'fact_man_day' => 'nullable|numeric',
            'overtime' => 'nullable|numeric',
            'comment' => 'required_if:fact_qty,-1|nullable|string',
        ];
    }
}
