function showInlineForm(moduleName) {

  const $container = $('#nodestmodal-displayname');
	
  $('#inlineNewForm').remove();
	
  let fieldsHtml = `
    <div class="form-group dpviz-inline-row">
			<label for="new_desc" class="col-md-4 dpviz-col-form-label control-label">
				Name / Description
			</label>
			<div class="col-md-8">
				<input type="text"
							 id="new_desc"
							 name="new_desc"
							 class="form-control"
							 maxlength="50"
							 placeholder="Description"
							 autocomplete="off" />
			</div>
		</div>
  `;

  switch (moduleName) {
    case 'Announcements':
      fieldsHtml += `
				<div class="form-group dpviz-inline-row">
					<label for="recordingsSelect" class="col-md-4 dpviz-col-form-label control-label">Recording</label>
					<div class="col-md-8">
						<select id="recordingsSelect"
										name="recordingsSelect"
										class="form-control dpviz-select"
										style="width:100%">
							<option value="">-- Loading... --</option>
						</select>
					</div>
				</div>`;
      break;

		/* ────────────────────────────────
		 *  Call Flow Control
		 * ──────────────────────────────── */
		case 'Call Flow Control':
			fieldsHtml += `
				<div class="form-group dpviz-inline-row">
					<label for="currentmode" class="col-md-4 dpviz-col-form-label control-label">
						Current Mode
					</label>
					<div class="col-md-8 dpviz-text-end">
						<div class="btn-group radioset mode-buttons" role="group" aria-label="Current Mode">
							<input type="radio" name="currentmode" id="day_mode" value="DAY" autocomplete="off">
							<label class="btn" for="day_mode">
								Normal (Green/BLF off)
							</label>

							<input type="radio" name="currentmode" id="night_mode" value="NIGHT" autocomplete="off" checked>
							<label class="btn" for="night_mode">
								Override (Red/BLF on)
							</label>
						</div>
					</div>
				</div>`;
			break;
		/* ────────────────────────────────
		 *  Call Recording
		 * ──────────────────────────────── */
		case 'Call Recording':
			fieldsHtml += `
				<div class="form-group dpviz-inline-row">
					<label for="recordingmode" class="col-md-4 dpviz-col-form-label control-label">Call Recording Mode</label>
					<div class="col-md-8 dpviz-text-end">
						<div class="btn-group radioset mode-buttons" role="group" aria-label="Mode">

								<input type="radio" class="dpviz-btn-check" name="recordingmode" id="rec_force" value="force">
								<label class="btn" for="rec_force">Force</label>

								<input type="radio" class="dpviz-btn-check" name="recordingmode" id="rec_yes" value="yes">
								<label class="btn" for="rec_yes">Yes</label>

								<input type="radio" class="dpviz-btn-check" name="recordingmode" id="rec_dontcare" value="dontcare" checked>
								<label class="btn" for="rec_dontcare">Don't Care</label>

								<input type="radio" class="dpviz-btn-check" name="recordingmode" id="rec_no" value="no">
								<label class="btn" for="rec_no">No</label>

								<input type="radio" class="dpviz-btn-check" name="recordingmode" id="rec_never" value="never">
								<label class="btn" for="rec_never">Never</label>

						</div>

					</div>
				</div>`;
			break;
		/* ────────────────────────────────
		 *  Dynamic Routes
		 * ──────────────────────────────── */
		case 'Dynamic Routes':
      fieldsHtml += `
				<div class="form-group dpviz-inline-row">
					<label for="recordingsSelect" class="col-md-4 dpviz-col-form-label control-label">
						Recording
					</label>
					<div class="col-md-8">
						<select id="recordingsSelect"
										name="recordingsSelect"
										class="form-control dpviz-select"
										style="width:100%">
							<option value="">-- Loading... --</option>
						</select>
					</div>
				</div>
				<div class="form-group dpviz-inline-row">
					<label for="dyn_timeout" class="col-md-4 dpviz-col-form-label control-label">
						Timeout
					</label>
					<div class="col-md-8">
						<input type="number" min="0" class="form-control" id="dyn_timeout" name="dyn_timeout" value="5">
					</div>
				</div>
				
				<!-- DYN Entries -->
				<div class="entries-block">
					<label class="dpviz-col-form-label">Dynamic Route Entries</label>

					<div class="entries-scroll">
						<table class="table table-bordered dpviz-table-sm" id="dynEntriesTable">
							<thead>
								<tr>
									<th style="width:100px">Match</th>
									<th>Destination</th>
									<th style="width:60px"></th>
								</tr>
							</thead>
							<tbody></tbody>
						</table>
					</div>

					<button type="button"
									id="addDYNEntryBtn"
									class="btn btn-default btn-sm dpviz-outline-primary">
						+ Add Entry
					</button>
				</div>`;
      break;
			
		/* ────────────────────────────────
		 *  Inbound Routes
		 * ──────────────────────────────── */
		case 'Inbound Routes':
			fieldsHtml += `
				<div class="form-group dpviz-inline-row">
					<label for="did" class="col-md-4 dpviz-col-form-label control-label">DID Number</label>
					<div class="col-md-8">
						<input type="text" id="did" name="did" class="form-control" placeholder="DID Number" autocomplete="off">
					</div>
				</div>
				<div class="form-group dpviz-inline-row">
					<label for="cidnum" class="col-md-4 dpviz-col-form-label control-label">CallerID Number</label>
					<div class="col-md-8">
						<input type="text" id="cidnum" name="cidnum" class="form-control" placeholder="ANY" autocomplete="off">
					</div>
				</div>
				<div class="form-group dpviz-inline-row">
					<label for="grppre" class="col-md-4 dpviz-col-form-label control-label">CID name prefix</label>
					<div class="col-md-8">
						<input type="text" id="grppre" name="grppre" class="form-control" autocomplete="off">
					</div>
				</div>
				<div class="form-group dpviz-inline-row">
					<label for="musicSelect" class="col-md-4 dpviz-col-form-label control-label">Music On Hold</label>
					<div class="col-md-8">
						<select id="musicSelect" name="musicSelect" class="form-control dpviz-select" style="width:100%">
							<option value="">-- Loading... --</option>
						</select>
					</div>
				</div>`;
			break;

		/* ────────────────────────────────
		 *  IVR
		 * ──────────────────────────────── */
		case 'IVR':
			fieldsHtml += `
				<div class="form-group dpviz-inline-row">
					<label for="recordingsSelect" class="col-md-4 dpviz-col-form-label control-label">
						Recording
					</label>
					<div class="col-md-8">
						<select id="recordingsSelect"
										name="recordingsSelect"
										class="form-control dpviz-select"
										style="width:100%">
							<option value="">-- Loading... --</option>
						</select>
					</div>
				</div>
				<div class="form-group dpviz-inline-row">
					<label for="timeout_time" class="col-md-4 dpviz-col-form-label control-label">
						Timeout
					</label>
					<div class="col-md-8">
						<input type="number" min="0" class="form-control" id="timeout_time" name="timeout_time" value="10">
					</div>
				</div>
				
				<!-- IVR Entries -->
				<div class="entries-block">
					<label class="dpviz-col-form-label">IVR Entries</label>

					<div class="entries-scroll">
						<table class="table table-bordered dpviz-table-sm" id="ivrEntriesTable">
							<thead>
								<tr>
									<th style="width:100px">Digit</th>
									<th>Destination</th>
									<th style="width:60px"></th>
								</tr>
							</thead>
							<tbody></tbody>
						</table>
					</div>

					<button type="button"
									id="addIVREntryBtn"
									class="btn btn-default btn-sm dpviz-outline-primary">
						+ Add Entry
					</button>
				</div>`;
			break;

		/* ────────────────────────────────
		 *  Languages
		 * ──────────────────────────────── */
		case 'Languages':
			fieldsHtml += `
				<div class="form-group dpviz-inline-row">
					<label for="languageSelect" class="col-md-4 dpviz-col-form-label control-label">Language Code</label>
					<div class="col-md-8">
						<select id="langSelect"
										name="langSelect"
										class="form-control dpviz-select"
										style="width:100%">
							<option value="">-- Loading... --</option>
						</select>
					</div>
				</div>`;
			break;

		/* ────────────────────────────────
		 *  Misc Destinations
		 * ──────────────────────────────── */
		case 'Misc Destinations':
			fieldsHtml += `
				<div class="form-group dpviz-inline-row">
					<label for="account" class="col-md-4 dpviz-col-form-label control-label">Dial</label>
					<div class="col-md-8">
						<input type="text" name="destdial" id="destdial" class="form-control" maxlength="100" autocomplete="off">
					</div>
				</div>`;
			break;

		/* ────────────────────────────────
		 *  Queues
		 * ──────────────────────────────── */
		case 'Queues':
			fieldsHtml = `
				<div class="form-group dpviz-inline-row">
					<label for="account" class="col-md-5 dpviz-col-form-label control-label">Queue Number</label>
					<div class="col-md-7">
						<input type="text" name="account" id="qaccount" class="form-control" maxlength="20" autocomplete="off">
					</div>
				</div>
				
				<div class="form-group dpviz-inline-row">
					<label for="new_desc" class="col-md-5 dpviz-col-form-label control-label">Name / Description</label>
					<div class="col-md-7">
						<input type="text" id="new_desc" name="new_desc" class="form-control"
									 maxlength="50" placeholder="Description" autocomplete="off">
					</div>
				</div>
				
				<div class="form-group dpviz-inline-row">
					<label for="staticlist" class="col-md-5 dpviz-col-form-label control-label">Static Agents (comma separated)</label>
					<div class="col-md-7">
						<input type="text" id="staticlist" name="staticlist" class="form-control"
									 placeholder="101,102,103" autocomplete="off">
					</div>
				</div>
				
				<div class="form-group dpviz-inline-row">
					<label for="dynlist" class="col-md-5 dpviz-col-form-label control-label">Dynamic Agents (comma separated)</label>
					<div class="col-md-7">
						<input type="text" id="dynlist" name="dynlist" class="form-control"
									 placeholder="101,102,103" autocomplete="off">
					</div>
				</div>
				
				<div class="form-group dpviz-inline-row">
					<label for="qstrategy" class="col-md-5 dpviz-col-form-label control-label">Ring Strategy</label>
					<div class="col-md-7">
						<select name="qstrategy" id="qstrategy" class="form-control dpviz-select">
							<option value="ringall" selected="">ringall</option>
							<option value="leastrecent">leastrecent</option>
							<option value="fewestcalls">fewestcalls</option>
							<option value="random">random</option>
							<option value="rrmemory">rrmemory</option>
							<option value="rrordered">rrordered</option>
							<option value="linear">linear</option>
							<option value="wrandom">wrandom</option>
						</select>
					</div>
				</div>
				
				<div class="form-group dpviz-inline-row">
					<label for="maxwait" class="col-md-5 dpviz-col-form-label control-label">Max Wait Time</label>
					<div class="col-md-7">
						<select name="maxwait" id="maxwait" class="form-control">
							<option value="0" selected>Unlimited</option>
							<option value="1">1 seconds</option>
							<option value="2">2 seconds</option>
							<option value="3">3 seconds</option>
							<option value="4">4 seconds</option>
							<option value="5">5 seconds</option>
							<option value="6">6 seconds</option>
							<option value="7">7 seconds</option>
							<option value="8">8 seconds</option>
							<option value="9">9 seconds</option>
							<option value="10">10 seconds</option>
							<option value="11">11 seconds</option>
							<option value="12">12 seconds</option>
							<option value="13">13 seconds</option>
							<option value="14">14 seconds</option>
							<option value="15">15 seconds</option>
							<option value="16">16 seconds</option>
							<option value="17">17 seconds</option>
							<option value="18">18 seconds</option>
							<option value="19">19 seconds</option>
							<option value="20">20 seconds</option>
							<option value="21">21 seconds</option>
							<option value="22">22 seconds</option>
							<option value="23">23 seconds</option>
							<option value="24">24 seconds</option>
							<option value="25">25 seconds</option>
							<option value="26">26 seconds</option>
							<option value="27">27 seconds</option>
							<option value="28">28 seconds</option>
							<option value="29">29 seconds</option>
							<option value="30">30 seconds</option>
							<option value="35">35 seconds</option>
							<option value="40">40 seconds</option>
							<option value="45">45 seconds</option>
							<option value="50">50 seconds</option>
							<option value="55">55 seconds</option>
							<option value="60">1 minute</option>
							<option value="80">1 minute, 20 seconds</option>
							<option value="100">1 minute, 40 seconds</option>
							<option value="120">2 minutes</option>
							<option value="140">2 minutes, 20 seconds</option>
							<option value="160">2 minutes, 40 seconds</option>
							<option value="180">3 minutes</option>
							<option value="200">3 minutes, 20 seconds</option>
							<option value="220">3 minutes, 40 seconds</option>
							<option value="240">4 minutes</option>
							<option value="260">4 minutes, 20 seconds</option>
							<option value="280">4 minutes, 40 seconds</option>
							<option value="300">5 minutes</option>
							<option value="360">6 minutes</option>
							<option value="420">7 minutes</option>
							<option value="480">8 minutes</option>
							<option value="540">9 minutes</option>
							<option value="600">10 minutes</option>
							<option value="660">11 minutes</option>
							<option value="720">12 minutes</option>
							<option value="780">13 minutes</option>
							<option value="840">14 minutes</option>
							<option value="900">15 minutes</option>
							<option value="960">16 minutes</option>
							<option value="1020">17 minutes</option>
							<option value="1080">18 minutes</option>
							<option value="1140">19 minutes</option>
							<option value="1200">20 minutes</option>
							<option value="1500">25 minutes</option>
							<option value="1800">30 minutes</option>
							<option value="2100">35 minutes</option>
							<option value="2400">40 minutes</option>
							<option value="2700">45 minutes</option>
							<option value="3000">50 minutes</option>
							<option value="3300">55 minutes</option>
							<option value="3600">1 hour</option>
							<option value="3900">1 hour, 5 minutes</option>
							<option value="4200">1 hour, 10 minutes</option>
							<option value="4500">1 hour, 15 minutes</option>
							<option value="4800">1 hour, 20 minutes</option>
							<option value="5100">1 hour, 25 minutes</option>
							<option value="5400">1 hour, 30 minutes</option>
							<option value="5700">1 hour, 35 minutes</option>
							<option value="6000">1 hour, 40 minutes</option>
							<option value="6300">1 hour, 45 minutes</option>
							<option value="6600">1 hour, 50 minutes</option>
							<option value="6900">1 hour, 55 minutes</option>
							<option value="7200">2 hours</option>
						</select>
					</div>
				</div>
				
				`;
			break;

		/* ────────────────────────────────
		 *  Ring Groups
		 * ──────────────────────────────── */
		case 'Ring Groups':
			fieldsHtml = `
				<div class="form-group dpviz-inline-row">
					<label for="account" class="col-md-5 dpviz-col-form-label control-label">Ring-Group Number</label>
					<div class="col-md-7">
						<input type="text" name="account" id="rgaccount" class="form-control" maxlength="20" autocomplete="off">
					</div>
				</div>

				<div class="form-group dpviz-inline-row">
					<label for="new_desc" class="col-md-5 dpviz-col-form-label control-label">Name / Description</label>
					<div class="col-md-7">
						<input type="text" id="new_desc" name="new_desc" class="form-control"
									 maxlength="50" placeholder="Description" autocomplete="off">
					</div>
				</div>

				<div class="form-group dpviz-inline-row">
					<label for="grplist" class="col-md-5 dpviz-col-form-label control-label">Extensions (comma separated)</label>
					<div class="col-md-7">
						<input type="text" id="grplist" name="grplist" class="form-control"
									 placeholder="101,102,103" autocomplete="off">
					</div>
				</div>

				<div class="form-group dpviz-inline-row">
					<label for="rgstrategy" class="col-md-5 dpviz-col-form-label control-label">Ring Strategy</label>
					<div class="col-md-7">
						<select name="rgstrategy" id="rgstrategy" class="form-control dpviz-select">
							<option value="ringall">ringall</option>
							<option value="ringall-prim">ringall-prim</option>
							<option value="hunt">hunt</option>
							<option value="hunt-prim">hunt-prim</option>
							<option value="memoryhunt">memoryhunt</option>
							<option value="memoryhunt-prim">memoryhunt-prim</option>
							<option value="firstavailable">firstavailable</option>
							<option value="firstnotonphone">firstnotonphone</option>
							<option value="random">random</option>
						</select>
					</div>
				</div>

				<div class="form-group dpviz-inline-row">
					<label for="grptime" class="col-md-5 dpviz-col-form-label control-label">Ring Time (max 300 sec)</label>
					<div class="col-md-7">
						<input type="number" min="0" max="300" id="grptime" name="grptime"
									 class="form-control" value="20">
					</div>
				</div>`;
			break;

		/* ────────────────────────────────
		 *  Set CallerID
		 * ──────────────────────────────── */
		case 'Set CallerID':
			fieldsHtml += `
				<div class="form-group dpviz-inline-row">
					<label for="calleridName" class="col-md-4 dpviz-col-form-label control-label">CallerID Name</label>
					<div class="col-md-8">
						<input type="text" id="calleridName" name="calleridName"
									 class="form-control" value="\${CALLERID(name)}" autocomplete="off">
					</div>
				</div>
				<div class="form-group dpviz-inline-row">
					<label for="calleridNumber" class="col-md-4 dpviz-col-form-label control-label">CallerID Number</label>
					<div class="col-md-8">
						<input type="text" id="calleridNumber" name="calleridNumber"
									 class="form-control" value="\${CALLERID(num)}" autocomplete="off">
					</div>
				</div>`;
			break;

		/* ────────────────────────────────
		 *  Time Conditions
		 * ──────────────────────────────── */
		case 'Time Conditions':
			fieldsHtml += `
				<div class="form-group dpviz-inline-row">
					<label for="mode" class="col-md-3 dpviz-col-form-label control-label">Mode</label>
					<div class="col-md-9 dpviz-text-end">
						<div class="btn-group radioset mode-buttons" role="group" aria-label="Mode">
							<input type="radio" class="dpviz-btn-check" name="mode" id="mode_legacy" value="time-group" checked>
							<label class="btn" for="mode_legacy">Time Group Mode</label>

							<input type="radio" class="dpviz-btn-check" name="mode" id="mode_calendar" value="calendar">
							<label class="btn" for="mode_calendar">Calendar Mode</label>

							<input type="radio" class="dpviz-btn-check" name="mode" id="mode_groups" value="calendar-group">
							<label class="btn" for="mode_groups">Calendar Group Mode</label>
						</div>
					</div>
				</div>

				<div id="timeGroupContainer" class="form-group dpviz-inline-row">
					<label for="timegroupSelect" class="col-md-3 dpviz-col-form-label control-label">Time Group</label>
					<div class="col-md-9">
						<select id="timegroupSelect" name="timegroup_id"
										class="form-control dpviz-select" style="width:100%">
							<option value="">-- Loading... --</option>
						</select>
					</div>
				</div>

				<div id="calendarContainer" class="form-group dpviz-inline-row" style="display:none;">
					<label for="calendarSelect" class="col-md-3 dpviz-col-form-label control-label">Calendar</label>
					<div class="col-md-9">
						<select id="calendarSelect" name="calendar_id"
										class="form-control dpviz-select" style="width:100%">
							<option value="">-- Loading... --</option>
						</select>
					</div>
				</div>

				<div id="calendarGroupContainer" class="form-group dpviz-inline-row" style="display:none;">
					<label for="calendarGroupSelect" class="col-md-3 dpviz-col-form-label control-label">Calendar Groups</label>
					<div class="col-md-9">
						<select id="calendarGroupSelect" name="calendar_group"
										class="form-control dpviz-select" style="width:100%">
							<option value="">-- Loading... --</option>
						</select>
					</div>
				</div>`;
			break;
	}

	
	const formHtml = `
  <form id="inlineNewForm" class="form-horizontal" style="margin-top:12px;">
    <div class="section-title" style="font-weight:bold; margin-bottom:6px;">
      Add New ${moduleName}
    </div>

    <div id="inlineFormFields">
      ${fieldsHtml}
    </div>

    <div class="form-actions" style="margin-top:8px; text-align:right;">
      <button id="createNewBtn" type="submit" class="btn btn-primary">
        Create
      </button>
    </div>
  </form>
`;



$container.append(formHtml);

const $modal = $('#nodestmodal');

$(document).on('mouseenter', '#nodestmodal .input-warn', function () {
  const $this = $(this);

  // Destroy any existing tooltip (may have been created by pbxlib)
  try { $this.tooltip('destroy'); } catch (e) {}

  // Reinit with modal-safe container and placement
  $this.tooltip({
    container: '#nodestmodal',   // keep tooltip above modal
    placement: 'auto right',     // avoids left-side clipping
    html: false,
    trigger: 'hover focus'
  });
});

$(document).on('input', '#new_desc', function () {
  const sanitized = $(this).val().replace(/[^A-Za-z0-9 & -]/g, '');
  if ($(this).val() !== sanitized) $(this).val(sanitized);
});


$(document).on('input change keyup', '#inlineNewForm input', function () {
  const $form = $('#inlineNewForm');
  const $btn  = $form.find('#createNewBtn');

  // Check for duplicates
  const hasDuplicate = $form.find('.duplicate-exten').length > 0;

  // Toggle button
  $btn.prop('disabled', hasDuplicate);
});

// Enable Bootstrap 5-style toggle behavior manually
$(document).on('change', '.btn-group.radioset input[type="radio"]', function () {
  const $group = $(this).closest('.btn-group.radioset');
  $group.find('label.btn').removeClass('active');
  $(this).next('label.btn').addClass('active');
});

$('#inlineNewForm .btn-group.radioset').each(function () {
  const $group = $(this);
  const $checked = $group.find('input[type="radio"]:checked').first();

  $group.find('label.btn').removeClass('active');
  if ($checked.length) {
    $checked.next('label.btn').addClass('active');
  }
});

	if (moduleName === 'Announcements' || moduleName === 'Dynamic Routes' || moduleName === 'IVR') {
		loadRecordings();
	}
	
	if (moduleName === 'Inbound Routes') {
		loadMusic();
	}
	
	if (moduleName === 'Languages') {
		loadLanguages();
	}

	if (moduleName === 'Queues') {
		loadQStrategy();
		loadMaxWait();
	}

	if (moduleName === 'Ring Groups') {
		loadRingStrategy();
	}
	
	if (moduleName === 'Time Conditions') {
		loadTimegroups();
		loadCalendars();
		loadCalendarGroups();

		// Attach toggle handler AFTER formHtml is appended
		$container.off('change', 'input[name="mode"]').on('change', 'input[name="mode"]', function() {
			const mode = $(this).val();

			if (mode === 'time-group') {
				$('#timeGroupContainer').show();
				$('#calendarContainer').hide();
				$('#calendarGroupContainer').hide();
			} 
			else if (mode === 'calendar') {
				$('#timeGroupContainer').hide();
				$('#calendarContainer').show();
				$('#calendarGroupContainer').hide();
			} 
			else if (mode === 'calendar-group') {
				$('#timeGroupContainer').hide();
				$('#calendarContainer').hide();
				$('#calendarGroupContainer').show();
			}
		});

		// Initialize correct state based on default checked radio
		const selected = $container.find('input[name="mode"]:checked').val();

		if (selected === 'time-group') {
			$('#timeGroupContainer').show();
			$('#calendarContainer').hide();
			$('#calendarGroupContainer').hide();
		} 
		else if (selected === 'calendar') {
			$('#timeGroupContainer').hide();
			$('#calendarContainer').show();
			$('#calendarGroupContainer').hide();
		} 
		else if (selected === 'calendar-group') {
			$('#timeGroupContainer').hide();
			$('#calendarContainer').hide();
			$('#calendarGroupContainer').show();
		}
	}


	
	


  $('#createNewBtn').on('click', function () {
		$('#inlineNewForm').on('submit', e => e.preventDefault());
		const $btn = $(this);
		const $name = $('#new_desc');
		const nameVal = $name.val().trim();

		// Check for missing name
		if (nameVal === '') {
			return warnInvalid($name, 'Name is required.');
		}

		const payload = { module: moduleName, name: nameVal };

		if (moduleName === 'Announcements')		payload.recording_id = $('#recordingsSelect').val();
		if (moduleName === 'Call Flow Control')		payload.currentmode = $('input[name="currentmode"]:checked').val();
		if (moduleName === 'Call Recording')		payload.recordingmode = $('input[name="recordingmode"]:checked').val();
		if (moduleName === 'Dynamic Routes') {
			payload.recording_id = $('#recordingsSelect').val();
			payload.dyn_timeout = $('#dyn_timeout').val();
			
			const dynEntries = [];
			let isValid = true;
			let seenDigits = new Set();   // track duplicates

			$('.dyn-digit').each(function () {
					const rowId  = $(this).closest('tr').data('row');
					const digit  = $(this).val();
					const $moduleEl = $(`select.dyn-module[data-row="${rowId}"]`);
					const $destEl = $(`select.dyn-dest[data-row="${rowId}"]`);
					const dest    = $destEl.val();

					const digitVal = digit.trim();

					// INVALID: digit empty but dest present
					if (dest && !/^[0-9*#]{1,10}$/.test(digitVal)) {
							warnInvalid($(this), 'Please enter a valid value for Digits Pressed');
							isValid = false;
							return;
					}

					// INVALID: digit filled but no destination
					if (digit && !dest) {
							warnInvalid($moduleEl, 'Destination is required.');
							isValid = false;
							return;
					}

					// Duplicate digit check
					if (digitVal) {
							if (seenDigits.has(digitVal)) {
									warnInvalid($(this), `Digit "${digitVal}" is already used.`);
									isValid = false;
									return;
							}
							seenDigits.add(digitVal);
					}

					// VALID entry → push in array
					if (digit && dest) {
							dynEntries.push({ digit: digitVal, dest });
					}
			});

			// stop submit on error
			if (!isValid) return;

			payload.dynEntries = dynEntries;

			
		}
		if (moduleName === 'Languages')		payload.lang_code = $('#langSelect').val();
		
		if (moduleName === 'IVR') {
			payload.recording_id = $('#recordingsSelect').val();
			payload.timeout_time = $('#timeout_time').val();
			
			const ivrEntries = [];

			let isValid = true;
			let seenDigits = new Set();  // 👈 track duplicates

			$('.ivr-digit').each(function () {
					const rowId  = $(this).closest('tr').data('row');
					const digit  = $(this).val();
					const $digitEl  = $(this); // easier reference
					const $moduleEl = $(`select.ivr-module[data-row="${rowId}"]`);
					const $destEl   = $(`select.ivr-dest[data-row="${rowId}"]`);
					const dest      = $destEl.val();

					const digitVal = digit.trim();

					// INVALID: digit empty but destination set
					if (dest && !/^[0-9*#]{1,10}$/.test(digitVal)) {
							warnInvalid($digitEl, 'Please enter a valid value for Digits Pressed');
							isValid = false;
							return;
					}

					// INVALID: digit set but destination empty
					if (digit && !dest) {
							warnInvalid($moduleEl, 'Destination is required.');
							isValid = false;
							return;
					}

					// Duplicate check
					if (digitVal) {
							if (seenDigits.has(digitVal)) {
									warnInvalid($digitEl, `Digit "${digitVal}" is already used.`);
									isValid = false;
									return;
							}
							seenDigits.add(digitVal);
					}

					// VALID entry
					if (digit && dest) {
							ivrEntries.push({ digit: digitVal, dest });
					}
			});

			// Stop form submission
			if (!isValid) return;

			payload.ivrEntries = ivrEntries;

		}
		
		if (moduleName === 'Misc Destinations') {
			const $destdial = $('#destdial');
			const destdialVal = $destdial.val().trim();
			
			if (destdialVal && !/^[0-9*#]{1,100}$/.test(destdialVal)) {
				return warnInvalid($destdial, 'Please enter a valid Dial string');
			}
			payload.destdial   = destdialVal;
		}
		
		if (moduleName === 'Queues') {
			const $qnum = $('#qaccount');
			const qnumVal = $qnum.val().trim();
			const $staticlist = $('#staticlist');
			const staticlistVal = $staticlist.val().trim();
			const $dynlist = $('#dynlist');
			const dynlistVal = $dynlist.val().trim();
			
			if (qnumVal === '' || !/^\d+$/.test(qnumVal)) {
				return warnInvalid($qnum, 'Queue number must be a valid integer.');
			}
			
			if (staticlistVal && !/^[0-9,]+$/.test(staticlistVal)) {
				return warnInvalid($staticlist, 'Only numbers and commas are allowed.');
			}

			if (dynlistVal && !/^[0-9,]+$/.test(dynlistVal)) {
				return warnInvalid($dynlist, 'Only numbers and commas are allowed.');
			}

			
			payload.extension   = qnumVal;
			payload.staticlist  = staticlistVal;
			payload.dynlist     = dynlistVal;
			payload.qstrategy   = $('#qstrategy').val();
			payload.maxwait     = $('#maxwait').val();
		
		}
		
		if (moduleName === 'Ring Groups') {
			const $rgnum = $('#rgaccount');
			const rgnumVal = $rgnum.val().trim();
			const $rglist = $('#grplist');
			const rglistVal = $rglist.val().trim();
			const $rgtime = $('#grptime');
			const rgtimeVal = $rgtime.val();
			if (rgnumVal === '' || !/^\d+$/.test(rgnumVal)) {
				return warnInvalid($rgnum, 'Ring Group number must be a valid integer.');
			}
			if (rglistVal === '') {
				return warnInvalid($rglist, 'Please enter an extension list.');
			}
			if (!/^[0-9,#]+$/.test(rglistVal)) {
				return warnInvalid($rglist, 'Only numbers, commas, and # symbols are allowed.');
			}
			if (rgtimeVal > 300) {
				return warnInvalid($rgtime, 'Value must be less than or equal to 300.');
			}
			
			payload.grpnum   = rgnumVal;
			payload.rgstrategy   = $('#rgstrategy').val();
			payload.grplist   = $('#grplist').val();
			payload.grptime   = $('#grptime').val();
		}
		
		if (moduleName === 'Set CallerID') {
					payload.calleridName   = $('#calleridName').val();
					payload.calleridNumber   = $('#calleridNumber').val();
		}
		
		if (moduleName === 'Time Conditions') {
			const mode = $('input[name="mode"]:checked').val();

			if (mode === 'time-group') {
				const $timegroupSelect = $('#timegroupSelect');
				const tgVal = $timegroupSelect.val();
				if (tgVal === '') {
					return warnInvalid($timegroupSelect, 'Time Group is required.');
				}
				payload.timegroup_id = tgVal;
				payload.calendar_id = '';
				payload.calendar_group_id = '';
			} 
			else if (mode === 'calendar') {
				const $calendarSelect = $('#calendarSelect');
				const calVal = $calendarSelect.val();
				if (calVal === '') {
					return warnInvalid($calendarSelect, 'Calendar is required.');
				}
				payload.calendar_id = calVal;
				payload.timegroup_id = '';
				payload.calendar_group_id = '';
			}
			else if (mode === 'calendar-group') {
				const $calendarGroupSelect = $('#calendarGroupSelect');
				const cgVal = $calendarGroupSelect.val();
				if (cgVal === '') {
					return warnInvalid($calendarGroupSelect, 'Calendar Group is required.');
				}
				payload.calendar_group_id = cgVal;
				payload.timegroup_id = '';
				payload.calendar_id = '';
			}
		}



		if (moduleName === 'Inbound Routes') {
			const $did = $('#did');
			const $cid = $('#cidnum');

			const didVal = $did.val().trim();
			const cidVal = $cid.val().trim();

			// Only digits, commas, [ ], and dashes; no spaces
			const allowed = /^(\+?[0-9A-D\*\#]+|_[0-9A-D\*\#]+)$/;

			// Always enforce FORMAT
			if (didVal === '' || !allowed.test(didVal)) {
				return warnInvalid($did, 'DID can only contain numbers, commas, [ ], and dashes (-). No spaces.');
			}
			if (cidVal !== '' && !allowed.test(cidVal)) {
				return warnInvalid($cid, 'CID can only contain numbers, commas, [ ], and dashes (-). No spaces.');
			}

			payload.did     = didVal;
			payload.cidnum  = cidVal;
			payload.grppre  = $('#grppre').val();
			payload.music   = $('#musicSelect').val();
		}
		
		const modalMode = $('#nodestmodal').data('mode');
		if (modalMode === 'insert') {
				payload.previous = $('#nodestmodal').data('previous');
		}

		$btn.prop('disabled', true);

		fetch('ajax.php?module=dpviz&command=create_destination', {
				method: 'POST',
				headers: { 'Content-Type': 'application/json' },
				body: JSON.stringify(payload)
		})
		.then(r => r.json())
		.then(res => {
				if (res.status !== 'success') {
						fpbxToast('Create failed: ' + (res.message || 'Unknown error'), 'error', 'error');
						$btn.prop('disabled', false);
						return;
				}

				const $modal      = $('#nodestmodal');
				const mode        = $modal.data('mode');   // 'create', 'link', or 'insert'
				const $destSelect = $('#destSelect');
				const $saveBtn    = $('#saveNoDestBtn');

				/* ---------------------------------------------------------
				 * MODE: ADD IVR ENTRY
				 * --------------------------------------------------------- */
				if (mode === 'add_ivr_entry') {
						$destSelect
								.append(new Option(res.label, res.value, true, true))
								.trigger('change.select2');

						$('#inlineNewForm').remove();
						if ($saveBtn.length) $saveBtn.prop('disabled', false);

						fpbxToast('Destination created. Click Save to link it.', 'success');
						return;
				}
				
				/* ---------------------------------------------------------
				 * MODE: ADD DYN ENTRY
				 * --------------------------------------------------------- */
				if (mode === 'add_dyn_entry') {
						$destSelect
								.append(new Option(res.label, res.value, true, true))
								.trigger('change.select2');

						$('#inlineNewForm').remove();
						if ($saveBtn.length) $saveBtn.prop('disabled', false);

						fpbxToast('Destination created. Click Save to link it.', 'success');
						return;
				}
				
				/* ---------------------------------------------------------
				 * MODE: LINK
				 * --------------------------------------------------------- */
				if (mode === 'link') {
						$destSelect
								.append(new Option(res.label, res.value, true, true))
								.trigger('change.select2');

						$('#inlineNewForm').remove();
						if ($saveBtn.length) $saveBtn.prop('disabled', false);

						fpbxToast('Destination created. Click Save to link it.', 'success');
						return;
				}

				/* ---------------------------------------------------------
				 * MODE: INSERT
				 * --------------------------------------------------------- */
				if (mode === 'insert') {
					// whatever dialplan/extension was active when the user clicked the node
					const currentDialplan = $('#dialPlan').val() || $('#ext').val();
					const title           = $modal.data('title');

					fetch('ajax.php?module=dpviz&command=save_nodest', {
							method: 'POST',
							headers: { 'Content-Type': 'application/json' },
							body: JSON.stringify({
									destination: res.value,
									titleText: title
							})
					})
					.then(r => r.json())
					.then(x => {
							if (x.status !== 'success') {
									fpbxToast('Insert failed: ' + (x.message || 'Unknown error'), 'error', 'error');
									return;
							}

							$('#nodestmodal').hide();

							// Make sure the EXT field reflects the dialplan we want to redraw
							$('#ext').val(currentDialplan);

							// Reuse the existing reload logic
							$('#reloadButton').trigger('click');

							fpbxToast('Inserted successfully into current dial plan!', 'success');
					});

					return; // do NOT continue to create-mode logic
				}



				/* ---------------------------------------------------------
				 * MODE: CREATE
				 * --------------------------------------------------------- */
				$('#nodestmodal').hide();

				const $dialPlan = $('#dialPlan');
				if ($dialPlan.length) {

						let opt;
						const lang = typeof currentLang !== 'undefined' ? currentLang : 'en';
						const moduleName = $('#moduleSelect').val();

						const valueWithLang = res.value + ',' + lang;
						const $existing = $dialPlan.find(`option[value="${valueWithLang}"]`);

						if ($existing.length) {
								opt = $existing[0];
						} else {
								opt = new Option(res.label, valueWithLang, true, true);

								const $group =
										$dialPlan.find(`optgroup[label="${moduleName}"], optgroup[label="${moduleName}s"]`).first();

								if ($group.length) {
										$group.append(opt);
								} else {
										const $newGroup = $(`<optgroup label="${moduleName}"></optgroup>`);
										$newGroup.append(opt);
										$dialPlan.append($newGroup);
								}
						}

						$dialPlan.val(valueWithLang).trigger('change');
						const dataObj = { id: valueWithLang, text: res.label, element: opt };
						$dialPlan.trigger({ type: 'select2:select', params: { data: dataObj } });
				}

				fpbxToast('Destination created and loaded successfully!', 'success');
		})
		.catch(e => {
				alert('Network error: ' + e);
				$btn.prop('disabled', false);
		});


	});


}


$(document).ready(function () {
  $('input[name="mode"]').on('change', function () {
    if (this.value === 'time-group') {
      $('#timeGroupContainer').show();
      $('#calendarContainer').hide();
      $('#timegroupSelect').prop('disabled', false);
      $('#calendarSelect').prop('disabled', true);
    } else if (this.value === 'calendar-group') {
      $('#timeGroupContainer').hide();
      $('#calendarContainer').show();
      $('#timegroupSelect').prop('disabled', true);
      $('#calendarSelect').prop('disabled', false);
    }
  });

  // Trigger once on load to set initial state
  $('input[name="mode"]:checked').trigger('change');
});


function loadTimegroups() {
  const $sel = $('#timegroupSelect');
  if (!$sel.length) return;

  $sel.empty().append('<option value="">-- Loading... --</option>');

  $.getJSON('ajax.php?module=dpviz&command=list_timegroups', function(res) {
    $sel.empty().append('<option value="">-- Select Time Group --</option>');

    if (res && res.status === 'success' && Array.isArray(res.groups)) {
      res.groups.forEach(g => $sel.append(new Option(g.description, g.id)));
    } else {
      $sel.append(new Option('(No time groups found)', ''));
    }

    if ($.fn.select2) {
			$('#timegroupSelect').select2({
				dropdownParent: $('#nodestmodal'),
				minimumResultsForSearch: 5,
				placeholder: 'Select Time Group',
				width: '100%'
			});
    }
  }).fail(function() {
    $sel.empty().append(new Option('Error loading time groups', ''));
  });
}

function loadCalendars() {
  const $sel = $('#calendarSelect');
  if (!$sel.length) return;

  $sel.empty().append('<option value="">-- Loading... --</option>');

  $.getJSON('ajax.php?module=dpviz&command=list_calendars', function(res) {
    $sel.empty().append('<option value="">-- Select Calendar --</option>');

    if (res && res.status === 'success' && Array.isArray(res.groups)) {
      res.groups.forEach(g => $sel.append(new Option(g.name, g.id)));
    } else {
      $sel.append(new Option('(No calendars found)', ''));
    }

    if ($.fn.select2) {
			$('#calendarSelect').select2({
				dropdownParent: $('#nodestmodal'),
				minimumResultsForSearch: 5,
				placeholder: 'Select Calendar',
				width: '100%'
			});
    }
  }).fail(function() {
    $sel.empty().append(new Option('Error loading time groups', ''));
  });
}

function loadCalendarGroups() {
  const $sel = $('#calendarGroupSelect');
  if (!$sel.length) return;

  $sel.empty().append('<option value="">-- Loading... --</option>');

  $.getJSON('ajax.php?module=dpviz&command=list_calendargroups', function(res) {
    $sel.empty().append('<option value="">-- Select Calendar Group --</option>');

    if (res && res.status === 'success' && Array.isArray(res.groups)) {
      res.groups.forEach(g => $sel.append(new Option(g.name, g.id)));
    } else {
      $sel.append(new Option('(No calendar groups found)', ''));
    }

    if ($.fn.select2) {
			$('#calendarGroupSelect').select2({
				dropdownParent: $('#nodestmodal'),
				minimumResultsForSearch: 5,
				placeholder: 'Select Calendar Group',
				width: '100%'
			});
    }
  }).fail(function() {
    $sel.empty().append(new Option('Error loading time groups', ''));
  });
}

function loadLanguages() {
  const $sel = $('#langSelect');
  if (!$sel.length) return;

  $sel.empty().append('<option value="">-- Loading... --</option>');

  $.getJSON('ajax.php?module=dpviz&command=list_languages', function(res) {
    $sel.empty().append('<option value="">-- Select Language --</option>');

    if (res && res.status === 'success' && res.groups && typeof res.groups === 'object') {

				Object.entries(res.groups).forEach(([langCode, langLabel]) => {
						$sel.append(new Option(langLabel, langCode));
				});

				// Auto-select the first option
				$sel.prop('selectedIndex', 0).trigger('change');

		} else {
				$sel.append(new Option('(No languages found)', ''));
		}

    // enhance with Select2 if available
    if ($.fn.select2) {
      $sel.select2({
        dropdownParent: $('#nodestmodal'),
				minimumResultsForSearch: 5,
        placeholder: 'Select Language',
        width: '100%'
      });
    }
  }).fail(function() {
    $sel.empty().append(new Option('Error loading language', ''));
  });
}

function loadMusic() {
  const $sel = $('#musicSelect');
  if (!$sel.length) return;

  $sel.empty().append('<option value="">-- Loading... --</option>');

  $.getJSON('ajax.php?module=dpviz&command=list_music', function(res) {
    $sel.empty().append('<option value="">-- Select Music --</option>');

    if (res && res.status === 'success' && Array.isArray(res.groups) && res.groups.length > 0) {
      res.groups.forEach(g => {
        // label and value are identical here
        $sel.append(new Option(g.category, g.category));
      });

      // Add “None” option at the bottom
      $sel.append(new Option('None', 'none'));

      // Auto-select the first item
      $sel.prop('selectedIndex', 1).trigger('change');
    } else {
      $sel.append(new Option('(No music found)', ''));
    }

    // enhance with Select2 if available
    if ($.fn.select2) {
      $sel.select2({
        dropdownParent: $('#nodestmodal'),
				minimumResultsForSearch: 5,
        placeholder: 'Select Music',
        width: '100%'
      });
    }
  }).fail(function() {
    $sel.empty().append(new Option('Error loading music', ''));
  });
}

function loadRecordings() {
  const $sel = $('#recordingsSelect');
  if (!$sel.length) return;

  $sel.empty().append('<option value="">-- Loading... --</option>');

  $.getJSON('ajax.php?module=dpviz&command=list_recordings', function(res) {
    $sel.empty().append('<option value="">-- Select Recording --</option>');

    if (res && res.status === 'success' && Array.isArray(res.groups) && res.groups.length > 0) {
			// Add “None” option at the top
      $sel.append(new Option('None', '0'));
			
      res.groups.forEach(g => {
        // label and value are identical here
        $sel.append(new Option(g.displayname, g.id));
      });

      

      // Auto-select the first item
      $sel.prop('selectedIndex', 1).trigger('change');
    } else {
      $sel.append(new Option('(No recordings found)', ''));
    }

    if ($.fn.select2) {
      $sel.select2({
        dropdownParent: $('#nodestmodal'),
				minimumResultsForSearch: 5,
        placeholder: 'Select Recording',
        width: '100%'
      });
    }
  }).fail(function() {
    $sel.empty().append(new Option('Error loading recordings', ''));
  });
}

function loadQStrategy() {
	$('#qstrategy').select2({
		dropdownParent: $('#nodestmodal'), 
		minimumResultsForSearch: 5,
		placeholder: 'Select ring strategy',
		width: '100%'
		});
}
function loadMaxWait() {
	$('#maxwait').select2({
		dropdownParent: $('#nodestmodal'), 
		minimumResultsForSearch: 5,
		placeholder: 'Select Max Wait Time',
		width: '100%'        
	});
}

function loadRingStrategy() {
	$('#rgstrategy').select2({
		dropdownParent: $('#nodestmodal'), 
		minimumResultsForSearch: 5,
		placeholder: 'Select ring strategy',
		width: '100%'
	});
}

function warnInvalid($el, msg) {
  // Clear previous state
  $el.removeClass('is-valid').addClass('is-invalid').attr('aria-invalid', 'true');

  // Add or update a feedback element right after the field
  let $fb = $el.next('.invalid-feedback');
  if ($fb.length === 0) {
    $fb = $('<div class="invalid-feedback"></div>');
    $el.after($fb);
  }
  $fb.text(msg);

  // Focus the field
  $el.trigger('focus');

  // Also raise a toast when fpbxToast is available
  if (typeof fpbxToast === 'function') {
    fpbxToast(msg, 'error', 'error');
  } else {
    // Fallback
    // alert(msg);
  }

  return false; // lets callers `return warnInvalid(...)` to stop the flow
}


//ivr entries

$(document).on('click', '#addIVREntryBtn', function () {
  addIVREntryRow();
});

$(document).on('click', '.remove-ivr-row', function () {
  $(this).closest('tr').remove();
});

function addIVREntryRow() {
  const rowId = "ivr_" + Date.now();

  const row = `
    <tr data-row="${rowId}">
      <td style="width:100px">
        <input type="text"
               class="form-control form-control-sm ivr-digit"
               maxlength="10">
      </td>

      <td>
				<div class="ivr-select-wrapper">
					<!-- Module Select -->
					<select class="form-control dpviz-select dpviz-select-sm ivr-module"
									data-row="${rowId}"
									style="width:100%;">
						<option value="">== choose one ==</option>
					</select>

					<!-- Destination wrapper (starts hidden) -->
					<div class="ivr-dest-wrapper" style="display:none; margin-top:1px;">
						<select class="form-control dpviz-select dpviz-select-sm ivr-dest"
										id="ivrEntrySelect_${rowId}"
										name="ivrEntry[${rowId}]"
										data-row="${rowId}"
										style="width:100%;">
						</select>
					</div>
				</div>
			</td>

      <td style="width:60px; text-align:center;">
        <button class="btn btn-danger btn-sm remove-ivr-row"><i class="fa fa-trash"></i></button>
      </td>
    </tr>
  `;

  $('#ivrEntriesTable tbody').append(row);

  // load data if not loaded yet
  ivrloadModulesIntoRow(rowId);
}

let ivrModuleCache = null;

function ivrloadModulesIntoRow(rowId) {
  if (ivrModuleCache) {
    ivrfillModuleDropdown(rowId);
    return;
  }

  fetch("ajax.php?module=dpviz&command=nodestselect")
    .then(r => r.json())
    .then(data => {
      ivrModuleCache = data;
      ivrfillModuleDropdown(rowId);
    });
}

function ivrfillModuleDropdown(rowId) {
  const $module = $(`tr[data-row="${rowId}"] .ivr-module`);

  // Add module options
  Object.keys(ivrModuleCache).forEach(mod => {
    $module.append(`<option value="${mod}">${mod}</option>`);
  });

  // Initialize select2 on the module
  $module.select2({
    width: '100%',
    dropdownParent: $module.closest('td'),
    minimumResultsForSearch: 5,
    dropdownCssClass: 's2-limit-height'
  });
}


$(document).on("change", ".ivr-module", function () {
  const rowId      = $(this).data("row");
  const moduleName = $(this).val();

  const $row      = $(`tr[data-row="${rowId}"]`);
  const $wrapper  = $row.find('.ivr-dest-wrapper');
  const $dest     = $row.find('.ivr-dest');

  // Always reset destination
  if ($dest.hasClass("select2-hidden-accessible")) {
    $dest.select2('destroy');
  }
  $dest.empty().append('');

  // No module selected → hide destination and bail
  if (!moduleName) {
    $wrapper.hide();
    return;
  }

  // Populate destination options
  const entries = ivrModuleCache[moduleName] || [];
  entries.forEach(e => {
    $dest.append(new Option(e.label, e.value));
  });

  // Show wrapper and init Select2
  $wrapper.show();
  initIVRSelect2($dest);
});

function initIVRSelect2($select) {
  if ($select.hasClass("select2-hidden-accessible")) {
    $select.select2('destroy');
  }

  $select.select2({
    width: '100%',
    dropdownParent: $select.closest('td'),
    minimumResultsForSearch: 5,
    dropdownCssClass: 's2-limit-height'
  });
}


//dynamic route entries
$(document).on('click', '#addDYNEntryBtn', function () {
  addDYNEntryRow();
});

$(document).on('click', '.remove-dyn-row', function () {
  $(this).closest('tr').remove();
});

function addDYNEntryRow() {
  const rowId = "dyn_" + Date.now();

  const row = `
    <tr data-row="${rowId}">
      <td style="width:100px">
        <input type="text"
               class="form-control form-control-sm dyn-digit"
               maxlength="10">
      </td>

      <td>
				<div class="dyn-select-wrapper">
					<!-- Module Select -->
					<select class="form-control dpviz-select dpviz-select-sm dyn-module"
									data-row="${rowId}"
									style="width:100%;">
						<option value="">== choose one ==</option>
					</select>

					<!-- Destination wrapper (starts hidden) -->
					<div class="dyn-dest-wrapper" style="display:none; margin-top:1px;">
						<select class="form-control dpviz-select dpviz-select-sm dyn-dest"
										id="dynEntrySelect_${rowId}"
										name="dynEntry[${rowId}]"
										data-row="${rowId}"
										style="width:100%;">
						</select>
					</div>
				</div>
			</td>

      <td style="width:60px; text-align:center;">
        <button class="btn btn-danger btn-sm remove-dyn-row"><i class="fa fa-trash"></i></button>
      </td>
    </tr>
  `;

  $('#dynEntriesTable tbody').append(row);

  // load data if not loaded yet
  dynloadModulesIntoRow(rowId);
}

let dynModuleCache = null;

function dynloadModulesIntoRow(rowId) {
  if (dynModuleCache) {
    dynfillModuleDropdown(rowId);
    return;
  }

  fetch("ajax.php?module=dpviz&command=nodestselect")
    .then(r => r.json())
    .then(data => {
      dynModuleCache = data;
      dynfillModuleDropdown(rowId);
    });
}

function dynfillModuleDropdown(rowId) {
  const $module = $(`tr[data-row="${rowId}"] .dyn-module`);

  // Add module options
  Object.keys(dynModuleCache).forEach(mod => {
    $module.append(`<option value="${mod}">${mod}</option>`);
  });

  // Initialize select2 on the module
  $module.select2({
    width: '100%',
    dropdownParent: $module.closest('td'),
    minimumResultsForSearch: 5,
    dropdownCssClass: 's2-limit-height'
  });
}


$(document).on("change", ".dyn-module", function () {
  const rowId      = $(this).data("row");
  const moduleName = $(this).val();

  const $row      = $(`tr[data-row="${rowId}"]`);
  const $wrapper  = $row.find('.dyn-dest-wrapper');
  const $dest     = $row.find('.dyn-dest');

  // Always reset destination
  if ($dest.hasClass("select2-hidden-accessible")) {
    $dest.select2('destroy');
  }
  $dest.empty().append('');

  // No module selected → hide destination and bail
  if (!moduleName) {
    $wrapper.hide();
    return;
  }

  // Populate destination options
  const entries = dynModuleCache[moduleName] || [];
  entries.forEach(e => {
    $dest.append(new Option(e.label, e.value));
  });

  // Show wrapper and init Select2
  $wrapper.show();
  initDYNSelect2($dest);
});

function initDYNSelect2($select) {
  if ($select.hasClass("select2-hidden-accessible")) {
    $select.select2('destroy');
  }

  $select.select2({
    width: '100%',
    dropdownParent: $select.closest('td'),
    minimumResultsForSearch: 5,
    dropdownCssClass: 's2-limit-height'
  });
}

