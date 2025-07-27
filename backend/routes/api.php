<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Post\CreatePostController;
use App\Http\Controllers\Post\DeletePostController;
use App\Http\Controllers\Post\CommentOnPostController;
use App\Http\Controllers\Post\DeletePostCommentController;
use App\Http\Controllers\Post\ReplyToCommentController;
use App\Http\Controllers\Post\DeleteReplyCommentController;
use App\Http\Controllers\Post\GetPostController;
use App\Http\Controllers\Post\LikePostController;
use App\Http\Controllers\Post\GetPostLikeController;
use App\Http\Controllers\Post\GetPostCommentController;
use App\Http\Controllers\User\AddSubscriberController;
use App\Http\Controllers\User\CreateAvatarController;
use App\Http\Controllers\User\RemoveSubscriberController;
use App\Http\Controllers\User\GetFollowerController;
use App\Http\Controllers\User\GetSubscriberController;
use App\Http\Controllers\User\GetUserProfileController;
use App\Http\Controllers\Notification\NotificationController;
use App\Events\NewMessage;

Route::get('/health', function () {
    return response()->json(['status' => 'ok']);
});
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/register', [RegisterController::class, 'register']);
Route::post('/verify', [RegisterController::class, 'verify']);
Route::post('/createPost', [CreatePostController::class, 'createPost']);
Route::delete("/deletePost/{userId}/{postId}", [DeletePostController::class, 'deletePost']);
Route::post('/commentOnPost', [CommentOnPostController::class, 'commentOnPost']);
Route::delete("/deletePostComment/{userId}/{postId}/{commentId}", [DeletePostCommentController::class, 'deletePostComment']);
Route::post('/replyToComment', [ReplyToCommentController::class, 'replyToComment']);
Route::delete('/deleteReplyComment/{userId}/{commentId}/{replyId}', [DeleteReplyCommentController::class, 'deleteReplyComment']);
Route::get('/getPost/{userId}/{postId}/{tag}', [GetPostController::class, 'getPost']);
Route::post('/likePost', [LikePostController::class, 'likePost']);
Route::get('/getPostLike/{postId}', [GetPostLikeController::class, 'getPostLike']);
Route::get('/getPostComment/{postId}', [GetPostCommentController::class, 'getPostComment']);
Route::get('/getCommentReply/{commentId}', [GetPostCommentController::class, 'getCommentReply']);
Route::post('/addSubscriber', [AddSubscriberController::class, 'addSubscriber']);
Route::post('/createAvatar', [CreateAvatarController::class, 'createAvatar']);
Route::delete('/removeSubscriber/{followerId}/{subscriberId}', [RemoveSubscriberController::class, 'removeSubscriber']);
Route::get('/getFollower/{userId}', [GetFollowerController::class, 'getFollower']);
Route::get('/getSubscriber/{userId}', [GetSubscriberController::class, 'getSubscriber']);
Route::get('/getUserInformation/{userId}', [GetUserProfileController::class, 'getUserInformation']);
Route::get('/searchByName/{name}', [GetUserProfileController::class, 'searchByName']);
Route::get('/getUnreadNotifications/{notifiableId}/{type}', [NotificationController::class, 'getUnreadNotifications']);
Route::post('/markNotificationAsRead', [NotificationController::class, 'markNotificationAsRead']);
Route::post('/markAllNotificationsAsRead', [NotificationController::class, 'markAllNotificationsAsRead']);

