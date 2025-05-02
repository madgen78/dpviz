<?php
namespace FreePBX\modules\Dpviz\dpp\table;

require_once __DIR__ . '/baseTables.php';

class TableMeetme extends baseTables
{
    public const PRIORITY = 8500;

    public function __construct(object &$dpp)
    {
        parent::__construct($dpp, "meetme", true);
        $this->key_id   = "exten";
        $this->key_name = "meetme";
    }
}
