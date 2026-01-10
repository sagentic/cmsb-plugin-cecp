<?php
/**
 * Conditional Error Checking Pro - Admin UI Pages
 *
 * Dashboard, Rules, Logs, Settings, and Help pages
 */

namespace ConditionalErrorCheckingPro;

/**
 * Set a flash notice that persists across redirects
 */
function setFlashNotice(string $message): void
{
	$_SESSION['_cecp_flash_notice'] = $message;
}

/**
 * Set a flash alert that persists across redirects
 */
function setFlashAlert(string $message): void
{
	$_SESSION['_cecp_flash_alert'] = $message;
}

/**
 * Display any flash messages and clear them
 */
function displayFlashMessages(): void
{
	if (!empty($_SESSION['_cecp_flash_notice'])) {
		\notice($_SESSION['_cecp_flash_notice']);
		unset($_SESSION['_cecp_flash_notice']);
	}
	if (!empty($_SESSION['_cecp_flash_alert'])) {
		\alert($_SESSION['_cecp_flash_alert']);
		unset($_SESSION['_cecp_flash_alert']);
	}
}

/**
 * Generate navigation bar HTML
 */
function getPluginNav(string $currentPage): string
{
	$pages = [
		'dashboard' => ['label' => t('Dashboard'), 'action' => 'ConditionalErrorCheckingPro\adminDashboard'],
		'rules'     => ['label' => t('Rules'), 'action' => 'ConditionalErrorCheckingPro\adminRules'],
		'logs'      => ['label' => t('Logs'), 'action' => 'ConditionalErrorCheckingPro\adminLogs'],
		'settings'  => ['label' => t('Settings'), 'action' => 'ConditionalErrorCheckingPro\adminSettings'],
		'help'      => ['label' => t('Help'), 'action' => 'ConditionalErrorCheckingPro\adminHelp'],
	];

	$html = '<nav aria-label="' . t('Conditional Error Checking Pro navigation') . '">';
	$html .= '<div class="btn-group" role="group" style="margin-bottom:20px">';

	foreach ($pages as $key => $page) {
		$isActive = ($key === $currentPage);
		$btnClass = $isActive ? 'btn btn-primary' : 'btn btn-default';
		$ariaCurrent = $isActive ? ' aria-current="page"' : '';
		$html .= '<a href="?_pluginAction=' . urlencode($page['action']) . '" class="' . $btnClass . '"' . $ariaCurrent . '>' . \htmlencode($page['label']) . '</a>';
	}

	$html .= '</div>';
	$html .= '</nav>';

	return $html;
}

/**
 * Get advanced actions array for adminUI
 */
function getAdvancedActions(): array
{
	return [
		t('Export Rules')   => '?_pluginAction=' . urlencode('ConditionalErrorCheckingPro\adminSettings') . '&_action=export',
		t('Import Rules')   => '?_pluginAction=' . urlencode('ConditionalErrorCheckingPro\adminSettings') . '&_action=importForm',
		t('Clear Old Logs') => '?_pluginAction=' . urlencode('ConditionalErrorCheckingPro\adminLogs') . '&_action=clearOld',
	];
}

/**
 * Dashboard Page
 */
function adminDashboard(): void
{
	$settings = loadSettings();
	$stats = getStats();
	$recentLogs = getRecentLogs(10);

	$adminUI = [];
	$adminUI['PAGE_TITLE'] = [
		t('Plugins') => '?menu=admin&action=plugins',
		t('Conditional Error Checking Pro'),
	];
	$adminUI['ADVANCED_ACTIONS'] = getAdvancedActions();

	$content = getPluginNav('dashboard');

	// Status banner
	if (!$settings['pluginEnabled']) {
		$content .= '<div class="alert alert-warning">';
		$content .= '<i class="fa-duotone fa-solid fa-triangle-exclamation"></i> ';
		$content .= t('Plugin is currently disabled. Enable it in Settings to start validating records.');
		$content .= '</div>';
	}

	// Statistics Section
	$content .= '<div class="separator"><div>' . t('Statistics') . '</div></div>';
	$content .= '<div class="row g-3 mb-4">';

	// Total Rules
	$content .= '<div class="col-6 col-lg-3">';
	$content .= '<div class="border rounded-3 p-3 h-100 text-center">';
	$content .= '<div class="text-uppercase small fw-semibold mb-3">' . t('Total Rules') . '</div>';
	$content .= '<div class="fs-2 fw-bold text-primary">' . (int)$stats['totalRules'] . '</div>';
	$content .= '</div></div>';

	// Active Rules
	$content .= '<div class="col-6 col-lg-3">';
	$content .= '<div class="border rounded-3 p-3 h-100 text-center">';
	$content .= '<div class="text-uppercase small fw-semibold mb-3">' . t('Active Rules') . '</div>';
	$content .= '<div class="fs-2 fw-bold text-success">' . (int)$stats['activeRules'] . '</div>';
	$content .= '</div></div>';

	// Blocked Today
	$content .= '<div class="col-6 col-lg-3">';
	$content .= '<div class="border rounded-3 p-3 h-100 text-center">';
	$content .= '<div class="text-uppercase small fw-semibold mb-3">' . t('Blocked Today') . '</div>';
	$content .= '<div class="fs-2 fw-bold text-danger">' . (int)$stats['blockedToday'] . '</div>';
	$content .= '</div></div>';

	// Tables Covered
	$content .= '<div class="col-6 col-lg-3">';
	$content .= '<div class="border rounded-3 p-3 h-100 text-center">';
	$content .= '<div class="text-uppercase small fw-semibold mb-3">' . t('Tables Covered') . '</div>';
	$content .= '<div class="fs-2 fw-bold text-info">' . (int)$stats['tablesWithRules'] . '</div>';
	$content .= '</div></div>';

	$content .= '</div>'; // row

	// Quick Actions
	$content .= '<div class="separator"><div>' . t('Quick Actions') . '</div></div>';
	$content .= '<p style="margin-bottom:15px">';
	$content .= '<a href="?_pluginAction=' . urlencode('ConditionalErrorCheckingPro\adminRuleEdit') . '" class="btn btn-primary">';
	$content .= '<i class="fa-duotone fa-solid fa-plus"></i> ' . t('Add New Rule');
	$content .= '</a> ';
	$content .= '<a href="?_pluginAction=' . urlencode('ConditionalErrorCheckingPro\adminRules') . '" class="btn btn-default">';
	$content .= '<i class="fa-duotone fa-solid fa-list"></i> ' . t('View All Rules');
	$content .= '</a> ';
	$content .= '<a href="?_pluginAction=' . urlencode('ConditionalErrorCheckingPro\adminLogs') . '" class="btn btn-default">';
	$content .= '<i class="fa-duotone fa-solid fa-scroll"></i> ' . t('View Logs');
	$content .= '</a>';
	$content .= '</p>';

	// Recent Activity
	$content .= '<div class="separator"><div>' . t('Recent Activity') . '</div></div>';

	if (empty($recentLogs)) {
		$content .= '<p class="text-muted">' . t('No validation activity logged yet.') . '</p>';
	} else {
		$content .= '<div class="table-responsive">';
		$content .= '<table class="table table-hover">';
		$content .= '<thead><tr>';
		$content .= '<th>' . t('Date') . '</th>';
		$content .= '<th>' . t('Table') . '</th>';
		$content .= '<th>' . t('Record') . '</th>';
		$content .= '<th>' . t('Rule') . '</th>';
		$content .= '<th>' . t('Status') . '</th>';
		$content .= '</tr></thead>';
		$content .= '<tbody>';

		foreach ($recentLogs as $log) {
			$statusBadge = $log['wasBlocked']
				? '<span class="badge" style="background-color:#d9534f;color:#fff">' . t('Blocked') . '</span>'
				: '<span class="badge" style="background-color:#5cb85c;color:#fff">' . t('Passed') . '</span>';

			$editUrl = '?menu=' . urlencode($log['tableName']) . '&action=edit&num=' . (int)$log['recordNum'];

			$content .= '<tr>';
			$content .= '<td class="text-nowrap">' . \htmlencode(date('M j, g:i a', strtotime($log['createdDate']))) . '</td>';
			$content .= '<td>' . \htmlencode($log['tableName']) . '</td>';
			$content .= '<td><a href="' . \htmlencode($editUrl) . '" target="_blank">#' . (int)$log['recordNum'] . '</a></td>';
			$content .= '<td>' . \htmlencode($log['ruleName']) . '</td>';
			$content .= '<td>' . $statusBadge . '</td>';
			$content .= '</tr>';
		}

		$content .= '</tbody></table>';
		$content .= '</div>';
	}

	$adminUI['CONTENT'] = $content;
	\adminUI($adminUI);
	exit;
}

