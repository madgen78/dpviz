<?php
namespace FreePBX\modules\Dpviz\dpp\destination;

require_once __DIR__ . '/baseDestinations.php';

class DestinationSetCid extends baseDestinations
{
    public const PRIORITY = 10500;

    public function __construct(object &$dpp)
    {
        parent::__construct($dpp);
        $this->regex = "/^app-setcid,(\d+),(\d+)/";
    }

    public function callback_followDestinations(&$route, &$node, $destination, $matches)
    {
        $cidnum   = $matches[1];
        $cidother = $matches[2];
        $cid 	  = $route['setcid'][$cidnum];

        $cid_name = preg_replace('/\${CALLERID\(name\)}/i', '<name>', $cid['cid_name']);
        $cid_num  = preg_replace('/\${CALLERID\(num\)}/i', '<number>', $cid['cid_num']);
        $label    = sprintf(_('Set CID\\nName= %s\\lNumber= %s\\l'), $cid_name, $cid_num);

        $node->attribute('label', $this->dpp->sanitizeLabels($label));
        $node->attribute('tooltip', $node->getAttribute('label'));
        $node->attribute('URL', htmlentities('/admin/config.php?display=setcid&view=form&id='.$cidnum));
        $node->attribute('target', '_blank');
        $node->attribute('shape', 'note');
        $node->attribute('fillcolor', self::pastels[6]);
        $node->attribute('style', 'filled');

        if ($cid['dest'] != '')
        {
            $route['parent_node']       = $node;
            $route['parent_edge_label'] = _(' Continue');

            $this->dpp->followDestinations($route, $cid['dest'], '');
        }
    }
}
