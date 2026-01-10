<?php
/**
 * Schema for Conditional Error Checking Pro - Logs Table
 *
 * This schema is for reference and documentation.
 * The actual table is created programmatically in createTablesIfNeeded().
 */

return [
	'menuName'    => 'Error Checking Logs',
	'_tableName'  => '_conditionalerrorcheckingpro_logs',
	'menuType'    => 'hidden',
	'_primaryKey' => 'num',

	'num' => [
		'type'          => 'none',
		'isSystemField' => '1',
	],

	'tableName' => [
		'label'    => 'Table Name',
		'type'     => 'textfield',
		'maxLength' => 255,
	],

	'recordNum' => [
		'label' => 'Record Number',
		'type'  => 'textfield',
	],

	'ruleNum' => [
		'label' => 'Rule Number',
		'type'  => 'textfield',
	],

	'ruleName' => [
		'label'    => 'Rule Name',
		'type'     => 'textfield',
		'maxLength' => 255,
	],

	'errorMessage' => [
		'label' => 'Error Message',
		'type'  => 'textbox',
	],

	'triggerField' => [
		'label'    => 'Trigger Field',
		'type'     => 'textfield',
		'maxLength' => 255,
	],

	'triggerValue' => [
		'label' => 'Trigger Value',
		'type'  => 'textbox',
	],

	'requiredField' => [
		'label'    => 'Required Field',
		'type'     => 'textfield',
		'maxLength' => 255,
	],

	'requiredValue' => [
		'label' => 'Required Value',
		'type'  => 'textbox',
	],

	'wasBlocked' => [
		'label' => 'Was Blocked',
		'type'  => 'checkbox',
	],

	'createdDate' => [
		'label'         => 'Created Date',
		'type'          => 'none',
		'isSystemField' => '1',
	],

	'createdByUserNum' => [
		'label'         => 'Created By',
		'type'          => 'none',
		'isSystemField' => '1',
	],
];
