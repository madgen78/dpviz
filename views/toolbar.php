<?php
if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed'); }

/**
 * Escape a value for HTML output.
 *
 * Everything below is built by string-concatenating database values into
 * markup and then echoed raw into the <select>, so every interpolated value
 * has to go through here. Descriptions and names are set in other modules,
 * which makes an unescaped one a stored XSS in the browser of any admin who
 * loads this page -- note that "it's inside <option>" is not protection,
 * since a payload can simply close the tag and the select first.
 */
if (!function_exists('dpp_h')) {
	function dpp_h($text) {
		return htmlspecialchars((string)$text, ENT_QUOTES, 'UTF-8');
	}
}

$options=\FreePBX::Dpviz()->getOptions();


if ($options['displaydestinations']){
	$destinations=\FreePBX::Dpviz()->safeGetDestinations();
	$displayDestinationOpt = ' [ '._('destination').' ]';
}else{
	$displayDestinationOpt = '';
}

try{
	$soundlang = FreePBX::create()->Soundlang;
	$options['lang'] = $soundlang->getLanguage();
}catch(\Exception $e){
	freepbx_log(FPBX_LOG_ERROR,"Soundlang is missing, please install it."); 
	$options['lang'] = "en";
}

$options['sections'] = [];

$jsSections = [];

if (isset($_SESSION['AMP_user']) && is_object($_SESSION['AMP_user'])
    && method_exists($_SESSION['AMP_user'], 'getSections')) {

    $sections = $_SESSION['AMP_user']->getSections();

    if (is_array($sections)) {
        $jsSections = $sections;
				$options['sections'] = $sections;
    }
}

?>
<script>
    window.dpvizConfig = window.dpvizConfig || {};
    dpvizConfig.sections = <?php echo json_encode($jsSections); ?>;
</script>
<?php

function dpp_load_incoming_routes() {
  global $db;
	global $options;
	global $destinations;
	
  $sql = "select * from incoming order by extension";
  $results = $db->getAll($sql, DB_FETCHMODE_ASSOC);
  if (DB::IsError($results)) {
    die_freepbx($results->getMessage()."<br><br>Error selecting from incoming");       
  }
	
	$routes = array();
  // Store the routes in a hash indexed by the inbound number
  if (is_array($results)) {
    foreach ($results as $route) {
      $num = $route['extension'];
      $cid = $route['cidnum'];
			if (empty($num) && empty($cid)){$exten='ANY';}else{$exten=$num.$cid;}
      $routes[$exten] = $route;
			
			if ($options['displaydestinations']){
					$destDescription ='';
					$routeDest = isset($destinations[$route['destination']])
						? $destinations[$route['destination']]
						: array('name'=>'','description'=>'');

					$name = !empty($routeDest['category'])
						? $routeDest['category']
						: $routeDest['name'];
						
					if (isset($routeDest['destination'])){
						$destDescription = (strpos($routeDest['destination'], 'zapateller') !== false)
						? _('Undefined Destination')
						: $routeDest['description'];
					}

					$routes[$exten]['goDestination'] = $name . ': ' . $destDescription;
			}
    }
  }
	return $routes;
}

$inroutes= dpp_load_incoming_routes();

$existingModules = array('incoming');

