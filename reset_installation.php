<?php
/**
 * Conditional Error Checking Pro - Reset Installation Tool
 *
 * This script resets the plugin to its initial state.
 * Run this from the command line or access via browser with confirmation.
 *
 * WARNING: This will delete ALL rules and logs!
 */

// Safety check - require confirmation
$confirmed = isset($_GET['confirm']) && $_GET['confirm'] === 'yes';

if (php_sapi_name() !== 'cli' && !$confirmed) {
	echo "<!DOCTYPE html>\n";
	echo "<html><head><title>Reset Conditional Error Checking Pro</title></head>\n";
	echo "<body style='font-family:sans-serif;padding:20px;'>\n";
	echo "<h1>Reset Conditional Error Checking Pro</h1>\n";
	echo "<p style='color:red;font-weight:bold;'>WARNING: This will delete ALL rules and logs!</p>\n";
	echo "<p>This action cannot be undone.</p>\n";
	echo "<p><a href='?confirm=yes' style='background:#d9534f;color:#fff;padding:10px 20px;text-decoration:none;border-radius:4px;'>Yes, Reset Everything</a></p>\n";
	echo "<p><a href='javascript:history.back()'>Cancel</a></p>\n";
	echo "</body></html>\n";
	exit;
}

// Load CMS environment
$dirsToCheck = ['', '../', '../../', '../../../'];
$loaded = false;

foreach ($dirsToCheck as $dir) {
	$initFile = $dir . 'lib/init.php';
	if (file_exists($initFile)) {
		require_once $initFile;
		$loaded = true;
		break;
	}
}

if (!$loaded) {
	// Try alternative path
	$webadminPath = dirname(__DIR__, 2);
	$initFile = $webadminPath . '/lib/init.php';
	if (file_exists($initFile)) {
		require_once $initFile;
		$loaded = true;
	}
}

if (!$loaded) {
	die("Error: Could not load CMS environment. Run this from the webadmin context.\n");
}

global $TABLE_PREFIX;

$output = [];
$output[] = "Conditional Error Checking Pro - Reset Tool";
$output[] = str_repeat("=", 50);
$output[] = "";

// Drop tables
$tables = [
	'_conditionalerrorcheckingpro_rules',
	'_conditionalerrorcheckingpro_logs',
];

foreach ($tables as $table) {
	$fullTable = $TABLE_PREFIX . $table;
	$result = mysqli()->query("DROP TABLE IF EXISTS `{$fullTable}`");
	if ($result) {
		$output[] = "Dropped table: {$fullTable}";
	} else {
		$output[] = "Failed to drop table: {$fullTable} - " . mysqli()->error;
	}
}

// Delete settings file
$settingsFile = __DIR__ . '/conditionalErrorCheckingPro_settings.json';
if (file_exists($settingsFile)) {
	if (unlink($settingsFile)) {
		$output[] = "Deleted settings file";
	} else {
		$output[] = "Failed to delete settings file";
	}
} else {
	$output[] = "Settings file not found (already clean)";
}

$output[] = "";
$output[] = "Reset complete!";
$output[] = "Tables will be recreated on next admin login.";
$output[] = "";

// Output results
if (php_sapi_name() === 'cli') {
	echo implode("\n", $output);
} else {
	echo "<!DOCTYPE html>\n";
	echo "<html><head><title>Reset Complete</title></head>\n";
	echo "<body style='font-family:sans-serif;padding:20px;'>\n";
	echo "<h1>Reset Complete</h1>\n";
	echo "<pre>" . htmlspecialchars(implode("\n", $output)) . "</pre>\n";
	echo "<p><a href='../../?menu=admin&action=plugins'>Return to Plugins</a></p>\n";
	echo "</body></html>\n";
}