/**
 * Rules List Page
 */
function adminRules(): void
{
	// Display any flash messages from redirects
	displayFlashMessages();

	// Handle actions
	$action = $_REQUEST['_action'] ?? '';
	$csrfToken = ($_SESSION['_CSRFToken'] ?? '');

	// Note: GET actions rely on CMSB's internal referer checking for security
	// security_dieOnInvalidCsrfToken() only works with POST requests
	if ($action === 'delete' && isset($_REQUEST['ruleNum'])) {
		\security_dieUnlessInternalReferer();
		$ruleNum = (int)$_REQUEST['ruleNum'];
		deleteRule($ruleNum);
		setFlashNotice(t('Rule deleted successfully.'));
		\redirectBrowserToURL('?_pluginAction=' . urlencode('ConditionalErrorCheckingPro\adminRules'));
	}

	if ($action === 'toggle' && isset($_REQUEST['ruleNum'])) {
		\security_dieUnlessInternalReferer();
		$ruleNum = (int)$_REQUEST['ruleNum'];
		$rule = loadRule($ruleNum);
		if ($rule) {
			$newStatus = $rule['isActive'] ? 0 : 1;
			saveRule(['isActive' => $newStatus], $ruleNum);
			$statusText = $newStatus ? t('enabled') : t('disabled');
			setFlashNotice(sprintf(t('Rule %s.'), $statusText));
		}
		\redirectBrowserToURL('?_pluginAction=' . urlencode('ConditionalErrorCheckingPro\adminRules'));
	}

	if ($action === 'duplicate' && isset($_REQUEST['ruleNum'])) {
		\security_dieUnlessInternalReferer();
		$ruleNum = (int)$_REQUEST['ruleNum'];
		$rule = loadRule($ruleNum);
		if ($rule) {
			$rule['ruleName'] = $rule['ruleName'] . ' (Copy)';
			unset($rule['num']);
			saveRule($rule);
			setFlashNotice(t('Rule duplicated successfully.'));
		}
		\redirectBrowserToURL('?_pluginAction=' . urlencode('ConditionalErrorCheckingPro\adminRules'));
	}

	// Handle bulk actions
	if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['bulkAction']) && !empty($_POST['selectedRules'])) {
		\security_dieOnInvalidCsrfToken();
		$bulkAction = $_POST['bulkAction'];
		$selectedRules = array_map('intval', $_POST['selectedRules']);

		foreach ($selectedRules as $ruleNum) {
			switch ($bulkAction) {
				case 'enable':
					saveRule(['isActive' => 1], $ruleNum);
					break;
				case 'disable':
					saveRule(['isActive' => 0], $ruleNum);
					break;
				case 'delete':
					deleteRule($ruleNum);
					break;
			}
		}

		setFlashNotice(sprintf(t('Bulk action completed on %d rules.'), count($selectedRules)));
		\redirectBrowserToURL('?_pluginAction=' . urlencode('ConditionalErrorCheckingPro\adminRules'));
	}

	// Load rules
	$filterTable = $_REQUEST['filterTable'] ?? '';
	$rules = loadAllRules();

	// Apply filter
	if ($filterTable) {
		$rules = array_filter($rules, fn($r) => $r['tableName'] === $filterTable);
	}

	// Get available tables for filter
	$tables = getAvailableTables();

	$adminUI = [];
	$adminUI['PAGE_TITLE'] = [
		t('Plugins') => '?menu=admin&action=plugins',
		t('Conditional Error Checking Pro') => '?_pluginAction=' . urlencode('ConditionalErrorCheckingPro\adminDashboard'),
		t('Rules'),
	];
	$adminUI['ADVANCED_ACTIONS'] = getAdvancedActions();

	$content = getPluginNav('rules');

	// Add Rule button
	$content .= '<div style="margin-bottom:20px">';
	$content .= '<a href="?_pluginAction=' . urlencode('ConditionalErrorCheckingPro\adminRuleEdit') . '" class="btn btn-primary">';
	$content .= '<i class="fa-duotone fa-solid fa-plus"></i> ' . t('Add New Rule');
	$content .= '</a>';
	$content .= '</div>';

	// Filter Section
	$content .= '<div class="separator"><div>' . t('Filter Rules') . '</div></div>';
	$content .= '<form method="get">';
	$content .= '<input type="hidden" name="_pluginAction" value="ConditionalErrorCheckingPro\adminRules">';
	$content .= '<div class="form-horizontal">';

	$content .= '<div class="form-group">';
	$content .= '<label for="filterTable" class="col-sm-2 control-label">' . t('Table') . '</label>';
	$content .= '<div class="col-sm-10">';
	$content .= '<select name="filterTable" id="filterTable" class="form-control" style="width:200px;display:inline-block">';
	$content .= '<option value="">' . t('All Tables') . '</option>';
	foreach ($tables as $table) {
		$selected = ($filterTable === $table) ? ' selected' : '';
		$content .= '<option value="' . \htmlencode($table) . '"' . $selected . '>' . \htmlencode($table) . '</option>';
	}
	$content .= '</select>';
	$content .= '</div></div>';

	// Buttons
	$content .= '<div class="form-group">';
	$content .= '<div class="col-sm-2 control-label"></div>';
	$content .= '<div class="col-sm-10">';
	$content .= '<button type="submit" class="btn btn-primary">' . t('Filter') . '</button>';
	$content .= ' <a href="?_pluginAction=' . urlencode('ConditionalErrorCheckingPro\adminRules') . '" class="btn btn-default">' . t('Reset') . '</a>';
	$content .= '</div></div>';

	$content .= '</div>'; // end form-horizontal
	$content .= '</form>';

	// Rules table with bulk actions
	$content .= '<form method="post" id="bulkForm">';
	$content .= '<input type="hidden" name="_pluginAction" value="' . \htmlencode('ConditionalErrorCheckingPro\adminRules') . '">';
	$content .= '<input type="hidden" name="_CSRFToken" value="' . \htmlencode($csrfToken) . '">';

	// Bulk action bar
	$content .= '<div class="well well-sm">';
	$content .= '<div class="form-inline">';
	$content .= '<div class="form-group">';
	$content .= '<select name="bulkAction" class="form-control">';
	$content .= '<option value="">' . t('Bulk Actions') . '</option>';
	$content .= '<option value="enable">' . t('Enable Selected') . '</option>';
	$content .= '<option value="disable">' . t('Disable Selected') . '</option>';
	$content .= '<option value="delete">' . t('Delete Selected') . '</option>';
	$content .= '</select>';
	$content .= '</div> ';
	$content .= '<button type="submit" class="btn btn-default">' . t('Apply') . '</button>';
	$content .= '</div></div>';

	if (empty($rules)) {
		$content .= '<div class="alert alert-info">' . t('No rules found. Create your first rule to get started.') . '</div>';
	} else {
		$content .= '<table class="table table-striped table-hover">';
		$content .= '<thead><tr>';
		$content .= '<th style="width:30px"><input type="checkbox" id="selectAll" title="' . t('Select All') . '"></th>';
		$content .= '<th>' . t('Table') . '</th>';
		$content .= '<th>' . t('Rule Name') . '</th>';
		$content .= '<th>' . t('Trigger') . '</th>';
		$content .= '<th>' . t('Required Field') . '</th>';
		$content .= '<th>' . t('Status') . '</th>';
		$content .= '<th>' . t('Order') . '</th>';
		$content .= '<th>' . t('Actions') . '</th>';
		$content .= '</tr></thead>';
		$content .= '<tbody>';

		$conditionTypes = getConditionTypes();

		foreach ($rules as $rule) {
			$statusBadge = $rule['isActive']
				? '<span class="badge" style="background-color:#5cb85c;color:#fff">' . t('Active') . '</span>'
				: '<span class="badge" style="background-color:#999;color:#fff">' . t('Disabled') . '</span>';

			$conditionLabel = $conditionTypes[$rule['triggerCondition']] ?? $rule['triggerCondition'];
			$triggerText = $rule['triggerField'] . ' ' . $conditionLabel;
			if ($rule['triggerValue'] && !in_array($rule['triggerCondition'], ['not_empty', 'is_empty'])) {
				$triggerText .= ' "' . $rule['triggerValue'] . '"';
			}

			$content .= '<tr>';
			$content .= '<td><input type="checkbox" name="selectedRules[]" value="' . (int)$rule['num'] . '" class="ruleCheckbox"></td>';
			$content .= '<td>' . \htmlencode($rule['tableName']) . '</td>';
			$content .= '<td>' . \htmlencode($rule['ruleName']) . '</td>';
			$content .= '<td><small>' . \htmlencode($triggerText) . '</small></td>';
			$content .= '<td>' . \htmlencode($rule['requiredField']) . '</td>';
			$content .= '<td>' . $statusBadge . '</td>';
			$content .= '<td>' . (int)$rule['ruleOrder'] . '</td>';
			$content .= '<td class="text-nowrap">';

			// Edit
			$content .= '<a href="?_pluginAction=' . urlencode('ConditionalErrorCheckingPro\adminRuleEdit') . '&ruleNum=' . (int)$rule['num'] . '" class="btn btn-xs btn-default" title="' . t('Edit') . '" aria-label="' . t('Edit rule') . '">';
			$content .= '<i class="fa-duotone fa-solid fa-pencil" aria-hidden="true"></i>';
			$content .= '</a> ';

			// Toggle
			$toggleIcon = $rule['isActive'] ? 'fa-toggle-on' : 'fa-toggle-off';
			$toggleTitle = $rule['isActive'] ? t('Disable') : t('Enable');
			$toggleAriaLabel = $rule['isActive'] ? t('Disable rule') : t('Enable rule');
			$content .= '<a href="?_pluginAction=' . urlencode('ConditionalErrorCheckingPro\adminRules') . '&_action=toggle&ruleNum=' . (int)$rule['num'] . '&_CSRFToken=' . urlencode($csrfToken) . '" class="btn btn-xs btn-default" title="' . $toggleTitle . '" aria-label="' . $toggleAriaLabel . '">';
			$content .= '<i class="fa-duotone fa-solid ' . $toggleIcon . '" aria-hidden="true"></i>';
			$content .= '</a> ';

			// Duplicate
			$content .= '<a href="?_pluginAction=' . urlencode('ConditionalErrorCheckingPro\adminRules') . '&_action=duplicate&ruleNum=' . (int)$rule['num'] . '&_CSRFToken=' . urlencode($csrfToken) . '" class="btn btn-xs btn-default" title="' . t('Duplicate') . '" aria-label="' . t('Duplicate rule') . '">';
			$content .= '<i class="fa-duotone fa-solid fa-copy" aria-hidden="true"></i>';
			$content .= '</a> ';

			// Delete
			$content .= '<a href="?_pluginAction=' . urlencode('ConditionalErrorCheckingPro\adminRules') . '&_action=delete&ruleNum=' . (int)$rule['num'] . '&_CSRFToken=' . urlencode($csrfToken) . '" class="btn btn-xs btn-danger" title="' . t('Delete') . '" aria-label="' . t('Delete rule') . '" onclick="return confirm(\'' . t('Are you sure you want to delete this rule?') . '\');">';
			$content .= '<i class="fa-duotone fa-solid fa-trash" aria-hidden="true"></i>';
			$content .= '</a>';

			$content .= '</td>';
			$content .= '</tr>';
		}

		$content .= '</tbody></table>';
	}

	$content .= '</form>';

	// JavaScript for select all
	$content .= '<script>
