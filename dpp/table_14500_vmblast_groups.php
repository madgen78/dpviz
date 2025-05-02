<?php
namespace FreePBX\modules\Dpviz\dpp\table;

require_once __DIR__ . '/baseTables.php';

class TableVmblastGroups extends baseTables
{
    public const PRIORITY = 14500;

    public function __construct(object &$dpp)
    {
        parent::__construct($dpp, "vmblast_groups", true);
        $this->key_id   = "grpnum";
        $this->key_name = "vmblasts";
    }

    public function callback_load(&$dproute)
    {
        foreach($this->getTableData() as $vmblastsGrp)
        {
            $id = $vmblastsGrp[$this->key_id];

            $dproute[$this->key_name][$id]['members'][] = $vmblastsGrp['ext'];

            $this->log(9, "vmblast:  vmblast=$id");
        }
        return true;
    }
}
