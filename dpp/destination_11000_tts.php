<?php
namespace FreePBX\modules\Dpviz\dpp\destination;

require_once __DIR__ . '/baseDestinations.php';

class DestinationTts extends baseDestinations
{
    public const PRIORITY = 11000;

    public function __construct(object &$dpp)
    {
        parent::__construct($dpp);
        $this->regex = "/^ext-tts,(\d+),(\d+)/";
    }

    public function callback_followDestinations(&$route, &$node, $destination, $matches)
    {
        $ttsnum 	= $matches[1];
        $ttsother 	= $matches[2];
        $tts 		= $route['tts'][$ttsnum];

        $ttsLabel	= sprintf(_('TTS: %s'), $tts['name']);
        $ttsTooltip = sprintf(_('Engine: %s\\nDesc: %s'), $tts['engine'], $tts['text']);

        $node->attribute('label', $this->dpp->sanitizeLabels($ttsLabel));
        $node->attribute('tooltip', $this->dpp->sanitizeLabels($ttsTooltip));
        $node->attribute('URL', htmlentities('/admin/config.php?display=tts&view=form&id='.$ttsnum));
        $node->attribute('target', '_blank');
        $node->attribute('shape', 'note');
        $node->attribute('fillcolor', self::pastels[6]);
        $node->attribute('style', 'filled');

        if ($tts['goto'] != '')
        {
            $route['parent_node']       = $node;
            $route['parent_edge_label'] = _(' Continue');

            $this->dpp->followDestinations($route, $tts['goto'], '');
        }
    }
}
