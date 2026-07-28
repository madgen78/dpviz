<?php
function sanitizeLabels($text) {
    if ($text === null) {
        $text = '';
    }
		
		$text = htmlentities($text, ENT_QUOTES, 'UTF-8');

    return $text;
}

function secondsToTimes($seconds) {
	
		if (!is_numeric($seconds) || $seconds < 0) {
			return $seconds;
		}
		
    $seconds = (int) round($seconds); // Ensure whole number input

    $hours = (int) ($seconds / 3600);
    $minutes = (int) (($seconds % 3600) / 60);
    $remainingSeconds = $seconds % 60;

    if ($hours > 0) {
        return $remainingSeconds === 0 
					? "$hours hrs, $minutes mins" 
					: "$hours hrs, $minutes mins, $remainingSeconds secs";
    } elseif ($minutes > 0) {
        return $remainingSeconds === 0 
					? "$minutes mins" 
					: "$minutes mins, $remainingSeconds secs";
    } else {
        return "$remainingSeconds secs";
    }
}

function formatPhoneNumbers($phoneNumber, $locale = 'en_US') {
	
		if (preg_match('/[()\[\],\-_]/', $phoneNumber)) {
				return $phoneNumber;
    }
		
		
    $hasPlus = substr($phoneNumber, 0, 1) === '+';
    $digits  = preg_replace('/\D/', '', $phoneNumber);

    switch ($locale) {
        // 🇺🇸 / 🇨🇦 US & Canada
        case 'en_US':
        case 'en_CA':
            if ($hasPlus && substr($digits, 0, 1) === '1') {
                $digits = substr($digits, 1); // drop leading 1
            }
            if (strlen($digits) === 10) {
                return ($hasPlus ? '+1 ' : '') .
                       '(' . substr($digits, 0, 3) . ') ' .
                       substr($digits, 3, 3) . '-' .
                       substr($digits, 6);
            }
            break;

        // 🇪🇸 Spain
        case 'es_ES':
            if (strlen($digits) === 9) {
                return substr($digits, 0, 3) . ' ' .
                       substr($digits, 3, 3) . ' ' .
                       substr($digits, 6);
            }
            break;

        // 🇫🇷 France
        case 'fr_FR':
            if (strlen($digits) === 10) {
                return trim(chunk_split($digits, 2, ' '));
            }
            break;

        // 🇩🇪 Germany (simplified)
        case 'de_DE':
            return preg_replace('/(0\d{2,5})(\d+)/', '$1 $2', $digits);

        // 🇳🇱 Netherlands
        case 'nl_NL':
            if (substr($digits, 0, 2) === '06' && strlen($digits) === 10) {
                return preg_replace('/(06)(\d{4})(\d{4})/', '$1-$2$3', $digits);
            }
            return preg_replace('/(0\d{2})(\d{3})(\d{4})/', '$1-$2 $3', $digits);

        // 🇯🇵 Japan
        case 'ja_JP':
            return preg_replace('/(0\d{1,4})(\d{2,4})(\d{4})/', '$1-$2-$3', $digits);

        // 🇨🇳 China (simplified vs mobile)
        case 'zh_CN':
            if (preg_match('/^1\d{10}$/', $digits)) {
                return preg_replace('/(\d{3})(\d{4})(\d{4})/', '$1-$2-$3', $digits);
            }
            return preg_replace('/(0\d{2,3})(\d{4})(\d{4})/', '($1) $2 $3', $digits);

        // 🇮🇹 Italy
        case 'it_IT':
            if (preg_match('/^3\d{8,9}$/', $digits)) {
                return preg_replace('/(\d{3})(\d{3})(\d{3,4})/', '$1 $2 $3', $digits);
            }
            return preg_replace('/(0\d{2,4})(\d+)/', '$1 $2', $digits);

        // 🇧🇷 Brazil
        case 'pt_BR':
            if (strlen($digits) === 11) {
                return preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $digits);
            }
            if (strlen($digits) === 10) {
                return preg_replace('/(\d{2})(\d{4})(\d{4})/', '($1) $2-$3', $digits);
            }
            break;

        // 🇵🇹 Portugal
        case 'pt_PT':
            if (strlen($digits) === 9) {
                return preg_replace('/(\d{3})(\d{3})(\d{3})/', '$1 $2 $3', $digits);
            }
            break;

        // 🇷🇺 Russia
        case 'ru_RU':
            if (strlen($digits) === 11 && substr($digits,0,1) === '8') {
                return preg_replace('/(\d{1})(\d{3})(\d{3})(\d{2})(\d{2})/', '$1 ($2) $3-$4-$5', $digits);
            }
            break;
    }

    // fallback → return original
    return $phoneNumber;
}

function sanitize_filename($string, $replace_with = '_') {
    // Replace spaces and other separators with underscore
    $string = preg_replace('/[^\w\-\.]+/', $replace_with, $string);

    // Remove multiple consecutive replace characters
    $string = preg_replace('/' . preg_quote($replace_with, '/') . '+/', $replace_with, $string);

    // Trim leading/trailing replace character
    $string = trim($string, $replace_with);

    // Prevent reserved Windows names
    $reserved = array('CON','PRN','AUX','NUL','COM1','COM2','COM3','COM4','COM5','COM6','COM7','COM8','COM9',
                 'LPT1','LPT2','LPT3','LPT4','LPT5','LPT6','LPT7','LPT8','LPT9');
    if (in_array(strtoupper($string), $reserved)) {
        $string = '_' . $string;
    }

    return $string;
}

