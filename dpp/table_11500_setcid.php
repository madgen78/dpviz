<?php
namespace FreePBX\modules\Dpviz\dpp\table;

require_once __DIR__ . '/baseTables.php';

class TableSetcid extends baseTables
{
    public const PRIORITY = 11500;

    public function __construct(object &$dpp)
    {
        parent::__construct($dpp, "setcid", true);
        $this->key_id   = "cid_id";
        $this->key_name = "setcid";
    }
}
