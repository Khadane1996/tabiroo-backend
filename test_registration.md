# Test du Nouveau Flow d'Inscription avec Cache

## Aperçu des Améliorations

### Avant (Ancienne logique)
1. Utilisateur s'inscrit → Création immédiate en base avec `etat = 0`
2. Création des tables `Adresse` et `Bancaire` 
3. Envoi OTP
4. Vérification OTP → `etat = 1`

**Problèmes :**
- Utilisateurs non confirmés s'accumulent en base
- Tables relationnelles créées inutilement
- Pas de gestion d'expiration des inscriptions

### Après (Nouvelle logique avec cache)
1. Utilisateur s'inscrit → Stockage temporaire dans le cache (15min)
2. Envoi OTP
3. Vérification OTP → Création en base + tables relationnelles

**Avantages :**
- Base de données propre (pas d'utilisateurs non confirmés)
- Expiration automatique (15 minutes)
- Possibilité de renvoyer l'OTP
- Transactions atomiques pour la création
- Meilleure sécurité

## Nouvelles Routes API

### 1. Inscription (modifiée)
```
POST /api/register
```

**Body:**
```json
{
    "firstNameOrPseudo": "John",
    "lastName": "Doe", 
    "email": "john@example.com",
    "phone": "+221771234567",
    "role_id": 2,
    "password": "password123"
}
```

**Réponse:**
```json
{
    "status": true,
    "message": "Code de confirmation envoyé. Veuillez vérifier votre email",
    "cache_key": "registration_john@example.com_1678123456"
}
```

### 2. Vérification OTP (modifiée)
```
POST /api/opt
```

**Body (option 1 - avec cache_key):**
```json
{
    "cache_key": "registration_john@example.com_1678123456",
    "confirmation_code": "1234"
}
```

**Body (option 2 - avec email/phone):**
```json
{
    "email": "john@example.com",
    "confirmation_code": "1234"
}
```

**Réponse:**
```json
{
    "status": true,
    "message": "Compte confirmé et créé avec succès.",
    "user": {
        "id": 1,
        "firstNameOrPseudo": "John",
        "lastName": "Doe",
        "email": "john@example.com",
        "phone": "+221771234567"
    }
}
```

### 3. Renvoyer OTP (nouvelle)
```
POST /api/resend-otp
```

**Body:**
```json
{
    "cache_key": "registration_john@example.com_1678123456"
}
```

### 4. Vérifier statut inscription (nouvelle)
```
POST /api/check-registration-status
```

**Body:**
```json
{
    "cache_key": "registration_john@example.com_1678123456"
}
```

## Test Manual

### Étape 1: Inscription
```bash
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "firstNameOrPseudo": "Test User",
    "email": "test@example.com",
    "role_id": 2,
    "password": "password123"
  }'
```

### Étape 2: Vérifier que l'utilisateur n'est pas en base
```sql
SELECT * FROM users WHERE email = 'test@example.com';
-- Doit retourner 0 résultat
```

### Étape 3: Vérification OTP
```bash
curl -X POST http://localhost:8000/api/opt \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "confirmation_code": "1234"
  }'
```

### Étape 4: Vérifier que l'utilisateur est maintenant en base
```sql
SELECT * FROM users WHERE email = 'test@example.com';
-- Doit retourner l'utilisateur avec etat = 1
```

## Gestion des Cas d'Erreur

### Session expirée (15min)
```json
{
    "status": false,
    "message": "Code de confirmation incorrect ou expiré."
}
```

### Code OTP incorrect
```json
{
    "status": false,
    "message": "Code de confirmation incorrect ou expiré."
}
```

### Utilisateur déjà existant
```json
{
    "status": false,
    "message": "Un compte existe déjà avec ces informations"
}
```

## Configuration du Cache

Le cache est configuré dans `config/cache.php` :
- Driver par défaut: `database`
- TTL: 15 minutes
- Clés de cache: `registration_{email|phone}_{timestamp}`

## Bonnes Pratiques Implémentées

1. **Validation renforcée** - Règles de validation plus strictes
2. **Transactions atomiques** - Création utilisateur + relations en une transaction
3. **Gestion d'erreurs** - Try-catch complets avec messages explicites
4. **Cache TTL** - Expiration automatique des données temporaires
5. **Codes HTTP appropriés** - 422 pour validation, 409 pour conflit, etc.
6. **Sécurité** - Pas d'exposition de données sensibles
7. **Extensibilité** - Méthodes pour renvoyer OTP et vérifier statut

## Migration depuis l'Ancien Système

L'ancien système reste compatible. Les utilisateurs avec `etat = 0` peuvent être nettoyés avec une commande Artisan :

```php
// Commande à créer pour nettoyer les anciens utilisateurs non confirmés
User::where('etat', 0)
    ->where('created_at', '<', now()->subDays(1))
    ->delete();
```