<?php
/**
 * Database Connection - MySQLi Singleton
 */
namespace App\Core;

class Database
{
    private static ?Database $instance = null;
    private \mysqli $connection;

    private function __construct()
    {
        $config = require __DIR__ . '/../../config/database.php';

        $host = $config['host'];
        $username = $config['username'];
        $password = $config['password'];
        $dbname = $config['dbname'];
        $port = (int) ($config['port'] ?? 3306);
        $socket = $config['socket'] ?? null;

        if ($socket) {
            $this->connection = new \mysqli($host, $username, $password, $dbname, $port, $socket);
        } else {
            $this->connection = new \mysqli($host, $username, $password, $dbname, $port);
        }

        if ($this->connection->connect_error) {
            throw new \RuntimeException('Database connection failed: ' . $this->connection->connect_error);
        }

        $this->connection->set_charset($config['charset']);
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection(): \mysqli
    {
        return $this->connection;
    }

    public function beginTransaction(): void
    {
        $this->connection->begin_transaction();
    }

    public function commit(): void
    {
        $this->connection->commit();
    }

    public function rollback(): void
    {
        $this->connection->rollback();
    }

    /**
     * Execute a query with prepared statement
     */
    public function query(string $sql, array $params = []): \mysqli_stmt|false
    {
        $stmt = $this->connection->prepare($sql);
        if (!$stmt) {
            throw new \RuntimeException('Query preparation failed: ' . $this->connection->error);
        }

        if (!empty($params)) {
            $types = '';
            $bindParams = [];
            foreach ($params as $key => $param) {
                if (is_int($param)) {
                    $types .= 'i';
                } elseif (is_float($param)) {
                    $types .= 'd';
                } elseif (is_null($param)) {
                    $types .= 's';
                } else {
                    $types .= 's';
                }
                $bindParams[] = $param;
            }

            if (!empty($bindParams)) {
                $stmt->bind_param($types, ...$bindParams);
            }
        }

        $stmt->execute();
        return $stmt;
    }

    /**
     * Fetch all rows as associative array
     */
    public function fetchAll(string $sql, array $params = []): array
    {
        $stmt = $this->query($sql, $params);
        $result = $stmt->get_result();
        if (!$result) {
            return [];
        }
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Fetch single row as associative array
     */
    public function fetchOne(string $sql, array $params = []): ?array
    {
        $stmt = $this->query($sql, $params);
        $result = $stmt->get_result();
        if (!$result) {
            return null;
        }
        return $result->fetch_assoc() ?: null;
    }

    /**
     * Insert a row and return the insert ID
     */
    public function insert(string $table, array $data): int
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));

        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        $this->query($sql, array_values($data));

        return $this->connection->insert_id;
    }

    /**
     * Update rows and return affected rows count
     */
    public function update(string $table, array $data, string $where, array $whereParams = []): int
    {
        $setClauses = [];
        foreach (array_keys($data) as $column) {
            $setClauses[] = "{$column} = ?";
        }
        $setString = implode(', ', $setClauses);

        $sql = "UPDATE {$table} SET {$setString} WHERE {$where}";
        $params = array_merge(array_values($data), $whereParams);

        $stmt = $this->query($sql, $params);
        return $stmt->affected_rows;
    }

    /**
     * Delete rows and return affected rows count
     */
    public function delete(string $table, string $where, array $params = []): int
    {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        $stmt = $this->query($sql, $params);
        return $stmt->affected_rows;
    }

    /**
     * Get the last insert ID
     */
    public function lastInsertId(): int
    {
        return $this->connection->insert_id;
    }

    /**
     * Escape a string for safe use in queries
     */
    public function escape(string $value): string
    {
        return $this->connection->real_escape_string($value);
    }
}