function makeNode($module,$id,$label,$tooltip,$node,$rec,$sections,$active=true){
	$rawname= str_replace(' ', '', strtolower($module));
	$append='';
	switch ($module) {
			case 'Announcement':
					if (empty($rec)){$append='#norec';}
					$url=$rawname.'&view=form&extdisplay='.$id.$append;
					$shape='rect';
					$color='#fdf5e6';
					break;

			case 'Callback':
					$url=$rawname.'&view=form&itemid='.$id;
					$shape='rect';
					$color='#f7a8a8';
					break;

			case 'Call Flow Control':
					$rawname='daynight';
					$url=$rawname.'&view=form&itemid='.$id.'&extdisplay='.$id;
					$shape='rect';
					$color='#f2c27a';
					break;

			case 'Call Recording':
					$url=$rawname.'&view=form&extdisplay='.$id;
					$shape='rect';
					$color='#f75f5f';
					break;
			
			case 'Conferences':
					$url=$rawname.'&view=form&extdisplay='.$id;
					$shape='rect';
					$color='#deb887';
					break;

			case 'Custom Dests':
					$url=$rawname.'&view=form&destid='.$id;
					$shape='rect';
					$color='#d1e8e2';
					break;

			case 'Directory':
					$url=$rawname.'&view=form&id='.$id;
					$shape='rect';
					$color='#eb94e2';
					break;

			case 'DISA':
					$url=$rawname.'&view=form&itemid='.$id;
					$shape='rect';
					$color='#e8fa42';
					break;

			case 'Dyn Route':
					if (empty($rec)){$append='#norec';}
					$url=$rawname.'&action=edit&id='.$id.$append;
					$shape='rect';
					$color='#818fd5';
					break;

			case 'Feature Code':
					$url=$rawname.'admin';
					$shape='rect';
					$color='#dcdcdc';
					break;

			case 'IVR':
					if (empty($rec)){$append='#norec';}
					$url=$rawname.'&action=edit&id='.$id.$append;
					$shape='rect';
					$color='#ffd700';
					break;

			case 'Languages':
					$url=$rawname.'&view=form&extdisplay='.$id;
					$shape='rect';
					$color='#ed9581';
					break;

			case 'Misc Apps':
					$url=$rawname.'&action=edit&extdisplay='.$id;
					$shape='rect';
					$color='#5ffef7';
					break;

			case 'Misc Dests':
					$url=$rawname.'&view=form&extdisplay='.$id;
					$shape='rect';
					$color='#ff7f50';
					break;

			case 'Paging':
					if (empty($rec)){$append='#norec';}
					$url=$rawname.'&view=form&extdisplay='.$id.$append;
					$shape='rect';
					$color='#87cefa';
					break;
			
			case 'Phonebook':
					$url='phonebook';
					$shape='rect';
					$color='#bdb76b';
					break;
			
			case 'Queue Callback':
					$url=$rawname.'&view=form&id='.$id;
					$shape='rect';
					$color='#98fb98';
					break;

			case 'Queues':
					if (empty($rec)){$append='#norec';}
					$url=$rawname.'&view=form&extdisplay='.$id.$append;
					$shape='rect';
					$color='#66cdaa';
					break;

			case 'Queue Priorities':
					$url='queueprio&view=form&extdisplay='.$id;
					$shape='rect';
					$color='#ffc3a0';
					break;

			case 'Ring Groups':
					if (empty($rec)){$append='#norec';}
					$url=$rawname.'&view=form&extdisplay='.$id.$append;
					$shape='rect';
					$color='#92b8ef';
					break;

			case 'Set CID':
					$url=$rawname.'&view=form&id='.$id;
					$shape='rect';
					$color='#9a74fb';
					break;

			case 'Time Conditions':
					$url=$rawname.'&view=form&itemid='.$id;
					$module="TC";
					$shape='rect';
					$color='#1e90ff';
					break;
			
			case 'TTS':
					$url=$rawname.'&view=form&id='.$id;
					$shape='rect';
					$color='#e89530';
					break;

			case 'Trunks':
					$idArray=explode(",",$id);
					$url=$rawname.'&tech='.$idArray[0].'&extdisplay=OUT_'.$idArray[1];
					$shape='rect';
					$color='#42dffa';
					break;
					
			case 'VM Blast':
					$url=$rawname.'&view=form&extdisplay='.$id;
					$shape='rect';
					$color='#968869';
					break;

			case 'Virtual Queues':
					$rawname='vqueue';
					$url='vqueue&action=modify&id='.$id;
					$module='VQueue';
					$shape='rect';
					$color='#00fa9a';
					break;

			case 'Voicemail':
					$url=$rawname.'&action=bsettings&ext='.$id;
					$shape='rect';
					$color='#979291';
					break;

			default:
					// Code to execute if no case matches. Deliberately NOT the
					// Voicemail grey - an unrecognized destination type must not
					// masquerade as a real module.
					$url='#';
					$shape='rect';
					$color='#cbbcf3';
	}
	if (!$active){
		$color= muteHexColor($color, .80);
	}
	$outline = adjustHexColor($color, -45);
	
	$node->attribute('label', "{$module}: {$label}");
	$node->attribute('tooltip', $tooltip);
	//$ignoreRaw=array('ringgroups');
	//if (hasSectionAccess($sections, $rawname) && in_array($rawname,$ignoreRaw)){
		$node->attribute('URL', htmlentities('config.php?display='.$url));
		$node->attribute('target', '_blank');
	//}
	$node->attribute('shape', $shape);
	$node->attribute('color', $outline);
	$node->attribute('penwidth', '2');
	$node->attribute('fillcolor', $color);
	$node->attribute('style', 'rounded,filled');
	
	return $node;
}

function hasSectionAccess($sections, $key) {
    return is_array($sections)
        && (in_array('*', $sections, true)
            || in_array($key, $sections, true));
}


function noDestination($dpgraph,$id){
		$noDestNode = $dpgraph->beginNode('noDest'.$id,
			array(
				'label' => "❔",
				'tooltip' => _('Click to choose destination'),
				'shape' => 'circle',
				'URL' => '#',
				'fontcolor' => '#ffffff',
				'fillcolor' => '#4a90e2',
				'color' => adjustHexColor('#4a90e2', -45),
				'penwidth' => '2',
				'fontsize' => '20pt',
				'fixedsize' => true,
				'style' => 'rounded,filled'
			)
		);				
		return $noDestNode;
}

function adjustHexColor($hex, $steps = 0) {
    $hex = str_replace('#', '', $hex);

    // Short form (#abc)
    if (strlen($hex) === 3) {
        $hex = $hex[0].$hex[0]
             . $hex[1].$hex[1]
             . $hex[2].$hex[2];
    }

    // Invalid
    if (strlen($hex) !== 6) {
        return '#000000';
    }

    $steps = max(-255, min(255, (int)$steps));

    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));

    $r = max(0, min(255, $r + $steps));
    $g = max(0, min(255, $g + $steps));
    $b = max(0, min(255, $b + $steps));

    return sprintf('#%02X%02X%02X', $r, $g, $b);
}

function muteHexColor($hex, $amount = 0.5) {
    // $amount: 0 = original color, 1 = fully gray

    $hex = ltrim($hex, '#');

    if (strlen($hex) === 3) {
        $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
    }

    if (strlen($hex) !== 6) {
        return '#808080';
    }

    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));

    // Perceived luminance (better than simple average)
    $gray = (int)(0.299 * $r + 0.587 * $g + 0.114 * $b);

    // Blend original color toward gray
    $r = (int)($r + ($gray - $r) * $amount);
    $g = (int)($g + ($gray - $g) * $amount);
    $b = (int)($b + ($gray - $b) * $amount);

    return sprintf('#%02X%02X%02X', $r, $g, $b);
}



function insertDestination($dpgraph,$id){
		$insertDestNode = $dpgraph->beginNode('insertDest'.$id,
			array(
				'label' => "➕️",
				'tooltip' => _('Click to insert new destination'),
				'shape' => 'plaintext',
				'URL' => '#',
				'fontcolor' => '#808080',
				'fontsize' => '15pt',
				'fixedsize' => true,
				'margin' => '0'
			)
		);				
		return $insertDestNode;
}

function newSelection($dpgraph,$id){
		$insertDestNode = $dpgraph->beginNode('newSelection'.$id,
			array(
				'label' => "➕️",
				'tooltip' => _('Click to add selection'),
				'shape' => 'plaintext',
				'URL' => '#',
				'fontcolor' => '#808080',
				'fontsize' => '15pt',
				'fixedsize' => true,
				'margin' => '0'
			)
		);				
		return $insertDestNode;
}

function newEntry($dpgraph,$id){
		$insertDestNode = $dpgraph->beginNode('newEntry'.$id,
			array(
				'label' => "➕️",
				'tooltip' => _('Click to add entry'),
				'shape' => 'plaintext',
				'URL' => '#',
				'fontcolor' => '#808080',
				'fontsize' => '15pt',
				'fixedsize' => true,
				'margin' => '0'
			)
		);				
		return $insertDestNode;
}

function stopNode($dpgraph,$id){
		$undoNode = $dpgraph->beginNode('undoLast'.$id,
			array(
				'label' => "➕️",
				'tooltip' => _('Click to continue...'),
				'shape' => 'circle',
				'URL' => '#',
				'fontcolor' => '#ffffff',
				'fontsize' => '20pt',
				'fixedsize' => true,
				'fillcolor' => '#4a90e2',
				'style' => 'filled'
			)
		);				
		return $undoNode;
}

function notFound($module,$destination,$node){
	$outline = adjustHexColor('#ff0000', -45);
	$pos = strrpos($destination, ',');
	if ($pos !== false) {
			$output = substr($destination, 0, $pos);
	} else {
			$output = $destination; // No comma found
	}
	
	$node->attribute('label', _('Bad Dest').": {$module}: {$output}");
	$node->attribute('tooltip', _('Bad Dest').": {$module}: {$output}");
	$node->attribute('shape', 'rect');
	$node->attribute('fillcolor', '#ff0000');
	$node->attribute('color', $outline);
	$node->attribute('penwidth', '2');
	$node->attribute('style', 'rounded,filled');
	
	return $node;
}

function findRecording($route,$id){
	if (is_numeric($id)){
		
		if (isset($route['recordings'][$id])){
			$name=$route['recordings'][$id]['displayname'];
		}else{
			$name=_('None');
		}
	}elseif ($id==''){
		$name=_('None');
	}else{
		$name=$id;
	}
	return $name;
}

