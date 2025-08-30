<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hygiène et sécurité - Tabiroo</title>
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
            <h1>Hygiène et sécurité</h1>
            <div style="max-width: 800px; margin: 0 auto; line-height: 1.6;">
                <h2>Nos engagements</h2>
                <p>La sécurité et l'hygiène sont au cœur de notre mission. Nous nous engageons à maintenir les plus hauts standards de qualité pour garantir une expérience culinaire sûre et agréable.</p>
                
                <h2>Standards d'hygiène</h2>
                <p>Tous nos hôtes doivent respecter des normes d'hygiène strictes, incluant :</p>
                <ul>
                    <li>Formation en hygiène alimentaire</li>
                    <li>Inspection régulière des cuisines</li>
                    <li>Respect des bonnes pratiques de manipulation des aliments</li>
                    <li>Utilisation d'équipements de protection appropriés</li>
                </ul>
                
                <h2>Sécurité des convives</h2>
                <p>Nous mettons en place plusieurs mesures pour assurer votre sécurité :</p>
                <ul>
                    <li>Vérification d'identité des hôtes</li>
                    <li>Système de notation et d'avis</li>
                    <li>Assurance responsabilité civile</li>
                    <li>Support client 24/7</li>
                </ul>
                
                <h2>Signalement</h2>
                <p>Si vous constatez un problème d'hygiène ou de sécurité, n'hésitez pas à nous contacter immédiatement.</p>
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
