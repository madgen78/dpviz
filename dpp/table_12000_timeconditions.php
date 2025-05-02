<?php
namespace FreePBX\modules\Dpviz\dpp\table;

require_once __DIR__ . '/baseTables.php';

class TableTimeconditions extends baseTables
{
    # Time Conditions
    public const PRIORITY = 12000;

    public function __construct(object &$dpp)
    {
        parent::__construct($dpp, "timeconditions");
        $this->key_id   = "timeconditions_id";
        $this->key_name = "timeconditions";
    }
}
