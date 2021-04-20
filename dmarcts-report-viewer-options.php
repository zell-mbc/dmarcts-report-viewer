<?php

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
// Edit the configuration variables in dmarcts-report-viewer.js with your preferences.
//
//####################################################################
//### functions ######################################################
//####################################################################

function html ($domains = array(), $orgs = array(), $dmarc_result_select = array(), $report_status_select = array(), $report_list_columns = array(), $filter , $cssfiles = array() ) {

	global $dmarc_result;
	global $options;
	global $cookie_options;

	global $html;

	$html[] = "<!DOCTYPE html>";
	$html[] = "<html>";
	$html[] = "	<head>";
	$html[] = "		<title>DMARC Report Viewer</title>";
	$html[] = "		<link id='css_stylesheet' rel='stylesheet' href='" . $cookie_options["cssfile"] . "'>";
	$html[] = "		<script src='dmarcts-report-viewer.js'></script>";
	$html[] = "		<meta charset=\"UTF-8\" />";
	$html[] = "		<meta name='google' content='notranslate' />";
	$html[] = "		<meta http-equiv=\"Content-Language\" content=\"en_US\" />";
	$html[] = "	</head>";

	$html[] = "	<body id='body')>";

// 	if ($_SERVER["REQUEST_METHOD"] == "POST") {
// 		$html[] = "<div  style='top: 0px;left: 0px;height: 100%;width: 100%;position: absolute;display: block;z-index: 1;align-items: center;display: flex;justify-content: center;height: 50%;'>";
// 		$html[] = "<div style='padding: 20px;background-color: whitesmoke;border: 1px solid black;border-radius: 2px;box-shadow: 7px 7px 3px grey;text-align: center;'>";
// 		$html[] = "Your settings have been saved to the database.<br><br>";
// 		$html[] = "<button type=\"button\" onclick=\"window.location.href = 'dmarcts-report-viewer.php';\" title=\"Return to Reports\">Return to Reports</button>&nbsp;";
// 		$html[] = "<button type=\"button\" onclick=\"window.location.href = 'dmarcts-report-viewer-options.php';\" title=\"Return to Options.\">Return to Options</button>";
// 		$html[] = "</div>";
// 		$html[] = "</div>";
// 		$filter = "style='filter: blur(3px);opacity: 50%;'";
// 	}

	$html[] = "		<div id='title' class='title'" . $filter . ">DMARCTS Options</div>";

	$html[] = "		<form " . $filter . "method=\"post\">";
	$html[] = "			<table class='optionlist'>";

	$option = array_keys($options);

	foreach ($option as $option_name) {
		foreach($options[$option_name] as $key=>$value) {
			switch ($options[$option_name]['option_type']) {
				case "heading":
					create_heading($options[$option_name]['option_label']);
					break 2;
				case "text":
				case "number":
					create_input_text($option_name, $options[$option_name]);
					break 2;
				case "radio":
				case "checkbox":
					create_input_radio($option_name);
					break 2;
				case "select":
					// For a select option, the option_values column contains the name of an array variable, e.g.$org, that is built in dmarcts-report-viewer.php
					$var = str_replace("$", "", $options[$option_name]['option_values']);	// Remove the '$', so e.g. '$org' becomes 'org'
					create_select($option_name, $options[$option_name], $$var);	// Double $$ explanation: $var = org, so $$var = ${$var} = ${org} = $org
					break 2;
				default:
					break 2;
			}
		}
	}
	$html[] = "			</table>";

	$html[] = "			<div style='text-align: center;'>";
	$html[] = "				<button type=\"button\" onclick=\"cancelOptions();\" title=\"Cancel changes and return to reports.\">Cancel</button>";

	$html[] = "				<button type=\"button\" onclick=\"resetOptions()\" title=\"Reset all options to their default values.\">Reset</button>";
	$html[] = "				<input type=\"submit\" title=\"Save changes and return to reports.\" value=\"Save\">";
	$html[] = "			</div>";
	$html[] = "		</form>";
	$html[] = "		<br /><br />";

	//	Page Footer
	//	--------------------------------------------------------------------------
	$html[] = "		<div id='footer' class='footer'" . $filter . ">&copy; 2016-" . date("Y") . " by <a href='http://www.techsneeze.com'>TechSneeze.com</a>, John Bieling and <a href='mailto:dmarcts-report-viewer@hazelden.ca'>John P. New</a>.</div>";
	$html[] = "	</body>";
	$html[] = "</html>";

	return implode("\n",$html);
}

