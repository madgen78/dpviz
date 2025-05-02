<?php
namespace FreePBX\modules\Dpviz\dpp\table;

require_once __DIR__ . '/baseTables.php';

class TableFeaturecodes extends baseTables
{
    public const PRIORITY = 6500;

    public function __construct(object &$dpp)
    {
        parent::__construct($dpp, "featurecodes", true);
        $this->key_id   = "defaultcode";
        $this->key_name = "featurecodes";
    }
}