function dpp_load_tables() {
	global $db, $existingModules;
	$dproute=array();

	$tables = array('announcement','callrecording','daynight','dynroute','languages','ivr_details',
	  'miscapps','miscdests','queues_config','ringgroups','setcid','timeconditions','virtual_queue_config',
		'dpviz_views'
	);
	
	$freepbx = \FreePBX::create();
	foreach ($tables as $table) {
    // Check if the table exists
    $tableExists = $db->getOne("SHOW TABLES LIKE '$table'");
    
    if (!$tableExists) {
        continue;
    }
		
		if ($table==='ivr_details'){
			$checkMod='ivr';
		}elseif ($table==='queues_config'){
			$checkMod='queues';
		}else{
			$checkMod=$table;
		}
		
		if ($table!='dpviz_views'){
			if ($freepbx->Modules->checkStatus($checkMod)) {
				$existingModules[] = $checkMod; //js array for new destinations
			}else{
				//module is disabled
				continue;
			}
		}
		
		$order = ($table === 'dpviz_views') ? 'ORDER BY description' : '';
		$query = "SELECT * FROM `$table` $order";
    $results = $db->getAll($query, DB_FETCHMODE_ASSOC);
    
    if (DB::IsError($results)) {
				continue;  // Skip to the next table
    }

 		if ($table == 'announcement') {
				foreach($results as $an) {
					$id = $an['announcement_id'];
					$dproute['announcements'][$id] = $an;
				}
		}elseif ($table == 'daynight') {
				foreach($results as $daynight) {
					$id = $daynight['ext'];
					if (!isset($dproute['daynight'][$id])) {
							$dproute['daynight'][$id] = array();
					}
					$dproute['daynight'][$id][] = $daynight;
				}
		}elseif ($table == 'dynroute') {
        foreach ($results as $dynroute) {
            $id = $dynroute['id'];
            $dproute['dynroute'][$id] = $dynroute;
        }
    }elseif ($table == 'languages') {
        foreach($results as $languages) {
					$id=$languages['language_id'];
					$dproute['languages'][$id] = $languages;
				}
    }elseif ($table == 'ivr_details') {
        foreach($results as $ivr) {
					$id = $ivr['id'];
					$dproute['ivrs'][$id] = $ivr;
				}
    }elseif ($table == 'miscapps') {
        foreach($results as $miscapps) {
					$id = $miscapps['miscapps_id'];
					$dproute['miscapps'][$id] = $miscapps;
				}
		}elseif ($table == 'queues_config') {
        foreach($results as $q) {
					$id = $q['extension'];
					$dproute['queues'][$id] = $q;
				}
		}elseif ($table == 'ringgroups') {
        foreach($results as $rg) {
					$id = $rg['grpnum'];
					$dproute['ringgroups'][$id] = $rg;
				}
    }elseif ($table == 'timeconditions') {
        foreach($results as $tc) {
					$id = $tc['timeconditions_id'];
					$dproute['timeconditions'][$id] = $tc;
				}
		}elseif ($table == 'virtual_queue_config') {
        foreach($results as $vqueues) {
					$id = $vqueues['id'];
					$dproute['vqueues'][$id] = $vqueues;
				}
		}elseif ($table == 'dpviz_views') {
        foreach($results as $dpvizViews) {
					$id = $dpvizViews['id'];
					$dproute['dpvizViews'][$id] = $dpvizViews;
				}
		}
		
	}
	return $dproute;
}

$otherroutes= dpp_load_tables();

//build dropdowns
$dropOptions="";

//build js array
echo "<script>window.existingModules = " . json_encode($existingModules) . ";</script>";


# Users
$users=\FreePBX::Core()->getAllUsersByDeviceType();

foreach($users as $user) {
	$id = $user['extension'];
	$otherroutes['extensions'][$id]= $user;
}

//Saved Views
if (isset($otherroutes['dpvizViews']) && count($otherroutes['dpvizViews']) > 0){
	$dropOptions.='<optgroup label="' . _('Saved Views') . '">';
	foreach ($otherroutes['dpvizViews'] as $i=>$ii){
		$skipArray = explode(';', $ii['skip']);
		$skipJson = htmlspecialchars(json_encode($skipArray), ENT_QUOTES, 'UTF-8');
		$description = htmlspecialchars($ii['description'], ENT_QUOTES, 'UTF-8');

		$dropOptions .= '<option
			value="' . dpp_h($ii['ext']) . '|' . dpp_h($ii['jump']) . '"
			data-id="' . dpp_h($ii['id']) .'" data-skips="' . $skipJson . '">' . $description . '</option>';
		}
		$dropOptions.='</optgroup>';
}

//Inbound Routes 
if (isset($inroutes) && count($inroutes) > 0){
	$dropOptions .= '<optgroup label="' . _('Inbound Routes') . $displayDestinationOpt.'">';
	
	foreach ($inroutes as $in=>$extt){
		$e=$extt['extension'];
		if (empty($e)){$e='ANY';}
		if (!empty($extt['cidnum'])){$c='&'.$extt['cidnum'];$cName=' / '.$extt['cidnum'];}else{$c=$cName='';}
		
		if ($options['displaydestinations'] && isset($extt['goDestination'])){
			$displayDestination = '[ ' . $extt['goDestination'] . ' ]';
		}else{
			$displayDestination='';
		}
		
		$dropOptions.='<option value="from-trunk,'.dpp_h($e.$c).',1,'.dpp_h($options['lang']).'">'.dpp_h($e.$cName).' : '.dpp_h($extt['description']).' '.dpp_h($displayDestination).'</option>';
	}
	$dropOptions.='</optgroup>';
}

