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
    resource('/posts', 'Post\Post');
    resource('/users', 'User\User');
    Route::group(['prefix' => 'users/{userID}'], function () {
        resource('/posts', 'User\Post', ['only' => ['index', 'show', 'store']]);
    });
    resource('/comments', 'Comment', ['only' => ['index', 'show']]);
    Route::group(['prefix' => 'posts/{postID}'], function () {
        resource('/comments', 'Post\Comment', ['only' => ['index', 'show', 'store']]);
    });
});

get('/', function () {
    return [
        'accounts' =>
            [
                ['email'    => 'Emily@globalpassportproject.com',
                 'password' => 'Pinterest2015'],
                ['email'    => 'Madison@globalpassportproject.com',
                 'password' => 'Pinterest2015'],
                ['email'    => 'Emma@globalpassportproject.com',
                 'password' => 'Pinterest2015'],
                ['email'    => 'Abigail@globalpassportproject.com',
                 'password' => 'Pinterest2015'],
                ['email'    => 'Olivia@globalpassportproject.com',
                 'password' => 'Pinterest2015'],
                ['email'    => 'Isabella@globalpassportproject.com',
                 'password' => 'Pinterest2015'],
                ['email'    => 'Hannah@globalpassportproject.com',
                 'password' => 'Pinterest2015'],
                ['email'    => 'Samantha@globalpassportproject.com',
                 'password' => 'Pinterest2015'],
                ['email'    => 'Ava@globalpassportproject.com',
                 'password' => 'Pinterest2015'],
                ['email'    => 'Ashley@globalpassportproject.com',
                 'password' => 'Pinterest2015'],
                ['email'    => 'Sophia@globalpassportproject.com',
                 'password' => 'Pinterest2015'],
                ['email'    => 'Elizabeth@globalpassportproject.com',
                 'password' => 'Pinterest2015'],
                ['email'    => 'Alexis@globalpassportproject.com',
                 'password' => 'Pinterest2015'],
                ['email'    => 'Grace@globalpassportproject.com',
                 'password' => 'Pinterest2015'],
            ]
    ];
});