function checkTimeGroupLogic($entry) {
    list($time, $dow, $dom, $month) = explode('|', $entry);

    $errors = array();

    $monthOrder = array('jan'=>1,'feb'=>2,'mar'=>3,'apr'=>4,'may'=>5,'jun'=>6,
                   'jul'=>7,'aug'=>8,'sep'=>9,'oct'=>10,'nov'=>11,'dec'=>12);

    $monthStart = $monthEnd = null;
    $monthInverted = false;

    // Check for inverted month range
    if ($month !== '*') {
        if (preg_match('/(\w{3})-(\w{3})/', strtolower($month), $m)) {
            if (isset($monthOrder[$m[1]], $monthOrder[$m[2]])) {
                $monthStart = $monthOrder[$m[1]];
                $monthEnd = $monthOrder[$m[2]];

                if ($monthStart > $monthEnd) {
                    $monthInverted = true;
                    $errors[] = _('month inverted');
                }
            }
        }
    }

    $dayStart = $dayEnd = null;
    $dayInverted = false;

    // Check for inverted day-of-month
    if ($dom !== '*' && preg_match('/(\d{1,2})-(\d{1,2})/', $dom, $d)) {
        $dayStart = (int)$d[1];
        $dayEnd = (int)$d[2];

        if ($dayStart > $dayEnd) {
            $dayInverted = true;
            $errors[] = _('day-of-month inverted');
        }
    }

    if (!empty($errors)) {
        return " ❌ " . implode(', ', $errors);
    }

    return null;
}


function translateRange($value, $map) {
    if ($value === '*') return '';
		
    $parts = explode('-', $value);
    $translated = array_map(function($part) use ($map) {
        return isset($map[$part]) ? $map[$part] : $part;
    }, $parts);

    return implode('-', $translated);
}


function getFeatureNum($recID, &$route) {

    $recording = lazyLoadRow($route, 'recordings', $recID);
    if (!$recording) {
        return _('Disabled');
    }

    // Skip concatenated files
    if (!empty($recording['filename']) && strpos($recording['filename'], '&') !== false) {
        return _('Disabled');
    }

    $fcKey = '*29' . $recID;
    $fc    = lazyLoadRow($route, 'featurecodes', $fcKey);

    if (!$fc) {
        return _('Disabled');
    }

    // Pick custom or default code
    $featurenum = !empty($fc['customcode']) ? $fc['customcode'] : $fc['defaultcode'];

    // Normalize enabled flags
    $recEnabled = !empty($recording['fcode']) && (string)$recording['fcode'] === '1';
    $fcEnabled  = isset($fc['enabled']) && (string)$fc['enabled'] === '1';

    if ($recEnabled && $fcEnabled) {
        return $featurenum;
    }

    return $featurenum . ' (' . _('Disabled') . ')';
}


/**
 * Add members to a queue array and keep them sorted.
 *
 * @param array &$queueMembers Reference to the members array (static or dynamic)
 * @param array $newMembers    Array of members in "enum,pen" format
 */
function addAndSortMembers(&$queueMembers, $newMembers) {
    if (!isset($queueMembers) || !is_array($queueMembers)) {
        $queueMembers = array();
    }

    // Add all new members
    foreach ($newMembers as $member) {
        $queueMembers[] = trim($member);
    }

    // Sort by pen first, then enum
    usort($queueMembers, function($a, $b) {
        $partsA = explode(',', $a);
        $partsB = explode(',', $b);

        $enumA = (int)$partsA[0];
        $penA  = (int)$partsA[1];
        $enumB = (int)$partsB[0];
        $penB  = (int)$partsB[1];

        if ($penA != $penB) {
            return $penA - $penB;
        }

        return $enumA - $enumB;
    });
}


function isExtensionRegistered($extension, $tech) {
		global $astman;   // reuse the existing manager connection

    if (!$astman || !$astman->connected()) {
        return false;
    }
    //$astman = \FreePBX::create()->astman;

    // Choose command based on technology
    switch (strtoupper($tech)) {
        case 'PJSIP':
            $cmd = "pjsip show aor $extension";
            break;
        case 'SIP':
            $cmd = "sip show peer $extension";
            break;
        case 'IAX':
            $cmd = "iax2 show peer $extension";
            break;
        default:
            return false; // Unknown technology
    }

    // Run the command
    $response = $astman->send_request('Command', array('Command' => $cmd));

    // Extract data
    $data = '';
    if (isset($response['data'])) {
        $data = is_array($response['data']) ? implode("\n", $response['data']) : $response['data'];
    }

    // Parse based on tech
    if (strtoupper($tech) === 'PJSIP') {
    $aors = parsePjsipAors($data);
			foreach ($aors as $aor) {
					foreach ($aor['contacts'] as $contactLine) {
							// Match "Avail" and extract latency
							if (preg_match('/\bAvail\b\s+([\d.]+)/i', $contactLine, $m)) {
									return true; // Consider it reachable
							}
					}
			}
		}elseif (preg_match('/Status\s*:\s*(\S+)/i', $data, $m)) {
			// SIP or IAX: look for "Status: OK" or similar
			return (stripos($m[1], 'OK') !== false);
    }

    return false;
}

function parsePjsipAors($data) {
    $lines = explode("\n", $data);
    $aors = array();
    $currentAor = null;

    foreach ($lines as $line) {
        $line = trim($line);

        if (preg_match('/^Aor:\s+(\S+)/', $line, $match)) {
            $currentAor = $match[1];
            $aors[$currentAor] = array(
                'contact_found' => false,
                'contacts' => array(),
            );
        }

        if ($currentAor && strpos($line, 'Contact:') === 0) {
            $aors[$currentAor]['contact_found'] = true;
            $aors[$currentAor]['contacts'][] = $line;
        }
    }

    return $aors;
}


function loadRegistrations() {
    static $registrations = null;

    if ($registrations !== null) {
        return $registrations; // already cached
    }

    $astman = \FreePBX::create()->astman;
    $registrations = array();

    // --- Handle PJSIP ---
    $resp = $astman->send_request('Command', ['Command' => 'database show registrar contact']);
    if (!empty($resp['data'])) {
        $lines = explode("\n", $resp['data']);
        foreach ($lines as $line) {
            if (strpos($line, '{') !== false) {
                $json = trim(substr($line, strpos($line, '{')));
                $decoded = json_decode($json, true);

                if ($decoded && !empty($decoded['endpoint']) && !empty($decoded['user_agent'])) {
                    $endpoint = $decoded['endpoint'];
                    if (!isset($registrations[$endpoint])) {
                        $registrations[$endpoint] = array();
                    }
                    $registrations[$endpoint][] = $decoded['user_agent'];
                }
            }
        }
    }

    // --- Handle SIP (chan_sip) ---
    $resp = $astman->send_request('Command', ['Command' => 'sip show peers']);
    if (!empty($resp['data'])) {
        $lines = explode("\n", $resp['data']);
        foreach ($lines as $line) {
            if (preg_match('/^(\d+)\//', trim($line), $m)) {
                $ext = $m[1];
                $peerResp = $astman->send_request('Command', ['Command' => "sip show peer $ext"]);
                if (!empty($peerResp['data']) && preg_match('/Useragent\s*:\s*(.+)/i', $peerResp['data'], $ua)) {
                    if (!isset($registrations[$ext])) {
                        $registrations[$ext] = array();
                    }
                    $registrations[$ext][] = trim($ua[1]);
                }
            }
        }
    }
    return $registrations;
}

function getUserAgent($extension) {
    $registrations = loadRegistrations();
    $key = $extension;

    if (isset($registrations[$key])) {
        return $registrations[$key];
    } else {
        return array();
    }
}