//Time Conditions
if (isset($otherroutes['timeconditions']) && count($otherroutes['timeconditions']) > 0){
	$dropOptions.='<optgroup label="' . _('Time Conditions') . '">';
	foreach ($otherroutes['timeconditions'] as $i=>$ii){
		$dropOptions.='<option value="timeconditions,'.dpp_h($ii['timeconditions_id']).',1,'.dpp_h($options['lang']).'">'.dpp_h($ii['displayname']).'</option>';
	}
	$dropOptions.='</optgroup>';
	
}

//Call Flow Control
if (isset($otherroutes['daynight']) && count($otherroutes['daynight']) > 0){
	$dropOptions.='<optgroup label="' . _('Call Flow Control') . '">';
	foreach ($otherroutes['daynight'] as $i=>$ii){
		foreach ($ii as $iii){
			if ($iii['dmode']=='fc_description'){
				$ext=$iii['ext'];
				$name='('.$ext.') '.$iii['dest'];
			}
		}
		$dropOptions.='<option value="app-daynight,'.dpp_h($ext).',1,'.dpp_h($options['lang']).'">'.dpp_h($name).'</option>';
	}
	$dropOptions.='</optgroup>';
	
}

//Dynamic Routes
if (isset($otherroutes['dynroute']) && count($otherroutes['dynroute']) > 0){
	$dropOptions.='<optgroup label="' . _('Dynamic Routes') . '">';
	foreach ($otherroutes['dynroute'] as $i=>$ii){
		$dropOptions.='<option value="dynroute-'.dpp_h($ii['id']).',s,1,'.dpp_h($options['lang']).'">'.dpp_h($ii['name']).'</option>';
	}
	$dropOptions.='</optgroup>';
}
//IVRs
if (isset($otherroutes['ivrs']) && count($otherroutes['ivrs']) > 0){
	$dropOptions.='<optgroup label="IVRs">';
	foreach ($otherroutes['ivrs'] as $i=>$ii){
		$dropOptions.='<option value="ivr-'.dpp_h($ii['id']).',s,1,'.dpp_h($options['lang']).'">'.dpp_h($ii['name']).'</option>';
	}
	$dropOptions.='</optgroup>';
}

//Virtual Queues
if (isset($otherroutes['vqueues']) && count($otherroutes['vqueues']) > 0){
	$dropOptions.='<optgroup label="' . _('Virtual Queues') . '">';
	foreach ($otherroutes['vqueues'] as $i=>$ii){
		$dropOptions.='<option value="ext-vqueues,'.dpp_h($ii['id']).',1,'.dpp_h($options['lang']).'" >'.dpp_h($ii['name']).'</option>';
	}
	$dropOptions.='</optgroup>';
}

//Queues
if (isset($otherroutes['queues']) && count($otherroutes['queues']) > 0){
	$dropOptions.='<optgroup label="' . _('Queues') . '">';
	foreach ($otherroutes['queues'] as $i=>$ii){
		$dropOptions.='<option value="ext-queues,'.dpp_h($ii['extension']).',1,'.dpp_h($options['lang']).'" >'.dpp_h($ii['extension']).' : '.dpp_h($ii['descr']).'</option>';
	}
	$dropOptions.='</optgroup>';
}

//Ring Groups
if (isset($otherroutes['ringgroups']) && count($otherroutes['ringgroups']) > 0){
	$dropOptions.='<optgroup label="' . _('Ring Groups') . '">';
	foreach ($otherroutes['ringgroups'] as $i=>$ii){
		$dropOptions.='<option value="ext-group,'.dpp_h($ii['grpnum']).',1,'.dpp_h($options['lang']).'">'.dpp_h($ii['grpnum']).' : '.dpp_h($ii['description']).'</option>';
	}
	$dropOptions.='</optgroup>';
}

//Announcements
if (isset($otherroutes['announcements']) && count($otherroutes['announcements']) > 0){
	$dropOptions.='<optgroup label="' . _('Announcements') . '">';
	foreach ($otherroutes['announcements'] as $i=>$ii){
		$dropOptions.='<option value="app-announcement-'.dpp_h($ii['announcement_id']).',s,1,'.dpp_h($options['lang']).'">'.dpp_h($ii['description']).'</option>';
	}
	$dropOptions.='</optgroup>';
}

