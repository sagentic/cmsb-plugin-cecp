<?php
/**
 * Menu Schema for Conditional Error Checking Pro
 *
 * Copy this file to /cmsb/data/schema/ to add a dedicated menu entry.
 *
 * Note: The plugin already registers itself in the Plugins menu automatically.
 * This file is only needed if you want a separate top-level menu entry.
 */

return [
	'menuName'    => 'Error Checking Pro',
	'_tableName'  => 'conditionalerrorcheckingpro',
	'menuType'    => 'link',
	'_pluginLink' => '?_pluginAction=ConditionalErrorCheckingPro\adminDashboard',
	'menuOrder'   => 1000,
];
