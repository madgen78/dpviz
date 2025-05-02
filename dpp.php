<?php

Namespace FreePBX\Modules\Dpviz;

include __DIR__ . '/vendor/autoload.php';

class dpp {

    public $freepbx = null;
    public $db      = null;

    private $list_class_tables              = [];
    private $list_calss_tables_destinations = [];

    public $inroutes = [];
    public $dproutes = [];

    protected $direction = 'LR'; // LR = Left to Right, TB = Top to Bottom

    // Log Level: 0 = total quiet, 9 = much verbose
    Const DPP_LOG_LEVEL = 3;

    // Log file path
    Const LOG_FILE = "/var/log/asterisk/dpviz.log";

    // Const neons = [
    // 	"#fe0000", "#fdfe02", "#0bff01", "#011efe", "#fe00f6",
    // 	"#ff5f1f", "#ff007f", "#39ff14", "#ff073a", "#ffae00",
    // 	"#08f7fe", "#ff44cc", "#ff6ec7", "#dfff00", "#32cd32",
    // 	"#ccff00", "#ff1493", "#00ffff", "#ff00ff", "#ff4500",
    // 	"#ff00aa", "#ff4c4c", "#7df9ff", "#adff2f", "#ff6347",
    // 	"#ff66ff", "#f2003c", "#ffcc00", "#ff69b4", "#0aff02"
    // ];

    public function __construct($freepbx, $load_routes = true)
    {
        $this->freepbx = $freepbx;
        $this->db      = $freepbx->Database;

        $this->LoadClass();

        if ($load_routes)
        {
            $this->loadIncomingRoutes();
        }
    }

    public function getDirection()
    {
        return $this->direction ?? 'LR';
    }

    public function setDirection($direction)
    {
        if ($direction == 'TB' || $direction == 'LR')
        {
            $this->direction = $direction;
        }
        else
        {
            $this->log(1, sprintf(_("Invalid direction '%s'!"), $direction));
            return false;
        }
        return true;
    }


    /**
     * Clean the list of class tables.
     */
    private function cleanClass()
    {
        $this->list_class_tables = [];
        $this->list_calss_tables_destinations = [];
    }


    private function parseClassName(string $file, string $classPrefix = ''): ?string
    {
        if (!file_exists($file))
        {
            $this->log(1, sprintf("File '%s' not found!", $file));
            return null;
        }

        // Get the class name from the file name
        // Example: table_01_users.php → clase TableUsers
        // Example: table_02_users_extra.php → class TableUsersExtra
        $basename_file = basename($file, '.php'); // Get the file name without the extension, for example: table_01_users
        // $regex = sprintf('/%s_\d+_(.+)/', strtolower($classPrefix));
        $regex = sprintf('/%s_(?:\d+_)?(.+)/', strtolower($classPrefix));
        // if (!preg_match('/table_\d+_(.+)/', strtolower($basename_file), $matches))
        if (!preg_match($regex, strtolower($basename_file), $matches))
        {
            $this->log(1, sprintf(_("Not extracting class name from '%s'!"), $basename_file));
            return null;
        }

        // Convert to PascalCase: users_extra → UsersExtra
        $rawName     = $matches[1]; // Example: users, users_extra
        $parts       = explode('_', $rawName);
        $classSuffix = implode('', array_map('ucfirst', $parts));
        $pretyName   = sprintf('%s%s', $classPrefix, $classSuffix); // Example: TableUsers, TableUsersExtra
        return $pretyName;
    }

