<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AddPostTest extends TestCase
{
    use RefreshDatabase;

    private function validParams($overrides = [])
    {
        return array_merge([
            'title' => 'Testing Title',
            'body' => 'Testing Body',
            'zone' => '부산 남구 서면역',
            'deadline' => now()->addDay(),
            'max_number_people' => 5,
        ], $overrides);
    }

    private function assertValidationError($response, $field)
    {
        $response->assertStatus(422)->assertJsonStructure(['errors' => [$field]]);
    }

    /** @test */
    function adding_a_valid_post()
    {
        $this->withoutExceptionHandling();
        $this->actingAs($user = User::factory()->create(), 'api');

        $response = $this->post('/api/posts', [
            'title' => 'Testing Title',
            'body' => 'Testing Body',
            'zone' => '부산 남구 서면역',
            'deadline' => now()->addDay(),
            'max_number_people' => 5,
        ]);

        $post = Post::first();

        $this->assertCount(1, Post::all());
        $this->assertEquals($user->id, $post->user_id);
        $this->assertEquals('Testing Title', $post->title);
        $this->assertEquals('Testing Body', $post->body);
        $this->assertEquals('부산 남구 서면역', $post->zone);
        $this->assertEquals(now()->addDay()->startOfSecond(), $post->deadline);
        $this->assertEquals(5, $post->max_number_people);

        $response->assertStatus(201)
            ->assertJson([
                'data' => [
                    'type' => 'posts',
                    'post_id' => $post->id,
                    'attributes' => [
                        'posted_by' => [
                            'data' => [
                                'attributes' => [
                                    'name' => $user->name,
                                ]
                            ]
                        ],
                        'title' => 'Testing Title',
                        'body' => 'Testing Body',
                        'zone' => '부산 남구 서면역',
                        'deadline' => $post->deadline->format('Y-m-d H:i'),
                        'max_number_people' => 5,
                    ]
                ],
                'links' => [
                    'self' => url('/posts/'.$post->id),
                ]
            ]);
    }

    /** @test */
    function title_is_required()
    {
        $response = $this->actingAs(User::factory()->create(), 'api')
            ->json('post', '/api/posts', $this->validParams([
                'title' => '',
            ]));

        $responseString = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('title', $responseString['errors']);
    }

    /** @test */
    function body_is_required()
    {
        $response = $this->actingAs(User::factory()->create(), 'api')
            ->json('post', '/api/posts', $this->validParams([
                'body' => '',
            ]));

        $responseString = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('body', $responseString['errors']);
    }

    /** @test */
    function zone_is_required()
    {
        $response = $this->actingAs(User::factory()->create(), 'api')
            ->json('post', '/api/posts', $this->validParams([
                'zone' => '',
            ]));

        $responseString = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('zone', $responseString['errors']);
    }

    /** @test */
    function deadline_is_required()
    {
        $response = $this->actingAs(User::factory()->create(), 'api')
            ->json('post', '/api/posts', $this->validParams([
                'deadline' => '',
            ]));

        $responseString = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('deadline', $responseString['errors']);
    }

    /** @test */
    function max_number_people_is_required()
    {
        $response = $this->actingAs(User::factory()->create(), 'api')
            ->json('post', '/api/posts', $this->validParams([
                'max_number_people' => '',
            ]));

        $responseString = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('max_number_people', $responseString['errors']);
    }

    /** @test */
    function max_number_people_must_be_numeric()
    {
        $response = $this->actingAs(User::factory()->create(), 'api')
            ->json('post', '/api/posts', $this->validParams([
                'max_number_people' => 'not a max number people',
            ]));

        $responseString = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('max_number_people', $responseString['errors']);
    }
}