document.getElementById("selectAll").addEventListener("change", function() {
	var checkboxes = document.querySelectorAll(".ruleCheckbox");
	for (var i = 0; i < checkboxes.length; i++) {
		checkboxes[i].checked = this.checked;
	}
});
</script>';

	$adminUI['CONTENT'] = $content;
	\adminUI($adminUI);
	exit;
}

/**
 * Rule Edit Page (Add/Edit)
 */
function adminRuleEdit(): void
{
	// Display any flash messages from redirects
	displayFlashMessages();

	$ruleNum = (int)($_REQUEST['ruleNum'] ?? 0);
	$rule = $ruleNum ? loadRule($ruleNum) : null;
	$isNew = !$rule;

	// Handle save
	if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['saveRule'])) {
		\security_dieOnInvalidCsrfToken();

		$errors = [];

		// Validate inputs
		$tableName = trim($_POST['tableName'] ?? '');
		$ruleName = trim($_POST['ruleName'] ?? '');
		$triggerField = trim($_POST['triggerField'] ?? '');
		$triggerCondition = $_POST['triggerCondition'] ?? 'not_empty';
		$triggerValue = trim($_POST['triggerValue'] ?? '');
		$requiredField = trim($_POST['requiredField'] ?? '');
		$errorMessage = trim($_POST['errorMessage'] ?? '');
		$isActive = isset($_POST['isActive']) ? 1 : 0;
		$ruleOrder = (int)($_POST['ruleOrder'] ?? 0);

		if ($tableName === '') {
			$errors[] = t('Please select a table.');
		}
		if ($ruleName === '') {
			$errors[] = t('Please enter a rule name.');
		}
		if ($triggerField === '') {
			$errors[] = t('Please select a trigger field.');
		}
		if ($requiredField === '') {
			$errors[] = t('Please select a required field.');
		}
		if ($errorMessage === '') {
			$errors[] = t('Please enter an error message.');
		}

		if (empty($errors)) {
			$data = [
				'tableName'        => $tableName,
				'ruleName'         => $ruleName,
				'triggerField'     => $triggerField,
				'triggerCondition' => $triggerCondition,
				'triggerValue'     => $triggerValue,
				'requiredField'    => $requiredField,
				'errorMessage'     => $errorMessage,
				'isActive'         => $isActive,
				'ruleOrder'        => $ruleOrder,
			];

			$savedNum = saveRule($data, $ruleNum ?: null);
			setFlashNotice($isNew ? t('Rule created successfully.') : t('Rule updated successfully.'));
			\redirectBrowserToURL('?_pluginAction=' . urlencode('ConditionalErrorCheckingPro\adminRuleEdit') . '&ruleNum=' . $savedNum);
		} else {
			foreach ($errors as $error) {
				\alert($error);
			}
			// Preserve form data
			$rule = $_POST;
		}
	}

	// Get available tables and condition types
	$tables = getAvailableTables();
	$conditionTypes = getConditionTypes();
	$csrfToken = ($_SESSION['_CSRFToken'] ?? '');

	// Get fields for selected table
	$selectedTable = $rule['tableName'] ?? '';
	$fields = $selectedTable ? getTableFields($selectedTable) : [];

	$adminUI = [];
	$adminUI['PAGE_TITLE'] = [
		t('Plugins') => '?menu=admin&action=plugins',
		t('Conditional Error Checking Pro') => '?_pluginAction=' . urlencode('ConditionalErrorCheckingPro\adminDashboard'),
		t('Rules') => '?_pluginAction=' . urlencode('ConditionalErrorCheckingPro\adminRules'),
		$isNew ? t('Add Rule') : t('Edit Rule'),
	];
	$adminUI['ADVANCED_ACTIONS'] = getAdvancedActions();
	$adminUI['FORM'] = ['autocomplete' => 'off'];
	$adminUI['HIDDEN_FIELDS'] = [
		['name' => '_pluginAction', 'value' => 'ConditionalErrorCheckingPro\adminRuleEdit'],
		['name' => 'saveRule', 'value' => '1'],
	];
	if ($ruleNum) {
		$adminUI['HIDDEN_FIELDS'][] = ['name' => 'ruleNum', 'value' => (string)$ruleNum];
	}
	$adminUI['BUTTONS'] = [
		['name' => '_action=save', 'label' => t('Save Rule')],
		['label' => t('Cancel'), 'href' => '?_pluginAction=' . urlencode('ConditionalErrorCheckingPro\adminRules')],
	];

	$content = getPluginNav('rules');

	// Basic Information Section
	$content .= '<div class="separator"><div>' . t('Basic Information') . '</div></div>';
	$content .= '<div class="form-horizontal">';

	// Table Name
	$content .= '<div class="form-group">';
	$content .= '<label for="tableName" class="col-sm-2 control-label">' . t('Table') . ' <span class="text-danger">*</span></label>';
	$content .= '<div class="col-sm-10">';
	$content .= '<select name="tableName" id="tableName" class="form-control" required onchange="loadTableFields(this.value)" style="width:300px;display:inline-block">';
	$content .= '<option value="">' . t('Select a table...') . '</option>';
	foreach ($tables as $table) {
		$selected = ($selectedTable === $table) ? ' selected' : '';
		$content .= '<option value="' . \htmlencode($table) . '"' . $selected . '>' . \htmlencode($table) . '</option>';
	}
	$content .= '</select>';
	$content .= '</div></div>';

	// Rule Name
	$content .= '<div class="form-group">';
	$content .= '<label for="ruleName" class="col-sm-2 control-label">' . t('Rule Name') . ' <span class="text-danger">*</span></label>';
	$content .= '<div class="col-sm-10">';
	$content .= '<input type="text" name="ruleName" id="ruleName" class="form-control" required value="' . \htmlencode($rule['ruleName'] ?? '') . '" placeholder="' . t('e.g., Phone requires Contact Name') . '" style="width:400px;display:inline-block">';
	$content .= '</div></div>';

	// Rule Order
	$content .= '<div class="form-group">';
	$content .= '<label for="ruleOrder" class="col-sm-2 control-label">' . t('Priority Order') . '</label>';
	$content .= '<div class="col-sm-10">';
	$content .= '<input type="number" name="ruleOrder" id="ruleOrder" class="form-control" value="' . (int)($rule['ruleOrder'] ?? 0) . '" min="0" style="width:100px;display:inline-block">';
	$content .= ' <span class="help-inline">' . t('Lower numbers run first') . '</span>';
	$content .= '</div></div>';

	$content .= '</div>'; // end form-horizontal

	// Trigger Condition Section
	$content .= '<div class="separator"><div>' . t('Trigger Condition') . '</div></div>';
	$content .= '<div class="form-horizontal">';

	// Trigger Field
	$content .= '<div class="form-group">';
	$content .= '<label for="triggerField" class="col-sm-2 control-label">' . t('When Field') . ' <span class="text-danger">*</span></label>';
	$content .= '<div class="col-sm-10">';
	$content .= '<select name="triggerField" id="triggerField" class="form-control" required style="width:300px;display:inline-block">';
	$content .= '<option value="">' . t('Select a field...') . '</option>';
	foreach ($fields as $field) {
		$selected = (($rule['triggerField'] ?? '') === $field['name']) ? ' selected' : '';
		$content .= '<option value="' . \htmlencode($field['name']) . '"' . $selected . '>' . \htmlencode($field['label']) . ' (' . \htmlencode($field['name']) . ')</option>';
	}
	$content .= '</select>';
	$content .= '</div></div>';

	// Trigger Condition Type
	$content .= '<div class="form-group">';
	$content .= '<label for="triggerCondition" class="col-sm-2 control-label">' . t('Condition') . '</label>';
	$content .= '<div class="col-sm-10">';
	$content .= '<select name="triggerCondition" id="triggerCondition" class="form-control" onchange="toggleTriggerValue(this.value)" style="width:200px;display:inline-block">';
	foreach ($conditionTypes as $value => $label) {
		$selected = (($rule['triggerCondition'] ?? 'not_empty') === $value) ? ' selected' : '';
		$content .= '<option value="' . \htmlencode($value) . '"' . $selected . '>' . \htmlencode($label) . '</option>';
	}
	$content .= '</select>';
	$content .= '</div></div>';

	// Trigger Value
	$showTriggerValue = !in_array($rule['triggerCondition'] ?? 'not_empty', ['not_empty', 'is_empty']);
	$triggerValueDisplay = $showTriggerValue ? '' : 'display:none;';
	$content .= '<div class="form-group" id="triggerValueGroup" style="' . $triggerValueDisplay . '">';
	$content .= '<label for="triggerValue" class="col-sm-2 control-label">' . t('Value') . '</label>';
	$content .= '<div class="col-sm-10">';
	$content .= '<input type="text" name="triggerValue" id="triggerValue" class="form-control" value="' . \htmlencode($rule['triggerValue'] ?? '') . '" style="width:300px;display:inline-block">';
	$content .= ' <span class="help-inline" id="triggerValueHelp">' . t('The value to compare against') . '</span>';
	$content .= '</div></div>';

	$content .= '</div>'; // end form-horizontal

	// Required Field Section
	$content .= '<div class="separator"><div>' . t('Required Field') . '</div></div>';
	$content .= '<div class="form-horizontal">';

	// Required Field
	$content .= '<div class="form-group">';
	$content .= '<label for="requiredField" class="col-sm-2 control-label">' . t('Then Require') . ' <span class="text-danger">*</span></label>';
	$content .= '<div class="col-sm-10">';
	$content .= '<select name="requiredField" id="requiredField" class="form-control" required style="width:300px;display:inline-block">';
	$content .= '<option value="">' . t('Select a field...') . '</option>';
	foreach ($fields as $field) {
		$selected = (($rule['requiredField'] ?? '') === $field['name']) ? ' selected' : '';
		$content .= '<option value="' . \htmlencode($field['name']) . '"' . $selected . '>' . \htmlencode($field['label']) . ' (' . \htmlencode($field['name']) . ')</option>';
	}
	$content .= '</select>';
	$content .= '</div></div>';

	// Error Message
	$content .= '<div class="form-group">';
	$content .= '<label for="errorMessage" class="col-sm-2 control-label">' . t('Error Message') . ' <span class="text-danger">*</span></label>';
	$content .= '<div class="col-sm-10">';
	$content .= '<textarea name="errorMessage" id="errorMessage" class="form-control" rows="2" required placeholder="' . t('Please enter a value for this field.') . '" style="width:500px">' . \htmlencode($rule['errorMessage'] ?? '') . '</textarea>';
	$content .= '</div></div>';

	$content .= '</div>'; // end form-horizontal

	// Options Section
	$content .= '<div class="separator"><div>' . t('Options') . '</div></div>';
	$content .= '<div class="form-horizontal">';

	// Is Active
	$isActiveChecked = ($rule['isActive'] ?? 1) ? ' checked' : '';
	$content .= '<div class="form-group">';
	$content .= '<div class="col-sm-2 control-label">' . t('Status') . '</div>';
	$content .= '<div class="col-sm-10">';
	$content .= '<div class="checkbox"><label>';
	$content .= '<input type="hidden" name="isActive" value="0">';
	$content .= '<input type="checkbox" name="isActive" value="1"' . $isActiveChecked . '> ' . t('Rule is active');
	$content .= '</label></div>';
	$content .= '</div></div>';

	$content .= '</div>'; // end form-horizontal

	// JavaScript for dynamic field loading
	$content .= '<script>