function countVmMessages($ext, $context, $folderType) {
    $basePath = "/var/spool/asterisk/voicemail/$context/$ext";

    if ($folderType === "inbox") {
        $path = "$basePath/INBOX";
        return is_dir($path) ? count(glob("$path/*.txt")) : 0;
    }

    if ($folderType === "other") {
        $folders = array("Family", "Friends", "Old", "Work", "Urgent");
        $count = 0;
        foreach ($folders as $f) {
            $path = "$basePath/$f";
            if (is_dir($path)) {
                $count += count(glob("$path/*.txt"));
            }
        }
        return $count;
    }

    // If invalid type passed, return 0
    return 0;
}


function loadExtension(&$route, $ext) {
    if (isset($route['extensions'][$ext])) {
        return $route['extensions'][$ext];
    }

    $core = \FreePBX::Core();
    // Voicemail is optional → must check
    $vm = null;
    $mailbox = array();

    if (\FreePBX::Modules()->checkStatus("voicemail")) {
        try {
            $vm = \FreePBX::Voicemail();
            $mailbox = $vm->getMailbox($ext);
        } catch (\Exception $e) {
            error_log("Voicemail module error for $ext: ".$e->getMessage());
            $mailbox = array();
        }
    }

    $user    = $core->getUser($ext);
    $device  = $core->getDevice($ext);
    $mailbox = $vm->getMailbox($ext);

    if (!$user && !$device && !$mailbox) {
        return false; // nothing to load
    }

    // Detect tech type
    $tech = '';
    if (!empty($device['tech'])) {
        $tech = $device['tech'];
    } elseif ($user) {
        $tech = 'virtual';
    }

    // Normalize shape
    $extension = array(
        'extension' => $ext,
        'name'      => isset($user['name']) ? $user['name'] : '',
        'tech'      => $tech,
        'user'      => $user ? $user : array(),
        'device'    => $device ? $device : array(),
        'mailbox'   => $mailbox ? $mailbox : array()
    );

    $route['extensions'][$ext] = $extension;
    return $extension;
}

function hydrateExtension(&$route, $ext) {
    $extData = loadExtension($route, $ext);
    if (!$extData) return false;

    $extension = &$route['extensions'][$ext];
    $freepbx   = \FreePBX::create();

    // Registration
    if (!isset($extension['reg_status'])) {
        $extension['reg_status'] = isExtensionRegistered($ext, $extension['tech']);
    }

    // DND
    if (!isset($extension['dnd'])) {
				$dnd = false;

				// Check if the DND module exists AND is enabled
				if (\FreePBX::Modules()->checkStatus("donotdisturb")) {

						$dndObj = $freepbx->Donotdisturb;

						if (is_object($dndObj)) {
								$dndStatus = $dndObj->getStatusByExtension($ext);
								$dnd = ($dndStatus === 'YES');
						}
				}

				$extension['dnd'] = $dnd;
		}


    // CF
    if (!isset($extension['cf'])) {
				$cf = array();

				// Only attempt Call Forward lookup if the module is installed & enabled
				if (\FreePBX::Modules()->checkStatus("callforward")) {
						try {
								$cfObj = $freepbx->Callforward;

								if (is_object($cfObj)) {
										$cf = $cfObj->getStatusesByExtension($ext);
								}
						} catch (\Exception $e) {
								error_log("CallForward check failed for $ext: " . $e->getMessage());
						}
				}

				$extension['cf'] = $cf;
		}

    // User Agent
    if (!isset($extension['ua'])) {
        $extension['ua'] = getUserAgent($ext);
    }

    // Voicemail counts
    if (!empty($extension['mailbox']) && !isset($extension['mailbox']['label'])) {
        $context = isset($extension['mailbox']['vmcontext']) ? $extension['mailbox']['vmcontext'] : '';
        $inbox   = countVmMessages($ext, $context, 'inbox');
        $other   = countVmMessages($ext, $context, 'other');

        $extension['mailbox']['label'] = sprintf(
            "%s: %d  %s: %d  %s: %d",
            _('INBOX'), $inbox,
            _('Other'), $other,
            _('Total'), $inbox + $other
        );
    }
		
		$fmfm = sql("SELECT * FROM findmefollow WHERE grpnum = " . q($ext), "getRow", DB_FETCHMODE_ASSOC);

		if ($fmfm && !DB::isError($fmfm)) {

				$extension['fmfm'] = $fmfm;

				// Check module availability first
				if (\FreePBX::Modules()->checkStatus("findmefollow")) {
						try {
								$check = \FreePBX::Findmefollow()->getDDial($ext);
								$extension['fmfm']['ddial'] = $check ? 'EXTENSION' : 'DIRECT';
						} catch (\Exception $e) {
								error_log("FindMeFollow getDDial failed for $ext: " . $e->getMessage());
								$extension['fmfm']['ddial'] = null;
						}
				} else {
						// module not installed / disabled
						$extension['fmfm']['ddial'] = null;
				}
		}

    return $extension;
}



function buildExtTooltip($ext, &$route) {
    static $tooltipCache = [];

    if (isset($tooltipCache[$ext])) {
        return $tooltipCache[$ext];
    }

    $extension = hydrateExtension($route, $ext);
    if (!$extension) {
        return $tooltipCache[$ext] = '';
    }

    // always safe values
    $tech  = isset($extension['tech']) ? strtoupper($extension['tech']) : '';
    $reg   = !empty($extension['reg_status']);
    $ua    = isset($extension['ua']) ? (array)$extension['ua'] : [];

    $tooltip  = "\n\n" . _('Tech') . ": " . $tech;
    $tooltip .= "\n"   . _('Registration') . ": " . ($reg ? _('Yes') : _('No'));

    // User Agent
    if (!empty($ua)) {
        $tooltip .= "\n" . _('User Agent') . ":\n" . implode("\n", $ua) . "\n";
    }

    // -------------------------------
    // DND (may be missing)
    // -------------------------------
    $dnd = isset($extension['dnd']) ? $extension['dnd'] : false;
    $tooltip .= "\n" . _('Do Not Disturb') . ": " . ($dnd ? _('Enabled') : _('Disabled'));

    // -------------------------------
    // CALL FORWARD (may be missing)
    // -------------------------------
    $cf = isset($extension['cf']) ? (array)$extension['cf'] : [];
    $hasCF = false;

    foreach (['CF','CFB','CFU'] as $type) {
        if (!empty($cf[$type])) {
            $hasCF = true;
            break;
        }
    }

    $tooltip .= "\n" . _('Call Forward') . ": " . ($hasCF ? _('Enabled') : _('Disabled'));

    if ($hasCF) {
        foreach (['CF','CFB','CFU'] as $type) {
            if (!empty($cf[$type])) {
                $tooltip .= "\n--$type: " . $cf[$type];
            }
        }
    }

    // -------------------------------
    // FMFM (may be missing)
    // -------------------------------
    if (isset($extension['fmfm']) && is_array($extension['fmfm'])) {
        $fm = $extension['fmfm'];

        // no ddial key? treat as disabled
        $ddial = isset($fm['ddial']) ? $fm['ddial'] : null;

        if ($ddial === 'DIRECT') {
            $tooltip .= "\n\nFMFM: " . _('Enabled');

            $preRing = isset($fm['pre_ring']) ? secondsToTimes($fm['pre_ring']) : '0';
            $grpTime = isset($fm['grptime'])  ? secondsToTimes($fm['grptime'])  : '0';
            $grpList = isset($fm['grplist'])  ? $fm['grplist']                 : '';
            $needsConf = (!empty($fm['needsconf']) && $fm['needsconf'] === 'CHECKED') ? _('Yes') : _('No');

            $tooltip .= "\n" . _('Initial Ring Time') . ": " . $preRing;
            $tooltip .= "\n" . _('Ring Time') . ": " . $grpTime;
            $tooltip .= "\n" . _('Follow-Me List') . ": " . $grpList;
            $tooltip .= "\n" . _('Confirm Calls') . ": " . $needsConf;

        } else {
            $tooltip .= "\n\nFMFM: " . _('Disabled');
        }
    } else {
        $tooltip .= "\n\nFMFM: " . _('Disabled');
    }

    // -------------------------------
    // VOICEMAIL section (may be missing entirely)
    // -------------------------------
    $mb = isset($extension['mailbox']) ? (array)$extension['mailbox'] : [];

    if (!empty($mb)) {
        $tooltip .= "\n\n" . _('Voicemail') . ": " . _('Enabled');

        // Email
        $emailString = isset($mb['email']) ? trim($mb['email']) : '';

        $tooltip .= "\n" . _('Email') . ":";
        if (!empty($emailString)) {
            $emails = array_map('trim', explode(',', $emailString));
            $first = true;
            foreach ($emails as $email) {
                $email = sanitizeLabels($email);
                $tooltip .= $first ? " $email" : "\n    $email";
                $first = false;
            }
        }

        // Misc VM Options
        if (!empty($mb['options']) && is_array($mb['options'])) {
            foreach ($mb['options'] as $k => $v) {
                $tooltip .= "\n" . ucfirst($k) . ": " . ucfirst($v);
            }
        }

        // Count Messages (create if missing)
        if (!isset($mb['label'])) {
            $context = isset($mb['vmcontext']) ? $mb['vmcontext'] : '';
            $inbox   = countVmMessages($ext, $context, 'inbox');
            $other   = countVmMessages($ext, $context, 'other');

            $extension['mailbox']['label'] = sprintf(
                "%s: %d  %s: %d  %s: %d",
                _('INBOX'), $inbox,
                _('Other'), $other,
                _('Total'), $inbox + $other
            );
        }

        $tooltip .= "\n" . $extension['mailbox']['label'];

    } else {
        $tooltip .= "\n\n" . _('Voicemail') . ": " . _('Disabled');
    }

    // Cache & return
    return $tooltipCache[$ext] = $tooltip;
}



