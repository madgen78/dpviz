<?php
namespace FreePBX\modules\Dpviz\dpp\destination;

require_once __DIR__ . '/baseDestinations.php';

class DestinationVoicemail extends baseDestinations
{
    public const PRIORITY = 12000;

    public function __construct(object &$dpp)
    {
        parent::__construct($dpp);
        $this->regex = "/^ext-local,vm([b,i,s,u])(\d+),(\d+)/";
    }

    public function callback_followDestinations(&$route, &$node, $destination, $matches)
    {
        $vmtype	 = $matches[1];
        $vmnum 	 = $matches[2];
        $vmother = $matches[3];

        $vm_array = array(
            'b' => _('(Busy Message)'),
            'i' => _('(Instructions Only)'),
            's' => _('(No Message)'),
            'u' => _('(Unavailable Message)')
        );
        $vmname   = $route['extensions'][$vmnum]['name'];
        $vmemail  = $route['extensions'][$vmnum]['email'];
        $vmemail  = str_replace("|",",\\n",$vmemail);

        $label = sprintf(_('Voicemail: %s %s %s\\n%s'), $vmnum, $vmname, $vm_array[$vmtype], $vmemail);

        $node->attribute('label', $this->dpp->sanitizeLabels($label));
        $node->attribute('tooltip', $node->getAttribute('label'));
        $node->attribute('URL', htmlentities('/admin/config.php?display=extensions&extdisplay='.$vmnum));
        $node->attribute('target', '_blank');
        $node->attribute('shape', 'folder');
        $node->attribute('fillcolor', self::pastels[11]);
        $node->attribute('style', 'filled');
    }
}
