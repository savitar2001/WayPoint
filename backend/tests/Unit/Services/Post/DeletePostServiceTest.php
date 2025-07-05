<?php
use Tests\TestCase;
use App\Services\Post\DeletePostService;
use App\Models\User;
use App\Models\Post;
use App\Services\Image\S3StorageService;  

class DeletePostServiceTest extends TestCase {
    private $userMock;
    private $postMock;
    private $s3StorageServiceMock;
    private $deletePostService;

    protected function setUp(): void {
        $this->userMock = $this->createMock(User::class);
        $this->postMock = $this->createMock(Post::class);
        $this->s3StorageServiceMock = $this->createMock(S3StorageService::class);

        $this->deletePostService = new DeletePostService($this->userMock, $this->postMock,$this->s3StorageServiceMock);
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

    public function testDeleteImageSuccess() {
        $postObject = new \stdClass();
        $postObject->image_url = 'fileName';
        $this->s3StorageServiceMock->method('deleteImage')->with('posts', 'fileName')->willReturn(['success' => true]);
        $this->postMock->method('searchPost')->willReturn([$postObject]);

        $response = $this->deletePostService->deleteImage(1);

        $this->assertTrue($response['success']);
    }

    public function testDeleteImageFail() {
        $postObject = new \stdClass();
        $postObject->image_url = 'fileName';
        $this->s3StorageServiceMock->method('deleteImage')->with('posts', 'fileName')->willReturn(['success' => false, 'error' => '刪除圖片失敗']);
        $this->postMock->method('searchPost')->willReturn([$postObject]);
    
        $response = $this->deletePostService->deleteImage(1);
    
        $this->assertFalse($response['success']);
        $this->assertEquals('刪除圖片失敗', $response['error']);
    }
}