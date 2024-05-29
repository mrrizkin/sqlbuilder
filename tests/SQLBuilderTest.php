<?php

use PHPUnit\Framework\TestCase;
use SQLBuilder\SQLBuilder;

class SQLBuilderTest extends TestCase
{
    public function testSelect(): void
    {
        $sql = SQLBuilder::select("name, email")
            ->from("users")
            ->where("id", "=", 1)
            ->build();

        $this->assertEquals('SELECT "name", "email" FROM "users" WHERE "id" = ?', $sql[0]);
    }

    public function testUpdate(): void
    {
        $sql = SQLBuilder::update("users")
            ->set("name", "John")
            ->where("id", "=", 1)
            ->build();

        $this->assertEquals('UPDATE "users" SET "name" = ? WHERE "id" = ?', $sql[0]);
    }

    public function testDelete(): void
    {
        $sql = SQLBuilder::delete("users")
            ->where("id", "=", 1)
            ->build();

        $this->assertEquals('DELETE FROM "users" WHERE "id" = ?', $sql[0]);
    }

    public function testInsert(): void
    {
        $sql = SQLBuilder::insert("users")
            ->columns(["name", "email"])
            ->values(["John", "john@mail.com"])
            ->build();

        $this->assertEquals('INSERT INTO "users" ("name", "email") VALUES (?, ?)', $sql[0]);
    }

    public function testSQLInjection1(): void
    {
        $sql = SQLBuilder::select("name, email")
            ->from("users")
            ->where("id", "=", "1; DROP TABLE users")
            ->build();

        $this->assertEquals('SELECT "name", "email" FROM "users" WHERE "id" = ?', $sql[0]);
    }

    public function testSQLInjection2(): void
    {
        $sql = SQLBuilder::select("name, email")
            ->from("users")
            ->where("id", "= 1; DROP TABLE users", "")
            ->build();

        $this->assertEquals('SELECT "name", "email" FROM "users" WHERE "id" = ?', $sql[0]);
    }
}
