# Conditional Error Checking Pro

> **Note:** This plugin only works with CMS Builder, available for download at https://www.interactivetools.com/download/

**Version:** 1.00
**Author:** Sagentic Web Design
**License:** MIT
**CMS Builder Version Required:** 3.59+

---

## Overview

Conditional Error Checking Pro transforms the basic conditional validation plugin into a full-featured, UI-driven validation rule engine. Create dynamic validation rules through an intuitive admin interface without writing any code.

### Key Features

- **Visual Rule Builder** - Create validation rules through the admin UI
- **Multi-Table Support** - Apply rules to any CMS table
- **9 Condition Types** - Flexible trigger conditions including regex support
- **Activity Logging** - Track all validation events with detailed logs
- **Dashboard Overview** - Statistics and recent activity at a glance
- **Import/Export** - Share rule configurations between installations
- **Email Notifications** - Get notified when saves are blocked
- **Help Documentation** - Built-in documentation and examples

---

## Installation

1. Upload the `conditionalErrorCheckingPro` folder to your `/cmsb/plugins/` directory
2. Run `fixown` to set proper file permissions
3. Access the CMS admin panel - the plugin will auto-create its database tables
4. Navigate to **Plugins > Conditional Error Checking Pro** to start creating rules

### Optional: Add Menu Entry

Copy `pluginSchemas/conditionalerrorcheckingpro_menu.schema.php` to `/cmsb/data/schema/` for a dedicated menu entry.

---

## Quick Start

1. Go to the **Rules** page and click **Add New Rule**
2. Select the table you want to add validation to
3. Choose a trigger field and condition (e.g., "phone is not empty")
4. Choose the field that becomes required when the trigger condition is met
5. Enter an error message to display if the required field is empty
6. Save the rule - it starts validating immediately

---

## Condition Types

| Condition | Description | Example |
|-----------|-------------|---------|
| `is not empty` | Field has any value | If `phone` is not empty... |
| `is empty` | Field is empty | If `status` is empty... |
| `equals` | Exact match | If `type` equals "premium"... |
| `does not equal` | Does not match | If `status` does not equal "archived"... |
| `contains` | Contains substring | If `name` contains "test"... |
| `does not contain` | Missing substring | If `email` does not contain "@"... |
| `is greater than` | Numeric comparison | If `priority` > 5... |
| `is less than` | Numeric comparison | If `count` < 0... |
| `matches pattern` | Regex pattern match | If `phone` matches `/^\d{10}$/`... |

---

## Example Rules

### Phone requires Contact Name

When a phone number is entered, require a contact name:

- **Table:** contacts
- **Trigger:** phone is not empty
- **Required:** contact_name
- **Error:** "Please enter a contact name when providing a phone number."

### Premium Status requires Payment Method

When membership type is set to premium, require payment info:

- **Table:** members
- **Trigger:** membership_type equals "premium"
- **Required:** payment_method
- **Error:** "Premium members must have a payment method on file."

### Shipping Address requires City

When a shipping address is entered, require the city:

- **Table:** orders
- **Trigger:** shipping_address is not empty
- **Required:** shipping_city
- **Error:** "Please enter a city for the shipping address."

---

## Settings

### General Settings

- **Enable Validation Rules** - Master switch to enable/disable all validation
- **Log Retention** - Number of days to keep validation logs (1-365)
- **Max Rules Per Table** - Maximum rules allowed per table (1-500)

### Email Notifications

- **Send Email on Block** - Email when a save is blocked
- **Notification Email** - Address to receive notifications

### Advanced Settings

- **Excluded Tables** - Tables that won't appear in the rule dropdown
- **Debug Mode** - Log additional information for troubleshooting

---

## Import/Export

### Exporting Rules

1. Click the **Advanced Actions** dropdown (three dots menu)
2. Select **Export Rules**
3. A JSON file will download with all your rules

### Importing Rules

1. Click the **Advanced Actions** dropdown
2. Select **Import Rules**
3. Upload a previously exported JSON file
4. Duplicate rules (same table + rule name) will be skipped

---

## Default Excluded Tables

The following tables are excluded from the rule dropdown by default:

- `accounts` - User accounts (system table)
- `_cron_log` - Cron job logs
- `menugroups` - Menu configuration
- `uploads` - Upload management
- Any table starting with `_` (system tables)

You can modify this list in Settings.

---

## Database Tables

The plugin creates two tables on first access:

### `_conditionalerrorcheckingpro_rules`

Stores validation rule definitions:

| Field | Type | Description |
|-------|------|-------------|
| num | INT | Primary key |
| tableName | VARCHAR(255) | Target table name |
| ruleName | VARCHAR(255) | Human-readable rule name |
| triggerField | VARCHAR(255) | Field that triggers the rule |
| triggerCondition | ENUM | Condition type |
| triggerValue | TEXT | Value for comparison |
| requiredField | VARCHAR(255) | Field that becomes required |
| errorMessage | TEXT | Custom error message |
| isActive | TINYINT(1) | Enable/disable rule |
| ruleOrder | INT | Execution priority |
| createdDate | DATETIME | Creation timestamp |
| updatedDate | DATETIME | Last modified timestamp |

### `_conditionalerrorcheckingpro_logs`

Stores validation activity:

| Field | Type | Description |
|-------|------|-------------|
| num | INT | Primary key |
| tableName | VARCHAR(255) | Table being validated |
| recordNum | INT | Record number |
| ruleNum | INT | Rule that triggered |
| ruleName | VARCHAR(255) | Rule name (denormalized) |
| errorMessage | TEXT | Error shown to user |
| wasBlocked | TINYINT(1) | Was save blocked? |
| createdDate | DATETIME | Timestamp |

---

## Troubleshooting

### Rules not working?

1. Check that the plugin is enabled in Settings
2. Verify the rule is marked as Active
3. Make sure the table is not in the excluded tables list
4. Check the Logs page to see if the rule is being triggered

### Fields not showing in dropdown?

1. The plugin reads field names from schema files
2. System fields starting with underscore are hidden
3. If a field was recently added, the schema file may need to be regenerated

### Logs filling up too fast?

1. Reduce the log retention days in Settings
2. Use the "Clear Old Logs" option in Advanced Actions
3. Only triggered rules are logged (not every save)

---

## Migration from Original Plugin

If you're migrating from the original `conditionalErrorChecking` plugin:

1. Document your existing hard-coded rules
2. Create equivalent rules through the new admin UI
3. Disable the old plugin in the plugins list
4. Delete or rename the old plugin folder
5. Test validation behavior on affected tables

---

## Technical Details

### Hook Used

```php
addAction('record_save_errorchecking', 'validateRecord', null, 3);
```

### Namespace

```php
namespace ConditionalErrorCheckingPro;
```

### Files

| File | Purpose |
|------|---------|
| `conditionalErrorCheckingPro.php` | Main plugin file |
| `conditionalErrorCheckingPro_admin.php` | Admin UI pages |
| `conditionalErrorCheckingPro_functions.php` | Helper functions |
| `conditionalErrorCheckingPro_settings.json` | Settings storage |
| `pluginSchemas/*.schema.php` | Table schema documentation |

---

## Support

For issues or feature requests, contact Sagentic Web Design or open an issue in the repository.

---

## License

MIT License - See LICENSE file for details.

---

## Changelog

See CHANGELOG.md for version history.
