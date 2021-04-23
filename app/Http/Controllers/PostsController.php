<?php

namespace App\Http\Controllers;

use App\Http\Resources\User as UserResource;
use Illuminate\Http\Request;

class PostsController extends Controller
{
    public function store()
    {
        $this->validate(request(), [
            'title' => ['required'],
            'body' => ['required'],
            'zone' => ['required'],
            'deadline' => ['required'],
            'max_number_people' => ['required', 'numeric'],
        ]);

        $post = request()->user()->posts()->create([
            'title' => request('title'),
            'body' => request('body'),
            'zone' => request('zone'),
            'deadline' => request('deadline'),
            'max_number_people' => request('max_number_people'),
        ]);

        return response()->json([
            'data' => [
                'type' => 'posts',
                'post_id' => $post->id,
                'attributes' => [
                    'posted_by' => new UserResource($post->user),
                    'title' => $post->title,
                    'body' => $post->body,
                    'zone' => $post->zone,
                    'deadline' => $post->deadline->format('Y-m-d H:i'),
                    'max_number_people' => $post->max_number_people,
                ]
            ],
            'links' => [
                'self' => url('/posts/'.$post->id),
            ]
        ], 201);
    }
}