//Languages
if (isset($otherroutes['languages']) && count($otherroutes['languages']) > 0){
	$dropOptions.='<optgroup label="' . _('Languages') . '">';
	foreach ($otherroutes['languages'] as $i=>$ii){
		$dropOptions.='<option value="app-languages,'.dpp_h($ii['language_id']).',1,'.dpp_h($options['lang']).'">'.dpp_h($ii['description']).'</option>';
	}
	$dropOptions.='</optgroup>';
	
}

//Misc Applications
if (isset($otherroutes['miscapps']) && count($otherroutes['miscapps']) > 0){
	$dropOptions.='<optgroup label="' . _('Misc Applications') . '">';
	foreach ($otherroutes['miscapps'] as $i=>$ii){
		$dropOptions.='<option value="miscapps,'.dpp_h($ii['miscapps_id']).',s,1,'.dpp_h($options['lang']).'">'.dpp_h($ii['description']).' ('.dpp_h($ii['ext']).')</option>';
	}
	$dropOptions.='</optgroup>';
}

//Extensions
if (isset($otherroutes['extensions']) && count($otherroutes['extensions']) > 0){
	$dropOptions.='<optgroup label="' . _('Extensions') . '">';
	foreach ($otherroutes['extensions'] as $i=>$ii){
		$dropOptions.='<option value="from-did-direct,'.dpp_h($ii['extension']).',1,'.dpp_h($options['lang']).'">'.dpp_h($ii['extension']).' '.dpp_h($ii['name']).'</option>';
	}
	$dropOptions.='</optgroup>';
	
}	

?>
<div style="border-radius: 10px; background-color:#F5F5F5; margin: 10px; padding: 10px;">
  <div class="row dpviz-toolbar-row">
    
    <!-- Left Side: Reload & Highlight -->
    <div class="col-sm-2 dpviz-toolbar-left">
      <div class="dpviz-toolbar-actions">
      
				<div style="position: relative; display: inline-block;">
					<!-- Hamburger button -->
					<button type="button" id="hamburgerBtn" class="btn btn-default" disabled>
						<i class="fa fa-bars"></i>
					</button>

					<!-- Dropdown menu -->
					<div class="dropdownMenu" id="dropdownMenu" style="display:none; position:absolute; top:35px; left:0; background:#f9f9f9; border:1px solid #ccc; border-radius:5px; min-width:150px; box-shadow:0 2px 5px rgba(0,0,0,0.2); padding:10px;">
						<button type="button" id="focus" class="btn btn-default" disabled>
							<i class="fa fa-magic"></i> <?php echo _('Highlight Paths'); ?>
						</button>
						<button type="button" id="sanitizeBtn" class="btn btn-default" disabled>
							<i class="fa fa-eye-slash"></i> <?php echo _('Sanitize Labels'); ?>
						</button>
						<button class="btn btn-default" onclick="openModal('customTimeModal')">
								<i class="fa fa-clock-o"></i> <?php echo _('Simulate Date & Time'); ?>
						</button>
						<button type="button" style="display:none;" id="saveModalBtn" class="btn btn-default">
							<i class="fa fa-save"></i> <?php echo _('Save View'); ?>
						</button>
						
					</div>
				</div>
				
				
				<button type="button" class="btn btn-default" id="reloadButton" title="<?php echo _('Reload'); ?> (R)" disabled>
          <i class="fa fa-refresh"></i> <?php echo _('Reload'); ?> <span class="dpviz-key-hint">[R]</span>
        </button>

      </div>
    </div>

    <!-- Middle: Dialplan -->
		<div class="col-sm-7 dpviz-toolbar-middle">
			<div class="input-group dpviz-toolbar-group">

				<!-- Label -->
				<div class="input-group-label dpviz-toolbar-label">
					<i class="fa fa-sitemap" aria-hidden="true"></i>
					<span id="dialplanLabel" style="margin-left:5px;"></span>
				</div>

				<!-- Select -->
				<select id="dialPlan" class="form-control">
					<option value=""><?php echo _('Choose Dial Plan'); ?></option>
					<?php echo $dropOptions; ?>
				</select>

				<!-- Buttons -->
				<button id="prevBtn" class="btn btn-default btn-sm" title="<?php echo _('Previous'); ?> (&#8592;)">
					<i class="fa fa-chevron-left"></i>
				</button>
				<button id="nextBtn" class="btn btn-default btn-sm" title="<?php echo _('Next'); ?> (&#8594;)">
					<i class="fa fa-chevron-right"></i>
				</button>
				<?php 
				if (is_array($options['sections']) && (in_array('*',$options['sections']) || count($options['sections']) > 1) ){ ?>
					<button id="addNewDestBtn" class="btn btn-default btn-sm" title="<?php echo _('New Destination'); ?>">
						<i class="fa fa-plus"></i>
					</button>
				<?php } ?>
			</div>
		</div>
    <!-- Right Side: Export -->
    <div class="col-sm-3 dpviz-toolbar-right dpviz-export-col">
      <div class="dpviz-export-shell">
        <button id="downloadButton" type="button" class="btn btn-default" aria-expanded="false" disabled title="<?php echo _('Export'); ?>">
          <i class="fa fa-download"></i>
        </button>
        <div id="exportPanel" class="dpviz-export-panel" hidden>
          <label for="filenameInput" class="dpviz-export-label"><?php echo _('Filename'); ?></label>
          <input type="text" id="filenameInput" name="nohistory" autocomplete="off" value="" class="form-control" disabled>

          <label for="exportType" class="dpviz-export-label"><?php echo _('Format'); ?></label>
          <select id="exportType" class="form-control" disabled>
            <option value="png-2"><?php echo _('Standard'); ?> PNG</option>
            <option value="png-4"><?php echo _('High'); ?> PNG</option>
            <option value="png-8"><?php echo _('Super'); ?> PNG</option>
            <option value="svg">SVG</option>
          </select>

          <button id="exportConfirmButton" type="button" class="btn btn-default" disabled>
            <i class="fa fa-download"></i> <?php echo _('Download'); ?>
          </button>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
