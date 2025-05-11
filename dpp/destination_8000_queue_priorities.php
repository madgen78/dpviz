<?php
namespace FreePBX\modules\Dpviz\dpp\destination;

require_once __DIR__ . '/baseDestinations.php';

class DestinationQueuePriorities extends baseDestinations
{
    # Queue Priorities
    public const PRIORITY = 8000;

    public function __construct(object &$dpp)
    {
        parent::__construct($dpp);
        $this->regex = "/^app-queueprio,(\d+),(\d+)/";
    }

    public function callback_followDestinations(&$route, &$node, $destination, $matches)
    {
        $queueprioID      = $matches[1];
        $queueprioIDOther = $matches[2];
        $queueprio        = $route['queueprio'][$queueprioID];


        $label= $this->sanitizeLabels(sprintf(_("Queue Priorities: %s\\nPriority: %s"), $queueprio['description'], $queueprio['queue_priority']));

        $node->attribute('label', $label);
        $node->attribute('tooltip', $label);
        $node->attribute('URL', $this->genUrlConfig('queueprio', $queueprioID)); // '/admin/config.php?display=queueprio&view=form&extdisplay='.$queueprioID
        $node->attribute('target', '_blank');
        $node->attribute('shape', 'rect');
        $node->attribute('fillcolor', self::pastels[16]);
        $node->attribute('style', 'filled');

        if ($queueprio['dest'] != '')
        {
            $this->findNextDestination($route, $node, $queueprio['dest'], _(" Continue"));

            // $route['parent_node']       = $node;
            // $route['parent_edge_label'] = _(" Continue");

            // $this->dpp->follow_destinations($route, sprintf("%s,%s", $queueprio['dest'], $lang), '');
        }
    }
}