function loadTableFields(tableName) {
	if (!tableName) {
		document.getElementById("triggerField").innerHTML = \'<option value="">' . t('Select a field...') . '</option>\';
		document.getElementById("requiredField").innerHTML = \'<option value="">' . t('Select a field...') . '</option>\';
		return;
	}

	var xhr = new XMLHttpRequest();
	xhr.open("GET", "?_pluginAction=' . urlencode('ConditionalErrorCheckingPro\ajaxGetFields') . '&tableName=" + encodeURIComponent(tableName), true);
	xhr.onload = function() {
		if (xhr.status === 200) {
			var fields = JSON.parse(xhr.responseText);
			var options = \'<option value="">' . t('Select a field...') . '</option>\';
			for (var i = 0; i < fields.length; i++) {
				options += \'<option value="\' + fields[i].name + \'">\' + fields[i].label + \' (\' + fields[i].name + \')</option>\';
			}
			document.getElementById("triggerField").innerHTML = options;
			document.getElementById("requiredField").innerHTML = options;
		}
	};
	xhr.send();
}

function toggleTriggerValue(condition) {
	var group = document.getElementById("triggerValueGroup");
	var help = document.getElementById("triggerValueHelp");
	if (condition === "not_empty" || condition === "is_empty") {
		group.style.display = "none";
	} else {
		group.style.display = "";
		if (condition === "regex_match") {
			help.textContent = "' . t('Enter a regular expression pattern (e.g., /^\\d{10}$/).') . '";
		} else {
			help.textContent = "' . t('The value to compare against.') . '";
		}
	}
}
</script>';

	$adminUI['CONTENT'] = $content;
	\adminUI($adminUI);
	exit;
}

