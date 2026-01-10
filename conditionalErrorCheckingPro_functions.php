<?php
/**
 * Conditional Error Checking Pro - Helper Functions
 *
 * Settings management, rule evaluation, logging, and utilities
 */

namespace ConditionalErrorCheckingPro;

/**
 * Get path to settings JSON file
 */
function getSettingsFilePath(): string
{
	return __DIR__ . '/conditionalErrorCheckingPro_settings.json';
}

/**
 * Get default settings
 */
function getDefaultSettings(): array
{
	return [
		'pluginEnabled'       => true,
		'logRetentionDays'    => 30,
		'maxRulesPerTable'    => 50,
		'emailNotifications'  => false,
		'notificationEmail'   => '',
		'debugMode'           => false,
		'excludedTables'      => [
			'accounts',
			'_cron_log',
			'menugroups',
			'uploads',
		],
	];
}

/**
 * Load plugin settings from JSON file
 */
function loadSettings(): array
{
	$settingsFile = getSettingsFilePath();
	$defaults = getDefaultSettings();

	if (!file_exists($settingsFile) || !is_readable($settingsFile)) {
		return $defaults;
	}

	$content = @file_get_contents($settingsFile);
	if ($content === false) {
		return $defaults;
	}

	$settings = @json_decode($content, true);
	if (!is_array($settings)) {
		return $defaults;
	}

	// Merge with defaults to ensure all keys exist
	return array_merge($defaults, $settings);
}

/**
 * Save plugin settings to JSON file
 */
function saveSettings(array $settings): bool
{
	$settingsFile = getSettingsFilePath();
	$json = json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
	return @file_put_contents($settingsFile, $json) !== false;
}

/**
 * Create database tables if they don't exist
 */
