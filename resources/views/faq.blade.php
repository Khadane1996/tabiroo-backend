<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAQ - Tabiroo</title>
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('css/faq.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=NomDeLaPolice:wght@300;400;600&display=swap" rel="stylesheet">
</head>
<body>
    <header>
        <div>
            <a href="/">
                <img src="{{ asset('images/logo.svg') }}" alt="Logo de Tabiroo" width="132">
            </a>
        </div>
        <nav>
            <ul>
                <a href="{{ url('/about') }}">À Propos</a>
                <a href="{{ url('/blog') }}">Blog</a>
                <a href="{{ url('/contact') }}">Contact</a>
                <a href="{{ url('/faq') }}" class="active">FAQ</a>
            </ul>
        </nav>
    </header>
    <main>
        <section class="faq-banner">
            <h1>FAQ</h1>
        </section>
        <section class="faq-section">
            <div class="faq-container">
                <div class="faq-item">
                    <button class="faq-question">Qu'est-ce que Tabiroo ? <span class="arrow">&#9662;</span></button>
                    <div class="faq-answer">Tabiroo est une plateforme qui met en relation des hôtes passionnés de cuisine et des convives à la recherche de repas faits maison.</div>
                </div>
                <div class="faq-item">
                    <button class="faq-question">Comment réserver un repas ? <span class="arrow">&#9662;</span></button>
                    <div class="faq-answer">Il suffit de parcourir les expériences disponibles, choisir une date et réserver en quelques clics via notre site ou notre application.</div>
                </div>
                <div class="faq-item">
                    <button class="faq-question">Comment devenir hôte ? <span class="arrow">&#9662;</span></button>
                    <div class="faq-answer">Inscrivez-vous en tant qu'hôte, créez vos menus et renseignez vos disponibilités pour commencer à accueillir des convives.</div>
                </div>
                <div class="faq-item">
                    <button class="faq-question">L'application est-elle gratuite ? <span class="arrow">&#9662;</span></button>
                    <div class="faq-answer">Oui, l'application Tabiroo est gratuite sur Google Play et l'App Store.</div>
                </div>
            </div>
        </section>
        <div class="faq-apps-banner-wrapper">
            <section class="faq-apps-banner">
                <div class="apps-content">
                    <div class="apps-text">
                        <h2>Nos apps<br>Disponible sur</h2>
                        <div class="apps-links">
                            <a href="#"><img src="images/Google Play Badge.svg" alt="PlayStore" width="150"></a>
                            <a href="#"><img src="images/App Store Badge.svg" alt="AppStore" width="150"></a>
                        </div>
                    </div>
                </div>
                <img src="{{ asset('images/dispo.svg') }}" alt="Nos apps disponibles" class="apps-banner__img">
            </section>
        </div>
    </main>
    <footer>
        <div class="menu">
            <img src="{{ asset('images/tabiroo-white.svg') }}" alt="">
            <div class="global">
                <div class="footer-menu">
                    <ul>
                        <a href="#">Accueil</a>
                        <a href="#">À Propos</a>
                        <a href="#">Blog</a>
                        <a href="#">Contact</a>
                    </ul>
                </div>
                <div class="footer-menu">
                    <ul>
                        <a href="#">Condition d'utilisation</a>
                        <a href="#">Politique de confidentialité</a>
                        <a href="#">Cookies Settings</a>
                        <a href="#">Hygiène et sécurité</a>
                    </ul>
                </div>
            </div>
        </div>
        <div class="link">
            <a href="#"><img src="images/Google Play Badge.svg" alt="PlayStore" width="150"></a>
            <a href="#"><img src="images/App Store Badge.svg" alt="AppStore" width="150"></a>
        </div>
        <div class="tabiroo">
            <div class="follow">
                <p>Suivez-nous</p>
                <div class="reseau">
                    <a href="#">
                        <img src="images/insta.svg" alt="">
                    </a>
                    <a href="#">
                        <img src="images/facebook.svg" alt="">
                    </a>
                    <a href="#">
                        <img src="images/linkedin.svg" alt="">
                    </a>
                    <a href="#">
                        <img src="images/x.svg" alt="">
                    </a>
                </div>
            </div>
            <p>Tabiroo 2025</p>
        </div>
    </footer>
    <script src="{{ asset('js/main.js') }}"></script>
    <script src="js/faq.js"></script>
</body>
</html>
