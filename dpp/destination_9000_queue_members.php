<?php
namespace FreePBX\modules\Dpviz\dpp\destination;

require_once __DIR__ . '/baseDestinations.php';

class DestinationQueueMembers extends baseDestinations
{
    # Queue members (static and dynamic)
    public const PRIORITY = 9000;

    public function __construct(object &$dpp)
    {
        parent::__construct($dpp);
        $this->regex = "/^qmember(\d+)/";
    }

    public function callback_followDestinations(&$route, &$node, $destination, $matches)
    {
        $qextension = $matches[1];

        if (isset($route['extensions'][$qextension]['name']))
        {
            $label = sprintf(_('Ext %s\\n%s'), $qextension, $route['extensions'][$qextension]['name']);
        }
        else
        {
            $label = $qextension;
        }
        $label = $this->dpp->sanitizeLabels($label);

        $node->attribute('label', $label);
        $node->attribute('tooltip', $node->getAttribute('label'));

        if (!is_numeric($label))
        {
            $node->attribute('URL', $this->genUrlConfig('extensions', $qextension, null)); // '/admin/config.php?display=extensions&extdisplay='.$qextension
            $node->attribute('target', '_blank');
        }

        if ($route['parent_edge_data_status'] == 'static')
        {
            $node->attribute('fillcolor', self::pastels[20]);
        }
        else
        {
            $node->attribute('fillcolor', self::pastels[8]);
        }
        $node->attribute('style', 'filled');
    }
}
