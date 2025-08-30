<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paramètres des cookies - Tabiroo</title>
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

    <main style="margin-top: 100px; padding: 50px;">
        <div class="container">
            <h1>Paramètres des cookies</h1>
            <div style="max-width: 800px; margin: 0 auto; line-height: 1.6;">
                <h2>Qu'est-ce qu'un cookie ?</h2>
                <p>Un cookie est un petit fichier texte stocké sur votre appareil qui nous aide à améliorer votre expérience sur notre site.</p>
                
                <h2>Types de cookies que nous utilisons</h2>
                <h3>Cookies essentiels</h3>
                <p>Ces cookies sont nécessaires au fonctionnement du site et ne peuvent pas être désactivés.</p>
                
                <h3>Cookies de performance</h3>
                <p>Ces cookies nous aident à comprendre comment les visiteurs interagissent avec notre site.</p>
                
                <h3>Cookies de fonctionnalité</h3>
                <p>Ces cookies permettent au site de se souvenir de vos choix et préférences.</p>
                
                <h2>Gérer vos préférences</h2>
                <p>Vous pouvez modifier vos préférences de cookies à tout moment en utilisant les paramètres de votre navigateur.</p>
            </div>
        </div>
    </main>

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
                    <a href="#"><img src="{{ asset('images/insta.svg') }}" alt=""></a>
                    <a href="#"><img src="{{ asset('images/facebook.svg') }}" alt=""></a>
                    <a href="#"><img src="{{ asset('images/linkedin.svg') }}" alt=""></a>
                    <a href="#"><img src="{{ asset('images/x.svg') }}" alt=""></a>
                </div>
            </div>
            <p>Tabiroo 2025</p>
        </div>
    </footer>
    <script src="{{ asset('js/main.js') }}"></script>
</body>
</html>