let lastSearchTerm = '';
$(document).ready(function() {
	
	
	
	$('#dialPlan').select2({
    placeholder: "<?php echo _('Choose Dial Plan'); ?>",
    dropdownAutoWidth: true,
    width: '100%',
    maximumSelectionLength: 20,
    dropdownCssClass: "custom-dropdown",
    dropdownParent: $("body"),
	});


	// Store search term right before selection
	$('#dialPlan').on('select2:selecting', function () {
			const $searchInput = $('.select2-search__field');
			if ($searchInput.length) {
					lastSearchTerm = $searchInput.val();
			}
	});

	// Restore last search term and focus when dropdown opens
	$('#dialPlan').on('select2:open', function () {
			function restoreAndFocus() {
					const $searchField = $('.select2-container--open .select2-search__field');
					if (!$searchField.length) {
							requestAnimationFrame(restoreAndFocus);
							return;
					}

					// Restore last search term once
					if (lastSearchTerm && $searchField.val() !== lastSearchTerm) {
							// Delay to ensure internal handlers are attached
							setTimeout(() => {
									$searchField.val(lastSearchTerm).trigger('input');
							}, 0);
					}

					// Focus cursor after restoring
					$searchField.focus();

					// Add clear button if not already present
					if ($searchField.parent().find('.select2-search__clear').length === 0) {
							const $clearBtn = $('<span class="select2-search__clear">x</span>');

							$clearBtn.on('click', function () {
									$searchField.val('').trigger('input').focus();
							});

							$searchField.wrap('<div class="select2-search__field-wrapper" style="position: relative;"></div>');
							$searchField.parent().append($clearBtn);
					}
			}

			restoreAndFocus();
});


	$('#dialPlan').on('select2:select', function (e) {
		const selectedId = e.params.data.id;
		const selectedText = e.params.data.text;

		let cleaned = selectedText.replace(/\s\[.*?\]/, '').replace(/(\w+)s\b/, '$1').trim();
		cleaned = cleaned.replace(/\s+/g, ' ').trim();

		const optionElement = e.params.data.element;
		const $option = $(optionElement);
		const optgroup = $option.parent('optgroup');
		const optgroupLabel = optgroup.length ? optgroup.attr('label') : null;

		const dialplanLabel = document.getElementById('dialplanLabel');
		const reloadButton = document.getElementById('reloadButton');
		const hamburgerButton = document.getElementById('hamburgerBtn');
		const focusButton = document.getElementById('focus');
		const sanitizeButton = document.getElementById('sanitizeBtn');
		const filenameInput = document.getElementById('filenameInput');
		const downloadButton = document.getElementById('downloadButton');
		const savedDescription = document.getElementById('savedDescription');
		const viewId = document.getElementById('viewId');

		updateExportFilename(selectedText, optionElement);

		reloadButton.disabled = false;
		hamburgerButton.disabled = false;
		focusButton.disabled = false;
		sanitizeButton.disabled = false;
		setExportControlsEnabled(true);
		resetFocusMode();
		
		let id= '', ext = '', jump = '', skips = [];

	if ($option.length && $option.data('skips') !== undefined) {
		document.getElementById('saveModalBtn').style.display = 'block';
		let id = $option.data('id');
		skips = [...$option.data('skips')];
		

		const parts = selectedId.split('|');
		if (parts.length >= 2) {
				ext = parts[0];
				jump = parts[1];
			} else {
				ext = selectedId;
			}
		
		viewId.value=id;
		savedDescription.value=selectedText;
		
	} else {
		// Fallback
		if (selectedId.includes('|')) {
			[ext, jump] = selectedId.split('|');
		} else {
			ext = selectedId;
		}
		skips = [];
		savedDescription.value='';
		viewId.value='';
	}

	generateVisualization(ext, jump, skips);
	
	});

});


