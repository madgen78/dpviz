<?php
namespace FreePBX\modules\Dpviz\dpp\destination;

require_once __DIR__ . '/baseDestinations.php';

class DestinationDynroute extends baseDestinations
{
    public const PRIORITY = 4000;

    public function __construct(object &$dpp)
    {
        parent::__construct($dpp);
        $this->regex = "/^dynroute-(\d+)/";
    }

    public function callback_followDestinations(&$route, &$node, $destination, $matches)
    {
        $dynnum       = $matches[1];
        $dynrt        = $route['dynroute'][$dynnum];
        $recID        = $dynrt['announcement_id'];
        $announcement = isset($route['recordings'][$recID]) ? $route['recordings'][$recID]['displayname'] : _('None');

        $label        = sprintf(_('DYN: %s\\nAnnouncement: %s'), $dynrt['name'], $announcement);

        $node->attribute('label', $this->dpp->sanitizeLabels($label));
        $node->attribute('tooltip', $node->getAttribute('label'));
        $node->attribute('URL', htmlentities('/admin/config.php?display=dynroute&action=edit&id='.$dynnum));
        $node->attribute('target', '_blank');
        $node->attribute('shape', 'component');
        $node->attribute('fillcolor', self::pastels[12]);
        $node->attribute('style', 'filled');

        if (!empty($dynrt['routes']))
        {
            ksort($dynrt['routes']);
            foreach ($dynrt['routes'] as $selid => $ent)
            {
                $route['parent_node']       = $node;
                $route['parent_edge_label'] = $this->dpp->sanitizeLabels(sprintf(_('  Match: %s\\n%s'), $ent['selection'], $ent['description']));

                $this->dpp->followDestinations($route, $ent['dest'], '');
            }
        }

        //are the invalid and timeout destinations the same?
        if ($dynrt['invalid_dest'] == $dynrt['default_dest'])
        {
            $route['parent_node']       = $node;
            $route['parent_edge_label'] = sprintf(_(' Invalid Input, Default (%s secs)'), $dynrt['timeout']);

            $this->dpp->followDestinations($route, $dynrt['invalid_dest'], '');
        }
        else
        {
            if ($dynrt['invalid_dest'] != '')
            {
                $route['parent_node']       = $node;
                $route['parent_edge_label'] = _(' Invalid Input');

                $this->dpp->followDestinations($route, $dynrt['invalid_dest'], '');
            }
            if ($dynrt['default_dest'] != '')
            {
                $route['parent_node']       = $node;
                $route['parent_edge_label'] = sprintf(_(' Default (%s secs)'), $dynrt['timeout']);

                $this->dpp->followDestinations($route, $dynrt['default_dest'], '');
            }
        }
    }
}
