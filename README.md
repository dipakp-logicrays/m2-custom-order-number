# Learning_CustomOrderNumber

Custom Order Number extension for Magento 2 that allows store owners to customize numbering formats for orders, invoices, shipments, and credit memos with flexible configuration options.

## Overview

The Custom Order Number extension provides complete control over the numbering system for all sales entities in your Magento 2 store. Replace Magento's default numbering with customized formats that align with your business requirements and branding. Support for orders, invoices, shipments, and credit memos with independent or shared numbering configurations.

## Features

### Core Features
- **Custom Order Number Format** - Define your own order number pattern using variables
- **Custom Invoice Number Format** - Use same as order or configure separate invoice format
- **Custom Shipment Number Format** - Use same as order or configure separate shipment format
- **Custom Credit Memo Number Format** - Use same as order or configure separate credit memo format
- **Store-Specific Counters** - Each store view has independent counter management
- **Flexible Variables** - Use date and counter variables in your format
- **Counter Padding** - Add leading zeros for consistent number length
- **Increment Step** - Customize the increment value for sequential numbering
- **Date-Based Reset** - Automatically reset counters daily, monthly, or yearly
- **Format Validation** - Validates format pattern on configuration save
- **Database Locking** - Prevents duplicate numbers in high-concurrency scenarios
- **Multiple Entity Support** - Handles multiple invoices/shipments/credit memos per order with unique suffixes
- **New Entities Only** - No impact on existing orders, invoices, shipments, or credit memos

## Compatibility

- **Magento Versions**: 2.4.4, 2.4.5, 2.4.6, 2.4.7, 2.4.8
- **PHP Version**: 8.1+
- **Database**: MySQL 8.0+

## Installation

### Method 1: Manual Installation

1. Create the module directory:
```bash
mkdir -p app/code/Learning/CustomOrderNumber
```

2. Copy the extension files to `app/code/Learning/CustomOrderNumber`

3. Enable the module:
```bash
bin/magento module:enable Learning_CustomOrderNumber
bin/magento setup:upgrade
bin/magento cache:clean
```

4. Compile and deploy (production mode):
```bash
bin/magento setup:di:compile
bin/magento setup:static-content:deploy -f
```

## Configuration

### Admin Configuration Path

Navigate to: **Stores > Configuration > Learning Modules > Custom Order Number**

### General Settings

**Enable Module**
- Enable/Disable the custom order number functionality
- Scope: Store View
- Default: No
- **Note:** This setting controls all entity types (orders, invoices, shipments, credit memos)

---

## Order Configuration

### Custom Order Number Format
- Define the order number pattern using variables
- **Required variable:** `{counter}` - must be present in the format
- **Optional variables:**
  - `{yyyy}` - 4-digit year (e.g., 2025)
  - `{yy}` - 2-digit year (e.g., 25)
  - `{mm}` - 2-digit month (e.g., 01)
  - `{dd}` - 2-digit day (e.g., 15)
- **Example:** `ORD-{yy}-{mm}-{dd}-{counter}`
- **Result:** `ORD-25-01-15-000001`
- Scope: Store View
- Default: `ORD-{yy}-{mm}-{dd}-{counter}`

### Start Counter From
- The initial counter value for new orders
- Use different starting values for different stores (e.g., 1000001 for Store 1, 2000001 for Store 2)
- Scope: Store View
- Default: 1
- Validation: Must be a positive integer

### Counter Increment Step
- The increment value added to the counter for each new order
- Example: If last counter is 1000001 and step is 4, next will be 1000005
- Scope: Store View
- Default: 1
- Validation: Must be a positive integer

### Counter Padding
- Total number of digits in the counter (with leading zeros)
- Example: Counter 24 with padding 6 = 000024
- Set 0 to disable padding
- Scope: Store View
- Default: 6
- Validation: Must be zero or greater

