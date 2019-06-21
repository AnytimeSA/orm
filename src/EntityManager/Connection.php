<?php

namespace Anytime\ORM\EntityManager;


/**
 * This object allow to close the connection of PDO easely even if it is used in multiple classes as the PDO object will remains until at least one reference of it still exists somewhere.
 *
 * Class Connection
 * @package Anytime\ORM\EntityManager
 */
class Connection
{
    /**
     * @var \PDO
     */
    private $pdo;

    /**
     * Connection constructor.
     * @param \PDO $pdo
     */
    public function __construct(\PDO $pdo = null)
    {
        $this->pdo = $pdo;
    }

    /**
     * @return \PDO
     */
    public function getPDO(): \PDO
    {
        return $this->pdo;
    }

    /**
     * @param \PDO $pdo
     */
    public function setPDO(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * @return bool
     */
    public function closeConnection(): bool
    {
        if(is_object($this->pdo)) {
            $this->pdo = null;
            return true;
        }

        return false;
    }

    public function beginTransaction(): bool
    {
        return $this->pdo->beginTransaction();
    }

    /**
     * @return bool
     */
    public function commit(): bool
    {
        return $this->pdo->commit();
    }

    /**
     * @return mixed
     */
    public function errorCode()
    {
        return $this->pdo->errorCode();
    }

    /**
     * @return array
     */
    public function errorInfo(): array
    {
        return $this->pdo->errorInfo();
    }

    /**
     * @param $statement
     * @return int
     */
    public function exec($statement): int
    {
        return $this->pdo->exec($statement);
    }

    /**
     * @param $attribute
     * @return mixed
     */
    public function getAttribute($attribute)
    {
        return $this->pdo->getAttribute($attribute);
    }

    /**
     * @return array
     */
    public function getAvailableDrivers(): array
    {
        return $this->pdo->getAvailableDrivers();
    }

    /**
     * @return bool
     */
    public function inTransaction(): bool
    {
        return $this->pdo->inTransaction();
    }

    /**
     * @param null $name
     * @return string
     */
    public function lastInsertId($name = null): string
    {
        return $this->pdo->lastInsertId($name);
    }

    /**
     * @param $statement
     * @param array $driver_options
     * @return bool|\PDOStatement
     */
    public function prepare($statement, array $driver_options = [])
    {
        return $this->pdo->prepare($statement, $driver_options);
    }

    /**
     * @param $statement
     * @param int $mode
     * @param null $arg3
     * @param array $ctorargs
     * @return bool|\PDOStatement
     */
    public function query($statement, $mode = \PDO::ATTR_DEFAULT_FETCH_MODE, $arg3 = null, array $ctorargs = [])
    {
        return $this->pdo->query($statement, $mode, $arg3, $ctorargs);
    }

    /**
     * @param $string
     * @param int $parameter_type
     * @return string
     */
    public function quote($string, $parameter_type = \PDO::PARAM_STR): string
    {
        return $this->pdo->quote($string, $parameter_type);
    }

    /**
     * @return bool
     */
    public function rollBack(): bool
    {
        return $this->pdo->rollBack();
    }

    /**
     * @param $attribute
     * @param $value
     * @return bool
     */
    public function setAttribute($attribute, $value): bool
    {
        return $this->pdo->setAttribute($attribute, $value);
    }

}
