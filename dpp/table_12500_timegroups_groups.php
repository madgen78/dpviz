<?php
namespace FreePBX\modules\Dpviz\dpp\table;

require_once __DIR__ . '/baseTables.php';

class TableTimegroupsGroups extends baseTables
{
    # Time Groups
    public const PRIORITY = 12500;

    public function __construct(object &$dpp)
    {
        parent::__construct($dpp, "timegroups_groups");
        $this->key_id   = "id";
        $this->key_name = "timegroups";
    }
}
