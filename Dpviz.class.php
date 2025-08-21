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

    public function install() {}
    public function uninstall() {}

    public function getOptions() {
        $sql = "SELECT * FROM dpviz LIMIT 1";
        $sth = $this->db->prepare($sql);
        $sth->execute();
        return $sth->fetch(\PDO::FETCH_ASSOC);
    }

    public function editDpviz($panzoom, $horizontal, $datetime,$dynmembers, $combineQueueRing, $extOptional, $fmfm, $minimal, $queue_member_display, $ring_member_display) {
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
						`ring_member_display` = :ring_member_display
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
						':ring_member_display' => $ring_member_display
        );

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($insert);
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

        switch ($action) {
            case 'edit':
                $this->editDpviz($panzoom, $horizontal, $datetime, $dynmembers, $combineQueueRing, $extOptional, $fmfm, $minimal, $queue_member_display, $ring_member_display);
                load_view(dirname(__FILE__) . "/views/rnav.php");
                break;
            default:
                break;
        }
    }

    public function ajaxRequest($req, &$setting) {
        switch ($req) {
            case 'save_options':
            case 'check_update':
            case 'make':
            case 'getrecording':
            case 'getfile':
                return true;
        }
        return false;
    }

    public function ajaxHandler() {
        $action = isset($_REQUEST['command']) ? $_REQUEST['command'] : '';
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

                $success = $this->editDpviz($panzoom, $horizontal, $datetime, $dynmembers, $combineQueueRing, $extOptional, $fmfm, $minimal, $queue_member_display, $ring_member_display);
                echo json_encode(array('success' => $success));
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
                // Properly setting up the language because calls to ajax.php currently ignore the lang COOKIE
                \FreePBX::create()->View->setAdminLocales();
                \bindtextdomain("dpviz", __DIR__ . "/i18n");
                \textdomain("dpviz");
                include 'process.php';
                echo json_encode(array(
                    //'vizButtons' => $buttons,
                    'vizHeader' => $header,
                    'gtext' => json_decode($gtext)
                ));
                exit;

            case 'getrecording':
                $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
								$fpbxResults= \FreePBX::Recordings()->getRecordingById($id);
								$lang=$_POST['lang'];
								include 'views/audio.php';
								
                header('Content-Type: application/json');
                echo json_encode(array(
                    'displayname' => $results['displayname'],
                    'filename' => $results['filename']
                ));
                exit;

            case 'getfile':
                include 'views/audio.php';
                exit;

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
}
