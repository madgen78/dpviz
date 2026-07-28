<?php /* $Id */
if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed'); }
//	License for all code of this FreePBX module can be found in the license file inside the module directory
//	Copyright 2013 Schmooze Com Inc.
//  Copyright (C) 2011 Mikael Carlsson (mickecarlsson at gmail dot com)
//
$module = 'dpviz';
$info = \FreePBX::Modules()->getInfo($module);
$version = $info[$module]['version'];

$pageResolvedUser = 'unknown';
if (isset($_SESSION['AMP_user']) && is_object($_SESSION['AMP_user']) && !empty($_SESSION['AMP_user']->username)) {
	$pageResolvedUser = (string)$_SESSION['AMP_user']->username;
} elseif (!empty($_SERVER['PHP_AUTH_USER'])) {
	$pageResolvedUser = (string)$_SERVER['PHP_AUTH_USER'];
}

$hiddenWhatsNewVersion = '';
if ($pageResolvedUser !== 'unknown') {
	$hiddenWhatsNewVersion = trim((string)\FreePBX::Dpviz()->getConfig('whatsnew_hidden_version', $pageResolvedUser));
} elseif (isset($options['whatsnew_hidden_version'])) {
	$hiddenWhatsNewVersion = trim((string)$options['whatsnew_hidden_version']);
}

$hideWhatsNew = ($hiddenWhatsNewVersion !== '' && version_compare($hiddenWhatsNewVersion, $version, '==')) ? 1 : 0;
?>
<link rel="stylesheet" href="modules/dpviz/assets/css/select2.min.css"  />
<link rel="stylesheet" href="modules/dpviz/assets/css/dpviz.css?load_version=<?php echo $version; ?>" />

