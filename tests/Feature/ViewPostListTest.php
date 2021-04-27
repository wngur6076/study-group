<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Tests\TestCase;

class ViewPostListTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function user_can_view_study_list()
    {
        $this->withoutExceptionHandling();
        $this->actingAs($user = User::factory()->create(), 'api');
        $posts = Post::factory(2)->create(['user_id' => $user->id]);

        $response = $this->get('/api/posts')
            ->assertStatus(200);

        $response->assertJson([
            'data' => [
                [
                    'data' => [
                        'type' => 'posts',
                        'post_id' => $posts->last()->id,
                        'attributes' => [
                            'posted_by' => [
                                'data' => [
                                    'attributes' => [
                                        'email' => $user->email,
                                    ]
                                ]
                            ],
                            'title' => $posts->last()->title,
                            'body' => $posts->last()->body,
                            'zone' => $posts->last()->zone,
                            'deadline' => $posts->last()->deadline->format('Y-m-d H:i'),
                            'max_number_people' => $posts->last()->max_number_people,
                            'requestSignCount' => 0,
                        ]
                    ],
                    'links' => [
                        'self' => url('/posts/'.$posts->last()->id),
                    ]
                ],
                [
                    'data' => [
                        'type' => 'posts',
                        'post_id' => $posts->first()->id,
                        'attributes' => [
                            'posted_by' => [
                                'data' => [
                                    'attributes' => [
                                        'email' => $user->email,
                                    ]
                                ]
                            ],
                            'title' => $posts->first()->title,
                            'body' => $posts->first()->body,
                            'zone' => $posts->first()->zone,
                            'deadline' => $posts->first()->deadline->format('Y-m-d H:i'),
                            'max_number_people' => $posts->first()->max_number_people,
                            'requestSignCount' => 0,
                        ]
                    ],
                    'links' => [
                        'self' => url('/posts/'.$posts->first()->id),
                    ]
                ]
            ],
            'links' => [
                'self' => url('/posts/'),
            ]
        ]);
    }

    /** @test */
    function user_can_see_tags_in_study_list()
    {
        $this->withoutExceptionHandling();
        $this->actingAs($user = User::factory()->create(), 'api');
        $posts = Post::factory(2)->create(['user_id' => $user->id]);
        Tag::factory(2)->create(['post_id' => $posts->first()->id]);
        Tag::factory()->create(['post_id' => $posts->last()->id]);

        $response = $this->get('/api/posts')
            ->assertStatus(200);

        $response->assertJson([
            'data' => [
                [
                    'data' => [
                        'type' => 'posts',
                        'post_id' => $posts->last()->id,
                        'attributes' => [
                            'tags' => [
                                'data' => [
                                    [
                                        'data' => [
                                            'type' => 'tags',
                                            'tag_id' => $posts->last()->tags->first()->id,
                                            'attributes' => [
                                                'name' => $posts->last()->tags->first()->name,
                                            ]
                                        ]
                                    ],
                                ],
                                'tag_count' => 1,
                            ]
                        ],
                    ],
                    'links' => [
                        'self' => url('/posts/'.$posts->last()->id),
                    ]
                ],
                [
                    'data' => [
                        'type' => 'posts',
                        'post_id' => $posts->first()->id,
                        'attributes' => [
                            'tags' => [
                                'data' => [
                                    [
                                        'data' => [
                                            'type' => 'tags',
                                            'tag_id' => $posts->first()->tags->first()->id,
                                            'attributes' => [
                                                'name' => $posts->first()->tags->first()->name,
                                            ]
                                        ]
                                    ],
                                    [
                                        'data' => [
                                            'type' => 'tags',
                                            'tag_id' => $posts->first()->tags->last()->id,
                                            'attributes' => [
                                                'name' => $posts->first()->tags->last()->name,
                                            ]
                                        ]
                                    ]
                                ],
                                'tag_count' => 2,
                            ]
                        ]
                    ],
                    'links' => [
                        'self' => url('/posts/'.$posts->first()->id),
                    ]
                ]
            ],
            'links' => [
                'self' => url('/posts/'),
            ]
        ]);
    }

    /** @test */
    function pagination_for_posts_works()
    {
        for ($i = 0; $i < 10; $i++) {
            Post::factory()->create(['title' => 'Post '.$i]);
        }

        for ($i = 10; $i < 20; $i++) {
            Post::factory()->create(['title' => 'Post '.$i]);
        }

        $response = $this->actingAs(User::factory()->create(), 'api')->json('GET', '/api/posts');

        $response->assertJsonFragment(['title' => 'Post 10']);
        $response->assertJsonFragment(['title' => 'Post 19']);

        $response = $this->actingAs(User::factory()->create(), 'api')->json('GET', '/api/posts?page=2');

        $response->assertJsonFragment(['title' => 'Post 0']);
        $response->assertJsonFragment(['title' => 'Post 9']);
    }
}
