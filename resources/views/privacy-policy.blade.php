<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Politique de confidentialité - Tabiroo</title>
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
            <h1>Politique de confidentialité</h1>
            <div style="max-width: 800px; margin: 0 auto; line-height: 1.6;">
                <h2>1. Collecte des données</h2>
                <p>Nous collectons les informations que vous nous fournissez directement, comme lors de la création de votre compte ou de l'utilisation de nos services.</p>
                
                <h2>2. Utilisation des données</h2>
                <p>Nous utilisons vos données pour fournir, maintenir et améliorer nos services, communiquer avec vous et assurer la sécurité de notre plateforme.</p>
                
                <h2>3. Partage des données</h2>
                <p>Nous ne vendons, n'échangeons ni ne louons vos informations personnelles à des tiers sans votre consentement.</p>
                
                <h2>4. Sécurité des données</h2>
                <p>Nous mettons en place des mesures de sécurité appropriées pour protéger vos informations personnelles.</p>
                
                <h2>5. Vos droits</h2>
                <p>Vous avez le droit d'accéder, de corriger ou de supprimer vos données personnelles à tout moment.</p>
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
