# Guide de Test des Paiements Stripe avec Postman

## Configuration préalable

### 1. Variables d'environnement Postman
Créez les variables suivantes dans votre environnement Postman :
- `BASE_URL`: http://localhost:8000 (ou votre URL)
- `CLIENT_TOKEN`: Token d'authentification du client
- `CHEF_TOKEN`: Token d'authentification du chef
- `CHEF_EMAIL`: Email du chef pour créer son compte Stripe

### 2. Clés de test Stripe
Assurez-vous d'utiliser les clés de test Stripe dans votre `.env` :
```
STRIPE_SECRET=sk_test_...
STRIPE_PUBLIC=pk_test_...
STRIPE_WEBHOOK_SECRET=whsec_test_...
```

## Étape 1 : Créer un compte Stripe Connect pour un chef

### 1.1 Connexion du chef
**Endpoint:** `POST {{BASE_URL}}/api/auth/login`
**Body:**
```json
{
    "email": "chef@example.com",
    "password": "password"
}
```
**Sauvegardez le token retourné dans `CHEF_TOKEN`**

### 1.2 Créer le compte Stripe Connect
**Endpoint:** `POST {{BASE_URL}}/api/stripe/account`
**Headers:**
```
Authorization: Bearer {{CHEF_TOKEN}}
Content-Type: application/json
```
**Body:**
```json
{
    "email": "{{CHEF_EMAIL}}"
}
```
**Réponse attendue:**
```json
{
    "message": "Compte Stripe Connect créé et lié avec succès",
    "account_id": "acct_1234567890"
}
```

### 1.3 Générer le lien d'onboarding Stripe
**Endpoint:** `POST {{BASE_URL}}/api/stripe/account/link`
**Headers:**
```
Authorization: Bearer {{CHEF_TOKEN}}
Content-Type: application/json
```
**Body:**
```json
{
    "account_id": "acct_1234567890",
    "refresh_url": "{{BASE_URL}}/stripe/refresh",
    "return_url": "{{BASE_URL}}/stripe/return"
}
```
**Réponse:**
```json
{
    "url": "https://connect.stripe.com/setup/s/..."
}
```
**⚠️ Le chef doit visiter cette URL pour compléter son inscription Stripe**

## Étape 2 : Créer une réservation avec paiement

### 2.1 Connexion du client
**Endpoint:** `POST {{BASE_URL}}/api/auth/login`
**Body:**
```json
{
    "email": "client@example.com",
    "password": "password"
}
```
**Sauvegardez le token retourné dans `CLIENT_TOKEN`**

### 2.2 Créer la réservation et obtenir le client_secret
**Endpoint:** `POST {{BASE_URL}}/api/client/reservation/reserve-and-pay`
**Headers:**
```
Authorization: Bearer {{CLIENT_TOKEN}}
Content-Type: application/json
```
**Body:**
```json
{
    "menu_prestation_id": 1,
    "client_id": 2,
    "chef_id": 1,
    "sous_total": 45.00,
    "frais_service": 5.00,
    "nombre_convive": 2,
    "date_prestation": "2025-10-15",
    "choix": "oui",
    "chef_stripe_account_id": "acct_1234567890",
    "amount": 50.00
}
```
**Réponse:**
```json
{
    "status": true,
    "message": "Réservation initialisée, confirmer le paiement",
    "reservation": {
        "id": 123,
        "menu_prestation_id": 1,
        "client_id": 2,
        "chef_id": 1,
        "sous_total": "45.00",
        "frais_service": "5.00",
        "payment_intent_id": "pi_1234567890",
        "payment_distributed": false
    },
    "client_secret": "pi_1234567890_secret_abc123"
}
```
**⚠️ Sauvegardez le `reservation.id` et le `client_secret` pour l'étape suivante**

## Étape 3 : Confirmer le paiement côté client

### Option A : Dans l'application mobile
L'application mobile utilise le SDK Stripe pour confirmer le paiement avec la carte saisie par l'utilisateur.

### Option B : Utiliser Stripe CLI pour simuler un paiement (tests uniquement)
```bash
stripe payment_intents confirm pi_1234567890 \
  --payment-method=pm_card_visa \
  -k sk_test_...
```

### Option C : Via l'API Stripe directement (tests uniquement)
```bash
curl https://api.stripe.com/v1/payment_intents/pi_1234567890/confirm \
  -u sk_test_YOUR_KEY: \
  -d payment_method=pm_card_visa
```

## Étape 4 : Vérifier le statut du paiement