function resolveExtensionStatus($extension, $context = 'queue', $flags = []) {
	
    // Always priority DND/CF
    if ($extension['dnd'] || !empty($extension['cf']['CF']) || !empty($extension['cf']['CFB']) || !empty($extension['cf']['CFU']) ) {
        return ['icon' => '🟡', 'label' => _('DND/CF')];
    }

    if ($context === 'queue_edge') {
        if (!empty($flags['paused'])) {
            return ['icon' => '⏸️', 'label' => ''];
        }

        // Dynamic members
        if (!empty($flags['dynamic'])) {
            if (!empty($flags['loggedin'])) {
                return ['icon' => '🔵', 'label' => ''];
            }
            // dynamic members that are offline show no icon
            return ['icon' => '', 'label' => ''];
        }
    }

    // Standard queue logic
    if ($context === 'queue') {
        if (!empty($flags['paused'])) {
            return ['icon' => '⏸️', 'label' => _('Paused')];
        }
        if (!empty($flags['loggedin'])) {
            return ['icon' => '🔵', 'label' => ''];
        }
    }

    // virtual
    if ($extension['tech']=='virtual'){
        return ['icon' => '⚪', 'label' => ''];
    }

    // Fallback: online/offline
    return [
        'icon'  => $extension['reg_status'] ? '🟢' : '🔴',
        'label' => ''
    ];
}

/**
 * Is this IVR selection a plain integer that is safe to fold into a range?
 *
 * Deliberately stricter than is_numeric(): real dial plans use selections like
 * "*", "*98" and multi-digit entries, and a leading-zero key such as "007" must
 * keep its own text rather than be cast to 7. Anything rejected here is shown
 * verbatim on its own line.
 */
function isCollapsibleSelection($sel) {
    return (bool) preg_match('/^(0|[1-9][0-9]*)$/', (string)$sel);
}

/**
 * Collapse a list of IVR selection keys into a compact range string:
 *   0,1,2,3   -> "0-3"
 *   1,3,7     -> "1,3,7"
 *   0,1,2,5,6 -> "0-2,5,6"
 *
 * Runs of three or more consecutive keys become a range; a pair stays listed,
 * since "0,1" is no longer than "0-1" and reads more plainly. Non-numeric keys
 * (Invalid Input, Timeout) are the caller's problem - they are not keypresses
 * and must not be folded into a range.
 *
 * Only plain integers are collapsed - see isCollapsibleSelection(). A selection
 * like "007" or "*98" is left to the caller so its exact text survives; casting
 * those to int would silently redisplay "007" as "7".
 */
function collapseSelectionRanges($keys) {
    $nums = array();
    foreach ($keys as $k) {
        if (isCollapsibleSelection($k)) {
            $nums[] = (int)$k;
        }
    }
    if (empty($nums)) {
        return '';
    }

    $nums = array_values(array_unique($nums));
    sort($nums, SORT_NUMERIC);

    $flush = function ($start, $prev) {
        if ($start === $prev)     { return (string)$start; }
        if ($prev - $start === 1) { return $start . ',' . $prev; }
        return $start . '-' . $prev;
    };

    $parts = array();
    $start = $prev = $nums[0];
    $count = count($nums);

    for ($i = 1; $i < $count; $i++) {
        if ($nums[$i] === $prev + 1) {
            $prev = $nums[$i];
            continue;
        }
        $parts[] = $flush($start, $prev);
        $start = $prev = $nums[$i];
    }
    $parts[] = $flush($start, $prev);

    return implode(',', $parts);
}

/**
 * Evaluate a time condition's calendar (or calendar group) at a given instant.
 *
 * $nowTs is a Unix timestamp (the driver does Carbon::createFromTimestamp), so
 * it carries the simulated time straight through. Timezone is deliberately left
 * null: setTimezone() bails on an empty value, which keeps the calendar's own
 * timezone in place. Passing the time condition's timezone here would override
 * it and make simulated results disagree with real-time ones.
 *
 * Returns true when the calendar matches at that instant, false when it does
 * not, and null when we cannot tell - Calendar module absent or disabled, an id
 * that no longer resolves, or a driver that threw. Callers treat null as
 * "unknown" and leave the edge neutral rather than coloring an unverified state.
 */
function calendarIsCurrently($tc, $nowTs = null) {
    if (empty($tc['calendar_id']) && empty($tc['calendar_group_id'])) {
        return null;
    }

    try {
        if (!\FreePBX::Modules()->checkStatus("calendar")) {
            return null;
        }

        $cal = \FreePBX::Calendar();

        if (!empty($tc['calendar_id'])) {
            if (!method_exists($cal, 'matchCalendar')) {
                return null;
            }
            return (bool) $cal->matchCalendar($tc['calendar_id'], $nowTs, null);
        }

        if (!method_exists($cal, 'matchGroup')) {
            return null;
        }
        return (bool) $cal->matchGroup($tc['calendar_group_id'], $nowTs, null);

    } catch (\Exception $e) {
        freepbx_log(FPBX_LOG_WARNING, "dpviz: could not evaluate calendar for time condition: ".$e->getMessage());
        return null;
    } catch (\Throwable $e) {
        // PHP 7+ Errors. Never reached on 5.6, where the class does not exist.
        freepbx_log(FPBX_LOG_WARNING, "dpviz: could not evaluate calendar for time condition: ".$e->getMessage());
        return null;
    }
}

function lazyFetchRow(&$route, $table, $id, $query, $idcol, $multi = false, $postprocess = null) {
    global $db; // persistent FreePBX DB handle

    $rows = $multi
        ? $db->getAll($query, [], DB_FETCHMODE_ASSOC)
        : $db->getRow($query, [], DB_FETCHMODE_ASSOC);

    // If the query failed (e.g., table missing), return null instead of dying
    if (DB::isError($rows)) {
        return null;
    }

    // If no rows found, return null
    if (!$rows) {
        return null;
    }

    $out = $rows;

    // Optional postprocess callback
    if ($postprocess && is_callable($postprocess)) {
        $out = call_user_func($postprocess, $out, $id);
    }

    // Cache
    if (!isset($route[$table])) {
        $route[$table] = [];
    }
    $route[$table][$id] = $out;

    return $out;
}



