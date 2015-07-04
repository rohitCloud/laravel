<?php

/**
 * Created by Rohit Arora
 */

$factory('App\Models\User', [
    'name'     => $faker->name,
    'email'    => $faker->email,
    'password' => $faker->word
]);

$factory('App\Models\Post', [
    'user_id' => 'factory:App\Models\User',
    'title'   => $faker->sentence,
    'body'    => $faker->paragraph
]);

$factory('App\Models\Comment', [
    'post_id' => 'factory:App\Models\Post',
    'comment' => $faker->sentence
]);