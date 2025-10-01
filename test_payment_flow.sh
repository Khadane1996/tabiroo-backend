#!/bin/bash

# Script de test du flux de paiement Tabiroo
# Utilisation: ./test_payment_flow.sh

# Configuration
BASE_URL="http://localhost:8000"
CHEF_EMAIL="chef@example.com"
CHEF_PASSWORD="password"
CLIENT_EMAIL="client@example.com"
CLIENT_PASSWORD="password"

# Couleurs pour l'affichage
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo -e "${YELLOW}=== Test du système de paiement Tabiroo ===${NC}\n"

# Fonction pour faire une requête API
api_request() {
    local method=$1
    local endpoint=$2
    local data=$3
    local token=$4
    
    if [ -z "$token" ]; then
        curl -s -X $method \
            -H "Content-Type: application/json" \
            -d "$data" \
            "$BASE_URL$endpoint"
    else
        curl -s -X $method \
            -H "Content-Type: application/json" \
            -H "Authorization: Bearer $token" \
            -d "$data" \
            "$BASE_URL$endpoint"
    fi
}

# 1. Login Chef
echo -e "${GREEN}1. Connexion du chef...${NC}"
CHEF_RESPONSE=$(api_request POST "/api/auth/login" "{\"email\":\"$CHEF_EMAIL\",\"password\":\"$CHEF_PASSWORD\"}")
CHEF_TOKEN=$(echo $CHEF_RESPONSE | jq -r '.token')
CHEF_ID=$(echo $CHEF_RESPONSE | jq -r '.user.id')

if [ "$CHEF_TOKEN" == "null" ]; then
    echo -e "${RED}Erreur: Impossible de connecter le chef${NC}"
    exit 1
fi
echo "Chef connecté. ID: $CHEF_ID"

# 2. Créer compte Stripe (si nécessaire)
echo -e "\n${GREEN}2. Vérification du compte Stripe...${NC}"
STRIPE_STATUS=$(api_request GET "/api/stripe/status" "" "$CHEF_TOKEN")
ACCOUNT_STATUS=$(echo $STRIPE_STATUS | jq -r '.status')

if [ "$ACCOUNT_STATUS" == "no_account" ]; then
    echo "Création du compte Stripe..."
    ACCOUNT_RESPONSE=$(api_request POST "/api/stripe/account" "{\"email\":\"$CHEF_EMAIL\"}" "$CHEF_TOKEN")
    STRIPE_ACCOUNT_ID=$(echo $ACCOUNT_RESPONSE | jq -r '.account_id')
    echo "Compte Stripe créé: $STRIPE_ACCOUNT_ID"
    
    # Générer lien d'onboarding
    LINK_RESPONSE=$(api_request POST "/api/stripe/account/link" \
        "{\"account_id\":\"$STRIPE_ACCOUNT_ID\",\"refresh_url\":\"$BASE_URL/stripe/refresh\",\"return_url\":\"$BASE_URL/stripe/return\"}" \
        "$CHEF_TOKEN")
    ONBOARDING_URL=$(echo $LINK_RESPONSE | jq -r '.url')
    echo -e "${YELLOW}⚠️  Le chef doit compléter son inscription Stripe à: $ONBOARDING_URL${NC}"
else
    STRIPE_ACCOUNT_ID=$(echo $STRIPE_STATUS | jq -r '.id')
    echo "Compte Stripe existant: $STRIPE_ACCOUNT_ID"
fi

# 3. Login Client
echo -e "\n${GREEN}3. Connexion du client...${NC}"
CLIENT_RESPONSE=$(api_request POST "/api/auth/login" "{\"email\":\"$CLIENT_EMAIL\",\"password\":\"$CLIENT_PASSWORD\"}")
CLIENT_TOKEN=$(echo $CLIENT_RESPONSE | jq -r '.token')
CLIENT_ID=$(echo $CLIENT_RESPONSE | jq -r '.user.id')

if [ "$CLIENT_TOKEN" == "null" ]; then
    echo -e "${RED}Erreur: Impossible de connecter le client${NC}"
    exit 1
