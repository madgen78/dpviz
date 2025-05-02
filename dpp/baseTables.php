<?php
namespace FreePBX\modules\Dpviz\dpp\table;

abstract class baseTables
{
    protected $dpp   = null;
    protected $route = null;

    protected $tableName = '';
    protected $optional  = false;

    protected $key_id   = "id";
    protected $key_name = "";

    /**
     * Constructor
     *
     * @param object $dpp       The dpp object
     * @param string $tableName The name of the table
     * @param bool   $optional  Whether the table is optional or not
     */
    public function __construct(object &$dpp, string $tableName = '', bool $optional = false)
    {
        $this->optional  = $optional;
        $this->tableName = $tableName;
        $this->dpp       = &$dpp;
        $this->route     = &$dpp->dproutes;
    }

    /**
     * Get is the table is optional or not
     * @return bool true if the table is optional, false otherwise
     */
    public function isOptional(): bool
    {
        return $this->optional;
    }

    /**
     * Check if the table exists in the database
     * @return bool true if the table exists, false otherwise
     */
    protected function isExistTable(): bool
    {
        $sql = "SHOW TABLES LIKE ?";
        $result = $this->dpp->fetchAll($sql, [$this->getTableName()]);
        return !empty($result);
    }

    /**
     * Get the table name
     */
    public function getTableName(): string
    {
        return $this->tableName;
    }

    /**
     * Get the table data from the database
    */
    protected function getTableData(): array
    {
        if (!$this->isExistTable())
        {
            return [];
        }

        $sql    = sprintf("SELECT * FROM %s", $this->getTableName());
        $return = $this->dpp->fetchAll($sql);
        return is_array($return) ? $return : [];
    }

    /**
     * Relay the log function to the dpp class
     */
    protected function log($level, $message)
    {
        $this->dpp->log($level, $message);
    }

    public function load(): ?bool
    {
        // callback function to load the table
        if (!$this->isExistTable())
        {
            $this->log(9, sprintf("Skip, table '%s' not exist!", $this->getTableName()));
            return false;
        }

        // call the callback function to load the table
        if (method_exists($this, 'callback_load'))
        {
            $callback = [$this, 'callback_load'];
            $args = [&$this->route];
            return call_user_func_array($callback, $args);

            // return call_user_func([$this, 'callback_load'], $this->route);
        }
        else
        {
            if (empty($this->key_name) || empty($this->key_id))
            {
                $this->log(9, sprintf("Skip, table '%s' has no key_name or key_id!", $this->getTableName()));
                return false;
            }
            foreach($this->getTableData() as $item)
            {
                $id = $item[$this->key_id];
                $this->route[$this->key_name][$id] = $item;
                $this->log(9, sprintf("%s=%s", $this->key_name, $id));
            }
            return true;
        }
    }
}
