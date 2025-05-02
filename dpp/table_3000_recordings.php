<?php
namespace FreePBX\modules\Dpviz\dpp\table;

require_once __DIR__ . '/baseTables.php';

class TableRecordings extends baseTables
{
    # Recordings
    public const PRIORITY = 3000;

    public function __construct(object &$dpp)
    {
        parent::__construct($dpp, "recordings");
        $this->key_id   = "id";
        $this->key_name = "recordings";
    }
}
