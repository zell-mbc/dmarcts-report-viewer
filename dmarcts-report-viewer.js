// dmarcts-report-viewer - A PHP based viewer of parsed DMARC reports.
// Copyright (C) 2016 TechSneeze.com, John Bieling and John P. New
// with additional extensions (sort order) of Klaus Tachtler.
//
// Available at:
// https://github.com/techsneeze/dmarcts-report-viewer
//
// This program is free software: you can redistribute it and/or modify it
// under the terms of the GNU General Public License as published by the Free
// Software Foundation, either version 3 of the License, or (at your option)
// any later version.
//
// This program is distributed in the hope that it will be useful, but WITHOUT
// ANY WARRANTY; without even the implied warranty of  MERCHANTABILITY or
// FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for
// more details.
//
// You should have received a copy of the GNU General Public License along with
// this program.  If not, see <http://www.gnu.org/licenses/>.
//
//####################################################################
//### configuration ##################################################
//####################################################################

// Copy dmarcts-report-viewer-config.php.sample to
// dmarcts-report-viewer-config.php and edit with the appropriate info
// for your database authentication and location.
//
//####################################################################

// ----------------------------------------------------------------------------
// Main Program
// ----------------------------------------------------------------------------


var current_report;
const getCellValue = (tr, idx) => tr.children[idx].innerText || tr.children[idx].textContent;
const comparer = (idx, asc) => (a, b) => ((v1, v2) => v1 !== '' && v2 !== '' && !isNaN(v1) && !isNaN(v2) ? v1 - v2 : v1.toString().localeCompare(v2))(getCellValue(asc ? a : b, idx), getCellValue(asc ? b : a, idx));

var cookie_name = "dmarcts-options";

// ----------------------------------------------------------------------------
//Functions
// ----------------------------------------------------------------------------

// Function to reset the <select> filters to show all records and refresh the data shown in the report_list_table.
function reset_report_list() {

	filter = document.getElementsByTagName("select");
	for (i = 0; i < filter.length; i++) {
		filter[i].selectedIndex = 0;
	}
	refresh_report_list();
}

// Function to refesh the data shown in the report_list_table using the currently selected <select> filters.
function refresh_report_list() {

	showReportlist('reportlistTbl');
}


function sorttable (table_id) {

	if (document.getElementById(table_id) != 'undefined' && document.getElementById(table_id) != null) {
		document.getElementById(table_id).querySelectorAll('th').
			forEach(th =>
				th.addEventListener(
					'click',
					(() => {
						const th_idx = Array.from(th.parentNode.children).indexOf(th); // Index of <th> element clicked, i.e. Sort column
						const table = document.getElementById(table_id);
						const tbody = table.querySelector('tbody');
						Array.from(tbody.querySelectorAll('tr')).sort(
							comparer(
								th_idx, this.asc = !this.asc)).
									forEach(tr =>
										tbody.appendChild(tr)
									);
						Array.from(th.parentNode.children).forEach(th => {th.classList.remove("asc_triangle");th.classList.remove("desc_triangle"); } );
						this.asc ? th.parentNode.children[th_idx].classList.add("asc_triangle") : th.parentNode.children[th_idx].classList.add("desc_triangle");
					}
					)
				)
			)
	}
}

