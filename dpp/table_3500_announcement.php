<?php
namespace FreePBX\modules\Dpviz\dpp\table;

require_once __DIR__ . '/baseTables.php';

class TableAnnouncement extends baseTables
{
    public const PRIORITY = 3500;

    public function __construct(object &$dpp)
    {
        parent::__construct($dpp, "announcement", true);
        $this->key_id      = "announcement_id";
        $this->key_name    = "announcements";
    }

    public function callback_load(&$dproute)
    {
        foreach($this->getTableData() as $an)
        {
            $id   = $an[$this->key_id];
            $dest = $an['post_dest'];
            $dproute[$this->key_name][$id] = $an;
            $dproute[$this->key_name][$id]['dest'] = $dest;

            $this->log(9, sprintf("announcement dest:  an=%s   dest=%s", $id, $dest));
        }
        return true;
    }
}
