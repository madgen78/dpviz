<?php
namespace FreePBX\modules\Dpviz\dpp\destination;

require_once __DIR__ . '/baseDestinations.php';

class DestinationBlackhole extends baseDestinations
{
    public const PRIORITY = 1500;

    public function __construct(object &$dpp)
    {
        parent::__construct($dpp);
        $this->regex = "/^app-blackhole,(hangup|congestion|busy|zapateller|musiconhold|ring|no-service),(\d+)/";
    }

    public function callback_followDestinations(&$route, &$node, $destination, $matches)
    {
        $blackholetype  = str_replace('musiconhold', _('Music On Hold'), $matches[1]);
        $blackholeother = $matches[2];

        $labal          = sprintf(_('Terminate Call: %s'), ucwords($blackholetype,'-'));
        $previousURL    = $route['parent_node']->getAttribute('URL', '');

        $node->attribute('label', $labal);
        $node->attribute('tooltip', $labal);
        $node->attribute('URL', $previousURL);
        $node->attribute('target', '_blank');
        $node->attribute('shape', 'invhouse');
        $node->attribute('fillcolor', 'orangered');
        $node->attribute('style', 'filled');
    }
}
