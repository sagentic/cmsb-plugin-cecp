<?php
/*
Plugin Name: Conditional Error Checking Pro
Description: Visual rule builder for hierarchical field validation with logging and notifications
Version: 1.00
CMS Version Required: 3.59
Author: Sagentic Web Design
*/

namespace ConditionalErrorCheckingPro;

// Version constant
$GLOBALS['CONDITIONALERRORCHECKING_PRO_VERSION'] = '1.00';

// Include function files
$pluginDir = __DIR__ . '/';
require_once $pluginDir . 'conditionalErrorCheckingPro_functions.php';
require_once $pluginDir . 'conditionalErrorCheckingPro_admin.php';

// Register hooks
\addAction('admin_postlogin', 'ConditionalErrorCheckingPro\pluginInit', null, -999);
\addAction('record_save_errorchecking', 'ConditionalErrorCheckingPro\validateRecord', null, 3);

// Register admin page handlers
\pluginAction_addHandlerAndLink(
	t('Conditional Error Checking Pro'),
	'ConditionalErrorCheckingPro\adminDashboard',
	'admins'
);

// Additional admin handlers (no menu links)
\pluginAction_addHandler('ConditionalErrorCheckingPro\adminRules', 'admins');
\pluginAction_addHandler('ConditionalErrorCheckingPro\adminRuleEdit', 'admins');
\pluginAction_addHandler('ConditionalErrorCheckingPro\adminLogs', 'admins');
\pluginAction_addHandler('ConditionalErrorCheckingPro\adminSettings', 'admins');
\pluginAction_addHandler('ConditionalErrorCheckingPro\adminHelp', 'admins');
\pluginAction_addHandler('ConditionalErrorCheckingPro\ajaxGetFields', 'admins');

/**
 * Plugin initialization - runs on admin login
 * Creates database tables if they don't exist
 */
function pluginInit(): void
{
	createTablesIfNeeded();
}

/**
 * Main validation function - runs on record save
 *
 * @param string $tableName Table being saved
 * @param mixed $recordExists Whether record already exists
 * @param mixed $oldRecord Previous record data (if exists)
 */
function validateRecord($tableName, $recordExists, $oldRecord): void
{
	// Load settings
	$settings = loadSettings();

	// Check if plugin is enabled globally
	if (empty($settings['pluginEnabled'])) {
		return;
	}

	// Check if this table is excluded
	$excludedTables = $settings['excludedTables'] ?? [];
	if (in_array($tableName, $excludedTables, true)) {
		return;
	}

	// Skip tables starting with underscore (system tables)
	if (str_starts_with($tableName, '_')) {
		return;
	}

	// Load active rules for this table
	$rules = loadRulesForTable($tableName);
	if (empty($rules)) {
		return;
	}

	$errors = [];
	$recordNum = (int)($_REQUEST['num'] ?? 0);

	// Evaluate each rule
	foreach ($rules as $rule) {
		$result = evaluateRule($rule);

		// Log the validation attempt
		logValidation($tableName, $recordNum, $rule, $result);

		// Collect errors
		if ($result['hasError']) {
			$errors[] = $result['errorMessage'];
		}
	}

	// Send notification if configured and errors occurred
	if (!empty($errors) && !empty($settings['emailNotifications'])) {
		sendBlockedNotification($tableName, $recordNum, $rules, $errors);
	}

	// Show errors and stop save
	if (!empty($errors)) {
		$errorsHTML = implode("<br>\n", array_map('htmlencode', $errors));
		die($errorsHTML);
	}
}
