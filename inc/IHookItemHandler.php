<?php

if (!defined('GLPI_ROOT')) {
    define('GLPI_ROOT', '../../..');
}
require_once GLPI_ROOT . '/inc/includes.php';

interface IPluginSmartAssignHookItemHandler {

    public function itemAdded(CommonDBTM $item);

    public function itemDeleted(CommonDBTM $item);

    public function itemPurged(CommonDBTM $item);
}
