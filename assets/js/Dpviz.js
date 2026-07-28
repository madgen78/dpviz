$(document).ready(function() {
	//github update check
	let updateResultOriginalHtml = null;

	$(document).ready(function () {
			updateResultOriginalHtml = $('#update-result').html();
	});

	$('#check-update-btn').click(function () {

			$('#update-result').html(
					`<div style="margin-top: 10px;">${translations.checking}</div>`
			);

			$.ajax({
					url: 'ajax.php?module=dpviz&command=check_update',
					method: 'POST',
					dataType: 'json',

					success: function (response) {

							if (response.status === 'success') {

									if (response.up_to_date) {

											$('#update-result')
													.html(`<div style="margin-top: 10px;">${translations.uptodate}</div>`)
													.delay(4000)
													.fadeOut('slow', function () {
															$(this)
																	.html(updateResultOriginalHtml)
																	.fadeIn('fast');
													});

									} else {

											// version strings come from the remote update
											// endpoint, so they are third-party data -- set
											// them as text rather than parsing them as markup
											const $link = $('<a>')
													.attr({ href: 'config.php?display=modules', target: '_blank' })
													.addClass('btn btn-default')
													.text(`${response.latest} ${translations.available} `)
													.append($('<i>').addClass('fa fa-external-link').attr('aria-hidden', 'true'));

											const $current = $('<div>')
													.css('margin-top', '6px')
													.text(`${translations.currentVersion}: ${response.current}`);

											$('#update-result').empty().append($link, $current);
									}

							} else {
									$('#update-result').text('Error: ' + response.message);
							}
					},

					error: function (xhr, status, error) {
							$('#update-result').text('AJAX error: ' + error);
					}
			});
	});


});

// GLOBAL Select2 autofocus
$(document).on('select2:open', function () {
    setTimeout(() => {
        const el = document.querySelector('.select2-container--open .select2-search__field');
        if (el) el.focus();
    }, 10);
});

// store the initial value when page loads
var originalDisplayDest = $('input[name="displaydestinations"]:checked').val() || null;

$('#dpvizForm').submit(function(event) {
	event.preventDefault(); 

	var $form = $(this);
	var formData = $form.serialize();
	var processed = document.getElementById('processed')?.value || '';
	var ext = document.getElementById('ext')?.value || '';
	var jump = document.getElementById('jump')?.value || '';
	var skip = [];
	try {
		const raw = document.getElementById('skip')?.value?.trim() || '[]';
		skip = JSON.parse(raw);
	} catch (e) {
		console.error("Invalid skip array", e);
	}

	$.ajax({
		type: 'POST',
		url: $form.attr('action'),
		data: formData,
		success: function(response) {
			if (typeof exportPrefix !== 'undefined') {
				exportPrefix = ($('#exportprefix').val() || '').trim();
			}
			if (typeof updateExportFilename === 'function') {
				updateExportFilename();
			}
			var saveButton = document.getElementById("saveButton");
			const savedText = saveButton.dataset.savedLabel;
			var originalContent = saveButton.innerHTML;
		
			saveButton.innerHTML = '<i class="fa fa-check"></i> ' + savedText;
			
			setTimeout(function() {
				if (processed === 'yes') {
					generateVisualization(ext,jump,skip);
				}
				saveButton.innerHTML = originalContent;
				$('.nav-tabs li[data-name="dpbox"] a').tab('show'); // Switch tab

				// Only reload if the user actually changed displaydestinations
				var newDisplayDest = $('input[name="displaydestinations"]:checked').val();
				if (newDisplayDest !== originalDisplayDest) {
					location.reload();
				}
			}, 1250);
		},
		error: function(error) {
			alert('Form submission failed: ' + error.statusText);
			document.getElementById('saveResponse').textContent = "Request failed.";
		}
	});
});



//reload button
$('#reloadButton').click(function() {
	var ext = document.getElementById('ext')?.value || '';
	var jump = document.getElementById('jump')?.value || '';
	
	var skip = [];
	try {
		const raw = document.getElementById('skip')?.value?.trim() || '[]';
		skip = JSON.parse(raw);
	} catch (e) {
		console.error("Invalid skip array", e);
	}
	
	resetFocusMode();
	generateVisualization(ext,jump,skip);
});


function generateVisualization(ext, jump, skips) {
	const $modal = $('#nodestmodal');
	if ($modal.is(':visible')) {
		$('#nodestmodal-displayname').empty();
		$('#inlineNewForm').remove();
		closeNoDestModal();
	}
	
	const vizContainer = document.getElementById("vizContainer");
	const spinner = document.getElementById("vizSpinner");
	const recordingModal = document.getElementById('recordingmodal');
	const overlay = document.getElementById('overlay');
	const vizHeader = document.getElementById('vizHeader');
	const vizGraph = document.getElementById('vizGraph');
	const sanitizeBtn = document.getElementById("sanitizeBtn");
	const header = document.getElementById("headerSelected");
	skips = skips || [];
	
	
	closeModal('recordingmodal');
	closeModal('customTimeModal');
	//console.log("Skips:", skips.join(", "));
	
	spinner.style.display = "flex";
  $.ajax({
    url: 'ajax.php?module=dpviz&command=make',
    type: 'POST',
    data: JSON.stringify({
			ext: ext,
			jump: jump,
			skip: skips
		}),
		
    dataType: 'json',
    success: function(response) {
			const saveButton = document.getElementById('saveModalBtn');
			if ((jump && jump.trim() !== '') || skips.length > 0) {
				saveButton.style.display = 'block';
			} else {
				saveButton.style.display = 'none';
			}
			
      $('#vizHeader').html(response.vizHeader);
			vizGraph.innerHTML = "";
      if (response.gtext) {
				//console.log(response.gtext);
				let dot = response.gtext
					//.replace(/\"/g, '\"')
					//.replace(/\\n/g, '\n')
				.replace(/\\l/g, '\l')
					;
					
					viz.renderSVGElement(dot).then(element => {
						//need reload?
						fetch('ajax.php?module=dpviz&command=need_reload_status')
						.then(r => r.json())
						.then(res => {
							if (res && res.status === 'success' && res.need_reload) {
								try {
									const $top = (window.top && (window.top.$ || parent.$)) ? (window.top.$ || parent.$) : null;
									if ($top) {
										const $applyBtn = $top('#button_reload');
										if ($applyBtn.length) {
											$applyBtn
												.css('display', 'inline')
												.removeClass('hidden')
												.show();
											$applyBtn.closest('li').show().removeClass('hidden');
										}
									}
								} catch (e) {
									console.warn('Unable to show Apply Config button:', e);
								}
							}
						})
						.catch(err => console.error('Failed to check reload status:', err));


						vizGraph.innerHTML = '';
						vizGraph.appendChild(element);
						svgContainer = element;
						isFocused = false;
						isSanitized = false;
						spinner.style.display = 'none';
						checkPanZoom();
						wireGraphvizTooltips(vizGraph);						
						
						// Ctrl/Command + shift + click handler for Graphviz nodes
						element.querySelectorAll('g.node').forEach(node => {
							node.addEventListener('click', function (e) {
								
								const titleText = node.dataset.gvtitle || "";
								if (!titleText) return;

								const href = node.querySelector("a")?.getAttribute("xlink:href") || "";
								if (!href.includes("#norec")) {

									// Patterns that trigger recording modal
									const recordingPatterns = [
										"play-system-recording",
										"ext-local",
										"app-announcement-",
										"ivr-",
										"ext-group",
										"vmblast-grp",
										"app-pagegroups",
										"dynroute",
										"queuecallback",
										"ext-queues"
									];

									for (const pattern of recordingPatterns) {
										if (titleText.startsWith(pattern)) {
											closeModal("recordingmodal");
											// special case: skip vms / vmi when pattern = ext-local
											if (
												pattern === "ext-local" &&
												(titleText.includes("vms") || titleText.includes("vmi"))
											) {
												return;
											}

											
											e.preventDefault();

											if (overlay && !isFocused && !isSanitized && !e.ctrlKey && !e.metaKey && !e.shiftKey) {
												
												spinner.style.display = "flex";
												getRecording(titleText);

												setTimeout(() => {
													spinner.style.display = "none";
													recordingModal.style.display = "block";
												}, 500);
											}
											break; // stop after first match
										}
									}
								}
								
									// -----------------------
									// Open modal on noDest click
									// -----------------------
									// open modal (no event binding here)
									if (titleText.startsWith("noDest") && !isFocused && !isSanitized) {
										e.preventDefault();
										resetFocusMode();
										loadNoDestModal(titleText);
									}
									
									if (titleText.startsWith("insertDest") && !isFocused && !isSanitized) {
										e.preventDefault();
										resetFocusMode();
										loadInsertDestModal(titleText);
									}
									
									if (titleText.startsWith("newSelection") && !isFocused && !isSanitized) {
										e.preventDefault();
										resetFocusMode();
										loadNewSelectionModal(titleText);
									}
									
									if (titleText.startsWith("newEntry") && !isFocused && !isSanitized) {
										e.preventDefault();
										resetFocusMode();
										loadNewEntryModal(titleText);
									}
									if (titleText.startsWith("reset") && !isFocused && !isSanitized) {
										e.preventDefault();
										resetFocusMode();
										generateVisualization(ext,'','');
									}

									if (titleText.startsWith("undoLast") && !isFocused && !isSanitized) {
											e.preventDefault();
											resetFocusMode();

											const toRemove = titleText.replace("undoLast", "").trim();

											const index = skips.indexOf(toRemove);
											if (index !== -1) {
													skips.splice(index, 1);
											}

											generateVisualization(ext,jump,skips);
									}
									
									// Ctrl/Meta -jump
									if ((e.ctrlKey || e.metaKey) && !isFocused && !isSanitized) {
										e.preventDefault();
										resetFocusMode();
										
										generateVisualization(ext,titleText,skips);
									}
									
									// Shift Key -skip(s)
									if (e.shiftKey && !isFocused && !isSanitized) {
										e.preventDefault();
										const allowedKeywords = [
											"announcement","callback","callrecording","daynight","directory",
											"dynroute","ext-group","ext-tts","from-trunk","ivr","languages",
											"miscapp","queueprio","queues","vqueues","setcid","timeconditions",
											"vmblast-grp"
										];

										const match = allowedKeywords.find(keyword =>
												titleText.toLowerCase().includes(keyword.toLowerCase())
										);

										if (!match) {
												return;
										}

										if (!skips.includes(titleText)) {
												skips.push(titleText);
												resetFocusMode();

												generateVisualization(ext,jump,skips);
										}
									}
								
							});
							const text = node.querySelector('text');
							if (text && text.textContent.trim() === '+') {
								const link = node.querySelector('a');
								if (link) {
									link.style.textDecoration = 'none';
								}
							}
							
							
						});

            element.querySelectorAll("g.node").forEach(node => {
              node.addEventListener("click", function(e) {
                if (isFocused) {
                  selectedNodeId = this.id;
                  highlightPathToNode(this.id);
                  e.preventDefault();
                  e.stopPropagation();
                  return false;
                }
              });
            });

            element.querySelectorAll("g.edge").forEach(edge => {
              edge.addEventListener("click", function(e) {
                if (isFocused) {
                  toggleEdgeHighlight(this.id);
                  e.preventDefault();
                  e.stopPropagation();
                  return false;
                }
              });
            });
						
						// keep highlight for just one path
						document.querySelectorAll("g.edge").forEach(edge => {
							edge.addEventListener("click", (e) => {
								// clear other selections
								document.querySelectorAll("g.edge.selected").forEach(el => {
									if (el !== edge) el.classList.remove("selected");
								});

								// toggle this one
								edge.classList.toggle("selected");

								e.stopPropagation(); // don’t bubble up to SVG container
							});
						});

						// --- sanitize setup ---
						const sanitizeBtn = document.getElementById("sanitizeBtn");
						let originalFilename = "";

						// Reset any previous sanitize state first
						resetSanitize();

						// Only bind the master button once
						if (!sanitizeBtn._bound) {
								sanitizeBtn.addEventListener("click", () => {
										const texts = document.querySelectorAll("g.node text");
										const header = document.getElementById("headerSelected");
										const svgExButton = document.getElementById("svgExButton");
										const input = document.getElementById("filenameInput");
										const version = document.getElementById("version");

										if (!isSanitized) {
												// ENTER sanitize mode
												originalFilename = input.value; // store filename
												disableLinks();
												input.value = "";
												input.placeholder = translations.enterFilename + '...';
												version.style.display = "flex";

												document.querySelectorAll("g.node a").forEach(link => {
														link.addEventListener("click", e => {
																if (isSanitized) e.preventDefault();
														});
												});

												// Black out all labels
												texts.forEach(t => censor(t));

												if (header) {
														delete header.dataset.censored;
														delete header.dataset.prevColor;
														delete header.dataset.prevBg;
														censor(header);
												}
												if (svgExButton) {
														svgExButton.style.display = 'none';
												}

												setSanitizeButton("restore");

										} else {
												// EXIT sanitize mode
												input.value = originalFilename; // restore filename
												version.style.display = "none";
												texts.forEach(t => uncensor(t));

												if (header) uncensor(header);
												if (svgExButton) {
														svgExButton.style.display = 'block';
												}

												restoreLinks();
												setSanitizeButton("sanitize");
										}

										isSanitized = !isSanitized;
								});

								// Delegated handler for clicks on nodes + header
								document.addEventListener("click", e => {
										if (!isSanitized) return;

										// Node labels
										if (e.target.closest("g.node")) {
												e.stopPropagation();
												e.target.closest("g.node").querySelectorAll("text").forEach(t => toggleCensor(t));
										}

										// Header
										const header = document.getElementById("headerSelected");
										if (header && e.target === header) {
												e.stopPropagation();
												toggleCensor(header);
										}
								});

								sanitizeBtn._bound = true; // prevent duplicate bindings
						}
						//end sanitize

          })
          .catch(error => {
            console.error('Viz.js render error:', error);
						console.log(dot);
          });
      } else {
        console.error('No gtext found in response.');
      }
    },
    error: function(xhr, status, error) {
			spinner.style.display = "none";  // Hide spinner

			// xhr.responseText is the raw server response body -- on a PHP fatal
			// that is an error page containing whatever data the handler was
			// touching. Render it as text, never as markup.
			const $err = $('<div>');
			$err.append($('<strong>').text('AJAX Error:'), $('<br>'));
			$err.append(document.createTextNode(`Status: ${status}`), $('<br>'));
			$err.append(document.createTextNode(`Error: ${error}`), $('<br>'));
			$err.append(document.createTextNode(`HTTP Status: ${xhr.status}`), $('<br>'));
			$err.append(document.createTextNode('Response: '));
			$err.append($('<pre>').css('white-space', 'pre-wrap').text(xhr.responseText));

			$('#vizContainer').empty().append($err);
			console.error('AJAX Error:', status, error);
		}
  });
	
}


