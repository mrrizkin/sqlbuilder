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
              "column" => null,
              "operator" => null,
              "value" => null,
              "type" => $type,
              "where" => $where->build()
            ];
            return;
        }

        $this->wheres[] = [
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
     * @param string $column The column name.
     * @param string $operator The comparison operator. Default is "=".
     * @param mixed $value The value to compare against. Default is null.
     * @return WhereBuilder
     */
    public function rawWhere($column, $operator = "=", $value = null): WhereBuilder
    {
        $this->w("UNSAFE", "AND", $column, $operator, $value);
        return $this;
    }

    /**
     * Adds a raw OR WHERE clause to the query.
     *
     * ATTENTION: This method is UNSAFE and should be used with caution.
     *
     * @param string $column The column name.
     * @param string $operator The comparison operator. Default is "=".
     * @param mixed $value The value to compare against. Default is null.
     * @return $this The current instance of the SQLBuilder.
     */
    public function orRawWhere($column, $operator = "=", $value = null): WhereBuilder
    {
        $this->w("UNSAFE", "OR", $column, $operator, $value);
        return $this;
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

            if ($where["where"] !== null) {
                $sql .= "(" . $where["where"][0] . ") ";
                $this->params = array_merge($this->params, $where["where"][1]);
            } else {
                if (is_array($where["value"])) {
                    $sql .= $where["column"] . " " . $where["operator"];
                    $sql .= " (" . implode(", ", array_fill(0, count($where["value"]), "?")) . ") ";
                    $this->params = array_merge($this->params, $where["value"]);
                } else {
                    $sql .= $where["column"] . " " . $where["operator"] . " ? ";
                    $this->params[] = $where["value"];
                }
            }
        }
        return [trim($sql), $this->params];
    }
}