function showReportlist(str) { // str is the name of the <div> to be filled

	var GETstring = "?";

	var domain = document.getElementById('selDomain').options[document.getElementById('selDomain').selectedIndex].value;
	var org = document.getElementById('selOrganisation').options[document.getElementById('selOrganisation').selectedIndex].value;
	var period = document.getElementById('selPeriod').options[document.getElementById('selPeriod').selectedIndex].value;
	var dmarc = document.getElementById('selDMARC').options[document.getElementById('selDMARC').selectedIndex].value;
	var report_status = document.getElementById('selReportStatus').options[document.getElementById('selReportStatus').selectedIndex].value;

	GETstring += "d=" + domain;
	GETstring += "&o=" + org;
	GETstring += "&p=" + period;
	GETstring += "&dmarc=" + dmarc;
	GETstring += "&rptstat=" + report_status;

	xhttp = new XMLHttpRequest();
	xhttp.onreadystatechange =
		function() {
			if (this.readyState == 4 && this.status == 200) {
				document.getElementById("report_list").innerHTML = this.responseText;
				document.getElementById("report_data").innerHTML = "";
				sorttable(str);
				set_title(domain);
				makeResizableDiv();
				if 	(
					(document.getElementById('resizer_horizontal') != 'undefined' && document.getElementById('resizer_horizontal') != null)
					||
					(document.getElementById('resizer_vertical') != 'undefined' && document.getElementById('resizer_vertical') != null)
					) {
							document.getElementById('resizer_horizontal').style.display = 'none';
							document.getElementById('resizer_vertical').style.display = 'none';
				}
			}
		};

	xhttp.open("GET", "dmarcts-report-viewer-report-list.php" + GETstring, true);
	xhttp.send();
}

function showMenu() {

		document.getElementById('menu').style.display = 'block';
		document.getElementById('screen_overlay').style.display = 'block';
}

function hideMenu() {

		document.getElementById('menu').style.display = 'none';
		document.getElementById('screen_overlay').style.display = 'none';
}

function optionMenu(_element) {

	var _div = document.getElementById('menu');
	var input = _element.getBoundingClientRect();

	_div.style.right = window.innerWidth - input.right + 'px';
	_div.style.top = document.getElementById('optionblock').bottom + 'px';

	if ( document.getElementById('menu').style.display == 'none' || document.getElementById('menu').style.display == '' ) {
		showMenu();
	} else {
		hideMenu();
	}
}

function set_title(domain) {

	domain == 'all' ? document.getElementById('title').innerText = "DMARC Reports" : document.getElementById('title').innerText = "DMARC Reports for " + domain;
}

function set_heights() {

	var report_list_height_percentage = default_reportlist_height/100;
	var taken_height =
		parseInt(window.getComputedStyle(document.getElementById('body')).getPropertyValue('margin-top'))
		+ parseInt(window.getComputedStyle(document.getElementById('body')).getPropertyValue('margin-bottom'))
		+ document.getElementById('optionblock').offsetHeight
		+ document.getElementById('title').offsetHeight
		+ document.getElementById('footer').offsetHeight
		+ parseInt(window.getComputedStyle(document.getElementById('footer')).getPropertyValue('margin-top'))
	;
	var available_height = window.innerHeight - taken_height;
	var report_list_height = parseInt(report_list_height_percentage * available_height);
	var report_data_height = available_height - report_list_height;


	document.getElementById('report_list').style.height = report_list_height + "px";
	document.getElementById('report_data').style.height = report_data_height + "px";
}

function set_report_data_heights() {

	report_data_table_div_height =
		document.getElementById('report_data').offsetHeight - document.getElementById('report_desc_container').offsetHeight
		+ "px"
		;

	document.getElementById('report_data_table_div').style.height = report_data_table_div_height;
	document.getElementById('report_data_xml').style.height = report_data_table_div_height;

}
function showXML() {

	if (document.getElementById('report_data_xml').style.display == 'none') {
		var div_height = document.getElementById('report_data_table_div').style.height
		set_report_data_widths("open_xml");
		document.getElementById('report_data_xml').style.display = 'inline-block';
		document.getElementById('report_data_table_div').style.display = 'inline-block';
		document.getElementById('report_data_table_div').style.float = 'left';
		document.getElementById('xml_html_img').src = 'html.png';
		document.getElementById('xml_html_img').title = 'Hide Raw Report XML';
		document.getElementById('xml_html_img').alt = 'Hide Raw Report XML';
		document.getElementById('resizer_vertical').style.display = "block";
	} else {
		var div_height = document.getElementById('report_data_xml').style.height
		set_report_data_widths("close_xml");
		document.getElementById('report_data_xml').style.display = 'none';
		document.getElementById('report_data_table_div').style.display = 'block';
		document.getElementById('report_data_table_div').style.float = '';
		document.getElementById('xml_html_img').src = 'xml.png';
		document.getElementById('xml_html_img').title = 'Show Raw Report XML';
		document.getElementById('xml_html_img').alt = 'Show Raw Report XML';
		document.getElementById('resizer_vertical').style.display = "none";
	}
	showResizers();
}