function getRecording(titleid) {
	const parts = titleid.split(",");
	const module = parts[0];
	const lang = parts[3];
	
	let mod = "";
	let id = "";
	let url= "";
	
	if (module.startsWith("play-system-recording")) {
		mod = 'systemrecording';
		id = parts[1];
		url = 'recordings&action=edit&id=' + id;
	}
	
	if (module.startsWith("app-announcement")) {
		const modParts = module.split("-");
		mod = modParts[1];
		id = modParts[2];
		url = 'announcement&view=form&extdisplay=' + id;
	}
	
	if (module.startsWith("ivr")) {
		const modParts = module.split("-");
		mod = modParts[0];
		id = modParts[1];
		url = 'ivr&action=edit&id=' + id;
	}
	
	if (module.startsWith("ext-group")) {
		mod = 'ringgroup';
		id = parts[1];
		url = 'ringgroups&view=form&extdisplay=' + id;
	}
	
	if (module.startsWith("vmblast-grp")) {
		mod = 'vmblast';
		id = parts[1];
		url = 'vmblast&view=form&extdisplay=' + id;
	}
	
	if (module.startsWith("app-pagegroups")) {
		mod = 'pagegroups';
		id = parts[1];
		url = 'paging&view=form&extdisplay=' + id;
	}
	
	if (module.startsWith("dynroute")) {
		const modParts = module.split("-");
		mod = modParts[0];
		id = modParts[1];
		url = 'dynroute&action=edit&id=' + id;
	}
	
	if (module.startsWith("queuecallback")) {
		const modParts = module.split("-");
		mod = modParts[0];
		id = modParts[1];
		url = 'queuecallback&view=form&id=' + id;
	}
	
	if (module.startsWith("ext-local")) {
		mod = 'voicemail';
		id = parts[1];
		ext = id.slice(3);
		url = 'voicemail&action=bsettings&ext=' + ext;
	}
	
	if (module.startsWith("ext-queues")) {
		mod = 'queues';
		id = parts[1];
		url = 'queues&view=form&extdisplay=' + id;
	}
	
	const formData = new URLSearchParams();
	formData.append('app', mod);
	formData.append('id', id);
	formData.append('lang', lang);

	fetch('ajax.php?module=dpviz&command=getrecording', {
		method: 'POST',
		headers: {
			'Content-Type': 'application/x-www-form-urlencoded'
		},
		body: formData
	})
	.then(response => {
		if (!response.ok) throw new Error("Failed to load recording info");
		return response.json();
	})
	.then(async data => {

		const description = data.modDescription;
		let recId = isNaN(Number(data.recId)) ? data.recId : Number(data.recId);
		const displayname = data.displayname;
		const audioList = document.getElementById('audioList');
		const autoplay = document.querySelector('input[name="autoplay"]:checked').value;
		audioList.innerHTML = "";

		$('#recordingmodal-title').html('<i class="fa fa-sitemap"></i> ' + translations[mod]);

		const displaynameTarget = document.getElementById('recording-displayname');
		displaynameTarget.innerHTML = '';

		// These buttons carry text straight out of the database -- IVR names,
		// announcement/queue descriptions, recording display names. Build them
		// as DOM nodes and set the label via textContent so a description
		// containing markup is shown, not executed.
		function recordingButton(iconClass, label, value, href) {
			const el = document.createElement(href ? 'a' : 'div');
			el.className = 'btn btn-default btn-lg' + (href ? '' : ' disabled');
			el.style.width = '100%';
			if (href) {
				el.href = href;          // property assignment, never parsed as markup
				el.target = '_blank';
			}

			const icon = document.createElement('i');
			icon.className = iconClass;
			el.appendChild(icon);

			el.appendChild(document.createTextNode(' ' + label + ': ' + value));

			if (href) {
				const linkIcon = document.createElement('i');
				linkIcon.className = 'fa fa-external-link';
				linkIcon.setAttribute('aria-hidden', 'true');
				el.appendChild(document.createTextNode(' '));
				el.appendChild(linkIcon);
			}
			return el;
		}

		if (mod !== 'systemrecording' && mod !== 'voicemail'){
			displaynameTarget.appendChild(recordingButton(
				'fa fa-sitemap', translations[mod], description, 'config.php?display=' + url
			));
		}
		// now decide on the recording button

		if (recId > 0) {
			// valid recording → show second button
			displaynameTarget.appendChild(recordingButton(
				'fa fa-bullhorn', translations.recordingLabel, displayname,
				'config.php?display=recordings&action=edit&id=' + recId
			));
		} else if (recId === 'voicemail'){
			displaynameTarget.appendChild(recordingButton(
				'fa fa-envelope', translations.voicemail, displayname, 'config.php?display=' + url
			));

		} else {
			// no recording → show standard message
			displaynameTarget.appendChild(recordingButton(
				'fa fa-bullhorn', translations.recordingLabel, 'None', null
			));
			return;
		}
	
		if (mod === 'voicemail' && !data.filename) {
			throw new Error(`${translations.noVmFile}`);
		}
		
		if (!data.filename || data.filename.trim() === '') {
			// plain text: the catch renders err.message via textContent now
			throw new Error(`${translations.noFilesLang} ${lang}`);
		}
		
		const filenames = data.filename.split('&').filter(f => f.trim() !== '');
		if (filenames.length === 0) {
			throw new Error("Filename array is empty after parsing.");
		}
		
		const audioElements = []; // keep all audio tags

		for (const filename of filenames) {
			//console.log("Fetching file:", filename);

			try {
				const response = await fetch('ajax.php?module=dpviz&command=getfile', {
					method: 'POST',
					headers: {
						'Content-Type': 'application/x-www-form-urlencoded'
					},
					body: `file=${encodeURIComponent(filename)}`
				});

				if (!response.ok) {
					throw new Error(`Could not fetch ${filename}`);
				}

				const blob = await response.blob();
				const headerFilename = response.headers.get('X-Filename');
				const audioUrl = URL.createObjectURL(blob);

				const container = document.createElement('div');
				container.classList.add('card', 'dpviz-mb-3', 'custom-card-bg');

				const cardBody = document.createElement('div');
				cardBody.classList.add('card-body');

				const shortFilename = headerFilename.split('/').pop();

				const cardTitle = document.createElement('h5');
				cardTitle.classList.add('card-title', 'text-left');

				// Create span for text
				const titleText = document.createElement('span');
				titleText.textContent = `${translations.audioLabel}: ${headerFilename}`;

				// Create download button
				const downloadBtn = document.createElement('button');
				downloadBtn.classList.add('btn', 'btn-sm', 'btn-outline-secondary');
				downloadBtn.innerHTML = '  <i class="fa fa-download"></i>';
				downloadBtn.title = translations.downloadFile;
				downloadBtn.style.marginLeft = '10px';

				// Handle download
				downloadBtn.addEventListener('click', () => {
					const link = document.createElement("a");
					link.href = audioUrl;                // audioUrl from your blob
					link.download = shortFilename;       // preserved filename
					document.body.appendChild(link);
					link.click();
					document.body.removeChild(link);
				});

				// Append text and button to the card title
				cardTitle.appendChild(titleText);
				cardTitle.appendChild(downloadBtn);

				// Append to card body
				cardBody.appendChild(cardTitle);

				const audio = document.createElement('audio');
				audio.controls = true;
				audio.src = audioUrl;
				cardBody.appendChild(audio);

				container.appendChild(cardBody);
				audioList.appendChild(container);

				audioElements.push(audio); // store audio
			} catch (err) {
				const container = document.createElement('div');
				container.classList.add('recording-container', 'error');

				const label = document.createElement('div');
				label.classList.add('alert', 'alert-warning');
				// filename comes from the recording's playback list / voicemail
				// paths, i.e. the database -- keep it out of innerHTML
				const nameEl = document.createElement('strong');
				nameEl.textContent = `${filename}.wav`;
				label.appendChild(document.createTextNode('File: '));
				label.appendChild(nameEl);
				label.appendChild(document.createTextNode(` ${translations.fileNotFound}`));

				container.appendChild(label);
				audioList.appendChild(container);
			}
		}

		// Chain audio playback
		audioElements.forEach((audio, index) => {
			if (autoplay === "1" && index < audioElements.length - 1) {
				audio.addEventListener('ended', () => {
					audioElements[index + 1].play().catch(err => {
						console.log("Next playback blocked:", err);
					});
				});
			}
		});
	
		if (autoplay === "1" && audioElements.length > 0) {
			setTimeout(() => {
				audioElements[0].play().catch(err => {
					console.log("Playback blocked:", err);
				});
			}, 500); // delay in ms (adjust as needed)
		}
	})
	.catch(err => {
		console.error("Fetch error:", err);

		const audioList = document.getElementById('audioList');

		const container = document.createElement('div');
		container.classList.add('recording-container', 'error');

		const label = document.createElement('div');
		label.classList.add('alert', 'alert-warning');
		// err.message can embed a database-derived filename (see the
		// "Could not fetch" throw above), so it is not safe as markup
		const errEl = document.createElement('strong');
		errEl.textContent = 'Error:';
		label.appendChild(errEl);
		label.appendChild(document.createTextNode(` ${err.message}`));

		container.appendChild(label);
		audioList.appendChild(container);
	});
}


