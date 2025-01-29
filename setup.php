<?php

if (!defined('PLUGIN_SMARTASSIGN_DIR')) {
    define('PLUGIN_SMARTASSIGN_DIR', __DIR__);
}

require_once PLUGIN_SMARTASSIGN_DIR . '/inc/logger.class.php';
require_once PLUGIN_SMARTASSIGN_DIR . '/inc/request.class.php';
require_once PLUGIN_SMARTASSIGN_DIR . '/inc/config.class.php';

// Inicializa os hooks do plugin
function plugin_init_smartassign() {
    global $PLUGIN_HOOKS, $CFG_GLPI, $LANG;

    $PLUGIN_HOOKS['csrf_compliant']['smartassign'] = true;
    $PLUGIN_HOOKS['menu_toadd']['smartassign'] = ['plugins' => 'SmartAssignConfigClass'];

    // Caminho para os arquivos de tradução usando a constante
    $localeDir = PLUGIN_SMARTASSIGN_DIR . '/locales';
    $domain = 'smartassign';

    // Configuração de gettext para carregar traduções
    if (function_exists('bindtextdomain')) {
        bindtextdomain($domain, $localeDir); // Define o diretório de traduções
        textdomain($domain); // Define o domínio de tradução atual
    }

    // Inicializa configurações do plugin
    SmartAssignConfigClass::init();
    SmartAssignConfigClass::loadSources();
}

// Obtém o nome e a versão do plugin @return array
function plugin_version_smartassign() {
    return SmartAssignConfigClass::getVersion();
}

// Verifica os pré-requisitos antes da instalação @return boolean
function plugin_smartassign_check_prerequisites() {
    if (version_compare(GLPI_VERSION, SmartAssignConfigClass::$PLUGIN_SMARTASSIGN_MIN_GLPI_VERSION, '<') ||
        version_compare(GLPI_VERSION, SmartAssignConfigClass::$PLUGIN_SMARTASSIGN_MAX_GLPI_VERSION, '>')) {
        
        PluginSmartAssignLogger::addCritical(__FUNCTION__ . ' - pré-requisitos não atendidos: ' . SmartAssignConfigClass::$PLUGIN_SMARTASSIGN_GLPI_VERSION_ERROR);
        
        if (method_exists('Plugin', 'messageIncompatible')) {
            Plugin::messageIncompatible('core', SmartAssignConfigClass::$PLUGIN_SMARTASSIGN_GLPI_VERSION_ERROR);
        }
        return false;
    }

    PluginSmartAssignLogger::addDebug(__FUNCTION__ . ' - pré-requisitos atendidos');
    return true;
}

/**
 * Verifica o processo de configuração do plugin
 *
 * @param boolean $verbose Habilita verbosidade. Padrão é false
 * @return boolean
 */
function plugin_smartassign_check_config($verbose = false) {
    if (true) {
        return true; // Configuração válida
    }

    if ($verbose) {
        echo "Instalado, mas não configurado";
    }
    return false;
}
