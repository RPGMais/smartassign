<?php

if (!defined('GLPI_ROOT')) {
    define('GLPI_ROOT', '../../..');
}
require_once GLPI_ROOT . '/inc/includes.php';

if (!defined('PLUGIN_SMARTASSIGN_DIR')) {
    define('PLUGIN_SMARTASSIGN_DIR', __DIR__);
}
require_once PLUGIN_SMARTASSIGN_DIR . '/inc/TicketHookHandler.class.php';
require_once PLUGIN_SMARTASSIGN_DIR . '/inc/ITILCategoryHookHandler.class.php';
require_once PLUGIN_SMARTASSIGN_DIR . '/inc/RRAssignmentsEntity.class.php';

/**
 * Hook Item Handlers by Item Type
 */
function plugin_smartassign_getHookHandlers() {
    $HOOK_HANDLERS = [
        'Ticket' => new PluginSmartAssignTicketHookHandler(),
        'ITILCategory' => new PluginSmartAssignITILCategoryHookHandler()
    ];
    return $HOOK_HANDLERS;
}

/**
 * Install hook
 *
 * @return boolean
 */
function plugin_smartassign_install() {
    global $DB;

    PluginSmartAssignLogger::addWarning(__FUNCTION__ . ' - entered...');
    $rrAssignmentsEntity = new PluginSmartAssignRRAssignmentsEntity();

    /**
     * create setting table
     */
    $rrAssignmentsEntity->init();
    return true;
}

/**
 * Uninstall hook
 *
 * @return boolean
 */
function plugin_smartassign_uninstall() {
    global $DB;
    /**
     * @todo removing tables, generated files, ...
     */
    PluginSmartAssignLogger::addWarning(__FUNCTION__ . ' - entered...');
    $rrAssignmentsEntity = new PluginSmartAssignRRAssignmentsEntity();
    /**
     * drop settings
     */
    $rrAssignmentsEntity->cleanUp();
    return true;
}

/**
 * hook handlers
 */

/**
 * pre item add
 */
function plugin_smartassign_hook_pre_item_add_handler(CommonDBTM $item) {
    PluginSmartAssignLogger::addWarning(__FUNCTION__ . " - entered with item: " . print_r($item, true));
    return $item;
}

/**
 * item added
 */
function plugin_smartassign_hook_item_add_handler(CommonDBTM $item) {
    PluginSmartAssignLogger::addWarning(__FUNCTION__ . " - entered with item: " . print_r($item, true));
    $HOOK_HANDLERS = plugin_smartassign_getHookHandlers();
    if (array_key_exists($item->getType(), $HOOK_HANDLERS)) {
        $handler = $HOOK_HANDLERS[$item->getType()];
        $handler->itemAdded($item);
    }
    return $item;
}

/**
 * item updated
 */
function plugin_smartassign_hook_item_update_handler(CommonDBTM $item) {
    PluginSmartAssignLogger::addWarning(__FUNCTION__ . " - entered with item: " . print_r($item, true));
    PluginSmartAssignLogger::addWarning(__FUNCTION__ . " - Hook Hanlder: ITEM UPDATE: " . $item->getType());
    Session::addMessageAfterRedirect(sprintf(__('%1$s: %2$s'), __('Hook Hanlder: ITEM UPDATE'), $item->getType()));
    return $item;
}

/**
 * pre item delete
 */
function plugin_smartassign_hook_pre_item_delete_handler(CommonDBTM $item) {
    PluginSmartAssignLogger::addWarning(__FUNCTION__ . " - entered with item: " . print_r($item, true));
    return $item;
}

/**
 * item deleted
 */
function plugin_smartassign_hook_item_delete_handler(CommonDBTM $item) {
    PluginSmartAssignLogger::addWarning(__FUNCTION__ . " - entered with item: " . print_r($item, true));
    $HOOK_HANDLERS = plugin_smartassign_getHookHandlers();
    if (array_key_exists($item->getType(), $HOOK_HANDLERS)) {
        $handler = $HOOK_HANDLERS[$item->getType()];
        $handler->itemDeleted($item);
    }
    return $item;
}

/**
 * item purged
 */
function plugin_smartassign_hook_item_purge_handler(CommonDBTM $item) {
    PluginSmartAssignLogger::addWarning(__FUNCTION__ . " - entered with item: " . print_r($item, true));
    $HOOK_HANDLERS = plugin_smartassign_getHookHandlers();
    if (array_key_exists($item->getType(), $HOOK_HANDLERS)) {
        $handler = $HOOK_HANDLERS[$item->getType()];
        $handler->itemPurged($item);
    }
    return $item;
}