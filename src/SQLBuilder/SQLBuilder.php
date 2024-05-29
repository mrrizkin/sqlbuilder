<?php

namespace SQLBuilder;

/**
 * Represents a SQL query builder.
 *
 * This class provides methods to build SQL queries dynamically.
 * It can be used to construct SELECT, INSERT, UPDATE, and DELETE queries.
 */
class SQLBuilder
{
    /**
     * Selects columns from the database table.
     *
     * @param array|string $columns The columns to select.
     * @return SelectBuilder The SelectBuilder instance.
     */
    public static function select($columns): SelectBuilder
    {
        $select = new SelectBuilder();
        return $select->select($columns);
    }

    /**
     * Inserts a new record into the specified table.
     *
     * @param string $table The name of the table to insert the record into.
     * @return InsertBuilder The InsertBuilder instance.
     */
    public static function insert($table): InsertBuilder
    {
        $insert = new InsertBuilder();
        return $insert->into($table);
    }

    /**
     * Update records in the specified table.
     *
     * @param string $table The name of the table to update.
     * @return UpdateBuilder The SQLBuilder instance.
     */
    public static function update($table): UpdateBuilder
    {
        $update = new UpdateBuilder();
        return $update->table($table);
    }

    /**
     * Deletes records from the specified table.
     *
     * @param string $table The name of the table to delete records from.
     * @return DeleteBuilder The DeleteBuilder instance.
     */
    public static function delete($table): DeleteBuilder
    {
        $delete = new DeleteBuilder();
        return $delete->from($table);
    }

    /**
     * Quotes a string for use in a SQL query.
     *
     * @param string $s The string to be quoted.
     * @return string The quoted string.
     */
    public static function quote($s): string
    {
        $safeSQL = new SafeSQL();
        return $safeSQL->quote($s);
    }
}
