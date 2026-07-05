<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'zzz_code' => $this->zzz_code,
            'tow' => $this->tow,
            'stow' => $this->stow,
            'sstow' => $this->sstow,
            'planned_qty' => $this->planned_qty,
            'planned_man_day' => $this->planned_man_day,
            'fact_qty' => $this->fact_qty,
            'fact_man_day' => $this->fact_man_day,
            'overtime' => $this->overtime,
            'comment' => $this->comment,
            'local_id' => $this->local_id,
            'status' => $this->status,
            'due_date' => $this->due_date,
            'techOffice' => new UserResource($this->whenLoaded('techOffice')),
            'hom' => new UserResource($this->whenLoaded('hom')),
            'sc' => new UserResource($this->whenLoaded('sc')),
            'pm' => new UserResource($this->whenLoaded('pm')),
            'logs' => TaskLogResource::collection($this->whenLoaded('logs')),
            'created_at' => $this->created_at ? $this->created_at->toISOString() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toISOString() : null,
        ];
    }
}
