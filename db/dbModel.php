<?php

namespace app\core\db;

use app\core\Model;
use app\core\Application;

abstract class DbModel extends Model {

    abstract public function tableName(): string;

    abstract public function attributes(): array;

    abstract public static function primaryKey(): string;

    protected static function prepare($sql) {

        return Application::$app->db->pdo->prepare($sql);
    }

    public function save() {

        $tableName = $this->tableName();
        $attributes = $this->attributes();
        $columns = implode(', ', $attributes);
        $bindings = implode(', ', array_map(fn($a) => ":$a", $attributes));

        $sql = "INSERT INTO $tableName ($columns) VALUES ($bindings)";
        $statement = self::prepare($sql);

        foreach ($attributes as $attribute) {
            $statement->bindValue(":$attribute", $this->{$attribute});
        }

        $statement->execute();
        return true;
    }

    public static function findOne($tableName, $where) {

        $attributes = array_keys($where);
        $clause = implode("AND ", array_map(fn($attr) => "$attr = :$attr", $attributes));

        $sql ="SELECT * FROM $tableName WHERE $clause LIMIT 1";
        $statement = self::prepare($sql);

        foreach ($where as $key => $value) {
            $statement->bindValue(":$key", $value);
        }

        $statement->execute();
        return $statement->fetchObject(static::class);
    }
}