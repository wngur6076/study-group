<?php

namespace Tests\Feature;

use App\Models\Post;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ViewPostTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_view_detailed_posts()
    {
        $this->withoutExceptionHandling();
        $this->actingAs($user = User::factory()->create(), 'api');
        $post = Post::factory()->create([
            'user_id' => $user->id,
            'title' => 'Testing Title',
            'body' => 'Testing Body',
            'zone' => '부산 남구 서면역',
            'deadline' => now()->addDay(),
            'max_number_people' => 5,
        ]);

        $response = $this->get("/api/posts/{$post->id}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'type' => 'posts',
                    'post_id' => $post->id,
                    'attributes' => [
                        'posted_by' => [
                            'data' => [
                                'attributes' => [
                                    'email' => $user->email,
                                    'name' => $user->name,
                                ]
                            ]
                        ],
                        'title' => 'Testing Title',
                        'body' => 'Testing Body',
                        'zone' => '부산 남구 서면역',
                        'deadline' => $post->deadline->format('Y-m-d H:i'),
                        'max_number_people' => 5,
                        'created_at' => $post->created_at->format('Y-m-d'),
                    ]
                ],
                'links' => [
                    'self' => url('/posts/'.$post->id),
                ]
            ]);
    }
}
