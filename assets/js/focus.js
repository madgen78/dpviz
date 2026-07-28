if (document.getElementById("reloadButton")) {

// Focus button handler
document.getElementById("focus").addEventListener("click", function(e) {
	e.stopPropagation();
	e.preventDefault();
	toggleFocusMode();
	return false;
}, false);

// Toggle edge highlight
function toggleEdgeHighlight(edgeId) {
	if (!svgContainer) return;
	const edge = document.getElementById(edgeId);
	if (!edge) return;

	if (highlightedEdges.has(edgeId)) {
		// Remove highlight
		highlightedEdges.delete(edgeId);
		resetEdgeStyle(edge);
	} else {
		// Add highlight
		highlightedEdges.add(edgeId);
		applyEdgeHighlight(edge);
	}
}

function applyEdgeHighlight(edge) {
	const edgePath = edge.querySelector("path");
	if (edgePath) {
		edgePath.style.stroke = "red";
		edgePath.style.strokeWidth = "3px";
	}

	const polygon = edge.querySelector("polygon");
	if (polygon) {
		polygon.style.fill = "red";
		polygon.style.stroke = "red";
	}

	edge.querySelectorAll("text").forEach(text => {
		text.style.fill = "red";
		text.style.fontWeight = "bold";
	});
}

function resetEdgeStyle(edge) {
	const edgePath = edge.querySelector("path");
	if (edgePath) {
		edgePath.style.stroke = "";
		edgePath.style.strokeWidth = "";
	}

	const polygon = edge.querySelector("polygon");
	if (polygon) {
		polygon.style.fill = "";
		polygon.style.stroke = "";
	}

	edge.querySelectorAll("text").forEach(text => {
		text.style.fill = "";
		text.style.fontWeight = "";
	});
}

function resetEdges() {
	if (!svgContainer) return;
	highlightedEdges.clear();
	svgContainer.querySelectorAll("g.edge").forEach(resetEdgeStyle);
}

const focusBtn = document.getElementById("focus");

function setFocusButton(state) {
  if (state === "highlight") {
    focusBtn.innerHTML = '<i class="fa fa-magic"></i> ' + translations.highlight;
    focusBtn.classList.add("btn-default");
    focusBtn.classList.remove("btn-primary", "active");
  } else if (state === "remove") {
    focusBtn.innerHTML = '<i class="fa fa-magic"></i> ' + translations.remove;
    focusBtn.classList.remove("btn-default");
    focusBtn.classList.add("btn-primary", "active");
  }
}

function resetFocusMode() {
  resetEdges();
  restoreLinks();
  isFocused = false;
  setFocusButton("highlight");
}

function toggleFocusMode() {
  if (!svgContainer) return;
  if (isFocused) {
    resetFocusMode();
  } else {
    disableLinks();
    isFocused = true;
    setFocusButton("remove");
  }
}

function disableLinks() {
	if (!svgContainer) return;
	svgContainer.querySelectorAll("g.node a").forEach(link => {
		if (link.hasAttribute("xlink:href")) {
			originalLinks.set(link, link.getAttribute("xlink:href"));
			link.setAttribute("xlink:href", "javascript:void(0);");
		}
	});
}

function restoreLinks() {
	if (!svgContainer) return;
	svgContainer.querySelectorAll("g.node a").forEach(link => {
		const originalHref = originalLinks.get(link);
		if (originalHref) {
			link.setAttribute("xlink:href", originalHref);
		}
	});
	originalLinks.clear();
}

/**
 * Matches on dataset.gvtitle, which wireGraphvizTooltips sets.
 */
function highlightPathToNode(nodeId) {
	if (!svgContainer) return;
	resetEdges();

	const node = document.getElementById(nodeId);
	if (!node) return;

	// get from dataset.gvtitle
	const targetNodeName = node.dataset.gvtitle ? node.dataset.gvtitle.trim() : "";
	if (!targetNodeName) return;

	const visitedNodes = new Set([targetNodeName]);
	const processedEdges = new Set();

	function findConnectedNodes(nodeName) {
		svgContainer.querySelectorAll("g.edge").forEach(edge => {
			if (processedEdges.has(edge.id)) return;

			// edge "title" was stored in dataset.gvtitle by tooltip wiring
			const edgeTitle = edge.dataset.gvtitle || "";
			if (!edgeTitle.includes("->")) return;

			const [sourceNode, destNode] = edgeTitle.split("->").map(s => s.trim());
			if (destNode === nodeName) {
				processedEdges.add(edge.id);
				visitedNodes.add(sourceNode);
				applyEdgeHighlight(edge);
				findConnectedNodes(sourceNode);
			}
		});
	}

	findConnectedNodes(targetNodeName);
}

}
