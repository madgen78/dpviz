<?php
namespace FreePBX\modules\Dpviz\dpp\destination;

require_once __DIR__ . '/baseDestinations.php';

class DestinationDirectory extends baseDestinations
{
    public const PRIORITY = 3000;

    public function __construct(object &$dpp)
    {
        parent::__construct($dpp);
        $this->regex = "/^directory,(\d+),(\d+)/";
    }

    public function callback_followDestinations(&$route, &$node, $destination, $matches)
    {
        $directorynum 	= $matches[1];
        $directoryother = $matches[2];
        $directory 		= $route['directory'][$directorynum];

        $label          = sprintf(_('Directory: %s'), $directory['dirname']);

        $node->attribute('label', $this->dpp->sanitizeLabels($label));
        $node->attribute('URL', $this->genUrlConfig('directory', $directorynum)); //'/admin/config.php?display=directory&view=form&id='.$directorynum
        $node->attribute('target', '_blank');
        $node->attribute('fillcolor', self::pastels[9]);
        $node->attribute('shape', 'folder');
        $node->attribute('style', 'filled');

        if ($directory['invalid_destination'] != '')
        {
            $route['parent_node']       = $node;
            $route['parent_edge_label'] = _(' Invalid Input');

            $this->dpp->followDestinations($route, $directory['invalid_destination'], '');
        }
    }
}
