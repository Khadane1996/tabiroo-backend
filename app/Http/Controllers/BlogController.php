<?php

namespace App\Http\Controllers;

use App\Models\Post;

class BlogController extends Controller
{
    /**
     * Display a listing of the posts.
     */
    public function index()
    {
        // Tous les articles publiés, y compris ceux marqués "À la une"
        $posts = Post::published()
            ->orderByDesc('published_at')
            ->paginate(9);

        return view('blog', [
            'posts' => $posts,
        ]);
    }

    /**
     * Display the specified post.
     */
    public function show(string $slug)
    {
        $post = Post::published()
            ->where('slug', $slug)
            ->firstOrFail();

        $suggestedPosts = Post::published()
            ->where('id', '<>', $post->id)
            ->latest('published_at')
            ->limit(4)
            ->get();

        return view('article-detail', [
            'post' => $post,
            'suggestedPosts' => $suggestedPosts,
        ]);
    }
}

