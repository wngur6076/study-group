<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckIsRequestorOrSelf
{
    public function handle(Request $request, Closure $next)
    {
        $post = Post::findOrFail(request('id'));

        if (Auth::user()->id == request('user_id') ||
            Auth::user()->id == $post->user_id) {
            return $next($request);
        }
        else {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
    }
}
