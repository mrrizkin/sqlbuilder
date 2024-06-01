<?php

namespace SQLBuilder;

/**
 * Class JoinBuilder
 *
 * Represents a join builder for constructing SQL queries.
 * Extends the SafeSQL class for safe query building.
 */
class JoinBuilder extends SafeSQL
{
    private $joins = [];
    private $params = [];

    /**
     * Joins a table with another table using a specified join type and condition.
     *
     * @param string $type The type of join (e.g., "INNER", "LEFT", "RIGHT").
     * @param string $table The name of the table to join.
     * @param string $left The left side of the join condition.
     * @param string $operator The operator to use in the join condition (e.g., "=", "<>", "<", ">").
     * @param string $right The right side of the join condition.
     * @return void
     */
    private function j($type, $table, $left, $operator, $right): void
    {
        if (is_callable($left)) {
            $job = new JoinOnBuilder();
            $left($job);
            $this->joins[] = [
              "type" => $type,
              "table" => $this->quoteTableName($table),
              "left" => null,
              "operator" => null,
              "right" => null,
              "on" => $job->build()
            ];

            return;
        }

        $this->joins[] = [
          "type" => $type,
          "table" => $this->quoteTableName($table),
          "left" => $this->quoteColumnName($left),
          "operator" => $this->operators($operator),
          "right" => $this->quoteColumnName($right),
          "on" => null
        ];
    }

    /**
     * Joins a table with the current query.
     *
     * @param string $table The name of the table to join.
     * @param string $left The left column to join on.
     * @param string $operator The operator to use for the join condition. Default is "=".
     * @param string|null $right The right column to join on. Default is null.
     * @return $this The current instance of the SQLBuilder.
     */
    public function join($table, $left, $operator = "=", $right = null): JoinBuilder
    {
        $this->j("JOIN", $table, $left, $operator, $right);
        return $this;
    }

    /**
     * Performs a left join operation on the specified table.
     *
     * @param string $table The name of the table to join.
     * @param string $left The left column to join on.
     * @param string $operator The operator to use for the join condition. Defaults to "=".
     * @param string|null $right The right column to join on. Defaults to null.
     * @return $this The current instance of the SQLBuilder class.
     */
    public function leftJoin($table, $left, $operator = "=", $right = null): JoinBuilder
    {
        $this->j("LEFT JOIN", $table, $left, $operator, $right);
        return $this;
    }

    /**
     * Performs a right join operation on the specified table.
     *
     * @param string $table The name of the table to join.
     * @param string $left The left column to join on.
     * @param string $operator The operator to use for the join condition. Defaults to "=".
     * @param string|null $right The right column to join on. Defaults to null.
     * @return $this The current instance of the SQLBuilder class.
     */
    public function rightJoin($table, $left, $operator = "=", $right = null): JoinBuilder
    {
        $this->j("RIGHT JOIN", $table, $left, $operator, $right);
        return $this;
    }

    /**
     * Performs an inner join operation on the specified table.
     *
     * @param string $table The name of the table to join.
     * @param string $left The left column to join on.
     * @param string $operator The operator to use for the join condition. Defaults to "=".
     * @param string|null $right The right column to join on. Defaults to null.
     * @return $this The current instance of the SQLBuilder class.
     */
    public function innerJoin($table, $left, $operator = "=", $right = null): JoinBuilder
    {
        $this->j("INNER JOIN", $table, $left, $operator, $right);
        return $this;
    }

    /**
     * Builds the SQL query.
     *
     * This method is responsible for constructing the SQL query based on the provided parameters.
     * It returns the final SQL query as a string.
     *
     * @return string The constructed SQL query.
     */
    public function build(): array
    {
        $sql = "";
        foreach ($this->joins as $join) {
            $sql .= " " . $join["type"] . " " . $join["table"] . " ON ";

            if ($join["on"] !== null) {
                $sql .= trim($join["on"][0]);
                $this->params = array_merge($this->params, $join["on"][1]);
            } else {
                $sql .= $join["left"] . " " . $join["operator"] . " " . $join["right"];
            }
        }
        return [$sql, $this->params];
    }

    public function clone(): JoinBuilder
    {
        $clone = new JoinBuilder();
        $clone->joins = $this->joins;
        $clone->params = $this->params;
        return $clone;
    }
}