function set_report_data_widths (open_close) {

	report_data_xml_width_percent = .20;

	// An allowance to accomodate Report Data table width expanding/contracting when sorting arrow is added to/removed from the column title
	// HTML5 doesn't seem to currently have a built-in onresize event for divs, so to do this automatically would take some decent JS library like https://github.com/marcj/css-element-queries
	allowance = 30;

	if ( open_close == "open_xml" ) {
		if ( report_data_xml_width_last == 0){
			report_data_xml_width = parseInt(document.getElementById('report_data').offsetWidth * report_data_xml_width_percent);
			report_data_table_div_width = document.getElementById('report_data').offsetWidth - report_data_xml_width - allowance;
		} else {
			report_data_xml_width = report_data_xml_width_last
			- document.getElementById('resizer_vertical').offsetWidth / 2
			-2
			;
			// Why '-2'? Because when the report_data_xml div is resized and another report is chosen, the handle and separator jump by 2 pixels
			// If someone can find out why, please fix it (it's probably because of all the other tiny fudges all over the place)
			report_data_table_div_width = document.getElementById('report_data').offsetWidth - report_data_xml_width - allowance;
		}
	} else {
		report_data_xml_width = 0;
		report_data_table_div_width = document.getElementById('report_data').offsetWidth;
	}

	document.getElementById('report_data_xml').style.width = report_data_xml_width + "px";
	document.getElementById('report_data_table_div').style.width = report_data_table_div_width + "px";

// 	document.getElementById('report_data').style.overflow = "hidden";

}

function showReport(str) {

	document.getElementById('screen_overlay').style.display = "block";
	document.getElementById('screen_overlay').style.cursor = "wait";

	if (str == null) {
		// Hide handles
		document.getElementById('resizer_horizontal').display = 'none';
		document.getElementById('resizer_vertical').display = 'none';
		document.getElementById('screen_overlay').style.display = "none";
		document.getElementById('screen_overlay').style.cursor = "default";
		alert('No report is selected.');
		return;
	}
	setSelected(str);	// setSelected function highlights the report row that is selcted

	current_report = str;

	var xhttp;
	if (str == "") {
		document.getElementById("report_data").innerHTML = "";
		return;
	}

	var GETstring = "report=" + str;

	var HostLookup = document.getElementsByName('selHostLookup');
	var HostLookup_value = false;
	for ( var i = 0; i < HostLookup.length; i++) {
		if(HostLookup[i].checked) {
			GETstring += "&hostlookup=" + HostLookup[i].value;
			break;
		}
	}

	GETstring += "&p=" + document.getElementById('selPeriod').value;
	GETstring += "&dmarc=" + document.getElementById('selDMARC').options[document.getElementById('selDMARC').selectedIndex].value;

	xhttp = new XMLHttpRequest();
	xhttp.onreadystatechange =
		function() {
			if (this.readyState == 4 && this.status == 200) {
				document.getElementById("report_data").innerHTML = this.responseText;
				sorttable('report_data_table');
				set_report_data_heights();
				document.getElementById('screen_overlay').style.cursor = "default";
				document.getElementById('screen_overlay').style.display = "none";
				showResizers();
				if ( typeof report_data_xml_width !== 'undefined' && report_data_xml_width > 0 ) {
					showXML("open_xml");
				}
			}
		};

	xhttp.open("GET", "dmarcts-report-viewer-report-data.php?" + GETstring, true);
	xhttp.send();
}

