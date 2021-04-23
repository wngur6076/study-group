<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class TagCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection,
            'tag_count' => $this->count(),
            'links' => [
                'self' => url('/tags'),
            ]
        ];
    }
}
