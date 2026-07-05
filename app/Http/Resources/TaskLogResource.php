<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class TaskLogResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'old_status' => $this->old_status,
            'new_status' => $this->new_status,
            'comment' => $this->comment,
            'created_at' => $this->created_at ? Carbon::parse($this->created_at)->toISOString() : null,
            'user' => new UserResource($this->whenLoaded('user')),
        ];
    }
}
