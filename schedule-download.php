<?php 
header("Content-type: text/csv");
header("Content-Disposition: attachment; filename=schedule.csv");
header("Pragma: no-cache");
header("Expires: 0");
$schedule_data = ($_POST["schedule_data"]);
$fp = fopen('php://output', 'w');
$val = array("ID" ,"Title", "Description", "Speaker", "Day", "Start","End");
fputcsv($fp, $val);
foreach($schedule_data as $key=>$s){
	$val = array(
		$s['id'],
		"{$s['title']}",
		"{$s['description']}",
		"{$s['speaker']}",
		"{$s['day']}",
		"{$s['time_start']}",
		"{$s['time_end']}",
	);
	fputcsv($fp, $val);
}
fclose($fp); ?>


