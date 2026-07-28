<?php
// License for all code of this FreePBX module can be found in the license file inside the module directory
// Copyright 2015 Sangoma Technologies.
// vim: set ai ts=4 sw=4 ft=php:

namespace FreePBX\modules;

class Dpviz extends \FreePBX_Helpers implements \BMO {

    private $freepbx;

    public function __construct($freepbx = null) {
        parent::__construct($freepbx);
        $this->freepbx = $freepbx;
        $this->db = $this->freepbx->Database;
    }

    protected function hasPersistentInstallTable() {
        try {
            $sth = $this->db->query("SHOW TABLES LIKE 'dpviz_persist'");
            return (bool)$sth->fetchColumn();
        } catch (\Exception $e) {
            return false;
        }
    }

    protected function getPersistentInstallUuid() {
        if (!$this->hasPersistentInstallTable()) {
            return '';
        }

        try {
            $sth = $this->db->prepare("SELECT install_uuid FROM dpviz_persist WHERE id = 1");
            $sth->execute();
            $uuid = $sth->fetchColumn();
            return is_string($uuid) ? trim($uuid) : '';
        } catch (\Exception $e) {
            return '';
        }
    }

    protected function setPersistentInstallUuid($uuid) {
        if (!$this->isValidUuid($uuid) || !$this->hasPersistentInstallTable()) {
            return false;
        }

        try {
            $sth = $this->db->prepare("REPLACE INTO dpviz_persist (id, install_uuid) VALUES (1, :uuid)");
            return (bool)$sth->execute(array(':uuid' => $uuid));
        } catch (\Exception $e) {
            return false;
        }
    }

    protected function ensureInstallUuid($forceRegenerate = false) {
        $uuid = false;

        if (!$forceRegenerate) {
            $uuid = $this->getPersistentInstallUuid();

            if (!$this->isValidUuid($uuid)) {
                $uuid = $this->getConfig('install_uuid');
                if ($this->isValidUuid($uuid)) {
                    $this->setPersistentInstallUuid($uuid);
                }
            }
        }

        if (!$this->isValidUuid($uuid)) {
            $uuid = $this->generateUuidV4();
            $this->setPersistentInstallUuid($uuid);
        }

        $this->setConfig('install_uuid', $uuid);

        return $uuid;
    }

    protected function isValidUuid($uuid) {
        return is_string($uuid) && preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-[1-5][a-f0-9]{3}-[89ab][a-f0-9]{3}-[a-f0-9]{12}$/i', $uuid);
    }

    protected function generateUuidV4() {
        $bytes = '';

        if (function_exists('openssl_random_pseudo_bytes')) {
            $strong = false;
            $bytes = openssl_random_pseudo_bytes(16, $strong);
            if ($bytes === false || strlen($bytes) !== 16) {
                $bytes = '';
            }
        }

        if (strlen($bytes) !== 16) {
            $bytes = '';
            for ($i = 0; $i < 16; $i++) {
                $bytes .= chr(mt_rand(0, 255));
            }
        }

        $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
        $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);
        $hex = bin2hex($bytes);

