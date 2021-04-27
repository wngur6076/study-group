<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EditPostTest extends TestCase
{
    use RefreshDatabase;

    private function oldAttributes($overrides = [])
    {
        return array_merge([
            'title' => 'Testing Title',
            'body' => 'Testing Body',
            'zone' => '부산 남구 서면역',
            'deadline' => now()->addDay(),
            'max_number_people' => 5,
        ], $overrides);
    }

    private function validParams($overrides = [])
    {
        return array_merge([
            'title' => 'New Testing Title',
            'body' => 'New Testing Body',
            'zone' => 'New 부산 남구 서면역',
            'deadline' => now()->addDay(2),
            'max_number_people' => 3,
        ], $overrides);
    }

    /** @test */
    public function post_creator_can_edit_post_information()
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

        $response = $this->patch("/api/posts/{$post->id}", [
            'title' => 'Update Testing Title',
            'body' => 'Update Testing Body',
            'zone' => 'Update 부산 남구 서면역',
            'deadline' => now()->addDay(2),
            'max_number_people' => 3,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'type' => 'posts',
                    'post_id' => $post->id,
                    'attributes' => [
                        'title' => 'Update Testing Title',
                        'body' => 'Update Testing Body',
                        'zone' => 'Update 부산 남구 서면역',
                        'deadline' => now()->addDay(2)->format('Y-m-d H:i'),
                        'max_number_people' => 3,
                    ]
                ],
                'links' => [
                    'self' => url('/posts/'.$post->id),
                ]
            ]);

        tap($post->fresh(), function ($post) {
            $this->assertEquals('Update Testing Title', $post->title);
            $this->assertEquals('Update Testing Body', $post->body);
            $this->assertEquals('Update 부산 남구 서면역', $post->zone);
            $this->assertEquals(3, $post->max_number_people);
        });
    }

    /** @test */
    function post_authors_can_edit_the_post_s_tag_information()
    {
        $this->withoutExceptionHandling();
        $this->actingAs(User::factory()->create(), 'api');

        $response = $this->post('/api/posts', $this->oldAttributes([
            'tags' => ['안녕', '김주혁'],
        ]));
        $post = Post::first();

        $response = $this->patch("/api/posts/{$post->id}", $this->validParams([
            'tags' => ['바보야', '주혁'],
        ]));

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'attributes' => [
                        'tags' => [
                            'data' => [
                                [
                                    'data' => [
                                        'type' => 'tags',
                                        'attributes' => [
                                            'name' => '바보야',
                                        ]
                                    ],
                                ],
                                [
                                    'data' => [
                                        'type' => 'tags',
                                        'attributes' => [
                                            'name' => '주혁',
                                        ]
                                    ],
                                ]
                            ],
                            'tag_count' => 2,
                            'links' => [
                                'self' => url('/tags'),
                            ]
                        ]
                    ]
                ],
            ]);
    }

    /** @test */
    function title_is_required()
    {
        $this->actingAs($user = User::factory()->create(), 'api');
        $post = Post::factory()->create($this->oldAttributes([
            'user_id' => $user->id,
        ]));

        $response = $this->json('PATCH', "/api/posts/{$post->id}", $this->validParams([
            'title' => '',
        ]));

        $responseString = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('title', $responseString['errors']);
    }

    /** @test */
    function body_is_required()
    {
        $this->actingAs($user = User::factory()->create(), 'api');
        $post = Post::factory()->create($this->oldAttributes([
            'user_id' => $user->id,
        ]));

        $response = $this->json('PATCH', "/api/posts/{$post->id}", $this->validParams([
            'body' => '',
        ]));

        $responseString = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('body', $responseString['errors']);
    }

    /** @test */
    function zone_is_required()
    {
        $this->actingAs($user = User::factory()->create(), 'api');
        $post = Post::factory()->create($this->oldAttributes([
            'user_id' => $user->id,
        ]));

        $response = $this->json('PATCH', "/api/posts/{$post->id}", $this->validParams([
            'zone' => '',
        ]));

        $responseString = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('zone', $responseString['errors']);
    }

    /** @test */
    function deadline_is_required()
    {
        $this->actingAs($user = User::factory()->create(), 'api');
        $post = Post::factory()->create($this->oldAttributes([
            'user_id' => $user->id,
        ]));

        $response = $this->json('PATCH', "/api/posts/{$post->id}", $this->validParams([
            'deadline' => '',
        ]));

        $responseString = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('deadline', $responseString['errors']);
    }

    /** @test */
    function max_number_people_is_required()
    {
        $this->actingAs($user = User::factory()->create(), 'api');
        $post = Post::factory()->create($this->oldAttributes([
            'user_id' => $user->id,
        ]));

        $response = $this->json('PATCH', "/api/posts/{$post->id}", $this->validParams([
            'max_number_people' => '',
        ]));

        $responseString = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('max_number_people', $responseString['errors']);
    }

    /** @test */
    function max_number_people_must_be_numeric()
    {
        $this->actingAs($user = User::factory()->create(), 'api');
        $post = Post::factory()->create($this->oldAttributes([
            'user_id' => $user->id,
        ]));

        $response = $this->json('PATCH', "/api/posts/{$post->id}", $this->validParams([
            'max_number_people' => 'not a max number people',
        ]));

        $responseString = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('max_number_people', $responseString['errors']);
    }

    /** @test */
    function tags_is_array()
    {
        $this->actingAs($user = User::factory()->create(), 'api');
        $post = Post::factory()->create($this->oldAttributes([
            'user_id' => $user->id,
        ]));

        $response = $this->json('PATCH', "/api/posts/{$post->id}", $this->validParams([
            'tags' => '테스트',
        ]));

        $responseString = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('tags', $responseString['errors']);
    }
}
