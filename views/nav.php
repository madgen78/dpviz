<div class="dpviz-help-page">
	<div class="dpviz-help-hero">
		<h3><?php echo _('Navigation & Usage'); ?></h3>
		<p><?php echo _('Quick reference for exploring, testing, and exporting dial plans in DPViz.'); ?></p>
	</div>

	<div class="dpviz-help-grid">
		<section class="dpviz-help-card">
			<h4><i class="fa fa-keyboard-o"></i> <?php echo _('Keyboard Shortcuts'); ?></h4>
			<ul class="dpviz-help-list">
				<li><i class="fa fa-refresh"></i><span><kbd>R</kbd> &mdash; <?php echo _('Reload the current graph.'); ?></span></li>
				<li><i class="fa fa-arrows-h"></i><span><kbd>&larr;</kbd> / <kbd>&rarr;</kbd> &mdash; <?php echo _('Jump to the previous or next dial plan.'); ?></span></li>
				<li><i class="fa fa-arrows-alt"></i><span><kbd>F</kbd> &mdash; <?php echo _('Fit the graph: reset pan and zoom to the default view.'); ?></span></li>
				<li><i class="fa fa-question-circle"></i><span><kbd>?</kbd> &mdash; <?php echo _('Open this Navigation & Usage page.'); ?></span></li>
				<li><i class="fa fa-info-circle"></i><span><?php echo _('Shortcuts pause while typing in a field, searching the dial plan list, or when a dialog is open.'); ?></span></li>
			</ul>
		</section>

		<section class="dpviz-help-card">
			<h4><i class="fa fa-mouse-pointer"></i> <?php echo _('Navigation Basics'); ?></h4>
			<ul class="dpviz-help-list">
				<li><i class="fa fa-ban"></i><span><?php echo _('Exclude Node(s): Press Shift + left-click a node to exclude it and downstream paths. Click "+" to show the path again. Use "Reset" to restore the original dial plan.'); ?></span></li>
				<li><i class="fa fa-random"></i><span><?php echo _('Redraw from a Node: Press Ctrl (Cmd on macOS) + left-click a node to start from it. To revert, Ctrl/Cmd + left-click the parent node.'); ?></span></li>
				<li><i class="fa fa-bullseye"></i><span><?php echo _('Highlight Paths: Click Highlight Paths, then select a node or edge. Click Remove Highlights to clear.'); ?></span></li>
				<li><i class="fa fa-eye-slash"></i><span><?php echo _('Sanitize Labels: Click Sanitize Labels to hide node labels and the header for privacy. Click nodes afterwards to reveal them individually, or click Restore to show all labels again.'); ?></span></li>
			</ul>
		</section>

		<section class="dpviz-help-card">
			<h4><i class="fa fa-magic"></i> <?php echo _('Tools & Actions'); ?></h4>
			<ul class="dpviz-help-list">
				<li><i class="fa fa-flask"></i><span><?php echo _('Simulate Date & Time: Preview how call routing will behave at a future date or time - useful for holidays, early closures, seasonal hours, or testing new time conditions.'); ?></span></li>
				<li><i class="fa fa-save"></i><span><?php echo _('Save View: Save the current dial plan layout. This button appears after modifying the view using CTRL or Shift + click on one or more nodes.'); ?></span></li>
				<li><i class="fa fa-folder-open"></i><span><?php echo _('Open Destinations: Click a destination to open it in a new tab.'); ?></span></li>
				<li><i class="fa fa-clock-o"></i><span><?php echo _('Open Time Groups: Click on "Match: (timegroup)" or "No Match" to view details in a new tab.'); ?></span></li>
			</ul>
		</section>

		<section class="dpviz-help-card">
			<h4><i class="fa fa-arrows-alt"></i> <?php echo _('Viewing Controls'); ?></h4>
			<ul class="dpviz-help-list">
				<li><i class="fa fa-hand-pointer-o"></i><span><?php echo _('Hover: Move your cursor over a path to highlight it.'); ?></span></li>
				<li><i class="fa fa-arrows"></i><span><?php echo _('Pan: Click and drag to move around the view.'); ?></span></li>
				<li><i class="fa fa-search"></i><span><?php echo _('Zoom: Use the mouse wheel to zoom in and out.'); ?></span></li>
			</ul>
		</section>

		<section class="dpviz-help-card">
			<h4><i class="fa fa-plug"></i> <?php echo _('Extension Node Status'); ?></h4>
			<ul class="dpviz-status-list">
				<li><span class="dpviz-status-dot is-green"></span><span><?php echo _('Registered extension (green border)'); ?></span></li>
				<li><span class="dpviz-status-dot is-blue"></span><span><?php echo _('Dynamic agent logged into queue (blue border)'); ?></span></li>
				<li><span class="dpviz-status-dot is-yellow"></span><span><?php echo _('DND or Call Forwarding enabled (yellow border)'); ?></span></li>
				<li><span class="dpviz-status-dot is-red"></span><span><?php echo _('Unregistered extension (red border)'); ?></span></li>
				<li><span class="dpviz-status-dot is-gray"></span><span><?php echo _('Virtual or non-extension node (no border)'); ?></span></li>
				<li><span class="dpviz-status-pause"><i class="fa fa-pause"></i></span><span><?php echo _('Paused queue member'); ?></span></li>
			</ul>
		</section>
	</div>
</div>