    /**
     * Load all class tables from the dpp_tables directory.
     * This method dynamically loads all table classes and stores them in the list_class_tables array.
     */
    private function loadClass()
    {
        $this->CleanClass();

        /**
         * Class Execution Order Management System
         *
         * Each PHP file must contain a class.
         * The class name is dynamically generated based on the file name.
         *
         * File naming rules:
         *  - Valid format: {prefix}_{optional_number_}_name.php
         *    Examples: table_01_users.php, table_users_extra.php
         *  - The number (e.g., '01') is optional. It is used only for visual organization in the folder.
         *
         * Class rules:
         *  - Classes can optionally define a 'PRIORITY' constant.
         *  - PRIORITY determines the execution order:
         *    - Classes with PRIORITY are executed first, sorted from lowest to highest value.
         *    - Classes without PRIORITY are executed afterward, in the order listed by the file system.
         *
         * Recommendation for PRIORITY:
         *  - Use large increments (e.g., 100, 200, 300) to allow inserting new classes later
         *    without needing to reorder existing ones.
         *
         * Execution summary:
         * 1. List all files in the folder.
         * 2. Import each file and calculate its class name.
         * 3. Separate classes into two groups: with PRIORITY and without PRIORITY.
         * 4. Sort and execute classes with PRIORITY first.
         * 5. Then execute classes without PRIORITY.
        */

        // Load all class tables and destinations from the dpp directory
        // The files should be named in the format: table_01_users.php, table_02_users_extra.php, etc.
        $files_tables       		   = glob(__DIR__ . '/dpp/table_*.php');
        $class_tables 	    		   = [];
        $class_tables_without_priority = [];
        sort($files_tables);      // Sort the files to ensure consistent loading order (table_01_users.php, table_02_users_extra.php, etc.)

        foreach ($files_tables as $file)
        {
            if (!file_exists($file))
            {
                $this->log(1, sprintf("File '%s' not found!", $file));
                continue;
            }

            // Get the class name from the file name
            $className = $this->parseClassName($file, 'Table');
            // Namespace + class name
            $fullClassName = sprintf('\\%s\\dpp\\table\\%s', __NAMESPACE__ , $className); // Example: \FreePBX\modules\Dpviz\dpp\table\TableUsers

            require_once $file; // Load the file
            if (class_exists($fullClassName))
            {
                if (defined("$fullClassName::PRIORITY"))
                {
                    $class_tables[$fullClassName] = $fullClassName::PRIORITY;
                }
                else
                {
                    $class_tables_without_priority[] = $fullClassName;
                }
            }
            else
            {
                $this->log(1, sprintf(_("Class '%s' not found in '%s'!"), $fullClassName, $file));
                continue;
            }
        }
        asort($class_tables);
        foreach (array_keys($class_tables) as $fullClassName)
        {
            // Create an instance of the class and add it to the list
            $this->list_class_tables[] = new $fullClassName($this);
            $this->log(5, sprintf(_("Class '%s' Create OK!"), $fullClassName));
        }
        foreach ($class_tables_without_priority as $fullClassName)
        {
            // Create an instance of the class and add it to the list
            $this->list_class_tables[] = new $fullClassName($this);
            $this->log(5, sprintf(_("Class '%s' Create OK, but without priority!"), $fullClassName));
        }


        // Load all class destinations from the dpp directory
        // The files should be named in the format: destination_01_users.php, destination_02_users_extra.php, etc.
        $files_destinations 				 = glob(__DIR__ . '/dpp/destination_*.php');
        $class_destinations 				 = [];
        $class_destinations_without_priority = [];
        sort($files_destinations); // Sort the files to ensure consistent loading order (destination_01_users.php, destination_02_users_extra.php, etc.)

        foreach ($files_destinations as $file)
        {
            if (!file_exists($file))
            {
                $this->log(1, sprintf("File '%s' not found!", $file));
                continue;
            }
            // Get the class name from the file name
            $className = $this->parseClassName($file, 'Destination');

            // Namespace + class name
            $fullClassName = sprintf('\\%s\\dpp\\destination\\%s', __NAMESPACE__ , $className); // Example: \FreePBX\modules\Dpviz\dpp\destination\DestinationUsers

            require_once $file; // Load the file
            if (class_exists($fullClassName))
            {
                if (defined("$fullClassName::PRIORITY"))
                {
                    $class_destinations[$fullClassName] = $fullClassName::PRIORITY;
                }
                else
                {
                    $class_destinations_without_priority[] = $fullClassName;
                }
            }
            else
            {
                $this->log(1, sprintf(_("Class '%s' not found in '%s'!"), $fullClassName, $file));
                continue;
            }
        }
        asort($class_destinations);
        foreach (array_keys($class_destinations) as $fullClassName)
        {
            // Create an instance of the class and add it to the list
            $this->list_calss_tables_destinations[] = new $fullClassName($this);
            $this->log(5, sprintf(_("Class '%s' Create OK!"), $fullClassName));
        }
        foreach ($class_destinations_without_priority as $fullClassName)
        {
            // Create an instance of the class and add it to the list
            $this->list_calss_tables_destinations[] = new $fullClassName($this);
            $this->log(5, sprintf(_("Class '%s' Create OK, but without priority!"), $fullClassName));
        }
    }

