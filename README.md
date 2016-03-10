# dmarcts-report-viewer
A PHP viewer for DMARC records that have been parsed by [John Levine's rddmarc script](http://www.taugh.com/rddmarc/) or the [dmarcts-report-parser.pl](https://github.com/techsneeze/dmarcts-report-parser)
* Allow to view pass/fail data for the parsed reports
* Identify potential DMARC related issues through red/yellow/green signals

## Installation and Configuration

NOTE: The viewer expects that you have already populated a database with data from [John Levine's rddmarc script](http://www.taugh.com/rddmarc/) or the [dmarcts-report-parser.pl](https://github.com/techsneeze/dmarcts-report-parser)

Once the php files ave been downloaded, you'll want to edit these basic configuration options at the top of the `dmarcts-report-viewer-config.php` script.  Most of them are self-explanatory:

```
$dbhost="localhost";
$dbname="dmarc";
$dbuser="dmarc";
$dbpass="xxx";
```

Ensure that `dmarcts-report-viewer-config.php` and `dmarcts-report-viewer.php` are in the same folder.
## Usage

Navigate in your browser to the location of the `dmarcts-report-viewer.php` file.  

You should be presented with the basic report view, allowing you to navigate through the reports that have been parsed.

More info can currently be found at : [TechSneeze.com](http://www.techsneeze.com/dmarc-report/)