document.addEventListener('play', function(e) {
  const audios = document.querySelectorAll('audio');
  audios.forEach(audio => {
    if (audio !== e.target) {
      audio.pause();
    }
  });
}, true);

document.addEventListener('keydown', function(event) {
  if (event.key === 'Escape') {
    const recordingModal = document.getElementById('recordingmodal');
		const savemodal = document.getElementById('saveModal');
		const nodestmodal = document.getElementById('nodestmodal');
		const customtimemodal = document.getElementById('customTimeModal');
		const whatsnewmodal = document.getElementById('whatsNewModal');
    if (recordingModal && recordingModal.style.display !== 'none') {
      closeModal('recordingmodal');
    }
		
		if (savemodal && savemodal.style.display !== 'none') {
      closeSaveModal();
    }
		
		if (nodestmodal && nodestmodal.style.display !== 'none') {
      closeNoDestModal();
    }
		
		if (customtimemodal && customtimemodal.style.display !== 'none') {
      closeCustomTimeModal();
    }

		if (whatsnewmodal && whatsnewmodal.style.display !== 'none') {
      closeWhatsNewModal();
    }
  }
});

/* ---- DPViz keyboard shortcuts ---------------------------------------- */
function dpvizShortcutsBlocked(event) {
	// Leave browser/OS combos (Ctrl+R, Cmd+R, Alt+...) alone
	if (event.ctrlKey || event.metaKey || event.altKey) {
		return true;
	}

	// Dial plan search box open: its own field owns typing and arrows
	if (document.querySelector('.select2-container--open')) {
		return true;
	}

	var ae = document.activeElement;
	if (ae) {
		var tag = ae.tagName ? ae.tagName.toLowerCase() : '';
		// select2 parks focus back on the #dialPlan <select> after a pick;
		// that must not swallow shortcuts (its open dropdown is handled
		// above). Other native selects (e.g. export format) still block.
		var isDialPlanSelect = (ae.id === 'dialPlan');
		if (ae.isContentEditable ||
			tag === 'input' ||
			tag === 'textarea' ||
			(tag === 'select' && !isDialPlanSelect)) {
			return true;
		}
	}

	// Any dpviz dialog open
	var modalIds = ['recordingmodal', 'saveModal', 'nodestmodal', 'customTimeModal', 'whatsNewModal', 'feedbackModal'];
	for (var i = 0; i < modalIds.length; i++) {
		var m = document.getElementById(modalIds[i]);
		if (m && window.getComputedStyle(m).display !== 'none') {
			return true;
		}
	}

	return false;
}

document.addEventListener('keydown', function (event) {
	if (dpvizShortcutsBlocked(event)) {
		return;
	}

	switch (event.key) {
		case 'r':
		case 'R': {
			var reloadButton = document.getElementById('reloadButton');
			if (reloadButton && !reloadButton.disabled) {
				event.preventDefault();
				$('#reloadButton').trigger('click');
			}
			break;
		}
		case 'f':
		case 'F': {
			// Fit: reset pan and zoom to the default view. No-ops when pan/zoom
			// is switched off, since there is no transform to undo.
			var vizGraph = document.getElementById('vizGraph');
			if (vizGraph && typeof vizGraph._dpvizPanZoomReset === 'function') {
				event.preventDefault();
				vizGraph._dpvizPanZoomReset();
			}
			break;
		}
		case 'ArrowLeft':
			event.preventDefault();
			$('#prevBtn').trigger('click');
			break;
		case 'ArrowRight':
			event.preventDefault();
			$('#nextBtn').trigger('click');
			break;
		case '?':
			event.preventDefault();
			$('.nav-tabs li[data-name="navigation"] a').tab('show');
			break;
	}
});

/* ---- Ensure keystrokes reach the page without a click first ---------- */
function dpvizEnsureKeyFocus() {
	// Don't yank focus from a control the user is actively using.
	// Exception: FreePBX auto-focuses its global quick-search (#fpbxsearch)
	// on every page load (assets/js/search.js). If that box is focused but
	// still empty, the user isn't searching - it's just the auto-focus - so
	// it is safe to take focus so our shortcuts work without a first click.
	var ae = document.activeElement;
	if (ae && ae !== document.body) {
		var inFpbxSearch = ae.closest ? ae.closest('#fpbxsearch') : null;
		var fpbxSearchEmpty = inFpbxSearch && String(ae.value || '').trim() === '';

		if (!fpbxSearchEmpty) {
			var t = ae.tagName ? ae.tagName.toLowerCase() : '';
			if (t === 'input' || t === 'textarea' || t === 'select' ||
				ae.isContentEditable ||
				(ae.classList && ae.classList.contains('select2-selection'))) {
				return;
			}
		}
	}

	// A dialog / open dropdown owns focus while it's up
	if (document.querySelector('.select2-container--open')) {
		return;
	}
	var modalIds = ['recordingmodal', 'saveModal', 'nodestmodal', 'customTimeModal', 'whatsNewModal', 'feedbackModal'];
	for (var i = 0; i < modalIds.length; i++) {
		var m = document.getElementById(modalIds[i]);
		if (m && window.getComputedStyle(m).display !== 'none') {
			return;
		}
	}

	var box = document.getElementById('vizContainer');
	if (!box) {
		return;
	}
	if (!box.hasAttribute('tabindex')) {
		box.setAttribute('tabindex', '-1');
	}
	try {
		box.focus({ preventScroll: true });
	} catch (e) {
		box.focus();
	}
}

