<?php
namespace FreePBX\modules\Dpviz\dpp\table;

require_once __DIR__ . '/baseTables.php';

class TableKvstoreFreepbxModulesCustomappsreg extends baseTables
{
    public const PRIORITY = 7500;

    public function __construct(object &$dpp)
    {
        parent::__construct($dpp, "kvstore_FreePBX_modules_Customappsreg", true);
        $this->key_id   = "key";
        $this->key_name = "customapps";
    }

    public function callback_load(&$dproute)
    {
        foreach($this->getTableData() as $customappsreg)
        {
            if (is_numeric($customappsreg[$this->key_id]))
            {
                $id  = $customappsreg[$this->key_id];
                $val = json_decode($customappsreg['val'], true);

                $dproute[$this->key_name][$id] = $val;

                $this->log(9, "customapps=$id");
            }
        }
        return true;
    }
}
