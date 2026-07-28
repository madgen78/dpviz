<?php if (!defined('FREEPBX_IS_AUTH')) { exit(_('No direct script access allowed')); }; 
$modinfo = \FreePBX::Modules()->getInfo('dpviz');
$ver = isset($modinfo['dpviz']['version']) ? $modinfo['dpviz']['version'] : '0.0.0';
?>
<!-- Feedback Modal -->
<div id="feedbackModal" class="feedback-modal">
	<div class="feedback-modal-content">
		<div class="feedback-modal-header modal-header-unified">
			<h2 class="modal-title-unified"><i class="fa fa-commenting"></i> <?php echo _('Feedback'); ?></h2>
			<button class="feedback-close modal-close-btn-unified" id="closeFeedbackModal">&times;</button>
		</div>
		<form id="feedbackForm" class="modal-body-unified dpviz-feedback-body">
			<label for="fbMessage" class="label-with-help">
				<?php echo _('Your Feedback'); ?>:
				<span class="help-text"><?php echo _('Provide any comments or suggestions (up to 500 characters).'); ?></span>
			</label>
			<textarea id="fbMessage" name="message" rows="7" maxlength="500" required></textarea>

			<label for="fbEmail" class="label-with-help">
				<?php echo _('Email (optional)'); ?>:
				<span class="help-text"><?php echo _('Provide your email if you would like a response.'); ?></span>
			</label>
			<input type="email" id="fbEmail" name="email" />

			<div class="feedback-actions">
				<button class="btn btn-default dpviz-btn-secondary" type="reset"><?php echo _('Reset'); ?></button>
				<button class="btn btn-default" type="submit"><?php echo _('Submit'); ?></button>
			</div>
			<input type="hidden" name="lang" value="<?php echo preg_replace('/\.UTF8$/i', '', setlocale(LC_TIME, 0)); ?>">
		</form>
	</div>
</div>


