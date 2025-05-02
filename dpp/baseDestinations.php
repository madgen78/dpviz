<?php
namespace FreePBX\modules\Dpviz\dpp\destination;

abstract class baseDestinations
{
    protected $dpp   = null;
    protected $regex = null;

    // Set some colors
    Const pastels = [
        "#7979FF", "#86BCFF", "#8ADCFF", "#3DE4FC", "#5FFEF7", "#33FDC0",
        "#ed9581", "#81a6a2", "#bae1e7", "#eb94e2", "#f8d580", "#979291",
        "#92b8ef", "#ad8086", "#F7A8A8", "#C5A3FF", "#FFC3A0", "#FFD6E0",
        "#FFB3DE", "#D4A5A5", "#A5D4D4", "#F5C6EC", "#B5EAD7", "#C7CEEA",
        "#E0BBE4", "#FFDFD3", "#FEC8D8", "#D1E8E2", "#E8D1E1", "#EAD5DC",
        "#F9E79F", "#D6EAF8"
    ];

    /**
     * Constructor
     *
     * @param object $dpp       The dpp object
     */
    public function __construct(object &$dpp)
    {
        $this->dpp = &$dpp;
    }

    public function isSetDestination() : bool
    {
        if (empty($this->regex))
        {
            return false;
        }
        return true;
    }

    public function getDestinationRegEx() : ?string
    {
        if (! $this->isSetDestination())
        {
            return null;
        }
        return $this->regex;
    }

    /**
     * Relay the log function to the dpp class
     */
    protected function log($level, string $message)
    {
        $this->dpp->log($level, $message);
    }

    public function followDestinations(&$route, &$node, $destination, $matches)
    {
        if (method_exists($this, 'callback_followDestinations'))
        {
            // return call_user_func([$this, 'callback_followDestinations'], $route, $node, $destination, $matches);
            $callback = [$this, 'callback_followDestinations'];
            $args = [&$route, &$node, $destination, $matches];
            return call_user_func_array($callback, $args);
        }
        else
        {
            $this->log('error', 'No callback function found for followDestinations in ' . get_class($this));
            return false;
        }
    }

    public function genUrlConfig($display, $extdisplay, $view = 'form')
    {
        $url_view = is_null($view) ? '' : sprintf("&view=%s", $view);
        return htmlentities(sprintf('/admin/config.php?display=%s%s&extdisplay=%s', $display, $url_view, $extdisplay));
    }

}