<script src="modules/dpviz/assets/js/html2canvas.min.js"></script>
<script src="modules/dpviz/assets/js/viz.min.js"></script>
<script src="modules/dpviz/assets/js/full.render.js"></script>
<script src="modules/dpviz/assets/js/focus.js"></script>
<script src="modules/dpviz/assets/js/select2.min.js"></script>
<script type="text/javascript">
//load graphviz
var viz = new Viz();
let isFocused = false;
let svgContainer = null;
let selectedNodeId = null;
let originalLinks = new Map();
let highlightedEdges = new Set(); // Track highlighted edges
var shouldShowWhatsNew = <?php echo $hideWhatsNew ? 'false' : 'true'; ?>;
var whatsNewVersion = <?php echo json_encode($version); ?>;
var whatsNewHiddenByServer = <?php echo $hideWhatsNew ? 'true' : 'false'; ?>;
const translations = {
	highlight: "<?php echo _('Highlight Paths'); ?>",
	remove: "<?php echo _('Remove Highlights'); ?>",
	checking: "<?php echo _('Checking...'); ?>",
	uptodate: "<?php echo _('You are up to date.'); ?>",
	available: "<?php echo _('available! Use Module Admin to update'); ?>",
	currentVersion: "<?php echo _('Current installed version'); ?>",
	fileNotFound: "<?php echo _('could not be found. To generate the file, simply go to the recording, select the \"convert to\" wav option, and click submit.'); ?>",
	recordingLabel: "<?php echo _('Recording'); ?>",
	noFilesLang: "<?php echo _('No files found for language:'); ?>",
	noVmFile: "<?php echo _('Greeting has not been recorded or is missing.'); ?>",
	copyFilename: "<?php echo _('Copy filename'); ?>",
	downloadFile: "<?php echo _('Download'); ?>",
	audioLabel: "<?php echo _('Audio'); ?>",
	viewSaved: "<?php echo _('View Saved Successfully'); ?>",
	viewDeleted: "<?php echo _('View Deleted Successfully'); ?>",
	sanitizeLabels: "<?php echo _('Sanitize Labels'); ?>",
	restoreLabels: "<?php echo _('Restore Labels'); ?>",
	enterFilename: "<?php echo _('Enter filename'); ?>",
	clickToZoom: "<?php echo _('Click graph to zoom'); ?>",
	feedbackSuccess: "<?php echo _('Thank you! Your feedback has been sent.'); ?>",
	feedbackError: "<?php echo _('Feedback submission failed. Please try again later.'); ?>",
	systemrecording: "<?php echo _('System Recording'); ?>",
	announcement: "<?php echo _('Announcement'); ?>",
	ivr: "<?php echo _('IVR'); ?>",
	voicemail: "<?php echo _('Voicemail'); ?>",
	ringgroup: "<?php echo _('Ring Group'); ?>",
	queues: "<?php echo _('Queue'); ?>",
	vmblast: "<?php echo _('Voicemail Blast'); ?>",
	pagegroups: "<?php echo _('Page Group'); ?>",
	dynroute: "<?php echo _('Dynamic Route'); ?>",
	queuecallback: "<?php echo _('Queue Callback'); ?>",
	newDestination: "<?php echo _('New Destination'); ?>",
	insertDestination: "<?php echo _('Insert New Destination'); ?>",
	customTimeSaved: "<?php echo _('Simulated date & time applied.'); ?>",
	customTimeRemoved: "<?php echo _('Simulated date & time cleared.'); ?>",
	noPermission: "<?php echo _('You do not have permission to create new destinations.'); ?>"
	
};
</script>
<meta charset="UTF-8">
<div class="container-fluid">
	<div class="display full-border">
		<h1><?php echo _("Dial Plan") .' Vizualizer'; ?></h1>
	</div>
	<div class="row">
		<div class="col-sm-12">
			<div class="fpbx-container">
			
				<ul class="nav nav-tabs" role="tablist">
					<li role="presentation" data-name="dpbox" class="active">
						<a href="#dpbox" aria-controls="dpbox" role="tab" data-toggle="tab">
							<i class="fa fa-sitemap"></i> <?php echo _("Dial Plan"); ?>
						</a>
					</li>
					<li role="presentation" data-name="navigation" class="change-tab">
						<a href="#navigation" aria-controls="navigation" role="tab" data-toggle="tab">
							<i class="fa fa-compass"></i> <?php echo _("Navigation & Usage"); ?>
						</a>
					</li>
					<li role="presentation" data-name="settings" class="change-tab">
						<a href="#settings" aria-controls="settings" role="tab" data-toggle="tab">
							<i class="fa fa-cog"></i> <?php echo _('Settings'); ?>
						</a>
					</li>
				</ul>
				<div class="tab-content display">
					<div role="tabpanel" id="dpbox" class="tab-pane active">
						<div id="vizToolbar">
							<?php require('views/toolbar.php');?>
						</div>
						<div id="vizSpinner">
							<div class="loader"></div>
							<h3 class="spinner-text"><?php echo _('Loading...'); ?></h3>
						</div>
						<div id="vizWrapper">
							
							<div id="overlay" onclick="closeModal()"></div>
							
							<div id="nodestmodal">
									<div class="modal-header-unified" id="nodestmodal-header">
											<span class="modal-title-unified" id="nodestmodal-title">Destination</span>
											<button class="modal-close-btn-unified" onclick="closeModal('nodestmodal')">&times;</button>
									</div>

									<div class="modal-body-unified" id="nodestmodal-displayname">
											<!-- dynamically injected content -->
									</div>
							</div>
							
							<!-- Recording Modal container -->
							<div id="recordingmodal">
									<div class="modal-header-unified" id="recordingmodal-header">
											<span class="modal-title-unified" id="recordingmodal-title">
													<i class="fa fa-music"></i> Recordings
											</span>
											<button class="modal-close-btn-unified" onclick="closeModal('recordingmodal')">&times;</button>
									</div>

									<div class="modal-body-unified" id="recording-displayname">
											
									</div>
									<div id="audioList"></div>
							</div>

							
							<!-- Saved View Modal container -->
							<div id="saveModal" class="savemodal">
								<div class="savemodal-content">
									<div class="modal-header-unified dpviz-save-header">
										<span class="modal-title-unified"><i class="fa fa-save"></i> <?php echo _('Save View'); ?></span>
										<button class="saveclose modal-close-btn-unified" type="button" onclick="closeSaveModal()">&times;</button>
									</div>

									<form id="saveViewForm" class="modal-body-unified dpviz-save-body">
										<label for="description" style="font-weight: bold; display: block; margin-bottom: 5px;"><?php echo _('Description'); ?>:</label>
										<input type="text" id="savedDescription" name="description" required>
										<div class="button-group">
											<button type="button" id="deleteViewBtn"><i class="fa fa-trash"></i> <?php echo _('Delete View'); ?></button>
											<button type="submit" id="saveviewbtn"><i class="fa fa-save"></i> <?php echo _('Save View'); ?></button>
										</div>
										<input type="hidden" id="viewId" name="id">
									</form>
								</div>
							</div>
						

							<!-- Custom Time Modal container -->						
							<div id="customTimeModal">
									<div class="modal-header-unified" id="customTimeModal-header">
											<span class="modal-title-unified">
													<i class="fa fa-clock-o"></i> <?php echo _('Simulate Date & Time'); ?>
											</span>
											<button class="modal-close-btn-unified" onclick="closeModal('customTimeModal')">&times;</button>
									</div>

									<div class="modal-body-unified">

											<div class="form-group" style="margin-bottom: 20px;">
													<label for="customDateTime" class="control-label"><?php echo _('Select Date & Time'); ?></label>
													<input type="datetime-local"
																 id="customDateTime"
																 name="customDateTime"
																 class="form-control"
																 style="max-width:260px;"
																 value="<?php echo $options['custom_datetime']; ?>">
													<small class="help-block" style="margin-top:6px;">
															<?php echo _('Click Reset to return to the real system time.'); ?>
													</small>
											</div>

											<div class="text-right" style="margin-top: 25px;">
													<button id="applyCustomDateTimeBtn" type="button" class="btn btn-primary" onclick="applyCustomDateTime()">
															<i class="fa fa-check"></i> <?php echo _('Apply'); ?>
													</button>
													<button type="button" class="btn btn-danger" onclick="resetCustomDateTime()">
															<i class="fa fa-times-circle"></i> <?php echo _('Reset'); ?>
													</button>
											</div>

									</div>
							</div>

							
							<div id="vizContainer" class="display full-border">
								<div id="vizHeader"><p><strong><?php echo _('Dial Plan Not Selected'); ?></strong><br><?php echo _('Use the dropdown to select a dial plan.'); ?></p></div>
								<div class="divider"></div>	
								<div id="vizGraph" class="grid-background"></div>
							</div>
						</div>
					</div>
					<div role="tabpanel" id="navigation" class="tab-pane">
						<?php require('views/nav.php');?>
					</div>
					<div role="tabpanel" id="settings" class="tab-pane">
						<?php require('views/options.php');?>
					</div>
				</div>
			</div>
		</div>
	</div>
							<!-- What's New Modal -->
							<div id="whatsNewModal" class="whatsnew-modal">
								<div class="whatsnew-modal-content">
									<div class="modal-header-unified dpviz-whatsnew-header" id="whatsNewModal-header">
										<span class="modal-title-unified"><i class="fa fa-bullhorn"></i> <?php echo sprintf(_("What's New in %s"), $version); ?></span>
										<button class="modal-close-btn-unified" id="closeWhatsNewModal" type="button">&times;</button>
									</div>

									<div class="modal-body-unified dpviz-whatsnew-body">
										<p class="dpviz-whatsnew-intro"><?php echo _('Here is a quick overview of the main improvements in this release.'); ?></p>

										<div class="dpviz-whatsnew-section">
											<h3><?php echo _('Time Conditions'); ?></h3>
											<ul>
												<li><?php echo _('A Time Condition pointed at a time group with no times defined no longer throws an error. It now shows "No times defined" on the arrows and follows No Match, which is what the dial plan actually does.'); ?></li>
												<li><?php echo _('Time Conditions that use a Calendar or Calendar Group now show the calendar name on the Match and No Match arrows again.'); ?></li>
												<li><?php echo _('Those arrows are now colored green for the path a call takes right now and red for the path it does not, the same way time group conditions already behaved. They stay black only when the state cannot be determined, such as when the Calendar module is disabled.'); ?></li>
												<li><?php echo _('Simulate Date & Time now evaluates calendars too, so you can check holiday routing before the date arrives.'); ?></li>
											</ul>
										</div>

										<div class="dpviz-whatsnew-section">
											<h3><?php echo _('Keyboard Shortcuts'); ?></h3>
											<ul>
												<li><?php echo _('Press R to reload the current graph.'); ?></li>
												<li><?php echo _('Use the Left and Right arrow keys to step to the previous or next dial plan.'); ?></li>
												<li><?php echo _('Press F to fit the graph back to the default pan and zoom.'); ?></li>
												<li><?php echo _('Press ? to jump straight to the Navigation & Usage page.'); ?></li>
												<li><?php echo _('Shortcuts work as soon as the page loads and pause automatically while you are typing, searching, or using a dialog.'); ?></li>
												<li><?php echo _('The Reload button shows an [R] hint, and the previous/next buttons show their shortcut keys on hover. A full list lives on the Navigation & Usage page.'); ?></li>
											</ul>
										</div>

										<div class="dpviz-whatsnew-section">
											<h3><?php echo _('Graph Navigation'); ?></h3>
											<ul>
												<li><?php echo _('The mouse wheel scrolls the page as usual until you click the graph to engage zoom. Click anywhere outside the graph to release it and scroll the page again. A brief "Click graph to zoom" hint appears if you wheel over the graph before engaging it.'); ?></li>
											</ul>
										</div>

										<div class="dpviz-whatsnew-section">
											<h3><?php echo _('Interface'); ?></h3>
											<ul>
												<li><?php echo _('The toolbar now scrolls with the page instead of floating, so it no longer overlaps the graph as you scroll.'); ?></li>
												<li><?php echo _('Nodes now have distinct colors where some previously shared the same one.'); ?></li>
											</ul>
										</div>

										<div class="dpviz-whatsnew-footer-clean">
											<div class="dpviz-whatsnew-left-clean" id="toggleWhatsNewPreference" tabindex="0" role="checkbox" aria-checked="false">
												<input type="checkbox" id="hideWhatsNewCheckbox" value="1">
												<span class="dpviz-whatsnew-toggletext-clean"><?php echo sprintf(_("Don't show this again for version %s"), $version); ?></span>
											</div>
											<div class="dpviz-whatsnew-right-clean">
												<?php // Same msgid as the Settings page link so the existing translations apply. ?>
												<a href="https://github.com/madgen78/dpviz/" class="dpviz-whatsnew-github" title="<?php echo _('Open the GitHub repository'); ?>" target="_blank" rel="noopener noreferrer"><i class="fa fa-github"></i> GitHub</a>
												<button type="button" class="btn btn-default" id="closeWhatsNewAction"><?php echo _('Close'); ?></button>
											</div>
										</div>
										</div>
									</div>
								</div>
							</div>

</div>
