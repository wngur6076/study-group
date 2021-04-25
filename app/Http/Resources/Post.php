<?php

namespace App\Http\Resources;

use App\Http\Resources\TagCollection;
use App\Http\Resources\User as UserResource;
use Illuminate\Http\Resources\Json\JsonResource;

class Post extends JsonResource
{
    public function toArray($request)
    {
        return [
            'data' => [
                'type' => 'posts',
                'post_id' => $this->id,
                'attributes' => [
                    'posted_by' => new UserResource($this->user),
                    'tags' => new TagCollection($this->tags),
                    'title' => $this->title,
                    'body' => $this->body,
                    'zone' => $this->zone,
                    'deadline' => $this->deadline->format('Y-m-d H:i'),
                    'max_number_people' => $this->max_number_people,
                ]
            ],
            'links' => [
                'self' => url('/posts/'.$this->id),
            ]
        ];
    }
}
