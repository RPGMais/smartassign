<?php

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class PluginSmartAssignLogger {

    protected static $logger = null;

    protected static function getLogger() {
        if (self::$logger === null) {
            self::$logger = new Logger('smartassign');

            // Diretório e nome do arquivo de log baseado na data atual
            $logDir = PLUGIN_SMARTASSIGN_DIR . '/logs/';
            $logFile = $logDir . 'smartassign_' . date('Y-m-d') . '.log';

            // Verifica se a pasta de logs existe, caso contrário cria
            if (!file_exists($logDir)) {
                if (!mkdir($logDir, 0755, true)) {
                    error_log("Erro: Não foi possível criar o diretório de logs: $logDir");
                    return null;
                }
            }

            // Adiciona o manipulador de arquivos ao logger
            try {
                self::$logger->pushHandler(new StreamHandler($logFile, Logger::DEBUG));
            } catch (\Exception $e) {
                error_log("Erro ao configurar o logger: " . $e->getMessage());
                return null;
            }

            // Limpa logs antigos
            self::cleanOldLogs($logDir, 7);
        }

        return self::$logger;
    }

    protected static function add($type, $message, $details = []) {
        $logger = self::getLogger();

        if ($logger === null) {
            error_log("[$type] $message - " . json_encode($details));
            return;
        }

        switch ($type) {
            case 100:
                $logger->debug($message, $details);
                break;
            case 200:
                $logger->info($message, $details);
                break;
            case 250:
                $logger->notice($message, $details);
                break;
            case 300:
                $logger->warning($message, $details);
                break;
            case 400:
                $logger->error($message, $details);
                break;
            case 500:
                $logger->critical($message, $details);
                break;
            default:
                $logger->info($message, $details);
                break;
        }
    }

    // Método para limpar logs com mais de X dias
    protected static function cleanOldLogs($logDir, $days) {
        if (!is_dir($logDir)) {
            return;
        }

        $files = scandir($logDir);
        $timeLimit = time() - ($days * 86400); // Calcula o tempo limite em segundos

        foreach ($files as $file) {
            $filePath = $logDir . $file;

            // Ignorar diretórios e garantir que seja um arquivo
            if (is_file($filePath) && strpos($file, 'smartassign_') === 0) {
                $fileTime = filemtime($filePath); // Obtém o tempo de modificação do arquivo

                if ($fileTime < $timeLimit) {
                    unlink($filePath); // Remove arquivos antigos
                }
            }
        }
    }

    public static function addDebug($message, $details = []) {
        self::add(100, $message, $details);
    }

    public static function addInfo($message, $details = []) {
        self::add(200, $message, $details);
    }

    public static function addNotice($message, $details = []) {
        self::add(250, $message, $details);
    }

    public static function addWarning($message, $details = []) {
        self::add(300, $message, $details);
    }

    public static function addError($message, $details = []) {
        self::add(400, $message, $details);
    }

    public static function addCritical($message, $details = []) {
        self::add(500, $message, $details);
    }
}
