<?php
namespace Vendor\CustomOrderProcessing\Model;

use Vendor\CustomOrderProcessing\Api\OrderStatusInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Webapi\Exception as WebapiException;

class OrderStatus implements OrderStatusInterface
{
    protected $orderRepository;

    public function __construct(OrderRepositoryInterface $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    public function updateStatus($orderIncrementId, $newStatus)
    {
        $order = $this->orderRepository->get($orderIncrementId);
        if (!$order->getId()) {
            throw new WebapiException(__('Order not found'), 404);
        }

        if (!$order->canCancel()) {
            throw new LocalizedException(__('Order status transition is not allowed.'));
        }

        $order->setStatus($newStatus);
        $this->orderRepository->save($order);

        return __('Order status updated successfully.');
    }
}