### Reset Counter on Date Change
- Automatically reset counter based on time period
- Options:
  - **No** - Counter never resets automatically
  - **Daily** - Reset at the start of each day
  - **Monthly** - Reset at the start of each month
  - **Yearly** - Reset at the start of each year
- When reset occurs, counter returns to "Start Counter From" value
- Scope: Store View
- Default: No

---

## Invoice Configuration

### Same as Order Number
- **Yes** - Invoice will use the exact same number as the order
  - Example: Order `ORD-25-10-30-000001` → Invoice `ORD-25-10-30-000001`
  - **Multiple Invoices:** When creating multiple invoices for the same order, suffixes are automatically added:
    - First invoice: `ORD-25-10-30-000001`
    - Second invoice: `ORD-25-10-30-000001-1`
    - Third invoice: `ORD-25-10-30-000001-2`
- **No** - Invoice will use its own format and counter configuration
- Scope: Store View
- Default: Yes

### Custom Invoice Number Format (when "Same as Order" is No)
- Define the invoice number pattern using variables
- **Required variable:** `{counter}` - must be present in the format
- **Optional variables:** `{yyyy}`, `{yy}`, `{mm}`, `{dd}`
- **Example:** `INV-{yy}-{mm}-{dd}-{counter}`
- **Result:** `INV-25-10-30-000001`
- Scope: Store View
- Default: `INV-{yy}-{mm}-{dd}-{counter}`
- **Note:** Only visible when "Same as Order Number" is set to "No"

### Start Counter From (Invoice)
- The initial counter value for new invoices
- Only used when "Same as Order Number" is "No"
- Scope: Store View
- Default: 1
- Validation: Must be a positive integer

### Counter Increment Step (Invoice)
- The increment value for invoice counter
- Only used when "Same as Order Number" is "No"
- Scope: Store View
- Default: 1
- Validation: Must be a positive integer

### Counter Padding (Invoice)
- Total number of digits in the invoice counter (with leading zeros)
- Only used when "Same as Order Number" is "No"
- Scope: Store View
- Default: 6
- Validation: Must be zero or greater

### Reset Counter on Date Change (Invoice)
- Automatically reset invoice counter based on time period
- Only used when "Same as Order Number" is "No"
- Options: No / Daily / Monthly / Yearly
- Scope: Store View
- Default: No

---

## Shipment Configuration

### Same as Order Number
- **Yes** - Shipment will use the exact same number as the order
  - Example: Order `ORD-25-10-30-000001` → Shipment `ORD-25-10-30-000001`
  - **Multiple Shipments:** When creating multiple shipments for the same order, suffixes are automatically added:
    - First shipment: `ORD-25-10-30-000001`
    - Second shipment: `ORD-25-10-30-000001-1`
    - Third shipment: `ORD-25-10-30-000001-2`
- **No** - Shipment will use its own format and counter configuration
- Scope: Store View
- Default: Yes

### Custom Shipment Number Format (when "Same as Order" is No)
- Define the shipment number pattern using variables
- **Required variable:** `{counter}` - must be present in the format
- **Optional variables:** `{yyyy}`, `{yy}`, `{mm}`, `{dd}`
- **Example:** `SHIP-{yy}-{mm}-{dd}-{counter}`
- **Result:** `SHIP-25-10-30-000001`
- Scope: Store View
- Default: `SHIP-{yy}-{mm}-{dd}-{counter}`
- **Note:** Only visible when "Same as Order Number" is set to "No"

### Start Counter From (Shipment)
- The initial counter value for new shipments
- Only used when "Same as Order Number" is "No"
- Scope: Store View
- Default: 1
- Validation: Must be a positive integer

### Counter Increment Step (Shipment)
- The increment value for shipment counter
- Only used when "Same as Order Number" is "No"
- Scope: Store View
- Default: 1
- Validation: Must be a positive integer

### Counter Padding (Shipment)
- Total number of digits in the shipment counter (with leading zeros)
- Only used when "Same as Order Number" is "No"
- Scope: Store View
- Default: 6
- Validation: Must be zero or greater

