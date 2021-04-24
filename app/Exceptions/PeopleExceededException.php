<?php

namespace App\Exceptions;

use Exception;

class PeopleExceededException extends Exception
{
    public function render($request)
    {
        return response()->json([
            'errors' => [
                'code' => 422,
                'title' => '모집 인원 초과',
                'detail' => '스터디 인원수가 꽉 찼습니다.',
            ]
        ], 422);
    }
}
