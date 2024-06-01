<?php

namespace SQLBuilder;

use Exception;

/**
 * Class SelectBuilder
 *
 * This class represents a SQL SELECT query builder.
 * It extends the SafeSQL class to ensure safe and secure SQL queries.
 */
class SelectBuilder extends SafeSQL
{
    private $selects = [];
    private $froms = [];
    private $orderBys = [];
    private $groupBys = [];
    private $limit = null;
    private $offset = null;
    private $params = [];

    private $whereBuilder = null;
    private $joinBuilder = null;

    public function __construct()
    {
        $this->selects = [];
        $this->froms = [];
        $this->orderBys = [];
        $this->groupBys = [];
        $this->limit = null;
        $this->offset = null;
        $this->params = [];

        $this->whereBuilder = new WhereBuilder();
        $this->joinBuilder = new JoinBuilder();
    }

    /**
     * Selects columns from the database table.
     *
     * @param array|string $columns The columns to select.
     * @return $this
     */
    public function select($columns): SelectBuilder
    {
        if (is_string($columns)) {
            $columns = preg_split('/\s*,\s*/', $columns, -1, PREG_SPLIT_NO_EMPTY);
        }
        foreach ($columns as $column) {
            $this->selects[] = $this->quoteColumnName($column);
        }
        return $this;
    }

    /**
     * Set the table(s) to select from.
     *
     * @param string|array $tables The table(s) to select from.
     * @return $this
     */
    public function from($tables): SelectBuilder
    {
        if (is_string($tables)) {
            $tables = preg_split('/\s*,\s*/', $tables, -1, PREG_SPLIT_NO_EMPTY);
        }
        foreach ($tables as $table) {
            $this->froms[] = $this->quoteTableName($table);
        }
        return $this;
    }

    /**
     * Adds a WHERE clause to the SQL query.
     *
     * @param string $column The column name.
     * @param string $operator The comparison operator. Default is "=".
     * @param mixed $value The value to compare against. Default is null.
     * @return SelectBuilder
     */
    public function where($column, $operator = "=", $value = null): SelectBuilder
    {
        $this->whereBuilder->where($column, $operator, $value);
        return $this;
    }

    /**
     * Adds an OR condition to the WHERE clause of the query.
     *
     * @param string $column The column name.
     * @param string $operator The comparison operator. Default is "=".
     * @param mixed $value The value to compare against. Default is null.
     * @return $this The current instance of the SQLBuilder class.
     */
    public function orWhere($column, $operator = "=", $value = null): SelectBuilder
    {
        $this->whereBuilder->orWhere($column, $operator, $value);
        return $this;
    }

    /**
     * Adds a WHERE clause to the SQL query.
     *
     * @param string $column The column name.
     * @param string $operator The comparison operator. Default is "=".
     * @param mixed $value The value to compare against. Default is null.
     * @return SelectBuilder
     */
    public function whereRaw(): SelectBuilder
    {
        $args = func_get_args();
        $statement = "";
        $bind = [];

        if (count($args) == 1) {
            $statement = $args[0];
        } elseif (count($args) >= 2) {
            $statement = $args[0];
            for ($i = 1; $i < count($args); $i++) {
                $bind[] = $args[$i];
            }
        } else {
            throw new Exception("Invalid number of arguments provided.");
        }

        $this->whereBuilder->whereRaw($statement, $bind);
        return $this;
    }

    /**
     * Adds an OR condition to the WHERE clause of the query.
     *
     * @param string $column The column name.
     * @param string $operator The comparison operator. Default is "=".
     * @param mixed $value The value to compare against. Default is null.
     * @return $this The current instance of the SQLBuilder class.
     */
    public function orWhereRaw(): SelectBuilder
    {
        $args = func_get_args();
        $statement = "";
        $bind = [];

        if (count($args) == 1) {
            $statement = $args[0];
        } elseif (count($args) >= 2) {
            $statement = $args[0];
            for ($i = 1; $i < count($args); $i++) {
                $bind[] = $args[$i];
            }
        } else {
            throw new Exception("Invalid number of arguments provided.");
        }

        $this->whereBuilder->orWhereRaw($statement, $bind);
        return $this;
    }

    /**
     * Joins a table in the SQL query.
     *
     * @param string $table The name of the table to join.
     * @param string $column The column to join on.
     * @param string $operator The operator to use for the join condition. Default is "=".
     * @param mixed $value The value to compare with the column. Default is null.
     * @return $this The current instance of the SQLBuilder.
     */
    public function join($table, $column, $operator = "=", $value = null): SelectBuilder
    {
        $this->joinBuilder->join($table, $column, $operator, $value);
        return $this;
    }

