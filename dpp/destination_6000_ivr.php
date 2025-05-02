<?php
namespace FreePBX\modules\Dpviz\dpp\destination;

require_once __DIR__ . '/baseDestinations.php';

class DestinationIvr extends baseDestinations
{
    public const PRIORITY = 6000;

    public function __construct(object &$dpp)
    {
        parent::__construct($dpp);
        $this->regex = "/^ivr-(\d+),([a-z]+),(\d+)/";
    }

    public function callback_followDestinations(&$route, &$node, $destination, $matches)
    {
        $inum   = $matches[1];
        $iflag  = $matches[2];
        $iother = $matches[3];

        $ivr		= $route['ivrs'][$inum];
        $recID		= $ivr['announcement'] ?? '';
        $ivrName    = $ivr['name'] ?? '';
        $ivrRecName = isset($route['recordings'][$recID]) ? $route['recordings'][$recID]['displayname'] : 'None';

        $ivrDestination        = $ivr['invalid_destination'] ?? '';
        $ivrDestinationTimeout = $ivr['timeout_destination'] ?? '';

        #feature code exist?
        if ( isset($route['featurecodes']['*29'.$recID]) )
        {
            #custom feature code?
            if ($route['featurecodes']['*29'.$recID]['customcode']!='')
            {
                $featurenum = $route['featurecodes']['*29'.$recID]['customcode'];
            }
            else
            {
                $featurenum = $route['featurecodes']['*29'.$recID]['defaultcode'];
            }
            #is it enabled?
            $rec_active = ($route['recordings'][$recID]['fcode']== '1') && ($route['featurecodes']['*29'.$recID]['enabled']=='1') ? _('yes'): _('no');
            $rec_status = $featurenum;
        }
        else
        {
            $rec_status = _('disabled');
            $rec_active = _('no');
        }
        $label = sprintf(_('IVR: %s\\nAnnouncement: %s\\lRecord (%s): %s\\l'), $ivrName, $ivrRecName, $rec_active, $rec_status);

        $node->attribute('label', $this->dpp->sanitizeLabels($label));
        $node->attribute('tooltip', $node->getAttribute('label'));
        $node->attribute('URL', htmlentities('/admin/config.php?display=ivr&action=edit&id='.$inum));
        $node->attribute('target', '_blank');
        $node->attribute('shape', 'component');
        $node->attribute('fillcolor', 'gold');
        $node->attribute('style', 'filled');

        # The destinations we need to follow are the invalid_destination,
        # timeout_destination, and the selection targets

        #now go through the selections
        if (!empty($ivr['entries']))
        {
            ksort($ivr['entries']);
            foreach ($ivr['entries'] as $selid => $ent)
            {
                $route['parent_node']       = $node;
                $route['parent_edge_label'] = sprintf(_(' Selection %s'), $this->dpp->sanitizeLabels($ent['selection']));

                $this->dpp->followDestinations($route, $ent['dest'], '');
            }
        }

        #are the invalid and timeout destinations the same?
        if ($ivrDestination == $ivrDestinationTimeout)
        {
            if (!empty($ivrDestination))
            {
                $route['parent_node']       = $node;
                $route['parent_edge_label'] = sprintf(_(' Invalid Input, Timeout (%s secs)'), $ivr['timeout_time']);

                $this->dpp->followDestinations($route, $ivrDestination, '');
            }
        }
        else
        {
            if ($ivrDestination != '')
            {
                $route['parent_node']       = $node;
                $route['parent_edge_label'] = _(' Invalid Input');

                $this->dpp->followDestinations($route, $ivrDestination, '');
            }
            if ($ivrDestinationTimeout != '')
            {
                $route['parent_node']       = $node;
                $route['parent_edge_label'] = sprintf(_(' Timeout (%s secs)'), $ivr['timeout_time']);

                $this->dpp->followDestinations($route, $ivrDestinationTimeout, '');
            }
        }
    }
}