(function () {
	function arm() {
		// Two staggered tries: FreePBX's search .focus() runs in its own
		// $(document).ready, which may land just after ours.
		setTimeout(dpvizEnsureKeyFocus, 150);
		setTimeout(dpvizEnsureKeyFocus, 600);
	}
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', arm);
	} else {
		arm();
	}
	window.addEventListener('load', arm);
	// Re-arm whenever the Dial Plan tab becomes visible again
	$(document).on('shown.bs.tab', 'a[href="#dpbox"]', dpvizEnsureKeyFocus);
})();

function closeModal(modalId) {
  const modal = document.getElementById(modalId);
  const overlay = document.getElementById('overlay');
  
  if (modal) modal.style.display = 'none';
  if (overlay) overlay.style.display = 'none';

  // Stop and reset all audio elements
  const allAudio = document.querySelectorAll('audio');
  allAudio.forEach(audio => {
    audio.pause();
    audio.currentTime = 0;
  });
	
	newDestinationMode = false;
}

document.addEventListener("DOMContentLoaded", () => {
    const recordingModal = document.getElementById("recordingmodal");
    const recordingHeader = document.getElementById("recordingmodal-header");
    makeDraggable(recordingModal, recordingHeader);

    const noDestModal = document.getElementById("nodestmodal");
    const noDestHeader = document.getElementById("nodestmodal-header");
    makeDraggable(noDestModal, noDestHeader);

    const customTimeModal = document.getElementById("customTimeModal");
    const customTimeHeader = document.getElementById("customTimeModal-header");
    makeDraggable(customTimeModal, customTimeHeader);

    const feedbackOverlay = document.getElementById("feedbackModal");
    const feedbackModal = feedbackOverlay?.querySelector(".feedback-modal-content");
    const feedbackHeader = feedbackModal?.querySelector(".feedback-modal-header");
    makeDraggable(feedbackModal, feedbackHeader);

    const saveOverlay = document.getElementById("saveModal");
    const saveModal = saveOverlay?.querySelector(".savemodal-content");
    const saveHeader = saveModal?.querySelector(".dpviz-save-header");
    makeDraggable(saveModal, saveHeader);
});


function makeDraggable(modal, header) {
    if (!modal || !header) return; // safety check
    
    let isDragging = false;
    let offsetX = 0;
    let offsetY = 0;

    header.addEventListener("mousedown", (e) => {
        isDragging = true;
        offsetX = e.clientX - modal.offsetLeft;
        offsetY = e.clientY - modal.offsetTop;
        document.body.style.userSelect = "none";
    });

    document.addEventListener("mouseup", () => {
        isDragging = false;
        document.body.style.userSelect = "auto";
    });

    document.addEventListener("mousemove", (e) => {
        if (isDragging) {
            modal.style.left = e.clientX - offsetX + "px";
            modal.style.top = e.clientY - offsetY + "px";
        }
    });
}


//saved view saveModal
document.getElementById('saveModalBtn').addEventListener('click', function () {
	const viewId = document.getElementById('viewId')?.value.trim() || '';
	const deleteBtn = document.getElementById('deleteViewBtn');
  if (viewId) {
    deleteBtn.style.display = 'inline-block';
  } else {
    deleteBtn.style.display = 'none';
  }
  document.getElementById('saveModal').style.display = 'block';
});

window.addEventListener('click', function (e) {
  const saveModal = document.getElementById('saveModal');
  if (e.target === saveModal) {
    saveModal.style.display = 'none';
  }
});

function closeSaveModal() {
  document.getElementById('saveModal').style.display = 'none';
}

function closeNoDestModal() {
  document.getElementById('nodestmodal').style.display = 'none';
}

function closeCustomTimeModal() {
  document.getElementById('customTimeModal').style.display = 'none';
}


//save / delete views
document.getElementById('saveViewForm').addEventListener('submit', function (e) {
  e.preventDefault();
	
	const id = document.getElementById('viewId')?.value.trim() || '';
  const description = document.getElementById('savedDescription').value;
	const ext = document.getElementById('ext')?.value.trim() || '';
	const jump = document.getElementById('jump')?.value.trim() || '';
	const skip = document.getElementById('skip')?.value.trim() || '';
	
  const data = {
		id: id,
    description: description,
    ext: ext,
    jump: jump,
    skip: skip // array
  };

	$.ajax({
		type: 'POST',
		url: 'ajax.php?module=dpviz&command=saveview',
		data: data,
		success: function (response) {
			fpbxToast(`${translations.viewSaved}`,'Success','success');
			$('#saveModal').hide();
			$('#description').val('');

			setTimeout(function () {
				location.reload();
			}, 2000);
			//console.log('Response:', response);
		},
		error: function (xhr, status, error) {
			alert('Error saving view.');
			console.error('AJAX Error:', error);
		}
	});

  // Close saveModal after submit
  document.getElementById('saveModal').style.display = 'none';
});

document.getElementById('deleteViewBtn').addEventListener('click', function () {
	const viewId = document.getElementById('viewId')?.value.trim() || '';
	
	$.ajax({
		type: 'POST',
		url: 'ajax.php?module=dpviz&command=deleteview',
		data: { id: viewId },
		success: function (response) {
			
			fpbxToast(`${translations.viewDeleted}`,'Success','success');
			$('#saveModal').hide();
			$('#description').val('');

			setTimeout(function () {
				location.reload();
			}, 2000);
			//console.log('Response:', response);
		},
		error: function (xhr, status, error) {
			alert('Error saving view.');
			console.error('AJAX Error:', error);
		}
	});
	
});


function openModal(id) {
    document.getElementById(id).style.display = 'block';
}


document.addEventListener("DOMContentLoaded", function () {
    const dtInput = document.getElementById('customDateTime');
    const applyBtn = document.getElementById('applyCustomDateTimeBtn');

    function updateApplyState() {
        applyBtn.disabled = (dtInput.value === '');
    }

    dtInput.addEventListener('input', updateApplyState);

    // call on init
    updateApplyState();
});

function applyCustomDateTime() {
    var dt = $('#customDateTime').val();

    $.post('ajax.php?module=dpviz&command=set_simtime', { customDateTime: dt })
        .done(function (res) {
            closeModal('customTimeModal');
            fpbxToast(`${translations.customTimeSaved}`,'info','info');
						$('#reloadButton').trigger('click');
        })
        .fail(function () {
            alert('Failed to save custom datetime.');
        });
}

function resetCustomDateTime() {
		document.getElementById('applyCustomDateTimeBtn').disabled = true;
		
    $.post('ajax.php?module=dpviz&command=set_simtime', { customDateTime: '' })
        .done(function () {
            closeModal('customTimeModal');
						$('#customDateTime').val('');
						fpbxToast(`${translations.customTimeRemoved}`,'info','info');
						$('#reloadButton').trigger('click');
        });
}


// ----- censor helpers -----
function censor(el) {
  if (el instanceof SVGTextElement) {
    if (el.dataset.censored) return;
    const bbox = el.getBBox();
    const rect = document.createElementNS("http://www.w3.org/2000/svg", "rect");
    rect.setAttribute("x", bbox.x);
    rect.setAttribute("y", bbox.y - 1);
    rect.setAttribute("width", bbox.width);
    rect.setAttribute("height", bbox.height + 2);
    rect.setAttribute("fill", "black");
    rect.classList.add("censor-bar");
    el.parentNode.insertBefore(rect, el);
    el.dataset.censored = "true";
  } else if (el instanceof HTMLElement) {
    if (el.dataset.censored) return;

    // Save original inline styles before overwriting
    el.dataset.prevColor = el.style.color || "";
    el.dataset.prevBg = el.style.backgroundColor || "";

    el.style.backgroundColor = "black";
    el.style.color = "black";
    el.dataset.censored = "true";
  }
}

function uncensor(el) {
  if (el instanceof SVGTextElement) {
    el.parentNode.querySelectorAll(".censor-bar").forEach(r => r.remove());
    delete el.dataset.censored;
  } else if (el instanceof HTMLElement) {
    // Restore original styles if we saved them
    if (el.dataset.prevColor !== undefined) {
      el.style.color = el.dataset.prevColor;
      delete el.dataset.prevColor;
    } else {
      el.style.color = "";
    }

    if (el.dataset.prevBg !== undefined) {
      el.style.backgroundColor = el.dataset.prevBg;
      delete el.dataset.prevBg;
    } else {
      el.style.backgroundColor = "";
    }

    delete el.dataset.censored;
		
  }
}

function toggleCensor(el) {
  if (!el) return;
  if (!el.dataset.censored) {
    censor(el);
  } else {
    uncensor(el);
  }
}

// ----- resetSanitize -----
function resetSanitize() {
  const texts = document.querySelectorAll("g.node text");
  const header = document.getElementById("headerSelected");

  // Remove all blackouts from node texts
  texts.forEach(t => uncensor(t));

  // Explicitly reset the header, even if dataset flags are stuck
  if (header) {
    header.style.color = "";
    header.style.backgroundColor = "";
    delete header.dataset.censored;
    delete header.dataset.prevColor;
    delete header.dataset.prevBg;
  }

  // Restore links
  restoreLinks();

  // Reset button text and global state
  setSanitizeButton("sanitize");
  isSanitized = false;

  // Remove per-node toggle listeners safely
  if (sanitizeBtn._nodeToggleBound) {
    document.querySelectorAll("g.node").forEach(node => {
      const text = node.querySelector("text");
      if (text) {
        const newText = text.cloneNode(true);
        text.parentNode.replaceChild(newText, text);
      }
    });
    sanitizeBtn._nodeToggleBound = false;
  }
}