    /**
     * Performs a left join operation on the specified table.
     *
     * @param string $table The name of the table to join.
     * @param string $column The column to join on.
     * @param string $operator The operator to use in the join condition. Default is "=".
     * @param mixed $value The value to compare with the column in the join condition. Default is null.
     * @return $this The current instance of the SQLBuilder class.
     */
    public function leftJoin($table, $column, $operator = "=", $value = null): SelectBuilder
    {
        $this->joinBuilder->leftJoin($table, $column, $operator, $value);
        return $this;
    }

    /**
     * Performs a right join operation on the specified table.
     *
     * @param string $table The name of the table to join.
     * @param string $column The column to join on.
     * @param string $operator The operator to use in the join condition. Defaults to "=".
     * @param mixed $value The value to compare with the join column. Defaults to null.
     * @return $this The current instance of the SQLBuilder class.
     */
    public function rightJoin($table, $column, $operator = "=", $value = null): SelectBuilder
    {
        $this->joinBuilder->rightJoin($table, $column, $operator, $value);
        return $this;
    }

    /**
     * Performs an inner join operation on the specified table.
     *
     * @param string $table The name of the table to join.
     * @param string $column The column to join on.
     * @param string $operator The operator to use in the join condition. Defaults to "=".
     * @param mixed $value The value to compare against in the join condition. Defaults to null.
     * @return $this The current instance of the SQLBuilder class.
     */
    public function innerJoin($table, $column, $operator = "=", $value = null): SelectBuilder
    {
        $this->joinBuilder->innerJoin($table, $column, $operator, $value);
        return $this;
    }

    /**
     * Sets the GROUP BY clause for the SQL query.
     *
     * @param array|string $columns The columns to group by.
     * @return $this The current instance of the SQL builder.
     */
    public function groupBy($columns): SelectBuilder
    {
        if (is_string($columns)) {
            $columns = preg_split('/\s*,\s*/', $columns, -1, PREG_SPLIT_NO_EMPTY);
        }
        foreach ($columns as $column) {
            $this->groupBys[] = $this->quoteColumnName($column);
        }
        return $this;
    }

    /**
     * Sets the column and direction for ordering the query results.
     *
     * @param string $column The column to order by.
     * @param string $direction The direction of the ordering. Default is "ASC".
     * @return $this The current instance of the SQLBuilder.
     */
    public function orderBy($column, $direction = "ASC"): SelectBuilder
    {
        $this->orderBys[] = [$this->quoteColumnName($column), $direction];
        return $this;
    }

    /**
     * Sets the maximum number of rows to be returned by the query.
     *
     * @param int $limit The maximum number of rows to be returned.
     * @return $this The current instance of the SQLBuilder.
     */
    public function limit($limit): SelectBuilder
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * Sets the offset for the query result.
     *
     * @param int $offset The number of rows to skip from the beginning of the result set.
     * @return $this The current instance of the SQLBuilder class.
     */
    public function offset($offset): SelectBuilder
    {
        $this->offset = $offset;
        return $this;
    }

    public function clone(): SelectBuilder
    {
        $clone = new SelectBuilder();
        $clone->selects = $this->selects;
        $clone->froms = $this->froms;
        $clone->orderBys = $this->orderBys;
        $clone->groupBys = $this->groupBys;
        $clone->limit = $this->limit;
        $clone->offset = $this->offset;
        $clone->params = $this->params;
        $clone->whereBuilder = $this->whereBuilder->clone();
        $clone->joinBuilder = $this->joinBuilder->clone();
        return $clone;
    }

    /**
     * Builds the SQL query based on the provided parameters.
     *
     * @return string The generated SQL query.
     */
    public function build(): array
    {
        $sql = "SELECT ";
        if (empty($this->selects)) {
            $sql .= "*";
        } else {
            $sql .= implode(", ", $this->selects);
        }
        $sql .= " FROM " . implode(", ", $this->froms);
        if (isset($this->joinBuilder)) {
            $join = $this->joinBuilder->build();
            $sql .= $join[0];
            $this->params = array_merge($this->params, $join[1]);
        }
        if (isset($this->whereBuilder)) {
            $where = $this->whereBuilder->build();
            if (!empty($where[0])) {
                $sql .= " WHERE " . $where[0];
                $this->params = array_merge($this->params, $where[1]);
            }
        }
        if (!empty($this->groupBys)) {
            $sql .= " GROUP BY " . implode(", ", $this->groupBys);
        }
        if (!empty($this->orderBys)) {
            $sql .= " ORDER BY ";
            foreach ($this->orderBys as $orderBy) {
                $sql .= $orderBy[0] . " " . $orderBy[1] . ", ";
            }
            $sql = rtrim($sql, ", ");
        }
        if ($this->limit !== null) {
            $sql .= " LIMIT ?";
            $this->params[] = $this->limit;
        }
        if ($this->offset !== null) {
            $sql .= " OFFSET ?";
            $this->params[] = $this->offset;
        }

        return [$sql, $this->params];
    }
}