// Functions that allow resizing of the data and raw xml divs
// Inspired by the code at https://medium.com/the-z/making-a-resizable-div-in-js-is-not-easy-as-you-think-bda19a1bc53d
function showResizers() {

	if ( document.getElementById('report_desc_container') || document.getElementById('report_data_xml') ) {
	document.getElementById('resizer_horizontal').style.display = "block";

	document.getElementById('resizer_horizontal').style.top =
		parseInt(window.getComputedStyle(document.getElementById('body')).getPropertyValue('margin-top'))
		+ document.getElementById('optionblock').offsetHeight
		+ document.getElementById('title').offsetHeight
		+ document.getElementById('report_list').offsetHeight
		- (document.getElementById('resizer_horizontal').offsetHeight)/2
		+ "px";

		document.getElementById('resizer_vertical').style.top =
			parseInt(window.getComputedStyle(document.getElementById('body')).getPropertyValue('margin-top'))
			+ document.getElementById('optionblock').offsetHeight
			+ document.getElementById('title').offsetHeight
			+ document.getElementById('report_list').offsetHeight
			+ document.getElementById('report_desc_container').offsetHeight
			+ document.getElementById('report_data_xml').offsetHeight/2
			- (document.getElementById('resizer_vertical').offsetHeight)/2
			+ "px";

		document.getElementById('resizer_vertical').style.left =
			+ document.getElementById('report_data_xml').offsetLeft
			- (document.getElementById('resizer_vertical').offsetWidth)/2
			+ 1	// Need to offset div by borderWidth/2 to centre the resizing handle on the border
				// But for some reason, some browsers (Google, Firefox; any others?) won't return the borderWidth (or even borderTopWidth) property if it set in a css file (but WILL return it if set inline with style="")
				// 		+ parseInt(document.getElementById('resizer_vertical').style.borderWidth)/2
			+ "px";
	}
}

function setSelected(str) {

	const table = document.getElementById("reportlistTbl");
	const rows = table.getElementsByTagName("tr");
	for (i = 0; i < rows.length; i++) {
		var currentRow = table.rows[i];
		currentRow.classList.remove("selected");
	}
	document.getElementById("report"+str).className += " selected";
}

