<?php

namespace SQLBuilder;

/**
 * Class JoinOnBuilder
 *
 * This class extends the SafeSQL class and represents a builder for the JOIN ON clause in SQL queries.
 */
class JoinOnBuilder extends SafeSQL
{
    private $joinOn = [];
    private $params = [];

    /**
     * Joins two expressions using the specified operator.
     *
     * @param bool $bool The boolean value indicating whether to join the expressions.
     * @param string $type The type of join to perform (e.g., "INNER", "LEFT", "RIGHT").
     * @param string $left The left expression to join.
     * @param string $operator The operator to use for the join.
     * @param string $right The right expression to join.
     * @return void
     */
    private function jo($bool, $type, $left, $operator, $right): void
    {
        if (is_callable($left)) {
            $job = new JoinOnBuilder();
            $left($job);
            $this->joinOn[] = [
              "bool" => $bool,
              "type" => $type,
              "left" => null,
              "operator" => null,
              "right" => null,
              "on" => $job->build()
            ];

            return;
        }

        $this->joinOn[] = [
          "bool" => $bool,
          "type" => $type,
          "left" => $this->quoteColumnName($left),
          "operator" => $this->operators($operator),
          "right" => $type === "ON" ? $this->quoteColumnName($right) : $right,
          "on" => null
        ];
    }

    /**
     * Sets the ON clause for the SQL query.
     *
     * @param string $left The left side of the column.
     * @param string $operator The comparison operator. Default is "=".
     * @param mixed $right The right side of the column. Default is null.
     * @return JoinOnBuilder
     */
    public function on($left, $operator = "=", $right = null): JoinOnBuilder
    {
        $this->jo("AND", "ON", $left, $operator, $right);
        return $this;
    }

    /**
     * Adds an "OR" condition to the query's "ON" clause.
     *
     * @param string $left The left side of the condition.
     * @param string $operator The operator to use in the condition. Defaults to "=".
     * @param mixed $right The right side of the condition. Defaults to null.
     * @return $this The current instance of the SQLBuilder class.
     */
    public function orOn($left, $operator = "=", $right = null): JoinOnBuilder
    {
        $this->jo("OR", "ON", $left, $operator, $right);
        return $this;
    }

    /**
     * Adds a WHERE clause to the SQL query.
     *
     * @param string $column The column name.
     * @param string $operator The comparison operator. Default is "=".
     * @param mixed $value The value to compare against. Default is null.
     * @return JoinOnBuilder
     */
    public function where($column, $operator = "=", $value = null): JoinOnBuilder
    {
        $this->jo("AND", "WHERE", $column, $operator, $value);
        return $this;
    }

    /**
     * Adds an "OR" condition to the query's WHERE clause.
     *
     * @param string $column The column name.
     * @param string $operator The comparison operator. Default is "=".
     * @param mixed $value The value to compare against. Default is null.
     * @return $this The current instance of the SQLBuilder.
     */
    public function orWhere($column, $operator = "=", $value = null): JoinOnBuilder
    {
        $this->jo("OR", "WHERE", $column, $operator, $value);
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
        foreach ($this->joinOn as $indexJoin => $join) {
            if ($indexJoin > 0) {
                $sql .= " " . $join["bool"] . " ";
            }

            if ($join["on"] !== null) {
                $sql .= "(" . trim($join["on"][0]) . ") ";
                $this->params = array_merge($this->params, $join["on"][1]);
            } else {
                if ($join["type"] === "ON") {
                    $sql .=  $join["left"] . " " . $join["operator"] . " " . $join["right"];
                } else {
                    $sql .=  $join["left"] . " " . $join["operator"] . " ? ";
                    $this->params[] = $join["right"];
                }
            }
        }
        return [$sql, $this->params];
    }
}
