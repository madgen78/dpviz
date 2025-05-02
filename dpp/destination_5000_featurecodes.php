<?php
namespace FreePBX\modules\Dpviz\dpp\destination;

require_once __DIR__ . '/baseDestinations.php';

class DestinationFeaturecodes extends baseDestinations
{
    public const PRIORITY = 5000;

    public function __construct(object &$dpp)
    {
        parent::__construct($dpp);
        $this->regex = "/^ext-featurecodes,(\*?\d+),(\d+)/";
    }

    public function callback_followDestinations(&$route, &$node, $destination, $matches)
    {
        $featurenum   = $matches[1];
        $featureother = $matches[2];
        $feature 	  = $route['featurecodes'][$featurenum];

        if ($feature['customcode'] != '')
        {
            $featurenum = $feature['customcode'];
        }

        $lable = sprintf(_('Feature Code: %s \\<%s\\>'), $feature['description'], $featurenum);

        $node->attribute('label', $this->dpp->sanitizeLabels($lable));
        $node->attribute('tooltip', $node->getAttribute('label'));
        $node->attribute('URL', htmlentities('/admin/config.php?display=featurecodeadmin'));
        $node->attribute('target', '_blank');
        $node->attribute('shape', 'folder');
        $node->attribute('fillcolor', 'gainsboro');
        $node->attribute('style', 'filled');
    }
}