/**
 * AJAX handler for getting table fields
 */
function ajaxGetFields(): void
{
	$tableName = $_GET['tableName'] ?? '';
	$fields = [];

	if ($tableName) {
		$tableFields = getTableFields($tableName);
		foreach ($tableFields as $field) {
			$fields[] = [
				'name'  => $field['name'],
				'label' => $field['label'],
				'type'  => $field['type'],
			];
		}
	}

	header('Content-Type: application/json');
	echo json_encode($fields);
	exit;
}

/**
 * Logs Page
 */
function adminLogs(): void
{
	// Display any flash messages from redirects
	displayFlashMessages();

	$action = $_REQUEST['_action'] ?? '';
	$csrfToken = ($_SESSION['_CSRFToken'] ?? '');

	// Handle clear old logs (GET action - uses referer check)
	if ($action === 'clearOld') {
		\security_dieUnlessInternalReferer();
		$settings = loadSettings();
		$days = $settings['logRetentionDays'] ?? 30;
		$deleted = clearOldLogs($days);
		setFlashNotice(sprintf(t('Cleared %d log entries older than %d days.'), $deleted, $days));
		\redirectBrowserToURL('?_pluginAction=' . urlencode('ConditionalErrorCheckingPro\adminLogs'));
	}

	// Handle CSV export
	if ($action === 'exportCsv') {
		$filters = [
			'tableName'  => $_GET['tableName'] ?? '',
			'ruleNum'    => $_GET['ruleNum'] ?? '',
			'wasBlocked' => $_GET['wasBlocked'] ?? '',
			'dateFrom'   => $_GET['dateFrom'] ?? '',
			'dateTo'     => $_GET['dateTo'] ?? '',
		];

		$result = getLogs(1, 10000, $filters);
		$logs = $result['logs'];

		header('Content-Type: text/csv');
		header('Content-Disposition: attachment; filename="validation_logs_' . date('Y-m-d') . '.csv"');

		$output = fopen('php://output', 'w');
		fputcsv($output, ['Date', 'Table', 'Record #', 'Rule', 'Trigger Field', 'Trigger Value', 'Required Field', 'Required Value', 'Status', 'Error Message']);

		foreach ($logs as $log) {
			fputcsv($output, [
				$log['createdDate'],
				$log['tableName'],
				$log['recordNum'],
				$log['ruleName'],
				$log['triggerField'],
				$log['triggerValue'],
				$log['requiredField'],
				$log['requiredValue'],
				$log['wasBlocked'] ? 'Blocked' : 'Passed',
				$log['errorMessage'],
			]);
		}

		fclose($output);
		exit;
	}

	// Get filters
	$filters = [
		'tableName'  => $_GET['tableName'] ?? '',
		'ruleNum'    => $_GET['ruleNum'] ?? '',
		'wasBlocked' => $_GET['wasBlocked'] ?? '',
		'dateFrom'   => $_GET['dateFrom'] ?? '',
		'dateTo'     => $_GET['dateTo'] ?? '',
	];

	$page = max(1, (int)($_GET['page'] ?? 1));
	$result = getLogs($page, 50, $filters);
	$logs = $result['logs'];
	$totalPages = $result['totalPages'];
	$totalCount = $result['totalCount'];

	// Get available tables for filter
	$tables = getAvailableTables();

	$adminUI = [];
	$adminUI['PAGE_TITLE'] = [
		t('Plugins') => '?menu=admin&action=plugins',
		t('Conditional Error Checking Pro') => '?_pluginAction=' . urlencode('ConditionalErrorCheckingPro\adminDashboard'),
		t('Logs'),
	];
	$adminUI['ADVANCED_ACTIONS'] = getAdvancedActions();

	$content = getPluginNav('logs');

	// Filter Section
	$content .= '<div class="separator"><div>' . t('Filter Logs') . '</div></div>';
	$content .= '<form method="get">';
	$content .= '<input type="hidden" name="_pluginAction" value="ConditionalErrorCheckingPro\adminLogs">';
	$content .= '<div class="form-horizontal">';

	// Table filter
	$content .= '<div class="form-group">';
	$content .= '<label for="tableName" class="col-sm-2 control-label">' . t('Table') . '</label>';
	$content .= '<div class="col-sm-10">';
	$content .= '<select name="tableName" id="tableName" class="form-control" style="width:200px;display:inline-block">';
	$content .= '<option value="">' . t('All Tables') . '</option>';
	foreach ($tables as $table) {
		$selected = ($filters['tableName'] === $table) ? ' selected' : '';
		$content .= '<option value="' . \htmlencode($table) . '"' . $selected . '>' . \htmlencode($table) . '</option>';
	}
	$content .= '</select>';
	$content .= '</div></div>';

	// Status filter
	$content .= '<div class="form-group">';
	$content .= '<label for="wasBlocked" class="col-sm-2 control-label">' . t('Status') . '</label>';
	$content .= '<div class="col-sm-10">';
	$content .= '<select name="wasBlocked" id="wasBlocked" class="form-control" style="width:200px;display:inline-block">';
	$content .= '<option value="">' . t('All') . '</option>';
	$blockedSelected = ($filters['wasBlocked'] === '1') ? ' selected' : '';
	$content .= '<option value="1"' . $blockedSelected . '>' . t('Blocked Only') . '</option>';
	$content .= '</select>';
	$content .= '</div></div>';

	// Date range
	$content .= '<div class="form-group">';
	$content .= '<label for="dateFrom" class="col-sm-2 control-label">' . t('Date Range') . '</label>';
	$content .= '<div class="col-sm-10">';
	$content .= '<input type="date" name="dateFrom" id="dateFrom" class="form-control" value="' . \htmlencode($filters['dateFrom']) . '" style="width:150px;display:inline-block">';
	$content .= ' <span class="help-inline">' . t('to') . '</span> ';
	$content .= '<input type="date" name="dateTo" id="dateTo" class="form-control" value="' . \htmlencode($filters['dateTo']) . '" style="width:150px;display:inline-block">';
	$content .= '</div></div>';

	// Build export URL with current filters
	$exportParams = array_filter($filters);
	$exportParams['_pluginAction'] = 'ConditionalErrorCheckingPro\adminLogs';
	$exportParams['_action'] = 'exportCsv';
	$exportUrl = '?' . http_build_query($exportParams);

	// Buttons
	$content .= '<div class="form-group">';
	$content .= '<div class="col-sm-2 control-label"></div>';
	$content .= '<div class="col-sm-10">';
	$content .= '<button type="submit" class="btn btn-primary">' . t('Filter') . '</button>';
	$content .= ' <a href="?_pluginAction=' . urlencode('ConditionalErrorCheckingPro\adminLogs') . '" class="btn btn-default">' . t('Reset') . '</a>';
	$content .= ' <a href="' . \htmlencode($exportUrl) . '" class="btn btn-default">';
	$content .= '<i class="fa-duotone fa-solid fa-download"></i> ' . t('Export CSV');
	$content .= '</a>';
	$content .= '</div></div>';

	$content .= '</div>'; // end form-horizontal
	$content .= '</form>';

	// Results info
	$content .= '<p class="text-muted">' . sprintf(t('Showing page %d of %d (%d total entries)'), $page, max(1, $totalPages), $totalCount) . '</p>';

	if (empty($logs)) {
		$content .= '<div class="alert alert-info">' . t('No log entries found.') . '</div>';
	} else {
		$content .= '<table class="table table-striped table-hover">';
		$content .= '<thead><tr>';
		$content .= '<th>' . t('Date') . '</th>';
		$content .= '<th>' . t('Table') . '</th>';
		$content .= '<th>' . t('Record') . '</th>';
		$content .= '<th>' . t('Rule') . '</th>';
		$content .= '<th>' . t('Trigger') . '</th>';
		$content .= '<th>' . t('Required') . '</th>';
		$content .= '<th>' . t('Status') . '</th>';
		$content .= '</tr></thead>';
		$content .= '<tbody>';

		foreach ($logs as $log) {
			$statusBadge = $log['wasBlocked']
				? '<span class="badge" style="background-color:#d9534f;color:#fff">' . t('Blocked') . '</span>'
				: '<span class="badge" style="background-color:#5cb85c;color:#fff">' . t('Passed') . '</span>';

			$triggerInfo = $log['triggerField'];
			if ($log['triggerValue']) {
				$triggerInfo .= ': ' . mb_substr($log['triggerValue'], 0, 30) . (mb_strlen($log['triggerValue']) > 30 ? '...' : '');
			}

			$requiredInfo = $log['requiredField'];
			if ($log['requiredValue']) {
				$requiredInfo .= ': ' . mb_substr($log['requiredValue'], 0, 30) . (mb_strlen($log['requiredValue']) > 30 ? '...' : '');
			}

			$content .= '<tr>';
			$content .= '<td>' . \htmlencode(date('M j, g:i a', strtotime($log['createdDate']))) . '</td>';
			$content .= '<td>' . \htmlencode($log['tableName']) . '</td>';
			$editUrl = '?menu=' . urlencode($log['tableName']) . '&action=edit&num=' . (int)$log['recordNum'];
			$content .= '<td><a href="' . \htmlencode($editUrl) . '" target="_blank">#' . (int)$log['recordNum'] . '</a></td>';
			$content .= '<td>' . \htmlencode($log['ruleName']) . '</td>';
			$content .= '<td><small>' . \htmlencode($triggerInfo) . '</small></td>';
			$content .= '<td><small>' . \htmlencode($requiredInfo) . '</small></td>';
			$content .= '<td>' . $statusBadge . '</td>';
			$content .= '</tr>';

			// Show error message on blocked
			if ($log['wasBlocked'] && $log['errorMessage']) {
				$content .= '<tr class="warning"><td colspan="7"><small><strong>' . t('Error:') . '</strong> ' . \htmlencode($log['errorMessage']) . '</small></td></tr>';
			}
		}

		$content .= '</tbody></table>';

		// Pagination
		if ($totalPages > 1) {
			$content .= '<nav aria-label="' . t('Log pagination') . '"><ul class="pagination">';

			// Previous
			if ($page > 1) {
				$prevUrl = '?_pluginAction=' . urlencode('ConditionalErrorCheckingPro\adminLogs') . '&page=' . ($page - 1) . '&' . http_build_query($filters);
				$content .= '<li><a href="' . \htmlencode($prevUrl) . '" aria-label="' . t('Previous') . '">&laquo;</a></li>';
			} else {
				$content .= '<li class="disabled"><span>&laquo;</span></li>';
			}

			// Page numbers
			$startPage = max(1, $page - 2);
			$endPage = min($totalPages, $page + 2);

			for ($i = $startPage; $i <= $endPage; $i++) {
				$pageUrl = '?_pluginAction=' . urlencode('ConditionalErrorCheckingPro\adminLogs') . '&page=' . $i . '&' . http_build_query($filters);
				if ($i === $page) {
					$content .= '<li class="active"><span>' . $i . '</span></li>';
				} else {
					$content .= '<li><a href="' . \htmlencode($pageUrl) . '">' . $i . '</a></li>';
				}
			}

			// Next
			if ($page < $totalPages) {
				$nextUrl = '?_pluginAction=' . urlencode('ConditionalErrorCheckingPro\adminLogs') . '&page=' . ($page + 1) . '&' . http_build_query($filters);
				$content .= '<li><a href="' . \htmlencode($nextUrl) . '" aria-label="' . t('Next') . '">&raquo;</a></li>';
			} else {
				$content .= '<li class="disabled"><span>&raquo;</span></li>';
			}

			$content .= '</ul></nav>';
		}
	}

	$adminUI['CONTENT'] = $content;
	\adminUI($adminUI);
	exit;
}