function create_heading($option_label) {

	global $html;

	$html[] = "				<tr class='option_title'>";
	$html[] = "					<td colspan='2'>";
	$html[] = "						<span>" . $option_label . "</span>";
	$html[] = "					</td>";
	$html[] = "				</tr>";
}

function create_input_text($option_name, $option = array()) {

	global $html;
	global $cookie_options;

	$extra_options = "";
	$after = "";

	$values = $option["option_values"];

	if (isset($cookie_options[$option_name]) ) {
		$value = "value='" . $cookie_options[$option_name] . "'";
	} else {
		$value = "";
	}
	$html[] = "				<tr>";
	$html[] = "					<td class='left_column'>";
	$html[] = "						<span class='bold'><label for=" . $option_name . ">" . $option["option_label"] . " </label></span>";
	$html[] = "						<br>";
	$html[] = "						<span class='option_description'>" . $option["option_description"] . "</span>";
	$html[] = "					</td>";
	$html[] = "					<td class='right_column'>";

	switch ($option["option_type"]) {
		case "number":
			if ( $values['min'] != "" && $values['max'] != "" ) {
				$extra_options = " min='" . $values['min'] . "' max='" . $values['max'] . "'";
			}
			if ( $values['units'] != "" ) {
				$after = " " . $values['units'];
			}
			break;
		default:
			break;
	}

	$html[] = "						<input $value type=" . $option["option_type"] . " id=" . $option_name . " name=" . $option_name . $extra_options . ">" . $after . "<br>";
	$html[] = "					</td>";
	$html[] = "				</tr>";
}

function create_input_radio($option_name) {

	global $html;
	global $options;
	global $cookie_options;

	$values = $options[$option_name]["option_values"];
	$html[] = "				<tr>";
	$html[] = "					<td class='left_column'>";
	$html[] = "						<span  class='bold'>" . $options[$option_name]["option_label"] . "</span>";
	$html[] = "						<br>";
	$html[] = "						<span class='option_description'>" . $options[$option_name]["option_description"] . "</span>";
	$html[] = "					</td>";
	$html[] = "					<td class='right_column'>";
	for ($i = 0; $i < sizeof($values); $i+=2) {
		$html[] =	"						<input type=" . $options[$option_name]["option_type"] . " id=" . strtolower(str_replace(" ", "_", $values[$i+1])) . " name=" . $option_name . ($options[$option_name]["option_type"] == "checkbox" ? "[]" : "" ) . " value='" . $values[$i] . "'" . checked($option_name, $values[$i]) . "><label for=" . $option_name . ">" . $values[$i+1] . " </label><br />";
	}
	$html[] = "					</td>";
	$html[] = "				</tr>";
}

function checked($option_name, $values) {

	global $options;
	global $cookie_options;
	$option_values = $options[$option_name]["option_values"];

	if ( is_array($cookie_options[$option_name]) ) {
		foreach ( $cookie_options[$option_name] as $cookie_option_value ) {
			if ( $cookie_option_value == $values ) {
				return " checked=\"checked\"";
			}
		}
	} else {
		if ( $cookie_options[$option_name] == $values ) {
			return " checked=\"checked\"";
		}
	}
}

