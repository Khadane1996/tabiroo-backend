@extends('admin.layouts.app')

@section('page-title', 'Modifier l\'article')

@section('content')
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Modifier l'article</h5>

            <form method="POST" action="{{ route('admin.posts.update', $post) }}" enctype="multipart/form-data">
                @method('PUT')
                @include('admin.posts.form')
            </form>
        </div>
    </div>
@endsection

