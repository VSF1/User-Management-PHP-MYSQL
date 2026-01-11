<?php
class Logger
{
    private $logFile;
    private $maxSize;

    public function __construct($filename = 'error.log', $maxSize = 5242880) // Default 5MB
    {
        $logDir = __DIR__ . '/../logs';
        if (!file_exists($logDir)) {
            // Attempt to create the directory
            mkdir($logDir, 0777, true);
        }
        $this->logFile = $logDir . '/' . $filename;
        $this->maxSize = $maxSize;
    }

    public function log($message, $level = 'INFO')
    {
        $this->rotate();

        $date = date('Y-m-d H:i:s');
        // Format: [2023-01-01 12:00:00] [INFO] Message
        $logEntry = sprintf("[%s] [%s] %s%s", $date, $level, $message, PHP_EOL);
        
        // Append to file
        file_put_contents($this->logFile, $logEntry, FILE_APPEND);
    }

    public function error($message)
    {
        $this->log($message, 'ERROR');
    }

    public function getLogs($level = null)
    {
        if (file_exists($this->logFile)) {
            $logs = file($this->logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            if ($level) {
                $logs = array_filter($logs, function($line) use ($level) {
                    return strpos($line, "[$level]") !== false;
                });
            }
            return $logs;
        }
        return [];
    }

    public function clearLogs()
    {
        file_put_contents($this->logFile, '');
    }

    public function getLogFilePath()
    {
        return $this->logFile;
    }

    private function rotate()
    {
        if (file_exists($this->logFile) && filesize($this->logFile) >= $this->maxSize) {
            $timestamp = date('Y-m-d_H-i-s');
            $backupFile = $this->logFile . '.' . $timestamp;
            rename($this->logFile, $backupFile);
        }
    }
}
?>