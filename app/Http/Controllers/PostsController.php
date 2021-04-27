<?php

namespace App\Http\Controllers;

use App\Http\Resources\Post as PostResource;
use App\Models\Post;

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
            'tags' => ['nullable', 'array'],
        ]);

        $post = request()->user()->posts()->create([
            'title' => request('title'),
            'body' => request('body'),
            'zone' => request('zone'),
            'deadline' => request('deadline'),
            'max_number_people' => request('max_number_people'),
        ]);

        if (request('tags')) {
            foreach (request('tags') as $tag) {
                $post->tags()->create(['name' => $tag]);
            }
        }

        return new PostResource($post);
    }

    public function show($id)
    {
        $post = Post::findOrFail($id);

        return new PostResource($post);
    }
}
