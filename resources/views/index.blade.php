<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tabiroo</title>
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
                <a href="{{ url('/blog') }}">Blog</a></li>
                <a href="{{ url('/contact') }}">Contact</a>
                <a href="{{ url('/faq') }}">FAQ</a>
            </ul>
        </nav>
    </header>

    <main>
        <div class="hero">
            <h1> Des repas faits maison, des instants conviviaux</h1>
            <p>Vivez une expérience culinaire unique près de chez vous.</p>
        </div>
        <div class="blocs">
            <div class="first">        
                <div class="container">
                    <h1>Vous souhaitez :</h1>
                    <div class="tabbed-content">
                        <div class="tab-menu">
                            <div class="menu-item active" data-tab="savourer">Savourez un plat</div>
                            <div class="menu-item" data-tab="partager">Partager votre cuisine</div>
                        </div>
                
                        <div class="tab-content active" data-content="savourer">
                            <h1 class="italic">Des repas exclusifs, une expérience<br> conviviale</h1>
                            <p>Réservez des repas faits maison dans un cadre convivial près de<br> chez vous. Savourez des plats uniques, préparés avec amour par<br> des hôtes passionnés.</p>
                            <div class="app-mob">
                                <div class="link">
                                    <a href="#"><img src="{{ asset('images/Google Play Badge.svg') }}" alt="PlayStore", width="150"></a>
                                    <img src="{{ asset('images/log.svg') }}" alt="", width="42">
                                    <a href="#"><img src="{{ asset('images/App Store Badge.svg') }}" alt="AppStore", width="150"></a>
                                </div>
                                <div class="mob">
                                    <img src="{{ asset('images/tabiroo.svg') }}" alt="", class="img-logo">
                                    <img src="{{ asset('images/X - mockup - white.svg') }}" class="img-mob" alt="">
                                </div>
                            </div>
                        </div>
                        <div class="tab-content" data-content="partager">
                            <h1 class="italic">Devenez hôte, partagez<br> votre passion</h1>
                            <p>Créez des repas faits maison et accueillez des convives chez vous pour des<br> moments conviviaux. Rejoignez une communauté passionnée et faites découvrir<br> votre cuisine tout en générant des compléments de revenus.</p>
                            <div class="app-mob">
                                <div class="link">
                                    <a href="#"><img src="{{ asset('images/Google Play Badge.svg') }}" alt="PlayStore", width="150"></a>
                                    <img src="{{ asset('images/black.svg') }}" alt="", width="42">
                                    <a href="#"><img src="{{ asset('images/App Store Badge.svg') }}" alt="AppStore", width="150"></a>
                                </div>
                                <div class="mob">
                                    <img src="{{ asset('images/tabiroo.svg') }}" alt="", class="img-logo">
                                    <img src="{{ asset('images/X - mockup - white.svg') }}" class="img-mob" alt="">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="second">
                <h1> Comment ça marche ?</h1>
                <div class="enum">
                    <h2>Pour les convives :</h2>
                    <div class="numero">
                        <div class="bloc-chiffre">
                            <img src="{{ asset('images/uno.svg') }}" alt="">
                            <h3>Explorez</h3>
                            <p>Sélectionnez une date et<br> trouvez l’expérience culinaire<br> qui vous plaît.</p>
                        </div>
                        <img src="{{ asset('images/down.svg') }}" alt="">
                        <div class="bloc-chiffre">
                            <img src="{{ asset('images/dos.svg') }}" alt="" class="dos">
                            <h3>Réservez en 2 clics</h3>
                            <p>Validez votre réservation<br> instantanément et recevez<br> votre confirmation.</p>
                        </div>
                        <img src="{{ asset('images/up.svg') }}" alt="">
                        <div class="bloc-chiffre">
                            <img src="{{ asset('images/tres.svg') }}" alt="">
                            <h3>Savourez</h3>
                            <p>Profitez d’un repas fait maison<br> et d’un moment convivial chez<br> votre hôte.</p>
                        </div>
                    </div>
                </div>

                <div class="enum">
                    <h2>Pour les hôtes :</h2>
                    <div class="numero">
                        <div class="bloc-chiffre">
                            <img src="{{ asset('images/uno.svg') }}" alt="">
                            <h3>Créez des <br> expériences</h3>
                            <p>Composez vos menus et<br> renseignez vos disponibilités.</p>
                        </div>
                        <img src="{{ asset('images/down.svg') }}" alt="">
                        <div class="bloc-chiffre">
                            <img src="{{ asset('images/dos.svg') }}" alt="" class="dos">
                            <h3>Recevez des <br>réservations</h3>
                            <p>Gérez vos commandes en<br> toute simplicité grâce à la<br> validation automatique.</p>
                        </div>
                        <img src="{{ asset('images/up.svg') }}" alt="">
                        <div class="bloc-chiffre">
                            <img src="{{ asset('images/tres.svg') }}" alt="">
                            <h3>Accueillez &<br> gagnez</h3>
                            <p>Faites découvrir votre cuisine à<br> vos convives et générez des<br> revenus complémentaires.</p>
                        </div>
                    </div>
                </div>      
            </div>
            <div class="third">
                <h1> À la Une</h1>
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
                </div>
                <button>Accéder au blog
                    <img src="{{ asset('images/arrow.svg') }}" alt="">
                </button>
            </div>
            <div class="four">
                <h1>Ils ont goûté à l’expérience</h1>
                <div class="temoignages-container">
                    <img src="{{ asset('images/left.svg') }}" alt="Précédent" class="prev" />
                    <div class="temoignages">
                        <div class="temoin">
                            <p>“Lorem ipsum dolor sit amet consectetur. Auctor id in eget nunc fermentum. Lectus volutpat ipsum etiam egestas velit et semper quisque. Eleifend vel egestas neque lacinia cras turpis facilisis vestibulum. Nibh suspendisse lectus urna ullamcorper.” </p>
                            <div class="profil">
                                <img src="{{ asset('images/pro1.svg') }}" alt="">
                                <div class="info">
                                    <span>Jane Cooper</span>
                                    <p>CEO Jasper</p>
                                </div>
                            </div>
                        </div>
                        <div class="temoin">
                            <p>“Lorem ipsum dolor sit amet consectetur. Auctor id in eget nunc fermentum. Lectus volutpat ipsum etiam egestas velit et semper quisque. Eleifend vel egestas neque lacinia cras turpis facilisis vestibulum. Nibh suspendisse lectus urna ullamcorper.” </p>
                            <div class="profil">
                                <img src="{{ asset('images/pro2.svg') }}" alt="">
                                <div class="info">
                                    <span>Rose Smith</span>
                                    <p>CEO Jasper</p>
                                </div>
                            </div>
                        </div>
                        <div class="temoin">
                            <p>“Lorem ipsum dolor sit amet consectetur. Auctor id in eget nunc fermentum. Lectus volutpat ipsum etiam egestas velit et semper quisque. Eleifend vel egestas neque lacinia cras turpis facilisis vestibulum. Nibh suspendisse lectus urna ullamcorper.” </p>
                            <div class="profil">
                                <img src="{{ asset('images/pro1.svg') }}" alt="">
                                <div class="info">
                                    <span>Jane Cooper</span>
                                    <p>CEO Jasper</p>
                                </div>
                            </div>
                        </div>
                        <div class="temoin">
                            <p>“Lorem ipsum dolor sit amet consectetur. Auctor id in eget nunc fermentum. Lectus volutpat ipsum etiam egestas velit et semper quisque. Eleifend vel egestas neque lacinia cras turpis facilisis vestibulum. Nibh suspendisse lectus urna ullamcorper.” </p>
                            <div class="profil">
                                <img src="{{ asset('images/pro2.svg') }}" alt="">
                                <div class="info">
                                    <span>Rose Smith</span>
                                    <p>CEO Jasper</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <img src="{{ asset('images/right.svg') }}" alt="Suivant" class="next" />
                </div>
            </div>
            
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
                        <a href="#">Condition d’utilisation</a>
                        <a href="#">Politique de confidentialité</a>
                        <a href="#">Cookies Settings</a>
                        <a href="#">Hygiène et sécurité</a>
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
    <script src="{{ asset('js/main.js') }}"></script>
</body>
</html>
