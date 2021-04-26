<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Post;
use App\Models\User;
use App\Models\StudyRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ViewStudyRequestTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_view_the_list_of_study_request()
    {
        $this->withoutExceptionHandling();

        $this->actingAs($user = User::factory()->create(), 'api');
        $post = Post::factory()->create(['user_id' => $user->id]);

        $studyRequest1 = StudyRequest::create([
            'user_id' => User::factory()->create(['email' => 'test1@test.com'])->id,
            'post_id' => $post->id,
            'confirmed_at' => now(),
            'status' => 1,
            'reason' => 'TEST 1',
            'project' => 'TEST 1',
        ]);
        $studyRequest2 = StudyRequest::create([
            'user_id' => User::factory()->create(['email' => 'test2@test.com'])->id,
            'post_id' => $post->id,
            'confirmed_at' => null,
            'status' => null,
            'reason' => 'TEST 2',
            'project' => 'TEST 2',
        ]);

        $response = $this->get("/api/study-groups/{$post->id}/request")
            ->assertStatus(200);

        $response->assertJson([
            'data' => [
                [
                    'data' => [
                        'type' => 'study-request',
                        'study_request_id' => $studyRequest1->id,
                        'attributes' => [
                            'study_requested_by' => [
                                'data' => [
                                    'attributes' => [
                                        'email' => 'test1@test.com',
                                    ]
                                ]
                            ],
                            'status' => 1,
                            'reason' => $studyRequest1->reason,
                            'project' => $studyRequest1->project,
                        ]
                    ],
                    'links' => [
                        'self' => url('/users/'.$studyRequest1->user_id),
                    ],
                ],
                [
                    'data' => [
                        'type' => 'study-request',
                        'study_request_id' => $studyRequest2->id,
                        'attributes' => [
                            'study_requested_by' => [
                                'data' => [
                                    'attributes' => [
                                        'email' => 'test2@test.com',
                                    ]
                                ]
                            ],
                            'status' => null,
                            'reason' => $studyRequest2->reason,
                            'project' => $studyRequest2->project,
                        ]
                    ],
                    'links' => [
                        'self' => url('/users/'.$studyRequest2->user_id),
                    ],
                ],
            ]
        ]);
    }

    /** @test */
    function users_can_view_the_study_requestor_s_status_when_requested()
    {
        $this->actingAs($user = User::factory()->create(), 'api');
        $post = Post::factory()->create();

        StudyRequest::create([
            'user_id' => User::factory()->create()->id,
            'post_id' => $post->id,
            'confirmed_at' => now(),
            'status' => 1,
            'reason' => 'TEST 1',
            'project' => 'TEST 1',
        ]);
        $this->get("/api/study-groups/{$post->id}/request")
            ->assertStatus(403);
    }
}