function setSanitizeButton(state) {
  if (state === "sanitize") {
    sanitizeBtn.innerHTML = '<i class="fa fa-eye-slash"></i> ' + translations.sanitizeLabels;
    sanitizeBtn.classList.add("btn-default");
    sanitizeBtn.classList.remove("btn-primary", "active");
  } else if (state === "restore") {
    sanitizeBtn.innerHTML = '<i class="fa fa-eye"></i> ' + translations.restoreLabels;
    sanitizeBtn.classList.remove("btn-default");
    sanitizeBtn.classList.add("btn-primary", "active");
  }
}

//hamburger menu
const hamburger = document.getElementById("hamburgerBtn");
const dropdown = document.getElementById("dropdownMenu");

hamburger.addEventListener("click", () => {
	dropdown.style.display = dropdown.style.display === "block" ? "none" : "block";
});

// click outside to close
document.addEventListener("click", (e) => {
	if (!hamburger.contains(e.target) && !dropdown.contains(e.target)) {
		dropdown.style.display = "none";
	}
});

//PanZoom
function checkPanZoom() {
  const pan = document.querySelector('input[name="panzoom"]:checked').value;
  const vizGraph = document.getElementById("vizGraph");

  if (pan === "1") {
    vizGraph.classList.add("panzoom-enabled");
    initPanZoom("vizGraph");
  } else {
    vizGraph.classList.remove("panzoom-enabled");
  }
}

function initPanZoom(containerId) {
  const viewport = document.getElementById(containerId);
  const svgElement = viewport.querySelector('svg');

  if (!svgElement) {
    console.warn("No SVG found in container", containerId);
    return;
  }

  // #vizGraph is a persistent container; only its innerHTML is swapped on each
  // prev/next/reload render, but initPanZoom re-runs every time. Tear down the
  // previous instance's listeners first so stale closures don't stack up. A
  // leftover closure left engaged=true (e.g. after click-to-zoom, then keyboard
  // navigation with no mouse event to release it) would keep calling
  // preventDefault() and block page scrolling on the new graph.
  if (typeof viewport._dpvizPanZoomCleanup === 'function') {
    viewport._dpvizPanZoomCleanup();
  }

  let panX = 0, panY = 0, scale = 1;
  let isPanning = false;
  let startX = 0, startY = 0;
  let panStartX = 0, panStartY = 0;
  const dragThreshold = 3;
  let moved = false;

  // inertia state
  let velocityX = 0, velocityY = 0;
  let lastX = 0, lastY = 0;
  let inertiaFrame = null;

  // click-to-engage: the wheel scrolls the page until the graph is clicked,
  // then it zooms. Clicking anywhere outside the graph releases it again.
  let engaged = false;
  let hintTimer = null;

  // "Click graph to zoom" hint (reuse if a prior load left one behind)
  let hint = viewport.querySelector('.panzoom-hint');
  if (!hint) {
    hint = document.createElement('div');
    hint.className = 'panzoom-hint';
    hint.textContent = (typeof translations !== 'undefined' && translations.clickToZoom)
      ? translations.clickToZoom
      : 'Click graph to zoom';
    viewport.appendChild(hint);
  }

  function setEngaged(on) {
    if (engaged === on) return;
    engaged = on;
    if (on) hint.classList.remove('show');
  }

  // briefly surface the hint when the user wheels over a disengaged graph
  function flashHint() {
    hint.classList.add('show');
    if (hintTimer) clearTimeout(hintTimer);
    hintTimer = setTimeout(function () {
      hint.classList.remove('show');
      hintTimer = null;
    }, 1400);
  }

  // engage on a click inside the graph, release on a click anywhere else
  function onDocMouseDownEngage(e) {
    setEngaged(viewport.contains(e.target));
  }

  function updateTransform() {
    svgElement.style.transform =
      `translate(${panX}px, ${panY}px) scale(${scale})`;
    svgElement.style.transformOrigin = "0 0";
  }

  function onMouseDown(e) {
    e.preventDefault();
    isPanning = true;
    moved = false;
    startX = lastX = e.clientX;
    startY = lastY = e.clientY;
    panStartX = panX;
    panStartY = panY;

    // stop inertia if still running
    if (inertiaFrame) {
      cancelAnimationFrame(inertiaFrame);
      inertiaFrame = null;
    }

    svgElement.style.pointerEvents = 'auto';
  }

  function onMouseMove(e) {
    if (!isPanning) return;

    const dx = e.clientX - startX;
    const dy = e.clientY - startY;

    if (!moved && Math.hypot(dx, dy) > dragThreshold) {
      moved = true;
      svgElement.style.pointerEvents = 'none';
    }

    if (moved) {
      panX = panStartX + dx;
      panY = panStartY + dy;
      updateTransform();

      // track velocity
      velocityX = e.clientX - lastX;
      velocityY = e.clientY - lastY;
      lastX = e.clientX;
      lastY = e.clientY;
    }
  }

  function onMouseUp() {
    isPanning = false;
    svgElement.style.pointerEvents = 'auto';

    if (moved) {
      applyInertia();
    }
  }

  function applyInertia() {
    const friction = 0.85;    // lower = quicker stop, closer to 1 = longer glide
    const minVelocity = 0.55; // stop when slower than this

    function step() {
      velocityX *= friction;
      velocityY *= friction;

      panX += velocityX;
      panY += velocityY;
      updateTransform();

      if (Math.abs(velocityX) > minVelocity || Math.abs(velocityY) > minVelocity) {
        inertiaFrame = requestAnimationFrame(step);
      } else {
        inertiaFrame = null;
      }
    }

    inertiaFrame = requestAnimationFrame(step);
  }
	

	function onWheel(e) {
			// Not engaged: let the wheel scroll the page normally, and nudge
			// the user toward clicking the graph if they meant to zoom.
			if (!engaged) {
				flashHint();
				return;
			}
			e.preventDefault();
			const rect = viewport.getBoundingClientRect();
			const mouseX = e.clientX - rect.left;
			const mouseY = e.clientY - rect.top;

			let zoomIntensity = wheelSensitivity;

			let newScale = e.deltaY < 0
					? scale * (1 + zoomIntensity)
					: scale * (1 - zoomIntensity);

			newScale = Math.max(0.3, Math.min(10, newScale));

			panX = mouseX - (mouseX - panX) * (newScale / scale);
			panY = mouseY - (mouseY - panY) * (newScale / scale);
			scale = newScale;

			updateTransform();
	}



  // Restore the default view. Bound to the "F" (fit) shortcut rather than a
  // mouse gesture: double-click means "zoom in" in most map/canvas UIs, and it
  // collided with the node click handlers used by Highlight Paths and
  // Sanitize Labels. Works whether or not the graph is engaged for wheel zoom,
  // since you can zoom, click away to disengage, and still be left zoomed.
  function resetView() {
    // stop any inertia glide still running
    if (inertiaFrame) {
      cancelAnimationFrame(inertiaFrame);
      inertiaFrame = null;
    }

    // restore the original (un-panned, un-zoomed) view
    panX = 0;
    panY = 0;
    scale = 1;
    velocityX = velocityY = 0;
    updateTransform();
  }

  viewport.addEventListener("mousedown", onMouseDown);
  document.addEventListener("mousemove", onMouseMove);
  document.addEventListener("mouseup", onMouseUp);
  document.addEventListener("mousedown", onDocMouseDownEngage);
  viewport.addEventListener("wheel", onWheel, { passive: false });

  // Expose the reset to the global keydown handler, which lives outside this
  // closure. Cleared in the cleanup below so a stale closure can never fire
  // against a detached SVG.
  viewport._dpvizPanZoomReset = resetView;

  // Remove exactly this instance's listeners on the next re-init, and cancel any
  // pending timers/frames so nothing keeps firing against the detached SVG.
  viewport._dpvizPanZoomCleanup = function () {
    viewport.removeEventListener("mousedown", onMouseDown);
    document.removeEventListener("mousemove", onMouseMove);
    document.removeEventListener("mouseup", onMouseUp);
    document.removeEventListener("mousedown", onDocMouseDownEngage);
    viewport.removeEventListener("wheel", onWheel);
    viewport._dpvizPanZoomReset = null;
    if (hintTimer) { clearTimeout(hintTimer); hintTimer = null; }
    if (inertiaFrame) { cancelAnimationFrame(inertiaFrame); inertiaFrame = null; }
  };
}

//mouse sensitivity
let defaultSensitivity = 0.2;

// Load saved value
let wheelSensitivity = parseFloat(localStorage.getItem('dpviz_zoomSensitivity')) || defaultSensitivity;

// Elements
const zoomSlider = document.getElementById('zoomSensitivity');
const zoomValue  = document.getElementById('zoomValue');
const resetBtn   = document.getElementById('resetZoomSensitivity');

// Initialize UI
zoomSlider.value = wheelSensitivity;
zoomValue.textContent = parseFloat(wheelSensitivity.toFixed(3));

// Save when changed
zoomSlider.addEventListener('input', function () {
    wheelSensitivity = parseFloat(this.value);
    zoomValue.textContent = parseFloat(wheelSensitivity.toFixed(3));
    localStorage.setItem('dpviz_zoomSensitivity', wheelSensitivity);
});

// Reset to default
resetBtn.addEventListener('click', function () {
    wheelSensitivity = defaultSensitivity;
    zoomSlider.value = defaultSensitivity;
    zoomValue.textContent = parseFloat(defaultSensitivity.toFixed(3));
    localStorage.setItem('dpviz_zoomSensitivity', defaultSensitivity);
});




//feedback form
const modal = document.getElementById('feedbackModal');
const openBtn = document.getElementById('openFeedbackModal');
const closeBtn = document.getElementById('closeFeedbackModal');

