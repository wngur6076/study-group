<?php

namespace App\Http\Resources;

use App\Models\User;
use App\Http\Resources\User as UserResource;
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
                    'study_requested_by' => new UserResource(User::findOrFail($this->user_id)),
                    'confirmed_at' => optional($this->confirmed_at)->diffForHumans(),
                    'status' => $this->status,
                    'reason' => $this->reason,
                    'project' => $this->project,
                ]
            ],
            'links' => [
                'self' => url('/users/'.$this->user_id),
            ]
        ];
    }
}
