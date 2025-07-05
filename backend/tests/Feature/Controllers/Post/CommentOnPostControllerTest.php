<?php
namespace Tests\Feature\Controllers\Post;

use Tests\TestCase;
use App\Models\Post;
use App\Models\PostComment;
use App\Services\Post\AddCommentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;

class CommentOnPostControllerTest extends TestCase{
    use RefreshDatabase;
    protected $addCommentService;

    protected function setUp(): void {
        parent::setUp();

        $this->addCommentService = Mockery::mock(AddCommentService::class);
        $this->app->instance(AddCommentService::class, $this->addCommentService);
    }

    public function testCommentOnPostSuccess() {
        $postId = 1;
        $userId = 1;
        $comment = 'This is a test comment';

        $this->addCommentService->shouldReceive('addCommentToPost')
            ->once()
            ->with($postId, $userId, $comment)
            ->andReturn(['success' => true]);

        $response = $this->postJson('/api/commentOnPost', [
            'userId' => $userId,
            'postId' => $postId,
            'comment' => $comment,
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }
    public function testAddCommentToPostFails() {
        $postId = 6000;
        $userId = 1;
        $comment = 'This is a test comment';

        $this->addCommentService->shouldReceive('addCommentToPost')
            ->once()
            ->with($postId, $userId, $comment)
            ->andReturn(['success' => false, 'error' => '新增貼文留言失敗']);
    
        $response = $this->postJson('/api/commentOnPost', [
            'userId' => $userId,
            'postId' => $postId,
            'comment' => $comment,
        ]);

        $response->assertStatus(422)
            ->assertJson(['error' => '新增貼文留言失敗']);
    }
}