<?php
namespace Vendor\CustomOrderProcessing\Api;

interface OrderStatusInterface
{
    /**
     * Update order status via API
     *
     * @param string $orderIncrementId
     * @param string $newStatus
     * @return string
     */
    public function updateStatus($orderIncrementId, $newStatus);
}