<?php

namespace App\Http\Resources\Chat;
use App\Http\Resources\Message\GetMessageResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GetChatResource extends JsonResource
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
            'agent_id' => $this->agent_id,
            'status' => $this->is_active,
            'created_at' => $this->created_at->diffForHumans(),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            'customer' => $this->whenLoaded('customer', function () {
                return [
                    'customer_id' => $this->customer->id,
                    'customer_name' => $this->customer->name,
                    'phone' => $this->customer->phone,
                ];
            }),
            'messages' => GetMessageResource::collection($this->whenLoaded('messages')),
        ];
    }
}
