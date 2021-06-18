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
//
//####################################################################
//### functions ######################################################
//####################################################################

function tmpl_reportData($reportnumber, $reports, $host_lookup = 1) {

	global $dmarc_where;
	global $cookie_options;

	$title_message = "Click to toggle sort direction by this column";

	if (!$reportnumber) {
		return "";
	}

	$reportdata[] = "";
	$reportdata[] = "<script type=\"text/javascript\">sorttable();</script>";

	$reportdata[] = "<!-- Start of report data -->";

	$reportsum    = 0;

	if (isset($reports[$reportnumber])) {
		$row = $reports[$reportnumber];

		$row['raw_xml'] = formatXML($row['raw_xml'], $reportnumber);

		$reportdata[] = "<div id='report_desc_container' class='center reportdesc_container'>";
		$reportdata[] = "<div id='report_desc' class='center reportdesc'  class='hilighted' onmouseover='highlight(this);' onmouseout='unhighlight(this);' onclick='pin(this)'>Report from ".$row['org']." for ".$row['domain']."<br>". format_date($row['mindate'], $cookie_options['date_format']). " to ".format_date($row['maxdate'], $cookie_options['date_format'])."<br> Policies: adkim=" . $row['policy_adkim'] . ", aspf=" . $row['policy_aspf'] .  ", p=" . $row['policy_p'] .  ", sp=" . $row['policy_sp'] .  ", pct=" . $row['policy_pct'] . "</div>";

		$reportdata[] = "<div style='display:inline-block;margin-left:20px;'><img src='xml.png' id='xml_html_img' width='30px' alt='Show Raw Report XML' title='Show Raw Report XML' onclick='report_data_xml_display_toggle()'></div>";

		$reportdata[] = "</div>";

	} else {
		return "Unknown report number!";
	}

	$reportdata[] = "<div id='report_data_xml' style='display:none; float:right; overflow-y:auto; border-left: 2px solid var(--shadow); text-align:left;padding-left: 7px;'>";
	$reportdata[] =  $row['raw_xml'];
	$reportdata[] = "</div>";

	$reportdata[] = "<div id='report_data_table_div' style='overflow-y:auto;'>";
	if ( $cookie_options['dmarc_results_matching_only'] ) {
		$reportdata[] = "\"Show Only Matching Report Data records\" option is \"On\".<br><span style='color: var(--red);'>Some report records may not be displayed.</span>";
	}
	$reportdata[] = "<table id='report_data_table' class='reportdata'>";
	$reportdata[] = "  <thead>";
	$reportdata[] = "    <tr>";
	$reportdata[] = "      <th class=\"asc_triangle\" title='" . $title_message . "'>IP</th>";
	$reportdata[] = "      <th title='" . $title_message . "'>Host<br />Name</th>";
	$reportdata[] = "      <th title='" . $title_message . "'>Message<br />Count</th>";
	$reportdata[] = "      <th title='" . $title_message . "'>Disposition</th>";
	$reportdata[] = "      <th title='" . $title_message . "'>Reason</th>";
	$reportdata[] = "      <th title='" . $title_message . "'>DKIM<br />Domain</th>";
	$reportdata[] = "      <th title='" . $title_message . "'>DKIM<br />Auth</th>";
	$reportdata[] = "      <th title='" . $title_message . "'>SPF<br />Domain</th>";
	$reportdata[] = "      <th title='" . $title_message . "'>SPF<br />Auth</th>";
	$reportdata[] = "      <th title='" . $title_message . "'>DKIM<br />Align</th>";
	$reportdata[] = "      <th title='" . $title_message . "'>SPF<br />Align</th>";
	$reportdata[] = "      <th title='" . $title_message . "'>DMARC</th>";
	$reportdata[] = "    </tr>";
	$reportdata[] = "  </thead>";
	$reportdata[] = "  <tbody>";

	global $mysqli;

$sql = "
SELECT
  *,
  (CASE
		WHEN dkim_align = 'fail' THEN 0
		WHEN dkim_align = 'pass' THEN 1
		ELSE 3
  END)
  +
  (CASE
		WHEN spf_align = 'fail' THEN 0
		WHEN spf_align = 'pass' THEN 1
		ELSE 3
		END)
	AS dmarc_result_min,
	(CASE
		WHEN dkim_align = 'fail' THEN 0
		WHEN dkim_align = 'pass' THEN 1
		ELSE 3
  END)
  +
  (CASE
		WHEN spf_align = 'fail' THEN 0
		WHEN spf_align = 'pass' THEN 1
		ELSE 3
	END)
	AS dmarc_result_max
FROM
  rptrecord
WHERE
	serial = " . $reportnumber . ( $dmarc_where ? " AND $dmarc_where" : "" ) . "
ORDER BY
	ip ASC
";

	$query = $mysqli->query($sql) or die("Query failed: ".$mysqli->error." (Error #" .$mysqli->errno.")");
	while($row = $query->fetch_assoc()) {
		if ( $row['ip'] ) {
			$ip = long2ip($row['ip']);
		} elseif ( $row['ip6'] ) {
			$ip = inet_ntop($row['ip6']);
		} else {
			$ip = "-";
		}

		/* escape html characters after exploring binary values, which will be messed up */
		$row = array_map('htmlspecialchars', $row);

		$reportdata[] = "    <tr id='line" . $row['id'] . "' class='" . get_dmarc_result($row)['color'] . "' title='DMARC Result: " . get_dmarc_result($row)['result'] . "'  onmouseover='highlight(this);' onmouseout='unhighlight(this);' onclick='pin(this);'>";
		$reportdata[] = "      <td>". $ip. "</td>";
		if ( $host_lookup ) {
			$reportdata[] = "      <td>". gethostbyaddr($ip). "</td>";
		} else {
			$reportdata[] = "      <td>#off#</td>";
		}
		$reportdata[] = "      <td>". $row['rcount']. "</td>";
		$reportdata[] = "      <td>". $row['disposition']. "</td>";
		$reportdata[] = "      <td>". $row['reason']. "</td>";
		$reportdata[] = "      <td>". $row['dkimdomain']. "</td>";
		$reportdata[] = "      <td class='" . get_status_color($row['dkimresult'])['color'] . "'>". $row['dkimresult']. "</td>";
		$reportdata[] = "      <td>". $row['spfdomain']. "</td>";
		$reportdata[] = "      <td class='" . get_status_color($row['spfresult'])['color'] . "'>". $row['spfresult']. "</td>";
		$reportdata[] = "      <td class='" . get_status_color($row['dkim_align'])['color'] . "'>". $row['dkim_align']. "</td>";
		$reportdata[] = "      <td class='" . get_status_color($row['spf_align'])['color'] . "'>". $row['spf_align']. "</td>";
		$reportdata[] = "      <td>" . get_dmarc_result($row)['result'] . "</td>";
		$reportdata[] = "    </tr>";

		$reportsum += $row['rcount'];
	}
	$reportdata[] = "  </tbody>";
	$reportdata[] = "<tr class='sum'><td></td><td class='right'>Sum:</td><td class='sum'>$reportsum</td><td colspan='9'></td></tr>";
	$reportdata[] = "</table>";

	$reportdata[] = "</div>";

	$reportdata[] = "";

	#indent generated html by 2 extra spaces
	return implode("\n  ",$reportdata);
}

