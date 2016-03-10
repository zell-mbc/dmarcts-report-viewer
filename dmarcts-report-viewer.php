<?php

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
	$reportlist = "";

	$reportlist .= "<center><h1>DMARC Reports</h1></center>\n";
	$reportlist .= "<hr align=center width=90% noshade>";
	$reportlist .= "<table align=center border=0 cellpadding=3>\n";
	$reportlist .= "<thead><tr><th>Start Date</th><th>End Date</th><th>Domain</th><th>Reporting Organization</th><th>Report ID</th><th>Messages</th></tr></thead><tbody>\n";

	$query_report = "SELECT * FROM report ORDER BY mindate";
	$result_report = mysql_query($query_report) or die(mysql_error());
	while($row = mysql_fetch_array($result_report)){
		$array_report[] = $row;
		$message_query = "SELECT *, SUM(rcount) FROM rptrecord WHERE serial = {$row['serial']}";
		$message_process = mysql_query($message_query) or die(mysql_error());
		$message_result = mysql_fetch_array($message_process);
		$date_output_format = "r";
		$reportlist .=  "<tr align=center>";
		$reportlist .=  "<td align=right>". format_date($row['mindate'], $date_output_format). "</td><td align=right>". format_date($row['maxdate'], $date_output_format). "</td><td>". $row['domain']. "</td><td>". $row['org']. "</td><td><a href=?report=". $row['serial']. "#rpt". $row['serial']. ">". $row['reportid']. "</a></td><td>". $message_result['SUM(rcount)']. "</td>";
		$reportlist .=  "</tr>";
		$reportlist .=  "\n";
	}
	$reportlist .=  "</tbody>";
	$reportlist .=  "</table>";
	$reportlist .= "<hr align=center width=90% noshade>";

	return $reportlist;
}

function tmpl_reportData($reportnumber) {

	if (!$reportnumber) {
		return "";
	}

	$reportdata = "";

	$query_date = "SELECT * FROM report where serial = $reportnumber";
	$query_rptrecord = "SELECT * FROM rptrecord where serial = $reportnumber";

	$result_date = mysql_query($query_date) or die(mysql_error());
	$showdate = mysql_fetch_array($result_date);

	$reportdata .= "<br/><center><strong>". format_date($showdate['mindate'], "r" ). "</strong></center><br />\n";
	$reportdata .= "<table align=center border=0 cellpadding=2>";
	$reportdata .= "<th>IP Address</th><th>Host Name</th><th>Message Count</th><th>Disposition</th><th>Reason</th><th>DKIM Domain</th><th>Raw DKIM Result</th><th>SPF Domain</th><th>Raw SPF Result</th>\n";

	$result_rptrecord = mysql_query($query_rptrecord) or die(mysql_error());
	while($row = mysql_fetch_array($result_rptrecord)){
		$rowcolor="FFFFFF";
		if (($row['dkimresult'] == "fail") && ($row['spfresult'] == "fail")){
		$rowcolor="FF0000"; //red
		} elseif (($row['dkimresult'] == "fail") || ($row['spfresult'] == "fail")){
		$rowcolor="FFA500"; //orange
		} elseif (($row['dkimresult'] == "pass") && ($row['spfresult'] == "pass")){
		$rowcolor="00FF00"; //lime
		} else {
		$rowcolor="FFFF00"; //yellow
		};

		if ( $row['ip'] ) {
			$ip = long2ip($row['ip']);
		}
		if ( $row['ip6'] ) {
			$ip = inet_ntop($row['ip6']);
		}

		$reportdata .= "<tr align=center bgcolor=". $rowcolor. ">";
		$reportdata .= "<td><a name=rpt". $row['serial'].">". $ip. "</td><td>". gethostbyaddr($ip). "</td><td>". $row['rcount']. "</td><td>". $row['disposition']. "</td><td>". $row['reason']. "</td>";
		$reportdata .= "<td>". $row['dkimdomain']. "</td><td>". $row['dkimresult']. "</td><td>". $row['spfdomain']. "</td><td>". $row['spfresult']. "</td></td>";
		$reportdata .= "</tr>";
		$reportdata .= "\n";
	}
	$reportdata .= "</table>";
	$reportdata .= "<hr align=center width=90% noshade>";

	return $reportdata;
}

function tmpl_page ($body) {
	$html = "";

	$html .= "<title>DMARC Report Viewer</title>";
	$html .= "<head>\n";
	$html .= "</head>\n";
	$html .= "<html>\n";
	$html .= "<body>\n";

	$html .= $body;

	$html .= "<center><h5>Brought to you by <a href=http://www.techsneeze.com>TechSneeze.com</a> - <a href=mailto:dave@techsneeze.com>dave@techsneeze.com</a></h5></center><br />\n";
	$html .= "</body>";
	$html .= "</html>";

	return $html;
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

//var_dump($array_report);
//var_dump($message_result);
//print_r(array_keys($array_report[5]));
?>
