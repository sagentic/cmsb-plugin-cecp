# Changelog

All notable changes to Conditional Error Checking Pro will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [1.00] - 2025-01-09

### Added

- Initial release
- Visual rule builder with admin UI
- 9 condition types:
  - is not empty
  - is empty
  - equals
  - does not equal
  - contains
  - does not contain
  - is greater than
  - is less than
  - matches pattern (regex)
- Multi-table support
- Activity logging with filtering
- Dashboard with statistics
- Import/export functionality (JSON)
- Email notifications on blocked saves
- Built-in help documentation
- Settings management via JSON file
- Bulk actions (enable, disable, delete)
- Rule duplication
- CSV export for logs
- Auto-creating database tables
- CSRF protection on all actions
- Accessible navigation with ARIA labels

### Technical

- Uses `record_save_errorchecking` hook
- Namespace: `ConditionalErrorCheckingPro`
- Tables auto-created on first admin login
- Settings stored in JSON file (not database)
- Compatible with CMS Builder 3.59+

---

## Migration from Original Plugin

The original `conditionalErrorChecking` plugin used hard-coded rules in PHP:

```php
// Old approach
if (!empty($_REQUEST['phone'])) {
    if (empty($_REQUEST['contact_name'])) {
        $errors[] = "Please enter a contact name.";
    }
}
```

This Pro version allows the same rules to be created through the UI without code changes.
