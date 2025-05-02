<?php
namespace FreePBX\modules\Dpviz\dpp\table;

require_once __DIR__ . '/baseTables.php';

class TableQueuesConfig extends baseTables
{
    # Queues
    public const PRIORITY = 10000;

    public function __construct(object &$dpp)
    {
        parent::__construct($dpp, "queues_config", true);
        $this->key_id   = "extension";
        $this->key_name = "queues";
    }

    public function callback_load(&$dproute)
    {
        foreach($this->getTableData() as $result)
        {
            $id = $result[$this->key_id];
            $dproute[$this->key_name][$id] = $result;
            $dproute[$this->key_name][$id]['members']['static']  = array();
            $dproute[$this->key_name][$id]['members']['dynamic'] = array();
        }
        return true;
    }
}
