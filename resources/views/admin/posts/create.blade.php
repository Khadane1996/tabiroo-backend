@extends('admin.layouts.app')

@section('page-title', 'Nouvel article')

@section('content')
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Créer un nouvel article</h5>

            <form method="POST" action="{{ route('admin.posts.store') }}" enctype="multipart/form-data">
                @include('admin.posts.form')
            </form>
        </div>
    </div>
@endsection

