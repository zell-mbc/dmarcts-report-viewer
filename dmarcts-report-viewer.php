<?php

// dmarcts-report-viewer - A PHP based viewer of parsed DMARC reports.
// Copyright (C) 2016 TechSneeze.com and John Bieling ( https://github.com/jobisoft/ )
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

$dbhost="localhost";
$dbname="dmarc";
$dbuser="dmarc";
$dbpass="xxx";



//####################################################################
//### functions ######################################################
//####################################################################

function format_date($date, $format) {
	$answer = date($format, strtotime($date));
	return $answer;
};

function tmpl_reportList() {
	$reportlist[] = "";
	$reportlist[] = "<!-- Start of report list -->";

	$reportlist[] = "<h1>DMARC Reports</h1>";
	$reportlist[] = "<table class='reportlist'>";
	$reportlist[] = "  <thead>";
	$reportlist[] = "    <tr>";
	$reportlist[] = "      <th>Start Date</th>";
	$reportlist[] = "      <th>End Date</th>";
	$reportlist[] = "      <th>Domain</th>";
	$reportlist[] = "      <th>Reporting Organization</th>";
	$reportlist[] = "      <th>Report ID</th>";
	$reportlist[] = "      <th>Messages</th>";
	$reportlist[] = "    </tr>";
	$reportlist[] = "  </thead>";

	$reportlist[] = "  <tbody>";

	$query_report = "SELECT * FROM report ORDER BY mindate";
	$result_report = mysql_query($query_report) or die(mysql_error());
	while($row = mysql_fetch_array($result_report)) {
		$message_query = "SELECT *, SUM(rcount) FROM rptrecord WHERE serial = {$row['serial']}";
		$message_process = mysql_query($message_query) or die(mysql_error());
		$message_result = mysql_fetch_array($message_process);
		$date_output_format = "r";
		$reportlist[] =  "    <tr>";
		$reportlist[] =  "      <td class='right'>". format_date($row['mindate'], $date_output_format). "</td>";
		$reportlist[] =  "      <td class='right'>". format_date($row['maxdate'], $date_output_format). "</td>";
		$reportlist[] =  "      <td class='center'>". $row['domain']. "</td>";
		$reportlist[] =  "      <td class='center'>". $row['org']. "</td>";
		$reportlist[] =  "      <td class='center'><a href='?report=". $row['serial']. "#rpt". $row['serial']. "'>". $row['reportid']. "</a></td>";
		$reportlist[] =  "      <td class='center'>". $message_result['SUM(rcount)']. "</td>";
		$reportlist[] =  "    </tr>";
	}
	$reportlist[] =  "  </tbody>";

	$reportlist[] =  "</table>";

	$reportlist[] = "<!-- End of report list -->";
	$reportlist[] = "";

	#indent generated html by 2 extra spaces
	return implode("\n  ",$reportlist);
}

function tmpl_reportData($reportnumber) {

	if (!$reportnumber) {
		return "";
	}

	$reportdata[] = "";
	$reportdata[] = "<!-- Start of report rata -->";

	$sql = "SELECT * FROM report where serial = $reportnumber";
	$query = mysql_query($sql) or die(mysql_error());
	if ($row = mysql_fetch_array($query)) {
		$reportdata[] = "<div class='center reportdesc'><p> Report from ".$row['org']." for ".$row['domain']."<br>(". format_date($row['mindate'], "r" ). " - ".format_date($row['maxdate'], "r" ).")</p></div>";
	} else {
		return "Unknown report number!";
	}

	$reportdata[] = "<a id='rpt".$reportnumber."'></a>";
	$reportdata[] = "<table class='reportdata'>";
	$reportdata[] = "  <thead>";
	$reportdata[] = "    <tr>";
	$reportdata[] = "      <th>IP Address</th>";
	$reportdata[] = "      <th>Host Name</th>";
	$reportdata[] = "      <th>Message Count</th>";
	$reportdata[] = "      <th>Disposition</th>";
	$reportdata[] = "      <th>Reason</th>";
	$reportdata[] = "      <th>DKIM Domain</th>";
	$reportdata[] = "      <th>Raw DKIM Result</th>";
	$reportdata[] = "      <th>SPF Domain</th>";
	$reportdata[] = "      <th>Raw SPF Result</th>";
	$reportdata[] = "    </tr>";
	$reportdata[] = "  </thead>";

	$reportdata[] = "  <tbody>";
	$sql = "SELECT * FROM rptrecord where serial = $reportnumber";
	$query = mysql_query($sql) or die(mysql_error());
	while($row = mysql_fetch_array($query)) {
		$status="";
		if (($row['dkimresult'] == "fail") && ($row['spfresult'] == "fail")){
			$status="red";
		} elseif (($row['dkimresult'] == "fail") || ($row['spfresult'] == "fail")){
			$status="orange";
		} elseif (($row['dkimresult'] == "pass") && ($row['spfresult'] == "pass")) {
			$status="lime";
		} else {
			$status="yellow";
		};

		if ( $row['ip'] ) {
			$ip = long2ip($row['ip']);
		}
		if ( $row['ip6'] ) {
			$ip = inet_ntop($row['ip6']);
		}

		$reportdata[] = "    <tr class='".$status."'>";
		$reportdata[] = "      <td>". $ip. "</td>";
		$reportdata[] = "      <td>". gethostbyaddr($ip). "</td>";
		$reportdata[] = "      <td>". $row['rcount']. "</td>";
		$reportdata[] = "      <td>". $row['disposition']. "</td>";
		$reportdata[] = "      <td>". $row['reason']. "</td>";
		$reportdata[] = "      <td>". $row['dkimdomain']. "</td>";
		$reportdata[] = "      <td>". $row['dkimresult']. "</td>";
		$reportdata[] = "      <td>". $row['spfdomain']. "</td>";
		$reportdata[] = "      <td>". $row['spfresult']. "</td>";
		$reportdata[] = "    </tr>";
	}
	$reportdata[] = "  </tbody>";
	$reportdata[] = "</table>";

	$reportdata[] = "<!-- End of report rata -->";
	$reportdata[] = "";

	#indent generated html by 2 extra spaces
	return implode("\n  ",$reportdata);
}

function tmpl_page ($body) {
	$html = array();

	$html[] = "<!DOCTYPE html>";
	$html[] = "<html>";
	$html[] = "  <head>";
	$html[] = "    <title>DMARC Report Viewer</title>";
	$html[] = "    <link rel='stylesheet' href='default.css'>";
	$html[] = "  </head>";

	$html[] = "  <body>";

	$html[] = $body;

	$html[] = "  <div class='footer'>Brought to you by <a href='http://www.techsneeze.com'>TechSneeze.com</a> - <a href='mailto:dave@techsneeze.com'>dave@techsneeze.com</a></div>";
	$html[] = "  </body>";
	$html[] = "</html>";

	return implode("\n",$html);
}



//####################################################################
//### main ###########################################################
//####################################################################

// Override hardcoded script configuration options by local config file.
// The file is expected to be in the same folder as this script, but it
// does not need to exists.
if (file_exists("dmarcts-report-viewer-config.php")) include "dmarcts-report-viewer-config.php";

// Make a MySQL Connection
mysql_connect($dbhost, $dbuser, $dbpass) or die(mysql_error());
mysql_select_db($dbname) or die(mysql_error());

// Generate Page with report list and report data (if a report is selected)
echo tmpl_page( ""
	.tmpl_reportList()
	.tmpl_reportData( (isset($_GET["report"]) ? $_GET["report"] : false ) )
);

?>
