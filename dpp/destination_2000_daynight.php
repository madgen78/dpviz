<?php
namespace FreePBX\modules\Dpviz\dpp\destination;

require_once __DIR__ . '/baseDestinations.php';

class DestinationDaynight extends baseDestinations
{
    public const PRIORITY = 2000;

    public function __construct(object &$dpp)
    {
        parent::__construct($dpp);
        $this->regex = "/^app-daynight,(\d+),(\d+)/";
    }

    public function callback_followDestinations(&$route, &$node, $destination, $matches)
    {
        $daynightnum   = $matches[1];
        $daynightother = $matches[2];
        $daynight 	   = $route['daynight'][$daynightnum];

        #feature code exist?
        $code = '';
        if ( isset($route['featurecodes']['*28'.$daynightnum]) )
        {
            #custom feature code?
            if ($route['featurecodes']['*28'.$daynightnum]['customcode'] != '')
            {
                $featurenum = $route['featurecodes']['*28'.$daynightnum]['customcode'];
            }
            else
            {
                $featurenum = $route['featurecodes']['*28'.$daynightnum]['defaultcode'];
            }
            #is it enabled?
            $code = sprintf(_("\\nToggle (%s): %s"), ($route['featurecodes']['*28'.$daynightnum]['enabled'] == '1') ? _('enabled') : _('disabled'), $featurenum);
        }

        #check current status and set path to active
        $current_daynight = array();
        $C                = sprintf('/usr/sbin/asterisk -rx "database show DAYNIGHT/C%s" | cut -d \':\' -f2 | tr -d \' \' | head -1', $daynightnum);
        exec($C, $current_daynight);

        $dactive = "";
        $nactive = "";
        if ($current_daynight[0] == 'DAY')
        {
            $dactive = _("(Active)");
        }
        else
        {
            $nactive = _("(Active)");
        }

        foreach ($daynight as $d)
        {
            switch ($d['dmode'])
            {
                case 'day':
                    $route['parent_node']       = $node;
                    $route['parent_edge_label'] = sprintf(_(' Day Mode %s'), $dactive);

                    $this->dpp->followDestinations($route, $d['dest'], '');
                    break;

                case 'night':
                    $route['parent_node']       = $node;
                    $route['parent_edge_label'] = sprintf(_(' Night Mode %s'), $nactive);

                    $this->dpp->followDestinations($route, $d['dest'],'');
                    break;

                case 'fc_description':
                    $node->attribute('label', $this->dpp->sanitizeLabels(sprintf(_("Call Flow: %s%s"), $d['dest'], $code)));
                    break;
            }
        }

        $daynight = $route['daynight'][$daynightnum];
        $node->attribute('URL', htmlentities('/admin/config.php?display=daynight&view=form&itemid='.$daynightnum.'&extdisplay='.$daynightnum));
        $node->attribute('target', '_blank');
        $node->attribute('fillcolor', self::pastels[14]);
        $node->attribute('style', 'filled');
    }
}
