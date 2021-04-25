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

        $response = $this->post("/api/study-groups/{$post->id}/request", [
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
                'self' => url('/users/'.$studyRequest->user_id),
            ]
        ]);
    }

    /** @test */
    function user_can_send_a_study_request_only_once()
    {
        $this->withoutExceptionHandling();

        $this->actingAs(User::factory()->create(), 'api');
        $post = Post::factory()->create();

        $this->post("/api/study-groups/{$post->id}/request", $this->validParams())
            ->assertStatus(200);
        $this->post("/api/study-groups/{$post->id}/request", $this->validParams())
            ->assertStatus(200);

        $studyRequest = StudyRequest::all();
        $this->assertCount(1, $studyRequest);
    }

    /** @test */
    function only_valid_posts_can_be_study_requested()
    {
        $this->actingAs(User::factory()->create(), 'api');

        $response = $this->post("/api/study-groups/123/request", $this->validParams())
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

    /** @test */
    function after_the_deadline_you_will_not_be_able_to_request_a_study()
    {
        $this->actingAs(User::factory()->create(), 'api');
        $post = Post::factory()->create(['deadline' => now()->subDay()]);

        $response = $this->post("/api/study-groups/{$post->id}/request", $this->validParams())
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

    /** @test */
    function number_of_people_is_full_you_cannot_request_a_study()
    {
        $this->actingAs($user = User::factory()->create(), 'api');
        $post = Post::factory()->create(['max_number_people' => 0]);

        StudyRequest::create([
            'user_id' => $user->id,
            'post_id' => $post->id,
            'confirmed_at' => now(),
            'status' => 1,
        ]);

        $response = $this->post("/api/study-groups/{$post->id}/request", $this->validParams())
            ->assertStatus(422);

        $response->assertJson([
            'errors' => [
                'code' => 422,
                'title' => '모집 인원 초과',
                'detail' => '스터디 인원수가 꽉 찼습니다.',
            ]
        ]);
    }

    /** @test */
    function reason_is_optional()
    {
        $post = Post::factory()->create();

        $response = $this->actingAs(User::factory()->create(), 'api')
            ->json('post', "/api/study-groups/{$post->id}/request", $this->validParams([
                'reason' => null,
            ]));

        $response->assertStatus(200);
    }

    /** @test */
    function project_is_optional()
    {
        $post = Post::factory()->create();

        $response = $this->actingAs(User::factory()->create(), 'api')
            ->json('post', "/api/study-groups/{$post->id}/request", $this->validParams([
                'project' => null,
            ]));

        $response->assertStatus(200);
    }
}