function createTablesIfNeeded(): void
{
	global $TABLE_PREFIX;

	// Check and create rules table
	$rulesTable = "{$TABLE_PREFIX}_conditionalerrorcheckingpro_rules";
	$result = \mysqli()->query("SHOW TABLES LIKE '{$rulesTable}'");
	if ($result && $result->num_rows === 0) {
		$sql = "CREATE TABLE `{$rulesTable}` (
			`num` int(10) unsigned NOT NULL AUTO_INCREMENT,
			`tableName` varchar(255) NOT NULL DEFAULT '',
			`ruleName` varchar(255) NOT NULL DEFAULT '',
			`triggerField` varchar(255) NOT NULL DEFAULT '',
			`triggerCondition` enum('not_empty','is_empty','equals','not_equals','contains','not_contains','greater_than','less_than','regex_match') NOT NULL DEFAULT 'not_empty',
			`triggerValue` text,
			`requiredField` varchar(255) NOT NULL DEFAULT '',
			`errorMessage` text NOT NULL,
			`isActive` tinyint(1) NOT NULL DEFAULT 1,
			`ruleOrder` int(10) NOT NULL DEFAULT 0,
			`createdDate` datetime DEFAULT NULL,
			`updatedDate` datetime DEFAULT NULL,
			`createdByUserNum` int(10) unsigned DEFAULT NULL,
			`updatedByUserNum` int(10) unsigned DEFAULT NULL,
			PRIMARY KEY (`num`),
			KEY `tableName` (`tableName`),
			KEY `isActive` (`isActive`),
			KEY `ruleOrder` (`ruleOrder`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
		\mysqli()->query($sql);
	}

	// Check and create logs table
	$logsTable = "{$TABLE_PREFIX}_conditionalerrorcheckingpro_logs";
	$result = \mysqli()->query("SHOW TABLES LIKE '{$logsTable}'");
	if ($result && $result->num_rows === 0) {
		$sql = "CREATE TABLE `{$logsTable}` (
			`num` int(10) unsigned NOT NULL AUTO_INCREMENT,
			`tableName` varchar(255) NOT NULL DEFAULT '',
			`recordNum` int(10) unsigned DEFAULT NULL,
			`ruleNum` int(10) unsigned DEFAULT NULL,
			`ruleName` varchar(255) NOT NULL DEFAULT '',
			`errorMessage` text,
			`triggerField` varchar(255) NOT NULL DEFAULT '',
			`triggerValue` text,
			`requiredField` varchar(255) NOT NULL DEFAULT '',
			`requiredValue` text,
			`wasBlocked` tinyint(1) NOT NULL DEFAULT 0,
			`createdDate` datetime DEFAULT NULL,
			`createdByUserNum` int(10) unsigned DEFAULT NULL,
			PRIMARY KEY (`num`),
			KEY `tableName` (`tableName`),
			KEY `ruleNum` (`ruleNum`),
			KEY `createdDate` (`createdDate`),
			KEY `wasBlocked` (`wasBlocked`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
		\mysqli()->query($sql);
	}
}

/**
 * Load active rules for a specific table
 */
function loadRulesForTable(string $tableName): array
{
	$escapedTable = \mysql_escape($tableName);
	$rules = \mysql_select(
		'_conditionalerrorcheckingpro_rules',
		"`tableName` = '{$escapedTable}' AND `isActive` = 1 ORDER BY `ruleOrder` ASC, `num` ASC"
	);
	return $rules ?: [];
}

/**
 * Load all rules (for admin display)
 */
function loadAllRules(string $orderBy = 'tableName ASC, ruleOrder ASC'): array
{
	// Validate order by to prevent SQL injection
	$allowedColumns = ['num', 'tableName', 'ruleName', 'triggerField', 'requiredField', 'isActive', 'ruleOrder', 'createdDate'];
	$orderParts = explode(',', $orderBy);
	$safeOrderParts = [];

	foreach ($orderParts as $part) {
		$part = trim($part);
		if (preg_match('/^(\w+)\s*(ASC|DESC)?$/i', $part, $matches)) {
			$column = $matches[1];
			$direction = strtoupper($matches[2] ?? 'ASC');
			if (in_array($column, $allowedColumns, true) && in_array($direction, ['ASC', 'DESC'], true)) {
				$safeOrderParts[] = "`{$column}` {$direction}";
			}
		}
	}

	$safeOrderBy = !empty($safeOrderParts) ? implode(', ', $safeOrderParts) : '`tableName` ASC, `ruleOrder` ASC';

	$rules = \mysql_select(
		'_conditionalerrorcheckingpro_rules',
		"TRUE ORDER BY {$safeOrderBy}"
	);
	return $rules ?: [];
}

/**
 * Load a single rule by ID
 */
function loadRule(int $ruleNum): ?array
{
	$rule = \mysql_get('_conditionalerrorcheckingpro_rules', $ruleNum);
	return $rule ?: null;
}

/**
 * Save a rule (insert or update)
 */
function saveRule(array $data, ?int $ruleNum = null): int
{
	global $CURRENT_USER;

	$now = date('Y-m-d H:i:s');
	$userId = $CURRENT_USER['num'] ?? 0;

	$colsToValues = [
		'tableName'        => $data['tableName'] ?? '',
		'ruleName'         => $data['ruleName'] ?? '',
		'triggerField'     => $data['triggerField'] ?? '',
		'triggerCondition' => $data['triggerCondition'] ?? 'not_empty',
		'triggerValue'     => $data['triggerValue'] ?? '',
		'requiredField'    => $data['requiredField'] ?? '',
		'errorMessage'     => $data['errorMessage'] ?? '',
		'isActive'         => isset($data['isActive']) ? (int)$data['isActive'] : 1,
		'ruleOrder'        => isset($data['ruleOrder']) ? (int)$data['ruleOrder'] : 0,
		'updatedDate'      => $now,
		'updatedByUserNum' => $userId,
	];

	if ($ruleNum) {
		// Update existing
		\mysql_update('_conditionalerrorcheckingpro_rules', $ruleNum, null, $colsToValues);
		return $ruleNum;
	} else {
		// Insert new
		$colsToValues['createdDate'] = $now;
		$colsToValues['createdByUserNum'] = $userId;
		return \mysql_insert('_conditionalerrorcheckingpro_rules', $colsToValues);
	}
}

/**
 * Delete a rule
 */
function deleteRule(int $ruleNum): bool
{
	\mysql_delete('_conditionalerrorcheckingpro_rules', $ruleNum);
	return true;
}

/**
 * Evaluate a rule against current request data
 *
 * @return array ['triggered' => bool, 'hasError' => bool, 'errorMessage' => string]
 */
function evaluateRule(array $rule): array
{
	$result = [
		'triggered'    => false,
		'hasError'     => false,
		'errorMessage' => '',
		'triggerValue' => '',
		'requiredValue' => '',
	];

	$triggerField = $rule['triggerField'] ?? '';
	$triggerCondition = $rule['triggerCondition'] ?? 'not_empty';
	$triggerValue = $rule['triggerValue'] ?? '';
	$requiredField = $rule['requiredField'] ?? '';
	$errorMessage = $rule['errorMessage'] ?? 'This field is required.';

	// Get current values from request
	$currentTriggerValue = $_REQUEST[$triggerField] ?? '';
	$currentRequiredValue = $_REQUEST[$requiredField] ?? '';

	$result['triggerValue'] = is_array($currentTriggerValue) ? implode(', ', $currentTriggerValue) : (string)$currentTriggerValue;
	$result['requiredValue'] = is_array($currentRequiredValue) ? implode(', ', $currentRequiredValue) : (string)$currentRequiredValue;

	// Check if trigger condition is met
	$triggered = evaluateCondition($currentTriggerValue, $triggerCondition, $triggerValue);
	$result['triggered'] = $triggered;

	if (!$triggered) {
		return $result;
	}

	// Trigger is met - check if required field has value
	$requiredEmpty = isEmpty($currentRequiredValue);

	if ($requiredEmpty) {
		$result['hasError'] = true;
		$result['errorMessage'] = $errorMessage;
	}

	return $result;
}

/**
 * Evaluate a condition
 */
function evaluateCondition($value, string $condition, string $compareValue): bool
{
	// Normalize value to string for comparison
	$strValue = is_array($value) ? implode(', ', $value) : (string)$value;

	switch ($condition) {
		case 'not_empty':
			return !isEmpty($value);

		case 'is_empty':
			return isEmpty($value);

		case 'equals':
			return $strValue === $compareValue;

		case 'not_equals':
			return $strValue !== $compareValue;

		case 'contains':
			return str_contains($strValue, $compareValue);

		case 'not_contains':
			return !str_contains($strValue, $compareValue);

		case 'greater_than':
			return is_numeric($strValue) && is_numeric($compareValue) && (float)$strValue > (float)$compareValue;

		case 'less_than':
			return is_numeric($strValue) && is_numeric($compareValue) && (float)$strValue < (float)$compareValue;

		case 'regex_match':
			// Ensure pattern has delimiters
			$pattern = $compareValue;
			if (!preg_match('/^[\/\#\~]/', $pattern)) {
				$pattern = '/' . $pattern . '/';
			}
			return @preg_match($pattern, $strValue) === 1;

		default:
			return false;
	}
}

/**
 * Check if a value is empty
 */
function isEmpty($value): bool
{
	if (is_array($value)) {
		return empty(array_filter($value, fn($v) => trim((string)$v) !== ''));
	}
	return trim((string)$value) === '';
}

/**
 * Log a validation attempt
 */
function logValidation(string $tableName, int $recordNum, array $rule, array $result): void
{
	global $CURRENT_USER;

	// Only log if triggered (to avoid excessive logging)
	if (!$result['triggered']) {
		return;
	}

	$colsToValues = [
		'tableName'        => $tableName,
		'recordNum'        => $recordNum,
		'ruleNum'          => $rule['num'] ?? 0,
		'ruleName'         => $rule['ruleName'] ?? '',
		'errorMessage'     => $result['errorMessage'],
		'triggerField'     => $rule['triggerField'] ?? '',
		'triggerValue'     => $result['triggerValue'],
		'requiredField'    => $rule['requiredField'] ?? '',
		'requiredValue'    => $result['requiredValue'],
		'wasBlocked'       => $result['hasError'] ? 1 : 0,
		'createdDate'      => date('Y-m-d H:i:s'),
		'createdByUserNum' => $CURRENT_USER['num'] ?? 0,
	];

	\mysql_insert('_conditionalerrorcheckingpro_logs', $colsToValues);
}

/**
 * Get log entries with pagination
 */
function getLogs(int $page = 1, int $perPage = 50, array $filters = []): array
{
	$where = [];

	if (!empty($filters['tableName'])) {
		$where[] = "`tableName` = '" . \mysql_escape($filters['tableName']) . "'";
	}
	if (!empty($filters['ruleNum'])) {
		$where[] = "`ruleNum` = " . (int)$filters['ruleNum'];
	}
	if (!empty($filters['wasBlocked'])) {
		$where[] = "`wasBlocked` = 1";
	}
	if (!empty($filters['dateFrom'])) {
		$where[] = "`createdDate` >= '" . \mysql_escape($filters['dateFrom']) . " 00:00:00'";
	}
	if (!empty($filters['dateTo'])) {
		$where[] = "`createdDate` <= '" . \mysql_escape($filters['dateTo']) . " 23:59:59'";
	}

	$whereCondition = !empty($where) ? implode(' AND ', $where) : 'TRUE';

	// Get total count
	$totalCount = \mysql_count('_conditionalerrorcheckingpro_logs', $whereCondition);

	// Calculate offset
	$offset = ($page - 1) * $perPage;

	// Get records
	$logs = \mysql_select(
		'_conditionalerrorcheckingpro_logs',
		"{$whereCondition} ORDER BY `createdDate` DESC LIMIT {$offset}, {$perPage}"
	);

	return [
		'logs'       => $logs ?: [],
		'totalCount' => $totalCount,
		'page'       => $page,
		'perPage'    => $perPage,
		'totalPages' => ceil($totalCount / $perPage),
	];
}

/**
 * Clear old log entries based on retention setting
 */
function clearOldLogs(int $daysToKeep = 30): int
{
	global $TABLE_PREFIX;

	$cutoffDate = date('Y-m-d H:i:s', strtotime("-{$daysToKeep} days"));
	$escapedDate = \mysql_escape($cutoffDate);

	$result = \mysqli()->query(
		"DELETE FROM `{$TABLE_PREFIX}_conditionalerrorcheckingpro_logs`
		 WHERE `createdDate` < '{$escapedDate}'"
	);

	return \mysqli()->affected_rows;
}

/**
 * Get statistics for dashboard
 */
function getStats(): array
{
	$todayStart = date('Y-m-d 00:00:00');
	$weekStart = date('Y-m-d 00:00:00', strtotime('-7 days'));
	$monthStart = date('Y-m-d 00:00:00', strtotime('-30 days'));

	return [
		'totalRules'       => \mysql_count('_conditionalerrorcheckingpro_rules', ''),
		'activeRules'      => \mysql_count('_conditionalerrorcheckingpro_rules', "`isActive` = 1"),
		'tablesWithRules'  => getDistinctTableCount(),
		'blockedToday'     => \mysql_count('_conditionalerrorcheckingpro_logs', "`wasBlocked` = 1 AND `createdDate` >= '{$todayStart}'"),
		'blockedWeek'      => \mysql_count('_conditionalerrorcheckingpro_logs', "`wasBlocked` = 1 AND `createdDate` >= '{$weekStart}'"),
		'blockedMonth'     => \mysql_count('_conditionalerrorcheckingpro_logs', "`wasBlocked` = 1 AND `createdDate` >= '{$monthStart}'"),
		'triggeredToday'   => \mysql_count('_conditionalerrorcheckingpro_logs', "`createdDate` >= '{$todayStart}'"),
		'totalLogs'        => \mysql_count('_conditionalerrorcheckingpro_logs', ''),
	];
}

/**
 * Get count of distinct tables with rules
 */
function getDistinctTableCount(): int
{
	global $TABLE_PREFIX;

	$result = \mysqli()->query(
		"SELECT COUNT(DISTINCT `tableName`) as cnt FROM `{$TABLE_PREFIX}_conditionalerrorcheckingpro_rules`"
	);

	if ($result) {
		$row = mysqli_fetch_assoc($result);
		return (int)($row['cnt'] ?? 0);
	}

	return 0;
}

/**
 * Get recent log entries
 */
function getRecentLogs(int $limit = 10): array
{
	$logs = \mysql_select(
		'_conditionalerrorcheckingpro_logs',
		"TRUE ORDER BY `createdDate` DESC LIMIT {$limit}"
	);
	return $logs ?: [];
}

/**
 * Get available tables for rule creation
 */
function getAvailableTables(): array
{
	$settings = loadSettings();
	$excludedTables = $settings['excludedTables'] ?? [];

	// Get all schema files
	$schemaDir = DATA_DIR . '/schema/';
	$tables = [];

	if (is_dir($schemaDir)) {
		$files = glob($schemaDir . '*.schema.php');
		foreach ($files as $file) {
			$tableName = basename($file, '.schema.php');

			// Skip excluded tables
			if (in_array($tableName, $excludedTables, true)) {
				continue;
			}

			// Skip system tables (starting with underscore)
			if (str_starts_with($tableName, '_')) {
				continue;
			}

			$tables[] = $tableName;
		}
	}

	sort($tables);
	return $tables;
}

/**
 * Get fields for a specific table from its schema
 */
function getTableFields(string $tableName): array
{
	$schemaFile = DATA_DIR . '/schema/' . $tableName . '.schema.php';

	if (!file_exists($schemaFile)) {
		return [];
	}

	$schema = include $schemaFile;
	if (!is_array($schema)) {
		return [];
	}

	$fields = [];
	foreach ($schema as $fieldName => $fieldDef) {
		// Skip metadata keys
		if (str_starts_with($fieldName, '_') || str_starts_with($fieldName, 'menu')) {
			continue;
		}

		// Skip non-array definitions
		if (!is_array($fieldDef)) {
			continue;
		}

		$label = $fieldDef['label'] ?? $fieldName;
		$type = $fieldDef['type'] ?? 'unknown';

		$fields[$fieldName] = [
			'name'  => $fieldName,
			'label' => $label,
			'type'  => $type,
		];
	}

	return $fields;
}

/**
 * Get condition type options for dropdown
 */
function getConditionTypes(): array
{
	return [
		'not_empty'    => t('is not empty'),
		'is_empty'     => t('is empty'),
		'equals'       => t('equals'),
		'not_equals'   => t('does not equal'),
		'contains'     => t('contains'),
		'not_contains' => t('does not contain'),
		'greater_than' => t('is greater than'),
		'less_than'    => t('is less than'),
		'regex_match'  => t('matches pattern (regex)'),
	];
}

/**
 * Export rules as JSON
 */
function exportRules(): string
{
	$rules = loadAllRules();

	// Remove internal IDs for portability
	foreach ($rules as &$rule) {
		unset($rule['num'], $rule['createdByUserNum'], $rule['updatedByUserNum']);
	}

	return json_encode([
		'exportVersion' => '1.0',
		'exportDate'    => date('Y-m-d H:i:s'),
		'pluginVersion' => $GLOBALS['CONDITIONALERRORCHECKING_PRO_VERSION'],
		'rules'         => $rules,
	], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
}

/**
 * Import rules from JSON
 */
function importRules(string $json): array
{
	$data = @json_decode($json, true);

	if (!is_array($data) || empty($data['rules'])) {
		return ['success' => false, 'message' => t('Invalid JSON format or no rules found.')];
	}

	$imported = 0;
	$skipped = 0;

	foreach ($data['rules'] as $rule) {
		// Validate required fields
		if (empty($rule['tableName']) || empty($rule['ruleName']) || empty($rule['triggerField'])) {
			$skipped++;
			continue;
		}

		// Check if similar rule already exists
		$existing = \mysql_select(
			'_conditionalerrorcheckingpro_rules',
			"`tableName` = '" . \mysql_escape($rule['tableName']) . "'
			 AND `ruleName` = '" . \mysql_escape($rule['ruleName']) . "'
			 LIMIT 1"
		);

		if (!empty($existing)) {
			$skipped++;
			continue;
		}

		saveRule($rule);
		$imported++;
	}

	return [
		'success'  => true,
		'imported' => $imported,
		'skipped'  => $skipped,
		'message'  => sprintf(t('Imported %d rules. Skipped %d (duplicates or invalid).'), $imported, $skipped),
	];
}

/**
 * Send email notification when save is blocked
 */
function sendBlockedNotification(string $tableName, int $recordNum, array $rules, array $errors): void
{
	global $CURRENT_USER, $SETTINGS;

	$settings = loadSettings();

	if (empty($settings['notificationEmail'])) {
		return;
	}

	// Build rule names list
	$ruleNames = array_map(fn($r) => $r['ruleName'] ?? 'Unknown', $rules);

	// Build email content
	$subject = sprintf(
		'[%s] Validation Rule Blocked Save: %s',
		$SETTINGS['programName'] ?? 'CMS Builder',
		$tableName
	);

	$body = "A record save was blocked by validation rules.\n\n";
	$body .= "Table: {$tableName}\n";
	$body .= "Record #: {$recordNum}\n";
	$body .= "User: " . ($CURRENT_USER['fullname'] ?? $CURRENT_USER['username'] ?? 'Unknown') . "\n";
	$body .= "Email: " . ($CURRENT_USER['email'] ?? 'N/A') . "\n";
	$body .= "Date: " . date('Y-m-d H:i:s') . "\n\n";
	$body .= "Triggered Rules:\n";
	foreach ($ruleNames as $name) {
		$body .= "  - {$name}\n";
	}
	$body .= "\nError Messages:\n";
	foreach ($errors as $error) {
		$body .= "  - {$error}\n";
	}

	// Use CMS's sendMessage function
	$mailOptions = [
		'to'      => $settings['notificationEmail'],
		'subject' => $subject,
		'body'    => $body,
	];

	@sendMessage($mailOptions);
}
