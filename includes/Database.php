<?php
class Database
{
    private $dbh;

    public function __construct()
    {
        // Helper to retrieve configuration from Environment or Constants
        $getConfig = function($key, $default = null) {
            $val = getenv($key);
            if ($val !== false) return $val;
            if (isset($_ENV[$key])) return $_ENV[$key];
            if (defined($key)) return constant($key);
            return $default;
        };

        $type = $getConfig('DB_TYPE', 'mysql');
        $host = $getConfig('DB_HOST');
        $name = $getConfig('DB_NAME');
        $user = $getConfig('DB_USER');
        $pass = $getConfig('DB_PASS');

        $options = array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION);
        $initialize = false;

        try {
            switch ($type) {
                case 'pgsql':
                    $dsn = "pgsql:host=".$host.";dbname=".$name;
                    break;
                case 'sqlite':
                    if (!file_exists($name)) {
                        $initialize = true;
                    }
                    $dsn = "sqlite:".$name; // For SQLite, DB_NAME should be the file path
                    break;
                case 'mysql':
                case 'mariadb':
                default:
                    $dsn = "mysql:host=".$host.";dbname=".$name;
                    if (defined('PDO::MYSQL_ATTR_INIT_COMMAND')) {
                        $options[PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES 'utf8'";
                    }
                    break;
            }
            $this->dbh = new PDO($dsn, $user, $pass, $options);

            if ($initialize && $type === 'sqlite') {
                $schemaPath = __DIR__ . '/../database/sqlite_schema.sql';
                if (file_exists($schemaPath)) {
                    $sql = file_get_contents($schemaPath);
                    $this->dbh->exec($sql);
                }
            }
        } catch (PDOException $e) {
            require_once __DIR__ . '/Logger.php';
            $logger = new Logger();
            $logger->error("Database Connection Error: " . $e->getMessage());
            exit("Error: " . $e->getMessage());
        }
    }

    public function getConnection()
    {
        return $this->dbh;
    }
}