<?php

namespace Tests\Feature;

use App\Services\Post\DeleteCommentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Mockery;

class DeletePostCommentControllerTest extends TestCase {
    use RefreshDatabase;
    protected $deleteCommentService;

    protected function setUp(): void{
        parent::setUp();

        $this->deleteCommentService = Mockery::mock(DeleteCommentService::class);
        $this->app->instance(DeleteCommentService::class, $this->deleteCommentService);
    }

    public function testDeleteCommentToPostSuccess() {
        $userId = 1;
        $postId = 1;
        $commentId = 1;

        $this->deleteCommentService->shouldReceive('deleteCommentToPost')
            ->once()
            ->with($userId, $commentId)
            ->andReturn(['success' => true]);

        $this->deleteCommentService->shouldReceive('updatePostCommentsCount')
            ->once()
            ->with($postId)
            ->andReturn(['success' => true]);

        $response = $this->deleteJson("/api/deletePostComment/1/1/1");

        $response->assertStatus(204);
    }

    public function testDeleteCommentToPostFailure(){
        $userId = 1;
        $postId = 1;
        $commentId = 1;

        $this->deleteCommentService->shouldReceive('deleteCommentToPost')
            ->once()
            ->with($userId, $commentId)
            ->andReturn(['success' => false, 'error' => '刪除貼文留言失敗']);

        $response = $this->deleteJson("/api/deletePostComment/1/1/1");
        $response->assertStatus(422)
        ->assertJson(['success' => false, 'error' => '刪除貼文留言失敗']);
    }

    public function testUpdatePostCommentsCountFail() {
        $userId = 1;
        $postId = 1;
        $commentId = 1;

        $this->deleteCommentService->shouldReceive('deleteCommentToPost')
            ->once()
            ->with($userId, $commentId)
            ->andReturn(['success' => true]);

        $this->deleteCommentService->shouldReceive('updatePostCommentsCount')
            ->once()
            ->with($postId)
            ->andReturn(['success' => false, 'error' => '更新貼文評論數失敗']);

        $response = $this->deleteJson("/api/deletePostComment/1/1/1");
        $response->assertStatus(422)
        ->assertJson(['success' => false, 'error' => '更新貼文評論數失敗']);
    }
}