### Reset Counter on Date Change (Shipment)
- Automatically reset shipment counter based on time period
- Only used when "Same as Order Number" is "No"
- Options: No / Daily / Monthly / Yearly
- Scope: Store View
- Default: No

---

## Credit Memo Configuration

### Same as Order Number
- **Yes** - Credit memo will use the exact same number as the order
  - Example: Order `ORD-25-10-30-000001` → Credit Memo `ORD-25-10-30-000001`
  - **Multiple Credit Memos:** When creating multiple credit memos for the same order, suffixes are automatically added:
    - First credit memo: `ORD-25-10-30-000001`
    - Second credit memo: `ORD-25-10-30-000001-1`
    - Third credit memo: `ORD-25-10-30-000001-2`
- **No** - Credit memo will use its own format and counter configuration
- Scope: Store View
- Default: Yes

### Custom Credit Memo Number Format (when "Same as Order" is No)
- Define the credit memo number pattern using variables
- **Required variable:** `{counter}` - must be present in the format
- **Optional variables:** `{yyyy}`, `{yy}`, `{mm}`, `{dd}`
- **Example:** `CR-{yy}-{mm}-{dd}-{counter}`
- **Result:** `CR-25-10-30-000001`
- Scope: Store View
- Default: `CR-{yy}-{mm}-{dd}-{counter}`
- **Note:** Only visible when "Same as Order Number" is set to "No"

### Start Counter From (Credit Memo)
- The initial counter value for new credit memos
- Only used when "Same as Order Number" is "No"
- Scope: Store View
- Default: 1
- Validation: Must be a positive integer

### Counter Increment Step (Credit Memo)
- The increment value for credit memo counter
- Only used when "Same as Order Number" is "No"
- Scope: Store View
- Default: 1
- Validation: Must be a positive integer

### Counter Padding (Credit Memo)
- Total number of digits in the credit memo counter (with leading zeros)
- Only used when "Same as Order Number" is "No"
- Scope: Store View
- Default: 6
- Validation: Must be zero or greater

### Reset Counter on Date Change (Credit Memo)
- Automatically reset credit memo counter based on time period
- Only used when "Same as Order Number" is "No"
- Options: No / Daily / Monthly / Yearly
- Scope: Store View
- Default: No

---

## Usage Examples

### Example 1: All Entities Same as Order

**Configuration:**
- Order Format: `ORD-{yy}-{mm}-{dd}-{counter}`
- Invoice Same as Order: **Yes**
- Shipment Same as Order: **Yes**
- Credit Memo Same as Order: **Yes`

**Results:**
- Order: `ORD-25-01-15-000001`
- Invoice: `ORD-25-01-15-000001`
- Shipment: `ORD-25-01-15-000001`
- Credit Memo: `ORD-25-01-15-000001`

### Example 2: All Entities with Own Formats

**Configuration:**
- Order Format: `ORD-{yy}-{mm}-{dd}-{counter}`
- Invoice Same as Order: **No**, Format: `INV-{yy}-{mm}-{dd}-{counter}`
- Shipment Same as Order: **No**, Format: `SHIP-{yy}-{mm}-{dd}-{counter}`
- Credit Memo Same as Order: **No**, Format: `CR-{yy}-{mm}-{dd}-{counter}`

**Results:**
- Order: `ORD-25-01-15-000001`
- Invoice: `INV-25-01-15-000001`
- Shipment: `SHIP-25-01-15-000001`
- Credit Memo: `CR-25-01-15-000001`

### Example 3: Multiple Invoices/Shipments/Credit Memos

**Configuration:**
- Order Format: `ORD-{yy}-{mm}-{dd}-{counter}`
- Invoice Same as Order: **Yes**
- Shipment Same as Order: **Yes`

**Order:** `ORD-25-01-15-000001`

