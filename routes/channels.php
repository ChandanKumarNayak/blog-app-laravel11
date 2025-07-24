<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

//only for user 
Broadcast::channel('post-create', function ($user) {
    return $user->role === config('constants.roles.USER');
});