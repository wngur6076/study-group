<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Post;
use App\Models\User;
use App\Models\StudyRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StudyRequestCancelTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function study_requests_can_be_canceled()
    {
        $this->withoutExceptionHandling();
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);

        $anotherUser = User::factory()->create();
        $this->actingAs($anotherUser, 'api')
            ->post("/api/study-groups/{$post->id}/request", [
                'reason' => '테스트',
                'project' => 'test',
            ])->assertStatus(200);

        $response = $this->actingAs($user, 'api')
            ->delete("/api/study-groups/{$post->id}/request-response", [
                'user_id' => $anotherUser->id,
            ])->assertStatus(204);

        $studyRequest = StudyRequest::first();
        $this->assertNull($studyRequest);
        $response->assertNoContent();
    }

    /** @test */
    function requestor_can_cancel_a_study_request()
    {
        $this->withoutExceptionHandling();
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);

        $anotherUser = User::factory()->create();
        $this->actingAs($anotherUser, 'api')
            ->post("/api/study-groups/{$post->id}/request", [
                'reason' => '테스트',
                'project' => 'test',
            ])->assertStatus(200);

        $response = $this->actingAs($anotherUser, 'api')
            ->delete("/api/study-groups/{$post->id}/request-response", [
                'user_id' => $anotherUser->id,
            ])->assertStatus(204);

        $studyRequest = StudyRequest::first();
        $this->assertNull($studyRequest);
        $response->assertNoContent();
    }

    /** @test */
    function user_id_is_required()
    {
        $this->actingAs($user = User::factory()->create(), 'api');
        $post = Post::factory()->create(['user_id' => $user->id]);

        $response = $this->json('delete', "/api/study-groups/{$post->id}/request-response", [
                'user_id' => '',
            ])->assertStatus(422);

        $responseString = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('user_id', $responseString['errors']);
    }

    /** @test */
    function users_can_view_the_status_of_the_study_requestor_when_canceled()
    {
        $this->withoutExceptionHandling();
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);

        $anotherUser = User::factory()->create();
        StudyRequest::create([
            'user_id' => $anotherUser->id,
            'post_id' => $post->id,
            'confirmed_at' => now(),
            'status' => 1,
        ]);

        $this->actingAs($user, 'api')
            ->delete("/api/study-groups/{$post->id}/request-response", [
                'user_id' => $anotherUser->id,
                'status' => 1,
            ])->assertStatus(204);

        $this->assertEquals(0, $post->requestSignCount());

    }
}
