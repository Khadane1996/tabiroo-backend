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
                <h1 class="titre-article">La restauration à domicile : un service gastronomique sur-mesure pour chaque occasion</h1>
                <div class="meta-article">
                    <span class="auteur-article">Laurant Gillet</span>
                    <span class="date-article">10 février 2025</span>
                    <span class="social-icons">
                        <img src="{{ asset('images/icon-link.svg') }}" alt="Facebook">
                        <img src="{{ asset('images/icon-facebook.svg') }}" alt="Facebook">
                        <img src="{{ asset('images/icon-x.svg') }}" alt="X">
                        <img src="{{ asset('images/icon-linkedin.svg') }}" alt="LinkedIn">
                        <img src="{{ asset('images/icon-instagram.svg') }}" alt="Instagram">
                    </span>
                </div>
                <img class="image-article" src="{{ asset('images/article2.svg') }}" alt="Image de l'article">
                <div class="contenu-article">
                    <h2>Introduction :</h2>
                    <p>Dans un monde où le temps est une ressource précieuse, la restauration à domicile émerge comme une solution idéale pour savourer des repas de qualité sans avoir à quitter le confort de sa maison. Que ce soit pour un dîner romantique, une fête entre amis, un événement professionnel ou même un repas quotidien, la cuisine à domicile apporte une expérience culinaire unique, adaptée à chaque besoin.</p>
                    <h2>Un service personnalisé pour chaque occasion :</h2>
                    <p>L'un des principaux avantages de la restauration à domicile est la possibilité de personnaliser chaque repas. Vous choisissez non seulement le menu, mais également les détails qui le rendent unique : des recettes végétariennes, sans gluten, ou même adaptées aux régimes spécifiques comme le keto ou le véganisme. Les chefs à domicile prennent le temps de comprendre vos préférences et vos besoins pour créer un repas qui vous ressemble.</p>
                    <img class="image-article" src="{{ asset('images/article3.svg') }}" alt="Image de l'article">
                    <h2>La cuisine raffinée sans effort :</h2>
                    <p>L'un des grands avantages de faire appel à un service de restauration à domicile est la liberté totale de profiter de vos invités ou de votre famille sans avoir à vous soucier des préparatifs ou de la vaisselle. Le chef s'occupe de tout : préparation des plats, cuisson, dressage et nettoyage après le repas. Vous n'avez qu'à savourer le moment.</p>
                    <img class="image-article" src="{{ asset('images/article4.svg') }}" alt="Image de l'article">
                    <h2>Un choix pratique pour les repas quotidiens :</h2>
                    <p>Outre les occasions spéciales, la restauration à domicile est également une solution idéale pour simplifier votre quotidien. Beaucoup de services proposent des livraisons régulières de repas préparés, adaptés à vos goûts et à votre emploi du temps.</p>
                    <h2>Conclusion :</h2>
                    <p>La restauration à domicile transforme la manière dont nous vivons l'expérience culinaire. Que ce soit pour une soirée intime, un repas d'affaires ou une simple envie de bien manger chez soi, ce service permet de combiner confort, qualité et personnalisation.</p>
                </div>
            </section>
            <aside class="article-sidebar">
                <div class="suggestion-article">
                    <img src="{{ asset('images/article1.svg') }}" alt="Suggestion 1">
                    <h3>Lorem ipsum dolor sit.</h3>
                    <p>Lorem ipsum dolor sit amet consectetur. Auctor id in eget nunc fermentum.</p>
                    <a href="#">Lire l'article</a>
                </div>
                <div class="suggestion-article">
                    <img src="{{ asset('images/article2.svg') }}" alt="Suggestion 1">
                    <h3>Lorem ipsum dolor sit.</h3>
                    <p>Lorem ipsum dolor sit amet consectetur. Auctor id in eget nunc fermentum.</p>
                    <a href="#">Lire l'article</a>
                </div>
                <div class="suggestion-article">
                    <img src="{{ asset('images/article3.svg') }}" alt="Suggestion 2">
                    <h3>Lorem ipsum dolor sit.</h3>
                    <p>Lorem ipsum dolor sit amet consectetur. Auctor id in eget nunc fermentum.</p>
                    <a href="#">Lire l'article</a>
                </div>
                <div class="suggestion-article">
                    <img src="{{ asset('images/article4.svg') }}" alt="Suggestion 3">
                    <h3>Lorem ipsum dolor sit.</h3>
                    <p>Lorem ipsum dolor sit amet consectetur. Auctor id in eget nunc fermentum.</p>
                    <a href="#">Lire l'article</a>
                </div>
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