<?php

namespace App\Http\Controllers;

use App\Http\Resources\StudyRequest as StudyRequestResource;
use App\Models\StudyRequest;

class StudyRequestResponseController extends Controller
{
    public function store($id)
    {
        $this->validate(request(), [
            'user_id' => ['required'],
            'status' => ['required'],
        ]);

        $studyRequest = StudyRequest::where('post_id', $id)
            ->where('user_id', request('user_id'))
            ->firstOrFail();

        $studyRequest->update([
            'confirmed_at' => now(),
            'status' => request('status'),
        ]);

        return new StudyRequestResource($studyRequest);
    }
}