function lazyLoadRow(&$route, $table, $id, $cidnum = '') {
    if (isset($route[$table][$id])) {
        return $route[$table][$id];
    }

    switch ($table) {
				case 'announcements':
						return lazyFetchRow($route, $table, $id,
								"SELECT * FROM announcement WHERE announcement_id = " . q($id),
								'announcement_id',
								false,
								function($row) {
										$row['dest'] = isset($row['post_dest']) ? $row['post_dest'] : '';
										return $row;
								}
						);

				case 'calendar':
				case 'calendargroup':
						// The Calendar module keys several unrelated payloads by the same id
						// (calendars, groups, calendar-raw, calendar-sync, outlook-details).
						// Without the namespace filter getRow() returns whichever row MySQL
						// hands back first - calendar-sync is a bare timestamp, calendar-raw
						// is iCal text - and json_decode() then yields no name for the label.
						$calNs = ($table === 'calendargroup') ? 'groups' : 'calendars';
						return lazyFetchRow($route,$table,$id,
						"SELECT * FROM kvstore_FreePBX_modules_Calendar WHERE `key` = " . q($id) . " AND `id` = " . q($calNs),
								'key',
								false,
								function ($row) {
										// Decode JSON value
										$val = json_decode($row['val'], true);
										if (is_array($val)) {
												return $val;
										}
										return array(); // fallback if JSON invalid
								}
						);

				case 'callback':
						return lazyFetchRow($route, $table, $id,
								"SELECT * FROM callback WHERE callback_id = " . q($id),
								'callback_id'
						);

				case 'callrecording':
						return lazyFetchRow($route, $table, $id,
								"SELECT * FROM callrecording WHERE callrecording_id = " . q($id),
								'callrecording_id'
						);

				case 'daynight':
						return lazyFetchRow($route, $table, $id,
								"SELECT * FROM daynight WHERE ext = " . q($id),
								'ext',
								true // multi
						);
						
				case 'directory':
						return lazyFetchRow($route, $table, $id,
								"SELECT * FROM directory_details WHERE id = " . q($id),
								'id'
						);
						
				case 'disa':
						return lazyFetchRow($route, $table, $id,
								"SELECT * FROM disa WHERE disa_id  = " . q($id),
								'disa_id'
						);			


				case 'dynroute':
						return lazyFetchRow($route,$table,$id,
								"SELECT * FROM dynroute WHERE id = " . q($id),
								'id',
								false,
								function ($row) use (&$route, $id) {
										// Fetch destinations for this dynroute
										$dests = sql(
												"SELECT * FROM dynroute_dests WHERE dynroute_id = " . q($id),
												"getAll",
												DB_FETCHMODE_ASSOC
										);

										if (!DB::isError($dests) && $dests) {
												foreach ($dests as $dest) {
														$selid = $dest['selection'];
														$row['routes'][$selid] = $dest;
												}
										} else {
												$row['routes'] = array();
										}

										return $row;
								}
						);

				case 'ext-group':
						return lazyFetchRow($route, $table, $id,
								"SELECT * FROM ringgroups WHERE grpnum = " . q($id),
								'grpnum'
						);
						
				case 'featurecodes':
						return lazyFetchRow($route,$table,$id,
								"SELECT *
								 FROM featurecodes
								 WHERE (defaultcode = " . q($id) . " OR customcode = " . q($id) . ")",
								'defaultcode',
								false,
								function ($row) {
										// index by whichever exists, prefer custom
										$key = !empty($row['customcode']) ? $row['customcode'] : $row['defaultcode'];
										return array_merge($row, ['_key' => $key]);
								}
						);

				case 'incoming':
						// Normalize "ANY"
						if ($id === 'ANY') {
								$id = '';
						}

						// First try DID + CID
						// Check if language_incoming table exists once up front
				$tableExists = sql("SHOW TABLES LIKE 'language_incoming'", "getOne");

				if (!empty($cidnum)) {
						$sql = "SELECT * FROM incoming WHERE extension = " . q($id) .
									 " AND cidnum = " . q($cidnum);
						$row = lazyFetchRow($route, $table, $id.'|'.$cidnum, $sql, 'extension');
						if ($row) {
								// Language sub-check (CID-specific)
								if (!empty($tableExists)) {
										$langSql = "SELECT language FROM language_incoming
																WHERE extension = " . q($id) .
																" AND cidnum = " . q($cidnum);
										$langRow = sql($langSql, "getRow", DB_FETCHMODE_ASSOC);
										if ($langRow && !empty($langRow['language'])) {
												$row['language'] = $langRow['language'];
												$route[$table][$id.'|'.$cidnum] = $row; // update cache
										}
								}
								return $row;
						}
				}

				// Fallback: DID only (no CID restriction)
				$sql = "SELECT * FROM incoming WHERE extension = " . q($id) .
							 " AND (cidnum IS NULL OR cidnum = '')";
				$row = lazyFetchRow($route, $table, $id, $sql, 'extension');
				if ($row) {
						// Language sub-check (DID only)
						if (!empty($tableExists)) {
								$langSql = "SELECT language FROM language_incoming
														WHERE extension = " . q($id) .
														" AND (cidnum IS NULL OR cidnum = '')";
								$langRow = sql($langSql, "getRow", DB_FETCHMODE_ASSOC);
								if ($langRow && !empty($langRow['language'])) {
										$row['language'] = $langRow['language'];
										$route[$table][$id] = $row; // update cache
								}
						}
						return $row;
				}


				case 'ivrs':
						return lazyFetchRow($route, $table, $id,
								"SELECT * FROM ivr_details WHERE id = " . q($id),
								'id',
								false,
								function($row, $id) {
										$entries = sql("SELECT * FROM ivr_entries WHERE ivr_id = " . q($id),
																	 "getAll", DB_FETCHMODE_ASSOC);
										if ($entries && is_array($entries)) {
												foreach ($entries as $ent) {
														$selid = $ent['selection'];
														$row['entries'][$selid] = $ent;
												}
										}
										return $row;
								}
						);

				case 'languages':
						return lazyFetchRow($route,$table,$id,
								"SELECT * FROM languages WHERE language_id = " . q($id),
								'language_id'
						);
		
				case 'meetme':
						return lazyFetchRow($route, $table, $id,
								"SELECT * FROM meetme WHERE exten = " . q($id),
								'exten'
						);

				case 'miscapps':
						return lazyFetchRow($route,$table,$id,
								"SELECT * FROM miscapps WHERE miscapps_id = " . q($id),
								'miscapps_id'
						);
						
				case 'miscdest':
						return lazyFetchRow($route,$table,$id,
								"SELECT * FROM miscdests WHERE id = " . q($id),
								'id'
						);

				case 'paging':
						return lazyFetchRow($route,$table,$id,
								"SELECT * FROM paging_config WHERE page_group = " . q($id),
								'page_group',
								false,
								function ($row) use ($id) {
										// Fetch group members
										$members = sql(
												"SELECT * FROM paging_groups WHERE page_number = " . q($id),
												"getAll",
												DB_FETCHMODE_ASSOC
										);
										$row['members'] = array();
										if (!DB::isError($members) && $members) {
												foreach ($members as $m) {
														$row['members'][] = $m['ext'];
												}
										}
										return $row;
								}
						);

				case 'queuecallback':
						return lazyFetchRow($route, $table, $id,
								"SELECT * FROM vqplus_callback_config WHERE id = " . q($id),
								'id'
						);

				case 'queues':
						return lazyFetchRow($route, $table, $id,
								"SELECT * FROM queues_config WHERE extension = " . q($id),
								'extension',
								false,
								function($row, $id) {
										$row['members']['static'] = array();
										$details = sql("SELECT * FROM queues_details WHERE id = " . q($id),
																	 "getAll", DB_FETCHMODE_ASSOC);
																	
										$row['members']['static'] = array();
										
										$strategy = null;
										foreach ($details as $qd) {
												if ($qd['keyword'] === 'strategy') {
														$strategy = $qd['data'];
														break;
												}
										}
										
										if ($details && is_array($details)) {
												foreach ($details as $qd) {

														if ($qd['keyword'] == 'member') {

																if (preg_match("/Local\/(\d+).*?,(\d+)/", $qd['data'], $m)) {

																		if ($strategy === 'linear') {
																				$row['members']['static'][$qd['flags']] = $m[1] . ',' . $m[2];
																		} else {
																				addAndSortMembers($row['members']['static'], array($m[1] . ',' . $m[2]));
																		}
																}

														} else {
																$row['data'][$qd['keyword']] = $qd['data'];
														}
													
												}
												if ($strategy === 'linear') {
														ksort($row['members']['static']);
												}
										}
										return $row;
								}
						);
				
				case 'queueprio':
						return lazyFetchRow($route,$table,$id,
								"SELECT * FROM queueprio WHERE queueprio_id = " . q($id),
								'queueprio_id'
						);
		
				case 'recordings':
						return lazyFetchRow($route,	$table,	$id,
								"SELECT * FROM recordings WHERE id = " . q($id),
								'id'
						);
		
				case 'setcid':
						return lazyFetchRow($route,$table,$id,
								"SELECT * FROM setcid WHERE cid_id = " . q($id),
								'cid_id'
						);
						
				case 'timeconditions':
						return lazyFetchRow($route, $table, $id,
								"SELECT * FROM timeconditions WHERE timeconditions_id = " . q($id),
								'timeconditions_id'
						);
				
				case 'timegroups':
						return lazyFetchRow($route,$table,$id,
								"SELECT * FROM timegroups_groups WHERE id = " . q($id),
								'id',
								false,
								function ($row) use (&$route, $id) {
										global $options;
										// Fetch details for this group
										$details = sql("SELECT * FROM timegroups_details WHERE timegroupid = " . q($id), "getAll", DB_FETCHMODE_ASSOC);
										if (!DB::isError($details) && $details) {
											//error_log('TZ: '.$route['currentTZ']);
											$simDT = !empty($options['custom_datetime']) ? $options['custom_datetime'] : null;
											
											if (timeGroupMatchesNow($details,$route['currentTZ'],$simDT)) {
													$row['iscurrently'] = true;
													//error_log('here');
											} else {
													$row['iscurrently'] = false;
											}

												$dowMap = array(
														'mon' => _('Mon'),'tue' => _('Tue'),'wed' => _('Wed'),
														'thu' => _('Thu'),'fri' => _('Fri'),'sat' => _('Sat'),'sun' => _('Sun'),
												);
												$monthMap = array(
														'jan' => _('Jan'),'feb' => _('Feb'),'mar' => _('Mar'),'apr' => _('Apr'),
														'may' => _('May'),'jun' => _('Jun'),'jul' => _('Jul'),'aug' => _('Aug'),
														'sep' => _('Sep'),'oct' => _('Oct'),'nov' => _('Nov'),'dec' => _('Dec'),
												);

												$dowOrder   = array('*'=>0,'sun'=>0,'mon'=>1,'tue'=>2,'wed'=>3,'thu'=>4,'fri'=>5,'sat'=>6);
												$monthOrder = array('*'=>0,'jan'=>1,'feb'=>2,'mar'=>3,'apr'=>4,'may'=>5,'jun'=>6,
																						'jul'=>7,'aug'=>8,'sep'=>9,'oct'=>10,'nov'=>11,'dec'=>12);

												// Sort details by month/dom/dow/time
												foreach ($details as &$tgd) {
														list($timeStr, $dowStr, $domStr, $monthStr) = explode('|', $tgd['time']);

														$tgd['_time']  = ($timeStr === '*') ? 0 : intval(str_replace(':', '', explode('-', $timeStr)[0]));
														$tgd['_dow']   = isset($dowOrder[strtolower(explode('-', $dowStr)[0])]) ? $dowOrder[strtolower(explode('-', $dowStr)[0])] : 0;
														$tgd['_dom']   = ($domStr === '*') ? 0 : intval(explode('-', $domStr)[0]);
														$tgd['_month'] = isset($monthOrder[strtolower(explode('-', $monthStr)[0])]) ? $monthOrder[strtolower(explode('-', $monthStr)[0])] : 0;
												}
												unset($tgd);

												usort($details, function($a, $b) {
														if ($a['_month'] !== $b['_month']) return $a['_month'] - $b['_month'];
														if ($a['_dom']   !== $b['_dom'])   return $a['_dom']   - $b['_dom'];
														if ($a['_dow']   !== $b['_dow'])   return $a['_dow']   - $b['_dow'];
														return $a['_time'] - $b['_time'];
												});
												
												$row['time'] = '';
												foreach ($details as $tgd) {
													$exploded = explode("|", $tgd['time']); 
													$time = ($exploded[0] !== "*") ? $exploded[0] : ""; 
													$dow = translateRange($exploded[1], $dowMap); 
													$day = ($exploded[2] !== "*") ? $exploded[2] : ""; 
													$month = translateRange($exploded[3], $monthMap); 
													if ($month && ($dow!='' || $day!='' || $time!=''))$month .= " | "; 
													if ($day && ($dow!='' || $time!='')) $day .= " | "; 
													if ($dow && ($time!='')) $dow .= " | ";
													
													$row['time'] .= $month . $day . $dow . $time . checkTimeGroupLogic($tgd['time']) . "\l"; 
												}
										} else {
												// no entries means no GotoIfTime, so the dialplan never matches
												if (!DB::isError($details)) {
														$row['iscurrently'] = false;
												}
												$row['time'] = "No times defined";
										}
										return $row;
								}
						);
				
				case 'trunks':
						return lazyFetchRow($route,$table,$id,
								"SELECT * FROM trunks WHERE trunkid = " . q($id),
								'trunkid'
						);

				case 'tts':
						return lazyFetchRow($route,$table,$id,
								"SELECT * FROM tts WHERE id = " . q($id),
								'id'
						);

				case 'vmblasts':
						return lazyFetchRow($route,$table,$id,
								"SELECT * FROM vmblast WHERE grpnum = " . q($id),
								'grpnum',
								false,
								function ($row) use ($id) {
										// Fetch blast members
										$members = sql(
												"SELECT * FROM vmblast_groups WHERE grpnum = " . q($id),
												"getAll",
												DB_FETCHMODE_ASSOC
										);
										$row['members'] = array();
										if (!DB::isError($members) && $members) {
												foreach ($members as $m) {
														$row['members'][] = $m['ext'];
												}
										}
										return $row;
								}
						);

				case 'vqplus_queue_config':
						return lazyFetchRow($route, $table, $id,
								"SELECT * FROM vqplus_queue_config WHERE queue_num = " . q($id),
								'queue_num',
								false,
								function($row, $id) use (&$route) {
										// Attach vqplus data to the queue entry
										if (!isset($route['queues'][$id])) {
												$route['queues'][$id] = array();
										}
										$route['queues'][$id]['vqplus'] = $row;
										return $row;
								}
						);

				case 'vqueues':
						return lazyFetchRow($route,$table,$id,
								"SELECT * FROM virtual_queue_config WHERE id = " . q($id),
								'id'
						);
		}


    return null;
}


