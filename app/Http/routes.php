<?php

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
    Route::resource('/posts', 'Post');
    Route::resource('/users', 'User');
    Route::resource('/comments', 'Comment');
    Route::group(['prefix' => 'posts'], function () {
        Route::group(['prefix' => '{postID}'], function ($postID) {
            Route::resource('/comments', 'Post\Comment');
        });
    });
});
