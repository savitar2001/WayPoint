<?php
namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Post;
use App\Models\PostComment;
use App\Services\Post\AddCommentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;

class ReplyToCommentControllerTest extends TestCase{
    use RefreshDatabase;
    protected $addCommentService;

    protected function setUp(): void {
        parent::setUp();

        $this->addCommentService = Mockery::mock(AddCommentService::class);
        $this->app->instance(AddCommentService::class, $this->addCommentService);
    }

    public function testReplyToCommentSuccess() {
        $commentId = 1;
        $userId = 1;
        $comment = 'This is a test comment';

        $this->addCommentService->shouldReceive('addReplyToComment')
            ->once()
            ->with($commentId, $userId, $comment)
            ->andReturn(['success' => true]);

        $this->addCommentService->shouldReceive('updateCommentReplyCount')
            ->once()
            ->with($commentId)
            ->andReturn(['success' => true]);

        $response = $this->postJson('/api/replyToComment', [
            'userId' => $userId,
            'commentId' => $commentId,
            'comment' => $comment,
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }
    public function testAddCommentToPostFails() {
        $commentId = 6000;
        $userId = 1;
        $comment = 'This is a test comment';

        $this->addCommentService->shouldReceive('addReplyToComment')
            ->once()
            ->with($commentId, $userId, $comment)
            ->andReturn(['success' => false, 'error' => '新增回覆其他用戶留言失敗']);
    
        $response = $this->postJson('/api/replyToComment', [
            'userId' => $userId,
            'commentId' => $commentId,
            'comment' => $comment,
        ]);

        $response->assertStatus(422)
            ->assertJson(['error' => '新增回覆其他用戶留言失敗']);
    }

    public function testUpdatePostCommentsCountFails() {
        $commentId = 1;
        $userId = 1;
        $comment = 'This is a test comment';

        $this->addCommentService->shouldReceive('addReplyToComment')
            ->once()
            ->with($commentId, $userId, $comment)
            ->andReturn(['success' => true]);

        $this->addCommentService->shouldReceive('updateCommentReplyCount')
            ->once()
            ->with($commentId)
            ->andReturn(['success' => false, 'error' => '更新留言回覆數失敗']);


        $response = $this->postJson('/api/replyToComment', [
            'userId' => $userId,
            'commentId' => $commentId,
            'comment' => $comment,
        ]);

        $response->assertStatus(422)
            ->assertJson(['error' => '更新留言回覆數失敗']);
    }
}