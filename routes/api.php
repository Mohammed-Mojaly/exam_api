<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PostController;

Route::group(['middleware' => 'api', 'prefix' => 'auth'], function ($router) {


    Route::controller(AuthController::class)->group(function () {
        Route::post('/login', 'login');
        Route::post('/register',  'register');
        Route::post('/logout',  'logout');
        Route::post('/refresh',  'refresh');
        Route::get('/profile',  'userProfile');
    });
    Route::controller(PostController::class)->group(function () {
        Route::get('/posts',  'index');
        Route::get('/posts/user-posts',  'user_posts');
        Route::get('/posts/search',  'search');
        Route::get('/posts/by-tags',  'posts_by_tags');
        Route::get('/posts/{id}',  'get_post');

        Route::post('/posts/create',  'store');
        Route::post('/posts/update/{post}',  'update');
        Route::delete('/posts/delete/{post}',  'distroy');

    });
});
