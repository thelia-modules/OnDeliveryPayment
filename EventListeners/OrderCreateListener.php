<?php

declare(strict_types=1);

namespace OnDeliveryPayment\EventListeners;

use OnDeliveryPayment\OnDeliveryPayment;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Thelia\Core\Event\Order\OrderEvent;
use Thelia\Core\Event\Order\OrderManualEvent;
use Thelia\Core\Event\TheliaEvents;
use Thelia\Model\Order;
use Thelia\Model\OrderStatus;
use Thelia\Model\OrderStatusQuery;

#[AsEventListener(event: TheliaEvents::ORDER_PAY, method: 'updateOrderStatus', priority: 100)]
#[AsEventListener(event: TheliaEvents::ORDER_CREATE_MANUAL, method: 'updateOrderStatusManual', priority: 100)]
class OrderCreateListener
{
    public function updateOrderStatus(OrderEvent $event): void
    {
        $this->updateStatus($event->getPlacedOrder());
    }

    public function updateOrderStatusManual(OrderManualEvent $event): void
    {
        $this->updateStatus($event->getOrder());
    }

    private function updateStatus(?Order $order): void
    {
        if (!$order instanceof Order) {
            return;
        }

        if ($order->getOrderStatus()?->getCode() !== OrderStatus::CODE_NOT_PAID) {
            return;
        }

        if ($order->getPaymentModuleInstance()?->getCode() !== 'OnDeliveryPayment') {
            return;
        }

        $newStatus = OrderStatusQuery::create()
            ->filterByCode(OnDeliveryPayment::ORDER_STATUS_ON_DELIVERY_PAYMENT_PAID)
            ->findOne();

        if (null === $newStatus) {
            return;
        }

        $order->setOrderStatus($newStatus);
        $order->save();
    }
}