openBtn.onclick = () => { 
    modal.style.display = 'block';
		if (typeof fpbxHelp !== 'undefined' && fpbxHelp.init) {
			fpbxHelp.init(modal);
		} 
};

closeBtn.onclick = () => { modal.style.display = 'none'; };
window.onclick = (e) => { if (e.target === modal) modal.style.display = 'none'; };

document.getElementById('feedbackForm').addEventListener('submit', (e) => {
    e.preventDefault();

    const form = e.target;             // 👈 the form element
    const formData = new FormData(form);

    fetch("ajax.php?module=dpviz&command=feedback", {
        method: "POST",
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === "ok") {
            fpbxToast(`${translations.feedbackSuccess}`, 'info','info');
        } else {
            fpbxToast(`${translations.feedbackError}`, 'error','error');
        }
    })
    .catch(err => {
        fpbxToast(`${translations.feedbackError}`, 'error','error');
    });

    modal.style.display = 'none';
    form.reset();
});



function getWhatsNewModalElements() {
	return {
		modal: document.getElementById('whatsNewModal'),
		closeBtn: document.getElementById('closeWhatsNewModal'),
		closeAction: document.getElementById('closeWhatsNewAction'),
		hideCheckbox: document.getElementById('hideWhatsNewCheckbox'),
		togglePreference: document.getElementById('toggleWhatsNewPreference')
	};
}

function showWhatsNewModal() {
	var elements = getWhatsNewModalElements();
	if (!elements.modal) {
		return;
	}

	if (elements.hideCheckbox) {
		elements.hideCheckbox.checked = !!whatsNewHiddenByServer;
		syncWhatsNewPreferenceState();
	}

	elements.modal.classList.remove('is-visible');
	elements.modal.style.display = 'block';
	window.requestAnimationFrame(function () {
		elements.modal.classList.add('is-visible');
	});
}

function closeWhatsNewModal() {
	var elements = getWhatsNewModalElements();
	if (!elements.modal) {
		return;
	}

	const hideValue = (elements.hideCheckbox && elements.hideCheckbox.checked) ? '1' : '0';
	whatsNewHiddenByServer = (hideValue === '1');

	fetch('ajax.php?module=dpviz&command=save_whatsnew', {
		method: 'POST',
		headers: {
			'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
		},
		body: 'hidewhatsnew=' + encodeURIComponent(hideValue)
	})
	.finally(() => {
		elements.modal.classList.remove('is-visible');
		window.setTimeout(function () {
			elements.modal.style.display = 'none';
		}, 180);
	});
}


function syncWhatsNewPreferenceState() {
	var elements = getWhatsNewModalElements();
	if (elements.togglePreference && elements.hideCheckbox) {
		elements.togglePreference.setAttribute('aria-checked', elements.hideCheckbox.checked ? 'true' : 'false');
	}
}

document.addEventListener("DOMContentLoaded", function () {
	const whatsNewOpenBtn = document.getElementById("openWhatsNewModal");
	if (whatsNewOpenBtn) {
		whatsNewOpenBtn.addEventListener("click", function (e) {
			e.preventDefault();
			e.stopPropagation();
			showWhatsNewModal();
		});
	}

	var elements = getWhatsNewModalElements();
	if (elements.closeBtn) {
		elements.closeBtn.addEventListener('click', closeWhatsNewModal);
	}
	if (elements.closeAction) {
		elements.closeAction.addEventListener('click', closeWhatsNewModal);
	}
	if (elements.togglePreference && elements.hideCheckbox) {
		elements.togglePreference.addEventListener('click', function (e) {
			if (e.target !== elements.hideCheckbox) {
				elements.hideCheckbox.checked = !elements.hideCheckbox.checked;
			}
			syncWhatsNewPreferenceState();
		});

		elements.hideCheckbox.addEventListener('change', syncWhatsNewPreferenceState);

		elements.togglePreference.addEventListener('keydown', function (e) {
			if (e.key === ' ' || e.key === 'Enter') {
				e.preventDefault();
				elements.hideCheckbox.checked = !elements.hideCheckbox.checked;
				syncWhatsNewPreferenceState();
			}
		});

		syncWhatsNewPreferenceState();
	}

	const whatsNewOverlay = document.getElementById("whatsNewModal");
	const whatsNewPanel = whatsNewOverlay ? whatsNewOverlay.querySelector(".whatsnew-modal-content") : null;
	const whatsNewHeader = document.getElementById("whatsNewModal-header");
	makeDraggable(whatsNewPanel, whatsNewHeader);

	if (typeof shouldShowWhatsNew !== 'undefined' && shouldShowWhatsNew) {
		setTimeout(function () {
			showWhatsNewModal();
		}, 350);
	}
});