function makeResizableDiv() {

	if (document.getElementById('resizer_horizontal') != 'undefined' && document.getElementById('resizer_horizontal') != null) {
		document.getElementById('resizer_horizontal').addEventListener(
			'mousedown',
				function (e) {
					e.preventDefault();
					original_width = document.getElementById('resizer_vertical').style.top;
					original_height_report_data = document.getElementById('report_data').offsetHeight;
					original_height_report_list = document.getElementById('report_list').offsetHeight;
					if ( document.getElementById('report_data_table_div') ) {
						original_height_report_data_table_div = document.getElementById('report_data_table_div').offsetHeight;
					}
					original_height_resizer_vertical = 30; // Should be equal to the in css file
					original_x = document.getElementById('resizer_horizontal').getBoundingClientRect().left;
					original_y = document.getElementById('resizer_horizontal').getBoundingClientRect().top;
					original_mouse_x = e.pageX;
					original_mouse_y = e.pageY;
					window.addEventListener('mousemove', resize);
					window.addEventListener('mouseup', stopResize);
				}
			);
	}

	function resize(e) {

		const height =	original_height_report_list
						- document.getElementById('optionblock').offsetHeight
						;
		if ( (original_mouse_y - e.pageY) < height && e.pageY < (window.innerHeight - document.getElementById('footer').offsetHeight - (document.getElementById('resizer_horizontal').offsetHeight / 2)) ) {
			// Change all cursors over Report List table to ns-cursor
			var cursors = document.getElementById("reportlistTbl").getElementsByTagName("tr");
			for(var i=0;i<cursors.length;i++){
				cursors[i].style.cursor = "ns-resize"
			}
			document.getElementById('body').style.cursor = "ns-resize";

			document.getElementById('resizer_horizontal').style.top = original_y + (e.pageY - original_mouse_y) + 'px';
			document.getElementById('resizer_vertical').style.top =
				document.getElementById('report_data_xml').offsetTop
				+ document.getElementById('report_data_xml').offsetHeight/2
				- document.getElementById('resizer_vertical').offsetHeight/2
				+ 'px'
			;
			document.getElementById('report_list').style.height =+ original_height_report_list + (e.pageY - original_mouse_y) + 'px';
			document.getElementById('report_data_xml').style.height =+ original_height_report_data_table_div + (original_mouse_y - e.pageY) + 'px';
			document.getElementById('report_data_table_div').style.height =+ original_height_report_data_table_div + (original_mouse_y - e.pageY) + 'px';
			document.getElementById('report_data').style.height =+ original_height_report_data + (original_mouse_y - e.pageY) + 'px';
			document.getElementById('body').style.height =+
				window.innerHeight
				- parseInt(window.getComputedStyle(document.getElementById('body')).getPropertyValue('margin-top'))
				- parseInt(window.getComputedStyle(document.getElementById('body')).getPropertyValue('margin-bottom'))
				+ 'px'
			;

			if ( document.getElementById('report_data_xml').offsetHeight > original_height_resizer_vertical) {
				document.getElementById('resizer_vertical').style.height = original_height_resizer_vertical + 'px';
			} else {
				document.getElementById('resizer_vertical').style.height = document.getElementById('report_data_xml').offsetHeight - 4 + 'px';
				if ( document.getElementById('report_data_xml').offsetHeight <= 4) {
					document.getElementById('resizer_vertical').style.display = 'none';
				} else {
					document.getElementById('resizer_vertical').style.display = 'block';
				}
			}
		}
	}

		function stopResize() {

			// Change all cursors back to default
			var cursors = document.getElementById("reportlistTbl").getElementsByTagName("tr");
			for(var i=0;i<cursors.length;i++){
				cursors[i].style.cursor = "default"
			}
			document.getElementById('body').style.cursor = "default";

			window.removeEventListener('mousemove', resize);
		}

	if (document.getElementById('resizer_vertical') != 'undefined' && document.getElementById('resizer_vertical') != null) {
		document.getElementById('resizer_vertical').addEventListener('mousedown', function (e) {
			e.preventDefault();// ;
			original_width_report_data_xml = document.getElementById('report_data_xml').offsetWidth;
			original_width_report_data_table_div = document.getElementById('report_data_table_div').offsetWidth;
			original_x = document.getElementById('resizer_vertical').getBoundingClientRect().left;
			original_y = document.getElementById('resizer_vertical').getBoundingClientRect().top;
			original_mouse_x = e.pageX;
			original_mouse_y = e.pageY;
			window.addEventListener('mousemove', resize_vertical);
			window.addEventListener('mouseup', stopResize_vertical);
		});
	}

	function resize_vertical(e) {

		if ( e.pageX > document.getElementById('resizer_vertical').offsetWidth && e.pageX < window.innerWidth - document.getElementById('resizer_vertical').offsetWidth ) {
			document.getElementById('body').style.cursor = "ew-resize";
			mouse_movement = e.pageX - original_mouse_x;
			document.getElementById('report_data_xml').style.width =+ original_width_report_data_xml - mouse_movement + 'px';
			document.getElementById('report_data_table_div').style.width =+ original_width_report_data_table_div + mouse_movement -5 + 'px';
			document.getElementById('resizer_vertical').style.left =
				document.getElementById('report_data_xml').offsetLeft
				- document.getElementById('resizer_vertical').offsetWidth/2
				+ 1
				+ 'px'
			;
		}

		report_data_xml_width_last = document.getElementById('report_data_xml').offsetWidth;
	}

		function stopResize_vertical() {

			document.getElementById('body').style.cursor = "default";
			window.removeEventListener('mousemove', resize_vertical);
		}
}