function runAstmanCommand($cmd) {
    global $astman;

    if (!$astman || !$astman->connected()) {
        return [];
    }

    $response = $astman->send_request('Command', ['Command' => $cmd]);

    if (empty($response['data'])) {
        return [];
    }

    // Special case: keep whitespace for "dialplan show"
    if (stripos($cmd, 'dialplan show') === 0) {
       $lines = explode("\n", $response['data']);
				$lines = array_map(function($line) {
						return str_replace("\t", "    ", $line); // 4 spaces per tab
				}, $lines);
    } else {
        // Trim whitespace and drop empty lines for other commands
        $lines = array_filter(array_map('trim', explode("\n", $response['data'])), 'strlen');
    }

    return $lines;
}



function parsePenaltyAgents(array $lines) {
    $agents = [];

    foreach ($lines as $line) {
        // Match only entries like /QPENALTY/<queue>/agents/<ext> : <penalty>
        if (preg_match('#/QPENALTY/\d+/agents/(\d+)\s*:\s*(\d+)#', $line, $m)) {
            $ext = $m[1];
            $pen = $m[2];
            $agents[] = $ext . ',' . $pen;
        }
    }

    return $agents;
}

function getLoggedInAgents($qnum) {
    $loggedin = [];

    $lines = runAstmanCommand("queue show $qnum");

    foreach ($lines as $line) {
        // look only at dynamic members
        if (strpos($line, '(dynamic)') !== false &&
            preg_match('/Local\/(\d+)@from-queue/', $line, $m)) {
            $loggedin[] = $m[1];
        }
    }

    return $loggedin;
}

