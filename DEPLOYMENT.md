# Deployment Information

## Plugin Details

| Item | Value |
|------|-------|
| **Plugin Name** | Conditional Error Checking Pro |
| **Version** | 1.00 |
| **Author** | Sagentic Web Design |
| **License** | MIT |
| **CMS Version Required** | 3.59+ |

## File Structure

```
conditionalErrorCheckingPro/
├── conditionalErrorCheckingPro.php           # Main plugin (1.5 KB)
├── conditionalErrorCheckingPro_admin.php     # Admin UI (35 KB)
├── conditionalErrorCheckingPro_functions.php # Functions (15 KB)
├── conditionalErrorCheckingPro_settings.json # Auto-created
├── pluginSchemas/
│   ├── _conditionalerrorcheckingpro_rules.schema.php
│   ├── _conditionalerrorcheckingpro_logs.schema.php
│   └── conditionalerrorcheckingpro_menu.schema.php
├── reset_installation.php
├── LICENSE
├── CHANGELOG.md
├── README.md
├── QUICK_START.md
└── DEPLOYMENT.md
```

## Database Tables Created

| Table | Purpose |
|-------|---------|
| `{prefix}_conditionalerrorcheckingpro_rules` | Validation rule definitions |
| `{prefix}_conditionalerrorcheckingpro_logs` | Validation activity logs |

Tables are auto-created on first admin login.

## Installation Steps

1. Upload folder to `/cmsb/plugins/`
2. Run `fixown` for permissions
3. Access admin panel
4. Navigate to Plugins section

## Optional: Menu Entry

Copy `pluginSchemas/conditionalerrorcheckingpro_menu.schema.php` to `/cmsb/data/schema/` for dedicated menu.

## Uninstallation

1. Disable plugin in CMS admin
2. Delete plugin folder
3. Optionally drop database tables:
   - `DROP TABLE {prefix}_conditionalerrorcheckingpro_rules;`
   - `DROP TABLE {prefix}_conditionalerrorcheckingpro_logs;`

## Dependencies

- PHP 8.1+
- CMS Builder 3.59+
- No external libraries required