<div class="display no-border">
	<div class="row">
		<div class="col-sm-12">
			<div class="fpbx-container">		
				<!--check for updates-->
				<div class="element-container">
					<div class="row">
						<div class="col-md-12">
							<div class="dpviz-settings-actions">
								<div class="dpviz-settings-actions-row dpviz-settings-actions-row-top">
									<div class="dpviz-settings-actions-col-left">
										<button id="openWhatsNewModal" type="button" class="btn btn-default"><?php echo _("What's New"); ?></button>
										<div class="dpviz-settings-icons">
											<a href="https://github.com/madgen78/dpviz/" class="btn btn-default" title="<?php echo _('Open the GitHub repository'); ?>" target="_blank"><i class="fa fa-github"></i> GitHub</a>
											<?php
												if (version_compare(get_framework_version(), '14.0.0', '>')) {
												echo '<button id="openFeedbackModal" type="button" class="btn btn-default" title="' . _('Give Feedback') . '"><i class="fa fa-commenting"></i> ' . _('Feedback') . '</button>';
												}
											?>
											<a href="https://buymeacoffee.com/adamvolchko" class="dpviz-coffee-link" title="<?php echo _('Buy me a coffee'); ?>" target="_blank" rel="noopener noreferrer"><i class="fa fa-coffee"></i><span class="sr-only"><?php echo _('Buy me a coffee'); ?></span></a>
										</div>
									</div>
									<div class="dpviz-settings-actions-col-right"></div>
								</div>
								<div class="dpviz-settings-actions-row dpviz-settings-actions-row-bottom">
									<div class="dpviz-settings-actions-col-left">
										<button id="check-update-btn" class="btn btn-default"><?php echo _('Check for Updates'); ?></button>
									</div>
									<div class="dpviz-settings-actions-col-right">
										<div id="update-result"><div><?php echo _('Current version'); ?>: <?php echo $ver; ?> </div></div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<form id="dpvizForm" action="ajax.php?module=dpviz&command=save_options" method="post">
				<!--autoplay-->
				<div class="element-container">
					<div class="row">
						<div class="col-md-12">
							<div class="row">
								<div class="form-group">
									<div class="col-md-3">
										<label class="control-label" for="autoplay"><?php echo _("Auto-play audio"); ?></label>
										<i class="fa fa-question-circle fpbx-help-icon" data-for="autoplay"></i>
									</div>
									<div class="col-md-9 radioset">
										<input type="radio" name="autoplay" id="autoplayyes" value="1" <?php echo ($options['autoplay']?"CHECKED":""); ?>>
										<label for="autoplayyes"><?php echo _("Yes"); ?></label>
										<input type="radio" name="autoplay" id="autoplayno" value="0" <?php echo ($options['autoplay']?"":"CHECKED"); ?>>
										<label for="autoplayno"><?php echo _("No"); ?></label>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-12">
							<span id="autoplay-help" class="help-block fpbx-help-block"><?php echo _("Automatically play audio when a node with audio is selected."); ?></span>
						</div>
					</div>
				</div>
				<!--END autoplay-->
				<!--datetime-->
				<div class="element-container">
					<div class="row">
						<div class="col-md-12">
							<div class="row">
								<div class="form-group">
									<div class="col-md-3">
										<label class="control-label" for="datetime"><?php echo _("Date & Time Stamp"); ?></label>
										<i class="fa fa-question-circle fpbx-help-icon" data-for="datetime"></i>
									</div>
									<div class="col-md-9 radioset">
										<input type="radio" name="datetime" id="datetimeyes" value="1" <?php echo ($options['datetime']?"CHECKED":""); ?>>
										<label for="datetimeyes"><?php echo _("Yes"); ?></label>
										<input type="radio" name="datetime" id="datetimeno" value="0" <?php echo ($options['datetime']?"":"CHECKED"); ?>>
										<label for="datetimeno"><?php echo _("No"); ?></label>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-12">
							<span id="datetime-help" class="help-block fpbx-help-block"><?php echo _("Displays the date and time on the graph."); ?></span>
						</div>
					</div>
				</div>
				<!--END datetime-->
				<!--panzoom-->
				<div class="element-container">
					<div class="row">
						<div class="col-md-12">
							<div class="row">
								<div class="form-group">
									<div class="col-md-3">
										<label class="control-label" for="panzoom"><?php echo _("Pan & Zoom");; ?></label>
										<i class="fa fa-question-circle fpbx-help-icon" data-for="panzoom"></i>
									</div>
									<div class="col-md-9 radioset">
										<input type="radio" name="panzoom" id="panzoomyes" value="1" <?php echo ($options['panzoom']?"CHECKED":""); ?>>
										<label for="panzoomyes"><?php echo _("Yes"); ?></label>
										<input type="radio" name="panzoom" id="panzoomno" value="0" <?php echo ($options['panzoom']?"":"CHECKED"); ?>>
										<label for="panzoomno"><?php echo _("No"); ?></label>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-12">
							<span id="panzoom-help" class="help-block fpbx-help-block"><?php echo _("Allows you to use pan and zoom functions. Click and hold to pan, and use the mouse wheel to zoom."); ?></span>
						</div>
					</div>
				</div>
				<!--END panzoom-->
				<!--horizontal-->
				<div class="element-container">
					<div class="row">
						<div class="col-md-12">
							<div class="row">
								<div class="form-group">
									<div class="col-md-3">
										<label class="control-label" for="horizontal"><?php echo _("Horizontal Layout"); ?></label>
										<i class="fa fa-question-circle fpbx-help-icon" data-for="horizontal"></i>
									</div>
									<div class="col-md-9 radioset">
										<input type="radio" name="horizontal" id="horizontalyes" value="1" <?php echo ($options['horizontal']?"CHECKED":""); ?>>
										<label for="horizontalyes"><?php echo _("Yes"); ?></label>
										<input type="radio" name="horizontal" id="horizontalno" value="0" <?php echo ($options['horizontal']?"":"CHECKED"); ?>>
										<label for="horizontalno"><?php echo _("No"); ?></label>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-12">
							<span id="horizontal-help" class="help-block fpbx-help-block"><?php echo _("Displays the dial plan in a horizontal layout."); ?></span>
						</div>
					</div>
				</div>
				<!--END horizontal-->
				<!--displaydestinations-->
				<div class="element-container">
					<div class="row">
						<div class="col-md-12">
							<div class="row">
								<div class="form-group">
									<div class="col-md-3">
										<label class="control-label" for="displaydestinations"><?php echo _("Display Destinations for Inbound Routes"); ?></label>
										<i class="fa fa-question-circle fpbx-help-icon" data-for="displaydestinations"></i>
									</div>
									<div class="col-md-9 radioset">
										<input type="radio" name="displaydestinations" id="displaydestinationsyes" value="1" <?php echo ($options['displaydestinations']?"CHECKED":""); ?>>
										<label for="displaydestinationsyes"><?php echo _("Yes"); ?></label>
										<input type="radio" name="displaydestinations" id="displaydestinationsno" value="0" <?php echo ($options['displaydestinations']?"":"CHECKED"); ?>>
										<label for="displaydestinationsno"><?php echo _("No"); ?></label>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-12">
							<span id="displaydestinations-help" class="help-block fpbx-help-block"><?php echo _("Inbound Routes will show the destination."); ?></span>
						</div>
					</div>
				</div>
				<!--END displaydestinations-->
				<!--insertnode-->
				<div class="element-container">
					<div class="row">
						<div class="col-md-12">
							<div class="row">
								<div class="form-group">
									<div class="col-md-3">
										<label class="control-label" for="insertnode"><?php echo _("Display Insert and Add Selection Nodes"); ?></label>
										<i class="fa fa-question-circle fpbx-help-icon" data-for="insertnode"></i>
									</div>
									<div class="col-md-9 radioset">
										<input type="radio" name="insertnode" id="insertnodeyes" value="1" <?php echo ($options['insertnode']?"CHECKED":""); ?>>
										<label for="insertnodeyes"><?php echo _("Yes"); ?></label>
										<input type="radio" name="insertnode" id="insertnodeno" value="0" <?php echo ($options['insertnode']?"":"CHECKED"); ?>>
										<label for="insertnodeno"><?php echo _("No"); ?></label>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-12">
							<span id="insertnode-help" class="help-block fpbx-help-block"><?php echo _("Displays a + between nodes. Clicking it creates a new destination and automatically links it to the current destination. Also Displays Add Selection (IVRs) and Add Entry (Dynamic Routes) "); ?></span>
						</div>
					</div>
				</div>
				<!--END insertnode-->
				<!--inuseby-->
				<div class="element-container">
					<div class="row">
						<div class="col-md-12">
							<div class="row">
								<div class="form-group">
									<div class="col-md-3">
										<label class="control-label" for="inuseby"><?php echo _("Display In Use By Nodes"); ?></label>
										<i class="fa fa-question-circle fpbx-help-icon" data-for="inuseby"></i>
									</div>
									<div class="col-md-9 radioset">
										<input type="radio" name="inuseby" id="inusebyyes" value="1" <?php echo ($options['inuseby']?"CHECKED":""); ?>>
										<label for="inusebyyes"><?php echo _("Yes"); ?></label>
										<input type="radio" name="inuseby" id="inusebyno" value="0" <?php echo ($options['inuseby']?"":"CHECKED"); ?>>
										<label for="inusebyno"><?php echo _("No"); ?></label>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-12">
							<span id="inuseby-help" class="help-block fpbx-help-block"><?php echo _("Display nodes where the selected destination is used."); ?></span>
						</div>
					</div>
				</div>
				<!--END inuseby-->
				<!--combineQueueRing node-->
				<div class="element-container">
					<div class="row">
						<div class="col-md-12">
							<div class="row">
								<div class="form-group">
									<div class="col-md-3">
										<label class="control-label" for="combineQueueRing"><?php echo _("Shared extension node handling"); ?></label>
										<i class="fa fa-question-circle fpbx-help-icon" data-for="combineQueueRing"></i>
									</div>
									<div class="col-md-9 radioset">
											<input type="radio" name="combineQueueRing" id="combineQueueRingNone" value="0" <?php echo ($options['combineQueueRing'] == 0 ? "CHECKED" : ""); ?>>
											<label for="combineQueueRingNone"><?php echo _("None"); ?></label>

											<input type="radio" name="combineQueueRing" id="combineQueueRingQueueRing" value="1" <?php echo ($options['combineQueueRing'] == 1 ? "CHECKED" : ""); ?>>
											<label for="combineQueueRingQueueRing"><?php echo _("Queues and Ring Groups Only"); ?></label>

											<input type="radio" name="combineQueueRing" id="combineQueueRingAll" value="2" <?php echo ($options['combineQueueRing'] == 2 ? "CHECKED" : ""); ?>>
											<label for="combineQueueRingAll"><?php echo _("All Destinations"); ?></label>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-12">
							<span id="combineQueueRing-help" class="help-block fpbx-help-block"><?php echo _("\"None\" displays individual extension nodes. \"Queues and Ring Groups Only\" combines them into one node. \"All\" merges all destinations into a single extension node."); ?></span>
						</div>
					</div>
				</div>
				<!--END combineQueueRing-->
				
				
				<!--allowlist node-->
				<div class="element-container">
					<div class="row">
						<div class="col-md-12">
							<div class="row">
								<div class="form-group">
									<div class="col-md-3">
										<label class="control-label" for="allowlist"><?php echo _("Show Allowlist"); ?></label>
										<i class="fa fa-question-circle fpbx-help-icon" data-for="allowlist"></i>
									</div>
									<div class="col-md-9 radioset">
											<?php if (\FreePBX::Modules()->checkStatus('allowlist')){ ?>
													<input type="radio" name="allowlist" id="allowlistyes" value="1" <?php echo ($options['allowlist']?"CHECKED":""); ?>>
													<label for="allowlistyes"><?php echo _("Yes"); ?></label>
											<?php } ?>
											<input type="radio" name="allowlist" id="allowlistno" value="0" <?php echo ($options['allowlist']?"":"CHECKED"); ?>>
											<label for="allowlistno"><?php echo _("No"); ?></label>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-12">
							<?php if (\FreePBX::Modules()->checkStatus('allowlist')){ ?>
								<span id="allowlist-help" class="help-block fpbx-help-block"><?php echo _("Displays the Allowlist information and destination."); ?></span>
							<?php }else{ ?>
								<span id="allowlist-help" class="help-block fpbx-help-block"><?php echo _("Allowlist module is not installed."); ?></span>
							<?php } ?>
						</div>
					</div>
				</div>
				<!--END allowlist-->
				<!--blacklist node-->
				<div class="element-container">
					<div class="row">
						<div class="col-md-12">
							<div class="row">
								<div class="form-group">
									<div class="col-md-3">
										<label class="control-label" for="blacklist"><?php echo _("Show Blacklist"); ?></label>
										<i class="fa fa-question-circle fpbx-help-icon" data-for="blacklist"></i>
									</div>
									<div class="col-md-9 radioset">
										<input type="radio" name="blacklist" id="blacklistyes" value="1" <?php echo ($options['blacklist']?"CHECKED":""); ?>>
										<label for="blacklistyes"><?php echo _("Yes"); ?></label>
										<input type="radio" name="blacklist" id="blacklistno" value="0" <?php echo ($options['blacklist']?"":"CHECKED"); ?>>
										<label for="blacklistno"><?php echo _("No"); ?></label>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-12">
							<span id="blacklist-help" class="help-block fpbx-help-block"><?php echo _("Displays the Blacklist information and destination."); ?></span>
						</div>
					</div>
				</div>
				<!--END blacklist-->
				<!--queue_member_display-->
				<div class="element-container">
					<div class="row">
						<div class="col-md-12">
							<div class="row">
								<div class="form-group">
									<div class="col-md-3">
										<label class="control-label" for="queue_member_display"><?php echo _("Show Queue Agents"); ?></label>
										<i class="fa fa-question-circle fpbx-help-icon" data-for="queue_member_display"></i>
									</div>
									<div class="col-md-9 radioset">
											<input type="radio" name="queue_member_display" id="queue_member_displayY" value="1" <?php echo ($options['queue_member_display'] == 1 ? "CHECKED" : ""); ?>>
											<label for="queue_member_displayY"><?php echo _("Single"); ?></label>

											<input type="radio" name="queue_member_display" id="queue_member_displayC" value="2" <?php echo ($options['queue_member_display'] == 2 ? "CHECKED" : ""); ?>>
											<label for="queue_member_displayC"><?php echo _("Combine"); ?></label>

											<input type="radio" name="queue_member_display" id="queue_member_displayN" value="0" <?php echo ($options['queue_member_display'] == 0 ? "CHECKED" : ""); ?>>
											<label for="queue_member_displayN"><?php echo _("Hide"); ?></label>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-12">
							<span id="queue_member_display-help" class="help-block fpbx-help-block"><?php echo _("\"Single\" displays individual agent nodes. \"Combine\" displays all agents in a single node. \"Hide\" does not display queue agents."); ?></span>
						</div>
					</div>
				</div>
				<!--END queue_member_display-->
				<!--dynmembers-->
				<div class="element-container">
					<div class="row">
						<div class="col-md-12">
							<div class="row">
								<div class="form-group">
									<div class="col-md-3">
										<label class="control-label" for="dynmembers"><?php echo _("Show Dynamic Members for Queues"); ?></label>
										<i class="fa fa-question-circle fpbx-help-icon" data-for="dynmembers"></i>
									</div>
									<div class="col-md-9 radioset">
										<input type="radio" name="dynmembers" id="dynmembersyes" value="1" <?php echo ($options['dynmembers']?"CHECKED":""); ?>>
										<label for="dynmembersyes"><?php echo _("Yes"); ?></label>
										<input type="radio" name="dynmembers" id="dynmembersno" value="0" <?php echo ($options['dynmembers']?"":"CHECKED"); ?>>
										<label for="dynmembersno"><?php echo _("No"); ?></label>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-12">
							<span id="dynmembers-help" class="help-block fpbx-help-block"><?php echo _("Displays the list of dynamic agents currently assigned to the queues."); ?></span>
						</div>
					</div>
				</div>
				<!--END dynmembers-->
				<!--queue_penalty-->
				<div class="element-container">
					<div class="row">
						<div class="col-md-12">
							<div class="row">
								<div class="form-group">
									<div class="col-md-3">
										<label class="control-label" for="queue_penalty"><?php echo _("Show Queue Agent Penalties"); ?></label>
										<i class="fa fa-question-circle fpbx-help-icon" data-for="queue_penalty"></i>
									</div>
									<div class="col-md-9 radioset">
										<input type="radio" name="queue_penalty" id="queue_penaltyyes" value="1" <?php echo ($options['queue_penalty']?"CHECKED":""); ?>>
										<label for="queue_penaltyyes"><?php echo _("Yes"); ?></label>
										<input type="radio" name="queue_penalty" id="queue_penaltyno" value="0" <?php echo ($options['queue_penalty']?"":"CHECKED"); ?>>
										<label for="queue_penaltyno"><?php echo _("No"); ?></label>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-12">
							<span id="queue_penalty-help" class="help-block fpbx-help-block"><?php echo _("Displays the penalty value for queue agents."); ?></span>
						</div>
					</div>
				</div>
				<!--END queue_penalty-->
				<!--ring_member_display-->
				<div class="element-container">
					<div class="row">
						<div class="col-md-12">
							<div class="row">
								<div class="form-group">
									<div class="col-md-3">
										<label class="control-label" for="ring_member_display"><?php echo _("Show Ring Group Members"); ?></label>
										<i class="fa fa-question-circle fpbx-help-icon" data-for="ring_member_display"></i>
									</div>
									<div class="col-md-9 radioset">
											<input type="radio" name="ring_member_display" id="ring_member_displayY" value="1" <?php echo ($options['ring_member_display'] == 1 ? "CHECKED" : ""); ?>>
											<label for="ring_member_displayY"><?php echo _("Single"); ?></label>

											<input type="radio" name="ring_member_display" id="ring_member_displayC" value="2" <?php echo ($options['ring_member_display'] == 2 ? "CHECKED" : ""); ?>>
											<label for="ring_member_displayC"><?php echo _("Combine"); ?></label>

											<input type="radio" name="ring_member_display" id="ring_member_displayN" value="0" <?php echo ($options['ring_member_display'] == 0 ? "CHECKED" : ""); ?>>
											<label for="ring_member_displayN"><?php echo _("Hide"); ?></label>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-12">
							<span id="ring_member_display-help" class="help-block fpbx-help-block"><?php echo _("\"Single\" displays individual member nodes. \"Combine\" displays all members in a single node. \"Hide\" does not display members."); ?></span>
						</div>
					</div>
				</div>
				<!--END combineQueueRing-->
				<!--fmfm-->
				<div class="element-container">
					<div class="row">
						<div class="col-md-12">
							<div class="row">
								<div class="form-group">
									<div class="col-md-3">
										<label class="control-label" for="fmfm"><?php echo _("Show Find Me Follow Me for Extensions"); ?></label>
										<i class="fa fa-question-circle fpbx-help-icon" data-for="fmfm"></i>
									</div>
									<div class="col-md-9 radioset">
										<input type="radio" name="fmfm" id="fmfmyes" value="1" <?php echo ($options['fmfm']?"CHECKED":""); ?>>
										<label for="fmfmyes"><?php echo _("Yes"); ?></label>
										<input type="radio" name="fmfm" id="fmfmno" value="0" <?php echo ($options['fmfm']?"":"CHECKED"); ?>>
										<label for="fmfmno"><?php echo _("No"); ?></label>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-12">
							<span id="fmfm-help" class="help-block fpbx-help-block"><?php echo _("Displays Find Me Follow Me data for extensions.")?></span>
						</div>
					</div>
				</div>
				<!--END fmfm-->
				<!--extOptional-->
				<div class="element-container">
					<div class="row">
						<div class="col-md-12">
							<div class="row">
								<div class="form-group">
									<div class="col-md-3">
										<label class="control-label" for="extOptional"><?php echo _("Show Extension Optional Destinations"); ?></label>
										<i class="fa fa-question-circle fpbx-help-icon" data-for="extOptional"></i>
									</div>
									<div class="col-md-9 radioset">
										<input type="radio" name="extOptional" id="extOptionalyes" value="1" <?php echo ($options['extOptional']?"CHECKED":""); ?>>
										<label for="extOptionalyes"><?php echo _("Yes"); ?></label>
										<input type="radio" name="extOptional" id="extOptionalno" value="0" <?php echo ($options['extOptional']?"":"CHECKED"); ?>>
										<label for="extOptionalno"><?php echo _("No"); ?></label>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-12">
							<span id="extOptional-help" class="help-block fpbx-help-block"><?php echo _("Displays and follows the optional destinations (No Answer, Busy, Not Reachable) set for the extension in the Advanced tab."); ?></span>
						</div>
					</div>
				</div>
				<!--END extOptional-->
				<!--Minimal-->
				<div class="element-container">
					<div class="row">
						<div class="col-md-12">
							<div class="row">
								<div class="form-group">
									<div class="col-md-3">
										<label class="control-label" for="minimal"><?php echo _("Show Minimal View"); ?></label>
										<i class="fa fa-question-circle fpbx-help-icon" data-for="minimal"></i>
									</div>
									<div class="col-md-9 radioset">
										<input type="radio" name="minimal" id="minimalyes" value="1" <?php echo ($options['minimal']?"CHECKED":""); ?>>
										<label for="minimalyes"><?php echo _("Yes"); ?></label>
										<input type="radio" name="minimal" id="minimalno" value="0" <?php echo ($options['minimal']?"":"CHECKED"); ?>>
										<label for="minimalno"><?php echo _("No"); ?></label>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-12">
							<span id="minimal-help" class="help-block fpbx-help-block"><?php echo _("Shows (default) or hides the following types of nodes: Extensions, Queue Members, Ring Group Members, Recordings, Voicemail, and Voicemail Blasting Members."); ?></span>
						</div>
					</div>
				</div>
				<!--END minimal-->
				<!--Mouse Sensitivity-->
				<div class="element-container">
					<div class="row">
						<div class="col-md-12">
							<div class="row">
								<div class="form-group">

									<div class="col-md-3">
										<label class="control-label" for="mouseSens">
												<?php echo _("Mouse Wheel Zoom Sensitivity"); ?>
										</label>
										<span id="zoomValue" style="margin-left:10px; font-weight:bold;"></span>
										<i class="fa fa-question-circle fpbx-help-icon" data-for="mouseSens"></i>
									</div>
									<div class="col-md-9" style="display:flex; align-items:center;">
										<input type="range"
													 id="zoomSensitivity"
													 class="zoom-slider"
													 min="0.005"
													 max=".4"
													 step="0.005"
													 value="0.2">
										<button id="resetZoomSensitivity"
														type="button"
														class="btn btn-default btn-xs"
														style="margin-left: 15px;">
										<?php echo _("Reset"); ?>
										</button>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="row">
							<div class="col-md-12">
									<span id="mouseSens-help" class="help-block fpbx-help-block">
											<?php echo _("Controls mouse wheel zoom sensitivity. This preference is saved in your browser and does not affect other users."); ?>
									</span>
							</div>
					</div>
				</div>
				<!--END Mouse Sensitivity-->
				<!--exportprefix-->
				<div class="element-container">
					<div class="row">
						<div class="col-md-12">
							<div class="row">
								<div class="form-group">
									<div class="col-md-3">
										<label class="control-label" for="exportprefix"><?php echo _("Export Filename Prefix"); ?></label>
										<i class="fa fa-question-circle fpbx-help-icon" data-for="exportprefix"></i>
									</div>
									<div class="col-md-9">
										<input type="text" class="form-control" id="exportprefix" name="exportprefix" maxlength="60" value="<?php echo htmlspecialchars(isset($options['exportprefix']) ? $options['exportprefix'] : '', ENT_QUOTES); ?>">
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-12">
							<span id="exportprefix-help" class="help-block fpbx-help-block"><?php echo _("Optional text prepended to all exported filenames, for example a site or customer identifier."); ?></span>
						</div>
					</div>
				</div>
				<!--END exportprefix-->
					
				<div class="row">
					<div class="col-md-12 text-right">
						<button class="btn btn-primary" name="submit" id="saveButton" type="submit" data-saved-label="<?php echo _('Saved!'); ?>">
							<i class="fa fa-save"></i> <?php echo _('Save'); ?>
						</button>
						<div id="saveResponse"></div>
					</div>
				</div>
				</form>
			</div>
		</div>
	</div>
