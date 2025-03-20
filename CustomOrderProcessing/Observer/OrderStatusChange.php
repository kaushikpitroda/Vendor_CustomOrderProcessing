<?php
namespace Vendor\CustomOrderProcessing\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\App\ResourceConnection;
use Psr\Log\LoggerInterface;
use Magento\Sales\Model\Order;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Store\Model\StoreManagerInterface;

class OrderStatusChange implements ObserverInterface
{
    protected $resource;
    protected $logger;
    protected $transportBuilder;
    protected $storeManager;

    public function __construct(
        ResourceConnection $resource,
        LoggerInterface $logger,
        TransportBuilder $transportBuilder,
        StoreManagerInterface $storeManager
    ) {
        $this->resource = $resource;
        $this->logger = $logger;
        $this->transportBuilder = $transportBuilder;
        $this->storeManager = $storeManager;
    }

    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $oldStatus = $order->getOrigData('status');
        $newStatus = $order->getStatus();
        $orderId = $order->getId();
        
        if ($oldStatus !== $newStatus) {
            $connection = $this->resource->getConnection();
            $tableName = $this->resource->getTableName('custom_order_status_log');
            
            $data = [
                'order_id' => $orderId,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'timestamp' => (new \DateTime())->format('Y-m-d H:i:s')
            ];
            
            $connection->insert($tableName, $data);
            $this->logger->info("Order #$orderId status changed from $oldStatus to $newStatus");
        }

        if ($newStatus === Order::STATE_COMPLETE) {
            $this->sendShipmentEmail($order);
        }
    }

    protected function sendShipmentEmail(Order $order)
    {
        try {
            $customerEmail = $order->getCustomerEmail();
            $customerName = $order->getCustomerFirstname();
            $store = $this->storeManager->getStore();
            
            $transport = $this->transportBuilder
                ->setTemplateIdentifier('order_shipped_template')
                ->setTemplateOptions([
                    'area' => 'frontend',
                    'store' => $store->getId()
                ])
                ->setTemplateVars(['order' => $order])
                ->setFromByScope('general')
                ->addTo($customerEmail, $customerName)
                ->getTransport();

            $transport->sendMessage();
            $this->logger->info("Shipment email sent to $customerEmail for order #{$order->getId()}");
        } catch (\Exception $e) {
            $this->logger->error("Failed to send shipment email: " . $e->getMessage());
        }
    }
}