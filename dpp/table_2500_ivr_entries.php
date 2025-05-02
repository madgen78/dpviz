<?php
namespace FreePBX\modules\Dpviz\dpp\table;

require_once __DIR__ . '/baseTables.php';

class TableIvrEntries extends baseTables
{
    # IVR entries
    public const PRIORITY = 2500;

    public function __construct(object &$dpp)
    {
        parent::__construct($dpp, "ivr_entries");
        $this->key_id   = "ivr_id";
        $this->key_name = "ivrs";
    }

    public function callback_load(&$dproute)
    {
        foreach($this->getTableData() as $ent)
        {
            $id    = $ent[$this->key_id];
            $selid = $ent['selection'];

            $dproute[$this->key_name][$id]['entries'][$selid] = $ent;

            $this->log(9, sprintf("entry:  ivr=%s   selid=%s", $id, $selid));
        }
        return true;
    }
}