function wireGraphvizTooltips(container) {
  const svg = container.querySelector('svg');
  if (!svg) return;

  // Tooltip DIV
  let tip = document.getElementById('gv-tooltip');
  if (!tip) {
    tip = document.createElement('div');
    tip.id = 'gv-tooltip';
    Object.assign(tip.style, {
      position: 'absolute',
      zIndex: '30',
      pointerEvents: 'none',
      background: '#222',
      color: '#fff',
      padding: '6px 10px',
      borderRadius: '8px',
      fontSize: '13px',
      whiteSpace: 'pre',
			fontFamily: 'monospace',
      opacity: 0,
      transition: 'opacity .12s'
    });
    container.style.position = 'relative';
    container.appendChild(tip);
  }

  const normalize = s => (s || '').replace(/&#10;|\\n/g, '\n');
  const titleMap = new WeakMap();

  // Capture <title>/<gvtitle> → parent, then remove
  svg.querySelectorAll('title, gvtitle').forEach(tn => {
  const parent = tn.parentNode;
  const val = (tn.textContent || '').trim();

  // skip root graph titles (graph0, graph1, etc)
  if (parent && parent.classList.contains('graph')) {
    tn.remove();
    return;
  }

  if (parent && val !== '') {
    // store for tooltips
    if (!titleMap.has(parent)) {
      titleMap.set(parent, normalize(val));
    }
    // also keep for modal logic
    if (!parent.dataset.gvtitle) {
      parent.dataset.gvtitle = normalize(val);
    }
  }

  // Remove native <title> to prevent browser tooltips
  tn.remove();
});


  // Capture data-gvtitle/xlink:title/title attributes
  svg.querySelectorAll('*').forEach(el => {
    const t = el.getAttribute('data-gvtitle') ||
              (el.getAttributeNS && el.getAttributeNS('http://www.w3.org/1999/xlink','title')) ||
              el.getAttribute('xlink:title') ||
              el.getAttribute('title');
    if (t && t.trim() !== '' && !titleMap.has(el)) {
      titleMap.set(el, normalize(t));
    }
    // strip native attributes
    el.removeAttribute('title');
    el.removeAttribute('xlink:title');
    try { el.removeAttributeNS('http://www.w3.org/1999/xlink', 'title'); } catch(_) {}
  });

  const show = (e, text) => {
    const rect = container.getBoundingClientRect();
    tip.textContent = text;
    tip.style.left = `${e.clientX - rect.left + 12}px`;
    tip.style.top  = `${e.clientY - rect.top  + 12}px`;
    tip.style.opacity = 1;
  };
  const hide = () => { tip.style.opacity = 0; };

  function findHost(target) {
    return target.closest('a, g.node, g.edge, g.cluster') || target.closest('g') || null;
  }

  function getLabelText(el) {
    const texts = el.querySelectorAll('text');
    if (!texts.length) return '';
    let parts = [];
    texts.forEach(t => {
      if (t.childNodes.length > 1) {
        let segs = [];
        t.childNodes.forEach(n => {
          if (n.textContent && n.textContent.trim() !== '') {
            segs.push(n.textContent.trim());
          }
        });
        if (segs.length) parts.push(segs.join('\n'));
      } else if (t.textContent.trim() !== '') {
        parts.push(t.textContent.trim());
      }
    });
    return parts.join('\n');
  }

  function getTooltipText(host) {
    if (!host) return '';

    // ignore root graph groups
    if (host.classList.contains('graph')) return '';

    // prefer captured xlink/title
    let el = host;
    while (el && el !== svg) {
      const t = titleMap.get(el);
      if (t && t.trim() !== '') return t;
      el = el.parentNode;
    }

    // Fallback: visible label text
    const labelText = getLabelText(host);
    if (labelText) return labelText;

    return '';
  }

  svg.addEventListener('mousemove', e => {
    const host = findHost(e.target);
    const text = getTooltipText(host);
    if (!text) return hide();
    show(e, text);
  }, { passive: true });

  svg.addEventListener('mouseleave', hide, { passive: true });
}





let nodestData = {};

const moduleMap = {
  announcement: 'Announcements',
	daynight: 'Call Flow Control',
	callrecording: 'Call Recording',
  dynroute: 'Dynamic Routes',
  incoming: 'Inbound Routes',
	ivr_details: 'IVR',
	languages: 'Languages',
	miscdests: 'Misc Destinations',
  queues_config: 'Queues',
  ringgroups: 'Ring Groups',
	setcid: 'Set CallerID',
  timeconditions: 'Time Conditions'
};


const moduleKeyAliases = {
  ivr_details: ['ivr', 'ivr_details'],
	incoming: ['incoming', 'did'],
	queues_config: ['queues', 'queues_config']
};

const filteredAllowNew = Object.keys(moduleMap)
  .filter(function (key) {
      const aliases = moduleKeyAliases[key] || [key];
      return aliases.some(function (k) {
          return window.existingModules.includes(k);
      });
  })
  .map(key => moduleMap[key]);


	



let newDestinationMode = false;

$(document).on('change', '#moduleSelect', function () {

    const mod   = $(this).val();
    const dests = nodestData[mod] || [];
    const $dest = $('#destSelect');

    $('#inlineNewForm').remove();
    $dest.empty().append('<option value="">-- Select Destination --</option>');

    const sections = Array.isArray(window.dpvizConfig?.sections)
        ? window.dpvizConfig.sections
        : [];

    let canAddNew = false;

    // module must support creation
    if (filteredAllowNew.includes(mod)) {

        // user must have permission
        if (sections.includes('*')) {
            canAddNew = true;
        } else {
            const key = Object.keys(moduleMap).find(k => moduleMap[k] === mod);
            if (key) {
                const aliases = moduleKeyAliases[key] || [key];
                canAddNew = aliases.some(k => sections.includes(k));
            }
        }
    }

    if (canAddNew) {
        $dest.append('<option value="__new__">➕ Add New…</option>');
    }

    dests.forEach(d => $dest.append(new Option(d.label, d.value)));

    $dest.trigger('change.select2');
    $('#saveNoDestBtn').prop('disabled', true);
});


$(document).on('change', '#destSelect', function () {
  const v = $(this).val();
  const $formContainer = $('#inlineNewFormContainer');
  if (v === '__new__') {
    showInlineForm($('#moduleSelect').val(), $formContainer);
    $('#saveNoDestBtn').prop('disabled', true);
  } else {
    $('#inlineNewForm').remove();
    $('#saveNoDestBtn').prop('disabled', !v);
  }
});



$(document).on('click', '#saveNoDestBtn', function () {

  const mode        = $('#nodestmodal').data('mode');   // 'ivr', 'link', or 'nodest'
  const value       = $('#destSelect').val();
  const label       = $('#destSelect option:selected').text();
  const noDestTitle = $(this).data('titleText');        // used only by noDest mode
  const rawExt      = $('#ext').val();                  // used by original noDest

  if (!value) {
    fpbxToast('Save failed: You must select a destination', 'error', 'error');
    return;
  }

  /* -----------------------------------------------------
   * MODE: IVR (multiple ivr entry rows)
   * ----------------------------------------------------- */
  if (mode === 'ivr') {
    const rowId = $('#nodestmodal').data('row');

    $('#ivrDestValue_' + rowId).val(value);
    $('#ivrDestLabel_' + rowId).text(label || '(none)');

    closeModal('nodestmodal');
    return;
  }


	/* -----------------------------------------------------
   * MODE: ADD IVR SELECTION (two-dropdown modal for linking nodes)
   * ----------------------------------------------------- */
  if (mode === 'add_ivr_entry') {
		const $destSelect = $('#destSelect');
		const $digitInput = $('#digitInput');
		const $saveBtn    = $('#saveNoDestBtn');
		const digit       = $('#digitInput').val();
		
		if (!/^[0-9*#]{1,10}$/.test(digit)) {
				return warnInvalid($digitInput, 'Please enter a valid value for Digits Pressed');
		}

		// Update dropdown
		$destSelect
			.append(new Option(label, value, true, true))
			.trigger('change.select2');

		$('#inlineNewForm').remove();
		if ($saveBtn.length) $saveBtn.prop('disabled', false);

		fetch('ajax.php?module=dpviz&command=add_ivr_entry', {
			method: 'POST',
			headers: { 'Content-Type': 'application/json' },
			body: JSON.stringify({
				titleText: noDestTitle || null,
				destination: value,
				digit: digit
			})
		})
		.then(r => r.json())
		.then(res => {
			if (res.status === 'success') {
				closeModal('nodestmodal');
				$('#saveNoDestBtn').removeData('titleText');
				$('#reloadButton').trigger('click');
				fpbxToast('Inserted successfully into current dial plan!', 'success');
				
			} else {
				fpbxToast((res.message || 'Unknown error'), 'error', 'error');
			}
		})
		.catch(e => alert('Network error: ' + e));

		return;
	}
	
	/* -----------------------------------------------------
   * MODE: ADD DYN ENTRY (two-dropdown modal for linking nodes)
   * ----------------------------------------------------- */
  if (mode === 'add_dyn_entry') {
		const $destSelect = $('#destSelect');
		const $digitInput = $('#digitInput');
		const $saveBtn    = $('#saveNoDestBtn');
		const digit       = $('#digitInput').val();
		
		if (!/^[0-9*#]{1,10}$/.test(digit)) {
				return warnInvalid($digitInput, 'Please enter a valid value for Digits Pressed');
		}

		// Update dropdown
		$destSelect
			.append(new Option(label, value, true, true))
			.trigger('change.select2');

		$('#inlineNewForm').remove();
		if ($saveBtn.length) $saveBtn.prop('disabled', false);

		// save the relationship
		fetch('ajax.php?module=dpviz&command=add_dyn_entry', {
			method: 'POST',
			headers: { 'Content-Type': 'application/json' },
			body: JSON.stringify({
				titleText: noDestTitle || null,
				destination: value,
				digit: digit
			})
		})
		.then(r => r.json())
		.then(res => {
			if (res.status === 'success') {
				closeModal('nodestmodal');
				$('#saveNoDestBtn').removeData('titleText');
				$('#reloadButton').trigger('click');
				fpbxToast('Inserted successfully into current dial plan!', 'success');
				
			} else {
				fpbxToast((res.message || 'Unknown error'), 'error', 'error');
			}
		})
		.catch(e => alert('Network error: ' + e));

		return;
	}
	
  /* -----------------------------------------------------
   * MODE: LINK (two-dropdown modal for linking nodes)
   * ----------------------------------------------------- */
  if (mode === 'link') {
		const $destSelect = $('#destSelect');
		const $saveBtn    = $('#saveNoDestBtn');

		// Update dropdown
		$destSelect
			.append(new Option(label, value, true, true))
			.trigger('change.select2');

		$('#inlineNewForm').remove();
		if ($saveBtn.length) $saveBtn.prop('disabled', false);

		// save the relationship
		fetch('ajax.php?module=dpviz&command=save_nodest', {
			method: 'POST',
			headers: { 'Content-Type': 'application/json' },
			body: JSON.stringify({
				titleText: noDestTitle || null,
				destination: value
			})
		})
		.then(r => r.json())
		.then(res => {
			if (res.status === 'success') {
				closeModal('nodestmodal');
				$('#saveNoDestBtn').removeData('titleText');
				generateVisualization(rawExt, '', '');
			} else {
				alert('Save failed: ' + (res.message || 'Unknown error'));
			}
		})
		.catch(e => alert('Network error: ' + e));

		return;
	}
	
  /* -----------------------------------------------------
   * MODE: NEW DESTINATION
   * ----------------------------------------------------- */
  if (typeof newDestinationMode !== 'undefined' && newDestinationMode === true) {
    $('#nodestmodal').hide();
    const lang = typeof currentLang !== 'undefined' ? currentLang : 'en';
    generateVisualization(value + ',' + lang, '', '');
    newDestinationMode = false;
    return;
  }

  /* -----------------------------------------------------
   * MODE: ORIGINAL noDest SAVE (default)
   * ----------------------------------------------------- */
  fetch('ajax.php?module=dpviz&command=save_nodest', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      titleText: noDestTitle || null,
      destination: value
    })
  })
  .then(r => r.json())
  .then(res => {
    if (res.status === 'success') {
      $('#nodestmodal').hide();
      $('#saveNoDestBtn').removeData('titleText');
      generateVisualization(rawExt, '', '');
    } else {
      alert('Save failed: ' + (res.message || 'Unknown error'));
    }
  })
  .catch(e => alert('Network error: ' + e));

});



function loadNoDestModal(titleText) {
  const $modal = $('#nodestmodal');
  const body   = document.getElementById('nodestmodal-displayname');
  const $title = $('#nodestmodal-title');

  $title.text(translations.newDestination || 'New Destination');
  $modal.data('mode', 'link');

  fetch('ajax.php?module=dpviz&command=nodestselect')
    .then(r => r.json())
    .then(data => {
      nodestData = data;

      const nodestKeys = Object.keys(nodestData);      // e.g. ["Announcements", "Extensions", "Queues"]
      const allowedModules = (typeof filteredAllowNew !== 'undefined'
        ? filteredAllowNew
        : []);                                        // fallback so it doesn’t blow up

      // Add allowed modules that aren't already in nodestData
      const missingModules = allowedModules.filter(m => !nodestKeys.includes(m));

      // Merge + sort alphabetically
      const combinedModules = [...nodestKeys, ...missingModules]
        .sort((a, b) => a.localeCompare(b, undefined, { sensitivity: 'base' }));

      body.innerHTML = `
        <select id="moduleSelect" style="width:100%">
          <option value="">-- Select Module --</option>
          ${combinedModules.map(m => `<option value="${m}">${m}</option>`).join('')}
        </select>
        <select id="destSelect" style="width:100%">
          <option value="">-- Select Destination --</option>
        </select>
        <div id="inlineNewFormContainer" style="margin-top:10px;"></div>
        <div style="margin-top:10px;text-align:right;">
          <button id="saveNoDestBtn" class="btn btn-primary btn-sm" disabled>Save</button>
        </div>
      `;

      $modal.show();

      $(body).find('select').select2({
        dropdownParent: $modal,
				minimumResultsForSearch: 5,
				width: '100%',
        dropdownCssClass: 's2-limit-height'
      });

      $('#saveNoDestBtn').data('titleText', titleText);
    })
    .catch(err => console.error('Error loading destinations:', err));
}


