<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

/**
 * Base Model
 *
 * Provides common CRUD operations backed by the DB singleton.
 * Child models declare a $table and optionally a $primaryKey.
 *
 * Example:
 *   class ListingModel extends ModelBase
 *   {
 *       protected static string $table      = 'listings';
 *       protected static string $primaryKey = 'id';
 *   }
 */
abstract class ModelBase
{
    /** Database table name. Must be overridden in each concrete model. */
    protected static string $table = '';

    /** Primary key column name. */
    protected static string $primaryKey = 'id';

    // -------------------------------------------------------------------------
    // Internal helpers
    // -------------------------------------------------------------------------

    /**
     * Assert that the concrete model has defined a table name.
     *
     * @throws RuntimeException
     */
    private static function assertTable(): void
    {
        if (static::$table === '') {
            throw new RuntimeException(
                'ModelBase: $table must be defined in ' . static::class
            );
        }
    }

    /**
     * Build a parameterised WHERE clause from an associative array.
     *
     * @param array<string, mixed> $where
     * @return array{clause: string, params: list<mixed>}
     */
    private static function buildWhere(array $where): array
    {
        if (empty($where)) {
            return ['clause' => '', 'params' => []];
        }

        $conditions = [];
        $params      = [];

        foreach ($where as $column => $value) {
            if ($value === null) {
                $conditions[] = "`{$column}` IS NULL";
            } else {
                $conditions[] = "`{$column}` = ?";
                $params[]      = $value;
            }
        }

        return [
            'clause' => 'WHERE ' . implode(' AND ', $conditions),
            'params' => $params,
        ];
    }

    // -------------------------------------------------------------------------
    // Public API
    // -------------------------------------------------------------------------

    /**
     * Find a single row by its primary key.
     *
     * @param int $id
     * @return array<string, mixed>|null
     */
    public static function find(int $id): ?array
    {
        self::assertTable();

        $sql = sprintf(
            'SELECT * FROM `%s` WHERE `%s` = ? LIMIT 1',
            static::$table,
            static::$primaryKey
        );

        return DB::selectOne($sql, [$id]);
    }

    /**
     * Find all rows matching optional WHERE conditions.
     *
     * @param array<string, mixed> $where   Associative column => value filter.
     * @param string               $orderBy Raw ORDER BY expression, e.g. 'created_at DESC'.
     * @param int                  $limit   0 = no limit.
     * @param int                  $offset
     * @return array<int, array<string, mixed>>
     */
    public static function findAll(
        array  $where   = [],
        string $orderBy = '',
        int    $limit   = 0,
        int    $offset  = 0
    ): array {
        self::assertTable();

        ['clause' => $whereClause, 'params' => $params] = self::buildWhere($where);

        $sql = sprintf('SELECT * FROM `%s` %s', static::$table, $whereClause);

        if ($orderBy !== '') {
            $sql .= ' ORDER BY ' . $orderBy;
        }

        if ($limit > 0) {
            $sql      .= ' LIMIT ?';
            $params[]  = $limit;

            if ($offset > 0) {
                $sql      .= ' OFFSET ?';
                $params[]  = $offset;
            }
        }

        return DB::select($sql, $params);
    }

    /**
     * Insert a new row and return the auto-increment ID.
     *
     * @param array<string, mixed> $data Column => value pairs.
     * @return int Last insert ID.
     */
    public static function insert(array $data): int
    {
        self::assertTable();

        if (empty($data)) {
            throw new RuntimeException('ModelBase::insert() called with empty $data array.');
        }

        $columns      = array_keys($data);
        $placeholders = array_fill(0, count($columns), '?');

        $sql = sprintf(
            'INSERT INTO `%s` (`%s`) VALUES (%s)',
            static::$table,
            implode('`, `', $columns),
            implode(', ', $placeholders)
        );

        return DB::insert($sql, array_values($data));
    }

    /**
     * Update a row by primary key.
     *
     * @param int                  $id   Primary key value.
     * @param array<string, mixed> $data Column => value pairs to update.
     * @return int Number of affected rows.
     */
    public static function update(int $id, array $data): int
    {
        self::assertTable();

        if (empty($data)) {
            throw new RuntimeException('ModelBase::update() called with empty $data array.');
        }

        $setParts = [];
        $params   = [];

        foreach ($data as $column => $value) {
            $setParts[] = "`{$column}` = ?";
            $params[]   = $value;
        }

        $params[] = $id; // For the WHERE clause.

        $sql = sprintf(
            'UPDATE `%s` SET %s WHERE `%s` = ?',
            static::$table,
            implode(', ', $setParts),
            static::$primaryKey
        );

        return DB::execute($sql, $params);
    }

    /**
     * Delete a row by primary key.
     *
     * @param int $id
     * @return int Number of affected rows.
     */
    public static function delete(int $id): int
    {
        self::assertTable();

        $sql = sprintf(
            'DELETE FROM `%s` WHERE `%s` = ?',
            static::$table,
            static::$primaryKey
        );

        return DB::execute($sql, [$id]);
    }

    /**
     * Count rows matching optional WHERE conditions.
     *
     * @param array<string, mixed> $where
     * @return int
     */
    public static function count(array $where = []): int
    {
        self::assertTable();

        ['clause' => $whereClause, 'params' => $params] = self::buildWhere($where);

        $sql = sprintf(
            'SELECT COUNT(*) AS `cnt` FROM `%s` %s',
            static::$table,
            $whereClause
        );

        $row = DB::selectOne($sql, $params);
        return (int) ($row['cnt'] ?? 0);
    }

    /**
     * Paginate rows.
     *
     * @param int                  $page    1-based page number.
     * @param int                  $perPage Rows per page.
     * @param array<string, mixed> $where   Filter conditions.
     * @param string               $orderBy Raw ORDER BY expression.
     * @return array{data: list<array<string,mixed>>, total: int, pages: int, current: int}
     */
    public static function paginate(
        int    $page    = 1,
        int    $perPage = 20,
        array  $where   = [],
        string $orderBy = ''
    ): array {
        self::assertTable();

        $page    = max(1, $page);
        $perPage = max(1, $perPage);
        $offset  = ($page - 1) * $perPage;

        $total = static::count($where);
        $pages = (int) ceil($total / $perPage);

        $data = static::findAll($where, $orderBy, $perPage, $offset);

        return [
            'data'    => $data,
            'total'   => $total,
            'pages'   => $pages,
            'current' => $page,
        ];
    }

    /**
     * Find rows using a raw SQL query with bound parameters.
     * Useful for complex JOINs not expressible via findAll().
     *
     * @param string $sql
     * @param array  $params
     * @return array<int, array<string, mixed>>
     */
    public static function raw(string $sql, array $params = []): array
    {
        return DB::select($sql, $params);
    }

    /**
     * Find a single row using a raw SQL query.
     *
     * @param string $sql
     * @param array  $params
     * @return array<string, mixed>|null
     */
    public static function rawOne(string $sql, array $params = []): ?array
    {
        return DB::selectOne($sql, $params);
    }
}
