<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Tests\TestCase;
use App\Models\Post;
use App\Models\User;
use App\Models\StudyRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StudyRequestAcceptTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function study_requests_can_be_accepted()
    {
        $this->withoutExceptionHandling();
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);

        $anotherUser = User::factory()->create(['email' => 'test@test.com']);
        $this->actingAs($anotherUser, 'api')
            ->post("/api/study-groups/{$post->id}/request", [
                'reason' => '테스트',
                'project' => 'test',
            ])->assertStatus(200);

        $response = $this->actingAs($user, 'api')
            ->post("/api/study-groups/{$post->id}/request-response", [
                'user_id' => $anotherUser->id,
                'status' => 1,
            ])->assertStatus(200);

        $studyRequest = StudyRequest::first();

        $this->assertNotNull($studyRequest->confirmed_at);
        $this->assertInstanceOf(Carbon::class, $studyRequest->confirmed_at);
        $this->assertEquals(now()->startOfSecond(), $studyRequest->confirmed_at);
        $this->assertEquals(1, $studyRequest->status);

        $response->assertJson([
            'data' => [
                'type' => 'study-request',
                'study_request_id' => $studyRequest->id,
                'attributes' => [
                    'study_requested_by' => [
                        'data' => [
                            'attributes' => [
                                'email' => 'test@test.com',
                            ]
                        ]
                    ],
                    'confirmed_at' => $studyRequest->confirmed_at->diffForHumans(),
                    'reason' => '테스트',
                ]
            ],
            'links' => [
                'self' => url('/users/'.$studyRequest->user_id),
            ]
        ]);
    }

    /** @test */
    function only_the_study_organizer_can_accept_the_request()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);

        $anotherUser = User::factory()->create();
        $this->actingAs($anotherUser, 'api')
            ->post("/api/study-groups/{$post->id}/request", [
                'reason' => '테스트',
                'project' => 'test',
            ])->assertStatus(200);

        $this->actingAs($anotherUser, 'api')
            ->post("/api/study-groups/{$post->id}/request-response", [
                'user_id' => $anotherUser->id,
                'status' => 1,
            ])->assertStatus(403);

        $studyRequest = StudyRequest::first();
        $this->assertNull($studyRequest->confirmed_at);
        $this->assertNull($studyRequest->status);
    }

    /** @test */
    function a_user_id_and_status_is_required_for_study_request_responses()
    {
        $this->actingAs($user = User::factory()->create(), 'api');
        $post = Post::factory()->create(['user_id' => $user->id]);

        $response = $this->json('post', "/api/study-groups/{$post->id}/request-response", [
                'user_id' => '',
                'status' => '',
            ])->assertStatus(422);

        $responseString = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('user_id', $responseString['errors']);
        $this->assertArrayHasKey('status', $responseString['errors']);

    }

    /** @test */
    function users_can_view_the_study_requestor_s_status_when_approved()
    {
        $this->withoutExceptionHandling();
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);

        $anotherUser = User::factory()->create();
        StudyRequest::create([
            'user_id' => $anotherUser->id,
            'post_id' => $post->id,
            'confirmed_at' => null,
            'status' => 1,
        ]);

        $this->actingAs($user, 'api')
            ->post("/api/study-groups/{$post->id}/request-response", [
                'user_id' => $anotherUser->id,
                'status' => 1,
            ])->assertStatus(200);

        $this->assertEquals(1, $post->requestSignCount());

        $response = $this->get("/api/posts/{$post->id}");
        $response->assertJson([
            'data' => [
                'type' => 'posts',
                'post_id' => $post->id,
                'attributes' => [
                    'requestSignCount' => 1,
                ]
            ]
        ]);
    }
}
