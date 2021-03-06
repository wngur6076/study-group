<?php

namespace App\Http\Middleware;

use App\Models\Post;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckIsSelf
{
    public function handle(Request $request, Closure $next)
    {
        $post = Post::findOrFail(request('id'));

        if (Auth::user()->id == $post->user_id) {
            return $next($request);
        }
        else {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
    }
}