function getDayNightStatus($daynightnum) {
    $lines = runAstmanCommand("database show DAYNIGHT/C$daynightnum");

    $value = "";
    foreach ($lines as $line) {
        // Lines look like:  "/DAYNIGHT/C1                              : DAY"
        if (strpos($line, "/DAYNIGHT/") !== false && strpos($line, ':') !== false) {
            list(, $val) = explode(":", $line, 2);
            $value = trim($val);
            break; // first match only
        }
    }

    $dactive = $nactive = "";
    if ($value === "DAY") {
        $dactive = "("._("Active").")";
    } elseif ($value === "NIGHT") {
        $nactive = "("._("Active").")";
    }

    return [$dactive, $nactive];
}

function isMiscAppEnabled($ext) {
    $lines = runAstmanCommand("dialplan show app-miscapps");

    foreach ($lines as $line) {
        if (strpos($line, $ext) !== false) {
            return true;  // found in dialplan
        }
    }

    return false; // not found
}

function getPausedAgents($qnum) {
    $pausedAgents = [];

    $lines = runAstmanCommand("queue show $qnum");

    foreach ($lines as $line) {
        if (strpos($line, '(paused)') !== false &&
            preg_match('/Local\/(\d+)@from-queue/', $line, $m)) {
            $pausedAgents[] = $m[1];
        }
    }

    return $pausedAgents;
}


function getLabel($destination, &$route, &$unresolvedDestinations, $selectionId = null) {
    $parts = explode(',', $destination);

    $context = $parts[0];
    $ext     = isset($parts[1]) ? $parts[1] : '';
    $prio    = isset($parts[2]) ? $parts[2] : '';

    // Conference
    if ($context === 'ext-meetme') {
        $conf = lazyLoadRow($route, 'meetme', $ext);
        $desc = $conf ? $conf['description'] : _('Bad Dest');
        return "Conference: {$ext} {$desc}";
    }

    // DISA
    if ($context === 'disa') {
        $disa = lazyLoadRow($route, 'disa', $ext);
        $desc = $disa ? $disa['displayname'] : _('Bad Dest');
        return "DISA: {$desc}";
    }
		
		//Feature Code
		if ($context === 'ext-featurecodes') {
        $fc = lazyLoadRow($route, 'featurecodes', $ext);
        $desc = $fc ? $fc['description'] : _('Bad Dest');
        return "Feature Code: {$ext} - {$desc}";
    }

    // Fallback: stash unresolved with selection id
    $unresolvedDestinations[] = [
        'selection'   => $selectionId,
        'dest' => $destination
    ];

    return false;
}

function inRange($value, $range) {
    if ($range === "*") return true;
    if (strpos($range, '-') !== false) {
        list($start, $end) = explode('-', $range);
        return $value >= $start && $value <= $end;
    }
    return $value == $range;
}

function timeInRange($now, $range) {
    if ($range === "*") return true;

    list($start, $end) = explode('-', $range);

    return ($now >= $start && $now <= $end);
}




function timeGroupMatchesNow($details, $tz = 'default', $simDT = null) {
    // Maps used for parsing FreePBX-style values
    $dowOrder = [
        '*'   => 0,
        'sun' => 0,
        'mon' => 1,
        'tue' => 2,
        'wed' => 3,
        'thu' => 4,
        'fri' => 5,
        'sat' => 6,
    ];

    $monthOrder = [
        '*'   => 0,
        'jan' => 1, 'feb' => 2, 'mar' => 3, 'apr' => 4,
        'may' => 5, 'jun' => 6, 'jul' => 7, 'aug' => 8,
        'sep' => 9, 'oct' => 10, 'nov' => 11, 'dec' => 12,
    ];

		// Time helpers
		// Determine current time based on timezone
    if (!empty($simDT)) {
				// Use simulated time
				try {
						if ($tz && strtolower($tz) !== 'default') {
								$now = new DateTime($simDT, new DateTimeZone($tz));
						} else {
								$now = new DateTime($simDT);
						}
				} catch (Exception $e) {
						error_log("Invalid simulated datetime '$simDT', falling back to real now()");
						$now = new DateTime();
				}
		} else {
				// Use real system time
				if ($tz && strtolower($tz) !== 'default') {
						try {
								$now = new DateTime('now', new DateTimeZone($tz));
						} catch (Exception $e) {
								$now = new DateTime();
						}
				} else {
						$now = new DateTime();
				}
		}

    $currMonth = (int)$now->format('n');   // 1-12
    $currDay   = (int)$now->format('j');   // 1-31
    $currDow   = (int)$now->format('w');   // 0-6 (Sun=0)
    $currTime  = $now->format('H:i');      // "13:45"

    // ---- helpers for parsing ranges ----

    $parseMonthRange = function($raw) use ($monthOrder) {
        if ($raw === "*") return "*";
        $raw = strtolower($raw);
        if (strpos($raw, '-') !== false) {
            list($a, $b) = explode('-', $raw);
            return [$monthOrder[$a], $monthOrder[$b]];
        }
        return [$monthOrder[$raw], $monthOrder[$raw]];
    };

    $parseDowRange = function($raw) use ($dowOrder) {
        if ($raw === "*") return "*";
        $raw = strtolower($raw);
        if (strpos($raw, '-') !== false) {
            list($a, $b) = explode('-', $raw);
            return [$dowOrder[$a], $dowOrder[$b]];
        }
        return [$dowOrder[$raw], $dowOrder[$raw]];
    };

    $parseNumericRange = function($raw) {
        if ($raw === "*") return "*";
        if (strpos($raw, '-') !== false) {
            list($a, $b) = explode('-', $raw);
            return [(int)$a, (int)$b];
        }
        return [(int)$raw, (int)$raw];
    };

    $timeInRange = function($now, $range) {
        if ($range === "*") return true;
				if (strpos($range, '-') === false) {
					return $now === $range;
				}
        list($start, $end) = explode('-', $range);
        return ($now >= $start && $now <= $end);
    };

    // ---- MAIN MATCHING LOOP ----

    foreach ($details as $tgd) {
        list($tRange, $dowRaw, $dayRaw, $monRaw) = explode("|", $tgd['time']);

        $monthRange = $parseMonthRange($monRaw);
        $dowRange   = $parseDowRange($dowRaw);
        $dayRange   = $parseNumericRange($dayRaw);

        // --- MONTH MATCH ---
        $monthMatch = (
            $monthRange === "*" ||
            ($currMonth >= $monthRange[0] && $currMonth <= $monthRange[1])
        );

        // --- DAY MATCH ---
        $dayMatch = (
            $dayRange === "*" ||
            ($currDay >= $dayRange[0] && $currDay <= $dayRange[1])
        );

        // --- DOW MATCH ---
        $dowMatch = (
            $dowRange === "*" ||
            ($currDow >= $dowRange[0] && $currDow <= $dowRange[1])
        );

        // --- TIME MATCH ---
        $timeMatch = (
            $tRange === "*" ||
            $timeInRange($currTime, $tRange)
        );

        // If ANY rule matches → timegroup is active
        if ($monthMatch && $dayMatch && $dowMatch && $timeMatch) {
            return true;
        }
    }

    return false;
}
