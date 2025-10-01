# Système de Paiement Simplifié avec Destination Charges

## Vue d'ensemble

Le système de paiement a été simplifié pour utiliser les "destination charges" de Stripe. Avec cette approche :
- Les paiements sont **directement versés** au compte du chef
- La commission de l'application est **automatiquement prélevée**
- **Pas besoin** d'endpoint de validation séparé
- Les chefs voient **immédiatement** les transactions dans leur dashboard

## Comment ça fonctionne

### 1. Création de la réservation et du paiement

```php
// Dans ReservationController::reserveAndPay
$pi = $this->stripe->createPaymentIntentWithDestination(
    $request->amount,           // Montant total
    'eur',                      // Devise
    $chef->stripe_account_id,   // Compte du chef
    $request->frais_service     // Commission de l'app
);
```

### 2. Flow du paiement

1. **Client paie** → L'argent va directement au chef
2. **Commission automatique** → Stripe prélève automatiquement votre commission
3. **Pas de transfert manuel** → Tout est automatique

## Configuration requise

### Pour les chefs

1. Avoir un compte Stripe Connect Express actif
2. Avoir complété l'onboarding Stripe
3. Avoir `charges_enabled` et `payouts_enabled` à true

### Vérifier le statut d'un chef

```bash
curl -X GET "{{BASE_URL}}/api/stripe/status" \
  -H "Authorization: Bearer {{CHEF_TOKEN}}"
```

## Endpoints API

### 1. Créer un compte Stripe pour un chef

```bash
POST /api/stripe/account
Headers: Authorization: Bearer {{CHEF_TOKEN}}
Body: {
    "email": "chef@example.com"
}
```

### 2. Obtenir le lien d'onboarding

```bash
POST /api/stripe/account/link
Headers: Authorization: Bearer {{CHEF_TOKEN}}
Body: {
    "account_id": "acct_xxxxx",
    "refresh_url": "{{BASE_URL}}/stripe/refresh",
    "return_url": "{{BASE_URL}}/stripe/return"
}
```

### 3. Créer une réservation avec paiement

```bash
POST /api/client/reservation/reserve-and-pay
Headers: Authorization: Bearer {{CLIENT_TOKEN}}
Body: {
    "menu_prestation_id": 1,
    "client_id": 2,
    "chef_id": 1,
    "sous_total": 45.00,
    "frais_service": 5.00,
    "nombre_convive": 2,
    "date_prestation": "2025-10-15",
    "choix": "oui",
    "chef_stripe_account_id": "acct_xxxxx",
    "amount": 50.00
}
```

### 4. Vérifier le statut d'un paiement

```bash
GET /api/stripe/payment-intent/{id}/status
Headers: Authorization: Bearer {{TOKEN}}
```

## Avantages du nouveau système

1. **Simplicité** : Un seul flow de paiement
2. **Transparence** : Les chefs voient immédiatement leurs paiements
3. **Automatisation** : Pas besoin de gérer manuellement les transferts
4. **Sécurité** : Stripe gère toute la complexité

## Migration depuis l'ancien système

Si vous aviez l'ancien système avec distribution différée :

1. Exécuter la migration pour nettoyer la base de données :
   ```bash
   php artisan migrate
   ```

2. Les champs suivants ont été supprimés :
   - `payment_distributed`
   - `transfer_id`
   - `distributed_at`

## Test avec Postman

Utilisez la collection `Tabiroo_Payment_Tests.postman_collection.json` mise à jour.

### Flow de test complet

1. **Login chef** → Récupérer le token
2. **Créer compte Stripe** (si nécessaire)
3. **Login client** → Récupérer le token
4. **Créer réservation** → Le paiement est directement versé au chef

## FAQ

### Q: Où les chefs voient-ils leurs paiements ?
R: Dans leur dashboard Stripe Express, accessible via le lien généré par l'endpoint `/api/stripe/account/link`

### Q: Comment modifier la commission ?
R: La commission est définie dans `frais_service` lors de la création de la réservation

### Q: Que se passe-t-il si le paiement échoue ?
R: La réservation est automatiquement annulée via l'endpoint `cancel-on-payment-fail`

## Support

Pour toute question sur le système de paiement :
1. Vérifiez le dashboard Stripe
2. Consultez les logs Laravel
3. Contactez le support Stripe pour les questions spécifiques aux comptes Connect
