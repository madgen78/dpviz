<?php
namespace FreePBX\modules\Dpviz\dpp\destination;

require_once __DIR__ . '/baseDestinations.php';

class DestinationMeetme extends baseDestinations
{
    public const PRIORITY = 2500;

    public function __construct(object &$dpp)
    {
        parent::__construct($dpp);
        $this->regex = "/^ext-meetme,(\d+),(\d+)/";
    }

    public function callback_followDestinations(&$route, &$node, $destination, $matches)
    {
        $meetmenum 	 = $matches[1];
        $meetmeother = $matches[2];
        $meetme 	 = $route['meetme'][$meetmenum];

        $label       = sprintf(_('Conferences: %s %s'), $meetme['exten'], $meetme['description']);

        $node->attribute('label', $this->dpp->sanitizeLabels($label));
        $node->attribute('URL', $this->genUrlConfig('conferences', $meetmenum));  // '/admin/config.php?display=conferences&view=form&extdisplay='.$meetmenum
        $node->attribute('target', '_blank');
        $node->attribute('fillcolor', 'burlywood');
        $node->attribute('style', 'filled');
    }
}
