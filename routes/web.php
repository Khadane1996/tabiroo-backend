<?php

use App\Mail\ConfirmRegistrationMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     Mail::to('ligrejutsu96@gmail.com')
//     ->send(new ConfirmRegistrationMail('565789'));
// });


Route::get('/', function () {
    return view('welcome');
});


Route::get('/mail', function () {
    return view('mail.confirm');
});