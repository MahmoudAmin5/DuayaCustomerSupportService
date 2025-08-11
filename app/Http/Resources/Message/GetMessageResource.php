<?php

namespace App\Http\Resources\Message;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GetMessageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'chat_id' => $this->chat_id,
            'type' => $this->type,
            'content' => $this->content,
            'file' => $this->when($this->type === 'file', $this->file), // Include file only if type is 'file'
            'created_at' => $this->created_at->diffForHumans(),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            'sender' => $this->whenLoaded('sender', function () {
                return [
                    'sender_id' => $this->sender->id,
                    'employee_name' => $this->sender->name,
                    'phone' => $this->sender->phone,
                ];
            }),
        ];
    }
}
