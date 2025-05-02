<?php
namespace FreePBX\modules\Dpviz\dpp\table;

require_once __DIR__ . '/baseTables.php';

class TableMiscdests extends baseTables
{
    public const PRIORITY = 9000;

    public function __construct(object &$dpp)
    {
        parent::__construct($dpp, "miscdests", true);
        $this->key_id   = "id";
        $this->key_name = "miscdest";
    }
}
