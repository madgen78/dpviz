<?php
namespace FreePBX\modules\Dpviz\dpp\destination;

require_once __DIR__ . '/baseDestinations.php';

class DestinationCustom extends baseDestinations
{
    //public const PRIORITY = 9999999;

    public function __construct(object &$dpp)
    {
        parent::__construct($dpp);
        $this->regex = "/.*/";
    }

    public function callback_followDestinations(&$route, &$node, $destination, $matches)
    {
        if (!empty($route['customapps']))
        {
            #custom destinations
            $custDest = null;
            foreach ($route['customapps'] as $entry)
            {
                if ($entry['target'] === $destination)
                {
                    $custDest = $entry;
                    break;
                }
            }
        }
        #end of Custom Destinations

        if (!empty($custDest))
        {
            $custId	   = $entry['destid'];
            $custLabel = sprintf(_('Cust Dest: %s\\nTarget: %s\\l'), $entry['description'], $entry['target']);
            $custNotes = $entry['notes'];

            $node->attribute('label', $this->dpp->sanitizeLabels($custLabel));
            if (empty($custNotes))
            {
                $node->attribute('tooltip', $node->getAttribute('label'));
            }
            else
            {
                $node->attribute('tooltip', $this->dpp->sanitizeLabels($custNotes));
            }
            $node->attribute('URL', htmlentities('/admin/config.php?display=customdests&view=form&destid='.$custId));
            $node->attribute('target', '_blank');
            $node->attribute('shape', 'component');
            $node->attribute('fillcolor', self::pastels[27]);
            $node->attribute('style', 'filled');
        }
        else
        {
            $this->log(1, "Unknown destination type: $destination");
            $node->attribute('fillcolor', self::pastels[12]);
            $node->attribute('label', $this->dpp->sanitizeLabels($destination));
            $node->attribute('style', 'filled');
        }
    }
}
