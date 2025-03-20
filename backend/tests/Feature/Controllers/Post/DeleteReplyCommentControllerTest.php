<?php

namespace Tests\Feature;

use App\Services\Post\DeleteCommentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Mockery;

class DeleteReplyCommentControllerTest extends TestCase {
    use RefreshDatabase;
    protected $deleteCommentService;

    protected function setUp(): void {
        parent::setUp();

        $this->deleteCommentService = Mockery::mock(DeleteCommentService::class);
        $this->app->instance(DeleteCommentService::class, $this->deleteCommentService);
    }

    public function testDeleteReplyCommentSuccess() {
        $userId = 1;
        $commentId = 1;
        $replyId = 1;

        $this->deleteCommentService->shouldReceive('deleteReplyToComment')
            ->once()
            ->with($userId, $replyId)
            ->andReturn(['success' => true]);

        $this->deleteCommentService->shouldReceive('updateCommentReplyCount')
            ->once()
            ->with($commentId)
            ->andReturn(['success' => true]);

        $response = $this->deleteJson("/api/deleteReplyComment/1/1/1");

        $response->assertStatus(204);
    }

    public function testDeleteReplyCommentFailure() {
        $userId = 1;
        $commentId = 1;
        $replyId = 1;

        $this->deleteCommentService->shouldReceive('deleteReplyToComment')
            ->once()
            ->with($userId, $replyId)
            ->andReturn(['success' => false, 'error' => '刪除回覆其他用戶留言失敗']);

        $response = $this->deleteJson("/api/deleteReplyComment/1/1/1");
        $response->assertStatus(422)
            ->assertJson(['success' => false, 'error' => '刪除回覆其他用戶留言失敗']);
    }

    public function testUpdateCommentReplyCountFail() {
        $userId = 1;
        $commentId = 1;
        $replyId = 1;

        $this->deleteCommentService->shouldReceive('deleteReplyToComment')
            ->once()
            ->with($userId, $replyId)
            ->andReturn(['success' => true]);

        $this->deleteCommentService->shouldReceive('updateCommentReplyCount')
            ->once()
            ->with($commentId)
            ->andReturn(['success' => false, 'error' => '更新留言回覆數失敗']);

        $response = $this->deleteJson("/api/deleteReplyComment/1/1/1");
        $response->assertStatus(422)
            ->assertJson(['success' => false, 'error' => '更新留言回覆數失敗']);
    }
}