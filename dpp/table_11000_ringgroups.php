<?php
namespace FreePBX\modules\Dpviz\dpp\table;

require_once __DIR__ . '/baseTables.php';

class TableRinggroups extends baseTables
{
    public const PRIORITY = 11000;

    public function __construct(object &$dpp)
    {
        parent::__construct($dpp, "ringgroups", true);
        $this->key_id   = "grpnum";
        $this->key_name = "ringgroups";
    }
}
