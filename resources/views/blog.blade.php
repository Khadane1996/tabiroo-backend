<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tabiroo</title>
    <link rel="stylesheet" href="{{ asset('css/blog.css') }}">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=NomDeLaPolice:wght@300;400;600&display=swap" rel="stylesheet">
</head>
<body>

    <header>
        <div>
            <a href="{{ url('/') }}">
                <img src="{{ asset('images/logo.svg') }}" alt="Logo de Tabiroo" width="132">
            </a>
        </div>
        <nav>
            <ul>
                <a href="{{ url('/about') }}" >À Propos</a>
                <a href="{{ url('/blog') }}" class="active">Blog</a>
                <a href="{{ url('/contact') }}">Contact</a>
                <a href="{{ url('/faq') }}">FAQ</a>
            </ul>
        </nav>
        <div class="hamburger-menu">
            <span></span>
            <span></span>
            <span></span>
        </div>
    </header>

    <main>
        <div class="blog">
            <h1>A la une</h1>
            <div class="blog-hero">
                <h1>Tabiroo : découvrez l’expérience unique des repas faits maison</h1>
                <p>Bien plus qu’un repas, Tabiroo vous invite à partager des saveurs authentiques et des moments
                    chaleureux chez des hôtes passionnés.</p>
            </div>
            <div class="third">
                <h1>Découvrez également</h1>
                <div class="une">
                    @forelse ($posts as $post)
                        <div class="articles">
                            <img src="{{ $post->image_path ? asset($post->image_path) : asset('images/article1.svg') }}"
                                alt="{{ $post->title }}">
                            <h3>{{ $post->title }}</h3>
                            <p>{{ $post->excerpt }}</p>
                            <a href="{{ route('blog.show', $post->slug) }}">Lire l'article</a>
                        </div>
                    @empty
                        <p>Aucun article publié pour le moment.</p>
                    @endforelse
                </div>

                @if ($posts instanceof \Illuminate\Pagination\LengthAwarePaginator && $posts->hasPages())
                    <div class="pagination">
                        {{-- Simple pagination en gardant le style existant --}}
                        @if ($posts->onFirstPage())
                            <button class="prev-page" disabled>‹</button>
                        @else
                            <a class="prev-page" href="{{ $posts->previousPageUrl() }}">‹</a>
                        @endif

                        @foreach ($posts->getUrlRange(1, $posts->lastPage()) as $page => $url)
                            @if ($page == $posts->currentPage())
                                <button class="page-number active">{{ $page }}</button>
                            @else
                                <a class="page-number" href="{{ $url }}">{{ $page }}</a>
                            @endif
                        @endforeach

                        @if ($posts->hasMorePages())
                            <a class="next-page" href="{{ $posts->nextPageUrl() }}">›</a>
                        @else
                            <button class="next-page" disabled>›</button>
                        @endif
                    </div>
                @endif

            </div>
        </div>
        
    </main>

    <script src="{{ asset('js/main.js') }}"></script>

    <footer>
        <div class="menu">
            <img src="{{ asset('images/tabiroo-white.svg') }}" alt="">
            <div class="global">
                <div class="footer-menu">
                    <ul>
                        <a href="{{ url('/') }}">Accueil</a>
                        <a href="{{ url('/about') }}">À Propos</a>
                        <a href="{{ url('/blog') }}">Blog</a>
                        <a href="{{ url('/contact') }}">Contact</a>
                    </ul>
                </div>
                <div class="footer-menu">
                    <ul>
                        <a href="{{ url('/terms') }}">Condition d'utilisation</a>
                        <a href="{{ url('/privacy-policy') }}">Politique de confidentialité</a>
                        <a href="{{ url('/cookies-settings') }}">Cookies Settings</a>
                        <a href="{{ url('/hygiene-security') }}">Hygiène et sécurité</a>
                    </ul>
                </div>
            </div>
            
        </div>
        <div class="link">
            <a href="#"><img src="{{ asset('images/Google Play Badge.svg') }}" alt="PlayStore", width="150"></a>
            <a href="#"><img src="{{ asset('images/App Store Badge.svg') }}" alt="AppStore", width="150"></a>
        </div>
        <div class="tabiroo">
            <div class="follow">
                <p>Suivez-nous</p>
                <div class="reseau">
                    <a href="#">
                        <img src="{{ asset('images/insta.svg') }}" alt="">
                    </a>
                    <a href="#">
                        <img src="{{ asset('images/facebook.svg') }}" alt="">
                    </a>
                    <a href="#">
                        <img src="{{ asset('images/linkedin.svg') }}" alt="">
                    </a>
                    <a href="#">
                        <img src="{{ asset('images/x.svg') }}" alt="">
                    </a>
                </div>
            </div>
            <p>Tabiroo 2025</p>
        </div>
    </footer>

</body>
</html>