**Multiple Invoices:**
- First Invoice: `ORD-25-01-15-000001`
- Second Invoice: `ORD-25-01-15-000001-1`
- Third Invoice: `ORD-25-01-15-000001-2`

**Multiple Shipments:**
- First Shipment: `ORD-25-01-15-000001`
- Second Shipment: `ORD-25-01-15-000001-1`

**Multiple Credit Memos:**
- First Credit Memo: `ORD-25-01-15-000001`
- Second Credit Memo: `ORD-25-01-15-000001-1`

### Example 4: Daily Reset Counters

**Configuration:**
- Order Format: `ORD-{yy}{mm}{dd}-{counter}`
- Start Counter: 1
- Reset Counter: Daily

**Results:**
- Day 1: `ORD-250115-0001`, `ORD-250115-0002`, ...
- Day 2: `ORD-250116-0001`, `ORD-250116-0002`, ...

---

## Multi-Store Support

The extension fully supports Magento's multi-store functionality:

- Each store view has **independent counter management** for all entity types
- Configuration can be set at **Store View scope**
- Different formats can be used for different stores
- Separate "Start Counter From" values ensure no conflicts between stores

### Recommended Store Setup

**Store 1 (US Store):**
```
Order Format: US-ORD-{yyyy}-{counter}
Invoice: Same as Order
Shipment: Same as Order
Credit Memo: Same as Order
Start Counter: 1000001
```

**Store 2 (EU Store):**
```
Order Format: EU-ORD-{yyyy}-{counter}
Invoice: Own Format: EU-INV-{yyyy}-{counter}
Shipment: Same as Order
Credit Memo: Same as Order
Start Counter: 2000001
```

---

## Technical Details

### Database Schema

The extension creates a table `learning_custom_order_counter` with the following structure:

```sql
CREATE TABLE learning_custom_order_counter (
  entity_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  entity_type VARCHAR(32) NOT NULL,
  store_id SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  counter_value INT UNSIGNED NOT NULL DEFAULT 1,
  last_reset_date DATE NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (entity_id),
  UNIQUE KEY (entity_type, store_id),
  FOREIGN KEY (store_id) REFERENCES store(store_id) ON DELETE CASCADE
);
```

**Entity Types:**
- `order` - Order counter
- `invoice` - Invoice counter (when using own format)
- `shipment` - Shipment counter (when using own format)
- `creditmemo` - Credit memo counter (when using own format)

### Architecture

**Sequence Manager Override:**
- Overrides `Magento\SalesSequence\Model\Manager` to provide custom sequence generation
- Handles order, invoice, shipment, and credit memo entity types
- Falls back to default Magento sequence for other entity types

**Plugins:**
- **Invoice Plugin:** `Magento\Sales\Model\ResourceModel\Order\Invoice::beforeSave()`
  - Sets invoice increment ID same as order when configured
  - Handles multiple invoices with suffix logic
- **Shipment Plugin:** `Magento\Sales\Model\ResourceModel\Order\Shipment::beforeSave()`
  - Sets shipment increment ID same as order when configured
  - Handles multiple shipments with suffix logic
- **Credit Memo Plugin:** `Magento\Sales\Model\ResourceModel\Order\Creditmemo::beforeSave()`
  - Sets credit memo increment ID same as order when configured
  - Handles multiple credit memos with suffix logic

**Counter Service:**
- Manages counter retrieval and increment operations for all entity types
- Implements `SELECT ... FOR UPDATE` for atomic operations
- Handles date-based reset logic
- Supports independent counters for each entity type and store

**Format Validation:**
- Backend model validates format on config save
- Ensures `{counter}` variable is present
- Validates all variables are recognized

### Multiple Entity Handling

When "Same as Order Number" is enabled for invoices, shipments, or credit memos:

