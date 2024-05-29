<?php

namespace SQLBuilder;

/**
 * Class InsertBuilder
 *
 * This class extends the SafeSQL class and provides functionality for building INSERT SQL queries.
 */
class InsertBuilder extends SafeSQL
{
    private $table;
    private $columns = [];
    private $values = [];
    private $params = [];

    /**
     * Sets the table to insert data into.
     *
     * @param string $table The name of the table.
     * @return $this The current instance of the SQLBuilder.
     */
    public function into($table): InsertBuilder
    {
        $this->table = $this->quoteTableName($table);
        return $this;
    }

    /**
     * Set the columns to be selected in the SQL query.
     *
     * @param array|string $columns The columns to be selected.
     * @return $this
     */
    public function columns($columns): InsertBuilder
    {
        if (is_string($columns)) {
            $columns = preg_split('/\s*,\s*/', $columns, -1, PREG_SPLIT_NO_EMPTY);
        }
        foreach ($columns as $column) {
            $this->columns[] = $this->quoteColumnName($column);
        }
        return $this;
    }

    /**
     * Sets the values for the SQL query.
     *
     * @param array $values The values to be set.
     * @return $this The current instance of the SQL builder.
     */
    public function values($values): InsertBuilder
    {
        if (is_string($values)) {
            $values = preg_split('/\s*,\s*/', $values, -1, PREG_SPLIT_NO_EMPTY);
        }
        $this->values[] = $values;
        return $this;
    }

    /**
     * Builds the SQL query.
     *
     * This method is responsible for constructing the SQL query based on the provided parameters.
     * It returns the final SQL query string.
     *
     * @return string The final SQL query string.
     */
    public function build(): array
    {
        $sql = "INSERT INTO " . $this->table . " (" . implode(", ", $this->columns) . ") VALUES ";
        $placeholders = [];
        foreach ($this->values as $value) {
            $placeholders[] = "(" . implode(", ", array_fill(0, count($value), "?")) . ")";
            $this->params = array_merge($this->params, $value);
        }
        $sql .= implode(", ", $placeholders);
        return [$sql, $this->params];
    }
}
