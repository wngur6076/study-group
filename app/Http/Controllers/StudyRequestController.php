<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\StudyRequest;
use App\Exceptions\PostNotFoundException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Resources\StudyRequest as StudyRequestReource;
use App\Http\Resources\StudyRequestCollection;
use Carbon\Carbon;

class StudyRequestController extends Controller
{
    public function index($id)
    {
        $studyRequests = StudyRequest::where('post_id', $id)->get();

        return new StudyRequestCollection($studyRequests);
    }

    public function store($id)
    {
        $this->validate(request(), [
            'reason' => ['nullable'],
            'project' => ['nullable'],
        ]);

        try {
            $post = Post::where('id', $id)
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
            StudyRequest::where('post_id', $id)
                ->where('user_id', auth()->user()->id)
                ->first()
        );
    }
}
