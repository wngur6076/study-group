<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Post;
use App\Models\User;
use App\Models\StudyRequest;
use App\Exceptions\PeopleExceededException;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PostTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function cannot_exceed_the_maximum_number_of_people()
    {
        $this->expectException(PeopleExceededException::class);

        $post = Post::factory()->create(['max_number_people' => 3]);
        $post->numberOfPeopleCheck(10);
    }

    /** @test */
    function can_see_the_number_of_paid_study_requests()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        StudyRequest::create([
            'user_id' => $user->id,
            'post_id' => $post->id,
            'confirmed_at' => now(),
            'status' => 1,
        ]);

        StudyRequest::create([
            'user_id' => $user->id,
            'post_id' => $post->id,
            'confirmed_at' => null,
            'status' => 1,
        ]);

        $this->assertEquals(1, $post->requestSignCount());
    }
}
