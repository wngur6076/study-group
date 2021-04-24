<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StudyRequest extends JsonResource
{
    public function toArray($request)
    {
        return [
            'data' => [
                'type' => 'study-request',
                'study_request_id' => $this->id,
                'attributes' => [
                    'confirmed_at' => $this->confirmed_at,
                    'post_id' => $this->post_id,
                    'user_id' => $this->user_id,
                    'reason' => $this->reason,
                    'project' => $this->project,
                ]
            ],
            'links' => [
                'self' => url('/posts/'.$this->post_id),
            ]
        ];
    }
}
