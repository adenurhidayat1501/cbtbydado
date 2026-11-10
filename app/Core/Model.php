<?php

namespace App\Core;

use PDO;

abstract class Model
{
    protected string $table;
    protected string $primaryKey = 'id';
    protected PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getPdo();
    }

    public function find($id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM `{$this->table}` WHERE `{$this->primaryKey}` = ? LIMIT 1");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function all(string $orderBy = ''): array
    {
        $sql = "SELECT * FROM `{$this->table}`";
        if (!empty($orderBy)) {
            $sql .= " ORDER BY {$orderBy}";
        }
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function where(array $conditions, string $orderBy = '', ?int $limit = null, int $offset = 0): array
    {
        $whereClauses = [];
        $values = [];

        foreach ($conditions as $col => $val) {
            if ($val === null) {
                $whereClauses[] = "`{$col}` IS NULL";
            } else {
                $whereClauses[] = "`{$col}` = ?";
                $values[] = $val;
            }
        }

        $sql = "SELECT * FROM `{$this->table}`";
        if (!empty($whereClauses)) {
            $sql .= " WHERE " . implode(" AND ", $whereClauses);
        }
        if (!empty($orderBy)) {
            $sql .= " ORDER BY {$orderBy}";
        }
        if ($limit !== null) {
            $sql .= " LIMIT {$limit} OFFSET {$offset}";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($values);
        return $stmt->fetchAll();
    }

    public function first(array $conditions, string $orderBy = ''): ?array
    {
        $results = $this->where($conditions, $orderBy, 1);
        return $results[0] ?? null;
    }

    public function count(array $conditions = []): int
    {
        $whereClauses = [];
        $values = [];

        foreach ($conditions as $col => $val) {
            if ($val === null) {
                $whereClauses[] = "`{$col}` IS NULL";
            } else {
                $whereClauses[] = "`{$col}` = ?";
                $values[] = $val;
            }
        }

        $sql = "SELECT COUNT(*) as total FROM `{$this->table}`";
        if (!empty($whereClauses)) {
            $sql .= " WHERE " . implode(" AND ", $whereClauses);
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($values);
        $row = $stmt->fetch();
        return (int)($row['total'] ?? 0);
    }

    public function insert(array $data): string|int
    {
        $cols = array_keys($data);
        $placeholders = array_fill(0, count($cols), '?');

        $sql = sprintf(
            "INSERT INTO `%s` (`%s`) VALUES (%s)",
            $this->table,
            implode('`, `', $cols),
            implode(', ', $placeholders)
        );

        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_values($data));
        return $this->db->lastInsertId();
    }

    public function update($id, array $data): bool
    {
        $setClauses = [];
        $values = [];

        foreach ($data as $col => $val) {
            $setClauses[] = "`{$col}` = ?";
            $values[] = $val;
        }

        $values[] = $id;

        $sql = sprintf(
            "UPDATE `%s` SET %s WHERE `%s` = ?",
            $this->table,
            implode(', ', $setClauses),
            $this->primaryKey
        );

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($values);
    }

    public function delete($id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM `{$this->table}` WHERE `{$this->primaryKey}` = ?");
        return $stmt->execute([$id]);
    }

    public function query(string $sql, array $params = []): array
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function execute(string $sql, array $params = []): int
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }
}

