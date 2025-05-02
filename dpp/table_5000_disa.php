<?php
namespace FreePBX\modules\Dpviz\dpp\table;

require_once __DIR__ . '/baseTables.php';

class TableDisa extends baseTables
{
    public const PRIORITY = 5000;

    public function __construct(object &$dpp)
    {
        parent::__construct($dpp, "disa", true);
        $this->key_id   = "disa_id";
        $this->key_name = "disa";
    }
}
