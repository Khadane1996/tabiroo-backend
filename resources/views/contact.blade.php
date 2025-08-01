<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tabiroo</title>
    <link rel="stylesheet" href="{{ asset('css/contact.css') }}">
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
                <a href="{{ url('/about') }}">À Propos</a>
                <a href="{{ url('/blog') }}">Blog</a></li>
                <a href="{{ url('/contact') }}" class="active">Contact</a>
                <a href="{{ url('/faq') }}">FAQ</a>
            </ul>
        </nav>
    </header>

    <main>
        <div class="hero">
            <h1>Contact</h1>
        </div>

        <!-- Formulaire de contact -->
        <section class="contact-form">
            <h3>Comment pouvons-nous vous aider ?</h3>
            <form action="" method="POST">
                <div class="form-group">
                    <label for="subject">Objet</label>
                    <select id="subject" name="subject" required>
                        <option value="" disabled selected hidden>Sélectionnez un objet</option>
                        <option value="support">Assistance & Support</option>
                        <option value="demande">Demande d’information</option>
                        <option value="partenariat">Collaboration & Partenariats</option>
                        <option value="media">Presse & Médias</option>
                        <option value="abus">Signaler un abus</option>
                        <option value="autre">Autre demande</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="fullname">Nom complet <span>*</span></label>
                    <input type="text" id="fullname" name="fullname" required placeholder="Entrez votre nom complet">
                </div>

                <div class="form-group">
                    <label for="email">Adresse e-mail <span>*</span></label>
                    <input type="email" id="email" name="email" required placeholder="Entrez votre e-mail">
                </div>

                <div class="form-group">
                    <label for="phone">Numéro de téléphone</label>
                    <input type="tel" id="phone" name="phone" required placeholder="Entrez votre numéro de téléphone">
                </div>

                <div class="form-group">
                    <label for="message">Votre message <span>*</span></label>
                    <textarea id="message" name="message" required placeholder="Message"></textarea>
                </div>

                <button type="submit" class="btn-submit">Envoyer</button>
            </form>
        </section>
    </main>
    <div id="overlay"></div>
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
            <a href="#"><img src="images/Google Play Badge.svg" alt="PlayStore" width="150"></a>
            <a href="#"><img src="images/App Store Badge.svg" alt="AppStore" width="150"></a>
        </div>
        <div class="tabiroo">
            <div class="follow">
                <p>Suivez-nous</p>
                <div class="reseau">
                    <a href="#"><img src="images/insta.svg" alt=""></a>
                    <a href="#"><img src="images/facebook.svg" alt=""></a>
                    <a href="#"><img src="images/linkedin.svg" alt=""></a>
                    <a href="#"><img src="images/x.svg" alt=""></a>
                </div>
            </div>
            <p>Tabiroo 2025</p>
        </div>
    </footer>

</body>
</html>