/**
 * Settings Page
 */
function adminSettings(): void
{
	// Display any flash messages from redirects
	displayFlashMessages();

	$action = $_REQUEST['_action'] ?? '';
	$csrfToken = ($_SESSION['_CSRFToken'] ?? '');

	// Handle export
	if ($action === 'export') {
		$json = exportRules();
		header('Content-Type: application/json');
		header('Content-Disposition: attachment; filename="conditional_error_checking_rules_' . date('Y-m-d') . '.json"');
		echo $json;
		exit;
	}

	// Handle import form
	if ($action === 'importForm') {
		$adminUI = [];
		$adminUI['PAGE_TITLE'] = [
			t('Plugins') => '?menu=admin&action=plugins',
			t('Conditional Error Checking Pro') => '?_pluginAction=' . urlencode('ConditionalErrorCheckingPro\adminDashboard'),
			t('Settings') => '?_pluginAction=' . urlencode('ConditionalErrorCheckingPro\adminSettings'),
			t('Import Rules'),
		];
		$adminUI['ADVANCED_ACTIONS'] = getAdvancedActions();
		$adminUI['FORM'] = ['autocomplete' => 'off', 'enctype' => 'multipart/form-data'];
		$adminUI['HIDDEN_FIELDS'] = [
			['name' => '_pluginAction', 'value' => 'ConditionalErrorCheckingPro\adminSettings'],
			['name' => '_action', 'value' => 'import'],
		];
		$adminUI['BUTTONS'] = [
			['name' => '_action=import', 'label' => t('Import')],
			['label' => t('Cancel'), 'href' => '?_pluginAction=' . urlencode('ConditionalErrorCheckingPro\adminSettings')],
		];

		$content = getPluginNav('settings');

		// Import Section
		$content .= '<div class="separator"><div>' . t('Import Rules from JSON') . '</div></div>';
		$content .= '<div class="form-horizontal">';

		$content .= '<div class="form-group">';
		$content .= '<label for="importFile" class="col-sm-2 control-label">' . t('JSON File') . '</label>';
		$content .= '<div class="col-sm-10">';
		$content .= '<input type="file" name="importFile" id="importFile" accept=".json" required>';
		$content .= '<p class="help-block">' . t('Upload a previously exported rules JSON file.') . '</p>';
		$content .= '</div></div>';

		$content .= '</div>'; // end form-horizontal

		$adminUI['CONTENT'] = $content;
		\adminUI($adminUI);
		exit;
	}

	// Handle import
	if ($action === 'import' && $_SERVER['REQUEST_METHOD'] === 'POST') {
		\security_dieOnInvalidCsrfToken();

		if (!empty($_FILES['importFile']['tmp_name'])) {
			$json = file_get_contents($_FILES['importFile']['tmp_name']);
			$result = importRules($json);

			if ($result['success']) {
				setFlashNotice($result['message']);
			} else {
				setFlashAlert($result['message']);
			}
		} else {
			setFlashAlert(t('Please select a file to import.'));
		}

		\redirectBrowserToURL('?_pluginAction=' . urlencode('ConditionalErrorCheckingPro\adminSettings'));
	}

	// Handle save settings
	if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['saveSettings'])) {
		\security_dieOnInvalidCsrfToken();

		$settings = loadSettings();

		$settings['pluginEnabled'] = isset($_POST['pluginEnabled']) ? true : false;
		$settings['logRetentionDays'] = max(1, min(365, (int)($_POST['logRetentionDays'] ?? 30)));
		$settings['maxRulesPerTable'] = max(1, min(500, (int)($_POST['maxRulesPerTable'] ?? 50)));
		$settings['emailNotifications'] = isset($_POST['emailNotifications']) ? true : false;
		$settings['notificationEmail'] = trim($_POST['notificationEmail'] ?? '');
		$settings['debugMode'] = isset($_POST['debugMode']) ? true : false;

		// Handle excluded tables (textarea, one per line)
		$excludedTablesRaw = trim($_POST['excludedTables'] ?? '');
		$excludedTables = array_filter(array_map('trim', explode("\n", $excludedTablesRaw)));
		$settings['excludedTables'] = $excludedTables;

		if (saveSettings($settings)) {
			setFlashNotice(t('Settings saved successfully.'));
		} else {
			setFlashAlert(t('Failed to save settings.'));
		}

		\redirectBrowserToURL('?_pluginAction=' . urlencode('ConditionalErrorCheckingPro\adminSettings'));
	}

	// Load current settings
	$settings = loadSettings();

	$adminUI = [];
	$adminUI['PAGE_TITLE'] = [
		t('Plugins') => '?menu=admin&action=plugins',
		t('Conditional Error Checking Pro') => '?_pluginAction=' . urlencode('ConditionalErrorCheckingPro\adminDashboard'),
		t('Settings'),
	];
	$adminUI['ADVANCED_ACTIONS'] = getAdvancedActions();
	$adminUI['FORM'] = ['autocomplete' => 'off'];
	$adminUI['HIDDEN_FIELDS'] = [
		['name' => '_pluginAction', 'value' => 'ConditionalErrorCheckingPro\adminSettings'],
		['name' => 'saveSettings', 'value' => '1'],
	];
	$adminUI['BUTTONS'] = [
		['name' => '_action=save', 'label' => t('Save Settings')],
	];

	$content = getPluginNav('settings');

	// General Settings Section
	$content .= '<div class="separator"><div>' . t('General Settings') . '</div></div>';
	$content .= '<div class="form-horizontal">';

	// Plugin Enabled
	$enabledChecked = $settings['pluginEnabled'] ? ' checked' : '';
	$content .= '<div class="form-group">';
	$content .= '<div class="col-sm-2 control-label">' . t('Validation') . '</div>';
	$content .= '<div class="col-sm-10">';
	$content .= '<div class="checkbox"><label>';
	$content .= '<input type="hidden" name="pluginEnabled" value="0">';
	$content .= '<input type="checkbox" name="pluginEnabled" value="1"' . $enabledChecked . '> ' . t('Enable validation rules');
	$content .= '</label></div>';
	$content .= '<p class="help-block">' . t('When disabled, no validation rules will be enforced on any table.') . '</p>';
	$content .= '</div></div>';

	// Log Retention
	$content .= '<div class="form-group">';
	$content .= '<label for="logRetentionDays" class="col-sm-2 control-label">' . t('Log Retention') . '</label>';
	$content .= '<div class="col-sm-10">';
	$content .= '<input type="number" name="logRetentionDays" id="logRetentionDays" class="form-control" value="' . (int)$settings['logRetentionDays'] . '" min="1" max="365" style="width:100px;display:inline-block">';
	$content .= ' <span class="help-inline">' . t('days') . '</span>';
	$content .= '<p class="help-block">' . t('Logs older than this will be removed when using Clear Old Logs. Only triggered rules are logged.') . '</p>';
	$content .= '</div></div>';

	// Max Rules Per Table
	$content .= '<div class="form-group">';
	$content .= '<label for="maxRulesPerTable" class="col-sm-2 control-label">' . t('Max Rules Per Table') . '</label>';
	$content .= '<div class="col-sm-10">';
	$content .= '<input type="number" name="maxRulesPerTable" id="maxRulesPerTable" class="form-control" value="' . (int)$settings['maxRulesPerTable'] . '" min="1" max="500" style="width:100px;display:inline-block">';
	$content .= '<p class="help-block">' . t('Limits how many rules can be created for a single table. Helps prevent performance issues.') . '</p>';
	$content .= '</div></div>';

	$content .= '</div>'; // end form-horizontal

	// Email Notifications Section
	$content .= '<div class="separator"><div>' . t('Email Notifications') . '</div></div>';
	$content .= '<div class="form-horizontal">';

	// Email Notifications Enabled
	$emailChecked = $settings['emailNotifications'] ? ' checked' : '';
	$content .= '<div class="form-group">';
	$content .= '<div class="col-sm-2 control-label">' . t('Notifications') . '</div>';
	$content .= '<div class="col-sm-10">';
	$content .= '<div class="checkbox"><label>';
	$content .= '<input type="hidden" name="emailNotifications" value="0">';
	$content .= '<input type="checkbox" name="emailNotifications" value="1"' . $emailChecked . '> ' . t('Send email when a save is blocked');
	$content .= '</label></div>';
	$content .= '</div></div>';

	// Notification Email
	$content .= '<div class="form-group">';
	$content .= '<label for="notificationEmail" class="col-sm-2 control-label">' . t('Email Address') . '</label>';
	$content .= '<div class="col-sm-10">';
	$content .= '<input type="email" name="notificationEmail" id="notificationEmail" class="form-control" value="' . \htmlencode($settings['notificationEmail']) . '" placeholder="admin@example.com" style="width:300px;display:inline-block">';
	$content .= '</div></div>';

	$content .= '</div>'; // end form-horizontal

	// Advanced Settings Section
	$content .= '<div class="separator"><div>' . t('Advanced Settings') . '</div></div>';
	$content .= '<div class="form-horizontal">';

	// Excluded Tables
	$excludedTablesText = implode("\n", $settings['excludedTables'] ?? []);
	$content .= '<div class="form-group">';
	$content .= '<label for="excludedTables" class="col-sm-2 control-label">' . t('Excluded Tables') . '</label>';
	$content .= '<div class="col-sm-10">';
	$content .= '<textarea name="excludedTables" id="excludedTables" class="form-control" rows="4" placeholder="' . t('One table name per line') . '" style="width:300px">' . \htmlencode($excludedTablesText) . '</textarea>';
	$content .= '<p class="help-block">' . t('Tables that should not appear in the rule creation dropdown.') . '</p>';
	$content .= '</div></div>';

	// Debug Mode
	$debugChecked = $settings['debugMode'] ? ' checked' : '';
	$content .= '<div class="form-group">';
	$content .= '<div class="col-sm-2 control-label">' . t('Debug Mode') . '</div>';
	$content .= '<div class="col-sm-10">';
	$content .= '<div class="checkbox"><label>';
	$content .= '<input type="hidden" name="debugMode" value="0">';
	$content .= '<input type="checkbox" name="debugMode" value="1"' . $debugChecked . '> ' . t('Enable debug mode');
	$content .= '</label></div>';
	$content .= '<p class="help-block">' . t('Logs additional information for troubleshooting. Disable in production for better performance.') . '</p>';
	$content .= '</div></div>';

	$content .= '</div>'; // end form-horizontal

	$adminUI['CONTENT'] = $content;
	\adminUI($adminUI);
	exit;
}

