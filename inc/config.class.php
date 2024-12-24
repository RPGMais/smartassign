<?php
// Antigo PluginSmartAssignConfig ou PluginRoundRobinConfig
class SmartAssignConfigClass {

    public static $PLUGIN_SMARTASSIGN_ENV = 'desenvolvimento';
    public static $PLUGIN_SMARTASSIGN_NAME = 'Ticket Balance';
    public static $PLUGIN_SMARTASSIGN_CODE = 'smartassign';
    public static $PLUGIN_SMARTASSIGN_VERSION = '1.2.0';
    public static $PLUGIN_SMARTASSIGN_AUTHOR = 'Richard Loureiro';
    public static $PLUGIN_SMARTASSIGN_LICENSE = 'GPLv3';
    public static $PLUGIN_SMARTASSIGN_HOME_PAGE = 'https://www.linkedin.com/in/richard-ti/';
    public static $PLUGIN_SMARTASSIGN_MIN_GLPI_VERSION = '9.5';
    public static $PLUGIN_SMARTASSIGN_GLPI_VERSION_ERROR = "Este plugin requer GLPI >= 9.5 e GLPI <= 11";
    public static $PLUGIN_SMARTASSIGN_MAX_GLPI_VERSION = '11';
    public static $PLUGIN_SMARTASSIGN_MIN_PHP_VERSION = '7.3';

    public static function init() {
        PluginSmartAssignLogger::addWarning(__METHOD__ . ' - definindo manipuladores de hooks');
        global $PLUGIN_HOOKS;

        $PLUGIN_HOOKS['csrf_compliant'][self::$PLUGIN_SMARTASSIGN_CODE] = true;

        // Declaração de hooks
        $PLUGIN_HOOKS['pre_item_add'][self::$PLUGIN_SMARTASSIGN_CODE] = [
            'Ticket' => 'plugin_smartassign_hook_pre_item_add_handler'
        ];
        $PLUGIN_HOOKS['item_add'][self::$PLUGIN_SMARTASSIGN_CODE] = [
            'Ticket' => 'plugin_smartassign_hook_item_add_handler',
            'ITILCategory' => 'plugin_smartassign_hook_item_add_handler'
        ];
        $PLUGIN_HOOKS['item_update'][self::$PLUGIN_SMARTASSIGN_CODE] = [
            'Ticket' => 'plugin_smartassign_hook_item_update_handler'
        ];
        $PLUGIN_HOOKS['pre_item_delete'][self::$PLUGIN_SMARTASSIGN_CODE] = [
            'Ticket' => 'plugin_smartassign_hook_pre_item_delete_handler'
        ];
        $PLUGIN_HOOKS['item_delete'][self::$PLUGIN_SMARTASSIGN_CODE] = [
            'Ticket' => 'plugin_smartassign_hook_item_delete_handler',
            'ITILCategory' => 'plugin_smartassign_hook_item_delete_handler'
        ];
        $PLUGIN_HOOKS['item_purge'][self::$PLUGIN_SMARTASSIGN_CODE] = [
            'Ticket' => 'plugin_smartassign_hook_item_purge_handler',
            'ITILCategory' => 'plugin_smartassign_hook_item_purge_handler'
        ];
    }

    public static function getVersion() {
        return [
            'name' => self::$PLUGIN_SMARTASSIGN_NAME,
            'version' => self::$PLUGIN_SMARTASSIGN_VERSION,
            'author' => self::$PLUGIN_SMARTASSIGN_AUTHOR,
            'license' => self::$PLUGIN_SMARTASSIGN_LICENSE,
            'homepage' => self::$PLUGIN_SMARTASSIGN_HOME_PAGE,
            'requirements' => [
                'glpi' => [
                    'min' => self::$PLUGIN_SMARTASSIGN_MIN_GLPI_VERSION,
                    'max' => self::$PLUGIN_SMARTASSIGN_MAX_GLPI_VERSION
                ],
                'php' => [
                    'min' => self::$PLUGIN_SMARTASSIGN_MIN_PHP_VERSION
                ]
            ]
        ];
    }

    public static function loadSources() {
        global $PLUGIN_HOOKS;

        PluginSmartAssignLogger::addWarning(__METHOD__ . ' - carregando fontes...');
        $PLUGIN_HOOKS['config_page'][self::$PLUGIN_SMARTASSIGN_CODE] = 'front/config.form.php';
    }

    public static function hookAddSource($uriArray, $hook, $sourceFile) {
        global $PLUGIN_HOOKS;

        if (!is_array($uriArray)) {
            throw new InvalidArgumentException("Estrutura de URI inválida, esperado array.");
        }
        foreach ($uriArray as $uri) {
            if (strpos(PluginSmartAssignRequest::getServerParam('REQUEST_URI'), $uri) !== false) {
                $PLUGIN_HOOKS[$hook][self::$PLUGIN_SMARTASSIGN_CODE] = $sourceFile;
                PluginSmartAssignLogger::addWarning(__METHOD__ . " - recurso $sourceFile carregado!");
                break;
            }
        }
    }

    public static function getRrAssignmentTable() {
        $pluginCode = self::$PLUGIN_SMARTASSIGN_CODE;
        return "glpi_plugin_" . $pluginCode . "_assignments";
    }

    public static function getRrOptionsTable() {
        $pluginCode = self::$PLUGIN_SMARTASSIGN_CODE;
        return "glpi_plugin_" . $pluginCode . "_options";
    }
	
	// Define o nome do menu.
    static function getMenuName() {
        return __('Ticket Balance');
    }

    // Define o conteúdo do menu.
    static function getMenuContent() {
        global $CFG_GLPI;
        $menu = [];
        $menu['title'] = __('Ticket Balance');
        $menu['page']  = $CFG_GLPI['root_doc'] . "/plugins/smartassign/front/config.form.php";
		$menu['icon']  = 'fas fa-user-check';
        return $menu;
    }
}