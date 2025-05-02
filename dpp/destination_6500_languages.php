<?php
namespace FreePBX\modules\Dpviz\dpp\destination;

require_once __DIR__ . '/baseDestinations.php';

class DestinationLanguages extends baseDestinations
{
    public const PRIORITY = 6500;

    public function __construct(object &$dpp)
    {
        parent::__construct($dpp);
        $this->regex = "/^app-languages,(\d+),(\d+)/";
    }

    public function callback_followDestinations(&$route, &$node, $destination, $matches)
    {
        $langnum   = $matches[1];
        $langother = $matches[2];
        $lang      = $route['languages'][$langnum];

        $lable = sprintf(_('Languages: %s'), $lang['description']);

        $node->attribute('label', $this->dpp->sanitizeLabels($lable));
        $node->attribute('URL', $this->genUrlConfig('languages', $langnum)); //'/admin/config.php?display=languages&view=form&extdisplay='.$langnum
        $node->attribute('target', '_blank');
        $node->attribute('shape', 'note');
        $node->attribute('fillcolor', self::pastels[6]);
        $node->attribute('style', 'filled');

        if ($lang['dest'] != '')
        {
            $route['parent_node']       = $node;
            $route['parent_edge_label'] = _(' Continue');

            $this->dpp->followDestinations($route, $lang['dest'],'');
        }
    }
}