/**
 * Help Page
 */
function adminHelp(): void
{
	$adminUI = [];
	$adminUI['PAGE_TITLE'] = [
		t('Plugins') => '?menu=admin&action=plugins',
		t('Conditional Error Checking Pro') => '?_pluginAction=' . urlencode('ConditionalErrorCheckingPro\adminDashboard'),
		t('Help'),
	];
	$adminUI['ADVANCED_ACTIONS'] = getAdvancedActions();

	$content = getPluginNav('help');

	// Getting Started
	$content .= '<div class="separator"><div>' . t('Getting Started') . '</div></div>';
	$content .= '<p>' . t('Conditional Error Checking Pro allows you to create validation rules that require certain fields based on the values of other fields. This is useful for creating dynamic forms where some fields become required only under certain conditions.') . '</p>';
	$content .= '<h4>' . t('Quick Start') . '</h4>';
	$content .= '<ol>';
	$content .= '<li>' . t('Go to the <strong>Rules</strong> page and click <strong>Add New Rule</strong>') . '</li>';
	$content .= '<li>' . t('Select the table you want to add validation to') . '</li>';
	$content .= '<li>' . t('Choose a trigger field and condition (e.g., "contact_method is not empty")') . '</li>';
	$content .= '<li>' . t('Choose the field that becomes required when the trigger condition is met') . '</li>';
	$content .= '<li>' . t('Enter an error message to display if the required field is empty') . '</li>';
	$content .= '<li>' . t('Save the rule and it will start validating immediately') . '</li>';
	$content .= '</ol>';

	// Condition Types
	$content .= '<div class="separator"><div>' . t('Condition Types') . '</div></div>';
	$content .= '<div class="table-responsive">';
	$content .= '<table class="table table-hover">';
	$content .= '<thead><tr><th>' . t('Condition') . '</th><th>' . t('Description') . '</th><th>' . t('Example') . '</th></tr></thead>';
	$content .= '<tbody>';
	$content .= '<tr><td><code>is not empty</code></td><td>' . t('Field has any value') . '</td><td>' . t('If phone is not empty...') . '</td></tr>';
	$content .= '<tr><td><code>is empty</code></td><td>' . t('Field is empty') . '</td><td>' . t('If status is empty...') . '</td></tr>';
	$content .= '<tr><td><code>equals</code></td><td>' . t('Exact match') . '</td><td>' . t('If type equals "premium"...') . '</td></tr>';
	$content .= '<tr><td><code>does not equal</code></td><td>' . t('Does not match') . '</td><td>' . t('If status does not equal "archived"...') . '</td></tr>';
	$content .= '<tr><td><code>contains</code></td><td>' . t('Contains substring') . '</td><td>' . t('If name contains "test"...') . '</td></tr>';
	$content .= '<tr><td><code>does not contain</code></td><td>' . t('Missing substring') . '</td><td>' . t('If email does not contain "@"...') . '</td></tr>';
	$content .= '<tr><td><code>is greater than</code></td><td>' . t('Numeric comparison') . '</td><td>' . t('If priority > 5...') . '</td></tr>';
	$content .= '<tr><td><code>is less than</code></td><td>' . t('Numeric comparison') . '</td><td>' . t('If count < 0...') . '</td></tr>';
	$content .= '<tr><td><code>matches pattern</code></td><td>' . t('Regex pattern match') . '</td><td>' . t('If phone matches /^\\d{10}$/...') . '</td></tr>';
	$content .= '</tbody></table>';
	$content .= '</div>';

	// Examples
	$content .= '<div class="separator"><div>' . t('Example Rules') . '</div></div>';

	$content .= '<h4>' . t('Phone requires Contact Name') . '</h4>';
	$content .= '<ul>';
	$content .= '<li><strong>' . t('Table:') . '</strong> contacts</li>';
	$content .= '<li><strong>' . t('Trigger:') . '</strong> phone is not empty</li>';
	$content .= '<li><strong>' . t('Required:') . '</strong> contact_name</li>';
	$content .= '<li><strong>' . t('Error:') . '</strong> "Please enter a contact name when providing a phone number."</li>';
	$content .= '</ul>';

	$content .= '<h4>' . t('Premium Status requires Payment Method') . '</h4>';
	$content .= '<ul>';
	$content .= '<li><strong>' . t('Table:') . '</strong> members</li>';
	$content .= '<li><strong>' . t('Trigger:') . '</strong> membership_type equals "premium"</li>';
	$content .= '<li><strong>' . t('Required:') . '</strong> payment_method</li>';
	$content .= '<li><strong>' . t('Error:') . '</strong> "Premium members must have a payment method on file."</li>';
	$content .= '</ul>';

	$content .= '<h4>' . t('Shipping Address requires City and Zip') . '</h4>';
	$content .= '<ul>';
	$content .= '<li><strong>' . t('Table:') . '</strong> orders</li>';
	$content .= '<li><strong>' . t('Trigger:') . '</strong> shipping_address is not empty</li>';
	$content .= '<li><strong>' . t('Required:') . '</strong> shipping_city (create separate rule for shipping_zip)</li>';
	$content .= '<li><strong>' . t('Error:') . '</strong> "Please enter a city for the shipping address."</li>';
	$content .= '</ul>';

	// Import/Export
	$content .= '<div class="separator"><div>' . t('Import/Export') . '</div></div>';
	$content .= '<p>' . t('You can export all your rules to a JSON file for backup or sharing with other CMS installations. Use the Advanced Actions menu to access these features:') . '</p>';
	$content .= '<ul>';
	$content .= '<li><strong>' . t('Export Rules:') . '</strong> ' . t('Downloads all rules as a JSON file') . '</li>';
	$content .= '<li><strong>' . t('Import Rules:') . '</strong> ' . t('Upload a previously exported JSON file to add rules') . '</li>';
	$content .= '</ul>';
	$content .= '<p class="text-info"><i class="fa-duotone fa-solid fa-info-circle"></i> ' . t('When importing, duplicate rules (same table + rule name) will be skipped.') . '</p>';

	// Troubleshooting
	$content .= '<div class="separator"><div>' . t('Troubleshooting') . '</div></div>';

	$content .= '<h4>' . t('Rules not working?') . '</h4>';
	$content .= '<ul>';
	$content .= '<li>' . t('Check that the plugin is enabled in Settings') . '</li>';
	$content .= '<li>' . t('Verify the rule is marked as Active') . '</li>';
	$content .= '<li>' . t('Make sure the table is not in the excluded tables list') . '</li>';
	$content .= '<li>' . t('Check the Logs page to see if the rule is being triggered') . '</li>';
	$content .= '</ul>';

	$content .= '<h4>' . t('Fields not showing in dropdown?') . '</h4>';
	$content .= '<ul>';
	$content .= '<li>' . t('The plugin reads field names from schema files') . '</li>';
	$content .= '<li>' . t('System fields starting with underscore are hidden') . '</li>';
	$content .= '<li>' . t('If a field was recently added, the schema file needs to be regenerated') . '</li>';
	$content .= '</ul>';

	// Version Info
	$content .= '<div class="separator"><div>' . t('Version Information') . '</div></div>';

	$content .= '<p><strong>' . t('Version:') . '</strong> ' . \htmlencode($GLOBALS['CONDITIONALERRORCHECKING_PRO_VERSION'] ?? '1.00') . '</p>';
	$content .= '<p><strong>' . t('Author:') . '</strong> <a href="https://www.sagentic.com" target="_blank" rel="noopener">Sagentic Web Design <span class="sr-only">' . t('(opens in new tab)') . '</span></a></p>';

	$adminUI['CONTENT'] = $content;
	\adminUI($adminUI);
	exit;
}
