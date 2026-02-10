<?php

use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\PostController as AdminPostController;
use App\Http\Controllers\BlogController;
use App\Http\Middleware\AdminAuthenticate;
use App\Mail\ConfirmRegistrationMail;
use App\Models\Post;
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
    $homeFeaturedPosts = Post::published()
        ->where('is_featured', true)
        ->latest('published_at')
        ->limit(4)
        ->get();

    return view('index', [
        'homeFeaturedPosts' => $homeFeaturedPosts,
    ]);
});

Route::get('/about', function () {
    return view('about');
});

// Blog public
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');

Route::get('/contact', function () {
    return view('contact');
});

Route::get('/faq', function () {
    return view('faq');
});

// Ancienne route de détail d'article : redirigée vers le blog
Route::get('/article-detail', function () {
    return redirect()->route('blog.index');
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

// Routes Admin (dashboard + gestion des articles)
Route::prefix('admin')->name('admin.')->group(function () {
    // Authentification admin
    Route::get('login', [AdminAuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AdminAuthController::class, 'login'])->name('login.submit');

    Route::middleware(AdminAuthenticate::class)->group(function () {
        Route::post('logout', [AdminAuthController::class, 'logout'])->name('logout');

        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

        Route::resource('posts', AdminPostController::class)->except(['show']);
    });
});
