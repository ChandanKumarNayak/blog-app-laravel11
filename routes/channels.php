<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

//only for user & editor
Broadcast::channel('post-create', function ($user) {
    $allowedRoles = [
        config('constants.roles.USER'),
        config('constants.roles.EDITOR'),
        config('constants.roles.ADMIN')
    ];
    return in_array($user->role, $allowedRoles);
});