function formatXML($raw_xml, $reportnumber) {

	global $mysqli;

	$out = "";
	$html = "";

	$sql = "
	SELECT
		MIN(id) AS id_min,
		MAX(id) AS id_max
	FROM
		rptrecord
	WHERE
		serial = $reportnumber;
	";

	$query = $mysqli->query($sql) or die("Query failed: ".$mysqli->error." (Error #" .$mysqli->errno.")");

	while($row = $query->fetch_assoc()) {
		$id_min = $row['id_min'];
		$id_max = $row['id_max'];
	}

	$dom = new DOMDocument();
	$dom->preserveWhiteSpace = false;
	$dom->formatOutput = true;
	$dom->loadXML($raw_xml);

	// These next few lines adding <?xml version=\"1.0\" encoding=\"UTF-8\" > and <feedback> (as well as the lines adding the closing </feedback> tag) are are very risky because they assume that the first two lines and the last line of the raw_xml are weel-formed
	// Hopefully not too risky as the raw_xml has already gone through the dmarcts-parser routine that looks for bad XML.
	// If someone can code a proper way to get those lines, it would be appreciated.
	$xml_arr = explode(PHP_EOL,$raw_xml);
	$out = $xml_arr[0] . "\n" . $xml_arr[1];
	// Should return first 2 lines of xml: <?xml version=\"1.0\" encoding=\"UTF-8\"> and <feedback>
	$html = "<pre><code class='xml'>" . htmlspecialchars($out) . "</code></pre>";

	$out = $dom->saveXML($dom->getElementsByTagName("report_metadata")[0]);
	$out = htmlspecialchars($out);

	$html .= "<div id='report_metadata' onmouseover='highlight(this);' onmouseout='unhighlight(this);' onclick='pin(this)'><pre><code class='xml'>" . $out . "</code></pre></div>";

	$records = $dom->getElementsByTagName("record");
	$i = 0;
	// $i++;
	foreach ( $records as $record) {
		$out = $dom->saveXML($dom->getElementsByTagName("record")[$i]);
		$out = htmlspecialchars($out);
		$html .= "<div id='record$id_min' onmouseover='highlight(this);' onmouseout='unhighlight(this);' onclick='pin(this)'><pre><code class='xml'>";
		$html .= $out;
		$html .= "</code></pre></div>";
		$id_min++;
		$i++;
	}

	$out = $xml_arr[sizeof($xml_arr)-2];
	$out = htmlspecialchars($out);
		$html .= "<pre><code class='xml'>" . $out . "</code></pre>";

	return $html;
}

