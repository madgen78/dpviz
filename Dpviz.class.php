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

    public function install() {
        // Required by BMO, but can remain empty
    }

    public function uninstall() {
        // Required by BMO, but can remain empty
    }

    public function getOptions() {
        $sql = "SELECT panzoom, horizontal, datetime, destination, scale, dynmembers FROM dpviz";
        $sth = $this->db->prepare($sql);
        $sth->execute();
        return $sth->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    public function editDpviz($panzoom, $horizontal, $datetime, $destination, $scale, $dynmembers) {
        $sql = "UPDATE dpviz SET
            `panzoom` = :panzoom,
            `horizontal` = :horizontal,
						`datetime` = :datetime,
						`destination` = :destination,
						`scale` = :scale,
						`dynmembers` = :dynmembers
            WHERE `id` = 1";
        $insert = [
            ':panzoom' => $panzoom,
            ':horizontal' => $horizontal,
						':datetime' => $datetime,
						':destination' => $destination,
						':scale' => $scale,
						':dynmembers' => $dynmembers
        ];
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($insert);
    }
    
    public function doConfigPageInit($page) {
        $request = $_REQUEST;
        $action = isset($request['action']) ? $request['action'] : '';
        $panzoom = isset($request['panzoom']) ? $request['panzoom'] : '';
        $horizontal = isset($request['horizontal']) ? $request['horizontal'] : '';
				$datetime = isset($request['datetime']) ? $request['datetime'] : '';
				$destination = isset($request['destination']) ? $request['destination'] : '';
				$scale = isset($request['scale']) ? $request['scale'] : '';
				$dynmembers = isset($request['dynmembers']) ? $request['dynmembers'] : '';

        switch ($action) {
            case 'edit':
                $this->editDpviz($panzoom, $horizontal, $datetime, $destination, $scale, $dynmembers);
                break;
            default:
                break;
        }
    }
		public function getRightNav($request) {
			return load_view(__DIR__."/views/rnav.php",[]);
		}
}
