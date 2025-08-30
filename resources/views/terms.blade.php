<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conditions d'utilisation - Tabiroo</title>
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
            <h1>Conditions d'utilisation</h1>
            <div style="max-width: 800px; margin: 0 auto; line-height: 1.6;">
                <h2>1. Acceptation des conditions</h2>
                <p>En utilisant la plateforme Tabiroo, vous acceptez d'être lié par ces conditions d'utilisation.</p>
                
                <h2>2. Description du service</h2>
                <p>Tabiroo est une plateforme qui met en relation des hôtes passionnés de cuisine et des convives à la recherche de repas faits maison.</p>
                
                <h2>3. Utilisation du service</h2>
                <p>Vous vous engagez à utiliser le service de manière responsable et à respecter les autres utilisateurs.</p>
                
                <h2>4. Responsabilités</h2>
                <p>Chaque utilisateur est responsable de ses actions et du contenu qu'il publie sur la plateforme.</p>
                
                <h2>5. Modifications</h2>
                <p>Nous nous réservons le droit de modifier ces conditions à tout moment.</p>
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