1. **First Entity:** Uses order number as-is (e.g., `ORD-25-10-30-000001`)
2. **Subsequent Entities:** Automatically appends suffix:
   - Second: `-1` (e.g., `ORD-25-10-30-000001-1`)
   - Third: `-2` (e.g., `ORD-25-10-30-000001-2`)
   - And so on...

This prevents unique constraint violations when creating multiple invoices, shipments, or credit memos for the same order.

---

## Cron Configuration

The extension includes a cron job that runs daily to check and reset counters:

**Cron Schedule:** `0 0 * * *` (Runs at midnight daily)

**Cron Job:** `learning_custom_order_number_reset_counter`

To manually run the cron:
```bash
bin/magento cron:run
```

---

## Troubleshooting

### Issue: Orders/Invoices/Shipments/Credit Memos still using default increment ID

**Solution:**
1. Ensure the module is enabled: `bin/magento module:status Learning_CustomOrderNumber`
2. Check configuration: **Stores > Configuration > Learning Modules > Custom Order Number > General Settings > Enable Module** is set to "Yes"
3. Clear cache: `bin/magento cache:clean`
4. Recompile DI: `bin/magento setup:di:compile`

### Issue: Duplicate numbers

**Solution:**
- The extension uses database locking to prevent duplicates
- Ensure database transactions are working properly
- Check logs in `var/log/system.log` for any errors
- For multiple entities (invoices/shipments/credit memos), ensure "Same as Order Number" is enabled to use suffix logic

### Issue: Format validation error

**Solution:**
- Ensure `{counter}` variable is present in the format
- Only use valid variables: `{counter}`, `{yyyy}`, `{yy}`, `{mm}`, `{dd}`
- Check for typos in variable names (case-sensitive)

### Issue: Counter not resetting

**Solution:**
1. Check cron is running: `bin/magento cron:run`
2. Verify reset frequency is configured (not "No")
3. Check cron logs for errors
4. Ensure module is enabled for the store

### Issue: Different stores have same numbers

**Solution:**
- Configure different "Start Counter From" values for each store
- Example: Store 1 = 1000001, Store 2 = 2000001
- Each store maintains independent counters for each entity type

### Issue: Multiple invoices/shipments/credit memos causing unique constraint violation

**Solution:**
- Ensure "Same as Order Number" is enabled for the entity type
- The plugin automatically handles suffix logic for multiple entities
- Check logs to verify plugin is executing correctly

---

## Logs

The extension logs important events and errors to:
- `var/log/system.log` - General information, counter resets, and plugin execution
- `var/log/exception.log` - Critical errors and exceptions

**Log Examples:**
```
CustomOrderNumber: Using custom sequence for order
CustomOrderNumber Sequence: Generated number
CustomOrderNumber InvoicePlugin: Set invoice increment ID same as order (beforeSave)
CustomOrderNumber ShipmentPlugin: Set shipment increment ID same as order (beforeSave)
CustomOrderNumber CreditmemoPlugin: Set credit memo increment ID same as order (beforeSave)
```

---

## Uninstallation

To remove the extension:

1. Disable the module:
```bash
bin/magento module:disable Learning_CustomOrderNumber
```

2. Remove the database table (optional):
```bash
mysql -u [user] -p [database] -e "DROP TABLE IF EXISTS learning_custom_order_counter;"
```

3. Remove module files:
```bash
rm -rf app/code/Learning/CustomOrderNumber
```

4. Run setup upgrade:
```bash
bin/magento setup:upgrade
bin/magento cache:clean
```

---

## Support

For issues, questions, or feature requests:
- Check the troubleshooting section above
- Review `var/log/system.log` for errors
- Ensure you're running a compatible Magento version
- Verify all configuration settings are correct

---

## License

This extension is provided as-is for educational and commercial use.

---

## Credits

**Module Name:** Learning_CustomOrderNumber  
**Vendor:** Learning  
**Version:** 1.0.0  
**Magento Version:** 2.4.4 - 2.4.8  
**PHP Version:** 8.1+
