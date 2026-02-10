<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAQ - Tabiroo</title>
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('css/faq.css') }}">
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
                <a href="{{ url('/faq') }}" class="active">FAQ</a>
            </ul>
        </nav>
        <div class="hamburger-menu">
            <span></span>
            <span></span>
            <span></span>
        </div>
    </header>
    <main>
        <section class="faq-banner">
            <h1>FAQ</h1>
        </section>
        <section class="faq-section">
            <div class="faq-container">
                <h2>À propos de Tabiroo</h2>

                <div class="faq-item">
                    <button class="faq-question">
                        Tabiroo est-il un restaurant ?
                        <span class="arrow">&#9662;</span>
                    </button>
                    <div class="faq-answer">
                        Non. Tabiroo est une plateforme de mise en relation entre hôtes (qui proposent des repas faits maison)
                        et convives (qui réservent). Tabiroo n’est pas responsable de la préparation des repas.
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-question">
                        Dois-je créer un compte pour utiliser Tabiroo ?
                        <span class="arrow">&#9662;</span>
                    </button>
                    <div class="faq-answer">
                        Oui, l’inscription est obligatoire pour proposer ou réserver un repas.
                    </div>
                </div>

                <hr>

                <h2>Convives (Clients) – Réservation & Paiement</h2>

                <div class="faq-item">
                    <button class="faq-question">
                        Comment trouver un repas près de chez moi ?
                        <span class="arrow">&#9662;</span>
                    </button>
                    <div class="faq-answer">
                        Utilisez la recherche dans l’appli : vous pouvez filtrer par localisation, type de repas
                        (brunch, déjeuner, dîner, etc.) et disponibilités.
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-question">
                        Puis-je inviter un ami et réserver pour plusieurs personnes ?
                        <span class="arrow">&#9662;</span>
                    </button>
                    <div class="faq-answer">
                        Oui, vous pouvez réserver pour plusieurs convives en une seule commande, dans la limite
                        des places disponibles chez l’hôte.
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-question">
                        Comment fonctionne le code de validation ?
                        <span class="arrow">&#9662;</span>
                    </button>
                    <div class="faq-answer">
                        Le jour du repas, vous recevez un code unique à remettre à l’hôte. Ce code confirme la prestation
                        et déclenche le paiement.
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-question">
                        Comment puis-je payer une réservation ?
                        <span class="arrow">&#9662;</span>
                    </button>
                    <div class="faq-answer">
                        Les paiements se font uniquement par carte bancaire via Stripe, un prestataire sécurisé.
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-question">
                        Quand l’hôte reçoit-il son paiement ?
                        <span class="arrow">&#9662;</span>
                    </button>
                    <div class="faq-answer">
                        L’argent est bloqué jusqu’au jour du repas. Une fois que le convive donne le code de validation
                        à l’hôte, le paiement est libéré (moins la commission Tabiroo).
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-question">
                        Tabiroo peut-il garder mon argent sans que je mange ?
                        <span class="arrow">&#9662;</span>
                    </button>
                    <div class="faq-answer">
                        Non. Si le repas n’a pas lieu ou est annulé dans les délais prévus, le convive est remboursé
                        sur son moyen de paiement initial.
                    </div>
                </div>

                <hr>

                <h2>Convives – Annulations & Remboursements</h2>

                <div class="faq-item">
                    <button class="faq-question">
                        Puis-je annuler une réservation ?
                        <span class="arrow">&#9662;</span>
                    </button>
                    <div class="faq-answer">
                        Oui :<br>
                        • Jusqu’à 48h avant le repas → remboursement intégral.<br>
                        • Moins de 48h avant → pas de remboursement, sauf si l’hôte annule.
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-question">
                        Et si l’hôte annule ?
                        <span class="arrow">&#9662;</span>
                    </button>
                    <div class="faq-answer">
                        Le convive est intégralement remboursé.
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-question">
                        Après le repas, puis-je demander un remboursement ?
                        <span class="arrow">&#9662;</span>
                    </button>
                    <div class="faq-answer">
                        Non. Une fois la prestation validée par le code de confirmation, aucun remboursement n’est possible.
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-question">
                        Que se passe-t-il si je suis en retard ?
                        <span class="arrow">&#9662;</span>
                    </button>
                    <div class="faq-answer">
                        Prévenez votre hôte via l’application. Selon ses disponibilités, il pourra accepter un léger retard.
                        En cas d’absence de communication, la prestation peut être considérée comme réalisée.
                    </div>
                </div>

                <hr>

                <h2>Convives – Expérience du repas</h2>

                <div class="faq-item">
                    <button class="faq-question">
                        Puis-je emporter le reste du repas ?
                        <span class="arrow">&#9662;</span>
                    </button>
                    <div class="faq-answer">
                        Cela dépend de chaque hôte. Vous pouvez lui demander directement.
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-question">
                        Puis-je choisir mon hôte en fonction des avis laissés par d’autres convives ?
                        <span class="arrow">&#9662;</span>
                    </button>
                    <div class="faq-answer">
                        Oui, chaque hôte dispose d’une note globale et d’avis visibles sur son profil.
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-question">
                        Y a-t-il des repas adaptés à des régimes spécifiques (végétarien, halal, sans gluten, etc.) ?
                        <span class="arrow">&#9662;</span>
                    </button>
                    <div class="faq-answer">
                        Oui, certains hôtes précisent les régimes qu’ils prennent en compte. Vérifiez les filtres
                        lors de votre recherche.
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-question">
                        Puis-je offrir une expérience Tabiroo en cadeau ?
                        <span class="arrow">&#9662;</span>
                    </button>
                    <div class="faq-answer">
                        Oui, des cartes cadeaux utilisables directement dans l’appli seront prochainement disponibles.
                    </div>
                </div>

                <hr>

                <h2>Convives – Assistance & Confiance</h2>

                <div class="faq-item">
                    <button class="faq-question">
                        Comment signaler un problème ou une mauvaise expérience ?
                        <span class="arrow">&#9662;</span>
                    </button>
                    <div class="faq-answer">
                        Vous pouvez signaler un incident via l’appli ou écrire à <a href="mailto:contact@tabiroo.com">contact@tabiroo.com</a>.
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-question">
                        Puis-je laisser un avis après un repas ?
                        <span class="arrow">&#9662;</span>
                    </button>
                    <div class="faq-answer">
                        Oui, chaque convive peut noter et commenter la prestation. Ces avis sont publics.
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-question">
                        Tabiroo peut-il supprimer un avis ?
                        <span class="arrow">&#9662;</span>
                    </button>
                    <div class="faq-answer">
                        Oui, si l’avis est abusif, diffamatoire ou non pertinent.
                    </div>
                </div>

                <hr>

                <h2>Hôtes (Chefs / Particuliers) – Création & Gestion</h2>

                <div class="faq-item">
                    <button class="faq-question">
                        Comment créer mon menu et publier une prestation ?
                        <span class="arrow">&#9662;</span>
                    </button>
                    <div class="faq-answer">
                        Depuis l’appli hôte, ajoutez vos plats avec photos, descriptions, prix et disponibilités.
                        Une fois validé, votre menu sera visible des convives.
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-question">
                        Puis-je limiter le nombre de convives accueillis chez moi ?
                        <span class="arrow">&#9662;</span>
                    </button>
                    <div class="faq-answer">
                        Oui, vous fixez vous-même le nombre maximum de places par repas.
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-question">
                        Comment fixer le prix de mes repas ?
                        <span class="arrow">&#9662;</span>
                    </button>
                    <div class="faq-answer">
                        Vous choisissez librement votre tarif. Nous vous conseillons de rester compétitif et transparent
                        pour attirer des convives.
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-question">
                        Puis-je refuser une réservation ?
                        <span class="arrow">&#9662;</span>
                    </button>
                    <div class="faq-answer">
                        Oui, tant que vous n’avez pas confirmé. Une fois validée, vous devez honorer votre engagement.
                    </div>
                </div>

                <hr>

                <h2>Hôtes – Paiement & Annulations</h2>

                <div class="faq-item">
                    <button class="faq-question">
                        Quand et comment suis-je payé ?
                        <span class="arrow">&#9662;</span>
                    </button>
                    <div class="faq-answer">
                        Les paiements sont versés via Stripe après validation du code de prestation par le convive,
                        déduction faite de la commission Tabiroo.
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-question">
                        Puis-je annuler un repas si je ne peux pas l’assurer ?
                        <span class="arrow">&#9662;</span>
                    </button>
                    <div class="faq-answer">
                        Oui, mais vous devez le faire le plus tôt possible. Les convives seront remboursés intégralement.
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-question">
                        Que faire si un convive ne se présente pas ?
                        <span class="arrow">&#9662;</span>
                    </button>
                    <div class="faq-answer">
                        Si le convive ne vient pas et n’a pas annulé dans les délais, le repas est considéré comme dû.
                        Vous serez donc payé.
                    </div>
                </div>

                <hr>

                <h2>Hygiène, Données & Litiges</h2>

                <div class="faq-item">
                    <button class="faq-question">
                        Comment gérer les allergies et régimes spécifiques ?
                        <span class="arrow">&#9662;</span>
                    </button>
                    <div class="faq-answer">
                        Les hôtes doivent préciser les ingrédients et allergènes dans leurs plats.
                        Les convives doivent vérifier ces informations et poser leurs questions si besoin.
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-question">
                        Quelles données Tabiroo collecte-t-il et pourquoi ?
                        <span class="arrow">&#9662;</span>
                    </button>
                    <div class="faq-answer">
                        Tabiroo collecte notamment : nom, prénom, e‑mail, téléphone, informations de paiement (via Stripe),
                        afin de gérer les comptes, réservations, paiements et communications liées au service.
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-question">
                        Tabiroo revend-il mes données ?
                        <span class="arrow">&#9662;</span>
                    </button>
                    <div class="faq-answer">
                        Non. Tabiroo ne revend ni ne cède les données personnelles.
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-question">
                        Comment exercer mes droits RGPD ?
                        <span class="arrow">&#9662;</span>
                    </button>
                    <div class="faq-answer">
                        Vous pouvez demander l’accès, la rectification ou la suppression de vos données
                        en écrivant à <a href="mailto:contact@tabiroo.com">contact@tabiroo.com</a>.
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-question">
                        Comment Tabiroo gère-t-il les litiges ?
                        <span class="arrow">&#9662;</span>
                    </button>
                    <div class="faq-answer">
                        En cas de problème, contactez-nous via l’appli ou à <a href="mailto:contact@tabiroo.com">contact@tabiroo.com</a>.
                        Chaque situation est examinée individuellement, avec une recherche de solution amiable.
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-question">
                        Y a-t-il une assurance en cas de problème ?
                        <span class="arrow">&#9662;</span>
                    </button>
                    <div class="faq-answer">
                        Tabiroo n’est pas un assureur. Les hôtes sont responsables de leur prestation.
                        Une couverture adaptée est à l’étude pour l’avenir.
                    </div>
                </div>
            </div>
        </section>
        <div class="faq-apps-banner-wrapper">
            <section class="faq-apps-banner">
                <div class="apps-content">
                    <div class="apps-text">
                        <h2>Nos apps<br>Disponible sur</h2>
                        <div class="apps-links">
                            <a href="#"><img src="{{ asset('images/Google Play Badge.svg') }}" alt="PlayStore" width="150"></a>
                            <a href="#"><img src="{{ asset('images/App Store Badge.svg') }}" alt="AppStore" width="150"></a>
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
    <script src="{{ asset('js/main.js') }}"></script>
</body>
</html>
