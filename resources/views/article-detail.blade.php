<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détail de l'article - Tabiroo</title>
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('css/blog.css') }}">
    <link rel="stylesheet" href="{{ asset('css/article.css') }}">
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
                <a href="{{ url('/about') }}">À Propos</a>
                <a href="{{ url('/blog') }}">Blog</a>
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
        <div class="article-layout">
            <section class="article-main">
                <h1 class="titre-article">{{ $post->title }}</h1>
                <div class="meta-article">
                    <span class="auteur-article">{{ $post->author_name }}</span>
                    <span class="date-article">
                        {{ optional($post->published_at)->format('d/m/Y') }}
                    </span>
                    <span class="social-icons">
                        <img src="{{ asset('images/icon-link.svg') }}" alt="Lien">
                        <img src="{{ asset('images/icon-facebook.svg') }}" alt="Facebook">
                        <img src="{{ asset('images/icon-x.svg') }}" alt="X">
                        <img src="{{ asset('images/icon-linkedin.svg') }}" alt="LinkedIn">
                        <img src="{{ asset('images/icon-instagram.svg') }}" alt="Instagram">
                    </span>
                </div>
                <img class="image-article"
                    src="{{ $post->image_path ? asset($post->image_path) : asset('images/article2.svg') }}"
                    alt="Image de l'article">
                <div class="contenu-article">
                    {!! $post->content !!}
                </div>
            </section>
            <aside class="article-sidebar">
                @foreach ($suggestedPosts as $suggested)
                    <div class="suggestion-article">
                        <img src="{{ $suggested->image_path ? asset($suggested->image_path) : asset('images/article1.svg') }}"
                            alt="{{ $suggested->title }}">
                        <h3>{{ $suggested->title }}</h3>
                        <p>{{ $suggested->excerpt }}</p>
                        <a href="{{ route('blog.show', $suggested->slug) }}">Lire l'article</a>
                    </div>
                @endforeach
            </aside>
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
            <a href="#"><img src="{{ asset('images/Google Play Badge.svg') }}" alt="PlayStore" width="150"></a>
            <a href="#"><img src="{{ asset('images/App Store Badge.svg') }}" alt="AppStore" width="150"></a>
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