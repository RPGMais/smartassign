<?php

require_once '../inc/config.class.php';
require_once '../inc/logger.class.php';
require_once '../inc/config.form.class.php';

/**
 * render menu bar
 */
Html::header('SmartAssign', $_SERVER['PHP_SELF'], "plugins", SmartAssignConfigClass::$PLUGIN_SMARTASSIGN_CODE, "config");

$pluginSmartAssignConfigClass = new SmartAssignConfigFormClass();

/**
 * check for post form data and perform requested action
 */
if (isset($_REQUEST['save'])) {
    PluginSmartAssignLogger::addWarning(__METHOD__ . ' - SAVE: POST: ', $_POST);
	$pluginSmartAssignConfigClass->saveSettings();
    Session::AddMessageAfterRedirect('Configuração salva');
    Html::back();
}

if (isset($_REQUEST['cancel'])) {
    PluginSmartAssignLogger::addWarning(__METHOD__ . ' - CANCEL: POST: ', $_POST);
    Session::AddMessageAfterRedirect('Configuração resetada');
    Html::back();
}

/**
 * then render current configuration
 */
$pluginSmartAssignConfigClass->renderTitle();
$pluginSmartAssignConfigClass->showFormSmartAssign();