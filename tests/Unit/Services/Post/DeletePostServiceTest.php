<?php
use Tests\TestCase;
use App\Services\Post\DeletePostService;
use App\Models\User;
use App\Models\Post;

class DeletePostServiceTest extends TestCase {
    private $userMock;
    private $postMock;
    private $deletePostService;

    protected function setUp(): void {
        $this->userMock = $this->createMock(User::class);
        $this->postMock = $this->createMock(Post::class);

        $this->deletePostService = new DeletePostService($this->userMock, $this->postMock);
    }

    public function testChangePostAmountSuccess() {
        $this->userMock->method('changeUserPostAmount')->with(1)->willReturn(1); 

        $response = $this->deletePostService->changePostAmount(1);

        $this->assertTrue($response['success']);
        $this->assertEmpty($response['error']);
    }

    public function testChangePostAmountFailure() {
        $this->userMock->method('changeUserPostAmount')->with(1)->willReturn(0);

        $response = $this->deletePostService->changePostAmount(1);

        $this->assertFalse($response['success']);
        $this->assertEquals('貼文數更新失敗', $response['error']);
    }

    public function testDeletePostToDatabaseSuccess() {
        $this->postMock->method('deletePost')->with(1, 2)->willReturn(true); 

        $response = $this->deletePostService->deletePostToDatabase(1, 2);

        $this->assertTrue($response['success']);
        $this->assertEmpty($response['error']);
    }

    public function testCreatePostToDatabaseFailure() {
        $this->postMock->method('deletePost')->with(1, 2)->willReturn(false);  

        $response = $this->deletePostService->deletePostToDatabase(1, 2);
        
        $this->assertFalse($response['success']);
        $this->assertEquals('資料庫貼文刪除失敗', $response['error']);
    }
}