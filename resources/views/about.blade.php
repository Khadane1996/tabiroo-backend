<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tabiroo</title>
    <link rel="stylesheet" href="{{ asset('css/about.css') }}">
    <link rel="stylesheet" href="css/main.css">
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
                <a href="{{ url('/about') }}" class="active">À Propos</a>
                <a href="{{ url('/blog') }}">Blog</a></li>
                <a href="{{ url('/contact') }}">Contact</a>
                <a href="{{ url('/faq') }}">FAQ</a>
            </ul>
        </nav>
    </header>

    <main>
        <div class="hero">
            <h1> À Propos de<br> nous</h1>
        </div>
        <div class="blocs">
            <div class="first">
                <div class="text">
                    <h1>À propos de <span class="tab">Tabiroo </span><span>Toi !</span></h1>
                    <p>Si t’es ici, ce n’est pas pour lire une présentation banale.<br>
                       Ce n’est pas pour connaître l’histoire d’une entreprise.<br> C’est pour ressentir l’état d’esprit qui m’anime.
                       Alors,<br> autant que cette parenthèse t’apporte quelque chose — <br>pas juste pour ta curiosité, mais pour toi, tes ambitions,<br> ton énergie.</p><br><br>
                    <h1>Tout est une question <br></span><span>d'état d'esprit.</span></h1>
                    <p>
                        On le voit tous les jours : quelqu’un réussit, sans<br> forcément avoir plus de talent, plus d’argent, ni même plus<br> d’opportunités. Mais il y arrive. Encore et encore. Pourquoi ?<br> Parce qu’il a décidé d’y arriver.
                    </p>
                </div>
                <img src="images/first.svg" alt="">
            </div>
            <div class="second">
                <h1> Dans la vie, il y a trois <span>types de personnes :</span></h1>
                <div class="enum">
                    <div class="numero">
                        <div class="bloc-chiffre">
                            <img src="images/uno.svg" alt="" class="dos">
                            <p>Ceux qui ne tentent rien. Ils<br> regardent, envient, mais ne<br> bougent pas.</p>
                        </div>
                        <div class="bloc-chiffre">
                            <img src="images/dos.svg" alt="" class="dos">
                            <p>Validez votre réservation<br> instantanément et recevez<br> votre confirmation.</p>
                        </div>
                        <div class="bloc-chiffre">
                            <img src="images/tres.svg" alt="">
                            <p>Profitez d’un repas fait maison<br> et d’un moment convivial chez<br> votre hôte.</p>
                        </div>
                    </div>
                </div>  
            </div>
        </div>
        <div class="three">
            <p>Les 3 finissent toujours par gagner. Pas parce qu’ils sont les plus doués, mais parce qu’ils y croient plus fort que les<br> autres. Alors pose-toi une vraie question : Dans quel groupe tu te places ? Le temps, lui, ne t’attend pas. Il avance,<br> implacable. Chaque jour, il creuse un vide derrière toi. Tu avances, ou tu tombes. Mais, que tu bouges ou non, la vie<Br> continue sans t’attendre. Sois acteur. Prends ta place. Provoque ta réussite. Même si t’as des doutes, avance. Parce<br> qu’au final… tout est une question d’état d’esprit.</p>
        </div>
        <div class="blocs" style="margin: 0 !important;">
            <div class="first">
                <div class="bloc-img">
                    <img src="images/logo.svg" alt="" width="50%">
                    <img src="images/third.svg" alt="">
                </div>
                <div class="text">
                    <h1>Et maintenant, <span>parlons de Tabiroo.</span></h1>
                    <p>Tabiroo n’est pas juste une plateforme, c’est une expérience<br> culinaire unique qui reconnecte les gens autour de ce qu’il y a<br> de plus authentique : des repas faits maison, des moments de<br> partage, et des rencontres inoubliables.<br><br>

                        Ici, les hôtes passionnés partagent leur savoir-faire et ouvrent<br> leur porte pour offrir une expérience exclusive. Chaque plat<br> est cuisiné avec cœur, chaque repas devient une aventure<br> humaine. Que tu sois en quête de nouvelles saveurs ou d’un<br> moment chaleureux chez un hôte passionné, Tabiroo<br> transforme chaque repas en une véritable immersion culinaire.<br><br>
                        
                        Envie de découvrir des mets faits maison, préparés avec soin ?<br>
                        Tu cherches une expérience gastronomique authentique, loin des restaurants traditionnels ?<br><br>
                        
                        Avec Tabiroo, plonge dans un univers où la cuisine devient<br> émotion, partage, et exclusivité. Que tu sois un hôte cherchant<br> un complément de revenu en partageant ta passion, ou un<br> convive à la recherche de découvertes gustatives, Tabiroo est<br> la plateforme idéale pour vivre une aventure culinaire sans égal..</p><br><br>                    
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

</body>
</html>
