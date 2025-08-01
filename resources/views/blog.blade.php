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
            <a href="/">
                <img src="{{ asset('images/logo.svg') }}" alt="Logo de Tabiroo" width="132">
            </a>
        </div>
        <nav>
            <ul>
                <a href="{{ url('/about') }}">À Propos</a>
                <a href="{{ url('/blog') }}" class="active">Blog</a></li>
                <a href="{{ url('/contact') }}">Contact</a>
                <a href="{{ url('/faq') }}">FAQ</a>
            </ul>
        </nav>
    </header>

    <main>
        <div class="blog">
            <h1>A la une</h1>
            <div class="blog-hero"> 
                <h1> Lorem ipsum dolor color sit.</h1>
                <p>Lorem ipsum dolor sit amet consectetur. Auctor id in eget nunc<br> fermentum. </p>  
            </div>
            <div class="third">
                <h1>Découvrez également</h1>
                <div class="une">
                    <div class="articles">
                        <img src="{{ asset('images/article1.svg') }}" alt="">
                        <h3>Lorem ipsum dolor sit.</h3>
                        <p>Lorem ipsum dolor sit amet consectetur. Auctor id in eget nunc fermentum. </p>
                        <a href="#">Lire l'article</a>
                    </div>
                    <div class="articles">
                        <img src="{{ asset('images/article2.svg') }}" alt="">
                        <h3>Lorem ipsum dolor sit.</h3>
                        <p>Lorem ipsum dolor sit amet consectetur. Auctor id in eget nunc fermentum. </p>
                        <a href="#">Lire l'article</a>
                    </div>
                    <div class="articles">
                        <img src="{{ asset('images/article3.svg') }}" alt="">
                        <h3>Lorem ipsum dolor sit.</h3>
                        <p>Lorem ipsum dolor sit amet consectetur. Auctor id in eget nunc fermentum. </p>
                        <a href="#">Lire l'article</a>
                    </div>
                    <div class="articles">
                        <img src="{{ asset('images/article4.svg') }}" alt="">
                        <h3>Lorem ipsum dolor sit.</h3>
                        <p>Lorem ipsum dolor sit amet consectetur. Auctor id in eget nunc fermentum. </p>
                        <a href="#">Lire l'article</a>
                    </div>
                    <div class="articles">
                        <img src="{{ asset('images/article4.svg') }}" alt="">
                        <h3>Lorem ipsum dolor sit.</h3>
                        <p>Lorem ipsum dolor sit amet consectetur. Auctor id in eget nunc fermentum. </p>
                        <a href="#">Lire l'article</a>
                    </div>
                    <div class="articles">
                        <img src="{{ asset('images/article2.svg') }}" alt="">
                        <h3>Lorem ipsum dolor sit.</h3>
                        <p>Lorem ipsum dolor sit amet consectetur. Auctor id in eget nunc fermentum. </p>
                        <a href="#">Lire l'article</a>
                    </div>
                    <div class="articles">
                        <img src="{{ asset('images/article1.svg') }}" alt="">
                        <h3>Lorem ipsum dolor sit.</h3>
                        <p>Lorem ipsum dolor sit amet consectetur. Auctor id in eget nunc fermentum. </p>
                        <a href="#">Lire l'article</a>
                    </div>
                    <div class="articles">
                        <img src="{{ asset('images/article2.svg') }}" alt="">
                        <h3>Lorem ipsum dolor sit.</h3>
                        <p>Lorem ipsum dolor sit amet consectetur. Auctor id in eget nunc fermentum. </p>
                        <a href="#">Lire l'article</a>
                    </div>
                    <div class="articles">
                        <img src="{{ asset('images/article3.svg') }}" alt="">
                        <h3>Lorem ipsum dolor sit.</h3>
                        <p>Lorem ipsum dolor sit amet consectetur. Auctor id in eget nunc fermentum. </p>
                        <a href="#">Lire l'article</a>
                    </div>
                    <div class="articles">
                        <img src="{{ asset('images/article1.svg') }}" alt="">
                        <h3>Lorem ipsum dolor sit.</h3>
                        <p>Lorem ipsum dolor sit amet consectetur. Auctor id in eget nunc fermentum. </p>
                        <a href="#">Lire l'article</a>
                    </div>
                    <div class="articles">
                        <img src="{{ asset('images/article2.svg') }}" alt="">
                        <h3>Lorem ipsum dolor sit.</h3>
                        <p>Lorem ipsum dolor sit amet consectetur. Auctor id in eget nunc fermentum. </p>
                        <a href="#">Lire l'article</a>
                    </div>
                    <div class="articles">
                        <img src="{{ asset('images/article3.svg') }}" alt="">
                        <h3>Lorem ipsum dolor sit.</h3>
                        <p>Lorem ipsum dolor sit amet consectetur. Auctor id in eget nunc fermentum. </p>
                        <a href="#">Lire l'article</a>
                    </div>
                    <div class="articles">
                        <img src="{{ asset('images/article1.svg') }}" alt="">
                        <h3>Lorem ipsum dolor sit.</h3>
                        <p>Lorem ipsum dolor sit amet consectetur. Auctor id in eget nunc fermentum. </p>
                        <a href="#">Lire l'article</a>
                    </div>
                    <div class="articles">
                        <img src="{{ asset('images/article2.svg') }}" alt="">
                        <h3>Lorem ipsum dolor sit.</h3>
                        <p>Lorem ipsum dolor sit amet consectetur. Auctor id in eget nunc fermentum. </p>
                        <a href="#">Lire l'article</a>
                    </div>
                    <div class="articles">
                        <img src="{{ asset('images/article1.svg') }}" alt="">
                        <h3>Lorem ipsum dolor sit.</h3>
                        <p>Lorem ipsum dolor sit amet consectetur. Auctor id in eget nunc fermentum. </p>
                        <a href="#">Lire l'article</a>
                    </div>
                    <div class="articles">
                        <img src="{{ asset('images/article2.svg') }}" alt="">
                        <h3>Lorem ipsum dolor sit.</h3>
                        <p>Lorem ipsum dolor sit amet consectetur. Auctor id in eget nunc fermentum. </p>
                        <a href="#">Lire l'article</a>
                    </div>
                </div>
                <div class="pagination">
                    <button class="prev-page">‹</button>
                    <button class="page-number active">1</button>
                    <button class="page-number">2</button>
                    <span>...</span>
                    <button class="page-number">3</button>
                    <button class="page-number">4</button>
                    <button class="next-page">›</button>
                </div>                
                
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
                        <a href="/">Accueil</a>
                        <a href="{{ url('/about') }}">À Propos</a>
                        <a href="{{ url('/blog') }}">Blog</a>
                        <a href="{{ url('/contact') }}">Contact</a>
                    </ul>
                </div>
                <div class="footer-menu">
                    <ul>
                        <a href="#">Condition d’utilisation</a>
                        <a href="#">Politique de confidentialité</a>
                        <a href="#">Cookies Settings</a>
                        <a href="#">Hygiène et sécurité</a>
                    </ul>
                </div>
            </div>
            
        </div>
        <div class="link">
            <a href="#"><img src="images/Google Play Badge.svg" alt="PlayStore", width="150"></a>
            <a href="#"><img src="images/App Store Badge.svg" alt="AppStore", width="150"></a>
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

</body>
</html>
