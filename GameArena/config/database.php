<?php
/**
 * GameArena - Database Configuration
 * Oracle Database Connection using OCI8
 * KUET Gaming Tournament Management System
 */

// Prevent direct access
if (!defined('GAMEARENA_DB')) {
    // Allow access for direct includes
}

// =====================================================
// Database Configuration Constants
// =====================================================
define('DB_HOST', 'localhost');
define('DB_PORT', '1521');
define('DB_SERVICE', 'XEPDB1');
define('DB_USERNAME', 'gamearena');
define('DB_PASSWORD', 'gamearena123');
define('DB_CHARSET', 'AL32UTF8');

// =====================================================
// Database Connection Class
// =====================================================
class Database {
    private static $instance = null;
    private $connection;
    private $isConnected = false;

    // Prevent cloning
    private function __clone() {}

    // Prevent unserialization
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }

    private function __construct() {
        $this->connect();
    }

    /**
     * Get singleton instance
     */
    public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Establish Oracle connection using OCI8
     */
    private function connect(): void {
        try {
            // Build connection string
            $connStr = DB_HOST . ':' . DB_PORT . '/' . DB_SERVICE;

            // Create Oracle connection
            $this->connection = @oci_connect(
                DB_USERNAME,
                DB_PASSWORD,
                $connStr,
                DB_CHARSET
            );

            if (!$this->connection) {
                $error = oci_error();
                throw new Exception('Oracle Connection Failed: ' . $error['message']);
            }

            $this->isConnected = true;

        } catch (Exception $e) {
            error_log('Database Connection Error: ' . $e->getMessage());
            $this->isConnected = false;
            throw $e;
        }
    }

    /**
     * Get database connection
     */
    public function getConnection() {
        if (!$this->isConnected || !$this->connection) {
            $this->connect();
        }
        return $this->connection;
    }

    /**
     * Check if connected
     */
    public function isConnected(): bool {
        return $this->isConnected && $this->connection !== false;
    }

    /**
     * Close connection
     */
    public function close(): void {
        if ($this->connection) {
            oci_close($this->connection);
            $this->connection = null;
            $this->isConnected = false;
        }
    }

    /**
     * Execute a query with parameter binding
     */
    public function execute(string $sql, array $params = [], string $execMode = OCI_NO_AUTO_COMMIT) {
        $stmt = @oci_parse($this->getConnection(), $sql);
        if (!$stmt) {
            $error = oci_error($this->getConnection());
            throw new Exception('Parse Error: ' . $error['message']);
        }

        // Bind parameters
        foreach ($params as $key => &$value) {
            if (is_int($key)) {
                // Positional binding (1-based)
                oci_bind_by_pos($stmt, $key + 1, $value);
            } else {
                // Named binding
                oci_bind_by_name($stmt, $key, $value);
            }
        }
        unset($value);

        // Execute
        $result = @oci_execute($stmt, $execMode);
        if (!$result) {
            $error = oci_error($stmt);
            throw new Exception('Execute Error: ' . $error['message']);
        }

        return $stmt;
    }

    /**
     * Fetch single row
     */
    public function fetchOne(string $sql, array $params = []): ?array {
        $stmt = $this->execute($sql, $params);
        $row = oci_fetch_array($stmt, OCI_ASSOC | OCI_RETURN_NULLS | OCI_RETURN_LOBS);
        oci_free_statement($stmt);
        return $row ?: null;
    }

    /**
     * Fetch all rows
     */
    public function fetchAll(string $sql, array $params = []): array {
        $stmt = $this->execute($sql, $params);
        $rows = [];

        while (($row = oci_fetch_array($stmt, OCI_ASSOC | OCI_RETURN_NULLS | OCI_RETURN_LOBS)) !== false) {
            $rows[] = $row;
        }

        oci_free_statement($stmt);
        return $rows;
    }

    /**
     * Fetch single value
     */
    public function fetchColumn(string $sql, array $params = []) {
        $row = $this->fetchOne($sql, $params);
        if ($row) {
            return reset($row);
        }
        return null;
    }

    /**
     * Insert and return affected rows
     */
    public function insert(string $sql, array $params = []): bool {
        $stmt = $this->execute($sql, $params, OCI_COMMIT_ON_SUCCESS);
        oci_free_statement($stmt);
        return true;
    }

    /**
     * Update and return affected rows
     */
    public function update(string $sql, array $params = []): bool {
        $stmt = $this->execute($sql, $params, OCI_COMMIT_ON_SUCCESS);
        oci_free_statement($stmt);
        return true;
    }

    /**
     * Delete and return affected rows
     */
    public function delete(string $sql, array $params = []): bool {
        $stmt = $this->execute($sql, $params, OCI_COMMIT_ON_SUCCESS);
        oci_free_statement($stmt);
        return true;
    }

    /**
     * Begin transaction
     */
    public function beginTransaction(): void {
        // OCI8 uses OCI_NO_AUTO_COMMIT by default
    }

    /**
     * Commit transaction
     */
    public function commit(): void {
        @oci_commit($this->getConnection());
    }

    /**
     * Rollback transaction
     */
    public function rollback(): void {
        @oci_rollback($this->getConnection());
    }

    /**
     * Get next sequence value
     */
    public function nextId(string $sequence): int {
        $sql = "SELECT {$sequence}.NEXTVAL AS id FROM dual";
        $result = $this->fetchOne($sql);
        return (int)$result['ID'];
    }

    /**
     * Get current sequence value
     */
    public function currentId(string $sequence): int {
        $sql = "SELECT {$sequence}.CURRVAL AS id FROM dual";
        $result = $this->fetchOne($sql);
        return (int)$result['ID'];
    }

    /**
     * Prepare and execute PL/SQL block
     */
    public function executePlsql(string $block, array $params = []): array {
        $stmt = @oci_parse($this->getConnection(), $block);
        if (!$stmt) {
            $error = oci_error($this->getConnection());
            throw new Exception('PL/SQL Parse Error: ' . $error['message']);
        }

        // Bind parameters
        foreach ($params as $key => &$value) {
            if (is_string($value) && strlen($value) > 4000) {
                oci_bind_by_name($stmt, $key, $value, -1, SQLT_CLOB);
            } else {
                oci_bind_by_name($stmt, $key, $value);
            }
        }
        unset($value);

        $result = @oci_execute($stmt);
        if (!$result) {
            $error = oci_error($stmt);
            throw new Exception('PL/SQL Execute Error: ' . $error['message']);
        }

        // Fetch OUT parameters
        $output = [];
        foreach ($params as $key => $value) {
            if (strpos($key, ':') === 0) {
                $output[substr($key, 1)] = $value;
            }
        }

        oci_free_statement($stmt);
        return $output;
    }
}

/**
 * Get database instance
 */
function getDB(): Database {
    return Database::getInstance();
}

/**
 * Quick query helpers
 */
function dbFetchOne(string $sql, array $params = []): ?array {
    return getDB()->fetchOne($sql, $params);
}

function dbFetchAll(string $sql, array $params = []): array {
    return getDB()->fetchAll($sql, $params);
}

function dbFetchColumn(string $sql, array $params = []) {
    return getDB()->fetchColumn($sql, $params);
}

function dbInsert(string $sql, array $params = []): bool {
    return getDB()->insert($sql, $params);
}

function dbUpdate(string $sql, array $params = []): bool {
    return getDB()->update($sql, $params);
}

function dbDelete(string $sql, array $params = []): bool {
    return getDB()->delete($sql, $params);
}

/**
 * Security Helper Functions
 */
function sanitize($input): string {
    return htmlspecialchars(trim((string)($input ?? '')), ENT_QUOTES, 'UTF-8');
}

function validateEmail(string $email): bool {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function generateCSRFToken(): string {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken(string $token): bool {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function jsonResponse(array $data, int $statusCode = 200): void {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}

function redirect(string $url): void {
    header("Location: $url");
    exit;
}