// Cookie Functions
// ----------------------------------------------------------------------------
function build_cookie() {

	// Don't allow cookie to be set if there are no reports displayed
	if ( document.getElementById('reportlistTbl') != "undefined" && document.getElementById('reportlistTbl') != null ) {
		// There are reports showing

		// Build cookie from various sources

		// Change the Period option from the value of the select to (i.e. 'all' or a date) to a boolean, to match the radio button option on the options page
		if ( document.getElementById('selPeriod').value == "all" ) {
			period = 0;
		} else {
			period = 1;
		}

		// Host lookup
		if ( document.getElementsByName('HostLookup')[0].checked ) {
			hostlookup = 1;
		} else {
			hostlookup = 0;
		}

		// Sort column and sort direction
		if ( document.getElementById('reportlistTbl').getElementsByTagName('thead')[0].getElementsByTagName('tr')[0].getElementsByClassName('desc_triangle').length != 0 ) {
			sort_column = document.getElementById('reportlistTbl').getElementsByTagName('thead')[0].getElementsByTagName('tr')[0].getElementsByClassName('desc_triangle')[0].id;
			sort = 0;
		} else {
			sort_column = document.getElementById('reportlistTbl').getElementsByTagName('thead')[0].getElementsByTagName('tr')[0].getElementsByClassName('asc_triangle')[0].id;
			sort = 1;
		}

		// Create cookie_value object that gets placed into cookie

		// When a new option is added, check the size of the cookie stored. The cookie size should be less than half of the maximum cookie size allowed per domain.
		// Less than half because sometimes the cookie is stored twice (once as dmarcts-options and once as dmarcts-options-tmp). Most browsers have a cookie limit of 4KB.
		// Currently, the following options generate a cookie of about 0.5KB
	cookie_value = {
			"cssfile" : document.styleSheets[0].href.split('/').pop() ,
			"report_list_height_percent" : parseInt( document.getElementById('report_list').offsetHeight/available_height * 100 + 0.5 ) ,
			"report_data_xml_width_percent" : report_data_xml_width_percent ,
			"xml_data_open" : xml_data_open ,
			"HostLookup" : hostlookup ,
			"Period" : period ,
			"DMARC" : document.getElementById('selDMARC').value ,
			"ReportStatus" : document.getElementById('selReportStatus').value ,
			"Domain" : document.getElementById('selDomain').value ,
			"Organisation" : document.getElementById('selOrganisation').value ,
			"sort_column" : sort_column ,
			"sort" : sort ,
			// "alignment_unknown" : 0 ,
			"dmarc_results_matching_only" : 0 ,
			"report_data_status" : "all"
		};

 		cookie_value = JSON.stringify(cookie_value);
		setCookie(cookie_name, cookie_value, 365)
	} else {
		// There are NO reports showing
		alert("Settings cannot be saved if there are no reports to display.\n\nChange the filters to show some reports.");
	}
	setCookie("dmarcts-options-tmp", "", -365)
	hideMenu();
}

function get_cookie(name) {

	name = name + "=";
	var cookie_str = decodeURIComponent(document.cookie);
  var cookie_array = cookie_str.split(';');
  for(var i = 0; i <cookie_array.length; i++) {
    var c = cookie_array[i];
    while ( c.charAt(0) == " " ) {
      c = c.substring(1);
    }
    if ( c.indexOf(name) == 0 ) {
      return c.substring(name.length, c.length);
    }
  }
  return "";
}

function setCookie(name, value, exp_days) {

  var d = new Date();
  d.setTime(d.getTime() + (exp_days*24*60*60*1000));
  var expires = "expires="+ d.toUTCString();
  document.cookie = name + "=" + encodeURI(value) + ";" + expires + ";path=/";
}

function resetOptions() {

	stored_cookie = get_cookie('dmarcts-options-tmp');

	if ( stored_cookie == "" || stored_cookie == null ){
		stored_cookie = get_cookie('dmarcts-options');
		setCookie("dmarcts-options-tmp", stored_cookie, 365)
		setCookie("dmarcts-options", "", 365)
	}
	window.location.href = 'dmarcts-report-viewer-options.php';
}

function cancelOptions() {

	stored_cookie = get_cookie('dmarcts-options-tmp');

	if ( stored_cookie ){
		setCookie("dmarcts-options", stored_cookie, 365)
		setCookie("dmarcts-options-tmp", "", -365)
	}
	window.location.href = 'dmarcts-report-viewer.php';
}