</div>

<script>
$(function () {
    $('#insertnodeyes').on('change', function () {
        if (this.checked) {
            $('#minimalno').prop('checked', true);
        }
    });

    $('#minimalyes').on('change', function () {
        if (this.checked) {
            $('#insertnodeno').prop('checked', true);
        }
    });

    var $form = $('#dpvizForm');
    if (!$form.length) {
        return;
    }

    var groups = [
        {
            id: 'dpvizgeneral',
            title: '<?php echo addslashes(_("Display & Navigation")); ?>',
            selectors: [
                'input[name="autoplay"]',
                'input[name="datetime"]',
                'input[name="panzoom"]',
                'input[name="horizontal"]',
                'input[name="displaydestinations"]'
            ]
        },
        {
            id: 'dpviznodes',
            title: '<?php echo addslashes(_("Destinations & Nodes")); ?>',
            selectors: [
                'input[name="insertnode"]',
                'input[name="inuseby"]',
                'input[name="combineQueueRing"]',
                'input[name="allowlist"]',
                'input[name="blacklist"]',
                'input[name="fmfm"]',
                'input[name="extOptional"]',
                'input[name="minimal"]'
            ]
        },
        {
            id: 'dpvizmembers',
            title: '<?php echo addslashes(_("Queues & Ring Groups")); ?>',
            selectors: [
                'input[name="queue_member_display"]',
                'input[name="dynmembers"]',
                'input[name="queue_penalty"]',
                'input[name="ring_member_display"]'
            ]
        },
        {
            id: 'dpvizexport',
            title: '<?php echo addslashes(_("Export & Interaction")); ?>',
            selectors: [
                'input[name="exportprefix"]',
                '#zoomSensitivity'
            ]
        }
    ];

    var moved = [];
    var $saveRow = $form.children('.row').last();

    $.each(groups, function (_, group) {
        var $title = $('<div>', { 'class': 'section-title', 'data-for': group.id });
        $title.append($('<h3>').html('<i class="fa fa-minus"></i> ' + group.title));

        var $section = $('<div>', { 'class': 'section', 'data-id': group.id });

        $.each(group.selectors, function (_, selector) {
            var $container = $form.find(selector).first().closest('.element-container');
            if (!$container.length) {
                return;
            }

            var el = $container.get(0);
            if ($.inArray(el, moved) !== -1) {
                return;
            }

            moved.push(el);
            $section.append($container);
        });

        if ($section.children().length) {
            $saveRow.before($title).before($section);
        }
    });

});
</script>
