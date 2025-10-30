# Learning_CustomOrderNumber

Custom Order Number extension for Magento 2 that allows store owners to customize the order numbering format with flexible configuration options.

## Overview

The Custom Order Number extension facilitates store admins to have complete control over the numbering system for orders in their Magento 2 store. Replace Magento's default order numbers with customized formats that align with your business requirements and branding.

## Features

- **Custom Order Number Format** - Define your own order number pattern using variables
- **Store-Specific Counters** - Each store view has independent counter management
- **Flexible Variables** - Use date and counter variables in your format
- **Counter Padding** - Add leading zeros for consistent number length
- **Increment Step** - Customize the increment value for sequential numbering
- **Date-Based Reset** - Automatically reset counters daily, monthly, or yearly
- **Format Validation** - Validates format pattern on configuration save
- **Database Locking** - Prevents duplicate order numbers in high-concurrency scenarios
- **New Orders Only** - No impact on existing orders

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

### Configuration Options

#### General Settings

**Enable Module**
- Enable/Disable the custom order number functionality
- Scope: Store View
- Default: No

#### Order Configuration

**Custom Order Number Format**
- Define the order number pattern using variables
- Required variable: `{counter}` - must be present in the format
- Optional variables:
  - `{yyyy}` - 4-digit year (e.g., 2025)
  - `{yy}` - 2-digit year (e.g., 25)
  - `{mm}` - 2-digit month (e.g., 01)
  - `{dd}` - 2-digit day (e.g., 15)
- Example: `ORD-{yy}-{mm}-{dd}-{counter}`
- Result: `ORD-25-01-15-000001`
- Scope: Store View
- Default: `ORD-{yy}-{mm}-{dd}-{counter}`

**Start Counter From**
- The initial counter value for new orders
- Use different starting values for different stores (e.g., 1000001 for Store 1, 2000001 for Store 2)
- Scope: Store View
- Default: 1
- Validation: Must be a positive integer

**Counter Increment Step**
- The increment value added to the counter for each new order
- Example: If last counter is 1000001 and step is 4, next will be 1000005
- Scope: Store View
- Default: 1
- Validation: Must be a positive integer

**Counter Padding**
- Total number of digits in the counter (with leading zeros)
- Example: Counter 24 with padding 6 = 000024
- Set 0 to disable padding
- Scope: Store View
- Default: 6
- Validation: Must be zero or greater

**Reset Counter on Date Change**
- Automatically reset counter based on time period
- Options:
  - **No** - Counter never resets automatically
  - **Daily** - Reset at the start of each day
  - **Monthly** - Reset at the start of each month
  - **Yearly** - Reset at the start of each year
- When reset occurs, counter returns to "Start Counter From" value
- Scope: Store View
- Default: No

## Usage Examples

### Example 1: Simple Sequential Numbering

**Configuration:**
- Format: `{counter}`
- Start Counter: 1
- Increment Step: 1
- Padding: 6

**Result:** `000001`, `000002`, `000003`, ...

### Example 2: Date-Based Order Numbers

**Configuration:**
- Format: `ORD-{yyyy}-{mm}-{dd}-{counter}`
- Start Counter: 1
- Increment Step: 1
- Padding: 4

**Result:** `ORD-2025-01-15-0001`, `ORD-2025-01-15-0002`, ...

### Example 3: Store-Specific Numbering

**Store 1 Configuration:**
- Format: `ST1-{counter}`
- Start Counter: 1000001
- Increment Step: 1
- Padding: 0

**Store 1 Result:** `ST1-1000001`, `ST1-1000002`, ...

**Store 2 Configuration:**
- Format: `ST2-{counter}`
- Start Counter: 2000001
- Increment Step: 1
- Padding: 0

**Store 2 Result:** `ST2-2000001`, `ST2-2000002`, ...

### Example 4: Daily Reset Counters

**Configuration:**
- Format: `ORD-{yy}{mm}{dd}-{counter}`
- Start Counter: 1
- Increment Step: 1
- Padding: 4
- Reset Counter: Daily

**Results:**
- Day 1: `ORD-250115-0001`, `ORD-250115-0002`, ...
- Day 2: `ORD-250116-0001`, `ORD-250116-0002`, ...

## Multi-Store Support

The extension fully supports Magento's multi-store functionality:

- Each store view has **independent counter management**
- Configuration can be set at **Store View scope**
- Different formats can be used for different stores
- Separate "Start Counter From" values ensure no conflicts between stores

### Recommended Store Setup

**Store 1 (US Store):**
```
Format: US-{yyyy}-{counter}
Start Counter: 1000001
```

**Store 2 (EU Store):**
```
Format: EU-{yyyy}-{counter}
Start Counter: 2000001
```

**Store 3 (Asia Store):**
```
Format: ASIA-{yyyy}-{counter}
Start Counter: 3000001
```

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

### Architecture

**Plugin System:**
- Intercepts `Magento\Sales\Model\Order::beforeSave()`
- Applies custom increment ID only for new orders
- Uses database row-level locking to prevent duplicate IDs

**Counter Service:**
- Manages counter retrieval and increment operations
- Implements `SELECT ... FOR UPDATE` for atomic operations
- Handles date-based reset logic

**Cron Job:**
- Runs daily at midnight (00:00)
- Checks all counters for reset conditions
- Resets counters based on configured frequency

**Format Validation:**
- Backend model validates format on config save
- Ensures `{counter}` variable is present
- Validates all variables are recognized

## Cron Configuration

The extension includes a cron job that runs daily to check and reset counters:

**Cron Schedule:** `0 0 * * *` (Runs at midnight daily)

**Cron Job:** `learning_custom_order_number_reset_counter`

To manually run the cron:
```bash
bin/magento cron:run
```

## Troubleshooting

### Issue: Orders still using default increment ID

**Solution:**
1. Ensure the module is enabled: `bin/magento module:status Learning_CustomOrderNumber`
2. Check configuration: **Stores > Configuration > Learning Modules > Custom Order Number > General Settings > Enable Module** is set to "Yes"
3. Clear cache: `bin/magento cache:clean`

### Issue: Duplicate order numbers

**Solution:**
- The extension uses database locking to prevent duplicates
- Ensure database transactions are working properly
- Check logs in `var/log/system.log` for any errors

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

### Issue: Different stores have same order numbers

**Solution:**
- Configure different "Start Counter From" values for each store
- Example: Store 1 = 1000001, Store 2 = 2000001
- Each store maintains independent counters

## Logs

The extension logs important events and errors to:
- `var/log/system.log` - General information and counter resets
- `var/log/exception.log` - Critical errors and exceptions

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

## Future Enhancements

The following features are planned for future releases:

- Custom formats for **Invoice** numbers
- Custom formats for **Shipment** numbers
- Custom formats for **Credit Memo** numbers
- Admin grid to view and manage counters
- Manual counter reset functionality from admin
- Import/Export counter values
- API endpoints for counter management

## Support

For issues, questions, or feature requests:
- Check the troubleshooting section above
- Review `var/log/system.log` for errors
- Ensure you're running a compatible Magento version

## License

This extension is provided as-is for educational and commercial use.

## Credits

**Module Name:** Learning_CustomOrderNumber
**Vendor:** Learning
**Version:** 1.0.0
**Magento Version:** 2.4.4 - 2.4.8
**PHP Version:** 8.1+

## Changelog

### Version 1.0.0
- Initial release
- Custom order number formatting
- Store-specific counters
- Date-based counter reset
- Format validation
- Database locking for concurrency
- Cron-based counter management
