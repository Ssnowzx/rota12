<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;
use PDOStatement;
use RuntimeException;

/**
 * PDO Singleton Database Wrapper
 *
 * Credentials are read from config/database.php constants:
 *   DB_HOST, DB_NAME, DB_USER, DB_PASS, DB_PORT
 */
final class DB
{
    private static ?PDO $instance = null;

    /** Prevent direct instantiation. */
    private function __construct() {}

    /** Prevent cloning. */
    private function __clone() {}

    /**
     * Return the shared PDO instance, creating it on first call.
     *
     * @throws RuntimeException If required constants are not defined.
     * @throws PDOException     If the connection fails.
     */
    public static function getInstance(): PDO
    {
        if (self::$instance !== null) {
            return self::$instance;
        }

        foreach (['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS', 'DB_PORT'] as $const) {
            if (!defined($const)) {
                throw new RuntimeException("Database constant '{$const}' is not defined. Check config/database.php.");
            }
        }

        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
            DB_HOST,
            (int) DB_PORT,
            DB_NAME
        );

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            // PDO::MYSQL_ATTR_FOUND_ROWS deprecated in PHP 8.5 — omit for compatibility
        ];

        self::$instance = new PDO($dsn, DB_USER, DB_PASS, $options);

        return self::$instance;
    }

    /**
     * Prepare and execute a statement, returning the PDOStatement.
     *
     * @param string  $sql    Parameterised SQL.
     * @param array   $params Bound values.
     * @return PDOStatement
     */
    public static function query(string $sql, array $params = []): PDOStatement
    {
        $stmt = self::getInstance()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Execute a SELECT and return all rows.
     *
     * @param string $sql
     * @param array  $params
     * @return array<int, array<string, mixed>>
     */
    public static function select(string $sql, array $params = []): array
    {
        return self::query($sql, $params)->fetchAll();
    }

    /**
     * Execute a SELECT and return a single row (or null if not found).
     *
     * @param string $sql
     * @param array  $params
     * @return array<string, mixed>|null
     */
    public static function selectOne(string $sql, array $params = []): ?array
    {
        $row = self::query($sql, $params)->fetch();
        return ($row !== false) ? $row : null;
    }

    /**
     * Execute an INSERT statement and return the last inserted ID.
     *
     * @param string $sql
     * @param array  $params
     * @return int
     */
    public static function insert(string $sql, array $params = []): int
    {
        self::query($sql, $params);
        return (int) self::getInstance()->lastInsertId();
    }

    /**
     * Execute an UPDATE / DELETE statement and return the number of affected rows.
     *
     * @param string $sql
     * @param array  $params
     * @return int
     */
    public static function execute(string $sql, array $params = []): int
    {
        return self::query($sql, $params)->rowCount();
    }

    /**
     * Begin a transaction.
     */
    public static function beginTransaction(): void
    {
        self::getInstance()->beginTransaction();
    }

    /**
     * Commit the current transaction.
     */
    public static function commit(): void
    {
        self::getInstance()->commit();
    }

    /**
     * Roll back the current transaction.
     */
    public static function rollback(): void
    {
        self::getInstance()->rollBack();
    }

    /**
     * Run a callable inside a transaction.
     * Automatically commits on success or rolls back on exception.
     *
     * @param callable $callback
     * @return mixed Return value of the callback.
     * @throws \Throwable Re-throws any exception after rollback.
     */
    public static function transaction(callable $callback)
    {
        self::beginTransaction();
        try {
            $result = $callback(self::getInstance());
            self::commit();
            return $result;
        } catch (\Throwable $e) {
            self::rollback();
            throw $e;
        }
    }
}
