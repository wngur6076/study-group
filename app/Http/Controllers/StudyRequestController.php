<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\StudyRequest;
use App\Exceptions\PostNotFoundException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Resources\StudyRequest as StudyRequestReource;
use Carbon\Carbon;

class StudyRequestController extends Controller
{
    public function store()
    {
        $this->validate(request(), [
            'post_id' => ['required'],
            'reason' => ['nullable'],
            'project' => ['nullable'],
        ]);

        try {
            $post = Post::where('id', request('post_id'))
                ->where('deadline', '>', Carbon::now())->firstOrFail();
            $post->numberOfPeopleCheck($post->requestSignCount());

            $post->studyRequest()->syncWithoutDetaching([
                    auth()->user()->id => [
                        'reason' => request('reason'),
                        'project' => request('project'),
                    ]
                ]);
        } catch (ModelNotFoundException $e) {
            throw new PostNotFoundException();
        }

        return new StudyRequestReource(
            StudyRequest::where('post_id', request('post_id'))
                ->where('user_id', auth()->user()->id)
                ->first()
        );
    }
}
