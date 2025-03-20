<?php
use Tests\TestCase;
use App\Services\Post\CreatePostService;
use App\Models\User;
use App\Models\Post;
use App\Services\Image\S3StorageService;

class CreatePostServiceTest extends TestCase {
    private $userMock;
    private $postMock;
    private $s3StorageServiceMock;
    private $createPostService;

    protected function setUp(): void {
        $this->userMock = $this->createMock(User::class);
        $this->postMock = $this->createMock(Post::class);
        $this->s3StorageServiceMock = $this->createMock(S3StorageService::class);

        $this->createPostService = new CreatePostService($this->userMock, $this->postMock,$this->s3StorageServiceMock);
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

    public function testUploadBase64ImageSuccess() {
        $this->s3StorageServiceMock->method('uploadBase64Image')->with('base64Image', 'post/')->willReturn(['success' => true, 'data' => ['url' => 'http://example.com/image.jpg']]);

        $response = $this->createPostService->uploadBase64Image('base64Image');

        $this->assertTrue($response['success']);
        $this->assertEquals('http://example.com/image.jpg', $response['data']['url']);
    }

    public function testUploadBase64ImageFail() {
        $this->s3StorageServiceMock->method('uploadBase64Image')->with('base64Image', 'post/')->willReturn(['success' => false, 'error' => '上傳圖片失敗']);

        $response = $this->createPostService->uploadBase64Image('base64Image');

        $this->assertFalse($response['success']);
        $this->assertEquals('上傳圖片失敗', $response['error']);
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