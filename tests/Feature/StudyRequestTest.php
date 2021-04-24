<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Post;
use App\Models\User;
use App\Models\StudyRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StudyRequestTest extends TestCase
{
    use RefreshDatabase;

    private function validParams($overrides = [])
    {
        return array_merge([
            'post_id' => Post::factory()->create()->id,
            'reason' => '테스트',
            'project' => 'test',
        ], $overrides);
    }

    /** @test */
    function user_can_send_a_study_request()
    {
        $this->withoutExceptionHandling();

        $this->actingAs($user = User::factory()->create(['id' => 123]), 'api');
        $post = Post::factory()->create();

        $response = $this->post('/api/study-request', [
            'post_id' => $post->id,
            'reason' => '테스트',
            'project' => 'test',
        ])->assertStatus(200);

        $studyRequest = StudyRequest::first();

        $this->assertNotNull($studyRequest);
        $this->assertEquals($post->id, $studyRequest->post_id);
        $this->assertEquals($user->id, $studyRequest->user_id);
        $response->assertJson([
            'data' => [
                'type' => 'study-request',
                'study_request_id' => $studyRequest->id,
                'attributes' => [
                    'confirmed_at' => null,
                    'reason' => '테스트',
                    'project' => 'test',
                ]
            ],
            'links' => [
                'self' => url('/posts/'.$post->id),
            ]
        ]);
    }

    /** @test */
    function user_can_send_a_study_request_only_once()
    {
        $this->withoutExceptionHandling();

        $this->actingAs(User::factory()->create(), 'api');
        $post = Post::factory()->create();

        $this->post('/api/study-request', $this->validParams([
            'post_id' => $post->id,
        ]))->assertStatus(200);
        $this->post('/api/study-request', $this->validParams([
            'post_id' => $post->id,
        ]))->assertStatus(200);

        $studyRequest = StudyRequest::all();
        $this->assertCount(1, $studyRequest);
    }

    /** @test */
    function only_valid_posts_can_be_study_requested()
    {
        $this->actingAs(User::factory()->create(), 'api');

        $response = $this->post('/api/study-request', $this->validParams([
            'post_id' => 123,
        ]))
            ->assertStatus(404);

        $this->assertNull(StudyRequest::first());
        $response->assertJson([
            'errors' => [
                'code' => 404,
                'title' => 'Post Not Found',
                'detail' => 'Unable to locate the post with the given information.',
            ]
        ]);
    }
}
