<?php
namespace FreePBX\modules\Dpviz\dpp\destination;

require_once __DIR__ . '/baseDestinations.php';

class DestinationTrunk extends baseDestinations
{
    public const PRIORITY = 13500;

    public function __construct(object &$dpp)
    {
        parent::__construct($dpp);
        $this->regex = "/^ext-trunk,(\d+),(\d+)/";
    }

    public function callback_followDestinations(&$route, &$node, $destination, $matches)
    {
        $trunk_id       = $matches[1];
        $trunk_priority = $matches[2];
        $trunk_tech     = $route['trunk'][$trunk_id]['tech'];
        $trunk_name     = $route['trunk'][$trunk_id]['name'];

        $label = sprintf(_('Trunk (%s): %s'), $trunk_tech, $trunk_name);

        $node->attribute('label', $this->dpp->sanitizeLabels($label));
        $node->attribute('tooltip', $node->getAttribute('label'));
        $node->attribute('URL', htmlentities(sprintf('/admin/config.php?display=trunks&tech=%s&extdisplay=OUT_%s', $trunk_tech, $trunk_id)));
        $node->attribute('target', '_blank');
        $node->attribute('shape', 'note');
        $node->attribute('fillcolor', 'oldlace');
        $node->attribute('style', 'filled');
    }
}
