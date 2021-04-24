<?php

namespace App\Exceptions;

use Exception;

class PostNotFoundException extends Exception
{
    public function render($request)
    {
        return response()->json([
            'errors' => [
                'code' => 404,
                'title' => 'Post Not Found',
                'detail' => 'Unable to locate the post with the given information.',
            ]
        ], 404);
    }
}
