@extends('admin.layouts.app')

@section('page-title', 'Articles de blog')

@section('content')
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="card-title mb-0">Liste des articles</h5>
                <a href="{{ route('admin.posts.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> Nouvel article
                </a>
            </div>

            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Titre</th>
                        <th>Slug</th>
                        <th>Statut</th>
                        <th>À la une</th>
                        <th>Publié le</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($posts as $post)
                        <tr>
                            <td>{{ $post->title }}</td>
                            <td>{{ $post->slug }}</td>
                            <td>
                                <span
                                    class="badge bg-{{ $post->status === 'published' ? 'success' : 'secondary' }}">{{ $post->status }}</span>
                            </td>
                            <td>
                                @if ($post->is_featured)
                                    <span class="badge bg-warning text-dark">À la une</span>
                                @endif
                            </td>
                            <td>{{ optional($post->published_at)->format('d/m/Y H:i') }}</td>
                            <td>
                                <a href="{{ route('admin.posts.edit', $post) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('admin.posts.destroy', $post) }}" method="POST" class="d-inline"
                                    onsubmit="return confirm('Supprimer cet article ?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">Aucun article pour le moment.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="mt-3">
                {{ $posts->links() }}
            </div>
        </div>
    </div>
@endsection