**Endpoint:** `GET {{BASE_URL}}/api/stripe/payment-intent/pi_1234567890/status`
**Headers:**
```
Authorization: Bearer {{CLIENT_TOKEN}}
```
**Réponse:**
```json
{
    "status": true,
    "payment_intent": {
        "id": "pi_1234567890",
        "status": "succeeded",
        "amount": 50,
        "currency": "eur",
        "metadata": {
            "chef_stripe_account_id": "acct_1234567890",
            "application_fee": "0"
        },
        "created": "2025-09-28 10:30:00"
    }
}
```

## Note : Distribution automatique

Avec le nouveau système de "destination charges", **la distribution est automatique** !
- Le paiement est directement versé au chef lors de la confirmation
- La commission est automatiquement prélevée
- Pas besoin d'appeler un endpoint de validation séparé

Les chefs peuvent voir leurs paiements immédiatement dans leur dashboard Stripe.

## Étape 6 : Vérifier les réservations

### 6.1 Lister les réservations du client
**Endpoint:** `GET {{BASE_URL}}/api/client/reservation/{{CLIENT_ID}}`
**Headers:**
```
Authorization: Bearer {{CLIENT_TOKEN}}
```

### 6.2 Lister les réservations du chef
**Endpoint:** `GET {{BASE_URL}}/api/client/reservation-chef/{{CHEF_ID}}`
**Headers:**
```
Authorization: Bearer {{CHEF_TOKEN}}
```

## Cas d'erreur à tester

### 1. Tenter de distribuer deux fois le même paiement
Appelez l'endpoint de validation deux fois avec le même ID de réservation.
**Erreur attendue:** "Le paiement a déjà été distribué"

### 2. Tenter de distribuer pour un chef sans compte Stripe
Créez une réservation pour un chef qui n'a pas de `stripe_account_id`.
**Erreur attendue:** "Le chef n'a pas de compte Stripe configuré"

### 3. Forcer la redistribution
Utilisez `"force": true` pour redistribuer un paiement déjà distribué.

## Collection Postman complète

Créez une collection Postman avec cette structure :
```
📁 Tabiroo Payment Tests
  📁 Setup
    ├─ 🔑 Login Chef
    ├─ 💳 Create Stripe Account
    └─ 🔗 Get Onboarding Link
  📁 Client Flow
    ├─ 🔑 Login Client
    ├─ 🛒 Create Reservation & Payment
    └─ ✅ Confirm Payment (Test)
  📁 Payment Distribution
    ├─ 📊 Check Payment Status
    └─ 💰 Validate & Distribute Payment
  📁 Verification
    ├─ 📋 List Client Reservations
    └─ 📋 List Chef Reservations
```

## Webhook local pour développement

Pour tester les webhooks en local, utilisez Stripe CLI :
```bash
# Installer Stripe CLI
brew install stripe/stripe-cli/stripe

# Se connecter
stripe login

# Rediriger les webhooks vers votre serveur local
stripe listen --forward-to localhost:8000/api/stripe/webhook

# Déclencher des événements de test
stripe trigger payment_intent.succeeded
```

## Notes importantes

1. **Cartes de test Stripe:**
   - Succès: `4242 4242 4242 4242`
   - Refus: `4000 0000 0000 0002`
   - Authentication requise: `4000 0025 0000 3155`

2. **Montants:** Tous les montants sont en euros dans l'API

3. **Sécurité:** En production, n'exposez jamais les endpoints de test

4. **Logs:** Vérifiez `storage/logs/laravel.log` pour déboguer

5. **Dashboard Stripe:** Consultez https://dashboard.stripe.com/test pour voir tous les paiements et transferts

## Tester le paiement dans l'app mobile

### Flow de paiement optimisé

1. **Sans carte enregistrée:**
   - L'utilisateur sera redirigé vers le modal de paiement
   - Saisir les informations de carte de test (4242 4242 4242 4242)
   - Le paiement sera traité automatiquement
   - La carte peut être sauvegardée pour les prochains paiements

2. **Avec carte enregistrée:**
   - Le paiement sera automatiquement traité avec la carte par défaut
   - En cas d'échec, l'utilisateur pourra saisir une nouvelle carte
   - Possibilité de gérer les cartes enregistrées (max 3)

3. **Gestion des erreurs:**
   - **Carte refusée** : Message d'erreur spécifique avec possibilité d'essayer une autre carte
   - **Fonds insuffisants** : Message adapté pour informer l'utilisateur
   - **Erreur réseau** : Possibilité de réessayer avec le même payment intent
   - **Session expirée** : L'utilisateur devra recommencer la réservation

4. **Sécurité:**
   - Les réservations échouées sont automatiquement annulées
   - Le client_secret n'est valide que pour une session
   - Validation côté serveur de tous les montants
