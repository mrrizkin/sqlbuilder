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
        $this->assertEquals([1], $sql[1]);
    }

    public function testSelectAs(): void
    {
        $sql = SQLBuilder::select("name as n, email as e")
            ->from("users")
            ->where("id", "=", 1)
            ->build();

        $this->assertEquals('SELECT "name" AS "n", "email" AS "e" FROM "users" WHERE "id" = ?', $sql[0]);
        $this->assertEquals([1], $sql[1]);
    }

    public function testSelectWhereRaw(): void
    {
        $sql = SQLBuilder::select("name, email")
            ->from("users")
            ->whereRaw("LOWER(name) = ?", "john")
            ->build();

        $this->assertEquals('SELECT "name", "email" FROM "users" WHERE LOWER(name) = ?', $sql[0]);
        $this->assertEquals(["john"], $sql[1]);
    }

    public function testUpdate(): void
    {
        $sql = SQLBuilder::update("users")
            ->set("name", "John")
            ->where("id", "=", 1)
            ->build();

        $this->assertEquals('UPDATE "users" SET "name" = ? WHERE "id" = ?', $sql[0]);
        $this->assertEquals(["John", 1], $sql[1]);
    }

    public function testDelete(): void
    {
        $sql = SQLBuilder::delete("users")
            ->where("id", "=", 1)
            ->build();

        $this->assertEquals('DELETE FROM "users" WHERE "id" = ?', $sql[0]);
        $this->assertEquals([1], $sql[1]);
    }

    public function testInsert(): void
    {
        $sql = SQLBuilder::insert("users")
            ->columns(["name", "email"])
            ->values(["John", "john@mail.com"])
            ->build();

        $this->assertEquals('INSERT INTO "users" ("name", "email") VALUES (?, ?)', $sql[0]);
        $this->assertEquals(["John", "john@mail.com"], $sql[1]);
    }

    public function testClone(): void
    {
        $sql = SQLBuilder::select("name, email")
            ->from("users")
            ->where("status", "=", 1);

        $sql2 = $sql->clone()->limit(10);

        $build1 = $sql->build();
        $build2 = $sql2->build();

        $this->assertEquals('SELECT "name", "email" FROM "users" WHERE "status" = ?', $build1[0]);
        $this->assertEquals([1], $build1[1]);

        $this->assertEquals('SELECT "name", "email" FROM "users" WHERE "status" = ? LIMIT ?', $build2[0]);
        $this->assertEquals([1, 10], $build2[1]);
    }

    public function testSQLInjection1(): void
    {
        $sql = SQLBuilder::select("name, email")
            ->from("users")
            ->where("id", "=", "1; DROP TABLE users")
            ->build();

        $this->assertEquals('SELECT "name", "email" FROM "users" WHERE "id" = ?', $sql[0]);
        $this->assertEquals(["1; DROP TABLE users"], $sql[1]);
    }

    public function testSQLInjection2(): void
    {
        $sql = SQLBuilder::select("name, email")
            ->from("users")
            ->where("id", "= 1; DROP TABLE users", "")
            ->build();

        $this->assertEquals('SELECT "name", "email" FROM "users" WHERE "id" = ?', $sql[0]);
        $this->assertEquals([""], $sql[1]);
    }
}
