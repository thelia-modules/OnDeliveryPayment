# Paiement à la livraison (OnDeliveryPayment)

Module de paiement Thelia 3 permettant de proposer l'option **paiement à la livraison** : le règlement est encaissé par le livreur au moment de la remise du colis.

## Compatibilité

- Thelia >= 3.0.0
- PHP >= 8.3

## Installation

### Via Composer (recommandé)

```bash
composer require thelia/on-delivery-payment-module
```

### Manuel

Copier le dossier `OnDeliveryPayment` dans `local/modules/` de votre installation Thelia.

### Activation

Depuis le back-office Thelia : **Modules → Paiement à la livraison → Activer**.

À l'activation, le module crée automatiquement un statut de commande dédié :

| Code | Libellé | Couleur |
|---|---|---|
| `on_delivery_payment_paid` | Paiement à la livraison | `#10b981` |

Ce statut est marqué `protected` afin de ne pas être supprimé accidentellement.

## Fonctionnement

1. À la finalisation de la commande, le client sélectionne « Paiement à la livraison » comme moyen de paiement.
2. Le module valide la commande sans transaction monétique.
3. Le listener `OrderCreateListener` repositionne automatiquement la commande sur le statut `on_delivery_payment_paid` :
   - sur l'événement `ORDER_PAY` (parcours client classique),
   - sur l'événement `ORDER_CREATE_MANUAL` (création depuis le back-office).
4. La gestion de stock à la création de commande est désactivée (`manageStockOnCreation()` retourne `false`).

## Configuration

Aucune configuration n'est requise. Le module est opérationnel dès son activation.

Vous pouvez restreindre la disponibilité du module via la gestion standard Thelia :

- pays autorisés
- zones de livraison
- montants minimum / maximum

## Désinstallation

Depuis le back-office : **Modules → Paiement à la livraison → Désactiver**, puis supprimer le dossier `local/modules/OnDeliveryPayment` si souhaité.

> Le statut de commande `on_delivery_payment_paid` n'est pas supprimé automatiquement pour préserver l'historique des commandes existantes.

## Licence

GPL-3.0-or-later

## Auteur

- mdevaud — <mdevaud@openstudio.fr>