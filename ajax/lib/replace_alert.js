/** Google Maps throws authentication errors using an alert box, which is very ugly.
 * This file replaces window.alert with a function that shows the error right before the
 * map_canvas element. */
function replaceAlert() {
	var errorElm = document.createElement("p");
	errorElm.id = "map_errors";

	var mapElm = document.getElementById("map_canvas");
	if (mapElm) {
		mapElm.parentElement.insertBefore(errorElm, mapElm);

		window.alert = function (text) {
			errorElm.innerText = text;
			errorElm.style.display = 'block';
		}
	} // else: no map, nothing to do.
}
window.addEventListener('DOMContentLoaded', replaceAlert, false);


