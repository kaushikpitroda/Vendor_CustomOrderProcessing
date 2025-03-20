# Vendor_CustomOrderProcessing

## Overview
`Vendor_CustomOrderProcessing` is a Magento 2 module that listens for order status changes and logs them into a custom database table. Additionally, when an order is marked as **shipped**, the module triggers an email notification to the customer.

## Features
- Listens for order status changes using the `sales_order_save_after` event.
- Logs order status changes (old status, new status, timestamp) into the `custom_order_status_log` table.
- Sends a shipment notification email to the customer when the order is marked as **Complete**.

## Installation
### 1. Enable the Module
```sh
bin/magento module:enable Vendor_CustomOrderProcessing
```

### 2. Run Setup Upgrade
```sh
bin/magento setup:upgrade
```

### 3. Clear Cache
```sh
bin/magento cache:flush
```

## Configuration
- The module automatically logs order status changes.
- Ensure that an email template with the identifier `order_shipped_template` exists in **Marketing > Email Templates**.

## Database Schema
The module creates a custom table `custom_order_status_log` with the following fields:

| Column     | Type      | Description |
|------------|----------|-------------|
| id         | int      | Primary Key |
| order_id   | int      | Magento Order ID |
| old_status | text     | Previous Order Status |
| new_status | text     | Updated Order Status |
| timestamp  | datetime | Status Change Timestamp |

## Event Observer
The module listens for the `sales_order_save_after` event and triggers actions accordingly.

## Testing
1. Place an order in Magento.
2. Change its status from **Processing → Complete** in the admin panel.
3. Check the `custom_order_status_log` table for the entry.
4. Verify that the customer receives the shipment email.

## Uninstallation
If you need to remove the module, disable it and remove its files:
```sh
bin/magento module:disable Vendor_CustomOrderProcessing
rm -rf app/code/Vendor/CustomOrderProcessing/
```