    /**
     * Get the list of class tables.
     * @param bool $force_load Whether to force load the class tables again.
     * @return array The list of class tables.
     */
    private function getClassTables(bool $force_load = false): array
    {
        if ($force_load || empty($this->list_class_tables))
        {
            $this->loadClass(); // Load the class tables if not already loaded
        }
        return $this->list_class_tables;
    }

    /**
     * Get the list of class destinations.
     * @param bool $force_load Whether to force load the class destinations again.
     */
    private function getClassDestinations(bool $force_load = false): array
    {
        if ($force_load || empty($this->list_calss_tables_destinations))
        {
            $this->loadClass(); // Load the class tables if not already loaded
        }
        return $this->list_calss_tables_destinations;
    }

    /**
     * Fetch all rows from the database using a SQL query.
     * @param string $sql The SQL query to execute.
     * @return array An array of associative arrays representing the rows.
     */
    public function fetchAll(string $sql, array $params = []): array
    {
        if (empty($sql))
        {
            return [];
        }
        $sth = $this->db->prepare($sql);
        $sth->execute($params);
        $results = $sth->fetchAll(\PDO::FETCH_ASSOC);
        return is_array($results) ? $results : [];
    }


    /**
     * Write a log message to the log file.
     * @param int $level The log level (0-9).
     * @param string $msg The log message.
     */
    public function log(int $level, string $msg)
    {
        if (self::DPP_LOG_LEVEL < $level) {
            return;
        }

        $ts = date('Y-m-d H:i:s');

        $logFile = self::LOG_FILE;
        $fd = fopen($logFile, "a");
        if (!$fd)
        {
            error_log(sprintf(_("Couldn't open log file: %s"), $logFile));
            return;
        }

        fwrite($fd, sprintf(_("[%s] [Level %d] %s\n"), $ts, $level, $msg));
        fclose($fd);
    }

    /**
     * Format a phone number into a standard format.
     * @param string $phoneNumber The phone number to format.
     * @return string The formatted phone number.
     */
    public function formatPhoneNumbers(string $phoneNumber) : string
    {
        $hasPlusOne = strpos($phoneNumber, '+1') === 0;

        // Strip all non-digit characters
        $digits = preg_replace('/\D/', '', $phoneNumber);

        // If +1 was present, remove the leading '1' from digits so we format the last 10
        if ($hasPlusOne && strlen($digits) === 11 && $digits[0] === '1')
        {
            $digits = substr($digits, 1);
        }

        if (strlen($digits) === 10)
        {
            $areaCode  = substr($digits, 0, 3);
            $nextThree = substr($digits, 3, 3);
            $lastFour  = substr($digits, 6, 4);

            return sprintf("%s(%s) %s-%s", $hasPlusOne ? '+1 ' : '', $areaCode, $nextThree, $lastFour);
        }

        // Return original if it doesn't fit expected pattern
        return $phoneNumber;
    }

    /**
     * Sanitize labels for Graphviz.
     * @param string $text The text to sanitize.
     * @return string The sanitized text.
     */
    public function sanitizeLabels(?string $text) : string
    {
        if ($text === null) {
            $text = '';
        }

        // // Convert HTML special characters
        // $text = htmlentities($text, ENT_QUOTES, 'UTF-8');

        // // Replace actual newlines with Graphviz-style escaped newline
        // $text = str_replace(["\r\n", "\r", "\n"], '\\n', $text);

        return $text;
    }

    /**
     * Convert seconds to a human-readable time format.
     * @param int $seconds The number of seconds to convert.
     * @return string The formatted time string.
     */
    public function secondsToTimes($seconds)
    {
        $seconds = (int) round($seconds); // Ensure whole number input

        $hours   = (int) ($seconds / 3600);
        $minutes = (int) (($seconds % 3600) / 60);
        $seconds = $seconds % 60;

        return $hours > 0 ? sprintf("%d hrs, %d mins", $hours, $minutes) : ($minutes > 0 ? sprintf("%d mins, %d secs", $minutes, $seconds) : sprintf("%d secs", $seconds));
    }


    /**
     * Load incoming routes from the database.
     */
    public function loadIncomingRoutes()
    {
        $sql     = sprintf("SELECT * FROM %s Order by extension", "incoming");
        $results = $this->fetchAll($sql);

        $this->inroutes = [];
        if (is_array($results) && !empty($results))
        {
            foreach ($results as $route)
            {
                $key = sprintf("%s%s", $route['extension'], $route['cidnum']);
                $this->inroutes[$key] = $route;
            }
        }
    }

