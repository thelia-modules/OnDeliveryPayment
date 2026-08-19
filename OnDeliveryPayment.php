<?php

declare(strict_types=1);

namespace OnDeliveryPayment;

use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\Connection\ConnectionInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ServicesConfigurator;
use Thelia\Model\Order;
use Thelia\Model\OrderStatus;
use Thelia\Model\OrderStatusQuery;
use Thelia\Module\AbstractPaymentModule;
use Symfony\Component\HttpFoundation\Response;

class OnDeliveryPayment extends AbstractPaymentModule
{
    public const string DOMAIN_NAME = 'ondeliverypayment';
    public const string MESSAGE_DOMAIN = 'ondeliverypayment';
    public const string ORDER_STATUS_ON_DELIVERY_PAYMENT_PAID = 'on_delivery_payment_paid';

    public static function configureServices(ServicesConfigurator $servicesConfigurator): void
    {
        $servicesConfigurator->load(self::getModuleCode().'\\', __DIR__)
            ->exclude([
                __DIR__.'/I18n',
                __DIR__.'/Config',
                __DIR__.'/Tests',
                __FILE__,
            ])
            ->autowire(true)
            ->autoconfigure(true);
    }

    public function pay(Order $order): ?Response
    {
        // Paiement sans passerelle : aucune redirection, le tunnel se poursuit.
        return null;
    }

    public function isValidPayment(): bool
    {
        return $this->getCurrentOrderTotalAmount() > 0;
    }

    public function postActivation(?ConnectionInterface $con = null): void
    {
        // Garde d'idempotence. createSpecialStatut() porte deja son propre
        // early-return sur le code du statut : la garde est donc un chemin rapide,
        // pas une correction de bug.
        if (self::getConfigValue('is_initialized', false)) {
            return;
        }

        $this->createSpecialStatut();

        self::setConfigValue('is_initialized', true);
    }

    public function manageStockOnCreation(): bool
    {
        return false;
    }

    private function createSpecialStatut(): void
    {
        if (OrderStatusQuery::create()->filterByCode(self::ORDER_STATUS_ON_DELIVERY_PAYMENT_PAID)->findOne()) {
            return;
        }

        $lastPosition = OrderStatusQuery::create()
            ->orderByPosition(Criteria::DESC)
            ->findOne()
            ?->getPosition() ?? 0;

        $orderStatus = new OrderStatus();
        $orderStatus
            ->setLocale('fr_FR')
            ->setCode(self::ORDER_STATUS_ON_DELIVERY_PAYMENT_PAID)
            ->setTitle('Paiement à la livraison')
            ->setColor('#10b981')
            ->setPosition($lastPosition + 1)
            ->setProtectedStatus(1)
            ->save();
    }
}
