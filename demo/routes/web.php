<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login-demo', function () {
    $user = User::where('email', 'admin@justsubs.test')->first();
    if ($user) {
        Auth::login($user);
    }

    return redirect('/justsubs');
})->name('login');
