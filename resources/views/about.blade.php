<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tabiroo</title>
    <link rel="stylesheet" href="{{ asset('css/about.css') }}">
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
                <a href="{{ url('/about') }}" class="active">À Propos</a>
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
        <div class="hero">
            <h1> À Propos de<br> nous</h1>
        </div>
        <div class="blocs">
            <div class="first">
                <div class="text">
                    <!-- <h1>À propos de <span class="tab">Tabiroo </span><span>Toi !</span></h1> -->
                    <p>
                        Tabiroo – Le goût du fait maison, la chaleur du partage.

                        Envie de vivre une expérience culinaire qui sort de l’ordinaire ? Avec Tabiroo, réservez votre place à la table d’hôtes passionnés, et plongez dans un univers de saveurs authentiques, de rencontres chaleureuses et de moments uniques.

                        Tabiroo, c’est la rencontre entre des particuliers gourmands et des chefs du quotidien qui partagent leur passion autour de repas faits maison.
                        Chez nos hôtes, chaque plat raconte une histoire, chaque repas devient une expérience conviviale, loin des codes traditionnels de la restauration.

                        Nous croyons que la meilleure cuisine est celle que l’on partage.
                        Grâce à Tabiroo, nos hôtes peuvent vivre pleinement leur passion et compléter leurs revenus en toute liberté, pendant que nos convives découvrent des expériences exclusives et inoubliables.

                        Tabiroo, c’est l’authenticité, la proximité et la convivialité au cœur de chaque bouchée.
                    </p>
                </div>
                <!-- <img src="{{ asset('images/first.svg') }}" alt=""> -->
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
