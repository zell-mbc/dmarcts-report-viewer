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

//####################################################################
//### variables ######################################################
//####################################################################

// The order in which the options appear here is the order they appear in the DMARC Results dropdown box
$dmarc_result = array(

	'DMARC_PASS' => array(
		'text' => 'Pass',
		'status_text' => 'All Passed.',
		'color' => 'green',
		'status_sort_key' => 3,
	),
	'DMARC_FAIL' => array(
		'text' => 'Fail',
		'status_text' => 'All Failed.',
		'color' => 'red',
		'status_sort_key' => 0,
	),
	'DMARC_PASS_AND_FAIL' => array(
		'text' => 'Mixed',
		'status_text' => 'At least one failed result.',
		'color' => 'orange',
		'status_sort_key' => 1,
	),
	'DMARC_OTHER_CONDITION' => array(
		'text' => 'Other',
		'status_text' => 'Other condition.',
		'color' => 'yellow',
		'status_sort_key' => 2,
	),
);

//####################################################################
//### functions ######################################################
//####################################################################

function main() {

	include "dmarcts-report-viewer-config.php";
}

// This function sets variables for the DMARC Result portion (left half-circle) in the Report List
function get_dmarc_result($row) {

	global $dmarc_result;
	$color = "";
	$color_sort_key = "";
	$result_text = "";

	if (($row['dmarc_result_min'] == 0) && ($row['dmarc_result_max'] == 0)) {
		$color     = $dmarc_result['DMARC_FAIL']['color'];
		$color_sort_key = $dmarc_result['DMARC_FAIL']['status_sort_key'];
		$result_text = $dmarc_result['DMARC_FAIL']['text'];
	} elseif (($row['dmarc_result_min'] == 0) && ($row['dmarc_result_max'] == 1 || $row['dmarc_result_max'] == 2)) {
		$color     = $dmarc_result['DMARC_PASS_AND_FAIL']['color'];
		$color_sort_key = $dmarc_result['DMARC_PASS_AND_FAIL']['status_sort_key'];
		$result_text = $dmarc_result['DMARC_PASS_AND_FAIL']['text'];
	} elseif (($row['dmarc_result_min'] == 1 || $row['dmarc_result_min'] == 2) && ($row['dmarc_result_max'] == 1 || $row['dmarc_result_max'] == 2)) {
		$color     = $dmarc_result['DMARC_PASS']['color'];
		$color_sort_key = $dmarc_result['DMARC_PASS']['status_sort_key'];
		$result_text = $dmarc_result['DMARC_PASS']['text'];
	} else {
		$color     = $dmarc_result['DMARC_OTHER_CONDITION']['color'];
		$color_sort_key = $dmarc_result['DMARC_OTHER_CONDITION']['status_sort_key'];
		$result_text = $dmarc_result['DMARC_OTHER_CONDITION']['text'];
	}
	return array('color' => $color, 'status_sort_key' => $color_sort_key, 'result' => $result_text);
}

// This function sets variables for the All Results portion (right half-circle) in the Report List table
function get_report_status($row) {

	global $dmarc_result;
	$color = "";
	$color_sort_key = "";
	$result_text = "";

	$dmarc_status_min = min($row['dkim_align_min'],$row['spf_align_min'],$row['dkim_result_min'],$row['spf_result_min'],$row['dmarc_result_min']);

	if ($row['dkim_align_min'] == 0 && $row['spf_align_min'] == 0 && $row['dkim_result_min'] == 0 && $row['spf_result_min'] == 0 && $row['dmarc_result_min'] == 0) {
		$color = $dmarc_result['DMARC_FAIL']['color'];
		$color_sort_key = $dmarc_result['DMARC_FAIL']['status_sort_key'];
		$result_text = $dmarc_result['DMARC_FAIL']['status_text'];
	} else {
		switch ($dmarc_status_min) {
			case 0:
				$color = $dmarc_result['DMARC_PASS_AND_FAIL']['color'];
				$color_sort_key = $dmarc_result['DMARC_PASS_AND_FAIL']['status_sort_key'];
				$result_text = $dmarc_result['DMARC_PASS_AND_FAIL']['status_text'];
				break;
			case 1:
				$color = $dmarc_result['DMARC_OTHER_CONDITION']['color'];
				$color_sort_key = $dmarc_result['DMARC_OTHER_CONDITION']['status_sort_key'];
				$result_text = $dmarc_result['DMARC_OTHER_CONDITION']['status_text'];
				break;
			case 2:
				$color = $dmarc_result['DMARC_PASS']['color'];
				$color_sort_key = $dmarc_result['DMARC_PASS']['status_sort_key'];
				$result_text = $dmarc_result['DMARC_PASS']['status_text'];
				break;
			default:
				break;
		}
	}

	return array('color' => $color, 'status_sort_key' => $color_sort_key, 'status_text' => $result_text);
}


function get_status_color($row) {
	global $dmarc_result;
	$status = "";
	$status_num = "";
	if (($row['dkimresult'] == "fail") && ($row['spfresult'] == "fail")) {
		$status     = $dmarc_result['DKIM_AND_SPF_FAIL']['color'];
		$status_num = $dmarc_result['DKIM_AND_SPF_FAIL']['status_num'];
	} elseif (($row['dkimresult'] == "fail") || ($row['spfresult'] == "fail")) {
		$status     = $dmarc_result['DKIM_OR_SPF_FAIL']['color'];
		$status_num = $dmarc_result['DKIM_OR_SPF_FAIL']['status_num'];
	} elseif (($row['dkimresult'] == "pass") && ($row['spfresult'] == "pass")) {
		$status     = $dmarc_result['DKIM_AND_SPF_PASS']['color'];
		$status_num = $dmarc_result['DKIM_AND_SPF_PASS']['status_num'];
	} else {
		$status     = $dmarc_result['OTHER_CONDITION']['color'];
		$status_num = $dmarc_result['OTHER_CONDITION']['status_num'];
	}
#	$status .= "\"><span style='display:none;'>" . $status_content . "</span></span>";
#	$status_num .= "\"><span style='display:none;'>" . $status_content . "</span></span>";
    return array($status, $status_num);
}

function format_date($date, $format) {
    $answer = date($format, strtotime($date));
    return $answer;
};