fi
echo "Client connecté. ID: $CLIENT_ID"

# 4. Créer réservation avec paiement
echo -e "\n${GREEN}4. Création de la réservation et du paiement...${NC}"
RESERVATION_DATA="{
    \"menu_prestation_id\": 1,
    \"client_id\": $CLIENT_ID,
    \"chef_id\": $CHEF_ID,
    \"sous_total\": 45.00,
    \"frais_service\": 5.00,
    \"nombre_convive\": 2,
    \"date_prestation\": \"$(date -d '+30 days' '+%Y-%m-%d' 2>/dev/null || date -v '+30d' '+%Y-%m-%d')\",
    \"choix\": \"oui\",
    \"chef_stripe_account_id\": \"$STRIPE_ACCOUNT_ID\",
    \"amount\": 50.00
}"

RESERVATION_RESPONSE=$(api_request POST "/api/client/reservation/reserve-and-pay" "$RESERVATION_DATA" "$CLIENT_TOKEN")
RESERVATION_ID=$(echo $RESERVATION_RESPONSE | jq -r '.reservation.id')
CLIENT_SECRET=$(echo $RESERVATION_RESPONSE | jq -r '.client_secret')
PAYMENT_INTENT_ID=$(echo $RESERVATION_RESPONSE | jq -r '.reservation.payment_intent_id')

if [ "$RESERVATION_ID" == "null" ]; then
    echo -e "${RED}Erreur lors de la création de la réservation:${NC}"
    echo $RESERVATION_RESPONSE | jq '.'
    exit 1
fi

echo "Réservation créée. ID: $RESERVATION_ID"
echo "Payment Intent ID: $PAYMENT_INTENT_ID"

# 5. Simuler le paiement avec Stripe CLI
echo -e "\n${GREEN}5. Simulation du paiement...${NC}"
echo -e "${YELLOW}Pour confirmer le paiement, utilisez Stripe CLI:${NC}"
echo "stripe payment_intents confirm $PAYMENT_INTENT_ID --payment-method=pm_card_visa"
echo -e "${YELLOW}Ou utilisez l'app mobile pour effectuer un vrai paiement${NC}"
echo ""
echo -e "${YELLOW}Appuyez sur Entrée une fois le paiement confirmé...${NC}"
read -r

# 6. Vérifier le statut du paiement
echo -e "\n${GREEN}6. Vérification du statut du paiement...${NC}"
STATUS_RESPONSE=$(api_request GET "/api/stripe/payment-intent/$PAYMENT_INTENT_ID/status" "" "$CLIENT_TOKEN")
echo "Statut: $(echo $STATUS_RESPONSE | jq -r '.payment_intent.status')"
echo "Montant: $(echo $STATUS_RESPONSE | jq -r '.payment_intent.amount')€"

# 7. Distribuer le paiement au chef
echo -e "\n${GREEN}7. Distribution du paiement au chef...${NC}"
DISTRIBUTE_RESPONSE=$(api_request POST "/api/client/reservation/$RESERVATION_ID/validate-payment" "{\"force\":false}" "$CLIENT_TOKEN")
DISTRIBUTE_STATUS=$(echo $DISTRIBUTE_RESPONSE | jq -r '.status')

if [ "$DISTRIBUTE_STATUS" != "true" ]; then
    echo -e "${RED}Erreur lors de la distribution:${NC}"
    echo $DISTRIBUTE_RESPONSE | jq '.'
    exit 1
fi

echo "Paiement distribué avec succès!"
echo "Transfer ID: $(echo $DISTRIBUTE_RESPONSE | jq -r '.data.transfer_id')"
echo "Montant chef: $(echo $DISTRIBUTE_RESPONSE | jq -r '.data.chef_amount')€"
echo "Commission app: $(echo $DISTRIBUTE_RESPONSE | jq -r '.data.commission_amount')€"

echo -e "\n${GREEN}✅ Test terminé avec succès!${NC}"
echo -e "${YELLOW}Note: Vérifiez le dashboard Stripe pour voir les transactions${NC}"
