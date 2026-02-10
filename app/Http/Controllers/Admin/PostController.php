<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::orderByDesc('created_at')->paginate(15);

        return view('admin.posts.index', compact('posts'));
    }

    public function create()
    {
        return view('admin.posts.create');
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['title']);
        }

        // Gestion de l'upload d'image (obligatoire à la création)
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('blog', 'public');
            // On stocke un chemin directement utilisable par asset()
            $data['image_path'] = 'storage/' . $path;
        }

        // Normalisation du flag "À la une"
        $data['is_featured'] = $request->boolean('is_featured');

        Post::create($data);

        return redirect()->route('admin.posts.index')
            ->with('success', 'Article créé avec succès.');
    }

    public function edit(Post $post)
    {
        return view('admin.posts.edit', compact('post'));
    }

    public function update(Request $request, Post $post)
    {
        $data = $this->validateData($request, $post->id);

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['title']);
        }

        // Gestion de l'upload d'image (optionnelle à la mise à jour)
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('blog', 'public');
            $data['image_path'] = 'storage/' . $path;
        }

        // Normalisation du flag "À la une" (permet de le désactiver)
        $data['is_featured'] = $request->boolean('is_featured');

        $post->update($data);

        return redirect()->route('admin.posts.index')
            ->with('success', 'Article mis à jour avec succès.');
    }

    public function destroy(Post $post)
    {
        $post->delete();

        return redirect()->route('admin.posts.index')
            ->with('success', 'Article supprimé avec succès.');
    }

    protected function validateData(Request $request, ?int $postId = null): array
    {
        $imageRules = $postId
            ? ['nullable', 'image', 'max:2048']
            : ['required', 'image', 'max:2048'];

        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:posts,slug,' . ($postId ?? 'NULL') . ',id'],
            'excerpt' => ['required', 'string'],
            'content' => ['required', 'string'],
            'image' => $imageRules,
            'author_name' => ['nullable', 'string', 'max:255'],
            'published_at' => ['required', 'date'],
            'is_featured' => ['sometimes', 'boolean'],
            'status' => ['required', 'string', 'in:draft,published'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
        ]);
    }
}

