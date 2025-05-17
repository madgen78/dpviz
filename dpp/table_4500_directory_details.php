<?php

namespace FreePBX\modules\Dpviz\dpp\table;

require_once __DIR__ . '/BaseTables.php';

class TableDirectoryDetails extends BaseTables
{
    public const PRIORITY = 4500;

    public function __construct(object &$dpp)
    {
        parent::__construct($dpp, "directory_details", true);
        $this->key_id   = "id";
        $this->key_name = "directory";
    }
}
