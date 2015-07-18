<?php

require_once 'constants.php';
/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::group(['prefix' => 'api/1.0'], function () {
    resource('/posts', 'Post\Post', ['only' => ['index', 'show', 'store']]);
    resource('/users', 'User\User', ['only' => ['index', 'show']]);
    Route::group(['prefix' => 'users/{userID}'], function () {
        resource('/posts', 'User\Post', ['only' => ['index', 'show']]);
    });
    resource('/comments', 'Comment', ['only' => ['index', 'show']]);
    Route::group(['prefix' => 'posts/{postID}'], function () {
        resource('/comments', 'Post\Comment', ['only' => ['index', 'show']]);
    });
});