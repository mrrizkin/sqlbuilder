<?php

namespace SQLBuilder;

/**
 * Represents a class for building safe SQL queries.
 *
 * @package SQLBuilder
 */
class SafeSQL
{
    /**
     * Quotes a string for use in a SQL statement.
     *
     * @param string $s The string to be quoted.
     * @return string The quoted string.
     */
    public function quote($s): string
    {
        return "'" . str_replace("'", "''", $s) . "'";
    }

    /**
     * Sets the operator for the SQL query.
     *
     * @param string $operator The operator to be used in the query.
     * @return void
     */
    public function operators($operator): string
    {
        $operators = [
            "=",
            "<",
            ">",
            "<=",
            ">=",
            "<>",
            "!=",
            "LIKE",
            "ILIKE",
            "NOT LIKE",
            "IN",
            "NOT IN",
            "BETWEEN",
            "NOT BETWEEN",
            "IS NULL",
            "IS NOT NULL",
            "IS",
            "IS NOT"
        ];

        return in_array(strtoupper($operator), $operators) ? $operator : "=";
    }

    /**
     * Quotes a simple table name.
     *
     * @param string $s The table name to be quoted.
     * @return string The quoted table name.
     */
    public function quoteSimpleTableName($s): string
    {
        if (strpos($s, '"') !== false) {
            return $s;
        }
        return '"' . $s . '"';
    }

    /**
     * Quotes a simple column name.
     *
     * This method takes a string representing a simple column name and returns
     * the quoted version of the name. The quoted version of the name is suitable
     * for use in SQL queries to ensure proper escaping and prevent SQL injection attacks.
     *
     * @param string $s The simple column name to be quoted.
     * @return string The quoted version of the column name.
     */
    public function quoteSimpleColumnName($s): string
    {
        if (strpos($s, '"') !== false || $s == "*") {
            return $s;
        }
        return '"' . $s . '"';
    }

    /**
     * Quotes a table name to be used in a SQL query.
     *
     * @param string $s The table name to be quoted.
     * @return string The quoted table name.
     */
    public function quoteTableName($s): string
    {
        if (stripos($s, ' AS ') !== false) {
            $sParts = preg_split('/\s+AS\s+/i', $s, -1, PREG_SPLIT_NO_EMPTY);
            $column = $this->quoteTableName($sParts[0]);
            $column .= ' AS ' . $this->quoteTableName($sParts[1]);
            return $column;
        }

        if (strpos($s, '(') !== false || strpos($s, '{{') !== false) {
            return $s;
        }
        if (strpos($s, '.') === false) {
            return $this->quoteSimpleTableName($s);
        }
        $parts = explode('.', $s);
        foreach ($parts as &$part) {
            $part = $this->quoteSimpleTableName($part);
        }
        return implode('.', $parts);
    }

    /**
     * Quotes a column name to be used in a SQL query.
     *
     * @param string $s The column name to be quoted.
     * @return string The quoted column name.
     */
    public function quoteColumnName($s): string
    {
        if (stripos($s, ' AS ') !== false) {
            $sParts = preg_split('/\s+AS\s+/i', $s, -1, PREG_SPLIT_NO_EMPTY);
            $column = $this->quoteColumnName($sParts[0]);
            $column .= ' AS ' . $this->quoteColumnName($sParts[1]);
            return $column;
        }

        if (strpos($s, '(') !== false || strpos($s, '{{') !== false || strpos($s, '[[') !== false) {
            return $s;
        }
        $prefix = '';
        if (($pos = strrpos($s, '.')) !== false) {
            $prefix = $this->quoteTableName(substr($s, 0, $pos)) . '.';
            $s = substr($s, $pos + 1);
        }
        return $prefix . $this->quoteSimpleColumnName($s);
    }
}
