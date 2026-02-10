@extends('admin.layouts.app')

@section('page-title', 'Dashboard')

@section('content')
    <div class="row">
        <div class="col-xxl-4 col-md-6">
            <div class="card info-card">
                <div class="card-body">
                    <h5 class="card-title">Articles de blog</h5>
                    <div class="d-flex align-items-center">
                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                            <i class="bi bi-newspaper"></i>
                        </div>
                        <div class="ps-3">
                            <h6>{{ $postsCount }}</h6>
                            <span class="text-muted small pt-2 ps-1">articles au total</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xxl-4 col-md-6">
            <div class="card info-card">
                <div class="card-body">
                    <h5 class="card-title">Articles publiés</h5>
                    <div class="d-flex align-items-center">
                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                            <i class="bi bi-check-circle"></i>
                        </div>
                        <div class="ps-3">
                            <h6>{{ $publishedCount }}</h6>
                            <span class="text-muted small pt-2 ps-1">visibles sur le site</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