        return sprintf(
            '%s-%s-%s-%s-%s',
            substr($hex, 0, 8),
            substr($hex, 8, 4),
            substr($hex, 12, 4),
            substr($hex, 16, 4),
            substr($hex, 20, 12)
        );
    }

    protected function readFirstAvailableFile($paths) {
        foreach ((array)$paths as $path) {
            if (is_readable($path)) {
                $value = trim((string)@file_get_contents($path));
                if ($value !== '') {
                    return $value;
                }
            }
        }

        return '';
    }

    protected function getMacAddresses() {
        $macs = array();
        $paths = glob('/sys/class/net/*/address');

        if (!is_array($paths)) {
            return $macs;
        }

        foreach ($paths as $path) {
            $iface = basename(dirname($path));
            if ($iface === 'lo') {
                continue;
            }

            $mac = strtolower(trim((string)@file_get_contents($path)));
            if ($mac !== '' && $mac !== '00:00:00:00:00:00') {
                $macs[] = $iface . ':' . $mac;
            }
        }

        sort($macs);
        return $macs;
    }

    protected function getInstallFingerprint() {
        $parts = array();
        $parts[] = 'host:' . php_uname('n');

        $machineId = $this->readFirstAvailableFile(array('/etc/machine-id', '/var/lib/dbus/machine-id'));
        if ($machineId !== '') {
            $parts[] = 'machine:' . $machineId;
        }

        $macs = $this->getMacAddresses();
        if (!empty($macs)) {
            $parts[] = 'macs:' . implode(',', $macs);
        }

        return hash('sha256', implode('|', $parts));
    }


    /**
     * Reject cross-site requests to state-changing commands.
     *
     * The framework already referer-checks, but only when the CHECKREFERER
     * config setting is enabled -- a system-wide toggle this module does not
     * control and an admin can switch off. This repeats the check locally so
     * dpviz's write endpoints defend themselves either way, and additionally
     * honors Origin, which the framework does not look at.
     *
     * By construction this can never reject a request the framework would
     * have accepted with CHECKREFERER on: that path already required a
     * same-host Referer, which satisfies this too.
     */
    protected function requireSameOrigin() {
        $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
        if ($host === '') {
            return $this->denyCrossOrigin();
        }

        // A cross-site <form> cannot set a custom header, and a cross-origin
        // fetch that tries one is stopped by the CORS preflight, so this
        // header is proof the call came from our own page's JavaScript.
        if (!empty($_SERVER['HTTP_X_DPVIZ_REQUEST'])) {
            return true;
        }

        // Origin first (sent on cross-origin form posts, so it is the more
        // reliable signal), then Referer. First one present decides.
        foreach (array('HTTP_ORIGIN', 'HTTP_REFERER') as $key) {
            if (empty($_SERVER[$key])) {
                continue;
            }
            $parsed = parse_url($_SERVER[$key]);
            if (empty($parsed['host'])) {
                continue;
            }
            $candidate = $parsed['host'] . (isset($parsed['port']) ? ':' . $parsed['port'] : '');
            return ($candidate === $host) ? true : $this->denyCrossOrigin();
        }

        return $this->denyCrossOrigin();
    }

    protected function denyCrossOrigin() {
        if (!headers_sent()) {
            header('Content-Type: application/json');
            http_response_code(403);
        }
        echo json_encode(array(
            'status'  => 'error',
            'message' => _('Request rejected: cross-site or unverifiable origin.')
        ));
        exit;
    }

    /**
     * Does the logged-in admin hold the given FreePBX section (ACL) permission?
     *
     * Section names are the menuitem keys from each module's module.xml -- the
     * same keys config.php gates page display on (ivr, queues, did, ...). We
     * defer to ampuser::checkSection() so the '*' wildcard and the legacy
     * ampuser conversion path are handled exactly as core handles them.
     *
     * Returns false when there is no authenticated admin in the session. That
     * matters: Ajax.class.php skips authentication entirely for requests
     * originating from 127.0.0.1, so without this the loopback interface would
     * be an unauthenticated write path into the dialplan.
     */
    protected function userHasSection($section) {
        if ($section === '' || !isset($_SESSION['AMP_user'])) {
            return false;
        }
        $user = $_SESSION['AMP_user'];
        if (!is_object($user) || !method_exists($user, 'checkSection')) {
            return false;
        }
        return (bool)$user->checkSection($section);
    }

    /**
     * Gate a state-changing ajax command on a section permission.
     *
     * Emits a JSON error and exits when the admin lacks the permission, so
     * callers can treat a return as "allowed". The graph only draws the
     * add/edit affordances a user has access to, but that is a client-side
     * decision -- this is the server-side enforcement behind it.
     */
    protected function requireSection($section) {
        if ($this->userHasSection($section)) {
            return true;
        }

        if (!headers_sent()) {
            header('Content-Type: application/json');
            http_response_code(403);
        }
        echo json_encode(array(
            'status'  => 'error',
            'message' => _('Permission denied: your account does not have access to this module.')
        ));
        exit;
    }

    /**
     * Maps the module names the create-destination modal posts to the FreePBX
     * section that governs them. Anything not listed here is refused outright.
     */
    protected function createDestinationSection($module) {
        $map = array(
            'Announcements'      => 'announcement',
            'Call Flow Control'  => 'daynight',
            'Call Recording'     => 'callrecording',
            'Dynamic Routes'     => 'dynroute',
            'Inbound Routes'     => 'did',
            'IVR'                => 'ivr',
            'Languages'          => 'languages',
            'Misc Destinations'  => 'miscdests',
            'Queues'             => 'queues',
            'Ring Groups'        => 'ringgroups',
            'Set CallerID'       => 'setcid',
            'Time Conditions'    => 'timeconditions'
        );

        return isset($map[$module]) ? $map[$module] : '';
    }

    /**
     * Resolve a requested audio file to a real path inside one of the two
     * directories this module is allowed to serve, or false.
     *
     * The roots come from FreePBX config where available so installs that move
     * ASTVARLIBDIR/ASTSPOOLDIR keep working; the literals are the stock paths
     * and only act as a fallback.
     */
    protected function resolveAudioPath($filename) {
        $varlib = '/var/lib/asterisk';
        $spool  = '/var/spool/asterisk';
        try {
            $cfg = \FreePBX::Config();
            $v = $cfg->get('ASTVARLIBDIR');
            $s = $cfg->get('ASTSPOOLDIR');
            if (!empty($v)) { $varlib = $v; }
            if (!empty($s)) { $spool  = $s; }
        } catch (\Exception $e) {
            // fall back to the stock paths
        }

        $roots = array(
            rtrim($varlib, '/') . '/sounds',
            rtrim($spool, '/') . '/voicemail'
        );

        $real = realpath($filename);
        if ($real === false || !is_file($real)) {
            return false;
        }

        foreach ($roots as $root) {
            $realRoot = realpath($root);
            if ($realRoot === false) {
                continue;
            }
            // Trailing separator matters: without it "/var/lib/asterisk/sounds"
            // would also prefix-match "/var/lib/asterisk/sounds-stolen/x.wav"
            $realRoot = rtrim($realRoot, '/') . '/';
            if (strpos($real, $realRoot) === 0) {
                return $real;
            }
        }

        return false;
    }

    protected function getCurrentUsername() {
        if (isset($_SESSION['AMP_user'])) {
            if (is_string($_SESSION['AMP_user']) && $_SESSION['AMP_user'] !== '') {
                return (string)$_SESSION['AMP_user'];
            }
            if (is_array($_SESSION['AMP_user']) && !empty($_SESSION['AMP_user']['username'])) {
                return (string)$_SESSION['AMP_user']['username'];
            }
            if (is_object($_SESSION['AMP_user'])) {
                if (!empty($_SESSION['AMP_user']->username)) {
                    return (string)$_SESSION['AMP_user']->username;
                }
                if (method_exists($_SESSION['AMP_user'], 'getUsername')) {
                    return (string)$_SESSION['AMP_user']->getUsername();
                }
            }
        }
        if (!empty($_SERVER['PHP_AUTH_USER'])) {
            return (string)$_SERVER['PHP_AUTH_USER'];
        }
        return 'noid';
    }

    protected function getCurrentModuleVersion() {
        $modinfo = \FreePBX::Modules()->getInfo('dpviz');
        return isset($modinfo['dpviz']['version']) ? (string)$modinfo['dpviz']['version'] : '0.0.0';
    }

    protected function getUserSettingsOverrides($username = null) {
        $username = $username ?: $this->getCurrentUsername();
        $settings = $this->getConfig('user_settings', $username);
        return is_array($settings) ? $settings : array();
    }

    protected function saveUserSettingsOverrides(array $settings, $username = null) {
        $username = $username ?: $this->getCurrentUsername();
        return $this->setConfig('user_settings', $settings, $username);
    }

    protected function valuesEquivalent($left, $right) {
        $left = ($left === null) ? '' : (string)$left;
        $right = ($right === null) ? '' : (string)$right;
        return $left === $right;
    }

    protected function getOverrideableSettingsColumns() {
        return array_values(array_diff($this->getSettingsColumns(), array('id', 'hidewhatsnew')));
    }

    protected function saveCurrentUserOverrides(array $newValues) {
        $global = $this->fetchSettingsRow();
        if (!$global && $this->restoreSettingsRowFromKv()) {
            $global = $this->fetchSettingsRow();
        }
        if (!is_array($global)) {
            $global = array();
        }

        $allowed = array_flip($this->getOverrideableSettingsColumns());
        $overrides = $this->getUserSettingsOverrides();

        foreach ($newValues as $key => $value) {
            if (!isset($allowed[$key])) {
                continue;
            }

            $globalValue = array_key_exists($key, $global) ? $global[$key] : null;
            if ($this->valuesEquivalent($value, $globalValue)) {
                unset($overrides[$key]);
            } else {
                $overrides[$key] = $value;
            }
        }

        return $this->saveUserSettingsOverrides($overrides);
    }

    protected function getCurrentUserWhatsNewHiddenVersion() {
        $username = $this->getCurrentUsername();
        $hiddenVersion = $this->getConfig('whatsnew_hidden_version', $username);
        return is_string($hiddenVersion) ? trim($hiddenVersion) : '';
    }

    protected function setCurrentUserWhatsNewHiddenVersion($version) {
        $username = $this->getCurrentUsername();
        return $this->setConfig('whatsnew_hidden_version', (string)$version, $username);
    }

    protected function fetchSettingsRow() {
        $sql = "SELECT * FROM dpviz LIMIT 1";
        $sth = $this->db->prepare($sql);
        $sth->execute();
        return $sth->fetch(\PDO::FETCH_ASSOC);
    }

    protected function getSettingsColumns() {
        $columns = array();
        $sth = $this->db->query("DESCRIBE dpviz");
        while ($row = $sth->fetch(\PDO::FETCH_ASSOC)) {
            if (!empty($row['Field'])) {
                $columns[] = $row['Field'];
            }
        }
        return $columns;
    }

    public function syncSettingsRowToKv() {
        $settings = $this->fetchSettingsRow();
        if (!is_array($settings) || empty($settings)) {
            return false;
        }

        $this->setConfig('settings_row', $settings);
        return true;
    }

    public function restoreSettingsRowFromKv() {
        $settings = $this->getConfig('settings_row');
        if (!is_array($settings) || empty($settings)) {
            return false;
        }

        $columns = $this->getSettingsColumns();
        $updates = array();
        $params = array();

        foreach ($columns as $column) {
            if ($column === 'id' || !array_key_exists($column, $settings)) {
                continue;
            }

            $updates[] = '`' . $column . '` = :' . $column;
            $params[':' . $column] = $settings[$column];
        }

        if (empty($updates)) {
            return false;
        }

        $sql = "UPDATE dpviz SET " . implode(', ', $updates) . " WHERE id = 1";
        $sth = $this->db->prepare($sql);
        return $sth->execute($params);
    }

    protected function sendAction($action) {
        return $this->sendCurlPost("action.php", array('action' => $action));
    }

    public function install() {
        return $this->sendAction('install');
    }

    public function uninstall() {
        return $this->sendAction('uninstall');
    }
		
    public function getOptions() {
        $row = $this->fetchSettingsRow();
        if (!$row && $this->restoreSettingsRowFromKv()) {
            $row = $this->fetchSettingsRow();
        }
        if (!is_array($row)) {
            $row = array();
        }

        $overrides = $this->getUserSettingsOverrides();
        foreach ($overrides as $key => $value) {
            $row[$key] = $value;
        }

        $hiddenVersion = trim((string)$this->getCurrentUserWhatsNewHiddenVersion());
        $row['debug_current_username'] = $this->getCurrentUsername();
        $row['whatsnew_hidden_version'] = $hiddenVersion;
        $row['hidewhatsnew'] = ($hiddenVersion !== '') ? 1 : 0;

        return $row;
    }

    public function editDpviz($panzoom, $horizontal, $datetime,$dynmembers, $combineQueueRing,
															$extOptional, $fmfm, $minimal, $queue_member_display, 
															$ring_member_display, $queue_penalty, $allowlist, $blacklist, $autoplay, 
															$displaydestinations, $inuseby, $insertnode, $exportprefix)
		{
        $sql = "UPDATE dpviz SET
            `panzoom` = :panzoom,
            `horizontal` = :horizontal,
            `datetime` = :datetime,
            `dynmembers` = :dynmembers,
            `combineQueueRing` = :combineQueueRing,
            `extOptional` = :extOptional,
            `fmfm` = :fmfm,
						`minimal` = :minimal,
						`queue_member_display` = :queue_member_display,
						`ring_member_display` = :ring_member_display,
						`queue_penalty` = :queue_penalty,
						`allowlist` = :allowlist,
						`blacklist` = :blacklist,
						`autoplay` = :autoplay,
						`displaydestinations` = :displaydestinations,
						`inuseby` = :inuseby,
						`insertnode` = :insertnode,
						`exportprefix` = :exportprefix
						
            WHERE `id` = 1";

        $insert = array(
            ':panzoom' => $panzoom,
            ':horizontal' => $horizontal,
            ':datetime' => $datetime,
            ':dynmembers' => $dynmembers,
            ':combineQueueRing' => $combineQueueRing,
            ':extOptional' => $extOptional,
            ':fmfm' => $fmfm,
						':minimal' => $minimal,
						':queue_member_display' => $queue_member_display,
						':ring_member_display' => $ring_member_display,
						':queue_penalty' => $queue_penalty,
						':allowlist' => $allowlist,
						':blacklist' => $blacklist,
						':autoplay' => $autoplay,
						':displaydestinations' => $displaydestinations,
						':inuseby' => $inuseby,
						':insertnode' => $insertnode,
						':exportprefix' => $exportprefix
        );

        $stmt = $this->db->prepare($sql);
        $success = $stmt->execute($insert);
        if ($success) {
            $this->syncSettingsRowToKv();
        }
        return $success;
    }

    public function doConfigPageInit($page) {
        $request = $_REQUEST;
        $action = isset($request['action']) ? $request['action'] : '';
        $panzoom = isset($request['panzoom']) ? $request['panzoom'] : '';
        $horizontal = isset($request['horizontal']) ? $request['horizontal'] : '';
        $datetime = isset($request['datetime']) ? $request['datetime'] : '';
        $dynmembers = isset($request['dynmembers']) ? $request['dynmembers'] : '';
        $combineQueueRing = isset($request['combineQueueRing']) ? $request['combineQueueRing'] : '';
        $extOptional = isset($request['extOptional']) ? $request['extOptional'] : '';
        $fmfm = isset($request['fmfm']) ? $request['fmfm'] : '';
				$minimal = isset($request['minimal']) ? $request['minimal'] : '';
				$queue_member_display = isset($request['queue_member_display']) ? $request['queue_member_display'] : '';
				$ring_member_display = isset($request['ring_member_display']) ? $request['ring_member_display'] : '';
				$queue_penalty = isset($request['queue_penalty']) ? $request['queue_penalty'] : '';
				$allowlist = isset($request['allowlist']) ? $request['allowlist'] : '';
				$blacklist = isset($request['blacklist']) ? $request['blacklist'] : '';
				$autoplay = isset($request['autoplay']) ? $request['autoplay'] : '';
				$displaydestinations = isset($request['displaydestinations']) ? $request['displaydestinations'] : '';
				$inuseby = isset($request['inuseby']) ? $request['inuseby'] : '';
				$insertnode = isset($request['insertnode']) ? $request['insertnode'] : '';
				$exportprefix = isset($request['exportprefix']) ? trim($request['exportprefix']) : '';

        switch ($action) {
            case 'edit':
                $this->saveCurrentUserOverrides(array(
                    'panzoom' => $panzoom,
                    'horizontal' => $horizontal,
                    'datetime' => $datetime,
                    'dynmembers' => $dynmembers,
                    'combineQueueRing' => $combineQueueRing,
                    'extOptional' => $extOptional,
                    'fmfm' => $fmfm,
                    'minimal' => $minimal,
                    'queue_member_display' => $queue_member_display,
                    'ring_member_display' => $ring_member_display,
                    'queue_penalty' => $queue_penalty,
                    'allowlist' => $allowlist,
                    'blacklist' => $blacklist,
                    'autoplay' => $autoplay,
                    'displaydestinations' => $displaydestinations,
                    'inuseby' => $inuseby,
                    'insertnode' => $insertnode,
                    'exportprefix' => $exportprefix
                ));
                break;
            default:
                break;
        }
				

				//error_log(print_r($user,true));
    }

    public function ajaxRequest($req, &$setting) {
        switch ($req) {
            case 'save_options':
            case 'save_whatsnew':
            case 'check_update':
            case 'make':
            case 'getrecording':
            case 'getfile':
						case 'getvoicemail':
						case 'saveview':
						case 'deleteview':
						case 'feedback':
						case 'nodestselect':
						case 'save_nodest':
						case 'create_destination':
						case 'add_ivr_entry':
						case 'add_dyn_entry':
						case 'list_timegroups':
						case 'list_calendars':
						case 'list_calendargroups':
						case 'list_languages':
						case 'list_music':
						case 'list_recordings':
						case 'set_simtime':
						case 'need_reload_status':
						case 'get_sections':
                return true;
        }
        return false;
    }

    public function ajaxHandler() {
        $action = isset($_REQUEST['command']) ? $_REQUEST['command'] : '';

        // Every command that changes state is origin-checked here, in one
        // place, so a new endpoint cannot quietly skip the gate.
        $stateChanging = array(
            'save_options', 'save_whatsnew', 'saveview', 'deleteview',
            'save_nodest', 'create_destination', 'add_ivr_entry',
            'add_dyn_entry', 'set_simtime'
        );
        if (in_array($action, $stateChanging, true)) {
            $this->requireSameOrigin();
        }

        switch ($action) {
            case 'save_options':
                $panzoom = isset($_POST['panzoom']) ? $_POST['panzoom'] : '';
                $horizontal = isset($_POST['horizontal']) ? $_POST['horizontal'] : '';
                $datetime = isset($_POST['datetime']) ? $_POST['datetime'] : '';
                $dynmembers = isset($_POST['dynmembers']) ? $_POST['dynmembers'] : '';
                $combineQueueRing = isset($_POST['combineQueueRing']) ? $_POST['combineQueueRing'] : '';
                $extOptional = isset($_POST['extOptional']) ? $_POST['extOptional'] : '';
                $fmfm = isset($_POST['fmfm']) ? $_POST['fmfm'] : '';
								$minimal= isset($_POST['minimal']) ? $_POST['minimal'] : '';
								$queue_member_display= isset($_POST['queue_member_display']) ? $_POST['queue_member_display'] : '';
								$ring_member_display= isset($_POST['ring_member_display']) ? $_POST['ring_member_display'] : '';
								$queue_penalty= isset($_POST['queue_penalty']) ? $_POST['queue_penalty'] : '';
								$allowlist = isset($_POST['allowlist']) ? $_POST['allowlist'] : '';
								$blacklist = isset($_POST['blacklist']) ? $_POST['blacklist'] : '';
								$autoplay = isset($_POST['autoplay']) ? $_POST['autoplay'] : '';
								$displaydestinations = isset($_POST['displaydestinations']) ? $_POST['displaydestinations'] : '';
								$inuseby = isset($_POST['inuseby']) ? $_POST['inuseby'] : '';
								$insertnode = isset($_POST['insertnode']) ? $_POST['insertnode'] : '';
								$exportprefix = isset($_POST['exportprefix']) ? trim($_POST['exportprefix']) : '';

                $success = $this->saveCurrentUserOverrides(array(
                                                                                        'panzoom' => $panzoom,
                                                                                        'horizontal' => $horizontal,
                                                                                        'datetime' => $datetime,
                                                                                        'dynmembers' => $dynmembers,
                                                                                        'combineQueueRing' => $combineQueueRing,
                                                                                        'extOptional' => $extOptional,
                                                                                        'fmfm' => $fmfm,
                                                                                        'minimal' => $minimal,
                                                                                        'queue_member_display' => $queue_member_display,
                                                                                        'ring_member_display' => $ring_member_display,
                                                                                        'queue_penalty' => $queue_penalty,
                                                                                        'allowlist' => $allowlist,
                                                                                        'blacklist' => $blacklist,
                                                                                        'autoplay' => $autoplay,
                                                                                        'displaydestinations' => $displaydestinations,
                                                                                        'inuseby' => $inuseby,
                                                                                        'insertnode' => $insertnode,
                                                                                        'exportprefix' => $exportprefix
                                ));
                echo json_encode(array('success' => $success));
                exit;

            case 'save_whatsnew':
                $hidewhatsnew = (isset($_POST['hidewhatsnew']) && $_POST['hidewhatsnew'] == '1') ? 1 : 0;

                try {
                    $success = $hidewhatsnew
                        ? $this->setCurrentUserWhatsNewHiddenVersion($this->getCurrentModuleVersion())
                        : $this->setCurrentUserWhatsNewHiddenVersion('');

                    echo json_encode(array(
                        'status' => 'success',
                        'saved' => $success,
                        'hidewhatsnew' => $hidewhatsnew
                    ));
                } catch (\PDOException $e) {
                    echo json_encode(array(
                        'status' => 'error',
                        'message' => $e->getMessage()
                    ));
                }
                exit;

            case 'check_update':
                $result = $this->checkForGitHubUpdate();
                if (isset($result['error'])) {
                    echo json_encode(array('status' => 'error', 'message' => $result['error']));
                } else {
                    echo json_encode(array(
                        'status' => 'success',
                        'current' => $result['current'],
                        'latest' => $result['latest'],
                        'up_to_date' => $result['up_to_date']
                    ));
                }
                exit;

            case 'make':
								$fpbx = \FreePBX::create();

								if (isset($fpbx->View) && method_exists($fpbx->View, 'setAdminLocales')) {
										$fpbx->View->setAdminLocales();
										\bindtextdomain("dpviz", __DIR__ . "/i18n");
										\textdomain("dpviz");
								} else {
										// fallback or do nothing
								}
                
                include 'process.php';
                echo json_encode(array(
                    'vizHeader' => $header,
                    'gtext' => json_decode($gtext)
                ));
                exit;

            case 'getrecording':
								$mod = isset($_POST['app']) ? $_POST['app'] : '';
                $id = isset($_POST['id']) ? $_POST['id'] : 0;
								$lang = isset($_POST['lang']) ? $_POST['lang'] : '';
                                $desc = '';
                                $recId = 0;
                                $displayname = '';
                                $audiolist = '';

								if ($mod=='systemrecording'){
										$desc = '';
										$recId = $id;
										
								} elseif ($mod=='announcement'){
									$annResults= \FreePBX::Announcement()->getAnnouncements();
									foreach ($annResults as $a=>$aa){
										if ($aa['announcement_id']==$id){
											$desc = $aa['description'];
											$recId = $aa['recording_id'];
											break;											
										}
									}
									
								} elseif ($mod=='ivr'){
									$ivrResults= \FreePBX::Ivr()->getDetails($id);
									$desc = $ivrResults['name'];
									$recId = $ivrResults['announcement'];
									
								} elseif ($mod=='queues'){
									$sql = "SELECT * FROM queues_config WHERE extension = ?";
									$sth = $this->db->prepare($sql);
									$sth->execute(array($id));
									$qResults = $sth->fetch(\PDO::FETCH_ASSOC);
									$desc = $qResults['descr'];
									if (isset($qResults['joinannounce_id']) && $qResults['joinannounce_id'] !==''){
										$recId = $qResults['joinannounce_id'];
									}else{
										$recId=0;
									}
									
								} elseif ($mod=='ringgroup'){
									$sql = "SELECT * FROM ringgroups WHERE grpnum = ?";
									$sth = $this->db->prepare($sql);
									$sth->execute(array($id));
									$rgResults = $sth->fetch(\PDO::FETCH_ASSOC);
									$desc = $rgResults['description'];
									if (isset($rgResults['annmsg_id']) && $rgResults['annmsg_id'] !==''){
										$recId = $rgResults['annmsg_id'];
									}else{
										$recId=0;
									}
									
								} elseif ($mod=='vmblast'){
									$sql = "SELECT * FROM vmblast WHERE grpnum = ?";
									$sth = $this->db->prepare($sql);
									$sth->execute(array($id));
									$vmblastResults = $sth->fetch(\PDO::FETCH_ASSOC);
									$desc = $vmblastResults['description'];
									if (isset($vmblastResults['audio_label']) && $vmblastResults['audio_label'] !==''){
										$recId = $vmblastResults['audio_label'];
									}else{
										$recId=0;
									}
									
								} elseif ($mod=='pagegroups'){
									$sql = "SELECT * FROM paging_config WHERE page_group = ?";
									$sth = $this->db->prepare($sql);
									$sth->execute(array($id));
									$vmblastResults = $sth->fetch(\PDO::FETCH_ASSOC);
									$desc = $vmblastResults['description'];
									if (isset($vmblastResults['announcement']) && $vmblastResults['announcement'] !==''){
										$recId = $vmblastResults['announcement'];
									}else{
										$recId=0;
									}
									
								} elseif ($mod=='dynroute'){
									$sql = "SELECT * FROM dynroute WHERE id = ?";
									$sth = $this->db->prepare($sql);
									$sth->execute(array($id));
									$dynResults = $sth->fetch(\PDO::FETCH_ASSOC);
									$desc = $dynResults['name'];
									if (isset($dynResults['announcement_id']) && $dynResults['announcement_id'] !==''){
										$recId = $dynResults['announcement_id'];
									}else{
										$recId=0;
									}
									
								} elseif ($mod=='queuecallback'){
									$sql = "SELECT * FROM vqplus_callback_config WHERE id = ?";
									$sth = $this->db->prepare($sql);
									$sth->execute(array($id));
									$qcbResults = $sth->fetch(\PDO::FETCH_ASSOC);
									$desc = $qcbResults['name'];
									if (!empty($qcbResults['announcement'])){
										$recId = $qcbResults['announcement'];
									}else{
										$recId=0;
									}
									
								} elseif ($mod=='voicemail'){
										$desc='voicemail';
										
										if (preg_match('/vm([a-z])(\d+)/', $id, $matches)) {
											$type = $matches[1]; // "u"
											$ext = $matches[2]; // "210"
											$vm = \FreePBX::Voicemail();
											$vmResults = $vm->getGreetingsByExtension($ext);
											$typeMap = array(
													'u' => 'unavail',
													'b' => 'busy',
											);
											$audiolist='';
											/*  TODO all VM greetings??
											foreach ($vmResults as $type=>$file){
												$audiolist.=$file.'&';
											}
											$audiolist = rtrim($audiolist, '&');
											*/
											$greetKey = isset($typeMap[$type]) ? $typeMap[$type] : null;
											$audiolist = isset($vmResults[$greetKey]) ? $vmResults[$greetKey] : null;
											
											$recId = 'voicemail';
											$displayname= _('Ext').' '.$ext;
											
										}
								}

								if (is_numeric($recId) && $recId > 0){
									
									$fpbxResults= \FreePBX::Recordings()->getRecordingById($recId);
									if (!empty($fpbxResults)){
										//getrecording
										if (isset($fpbxResults) && !empty($fpbxResults['playbacklist'])){
											$audiolist='';
											foreach ($fpbxResults['playbacklist'] as $f){
												if (!empty($fpbxResults['soundlist'][$f]['filenames'][$lang])){
													$audiolist.='/var/lib/asterisk/sounds/'.$lang.'/'.$f.'&';
												}
											}
											$audiolist = rtrim($audiolist, '&');
											$displayname = $fpbxResults['displayname'];
										}
										
									}else{
										$recId = 0;
										$displayname = '';
										$audiolist = '';
									}
								}elseif ($recId==='voicemail'){
									
									
								}else{
									$displayname = '';
									$audiolist = '';
								}
								
                header('Content-Type: application/json');
                echo json_encode(array(
										'modDescription' => $desc,
										'recId' => $recId,
                    'displayname' => $displayname,
                    'filename' => $audiolist
										
                ));
                exit;

            case 'getfile':
                if (isset($_POST['file'])){
									$filename= $_POST['file'];
									if (substr($filename, -4) !== ".wav") {
										$filename .= ".wav";
									}

									// Confine the read to the two directories this endpoint is
									// meant to serve. realpath() collapses ../ and resolves
									// symlinks first, so the comparison is against the true
									// on-disk path, not the string the browser sent. Without
									// this, any .wav anywhere on the box was readable.
									$filename = $this->resolveAudioPath($filename);

									if ($filename !== false && is_readable($filename)) {
											$xFilename = str_replace(
													array("/var/lib/asterisk/sounds/", "/var/spool/asterisk/voicemail/"),
													"",
													$filename
											);
											header('Content-Type: audio/wav');
											header('Content-Length: ' . filesize($filename));
											header('Content-Disposition: inline; filename="' . basename($xFilename) . '"');
											header('X-Filename: ' . "$xFilename");
											readfile($filename);
											exit;
									} else {
											http_response_code(404);
											echo "File not found.";
											exit;
									}
								}

                exit;

						case 'saveview':
								// Saved views are shared by every admin, so writing one is
								// only for admins who actually hold the dpviz section
								$this->requireSection('dpviz');

								try {
										$description = isset($_POST['description']) ? trim($_POST['description']) : '';
										$ext         = isset($_POST['ext']) ? trim($_POST['ext']) : '';
										$jump        = isset($_POST['jump']) ? trim($_POST['jump']) : '';
										$viewId      = isset($_POST['id']) ? (int)$_POST['id'] : 0;
										$skip        = '';

										// Decode 'skip' JSON array if present and sanitize each value
										if (!empty($_POST['skip'])) {
												$decoded = json_decode($_POST['skip'], true);
												if (is_array($decoded)) {
														$skipArray = array_map(function($item) {
																return trim($item); // remove whitespace
														}, $decoded);
														$skip = implode(';', $skipArray);
												}
										}

										$params = array(
												':description' => $description,
												':ext'         => $ext,
												':jump'        => $jump,
												':skip'        => $skip
										);

										if ($viewId > 0) {
												$sql = "UPDATE dpviz_views 
																SET description = :description, ext = :ext, jump = :jump, skip = :skip
																WHERE id = :id";
												$params[':id'] = $viewId;
										} else {
												$sql = "INSERT INTO dpviz_views (description, ext, jump, skip)
																VALUES (:description, :ext, :jump, :skip)";
										}

										$stmt = $this->db->prepare($sql);
										$stmt->execute($params);

										echo json_encode(array(
												'status' => 'success',
												'message' => 'Saved successfully.'
										));

								} catch (\PDOException $e) {
										
										error_log($e->getMessage());

										// Generic error message to client
										echo json_encode(array(
												'status' => 'error',
												'message' => 'Database error.'
										));
								}


                exit;
								
						case 'deleteview':
								$this->requireSection('dpviz');

								try {
										if (isset($_POST['id']) && $_POST['id'] !== '') {
												$viewId = $_POST['id'];
												$stmt = $this->db->prepare("DELETE FROM dpviz_views WHERE id = :id");
												$stmt->execute(array(':id' => $viewId));

												echo json_encode(array('status' => 'success', 'message' => 'View deleted successfully.'));
										} else {
												echo json_encode(array('status' => 'error', 'message' => 'Missing or empty ID.'));
										}
								} catch (\PDOException $e) {
										echo json_encode(array('status' => 'error', 'message' => $e->getMessage()));
								}

									exit;

						case 'feedback':
								$message = isset($_POST['message']) ? $_POST['message'] : '';
								$email   = isset($_POST['email']) ? $_POST['email'] : '';
								$lang   = isset($_POST['lang']) ? $_POST['lang'] : '';
								
								if (trim($message) === '') {
										echo json_encode(array('status' => 'error', 'message' => 'Message is required'));
										exit;
								}

								$postFields = array(
										'message' => $message,
										'email' => $email,
										'lang' => $lang
								);

								$data = $this->sendCurlPost("feedback.php", $postFields);
								
								header('Content-Type: application/json');

								if (isset($data['status']) && $data['status'] === 'ok') {
										echo json_encode(array('status' => 'ok'));
								} else {
										$errorMsg = isset($data['message']) ? $data['message'] : 'External service failed';
										echo json_encode(array('status' => 'error', 'message' => $errorMsg));
								}
								exit;

						case 'nodestselect':
								$freepbx = \FreePBX::create();
								$vm = $freepbx->Modules->loadFunctionsInc('voicemail');
								$destinations = $this->safeGetDestinations();
								
								$grouped = [];

								foreach ($destinations as $key => $d) {
										if (!is_array($d) || empty($d['destination'])) continue;

										// Use "name" (Announcements, Callback, Calendar, etc.) as module label
										$modName = $d['name'] ?: ucfirst($d['module']);
										// Special handling for Core
										if ($d['module'] === 'core') {
											$modName = $d['category'];
										}
				
										$grouped[$modName][] = [
												'value' => $d['destination'],
												'label' => $d['description']
										];
										ksort($grouped);
								}

								header('Content-Type: application/json');
								echo json_encode($grouped);
								exit;

						case 'save_nodest':
						
								header('Content-Type: application/json');

								$raw = file_get_contents('php://input');
								$payload = json_decode($raw, true);

//error_log(print_r($payload,true));

								$titleText   = isset($payload['titleText']) ? trim($payload['titleText']) : '';
								$destination = isset($payload['destination']) ? trim($payload['destination']) : '';

								if ($titleText === '' || $destination === '') {
										echo json_encode(['status' => 'error', 'message' => 'Missing titleText or destination']); exit;
								}

								// Expect "noDest<context>,<exten>,<priority>,<lang>"
								// e.g. "noDestfrom-trunk,8884443377,1,en"
								if (strpos($titleText, 'noDest') !== 0 && strpos($titleText, 'insertDest') !== 0 ) {
										echo json_encode(['status' => 'error', 'message' => 'Invalid titleText format']); exit;
								}

								if (strpos($titleText, 'noDest') === 0) {
										$rest = substr($titleText, 6);  // remove "noDest"
								} elseif (strpos($titleText, 'insertDest') === 0) {
										$rest = substr($titleText, 10); // remove "insertDest"
								} else {
										$rest = $titleText; // fallback
								}
								
								$parts = explode(',', $rest);
								if (count($parts) < 2) {
										echo json_encode(['status' => 'error', 'message' => 'Invalid title parts']); 
										exit;
								}

								$context = $parts[0];
								$id      = $parts[1];

								// Normalize context/id
								if (preg_match('/^app-announcement-(\d+)$/', $context, $m)) {
										$context = 'app-announcement';
										$id      = $m[1];
								}

								if (preg_match('/^(?:no|insert)Destdynroute-(\d+),.+,\d+,.+\-(\w+)$/', $titleText, $m)) {
										$id      = $m[1];
										$context = 'dynroute'.$m[2];
								}

								if (preg_match('/^sel-(\d+)&(\d+)/', $context, $m)) {
										$context = 'ivrentries';
										$id      = $m[1];
										$sel     = $m[2];
								}
								
								if (preg_match('/^sel-(\d+)&(i|t)/', $context, $m)) {
									$id = $m[1];
									$map = [
											'i' => 'ivrinvalid',
											't' => 'ivrtimeout'
									];

									if (isset($map[$m[2]])) {
											$context = $map[$m[2]];
									}
										
								}
								
								if (preg_match('/^(?:no|insert)Destivr-(\d+),.+,\d+,.+\-(\w+)$/', $titleText, $m)) {
										$id      = $m[1];
										$context = 'ivr'.$m[2];
								}

								if (preg_match('/^(?:no|insert)Destapp-daynight,(\d+),\d+,.+\-((?:day|night))/', $titleText, $m)) {
										$context = 'app-daynight';
										$id      = $m[1];
										$mode    = $m[2];
								}

								if (preg_match('/^(?:no|insert)Desttimeconditions,(\d+),\d+,.+\-((?:true|false)goto)/', $titleText, $m)) {
										$context = 'timeconditions'.$m[2];
										$id      = $m[1];
								}
								
								// Same pattern rule as the from-trunk branch in process.php: a DID
								// can carry several character classes, and commas inside them are
								// part of the pattern, not field separators.
								if (preg_match("/^(?:no|insert)Destfrom-trunk,((?:[^\[&,]|\[[^\]]*\])*)(&[^,]*)?,(\d+),(.+)/", $titleText, $m)) {
										$context = 'from-trunk';
										$id      = str_replace("ANY", "", $m[1]);;
										$cid     = str_replace("&", "", $m[2]);
								}
								
								if (!is_numeric($id) && $context!='from-trunk') {
										echo json_encode(['status' => 'error', 'message' => "Unsupported id: $id $context"]);
										exit;
								}

								// Map context -> table/column, plus the FreePBX section that
								// governs the object whose destination we are about to rewrite
								$map = [
										'from-trunk'                => ['table' => 'incoming',       'key_cols' => ['extension','cidnum'], 'dest_col' => 'destination',          'section' => 'did'],
										'app-announcement'          => ['table' => 'announcement',   'key_cols' => ['announcement_id'],    'dest_col' => 'post_dest',            'section' => 'announcement'],
										'app-daynight'              => ['table' => 'daynight',       'key_cols' => ['ext','dmode'],        'dest_col' => 'dest',                 'section' => 'daynight'],
										'app-languages'             => ['table' => 'languages',      'key_cols' => ['language_id'],        'dest_col' => 'dest',                 'section' => 'languages'],
										'app-setcid'                => ['table' => 'setcid',         'key_cols' => ['cid_id'],             'dest_col' => 'dest',                 'section' => 'setcid'],
										'dynrouteinvalid'           => ['table' => 'dynroute',       'key_cols' => ['id'],                 'dest_col' => 'invalid_dest',         'section' => 'dynroute'],
										'dynroutedefault'           => ['table' => 'dynroute',       'key_cols' => ['id'],                 'dest_col' => 'default_dest',         'section' => 'dynroute'],
										'ext-callrecording'         => ['table' => 'callrecording',  'key_cols' => ['callrecording_id'],   'dest_col' => 'dest',                 'section' => 'callrecording'],
										'ext-group'                 => ['table' => 'ringgroups',     'key_cols' => ['grpnum'],             'dest_col' => 'postdest',             'section' => 'ringgroups'],
										'ext-queues'                => ['table' => 'queues_config',  'key_cols' => ['extension'],          'dest_col' => 'dest',                 'section' => 'queues'],
										'ivrinvalid'                => ['table' => 'ivr_details',    'key_cols' => ['id'],                 'dest_col' => 'invalid_destination',  'section' => 'ivr'],
										'ivrtimeout'                => ['table' => 'ivr_details',    'key_cols' => ['id'],                 'dest_col' => 'timeout_destination',  'section' => 'ivr'],
										'ivrentries'                => ['table' => 'ivr_entries',    'key_cols' => ['ivr_id','selection'], 'dest_col' => 'dest',                 'section' => 'ivr'],
										'timeconditionstruegoto'    => ['table' => 'timeconditions', 'key_cols' => ['timeconditions_id'],  'dest_col' => 'truegoto',             'section' => 'timeconditions'],
										'timeconditionsfalsegoto'   => ['table' => 'timeconditions', 'key_cols' => ['timeconditions_id'],  'dest_col' => 'falsegoto',            'section' => 'timeconditions'],
										// add more mappings later...
								];

								if (!isset($map[$context])) {
										echo json_encode(['status' => 'error', 'message' => "Unsupported context: $context"]);
										exit;
								}

								// Enforce the ACL on the object being modified. New map
								// entries must carry a 'section' or they are refused.
								$this->requireSection(isset($map[$context]['section']) ? $map[$context]['section'] : '');

								$table   = $map[$context]['table'];
								$keyCols = $map[$context]['key_cols'];
								$destCol = $map[$context]['dest_col'];

								$where  = [];
								$params = [':dest' => $destination];

								foreach ($keyCols as $col) {
										$where[] = "`$col` = :$col";
										switch ($col) {
												case 'announcement_id':
												case 'callrecording_id':
												case 'dynroute':
												case 'extension':
												case 'ext':
												case 'cid_id':
												case 'ext-queues':
												case 'grpnum':
												case 'id':
												case 'ivr_id':
												case 'language_id':
												case 'timeconditions_id':
														$params[":$col"] = $id;
														break;
												case 'dmode':
														$params[":$col"] = $mode;
														break;
												case 'cidnum':
														$params[":$col"] = $cid;
														break;
												case 'selection':
														$params[":$col"] = $sel;
										}
								}

								$whereSql = implode(' AND ', $where);

								try {
										$sql = "UPDATE `$table` SET `$destCol` = :dest WHERE $whereSql LIMIT 1";
										$stmt = $this->db->prepare($sql);
										$stmt->execute($params);

										needreload();
										echo json_encode(['status' => 'success']);
								} catch (\Exception $e) {
										echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
								}
								exit;
						
						case 'create_destination':
								$input = json_decode(file_get_contents("php://input"), true);
//error_log(print_r($input,true));
								$module = '';
								$name   = '';

								if (isset($input['module'])) {
										$module = $input['module'];
								}
								if (isset($input['name'])) {
										$name = trim($input['name']);
								}
								if (isset($input['previous'])) {
										$destination = trim($input['previous']);
								}else{
									$destination='app-blackhole,zapateller,1';
								}
								
								if ($name === '') {
										echo json_encode(array('status' => 'error', 'message' => _('Name is required')));
										exit;
								}

								// Creating a destination means creating an object in another
								// module -- require that module's section, not just a dpviz login
								$this->requireSection($this->createDestinationSection($module));

								try {

										switch ($module) {
											
												case 'Announcements':
														// Check for duplicates
														if ($this->nameExists('announcement', 'description', $name)) {
																echo json_encode([
																		'status' => 'error',
																		'message' => 'Announcement Description Already Exist'
																]);
																exit;
														}
												
														$recId = !empty($input['recording_id']) ? $input['recording_id'] : 0;

														// Use the Announcements module's own API instead of a raw INSERT
														$id = \FreePBX::Announcement()->addAnnouncement($name, $recId, 0, $destination, 0, 0, '');
														
														// Build FreePBX destination string
														$value = "app-announcement-" . $id . ",s,1";
														$label = htmlspecialchars($name, ENT_QUOTES);

														needreload();
														echo json_encode(array(
																'status' => 'success',
																'value'  => $value,
																'label'  => $label
														));
														
														break;

												case 'Call Flow Control':
														$currentmode = isset($input['currentmode']) ? $input['currentmode'] : 'NIGHT';
														
														try {
																$db = \FreePBX::Database();
																$sql = "
																		SELECT CAST(ext AS UNSIGNED) AS id
																		FROM daynight
																		WHERE CAST(ext AS UNSIGNED) BETWEEN 1 AND 99
																		ORDER BY id ASC
																";
																$sth = $db->prepare($sql);
																$sth->execute();

																$ids = array();
																while ($row = $sth->fetch(\PDO::FETCH_ASSOC)) {
																		if (isset($row['id'])) {
																				$ids[] = (int)$row['id'];
																		}
																}

																$id = 0;
																for ($i = 1; $i < 100; $i++) {
																		if (!in_array($i, $ids, true)) {
																				$id = $i;
																				break;
																		}
																}

																if ($id === 0) {
																		throw new Exception('No available Call Flow Control IDs below 100.');
																}

																$dn = \FreePBX::Daynight();
																
																$vals = array(
																		'action'              => 'add',
																		'goto0'               => 'truegoto',
																		'truegoto0'           => 'app-blackhole,zapateller,1',  // default DAY destination
																		'goto1'               => 'falsegoto',
																		'falsegoto1'          => 'app-blackhole,zapateller,1',  // default NIGHT destination
																		'fc_description'      => $name,
																		'day_recording_id'    => '',
																		'night_recording_id'  => '',
																		'password'            => '',
																		'state'               => $currentmode
																);

																$dn->edit($vals, $id);

																$value = "app-daynight," . $id . ",1";
																$label = htmlspecialchars($name, ENT_QUOTES);

																needreload();
																echo json_encode(array(
																		'status' => 'success',
																		'value'  => $value,
																		'label'  => $label
																));
														} catch (\Exception $e) {
																echo json_encode(array(
																		'status'  => 'error',
																		'message' => 'Failed to create Call Flow Control: ' . $e->getMessage()
																));
														}
														break;
														
												case 'Call Recording':
														if ($this->nameExists('callrecording', 'description', $name)) {
																echo json_encode([
																		'status' => 'error',
																		'message' => 'Description name already exists'
																]);
																exit;
														}
														
														$recMode = isset($input['recordingmode']) ? $input['recordingmode'] : '';

														// Use the Call Recording module's own API instead of a raw INSERT
														$id = \FreePBX::Callrecording()->add($name, $recMode, $destination);

														if (empty($id)) {
																echo json_encode(array(
																		'status'  => 'error',
																		'message' => _('Could not create Call Recording.')
																));
																exit;
														}
														
														// Build FreePBX destination string
														$value = "ext-callrecording," . $id . ",1";
														$label = htmlspecialchars($name, ENT_QUOTES);

														needreload();
														echo json_encode(array(
																'status' => 'success',
																'value'  => $value,
																'label'  => $label
														));
														
														break;

												case 'Dynamic Routes':

														// Check for duplicates
														if ($this->nameExists('dynroute', 'name', $name)) {
																echo json_encode([
																		'status' => 'error',
																		'message' => 'Dynamic Route Description Already Exist'
																]);
																exit;
														}
														
														$freepbx = \FreePBX::create();
														$freepbx->Modules->loadFunctionsInc('dynroute');
												
														$id = dynroute_save_details([
																'id'										=> false,
																'name'                  => $name,
																'description'           => '',
																'sourcetype'            => 'none',
																'enable_substitutions'  => 'CHECKED',
																'mysql_host'            => '',
																'mysql_dbname'          => '',
																'mysql_query'           => '',
																'mysql_username'        => '',
																'mysql_password'        => '',
																'odbc_func'             => '',
																'odbc_query'            => '',
																'url_query'             => '',
																'agi_query'             => '',
																'agi_var_name_res'      => '',
																'astvar_query'          => '',
																'enable_dtmf_input'     => '',
																'max_digits'            => 0,
																'timeout'               => !empty($input['dyn_timeout']) ? $input['dyn_timeout'] : 5,
																'announcement_id'       => !empty($input['recording_id']) ? $input['recording_id'] : 0,
																'chan_var_name'         => '',
																'chan_var_name_res'     => '',
																'validation_regex'      => '',
																'max_retries'           => 0,
																'invalid_retry_rec_id'  => 0,
																'invalid_rec_id'        => 0,
																'invalid_dest'          => $destination,
																'default_dest'          => $destination
																
														]);
														
														if (is_array($input['dynEntries']) && !empty($input['dynEntries'])){
															$entries=array();
															foreach ($input['dynEntries'] as $d){
																$entry=array(
																	'dynroute_id' => $id,
																	'selection' => $d['digit'],
																	'dest' => $d['dest'],
																	'description' => ''
																);
																$entries[]=$entry;
															}
															
															$dynEntries = \FreePBX::Dynroute();
															$dynEntries->saveEntry($id,$entries);
														}
														
														// Build FreePBX destination string
														$value = "dynroute-" . $id . ",s,1";
														$label = htmlspecialchars($name, ENT_QUOTES);

														needreload();
														echo json_encode(array(
																'status' => 'success',
																'value'  => $value,
																'label'  => $label
														));
														
														break;
														
												case 'Inbound Routes':
														if (isset($input['did']) && isset($input['cidnum'])) {
															$invalidDIDChars = array('<', '>');
															$did = trim(str_replace($invalidDIDChars, "", $input['did']));
															$cid = trim(str_replace($invalidDIDChars, "", $input['cidnum']));
														}
														
														$inboundRoute = \FreePBX::Core();
														$didSettings = array(
															'description' => $name,
															'extension'   => $did,
															'cidnum'      => !empty($cid) ? $cid : '',
															'destination' => $destination,
															'mohclass'    => isset($input['music']) ? $input['music'] : '',
															'grppre'      => isset($input['grppre']) ? $input['grppre'] : '',
															'privacyman'  => 0,
															'pmmaxretries'=> '',
															'pmminlength' => '',
															'alertinfo'   => '',
															'ringing'     => '',
															'fanswer'     => '',
															'reversal'    => '',
															'rvolume'     => '',
															'delay_answer'=> '0',
															'pricid'      => '',
														);

														if (method_exists($inboundRoute, 'addDIDDefaults')) {
															$inboundRoute->addDIDDefaults($didSettings);
														}

														$result = $inboundRoute->addDID($didSettings);

														if ($result) {
															header('Content-Type: application/json');
															if (!empty($cid)){
																$value = 'from-trunk,' . $did . '&' . $cid . ',1';
															}else{
																$value = 'from-trunk,' . $did . ',1';
															}
															$label = htmlspecialchars($name, ENT_QUOTES);

															needreload();
															echo json_encode(array(
																'status' => 'success',
																'value'  => $value,
																'label'  => $label
															));
														} else {
															echo json_encode(array(
																'status'  => 'error',
																'message' => _('Inbound Route exists.')
															));
														}
														break;

												case 'IVR':
														if ($this->nameExists('ivr_details', 'name', $name)) {
																echo json_encode([
																		'status' => 'error',
																		'message' => 'IVRs name already exist'
																]);
																exit;
														}
														$recId = !empty($input['recording_id']) ? $input['recording_id'] : 0;

														$ivrData = array(
																'id'                      => '',
																'name'                    => $name,
																'description'             => '',
																'announcement'            => (int)$recId,
																'directdial'              => 'Disabled',
																'invalid_loops'           => 3,
																'invalid_retry_recording' => 'default',
																'invalid_destination'     => 'app-blackhole,zapateller,1',
																'timeout_enabled'         => null,
																'invalid_recording'       => 'default',
																'retvm'                   => '',
																'timeout_time'            => $input['timeout_time'],
																'timeout_recording'       => 'default',
																'timeout_retry_recording' => 'default',
																'timeout_destination'     => 'app-blackhole,zapateller,1',
																'timeout_loops'           => 3,
																'timeout_append_announce' => 0,
																'invalid_append_announce' => 0,
																'timeout_ivr_ret'         => 0,
																'invalid_ivr_ret'         => 0,
																'alertinfo'               => '',
																'rvolume'                 => 0
														);

														if (dpviz_ivr_details_has_column('strict_dial_timeout')) {
																$ivrData['strict_dial_timeout'] = 2;
														}

														// Use the IVR module's own API instead of raw INSERTs
														$ivr = \FreePBX::Ivr();
														$id  = $ivr->saveDetails($ivrData);

														if (!empty($input['ivrEntries']) && is_array($input['ivrEntries'])) {
																$ivrEntries = array();
																foreach ($input['ivrEntries'] as $e) {
																		$ivrEntries[] = array(
																				'ivr_id'    => $id,
																				'selection' => $e['digit'],
																				'dest'      => $e['dest'],
																				'ivr_ret'   => 0
																		);
																}
																if (!empty($ivrEntries)) {
																		$ivr->saveEntry($id, $ivrEntries);
																}
														}
														
														// Build FreePBX destination string
														$value = "ivr-" . $id . ",s,1";
														$label = htmlspecialchars($name, ENT_QUOTES);

														needreload();
														echo json_encode(array(
																'status' => 'success',
																'value'  => $value,
																'label'  => $label
														));
												
														break;
														
												case 'Languages':
														if ($this->nameExists('languages', 'description', $name)) {
																echo json_encode([
																		'status' => 'error',
																		'message' => $name . ' already used, please use a different description.'
																]);
																exit;
														}
														
														$lang = \FreePBX::Languages();
														$id = $lang->addLanguage($name, $input['lang_code'], $destination);
														
														// Build FreePBX destination string
														$value = "app-languages," . $id . ",1";
														$label = htmlspecialchars($name, ENT_QUOTES);

														needreload();
														echo json_encode(array(
																'status' => 'success',
																'value'  => $value,
																'label'  => $label
														));
												
														break;
														
												case 'Misc Destinations':
														if ($this->nameExists('miscdests', 'description', $name)) {
																echo json_encode([
																		'status' => 'error',
																		'message' => 'Misc Destinations name already exist'
																]);
																exit;
														}
														
														$md = \FreePBX::Miscdests();
														$id = $md->add($name,$input['destdial']);
														
														// Build FreePBX destination string
														$value = "ext-miscdests," . $id . ",1";
														$label = htmlspecialchars($name, ENT_QUOTES);

														needreload();
														echo json_encode(array(
																'status' => 'success',
																'value'  => $value,
																'label'  => $label
														));
												
														break;
														
												case 'Queues':
														if ($this->nameExists('queues_config', 'descr', $name)) {
																echo json_encode([
																		'status' => 'error',
																		'message' => 'QUEUEs name already exist'
																]);
																exit;
														}
														
														$qnum= $input['extension'];
														if ($this->queueExists($qnum)) {
																header('Content-Type: application/json');
																echo json_encode([
																		'status'  => 'error',
																		'message' => "Queue {$qnum} already exists."
																]);
																exit;
														}
														
														
														//$freepbx = \FreePBX::create();
														//$freepbx->Modules->loadFunctionsInc('queues');
														global $amp_conf;
														$base=$_SERVER['SCRIPT_NAME'];
														$base = preg_replace('#/[^/]+$#', '', $base);
														require_once($amp_conf['AMPWEBROOT'] . $base . '/modules/queues/functions.inc/geters_seters.php');
														$strategy= $input['qstrategy'];
														$maxwait= $input['maxwait'];
														
														$items = array_map('trim', explode(',', $input['staticlist']));

														$filtered = array_filter($items, function($v) {
																// digits only
																return preg_match('/^[0-9]+$/', $v);
														});

														// remove duplicates
														$unique = array_unique($filtered);

														$staticAgents = array_map(function($ext) {
																return "Local/{$ext}@from-queue/n,0";
														}, $unique);

														
														$dynamicAgentsRaw = array_filter(array_map('trim', explode(',', $input['dynlist'])));

														$dynamicAgents = array_map(function($ext) {
																return "{$ext},0";
														}, $dynamicAgentsRaw);
														
														
														$_REQUEST = array_merge($_REQUEST, [
															'strategy'           => $strategy,
															'music'              => 'inherit',
															'timeout'            => '15',
															'retry'              => '5',
															'joinempty'          => 'yes',
															'leavewhenempty'     => 'no',
															'announceposition'   => 'no',
															'announceholdtime'   => 'no',
															'recording'          => 'dontcare',
															'answered_elsewhere' => '0',
															'maxlen'             => '0',
															'wrapuptime'         => '0',
															'announcefreq'       => '0',
															'min-announce'       => '15',
															'pannouncefreq'      => '0'
															
														]);

														try {
															$result=queues_add(
																	$qnum, //account
																	$name, //name
																	'', //password
																	'', //prefix
																	$destination,  //goto
																	'', //agentannounce_id
																	$staticAgents, //members
																	'', //joinannounce_id
																	$maxwait, //maxwait
																	'', //alertinfo
																	'0', //cwignore
																	'', //qregex
																	'0', //queuewait
																	'0', //use_queue_context
																	$dynamicAgents, //dynmembers
																	'no', //dynmemberonly
																	'', //togglehint
																	'0', //qnoanswer
																	'', //callconfirm
																	'', //callconfirm_id
																	'', //monitor_type
																	'0', //monitor_heard
																	'0', //monitor_spoken
																	'0', //answered_elsewhere
																	'', //recording
																	'', //rvolume
																	''  //rvol_mode
															);
															
															if ($result) {
																$value = "ext-queues," . $qnum . ",1";
																$label = htmlspecialchars($name, ENT_QUOTES);

																needreload();
																echo json_encode(array(
																		'status' => 'success',
																		'value'  => $value,
																		'label'  => $label
																));
															}else{
																throw new Exception('queues_add() returned false');
															}
														} catch (\Exception $e) {
																echo json_encode([
																		'status'  => 'error',
																		'message' => 'Failed to create Queue: ' . $e->getMessage()
																]);
																exit;
														}
														break;
												case 'Ring Groups':
														$grpnum = $input['grpnum'];
														if ($this->ringgroupExists($grpnum)) {
																header('Content-Type: application/json');
																echo json_encode([
																		'status'  => 'error',
																		'message' => "Ring Group {$grpnum} already exists."
																]);
																exit;
														}
														
														$strategy = $input['rgstrategy'];
														$grptime= $input['grptime'];
														$items = array_map('trim', explode(',', $input['grplist']));

														$filtered = array_filter($items, function($v) {
																// allowed: digits or digits followed by #
																return preg_match('/^[0-9]+#?$/', $v);
														});

														$grplist = implode('-', array_unique($filtered));

														
														$rg = \FreePBX::Ringgroups();
																
														$result = $rg->add(
																$grpnum,
																$strategy,
																$grptime,
																$grplist, 
																$destination, //postdest
																$name, //desc
																'',  //grppre
																'0', //annmsg_id
																'',  //alertinfo
																'',  //needsconf
																'',  //remotealert_id
																'',  //toolate_id
																'',  //ringing
																'',  //cwignore
																'',  //cfignore
																'default',  //changecid
																'',  //fixedcid
																'',  //cpickup
																'dontcare',  //recording
																'yes', //progress
																'no',  //elsewhere
																''  //rvolume
														);
														
														if ($result) {
															$value = "ext-group," . $grpnum . ",1";
															$label = htmlspecialchars($name, ENT_QUOTES);

															needreload();
															echo json_encode(array(
																	'status' => 'success',
																	'value'  => $value,
																	'label'  => $label
															));
														}else{
															echo json_encode([
																	'status'  => 'error',
																	'message' => 'Failed to create Ring Group: ' . $e->getMessage()
															]);
															exit;
														}
														break;
												
												case 'Set CallerID':
														if ($this->nameExists('setcid', 'description', $name)) {
																echo json_encode([
																		'status' => 'error',
																		'message' => $name . ' already used, please use a different Description.'
																]);
																exit;
														}
														$setcid = \FreePBX::Setcid();

														$result = $setcid->update(
																null,
																$name,
																$input['calleridName'],
																$input['calleridNumber'],
																$destination
														);

														if ($result) {
																global $db;

																// safer way to get new row ID
																$id = $db->getOne("
																		SELECT cid_id 
																		FROM setcid 
																		WHERE description = ? 
																		ORDER BY cid_id DESC 
																		LIMIT 1
																", [$name]);

																if (!$id) {
																		throw new Exception("Unable to determine new SetCID ID");
																}

																$value = "app-setcid," . intval($id) . ",1";
																$label = htmlspecialchars($name, ENT_QUOTES);

																needreload();

																echo json_encode([
																		'status' => 'success',
																		'value'  => $value,
																		'label'  => $label
																]);
														} else {
																echo json_encode([
																		'status'  => 'error',
																		'message' => 'Failed to create Set Caller ID'
																]);
														}

														break;
												case 'Time Conditions':
														if ($this->nameExists('timeconditions', 'displayname', $name)) {
																echo json_encode([
																		'status' => 'error',
																		'message' => 'Please enter a valid Time Conditions Name'
																]);
																exit;
														}
														// require at least one of the three IDs
														if (
																empty($input['timegroup_id']) &&
																empty($input['calendar_id']) &&
																empty($input['calendar_group_id'])
														) {
																echo json_encode([
																		'status'  => 'error',
																		'message' => _('Either Time Group, Calendar, or Calendar Group must be set.')
																]);
																exit;
														}

														// Normalize variables
														$tgid  = !empty($input['timegroup_id'])       ? $input['timegroup_id']       : '';
														$calid = !empty($input['calendar_id'])        ? $input['calendar_id']        : '';
														$cgid  = !empty($input['calendar_group_id'])  ? $input['calendar_group_id']  : '';

														// Validate: must have exactly one logical side set
														if (
																($tgid === '' && $calid === '' && $cgid === '') ||        // none
																($tgid !== '' && ($calid !== '' || $cgid !== '')) ||      // mixed
																($calid !== '' && $cgid !== '')                          // both calendar variants
														) {
																echo json_encode([
																		'status'  => 'error',
																		'message' => _('Select either a Time Group or a single Calendar / Calendar Group (not both).')
																]);
																exit;
														}

														// Determine mode
														$tcMode = ($tgid !== '') ? 'time-group' : 'calendar-group';

														// Create the Time Condition entry
														$tc = \FreePBX::Timeconditions();
														$id = $tc->addTimeCondition([
																'displayname'      => $name,
																'time'             => $tgid,
																'timezone'         => 'default',
																'goto0'            => 'truegoto',
																'truegoto0'        => 'app-blackhole,zapateller,1',
																'goto1'            => 'falsegoto',
																'falsegoto1'       => 'app-blackhole,zapateller,1',
																'generate_hint'    => '1',
																'invert_hint'      => '0',
																'fcc_password'     => '',
																'deptname'         => null,
																'mode'             => $tcMode,
																'calendar-id'      => $calid,
																'calendar-group'   => $cgid
														]);

														// Build return payload
														$value = "timeconditions,{$id},1";
														$label = htmlspecialchars($name, ENT_QUOTES);

														needreload();
														echo json_encode([
																'status' => 'success',
																'value'  => $value,
																'label'  => $label
														]);
														break;
												

												// case 'ivr':
												// case 'ringgroups':
												// Add more modules here...

												default:
														echo json_encode(array('status' => 'error', 'message' => "Module '" . $module . "' not supported"));
										}
										
										
								} catch (\Exception $e) {
										echo json_encode(array('status' => 'error', 'message' => $e->getMessage()));
								}
								exit;
								
						case 'add_ivr_entry':
						
								 header('Content-Type: application/json');
								$input = json_decode(file_get_contents("php://input"), true);

								$titleText   = isset($input['titleText']) ? trim($input['titleText']) : '';
								$destination = isset($input['destination']) ? trim($input['destination']) : '';
								$digit       = isset($input['digit']) ? trim($input['digit']) : '';

								// Basic validation
								if ($titleText === '' || $destination === '' || $digit === '') {
										echo json_encode([
												'status' => 'error',
												'message' => 'Missing titleText, destination, or digit'
										]);
										exit;
								}

								// Extract IVR ID
								if (!preg_match('/ivr-(\d+),/', $titleText, $m)) {
										echo json_encode([
												'status' => 'error',
												'message' => 'Could not extract IVR ID'
										]);
										exit;
								}

								$ivrId = (int)$m[1];

								$this->requireSection('ivr');

								// Check for duplicates
								if ($this->ivrEntryExists($ivrId, $digit)) {
										echo json_encode([
												'status' => 'error',
												'message' => 'Digit already exists for this IVR.'
										]);
										exit;
								}
								
								$ivr = \FreePBX::Ivr();

								// Read existing entries, append the new one, then save the full
								// set back through the IVR module instead of a raw INSERT
								$allEntries = $ivr->getAllEntries();
								$entries = (isset($allEntries[$ivrId]) && is_array($allEntries[$ivrId])) ? $allEntries[$ivrId] : array();

								$entries[] = array(
										'ivr_id'    => $ivrId,
										'selection' => $digit,
										'dest'      => $destination,
										'ivr_ret'   => 0
								);

								try {
										$ivr->saveEntry($ivrId, $entries);
										needreload();
										echo json_encode(['status' => 'success']);
								} catch (\Exception $e) {
										echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
								}

								exit;
						case 'add_dyn_entry':
						    header('Content-Type: application/json');
								$input = json_decode(file_get_contents("php://input"), true);

								$titleText   = isset($input['titleText']) ? trim($input['titleText']) : '';
								$destination = isset($input['destination']) ? trim($input['destination']) : '';
								$digit       = isset($input['digit']) ? trim($input['digit']) : '';

								// Basic validation
								if ($titleText === '' || $destination === '' || $digit === '') {
										echo json_encode([
												'status' => 'error',
												'message' => 'Missing titleText, destination, or digit'
										]);
										exit;
								}

								// Extract Dynamic Route ID
								if (!preg_match('/dynroute-(\d+),/', $titleText, $m)) {
										echo json_encode([
												'status' => 'error',
												'message' => 'Could not extract Dynamic Route ID'
										]);
										exit;
								}

								$dynId = (int)$m[1];

								$this->requireSection('dynroute');

								// Check for duplicates
								if ($this->dynEntryExists($dynId, $digit)) {
										echo json_encode([
												'status' => 'error',
												'message' => 'Digit already exists for this Dynamic Route.'
										]);
										exit;
								}

								$dyn = \FreePBX::Dynroute();

								// Read existing entries, append the new one, then save the full
								// set back through the Dynamic Routes module instead of a raw INSERT
								$allEntries = $dyn->getAllEntries();
								$entries = (isset($allEntries[$dynId]) && is_array($allEntries[$dynId])) ? $allEntries[$dynId] : array();

								$entries[] = array(
										'dynroute_id' => $dynId,
										'selection'   => $digit,
										'dest'        => $destination,
										'description' => ''
								);

								try {
										$dyn->saveEntry($dynId, $entries);
										needreload();
										echo json_encode(['status' => 'success']);
								} catch (\Exception $e) {
										echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
								}

								exit;

						case 'list_timegroups':
								header('Content-Type: application/json; charset=utf-8');
								try {
										$tc = \FreePBX::Timeconditions();
										$groupsRaw = $tc->listTimegroups();

										if (is_array($groupsRaw)) {
												$groups = array();
												foreach ($groupsRaw as $row) {
														$id   = isset($row['value']) ? $row['value'] : $row[0];
														$desc = isset($row[1]) ? $row[1] : $id;

														$groups[] = array(
																'id'          => (string)$id,
																'description' => (string)$desc
														);
												}
										}

										echo json_encode([
												'status' => 'success',
												'groups' => $groups
										]);
								} catch (\Throwable $e) {
										echo json_encode([
												'status'  => 'error',
												'message' => $e->getMessage()
										]);
								}
								exit;
						
						case 'list_calendars':
								header('Content-Type: application/json; charset=utf-8');
								try {
										$calsRaw = \FreePBX::Calendar()->listCalendars();
										$groups = array();

										if (is_array($calsRaw)) {
												foreach ($calsRaw as $id => $row) {
														$name = isset($row['name']) ? $row['name'] : $id;

														$groups[] = array(
																'id'   => (string)$id,
																'name' => (string)$name
														);
												}
										}

										echo json_encode([
												'status' => 'success',
												'groups' => $groups
										]);
								} catch (\Throwable $e) {
										echo json_encode([
												'status'  => 'error',
												'message' => $e->getMessage()
										]);
								}
								exit;
								
						case 'list_calendargroups':
								header('Content-Type: application/json; charset=utf-8');
								try {
										$calsRaw = \FreePBX::Calendar()->listGroups();
										$groups = array();

										if (is_array($calsRaw)) {
												foreach ($calsRaw as $id => $row) {
														$name = isset($row['name']) ? $row['name'] : $id;

														$groups[] = array(
																'id'   => (string)$id,
																'name' => (string)$name
														);
												}
										}

										echo json_encode([
												'status' => 'success',
												'groups' => $groups
										]);
								} catch (\Throwable $e) {
										echo json_encode([
												'status'  => 'error',
												'message' => $e->getMessage()
										]);
								}
								exit;
								
						case 'list_languages':
								header('Content-Type: application/json; charset=utf-8');
								try {
										$lang = \FreePBX::Soundlang();
										$groupsRaw = $lang->getLanguages();

										if (is_array($groupsRaw)) {
												$groups = array();
												foreach ($groupsRaw as $row) {
														$id   = isset($row['lang_code']) ? $row['lang_code'] : $row[0];
														$desc = isset($row['description']) ? $row['description'] : $id;
														$groups[] = array(
																'lang_code'   => (string)$id,
																'description' => (string)$desc
														);
												}
										}

										echo json_encode([
												'status' => 'success',
												'groups' => $groupsRaw
										]);
								} catch (\Throwable $e) {
										echo json_encode([
												'status'  => 'error',
												'message' => $e->getMessage()
										]);
								}
								exit;
								
						case 'list_music':
								header('Content-Type: application/json; charset=utf-8');
								try {
										$music = \FreePBX::Music();
										$groupsRaw = $music->getCategories();

										if (is_array($groupsRaw)) {
												$groups = array();
												foreach ($groupsRaw as $row) {
														$id   = isset($row['id']) ? $row['id'] : $row[0];
														$desc = isset($row['category']) ? $row['category'] : $id;
														$groups[] = array(
																'id'          => (string)$id,
																'category' => (string)$desc
														);
												}
										}

										echo json_encode([
												'status' => 'success',
												'groups' => $groups
										]);
								} catch (\Throwable $e) {
										echo json_encode([
												'status'  => 'error',
												'message' => $e->getMessage()
										]);
								}
								exit;
								
						case 'list_recordings':
								header('Content-Type: application/json; charset=utf-8');

								try {
										$recordings = \FreePBX::Recordings();
										$groupsRaw  = array();

										// Prefer FreePBX 17+ method if available and non-empty
										if (method_exists($recordings, 'getAllRecordingsList')) {
												$groupsRaw = $recordings->getAllRecordingsList();

												// In some cases (older 17 builds), method exists but returns empty
												if ((empty($groupsRaw) || !is_array($groupsRaw)) && method_exists($recordings, 'getAll')) {
														$groupsRaw = $recordings->getAll();
												}
										}
										// Legacy fallback (FreePBX ≤16)
										elseif (method_exists($recordings, 'getAll')) {
												$groupsRaw = $recordings->getAll();
										}

										if (!is_array($groupsRaw)) {
												throw new Exception('Recordings list could not be loaded.');
										}

										$groups = array();
										foreach ($groupsRaw as $row) {
												// id and display name handling (support numeric or associative arrays)
												if (isset($row['id'])) {
														$id = $row['id'];
												} elseif (isset($row[0])) {
														$id = $row[0];
												} else {
														$id = '';
												}

												if (isset($row['displayname'])) {
														$desc = $row['displayname'];
												} elseif (isset($row[1])) {
														$desc = $row[1];
												} else {
														$desc = $id;
												}

												if ($id !== '') {
														$groups[] = array(
																'id'          => (string)$id,
																'displayname' => (string)$desc
														);
												}
										}

										echo json_encode(array(
												'status' => 'success',
												'groups' => $groups
										));
								} catch (\Exception $e) {
										error_log('list_recordings error: ' . $e->getMessage());
										echo json_encode(array(
												'status'  => 'error',
												'message' => $e->getMessage()
										));
								}
								exit;

						case 'set_simtime':
								header('Content-Type: application/json');

								$dt = '';
								if (isset($_POST['customDateTime'])) {
										$dt = $_POST['customDateTime'];
								}

								if ($dt === '') {
                                        $stored = null;
                                } else {
                                        $stored = $dt;
                                }

                                $this->saveCurrentUserOverrides(array('custom_datetime' => $stored));

								echo json_encode(array(
										'status' => 'success',
										'stored' => $stored
								));
								exit;


						case 'need_reload_status':
								$needs_reload=check_reload_needed();

								echo json_encode([
										'status' => 'success',
										'need_reload' => (bool)$needs_reload
								]);
								exit;

						case 'get_sections':
								$freepbx = \FreePBX::create();
								$freepbx->Modules->loadFunctionsInc('core');

								$ampUser = core_getAmpUser($_SESSION['AMP_user']->username);

								$sections = [];
								if (!empty($ampUser['sections']) && is_array($ampUser['sections'])) {
										$sections = $ampUser['sections'];
								}

								echo json_encode([
										'status'   => 'success',
										'sections' => $sections
								]);
								exit;


						
						//default------------
            default:
                echo json_encode(array('status' => 'error', 'message' => 'Unknown command'));
                exit;
        }
    }
		
		public function checkForGitHubUpdate() {
        $modinfo = \FreePBX::Modules()->getInfo('dpviz');
        $ver = isset($modinfo['dpviz']['version']) ? $modinfo['dpviz']['version'] : '0.0.0';

        $url = "https://modules.volchko.xyz/dpviz/module.json";

        $opts = array(
            "http" => array(
                "method" => "GET",
                "header" => "User-Agent: ".\FreePBX::Config()->get("DASHBOARD_FREEPBX_BRAND").' '.get_framework_version()."\r\n"
            )
        );
        $context = stream_context_create($opts);
        $json = @file_get_contents($url, false, $context);

        if ($json === false) {
            return array('error' => 'Failed to fetch release info.');
        }

        $data = json_decode($json, true);
        if (!isset($data['version'])) {
            return array('error' => 'Invalid response from server.');
        }

        $latestVersion = ltrim($data['version'], 'v');
        $upToDate = version_compare($ver, $latestVersion, '>=');

        return array(
            'current' => $ver,
            'latest' => $latestVersion,
            'up_to_date' => $upToDate
        );
    }
		
		function sendCurlPost($url, array $postFields = array(), $decodeJson = true, $allowUuidRegenerate = true) {
				$endpoint = $url;
				$url = 'https://modules.volchko.xyz/dpviz/' . $endpoint;
				$modinfo = \FreePBX::Modules()->getInfo('dpviz');
				$dpvizVersion = '0.0.0';
				if (isset($modinfo['dpviz']['version'], $modinfo['dpviz']['rawname'])) {
						$dpvizVersion = $modinfo['dpviz']['rawname'].' '.$modinfo['dpviz']['version'];
				}
				
				$postFields['dpversion'] = $dpvizVersion;
				$postFields['fpbxversion'] = \FreePBX::Config()->get("DASHBOARD_FREEPBX_BRAND") . ' ' . get_framework_version();
				$postFields['install_uuid'] = $this->ensureInstallUuid();
				$postFields['install_fingerprint'] = $this->getInstallFingerprint();
				
				$ch = curl_init($url);

				curl_setopt_array($ch, [
						CURLOPT_POST            => true,
						CURLOPT_POSTFIELDS      => http_build_query($postFields),
						CURLOPT_RETURNTRANSFER  => true,
						CURLOPT_TIMEOUT         => 15,
						CURLOPT_CONNECTTIMEOUT  => 10,
						CURLOPT_FOLLOWLOCATION  => true,
						CURLOPT_SSL_VERIFYPEER  => true,
				]);

				$response  = curl_exec($ch);
				$httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
				$curlError = curl_error($ch);
				curl_close($ch);

				if ($response === false) {
						return [
								'status'  => 'error',
								'message' => 'cURL error: ' . $curlError
						];
				}

				if ($httpCode !== 200) {
						return [
								'status'  => 'error',
								'message' => 'HTTP error: ' . $httpCode
						];
				}

				$decoded = $decodeJson ? json_decode($response, true) : $response;
				if ($decodeJson && $allowUuidRegenerate && is_array($decoded) && !empty($decoded['regenerate_uuid'])) {
						$postFields['previous_install_uuid'] = $postFields['install_uuid'];
						$postFields['install_uuid'] = $this->ensureInstallUuid(true);
						return $this->sendCurlPost($endpoint, $postFields, $decodeJson, false);
				}

				return $decoded;
		}
		
		public function queueExists($qnum) {
				$sql = "SELECT COUNT(*) FROM `queues_config` WHERE extension = ?";
				$stmt = $this->db->prepare($sql);
				$stmt->execute([$qnum]);
				return $stmt->fetchColumn() > 0;
		}
		
		public function ringgroupExists($grpnum) {
				$sql = "SELECT COUNT(*) FROM `ringgroups` WHERE grpnum = ?";
				$stmt = $this->db->prepare($sql);
				$stmt->execute([$grpnum]);
				return $stmt->fetchColumn() > 0;
		}
		
		public function ivrEntryExists($ivr,$selection) {
				$sql = "SELECT COUNT(*) FROM `ivr_entries` WHERE ivr_id = ? AND selection = ?";
				$stmt = $this->db->prepare($sql);
				$stmt->execute([$ivr,$selection]);
				return $stmt->fetchColumn() > 0;
		}
		
		public function dynEntryExists($dyn,$selection) {
				$sql = "SELECT COUNT(*) FROM `dynroute_dests` WHERE dynroute_id = ? AND selection = ?";
				$stmt = $this->db->prepare($sql);
				$stmt->execute([$dyn,$selection]);
				return $stmt->fetchColumn() > 0;
		}
		public function nameExists($table, $col, $name) {

				// Whitelist allowed tables + columns to avoid SQL injection
				$allowedTables = ['announcement', 'callrecording', 'dynroute', 'ivr_details', 
													'languages', 'miscdests','queues_config', 'ringgroups',
													'queues','setcid','timeconditions']; 
				$allowedCols   = ['name', 'description', 'descr', 'displayname'];

				if (!in_array($table, $allowedTables)) {
						throw new Exception("Invalid table");
				}
				if (!in_array($col, $allowedCols)) {
						throw new Exception("Invalid column");
				}

				// Build SQL safely using validated identifiers
				$sql = "SELECT COUNT(*) FROM `$table` WHERE `$col` = ? LIMIT 1";
				$stmt = $this->db->prepare($sql);
				$stmt->execute([$name]);

				return $stmt->fetchColumn() > 0;
		}

		/**
		 * Wrapper around \FreePBX::Modules()->getDestinations().
		 *
		 * getDestinations() runs every installed module's legacy
		 * <module>_destinations() function. A single stale third-party module
		 * (e.g. an old calendar that still reads $group['description']) can raise
		 * an E_NOTICE / E_WARNING which Whoops promotes to an ErrorException; in
		 * dpviz's JSON/AJAX context that becomes a hard HTTP 500 that takes down
		 * the whole graph. We install a temporary error handler that swallows
		 * (and logs) non-fatal notices/warnings raised while destinations load,
		 * so one broken module degrades gracefully instead of 500-ing dpviz.
		 * Real errors still propagate. PHP 5.6 safe (finally is 5.5+).
		 *
		 * @param array $restrict Optional module restrict list, passed through.
		 * @return array          Destinations, or [] if the call itself throws.
		 */
		public function safeGetDestinations($restrict = array()) {
				// Non-fatal error types we swallow. Anything else (recoverable /
				// fatal) is left to normal handling by returning false below.
				$swallow = E_NOTICE | E_WARNING | E_DEPRECATED | E_STRICT | E_USER_NOTICE | E_USER_WARNING | E_USER_DEPRECATED;

				set_error_handler(function ($errno, $errstr, $errfile, $errline) use ($swallow) {
						// Respect the @ suppression operator on all PHP versions.
						if (!(error_reporting() & $errno)) {
								return false;
						}
						// Let anything outside our non-fatal set fall through to
						// the normal (Whoops) handler.
						if (!($errno & $swallow)) {
								return false;
						}
						freepbx_log(FPBX_LOG_WARNING, sprintf(
								"dpviz: suppressed a non-fatal error while loading module destinations: %s in %s on line %d",
								$errstr, $errfile, $errline
						));
						return true; // handled; do not promote to an exception
				});

				try {
						return \FreePBX::Modules()->getDestinations($restrict);
				} catch (\Exception $e) {
						freepbx_log(FPBX_LOG_ERROR, sprintf(
								"dpviz: getDestinations() threw while loading module destinations: %s in %s on line %d",
								$e->getMessage(), $e->getFile(), $e->getLine()
						));
						return array();
				} catch (\Throwable $e) {
						// PHP 7+ Errors (won't parse-break on 5.6 since it's a
						// separate catch block; \Throwable simply never matches there).
						freepbx_log(FPBX_LOG_ERROR, sprintf(
								"dpviz: getDestinations() raised %s while loading module destinations: %s in %s on line %d",
								get_class($e), $e->getMessage(), $e->getFile(), $e->getLine()
						));
						return array();
				} finally {
						restore_error_handler();
				}
		}

}


