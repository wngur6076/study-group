<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class User extends JsonResource
{
    public function toArray($request)
    {
        return [
            'data' => [
                'type' => 'users',
                'user_id' => $this->id,
                'attributes' => [
                    'name' => $this->name,
                    'email' => $this->email,
                ]
            ],
            'links' => [
                'self' => url('/users/'.$this->id),
            ]
        ];
    }
}
