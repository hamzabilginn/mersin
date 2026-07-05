<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sender_id' => $this->sender_id,
            'receiver_id' => $this->receiver_id,
            'channel' => $this->channel,
            'content' => $this->content,
            'offline_id' => $this->offline_id,
            'sender' => new UserResource($this->whenLoaded('sender')),
            'created_at' => $this->created_at ? $this->created_at->toISOString() : null,
        ];
    }
}
