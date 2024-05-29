<?php

namespace SQLBuilder;

/**
 * Class UpdateBuilder
 *
 * Represents a SQL UPDATE statement builder.
 * Extends the SafeSQL class for safe query building.
 */
class UpdateBuilder extends SafeSQL
{
    private $table;
    private $columns = [];
    private $values = [];
    private $params = [];

    private $whereBuilder = null;

    public function __construct()
    {
        $this->table = null;
        $this->columns = [];
        $this->values = [];
        $this->params = [];

        $this->whereBuilder = new WhereBuilder();
    }

    /**
     * Sets the table for the SQL query.
     *
     * @param string $table The name of the table.
     * @return $this The current instance of the SQLBuilder.
     */
    public function table($table): UpdateBuilder
    {
        $this->table = $this->quoteTableName($table);
        return $this;
    }

    /**
     * Sets the value of a column in the SQL query.
     *
     * @param string $column The name of the column.
     * @param mixed $value The value to be set for the column.
     * @return void
     */
    public function set($column, $value): UpdateBuilder
    {
        $this->columns[] = $this->quoteColumnName($column);
        $this->values[] = $value;
        return $this;
    }

    /**
     * Adds a WHERE clause to the update query.
     *
     * @param string $column The column name.
     * @param string $operator The comparison operator. Default is "=".
     * @param mixed $value The value to compare against. Default is null.
     * @return $this The current instance of the UpdateBuilder.
     */
    public function where($column, $operator = "=", $value = null): UpdateBuilder
    {
        $this->whereBuilder->where($column, $operator, $value);
        return $this;
    }

    /**
     * Adds an OR condition to the WHERE clause of the update query.
     *
     * @param string $column The column name.
     * @param string $operator The comparison operator. Default is "=".
     * @param mixed $value The value to compare against. Default is null.
     * @return $this The current instance of the UpdateBuilder.
     */
    public function orWhere($column, $operator = "=", $value = null): UpdateBuilder
    {
        $this->whereBuilder->orWhere($column, $operator, $value);
        return $this;
    }

    /**
     * Builds the SQL query for the UPDATE statement.
     *
     * @return string The generated SQL query.
     */
    public function build(): array
    {
        $sql = "UPDATE " . $this->table . " SET ";
        $set = [];
        foreach ($this->columns as $i => $column) {
            $set[] = $column . " = ?";
            $this->params[] = $this->values[$i];
        }
        $sql .= implode(", ", $set);
        if (isset($this->whereBuilder)) {
            $where = $this->whereBuilder->build();
            if (!empty($where[0])) {
                $sql .= " WHERE " . $where[0];
                $this->params = array_merge($this->params, $where[1]);
            }
        }
        return [$sql, $this->params];
    }
}
