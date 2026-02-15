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
        // Article à la une (un seul possible)
        $featuredPost = Post::published()
            ->where('is_featured', true)
            ->latest('published_at')
            ->first();

        // Articles publiés, excluant celui à la une pour éviter la duplication
        $postsQuery = Post::published()->orderByDesc('published_at');
        if ($featuredPost) {
            $postsQuery->where('id', '<>', $featuredPost->id);
        }
        $posts = $postsQuery->paginate(9);

        return view('blog', [
            'featuredPost' => $featuredPost,
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

