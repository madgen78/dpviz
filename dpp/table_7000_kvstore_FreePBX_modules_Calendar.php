<?php
namespace FreePBX\modules\Dpviz\dpp\table;

require_once __DIR__ . '/baseTables.php';

class TableKvstoreFreepbxMmodulesCalendar extends baseTables
{
    public const PRIORITY = 7000;

    public function __construct(object &$dpp)
    {
        parent::__construct($dpp, "kvstore_FreePBX_modules_Calendar", true);
        $this->key_id   = "key";
        $this->key_name = "calendar";
    }

    public function callback_load(&$dproute)
    {
        foreach($this->getTableData() as $calendar)
        {
            switch ($calendar['id'])
            {
                case 'calendars':
                case 'groups':
                    $id = $calendar['key'];
                    $dproute[$this->key_name][$id] = json_decode($calendar['val'],true);
                    dpplog(9, "calendars=$id");
                    break;

                default:
                    dpplog(1, "Unknown calendar type: {$calendar['id']}");
            }
        }
        return true;
    }
}