function loadInsertDestModal(titleText) {

    const $modal = $('#nodestmodal');
    const $body  = $('#nodestmodal-displayname');
    const $title = $('#nodestmodal-title');

    const excludeModules = [
        'Call Flow Control',
        'Dynamic Routes',
        'IVR',
        'Inbound Routes',
        'Misc Destinations',
        'Time Conditions'
    ];

    $title.text(translations.insertDestination || 'Insert New Destination');
    $modal.data('mode', 'insert');
    $modal.data('title', titleText);

    let previous = null;
    if (titleText.includes('|')) {
        previous = titleText.split('|')[1];
    }
    $modal.data('previous', previous);

    // sections come from PHP
    const sections = Array.isArray(dpvizConfig.sections)
        ? dpvizConfig.sections
        : [];

    const hasWildcard = sections.includes('*');

    const allowedLabels = filteredAllowNew.filter(function (label) {

        // Insert-specific exclusions
        if (excludeModules.includes(label)) {
            return false;
        }

        if (hasWildcard) {
            return true;
        }

        // label → moduleMap key
        const key = Object.keys(moduleMap).find(k => moduleMap[k] === label);
        if (!key) return false;

        const aliases = moduleKeyAliases[key] || [key];

        return aliases.some(k => sections.includes(k));
    });

    if (!allowedLabels.length) {
        $body.html(`
            <div class="alert alert-warning">
                ${translations.noPermission || 'You do not have permission to insert new destinations.'}
            </div>
        `);
        $modal.fadeIn(150);
        return;
    }

    $body.html(`
        <select id="moduleSelect" style="width:100%">
            <option value="">-- Select Module --</option>
            ${allowedLabels.map(m =>
                `<option value="${m}">${m}</option>`
            ).join('')}
        </select>
        <div id="inlineNewFormContainer" style="margin-top:10px;"></div>
    `);

    $modal.fadeIn(150);

    const $moduleSelect  = $modal.find('#moduleSelect');
    const $formContainer = $modal.find('#inlineNewFormContainer');

    if ($.fn.select2) {
        if ($moduleSelect.hasClass('select2-hidden-accessible')) {
            $moduleSelect.select2('destroy');
        }
        $moduleSelect.select2({
            dropdownParent: $modal,
            minimumResultsForSearch: 5,
            width: '100%',
            dropdownCssClass: 's2-limit-height'
        });
    }

    $moduleSelect
        .off('.inline')
        .on('select2:select.inline', function (e) {
            const label = $(this).val() || e?.params?.data?.id;
            $modal.find('#inlineNewForm').remove();
            if (label) {
                showInlineForm(label, $formContainer);
            }
        });
}



function loadNewSelectionModal(titleText) {

    const $modal = $('#nodestmodal');
    const body   = document.getElementById('nodestmodal-displayname');
    const $title = $('#nodestmodal-title');

    $title.text(translations.addSelection || 'Add Selection');
    $modal.data('mode', 'add_ivr_entry');

    fetch('ajax.php?module=dpviz&command=nodestselect')
        .then(r => r.json())
        .then(data => {

            nodestData = data;
            const nodestKeys = Object.keys(nodestData); // LABELS

            // sections come from PHP
            const sections = Array.isArray(dpvizConfig.sections)
                ? dpvizConfig.sections
                : [];

            const hasWildcard = sections.includes('*');

            const allowedLabels = filteredAllowNew.filter(function (label) {

                if (hasWildcard) {
                    return true;
                }

                // label → moduleMap key
                const key = Object.keys(moduleMap).find(
                    k => moduleMap[k] === label
                );
                if (!key) return false;

                const aliases = moduleKeyAliases[key] || [key];
                return aliases.some(k => sections.includes(k));
            });

            const combinedModules = Array.from(new Set([
                ...nodestKeys,
                ...allowedLabels
            ])).sort((a, b) =>
                a.localeCompare(b, undefined, { sensitivity: 'base' })
            );

            body.innerHTML = `
                <div class="no-dest-row">
                    <div class="no-dest-digit">
                        <input type="text"
                               id="digitInput"
                               maxlength="10"
                               class="form-control"
                               style="text-align:center;"
                               placeholder="Digit"
                               autocomplete="off">
                    </div>

                    <div class="no-dest-selects ivr-select-wrapper">
                        <select id="moduleSelect" style="width:100%">
                            <option value="">-- Select Module --</option>
                            ${combinedModules.map(m =>
                                `<option value="${m}">${m}</option>`
                            ).join('')}
                        </select>

                        <select id="destSelect" style="width:100%">
                            <option value="">-- Select Destination --</option>
                        </select>
                    </div>
                </div>

                <div id="inlineNewFormContainer" style="margin-top:10px;"></div>

                <div style="margin-top:10px;text-align:right;">
                    <button id="saveNoDestBtn"
                            class="btn btn-primary btn-sm"
                            disabled>Save</button>
                </div>
            `;

            $modal.show();

            $(body).find('select').select2({
                dropdownParent: $modal,
                minimumResultsForSearch: 5,
                width: '100%',
                dropdownCssClass: 's2-limit-height'
            });

            $('#saveNoDestBtn').data('titleText', titleText);
        })
        .catch(err => console.error('Error loading destinations:', err));
}




function loadNewEntryModal(titleText) {

    const $modal = $('#nodestmodal');
    const body   = document.getElementById('nodestmodal-displayname');
    const $title = $('#nodestmodal-title');

    $title.text(translations.addEntry || 'Add Entry');
    $modal.data('mode', 'add_dyn_entry');

    fetch('ajax.php?module=dpviz&command=nodestselect')
        .then(r => r.json())
        .then(data => {

            nodestData = data;
            const nodestKeys = Object.keys(nodestData);

            // sections from injected config
            const sections = Array.isArray(window.dpvizConfig?.sections)
                ? window.dpvizConfig.sections
                : [];

            const hasWildcard = sections.includes('*');

            // modules the user is allowed to create
            const allowedCreatable = Array.isArray(filteredAllowNew)
                ? filteredAllowNew.filter(function (label) {

                    if (hasWildcard) {
                        return true;
                    }

                    // label → moduleMap key
                    const key = Object.keys(moduleMap).find(k => moduleMap[k] === label);
                    if (!key) return false;

                    const aliases = moduleKeyAliases[key] || [key];
                    return aliases.some(k => sections.includes(k));
                })
                : [];

            // Merge backend-provided destinations + allowed creatable modules
            const combinedModules = Array.from(new Set([
                ...nodestKeys,
                ...allowedCreatable
            ])).sort((a, b) =>
                a.localeCompare(b, undefined, { sensitivity: 'base' })
            );

            body.innerHTML = `
                <div class="no-dest-row">
                    <div class="no-dest-digit">
                        <input type="text"
                               id="digitInput"
                               maxlength="10"
                               class="form-control"
                               style="text-align:center;"
                               placeholder="Digit"
                               autocomplete="off">
                    </div>

                    <div class="no-dest-selects ivr-select-wrapper">
                        <select id="moduleSelect" style="width:100%">
                            <option value="">-- Select Module --</option>
                            ${combinedModules.map(m =>
                                `<option value="${m}">${m}</option>`
                            ).join('')}
                        </select>

                        <select id="destSelect" style="width:100%">
                            <option value="">-- Select Destination --</option>
                        </select>
                    </div>
                </div>

                <div id="inlineNewFormContainer" style="margin-top:10px;"></div>

                <div style="margin-top:10px;text-align:right;">
                    <button id="saveNoDestBtn"
                            class="btn btn-primary btn-sm"
                            disabled>Save</button>
                </div>
            `;

            $modal.show();

            $(body).find('select').select2({
                dropdownParent: $modal,
                minimumResultsForSearch: 5,
                width: '100%',
                dropdownCssClass: 's2-limit-height'
            });

            $('#saveNoDestBtn').data('titleText', titleText);
        })
        .catch(err => console.error('Error loading destinations:', err));
}




function openNewDestinationModal() {

    const $modal = $('#nodestmodal');
    const $body  = $('#nodestmodal-displayname');
    const $title = $('#nodestmodal-title');

    const excludeModules = ['Set CallerID', 'Blacklist', 'Languages', 'Misc Destinations'];

    $title.text(translations.newDestination || 'New Destination');
    $modal.data('mode', 'create');

    // sections come from PHP
    const sections = Array.isArray(dpvizConfig.sections)
        ? dpvizConfig.sections
        : [];

    const hasWildcard = sections.includes('*');

    // Filter allowed modules by KEY, display by LABEL
    const allowedLabels = filteredAllowNew.filter(function (label) {

        if (excludeModules.includes(label)) {
            return false;
        }

        if (hasWildcard) {
            return true;
        }

        // Map label → moduleMap key
        const key = Object.keys(moduleMap).find(k => moduleMap[k] === label);
        if (!key) return false;

        const aliases = moduleKeyAliases[key] || [key];

        return aliases.some(function (k) {
            return sections.includes(k);
        });
    });

    if (!allowedLabels.length) {
        $body.html(`
            <div class="alert alert-warning">
                ${translations.noPermission || 'You do not have permission to create new destinations.'}
            </div>
        `);
        $modal.fadeIn(150);
        return;
    }

    $body.html(`
        <select id="moduleSelect" style="width:100%">
            <option value="">-- Select Module --</option>
            ${allowedLabels.map(label =>
                `<option value="${label}">${label}</option>`
            ).join('')}
        </select>
        <div id="inlineNewFormContainer" style="margin-top:10px;"></div>
    `);

    $modal.fadeIn(150);

    const $moduleSelect  = $modal.find('#moduleSelect');
    const $formContainer = $modal.find('#inlineNewFormContainer');

    if ($.fn.select2) {
        if ($moduleSelect.hasClass('select2-hidden-accessible')) {
            $moduleSelect.select2('destroy');
        }

        $moduleSelect.select2({
            dropdownParent: $modal,
            minimumResultsForSearch: 5,
            width: '100%',
            dropdownCssClass: 's2-limit-height'
        });
    }

    $moduleSelect
        .off('.inline')
        .on('select2:select.inline', function () {
            const label = $(this).val(); // 'IVR', 'Queues', etc.

            $modal.find('#inlineNewForm').remove();

            if (label) {
                showInlineForm(label, $formContainer);
            }
        });
}