/**
 * Draw a destination select box, but allow an "Unassigned" option
 *
 * @param string $goto        The current destination string (e.g. "ext-local,2000,1")
 * @param string $name        The HTML name/id for the select box
 * @param array  $restrict    Restrict to certain modules (optional)
 * @param string $class       Extra CSS classes (optional)
 * @return string             HTML for the <select>
 */
 
function drawselects_unassigned($goto = '', $name = 'goto', $restrict = [], $class = '') {
    $destinations = \FreePBX::Dpviz()->safeGetDestinations($restrict);

    $html  = '<select name="' . htmlspecialchars($name) . '" id="' . htmlspecialchars($name) . '"';
    if ($class) {
        $html .= ' class="' . htmlspecialchars($class) . '"';
    }
    $html .= '>';

    // Add our fake "Unassigned" option mapped to zapateller
    $unassigned = 'app-blackhole,zapateller,1';
    $selected   = ($goto === $unassigned || empty($goto)) ? ' selected' : '';
    $html .= '<option value="' . $unassigned . '"' . $selected . '>'
           . _('-- Unassigned --')
           . '</option>';

    foreach ($destinations as $mod => $dests) {
    if (empty($dests)) continue;
				$html .= '<optgroup label="' . htmlspecialchars($mod) . '">';
				foreach ($dests as $d) {
						if (!is_array($d) || !isset($d['destination'])) continue;
						$sel = ($goto === $d['destination']) ? ' selected' : '';
						$html .= '<option value="' . htmlspecialchars($d['destination']) . '"' . $sel . '>'
									 . htmlspecialchars($d['description'])
									 . '</option>';
				}
				$html .= '</optgroup>';
		}

    $html .= '</select>';

    return $html;
}



function dpviz_ivr_details_has_column($column) {
    global $db;
    static $columns = null;

    if ($columns === null) {
        $columns = array();
        $rows = $db->getAll("DESCRIBE ivr_details", DB_FETCHMODE_ASSOC);
        if (!\DB::isError($rows) && is_array($rows)) {
            foreach ($rows as $row) {
                if (!empty($row['Field'])) {
                    $columns[$row['Field']] = true;
                }
            }
        }
    }

    return isset($columns[$column]);
}
