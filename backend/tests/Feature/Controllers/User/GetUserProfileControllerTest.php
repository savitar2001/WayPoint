<?php

namespace Tests\Feature;

use Mockery;
use Tests\TestCase;
use App\Services\User\UserProfileService;

class GetUserProfileControllerTest extends TestCase{
    private $userProfileService;

    protected function setUp(): void{
        parent::setUp();
        $this->userProfileService = Mockery::mock(UserProfileService::class);
        $this->app->instance(UserProfileService::class, $this->userProfileService);
    }

    public function testGetUserInformationSuccess(){
        $userId = 1;
        $userInformation = [
            'success' => true,
            'data' => [
                'id' => $userId,
                'name' => 'John Doe',
                'avatarUrl' => 'test.jpg',
                'postAmount' => 10,
                'subscriberCount' => 200,
                'followerCount' => 150
            ]
        ];
        $presignedUrl = [
            'success' => true,
            'data' => ['url' => 'https://example.com/avatar/test.jpg']
        ];

        $this->userProfileService
            ->shouldReceive('getUserInformation')
            ->with($userId)
            ->andReturn($userInformation);

        $this->userProfileService
            ->shouldReceive('generatePresignedUrl')
            ->with('test.jpg')
            ->andReturn($presignedUrl);

        $response = $this->getJson('/api/getUserInfromation?userId=' . $userId);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $userId,
                    'name' => 'John Doe',
                    'avatarUrl' => 'https://example.com/avatar/test.jpg',
                    'postAmount' => 10,
                    'subscriberCount' => 200,
                    'followerCount' => 150
                ]
            ]);
    }

    public function testGetUserInformationFailure(){
        $userId = 999;
        $userInformation = [
            'success' => false,
            'error' => '無法取得使用者資訊'
        ];

        $this->userProfileService
            ->shouldReceive('getUserInformation')
            ->with($userId)
            ->andReturn($userInformation);

        $response = $this->getJson('/api/getUserInfromation?userId=' . $userId);

        $response->assertStatus(422)
            ->assertJson($userInformation);
    }

    public function testSearchByNameSuccess()
    {
        $name = 'John Doe';
        $userByName = [
            'success' => true,
            'data' => [
                'id' => 1,
                'name' => $name,
                'avatarUrl' => 'avatar/test.jpg'
            ]
        ];
        $presignedUrl = [
            'success' => true,
            'data' => ['url' => 'https://example.com/avatar/test.jpg']
        ];

        $this->userProfileService
            ->shouldReceive('getUserByName')
            ->with($name)
            ->andReturn($userByName);

        $this->userProfileService
            ->shouldReceive('generatePresignedUrl')
            ->with('avatar/test.jpg')
            ->andReturn($presignedUrl);

        $response = $this->getJson('/api/searchByName?name=' . $name);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => 1,
                    'name' => $name,
                    'avatarUrl' => 'https://example.com/avatar/test.jpg'
                ]
            ]);
    }

    public function testSearchByNameFailure()
    {
        $name = 'Nonexistent User';
        $userByName = [
            'success' => false,
            'error' => '無法取得使用者資訊'
        ];

        $this->userProfileService
            ->shouldReceive('getUserByName')
            ->with($name)
            ->andReturn($userByName);

        $response = $this->getJson('/api/searchByName?name=' . $name);

        $response->assertStatus(422)
            ->assertJson($userByName);
    }

    public function testGetUserInformationMissingParameter()
    {
        $response = $this->getJson('/api/getUserInfromation');

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error' => '參數不足'
            ]);
    }

    public function testSearchByNameMissingParameter()
    {
        $response = $this->getJson('/api/searchByName');

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error' => '參數不足'
            ]);
    }
}