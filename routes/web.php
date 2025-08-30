<?php

use App\Mail\ConfirmRegistrationMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     Mail::to('ligrejutsu96@gmail.com')
//     ->send(new ConfirmRegistrationMail('565789'));
// });


// Route::get('/', function () {
//     return view('welcome');
// });


// Route::get('/mail', function () {
//     return view('mail.confirm');
// });

Route::get('/', function () {
    return view('index');
});

Route::get('/about', function () {
    return view('about');
});

Route::get('/blog', function () {
    return view('blog');
});

Route::get('/contact', function () {
    return view('contact');
});

Route::get('/faq', function () {
    return view('faq');
});

Route::get('/article-detail', function () {
    return view('article-detail');
});

// Nouvelles routes pour les pages légales
Route::get('/terms', function () {
    return view('terms');
});

Route::get('/privacy-policy', function () {
    return view('privacy-policy');
});

Route::get('/cookies-settings', function () {
    return view('cookies-settings');
});

Route::get('/hygiene-security', function () {
    return view('hygiene-security');
});