    /**
     * Find a route by its number.
     * @param string $num The route number to find.
     * @return array The matching route, or an empty array if not found.
     */
    public function findRoute($num)
    {
        $match   = array();
        $pattern = '/[^_xX+0-9\[\]]/';   # remove all non-digits
        $num     = preg_replace($pattern, '', $num);

        // "extension" is the key for the routes hash
        foreach ($this->inroutes as $ext => $route)
        {
            if ($ext == $num) {
                $match = $this->inroutes[$num];
            }
        }
        $this->dproutes = $match;
        return $match;
    }

    public function isExistRoute($num, $force_load = false)
    {
        $this->loadIncomingRoutes($force_load);
        $exist = $this->findRoute($num);
        return empty($exist) ? false : true;
    }

    /**
     * Load all tables from the database.
     * @param bool $force_load Whether to force load the tables again.
     */
    public function loadTables($force_load = false)
    {
        foreach ($this->getClassTables($force_load) as $table_class)
        {

            // call the load method of the class
            $table_class->load();
        }
    }

    #
    # This is a recursive function.  It digs through various nodes
    # (ring groups, ivrs, time conditions, extensions, etc.) to find
    # the path a call takes.  It creates a graph of the path through
    # the dial plan, stored in the $route object.
    #
    #
    public function followDestinations(&$route, $destination, $optional)
    {
        $optional = preg_match('/^[_xX+\d\[\]]+$/', $optional) ? '' : $optional;

        if (! isset ($route['dpgraph']))
        {
            $route['dpgraph'] = new \Alom\Graphviz\Digraph('"'.$route['extension'].'"');
            $route['dpgraph']->attr(
                'graph',
                array(
                    'rankdir' => $this->getDirection()
                )
            );
        }

        $dpgraph = $route['dpgraph'];
        $this->log(9, "destination='$destination' route[extension]: " . print_r($route['extension'], true));

        # This only happens on the first call.  Every recursive call includes
        # a destination to look at.  For the first one, we get the destination from
        # the route object.

        if ($destination == '')
        {
            if (empty($route['extension']))
            {
                $didLabel = _('ANY');
            }
            elseif (is_numeric($route['extension']) && (strlen($route['extension'])==10 || strlen($route['extension'])==11))
            {
                $didLabel = $this->formatPhoneNumbers($route['extension']);
            }
            else
            {
                $didLabel = $route['extension'];
            }

            $didLink = sprintf("%s/", $route['extension']);
            if (!empty($route['cidnum']))
            {
                $didLabel .= sprintf(' / %s', $this->formatPhoneNumbers($route['cidnum']));
                $didLink  .= $route['cidnum'];
            }
            $didLabel  .= sprintf('\\n%s', $route['description']);

            $didData	= $route['incoming'][$route['extension']];
            $didTooltip = sprintf("%s\\n", $didData['extension']);
            $didTooltip.= !empty($didData['cidnum']) 		? sprintf(_('Caller ID Number= %s\\n'), $didData['cidnum']) : '';
            $didTooltip.= !empty($didData['description']) 	? sprintf(_('Description= %s\\n'), $didData['description']) : '';
            $didTooltip.= !empty($didData['alertinfo']) 	? sprintf(_('Alert Info= %s\\n'), $didData['alertinfo']) : '';
            $didTooltip.= !empty($didData['grppre']) 		? sprintf(_('CID Prefix= %s\\n'), $didData['grppre']) : '';
            $didTooltip.= !empty($didData['mohclass']) 		? sprintf(_('MOH Class= %s\\n'), $didData['mohclass']) : '';

            $node_extension = array(
                'label'		=> $this->sanitizeLabels($didLabel),
                'tooltip'	=> $this->sanitizeLabels($didTooltip),
                'width'		=> 2,
                'margin'	=> '.13',
                'shape'		=> 'cds',
                'style'		=> 'filled',
                'URL'		=> htmlentities('/admin/config.php?display=did&view=form&extdisplay='.urlencode($didLink)),
                'target'  	=>'_blank',
                'fillcolor' => 'darkseagreen'
            );
            $dpgraph->node($route['extension'], $node_extension);

            // $graph->node() returns the graph, not the node, so we always
            // have to get() the node after adding to the graph if we want
            // to save it for something.
            // UPDATE: beginNode() creates a node and returns it instead of
            // returning the graph.  Similarly for edge() and beginEdge().
            $route['parent_node'] = $dpgraph->get($route['extension']);



            # One of thse should work to set the root node, but neither does.
            # See: https://rt.cpan.org/Public/Bug/Display.html?id=101437
            #$route->{parent_node}->set_attribute('root', 'true');
            #$dpgraph->set_attribute('root' => $route->{extension});

            // If an inbound route has no destination, we want to bail, otherwise recurse.
            if ($optional != '')
            {
                $route['parent_edge_label'] = ' ';
                $this->followDestinations($route, $optional, '');
            }
            elseif ($route['destination'] != '')
            {
                $route['parent_edge_label'] = _(' Always');
                $this->followDestinations($route, $route['destination'], '');
            }
            return;
        }
        $this->log(9, "Inspecting destination $destination");

        // We use get() to see if the node exists before creating it.  get() throws
        // an exception if the node does not exist so we have to catch it.
        try
        {
            $node = $dpgraph->get($destination);
        }
        catch (\Exception $e)
        {
            $this->log(7, "Adding node: $destination");
            $node = $dpgraph->beginNode($destination);
            $node->attribute('margin', '.25,.055');
        }

        // Add an edge from our parent to this node, if there is not already one.
        // We do this even if the node already existed because this node might
        // have several paths to reach it.
        $ptxt = $route['parent_node']->getAttribute('label', '');
        $ntxt = $node->getAttribute('label', '');
        $this->log(9, "Found it: ntxt = $ntxt");

        if ($ntxt == '' )
        {
            $ntxt = sprintf(_("(new node: %s)"), $destination);
        }

        if ($dpgraph->hasEdge(array($route['parent_node'], $node)))
        {
            $this->log(9, "NOT making an edge from $ptxt -> $ntxt");
            $edge= $dpgraph->beginEdge(array($route['parent_node'], $node));
            $edge->attribute('label', $this->sanitizeLabels($route['parent_edge_label']));
            $edge->attribute('labeltooltip', $this->sanitizeLabels($ptxt));
            $edge->attribute('edgetooltip', $this->sanitizeLabels($ptxt));

        }
        else
        {
            $this->log(9, "Making an edge from $ptxt -> $ntxt");
            $edge= $dpgraph->beginEdge(array($route['parent_node'], $node));
            $edge->attribute('label', $this->sanitizeLabels($route['parent_edge_label']));
            $edge->attribute('labeltooltip', $this->sanitizeLabels($route['parent_edge_label']));

            if (preg_match("/^( Match| No Match)/", $route['parent_edge_label']))
            {
                $edge->attribute('URL', $route['parent_edge_url']);
                $edge->attribute('target', $route['parent_edge_target']);
                $edge->attribute('labeltooltip', $this->sanitizeLabels($route['parent_edge_labeltooltip']));
                $edge->attribute('edgetooltip', $this->sanitizeLabels($route['parent_edge_labeltooltip']));
            }
            if (preg_match("/^( IVR)./", $route['parent_edge_label']))
            {
                $edge->attribute('style', 'dashed');
            }

            //start from node
            if (preg_match("/^ +$/", $route['parent_edge_label']))
            {
                $edge->attribute('style', 'dotted');
            }
        }

        $this->log(9, "The Graph: " . print_r($dpgraph, true));

        // Now bail if we have already recursed on this destination before.
        if ($node->getAttribute('label', 'NONE') != 'NONE')
        {
            return;
        }

        foreach ($this->getClassDestinations() as &$item)
        {
            if (preg_match($item->getDestinationRegEx(), $destination, $matches))
            {
                $item->followDestinations($route, $node, $destination, $matches);
                break;
            }
        }
    }

    /**
     * Render the graph for a specific route number.
     * @param string $num The route number to render.
     * @param string $clickedNodeTitle The title of the clicked node (optional).
     * @return string The rendered graph in DOT format.
     */
    public function render($num, $clickedNodeTitle = '')
    {
        $optional = empty($clickedNodeTitle) ? '' : $clickedNodeTitle;

        $this->loadIncomingRoutes(true);
        $this->findRoute($num);
        $this->loadTables(true);
        $this->log(5, "Doing follow dest ...");
        $this->followDestinations($this->dproutes, '', $optional);
        $this->log(5, "Finished follow dest ...");
        return $this->dproutes['dpgraph']->render();
    }
}
