<?php
namespace FreePBX\modules\Dpviz\dpp\table;

require_once __DIR__ . '/baseTables.php';

class TableTimegroupsDetails extends baseTables
{
    # Time Groups Details
    public const PRIORITY = 13000;

    public function __construct(object &$dpp)
    {
        parent::__construct($dpp, "timegroups_details");
        $this->key_id   = "timegroupid";
        $this->key_name = "timegroups";
    }

    public function callback_load(&$dproute)
    {
        foreach($this->getTableData() as $tgd)
        {
            $id = $tgd[$this->key_id];
            if (! isset($dproute[$this->key_name][$id]))
            {
                $this->log(1, sprintf("timegroups_details id found for unknown timegroup, id=%s", $id));
                continue;
            }

            if (!isset($dproute[$this->key_name][$id]['time']))
            {
                $dproute[$this->key_name][$id]['time'] = "";
            }
            $exploded = explode("|", $tgd['time']);
            $time 	  = ($exploded[0] !== "*") ? $exploded[0] : "";
            $dow 	  = ($exploded[1] !== "*") ? sprintf("%s, ", ucwords($exploded[1], "-")) : "";
            $date 	  = ($exploded[2] !== "*") ? sprintf("%s ", $exploded[2]) : "";
            $month 	  = ($exploded[3] !== "*") ? sprintf("%s ", ucfirst($exploded[3])) : "";

            $dproute[$this->key_name][$id]['time'] .= sprintf("%s%s%s%s\\l", $dow, $month, $date, $time);
        }
        return true;
    }
}
