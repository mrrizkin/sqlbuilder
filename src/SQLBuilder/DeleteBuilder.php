<?php

namespace SQLBuilder;

/**
 * Represents a DeleteBuilder class that extends SafeSQL.
 */
class DeleteBuilder extends SafeSQL
{
    private $table;
    private $params = [];

    private $whereBuilder = null;

    public function __construct()
    {
        $this->table = null;
        $this->params = [];

        $this->whereBuilder = new WhereBuilder();
    }

    /**
     * Set the table to delete from.
     *
     * @param string $table The name of the table to delete from.
     * @return $this The current instance of the DeleteBuilder.
     */
    public function from($table): DeleteBuilder
    {
        $this->table = $this->quoteTableName($table);
        return $this;
    }

    /**
     * Adds a WHERE clause to the delete query.
     *
     * @param string $column The column name.
     * @param string $operator The comparison operator. Default is "=".
     * @param mixed $value The value to compare against. Default is null.
     * @return $this The current instance of the DeleteBuilder.
     */
    public function where($column, $operator = "=", $value = null): DeleteBuilder
    {
        $this->whereBuilder->where($column, $operator, $value);
        return $this;
    }

    /**
     * Adds an OR condition to the WHERE clause of the delete query.
     *
     * @param string $column The column name.
     * @param string $operator The comparison operator. Default is "=".
     * @param mixed $value The value to compare against. Default is null.
     * @return $this The current instance of the DeleteBuilder.
     */
    public function orWhere($column, $operator = "=", $value = null): DeleteBuilder
    {
        $this->whereBuilder->orWhere($column, $operator, $value);
        return $this;
    }

    /**
     * Builds the SQL query for deleting records.
     *
     * @return string The generated SQL query.
     */
    public function build(): array
    {
        $sql = "DELETE FROM " . $this->table;
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
