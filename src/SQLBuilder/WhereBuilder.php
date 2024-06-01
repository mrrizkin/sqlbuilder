<?php

namespace SQLBuilder;

/**
 * Class WhereBuilder
 *
 * This class extends the SafeSQL class and provides functionality for building WHERE clauses in SQL queries.
 */
class WhereBuilder extends SafeSQL
{
    private $wheres = [];
    private $params = [];

    /**
     * Performs a WHERE clause operation in the SQL query.
     *
     * @param string $mode The mode of the WHERE clause (e.g., "SAFE" or "UNSAFE").
     * @param string $type The type of the WHERE clause (e.g., "AND" or "OR").
     * @param string $column The column name in the WHERE clause.
     * @param string $operator The operator used in the WHERE clause.
     * @param mixed $value The value to compare in the WHERE clause.
     * @return void
     */
    private function w($mode, $type, $column, $operator, $value): void
    {
        if (is_callable($column)) {
            $where = new WhereBuilder();
            $column($where);
            $this->wheres[] = [
              "mode" => $mode,
              "column" => null,
              "operator" => null,
              "value" => null,
              "type" => $type,
              "where" => $where->build()
            ];
            return;
        }

        $this->wheres[] = [
          "mode" => $mode,
          "column" => $mode === "SAFE" ? $this->quoteColumnName($column) : $column,
          "operator" => $mode === "SAFE" ? $this->operators($operator) : $operator,
          "value" => $value,
          "type" => $type,
          "where" => null
        ];
    }

    /**
     * Adds a WHERE clause to the SQL query.
     *
     * @param string $column The column name.
     * @param string $operator The comparison operator (default: "=").
     * @param mixed $value The value to compare against (default: null).
     * @return WhereBuilder
     */
    public function where($column, $operator = "=", $value = null): WhereBuilder
    {
        $this->w("SAFE", "AND", $column, $operator, $value);
        return $this;
    }

    /**
     * Adds an OR condition to the WHERE clause of the SQL query.
     *
     * @param string $column The column name.
     * @param string $operator The comparison operator. Default is "=".
     * @param mixed $value The value to compare against. Default is null.
     * @return $this The current instance of the SQLBuilder class.
     */
    public function orWhere($column, $operator = "=", $value = null): WhereBuilder
    {
        $this->w("SAFE", "OR", $column, $operator, $value);
        return $this;
    }

    /**
     * Adds a raw WHERE clause to the query.
     *
     * ATTENTION: This method is UNSAFE and should be used with caution.
     *
     * @param string $statement
     * @param mixed $bind
     */
    public function whereRaw($statement, $bind = null): WhereBuilder
    {
        $this->w("UNSAFE", "AND", $statement, null, $bind);
        return $this;
    }

    /**
     * Adds a raw OR WHERE clause to the query.
     *
     * ATTENTION: This method is UNSAFE and should be used with caution.
     *
     * @param string $statement
     * @param mixed $bind
     */
    public function orWhereRaw($statement, $bind = null): WhereBuilder
    {
        $this->w("UNSAFE", "OR", $statement, null, $bind);
        return $this;
    }

    /**
     * @return array<int,mixed>
     * @param mixed $where
     */
    private function safeWhere($where): array
    {
        $sql = "";
        $params = [];

        if ($where["where"] !== null) {
            $sql .= "(" . $where["where"][0] . ") ";
            $params = $where["where"][1];
            return [$sql, $params];
        }

        if ($where["operator"] === "IS NULL" || $where["operator"] === "IS NOT NULL") {
            $sql .= $where["column"] . " " . $where["operator"];
            return [$sql, $params];
        }

        if (is_array($where["value"]) && ($where["operator"] === "IN" || $where["operator"] === "NOT IN")) {
            $sql .= $where["column"] . " " . $where["operator"];
            $sql .= " (" . implode(", ", array_fill(0, count($where["value"]), "?")) . ") ";
            $params = $where["value"];
            return [$sql, $params];
        }

        $sql .= $where["column"] . " " . $where["operator"] . " ? ";
        $params[] = $where["value"];
        return [$sql, $params];
    }
    /**
     * @param mixed $where
     * @return array<int,mixed>|array
     */
    private function unsafeWhere($where): array
    {
        $sql = "";
        $params = [];

        if ($where["where"] !== null) {
            $sql .= "(" . $where["where"][0] . ") ";
            $params = $where["where"][1];
            return [$sql, $params];
        }

        $sql .= $where["column"];
        $params = $where["value"];
        return [$sql, $params];
    }

    /**
     * Builds the SQL query based on the provided parameters.
     *
     * @return string The generated SQL query.
     */
    public function build(): array
    {
        $sql = "";
        foreach ($this->wheres as $indexWhere => $where) {
            if ($indexWhere > 0) {
                $sql .= $where["type"] . " ";
            }

            if ($where["mode"] === "SAFE") {
                $safe = $this->safeWhere($where);
                $sql .= $safe[0];
                $this->params = array_merge($this->params, $safe[1]);
                continue;
            }

            $unsafe = $this->unsafeWhere($where);
            $sql .= $unsafe[0];
            $this->params = array_merge($this->params, $unsafe[1]);
        }
        return [trim($sql), $this->params];
    }

    public function clone(): WhereBuilder
    {
        $clone = new WhereBuilder();
        $clone->wheres = $this->wheres;
        $clone->params = $this->params;
        return $clone;
    }
}
