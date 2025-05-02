<?php
namespace FreePBX\modules\Dpviz\dpp\table;

require_once __DIR__ . '/baseTables.php';

class TableIvrDetails extends baseTables
{
    # IVRs
    public const PRIORITY = 2000;

    public function __construct(object &$dpp)
    {
        parent::__construct($dpp, "ivr_details");
        $this->key_id   = "id";
        $this->key_name = "ivrs";
    }
}
