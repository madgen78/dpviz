<?php
namespace FreePBX\modules\Dpviz\dpp\destination;

require_once __DIR__ . '/baseDestinations.php';

class DestinationRecording extends baseDestinations
{
    public const PRIORITY = 7500;

    public function __construct(object &$dpp)
    {
        parent::__construct($dpp);
        $this->regex = "/^play-system-recording,(\d+),(\d+)/";
    }

    public function callback_followDestinations(&$route, &$node, $destination, $matches)
    {
        $recID 		= $matches[1];
        $recIDOther = $matches[2];
        $playName   = isset($route['recordings'][$recID]) ? $route['recordings'][$recID]['displayname'] : _('None');

        $label = sprintf(_('Play Recording: %s'), $this->dpp->sanitizeLabels($playName));

        $node->attribute('label', $label);
        $node->attribute('URL', htmlentities('/admin/config.php?display=recordings&action=edit&id='.$recID));
        $node->attribute('target', '_blank');
        $node->attribute('shape', 'rect');
        $node->attribute('fillcolor', self::pastels['16']);
        $node->attribute('style', 'filled');
    }
}
