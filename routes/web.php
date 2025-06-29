<?php

use App\Models\User;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\SignupController;

Route::controller(SignupController::class)->group(function () {
    Route::get('/', 'signup')->name('auth.signup');
    Route::post('/signup', 'doRegister')->name('auth.register');
});

Route::controller(LoginController::class)->group(function () {
    Route::get('/login', 'login')->name('auth.login');
    Route::post('/authenticate', 'doLogin')->name('auth.validate');
});

Route::controller(PostController::class)->group(function () {
    Route::get('/blogs','index')->name('home');
    Route::get('create/', 'create')->name('post.create');
    Route::post('submit/', 'storePost')->name('post.submit');
    Route::get('post/{slug}', 'singlePost')->name('post.show');
    Route::get('edit/{id}', 'editPost')->name('post.edit');
    Route::put('update/{id}', 'updatePost')->name('post.update');
    Route::delete('destroy/{id}', 'deletePost')->name('post.delete');
});