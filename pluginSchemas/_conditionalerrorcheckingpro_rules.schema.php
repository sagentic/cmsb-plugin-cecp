<?php
/**
 * Schema for Conditional Error Checking Pro - Rules Table
 *
 * This schema is for reference and documentation.
 * The actual table is created programmatically in createTablesIfNeeded().
 */

return [
	'menuName'    => 'Error Checking Rules',
	'_tableName'  => '_conditionalerrorcheckingpro_rules',
	'menuType'    => 'hidden',
	'_primaryKey' => 'num',

	'num' => [
		'type'          => 'none',
		'isSystemField' => '1',
	],

	'tableName' => [
		'label'      => 'Table Name',
		'type'       => 'textfield',
		'isRequired' => '1',
		'maxLength'  => 255,
	],

	'ruleName' => [
		'label'      => 'Rule Name',
		'type'       => 'textfield',
		'isRequired' => '1',
		'maxLength'  => 255,
	],

	'triggerField' => [
		'label'      => 'Trigger Field',
		'type'       => 'textfield',
		'isRequired' => '1',
		'maxLength'  => 255,
	],

	'triggerCondition' => [
		'label'       => 'Trigger Condition',
		'type'        => 'list',
		'listType'    => 'pulldown',
		'optionsType' => 'text',
		'optionsText' => "not_empty|Is Not Empty\nis_empty|Is Empty\nequals|Equals\nnot_equals|Does Not Equal\ncontains|Contains\nnot_contains|Does Not Contain\ngreater_than|Greater Than\nless_than|Less Than\nregex_match|Matches Pattern (Regex)",
	],

	'triggerValue' => [
		'label' => 'Trigger Value',
		'type'  => 'textbox',
	],

	'requiredField' => [
		'label'      => 'Required Field',
		'type'       => 'textfield',
		'isRequired' => '1',
		'maxLength'  => 255,
	],

	'errorMessage' => [
		'label'      => 'Error Message',
		'type'       => 'textbox',
		'isRequired' => '1',
	],

	'isActive' => [
		'label'       => 'Active',
		'type'        => 'checkbox',
		'checkedByDefault' => '1',
	],

	'ruleOrder' => [
		'label' => 'Rule Order',
		'type'  => 'textfield',
	],

	'createdDate' => [
		'label'         => 'Created Date',
		'type'          => 'none',
		'isSystemField' => '1',
	],

	'updatedDate' => [
		'label'         => 'Updated Date',
		'type'          => 'none',
		'isSystemField' => '1',
	],

	'createdByUserNum' => [
		'label'         => 'Created By',
		'type'          => 'none',
		'isSystemField' => '1',
	],

	'updatedByUserNum' => [
		'label'         => 'Updated By',
		'type'          => 'none',
		'isSystemField' => '1',
	],
];