function create_select($option_name, $option = array(), $var) {

	global $html;
	global $cookie_options;
	// $ var is the array variable, e.g. $org

	$values = $option["option_values"];
	$selected = "";
	$js = "";

	$html[] = "					<tr>";
	$html[] = "						<td class='left_column'>";
	$html[] = "							<span  class='bold'><label for=" . $option_name . ">" . $option["option_label"] . " </label></span>";
	$html[] = "							<br>";
	$html[] = "							<span class='option_description'>" . $option["option_description"] . "</span>";
	$html[] = "						</td>";
	$html[] = "					<td class='right_column'>";

	if ( $option_name == "cssfile" ) {
		$js = " onchange='change_stylesheet();'";
	}

	$html[] = "						<select name='" . $option_name . "' id='sel" . $option_name . "'" . $js . ">";
	foreach ($var as $key => $value) {
		if ( $cookie_options[$option_name] == $key ) {
			$selected = "selected";
		} else {
			$selected = "";
		}
		$html[] =	"							<option value='" . $key . "' " . $selected . ">" . $value . "</option>";
	}

	$html[] = "					</td>";
	$html[] = "				</tr>";
}

function test_input($data) {

  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);

  return $data;
}


//####################################################################
//### main ###########################################################
//####################################################################

// These files must exist, in the same folder as this script.
include "dmarcts-report-viewer-config.php";
include "dmarcts-report-viewer-common.php";

// Get all configuration options
// --------------------------------------------------------------------------
configure();


// Make a MySQL Connection using mysqli
// --------------------------------------------------------------------------
$mysqli = new mysqli($dbhost, $dbuser, $dbpass, $dbname, $dbport);
if ($mysqli->connect_errno) {
	echo "Error: Failed to make a MySQL connection<br />";
	echo "Errno: " . $mysqli->connect_errno . " ";
	echo "Error: " . $mysqli->connect_error . " ";
// Debug ONLY. This will expose database credentials when database connection fails
// 	echo "Database connection information: <br />dbhost: " . $dbhost . "<br />dbuser: " . $dbuser . "<br />dbpass: " . $dbpass . "<br />dbname: " . $dbname . "<br />dbport: " . $dbport . "<br />";
	exit;
}


// Get all css files in dmartcts directory
// --------------------------------------------------------------------------
$cssfiles = array();
$dir = dirname(__FILE__);
$scan_arr = scandir($dir);
$files_arr = array_diff($scan_arr, array('.','..') );

foreach ($files_arr as $file) {
	$file_ext = pathinfo($file, PATHINFO_EXTENSION);
	if ( $file_ext=="css" ) {
		$cssfiles[$file] = $file;
	}
}


// Get all domains reported
// --------------------------------------------------------------------------
$sql="
SELECT
	DISTINCT domain
FROM
	report
ORDER BY domain";

$query = $mysqli->query($sql) or die("Query failed: ".$mysqli->error." (Error #" .$mysqli->errno.")");

$domains['all'] = "[all]";
while($row = $query->fetch_assoc()) {
	$domains[$row['domain']] = $row['domain'];
}

// Get all reporters
// --------------------------------------------------------------------------
$sql="
SELECT
	DISTINCT org
FROM
	report
ORDER BY org";


$i = 1;
$dmarc_result_select['all'] = "[all]";
foreach($dmarc_result as $key => $value) {
			$dmarc_result_select[$key] = $value['text'];
			$i++;
}


$i = 1;
$report_status_select['all'] = "[all]";
foreach($dmarc_result as $key => $value) {
			$report_status_select[$key] = $value['status_text'];
			$i++;
}


$query = $mysqli->query($sql) or die("Query failed: ".$mysqli->error." (Error #" .$mysqli->errno.")");
$orgs['all'] = "[all]";
while($row = $query->fetch_assoc()) {
	$orgs[$row['org']] = $row['org'];
}


// Generate Page with report list and report data (if a report is selected).
// --------------------------------------------------------------------------
echo html(
	$domains,
	$orgs,
	$dmarc_result_select,
	$report_status_select,
	$report_list_columns,
	$filter,
	$cssfiles
);
// }
?>