// getVisibleOptions respects lastSearchTerm while the dropdown is closed
function getVisibleOptions($el) {
  let searchTerm = lastSearchTerm || '';

   let $options = $el.find('option:not(:disabled)').filter(function() {
    return $(this).val() !== '';
  });

  if (searchTerm.length) {
    let lower = searchTerm.toLowerCase();
    $options = $options.filter(function() {
      return $(this).text().toLowerCase().includes(lower);
    });
  }

  return $options.map(function() { return this.value; }).get();
}

// Apply selection and trigger proper select2:select with correct data
function applySelection($el, value) {
  const $option = $el.find('option[value="' + value + '"]');
  if (!$option.length) return;

  const dataObj = { 
    id: $option.val(), 
    text: $option.text(), 
    element: $option[0] 
  };

  $el.val(value).trigger('change'); // normal change event
  $el.trigger({ type: 'select2:select', params: { data: dataObj } });
}

function select2Next($el) {
  const visible = getVisibleOptions($el);
  if (!visible.length) return;

  const currentVal = $el.val();
  let idx = visible.indexOf(currentVal);
  let nextIndex = (idx + 1 + visible.length) % visible.length;
  applySelection($el, visible[nextIndex]);
}

function select2Prev($el) {
  const visible = getVisibleOptions($el);
  if (!visible.length) return;

  const currentVal = $el.val();
  let idx = visible.indexOf(currentVal);
  let prevIndex = (idx - 1 + visible.length) % visible.length;
  applySelection($el, visible[prevIndex]);
}

// Buttons
$('#nextBtn').on('click', function() {
  select2Next($('#dialPlan'));
});

$('#prevBtn').on('click', function() {
  select2Prev($('#dialPlan'));
});

<?php
	$lang = isset($options['lang']) ? $options['lang'] : 'en';
	// json_encode, not addslashes: it emits the surrounding quotes and escapes
	// the sequences that actually matter inside a <script> block
	echo 'const currentLang = ' . json_encode($lang) . ';';
	echo 'var exportPrefix = ' . json_encode(trim(isset($options['exportprefix']) ? $options['exportprefix'] : '')) . ';';
?>

