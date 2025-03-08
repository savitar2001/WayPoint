<?php
use Tests\TestCase;
use App\Services\Post\CreatePostService;
use App\Models\User;
use App\Models\Post;

class CreatePostServiceTest extends TestCase {
    private $userMock;
    private $postMock;
    private $createPostService;

    protected function setUp(): void {
        $this->userMock = $this->createMock(User::class);
        $this->postMock = $this->createMock(Post::class);

        $this->createPostService = new CreatePostService($this->userMock, $this->postMock);
    }

    public function testChangePostAmountSuccess() {
        $this->userMock->method('changeUserPostAmount')->with(1)->willReturn(1); 

        $response = $this->createPostService->changePostAmount(1);

        $this->assertTrue($response['success']);
        $this->assertEmpty($response['error']);
    }

    public function testChangePostAmountFailure() {
        $this->userMock->method('changeUserPostAmount')->with(1)->willReturn(0);

        $response = $this->createPostService->changePostAmount(1);

        $this->assertFalse($response['success']);
        $this->assertEquals('貼文數更新失敗', $response['error']);
    }

    public function testCreatePostToDatabaseSuccess() {
        $this->postMock->method('createPost')->with(1, 'Authur', 'Post Content', 'Tag', 'imageUrl')->willReturn(true); 

        $response = $this->createPostService->createPostToDatabase(1, 'Authur', 'Post Content', 'Tag', 'imageUrl');

        $this->assertTrue($response['success']);
        $this->assertEmpty($response['error']);
    }

    public function testCreatePostToDatabaseFailure() {
        $this->postMock->method('createPost')->with(1, 'Authur', 'Post Content', 'Tag', 'imageUrl')->willReturn(false);  

        $response = $this->createPostService->createPostToDatabase(1, 'Authur', 'Post Content', 'Tag', 'imageUrl');
        
        $this->assertFalse($response['success']);
        $this->assertEquals('新增貼文至資料庫失敗', $response['error']);
    }
}