//####################################################################
//### main ###########################################################
//####################################################################

// These files are expected to be in the same folder as this script, and must exist.
include "dmarcts-report-viewer-config.php";
include "dmarcts-report-viewer-common.php";

// Get all configuration options
// --------------------------------------------------------------------------
configure();


// Parameters of GET
// --------------------------------------------------------------------------

if(isset($_GET['report']) && is_numeric($_GET['report'])){
	$reportid=$_GET['report']+0;
}elseif(!isset($_GET['report'])){
	$reportid=false;
}else{
	die('Invalid Report ID');
}

if(isset($_GET['hostlookup']) && is_numeric($_GET['hostlookup'])){
	$hostlookup=$_GET['hostlookup']+0;
}elseif(!isset($_GET['hostlookup'])){
	$hostlookup= isset( $default_lookup ) ? $default_lookup : 1;
}else{
	die('Invalid hostlookup flag');
}

if(isset($_GET['sortorder']) && is_numeric($_GET['sortorder'])){
	$sortorder=$_GET['sortorder']+0;
}elseif(!isset($_GET['sortorder'])){
	$sortorder= isset( $default_sort ) ? $default_sort : 1;
}else{
	die('Invalid sortorder flag');
}

if( $cookie_options['dmarc_results_matching_only'] && isset($_GET['dmarc']) ) {
	$dmarc_select=$_GET['dmarc'];
}else{
	$dmarc_select= '';
}

if( $dmarc_select == "all" ) {
	$dmarc_select= '';
}

// Debug
//echo "<br />D=$dom_select <br /> O=$org_select <br />";

// Make a MySQL Connection using mysqli
// --------------------------------------------------------------------------
$mysqli = new mysqli($dbhost, $dbuser, $dbpass, $dbname, $dbport);
if ($mysqli->connect_errno) {
	echo "Error: Failed to make a MySQL connection, here is why: \n";
	echo "Errno: " . $mysqli->connect_errno . "\n";
	echo "Error: " . $mysqli->connect_error . "\n";
// Debug ONLY. This will expose database credentials when database connection fails
// 	echo "Database connection information: <br />dbhost: " . $dbhost . "<br />dbuser: " . $dbuser . "<br />dbpass: " . $dbpass . "<br />dbname: " . $dbname . "<br />dbport: " . $dbport . "<br />";
	exit;
}

// // Get allowed reports and cache them - using serial as key
// --------------------------------------------------------------------------
$reports = array();

// set sort direction
// --------------------------------------------------------------------------
$sort = '';
if( $sortorder ) {
	$sort = "ASC";
} else {
	$sort = "DESC";
}

// DMARC
// dkimresult spfresult
// --------------------------------------------------------------------------
switch ($dmarc_select) {
	case "DMARC_PASS": // DKIM and SPF Pass: Green
		$dmarc_where = "(rptrecord.dkimresult='pass' AND rptrecord.spfresult='pass')";
		break;
	case "DMARC_PASS_AND_FAIL": // DKIM or SPF Fail: Orange
		$dmarc_where = "(rptrecord.dkimresult='fail' OR rptrecord.spfresult='fail')";
		break;
	case "DMARC_FAIL": // DKIM and SPF Fail: Red
		$dmarc_where = "(rptrecord.dkimresult='fail' AND rptrecord.spfresult='fail')";
		break;
	case "DMARC_OTHER_CONDITION": // Other condition: Yellow
		$dmarc_where = "NOT ((rptrecord.dkimresult='pass' AND rptrecord.spfresult='pass') OR (rptrecord.dkimresult='fail' OR rptrecord.spfresult='fail') OR (rptrecord.dkimresult='fail' AND rptrecord.spfresult='fail'))"; // In other words, "NOT" all three other conditions
		break;
	default:
		break;
}

// Include the rcount via left join, so we do not have to make an sql query
// for every single report.
// --------------------------------------------------------------------------

$sql = "
SELECT
	*
FROM
	report
WHERE
	serial = " . $mysqli->real_escape_string($reportid)
;

// Debug
// echo "<br /><b>Data Report sql:</b> $sql<br />";

$query = $mysqli->query($sql) or die("Query failed: ".$mysqli->error." (Error #" .$mysqli->errno.")");
while($row = $query->fetch_assoc()) {
	if (true) {
		//add data by serial
		$reports[$row['serial']] = $row;
	}
}

// Generate Page with report list and report data (if a report is selected).
// --------------------------------------------------------------------------
echo tmpl_reportData($reportid, $reports, $hostlookup );

?>
