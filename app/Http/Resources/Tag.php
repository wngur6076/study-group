<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Tag extends JsonResource
{
    public function toArray($request)
    {
        return [
            'data' => [
                'type' => 'tags',
                'tag_id' => $this->id,
                'attributes' => [
                    'name' => $this->name,
                ]
            ],
            'links' => [
                'self' => url('/tags/'.$this->id),
            ]
        ];
    }
}
