<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;

class DashboardController extends Controller
{
    public function index()
    {
        $postsCount = Post::count();
        $publishedCount = Post::where('status', 'published')->count();

        return view('admin.dashboard', [
            'postsCount' => $postsCount,
            'publishedCount' => $publishedCount,
        ]);
    }
}

