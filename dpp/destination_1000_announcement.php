<?php
namespace FreePBX\modules\Dpviz\dpp\destination;

require_once __DIR__ . '/baseDestinations.php';

class DestinationAnnouncement extends baseDestinations
{
    public const PRIORITY = 1000;

    public function __construct(object &$dpp)
    {
        parent::__construct($dpp);
        $this->regex = "/^app-announcement-(\d+),s,(\d+)/";
    }

    public function callback_followDestinations(&$route, &$node, $destination, $matches)
    {
        $annum	 = $matches[1];
        $another = $matches[2];

        $an    = $route['announcements'][$annum];
        $recID = $an['recording_id'];

        $announcement = isset($route['recordings'][$recID]) ? $route['recordings'][$recID]['displayname'] : _('None');
        #feature code exist?
        if ( isset($route['featurecodes']['*29'.$recID]) )
        {
            #custom feature code?
            if ($route['featurecodes']['*29'.$an['recording_id']]['customcode'] != '')
            {
                $featurenum = $route['featurecodes']['*29'.$an['recording_id']]['customcode'];
            }
            else
            {
                $featurenum = $route['featurecodes']['*29'.$an['recording_id']]['defaultcode'];
            }
            $rec_active = ($route['recordings'][$recID]['fcode']== '1') && ($route['featurecodes']['*29'.$recID]['enabled']=='1') ? _('yes'): _('no');
            $rec_status = $featurenum;
        }
        else
        {
            $rec_status = _('disabled');
            $rec_active = _('no');
        }

        $label = sprintf(_('Announcements: %s\\nRecording: %s\\nRecord (%s): %s'), $an['description'], $announcement, $rec_active, $rec_status);

        $node->attribute('label', $this->dpp->sanitizeLabels($label));
        $node->attribute('tooltip', $node->getAttribute('label'));
        $node->attribute('URL', $this->genUrlConfig('announcement', $annum)); //'/admin/config.php?display=announcement&view=form&extdisplay='.$annum
        $node->attribute('target', '_blank');
        $node->attribute('shape', 'note');
        $node->attribute('fillcolor', 'oldlace');
        $node->attribute('style', 'filled');

        # The destinations we need to follow are the no-answer destination
        # (postdest) and the members of the group.

        if ($an['post_dest'] != '')
        {
            $route['parent_node']       = $node;
            $route['parent_edge_label'] = _(' Continue');

            $this->dpp->followDestinations($route, $an['post_dest'], '');
        }
    }
}