function sanitizeFilename(filename) {
    filename = (filename || '').replace(/[\/\:*?"<>|]/g, '_').replace(/\s+/g, '_');

    return filename.split('').filter(function (ch) {
        var code = ch.charCodeAt(0);
        return code >= 32 && code !== 127;
    }).join('').trim();
}

function applyExportPrefix(filename) {
    var base = sanitizeFilename(filename || '');
    var prefix = sanitizeFilename(exportPrefix || '');

    if (!prefix) {
        return base;
    }
    if (!base) {
        return prefix;
    }
    if (base === prefix || base.indexOf(prefix + '_') === 0) {
        return base;
    }

    return prefix + '_' + base;
}

function updateExportFilename(selectedText, optionElement) {
    var filenameInput = document.getElementById('filenameInput');
    var dialplanLabel = document.getElementById('dialplanLabel');
    var optionNode = optionElement || ($('#dialPlan').find('option:selected').get(0) || null);
    var optionText = selectedText || (optionNode ? optionNode.text : '');

    if (!filenameInput || !optionNode || !optionText) {
        return;
    }

    var cleaned = optionText.replace(/\s\[.*?\]/, '').replace(/(\w+)s\b/, '$1').trim();
    cleaned = cleaned.replace(/\s+/g, ' ').trim();

    var $option = $(optionNode);
    var $optgroup = $option.parent('optgroup');
    var optgroupLabel = $optgroup.length ? $optgroup.attr('label') : null;

    if (optgroupLabel) {
        var label = optgroupLabel.replace(/\s\[.*?\]/, '').replace(/(\w+)s\b/, '$1').trim();
        if (dialplanLabel) {
            dialplanLabel.textContent = label;
        }
        filenameInput.value = applyExportPrefix(label + '_' + cleaned);
        sessionStorage.setItem('selectedName', label + ': ' + optionText);
    } else {
        if (dialplanLabel) {
            dialplanLabel.textContent = '';
        }
        filenameInput.value = applyExportPrefix(optionText);
    }
}

function setExportControlsEnabled(enabled) {
  const filenameInput = document.getElementById('filenameInput');
  const downloadButton = document.getElementById('downloadButton');
  const exportType = document.getElementById('exportType');
  const exportConfirmButton = document.getElementById('exportConfirmButton');
  const exportPanel = document.getElementById('exportPanel');

  if (filenameInput) filenameInput.disabled = !enabled;
  if (downloadButton) downloadButton.disabled = !enabled;
  if (exportType) exportType.disabled = !enabled;
  if (exportConfirmButton) exportConfirmButton.disabled = !enabled;

  if (!enabled && exportPanel && downloadButton) {
    exportPanel.setAttribute('hidden', 'hidden');
    downloadButton.setAttribute('aria-expanded', 'false');
  }
}

function dpvizExportImage(scale, onComplete) {
  const container = document.querySelector('#vizContainer');
  if (!container) {
    alert(<?php echo json_encode(_('Container not found!')); ?>);
    if (typeof onComplete === 'function') onComplete(false);
    return;
  }

  const clone = container.cloneNode(true);
  const svg = clone.querySelector('svg');
  if (svg) svg.style.transform = 'none';

  clone.style.position = 'absolute';
  clone.style.left = '-99999px';
  document.body.appendChild(clone);

  html2canvas(clone, {
    scale: scale || 2,
    useCORS: true,
    allowTaint: true,
    backgroundColor: '#ffffff'
  }).then(function (canvas) {
    const input = document.getElementById('filenameInput');
    const filename = ((input && input.value ? input.value.trim() : '') || 'export') + '.png';
    const link = document.createElement('a');
    link.href = canvas.toDataURL('image/png');
    link.download = filename;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    document.body.removeChild(clone);
    if (typeof onComplete === 'function') onComplete(true);
  }).catch(function (error) {
    console.error('Export failed:', error);
    if (clone.parentNode) document.body.removeChild(clone);
    if (typeof onComplete === 'function') onComplete(false);
  });
}

function dpvizExportCleanedSVG(svgElement, filename) {
  const clonedSVG = svgElement.cloneNode(true);
  clonedSVG.querySelectorAll('a').forEach(function (link) {
    const parent = link.parentNode;
    while (link.firstChild) parent.insertBefore(link.firstChild, link);
    parent.removeChild(link);
  });

  const svgData = new XMLSerializer().serializeToString(clonedSVG);
  const blob = new Blob([svgData], { type: 'image/svg+xml;charset=utf-8' });
  const url = URL.createObjectURL(blob);
  const anchor = document.createElement('a');
  anchor.href = url;
  anchor.download = filename && filename.slice(-4) === '.svg' ? filename : filename + '.svg';
  document.body.appendChild(anchor);
  anchor.click();
  document.body.removeChild(anchor);
  URL.revokeObjectURL(url);
}

function dpvizHandleSVGExport(onComplete) {
  const svgElement = document.querySelector('#vizContainer svg');
  if (!svgElement) {
    alert(<?php echo json_encode(_('SVG not found!')); ?>);
    if (typeof onComplete === 'function') onComplete(false);
    return;
  }

  const input = document.getElementById('filenameInput');
  const filename = ((input && input.value ? input.value.trim() : '') || 'graph') + '.svg';
  dpvizExportCleanedSVG(svgElement, filename);
  if (typeof onComplete === 'function') onComplete(true);
}

document.addEventListener('DOMContentLoaded', () => {
  const downloadButton = document.getElementById('downloadButton');
  const exportPanel = document.getElementById('exportPanel');
  const exportType = document.getElementById('exportType');
  const exportConfirmButton = document.getElementById('exportConfirmButton');
  const filenameInput = document.getElementById('filenameInput');

  if (downloadButton && exportPanel) {
    downloadButton.addEventListener('click', function (e) {
      e.preventDefault();
      if (downloadButton.disabled) return;

      const isHidden = exportPanel.hasAttribute('hidden');
      if (isHidden) {
        exportPanel.removeAttribute('hidden');
        downloadButton.setAttribute('aria-expanded', 'true');
        if (filenameInput) {
          filenameInput.focus();
          filenameInput.select();
        }
      } else {
        exportPanel.setAttribute('hidden', 'hidden');
        downloadButton.setAttribute('aria-expanded', 'false');
      }
    });
  }

  if (exportConfirmButton && exportType) {
    exportConfirmButton.addEventListener('click', function () {
      if (exportConfirmButton.disabled) return;

      if (filenameInput) {
        filenameInput.value = applyExportPrefix(filenameInput.value || '');
      }

      const originalButtonHtml = exportConfirmButton.innerHTML;
      exportConfirmButton.disabled = true;
      exportConfirmButton.textContent = <?php echo json_encode(_('Downloading...')); ?>;

      function finishExport(started) {
        window.setTimeout(function () {
          exportConfirmButton.disabled = false;
          exportConfirmButton.innerHTML = originalButtonHtml;

          if (started && exportPanel && downloadButton) {
            exportPanel.setAttribute('hidden', 'hidden');
            downloadButton.setAttribute('aria-expanded', 'false');
          }
        }, started ? 180 : 0);
      }

      const selectedType = exportType.value;
      const runExport = function () {
        if (selectedType === 'svg') {
          dpvizHandleSVGExport(finishExport);
        } else {
          const scale = parseInt(selectedType.split('-')[1], 10) || 2;
          dpvizExportImage(scale, finishExport);
        }
      };

      if (window.requestAnimationFrame) {
        window.requestAnimationFrame(function () {
          window.setTimeout(runExport, 30);
        });
      } else {
        window.setTimeout(runExport, 30);
      }
    });
  }
});

$(document).on('click', '#addNewDestBtn', function() {
  const vizGraph = document.getElementById('vizGraph');
	const vizHeader = document.getElementById('vizHeader');
  const dialplanLabel = document.getElementById('dialplanLabel');
  const reloadButton = document.getElementById('reloadButton');
  const hamburgerButton = document.getElementById('hamburgerBtn');
  const focusButton = document.getElementById('focus');
  const sanitizeButton = document.getElementById('sanitizeBtn');
  const filenameInput = document.getElementById('filenameInput');
  const downloadButton = document.getElementById('downloadButton');
  const savedDescription = document.getElementById('savedDescription');
  const viewId = document.getElementById('viewId');
	const saveModalBtn = document.getElementById('saveModalBtn');
	const recordingModal = document.getElementById('recordingmodal');
	const customtimemodal = document.getElementById('customTimeModal');
  const $dialPlan = $('#dialPlan');

  // Clear the visualization
  vizGraph.innerHTML = '';
	vizHeader.innerHTML = '';

  // Reset text fields / labels
  dialplanLabel.textContent = '';
  filenameInput.value = '';
  savedDescription.value = '';
  viewId.value = '';

  // Reset buttons and states
  reloadButton.disabled = true;
  hamburgerButton.disabled = true;
  focusButton.disabled = true;
  sanitizeButton.disabled = true;
  setExportControlsEnabled(false);
  saveModalBtn.style.display = 'none';
	recordingModal.style.display = 'none';
	customtimemodal.style.display = 'none';

  // Clear stored session name
  sessionStorage.removeItem('selectedName');

  // Reset focus / mode
  if (typeof resetFocusMode === 'function') resetFocusMode();

  // Reset the Select2 dropdown back to placeholder
  if ($dialPlan.length) {
    $dialPlan.val(null).trigger('change');
  }

  // Open the modal
  openNewDestinationModal();
});

</script>
