<?php
namespace FreePBX\modules\Dpviz\dpp\destination;

require_once __DIR__ . '/baseDestinations.php';

class DestinationRingGroups extends baseDestinations
{
    public const PRIORITY = 9500;

    public function __construct(object &$dpp)
    {
        parent::__construct($dpp);
        $this->regex = "/^ext-group,(\d+)/";
    }

    public function callback_followDestinations(&$route, &$node, $destination, $matches)
    {
        $rgnum 	 = $matches[1];
        $rg      = $route['ringgroups'][$rgnum];

        $label   = sprintf(_('Ring Group: %s %s'), $rgnum, $rg['description']);

        //TODO: change metod to getSetting() in Dpviz class
        $combineQueueRing  = \FreePBX::Dpviz()->getSetting('combine_queue_ring');

        $node->attribute('label', $this->dpp->sanitizeLabels($label));
        $node->attribute('URL', $this->genUrlConfig('ringgroups', $rgnum)); //'/admin/config.php?display=ringgroups&view=form&extdisplay='.$rgnum
        $node->attribute('target', '_blank');
        $node->attribute('fillcolor', self::pastels[12]);
        $node->attribute('style', 'filled');

        $grplist=str_replace('#', '', $rg['grplist']);
        $grplist = preg_split("/-/", $grplist);

        foreach ($grplist as $member)
        {
            $route['parent_node']       = $node;
            $route['parent_edge_label'] = '';

            switch ($combineQueueRing)
            {
                case "1":
                    $go = sprintf("qmember%s", $member);
                    break;

                case "2":
                    $go = sprintf("from-did-direct,%s,1", $member);
                    break;

                default:
                    $go = sprintf("rg%s", $member);
            }
            $this->dpp->followDestinations($route, $go, '');
            //$this->dpp->followDestinations($route, sprintf( $combineQueueRing ? "qmember%s" : "rg%s", $member), '');
        }

        # The destinations we need to follow are the no-answer destination
        # (postdest) and the members of the group.
        if ($rg['postdest'] != '')
        {
            $route['parent_node'] = $node;
            $route['parent_edge_label'] = sprintf(_(' No Answer (%s)'), $this->dpp->secondsToTimes($rg['grptime']));

            $this->dpp->followDestinations($route, $rg['postdest'], '');
        }
    }
}
