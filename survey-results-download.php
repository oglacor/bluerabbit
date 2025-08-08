<?php 
header("Content-type: text/csv");
header("Content-Disposition: attachment; filename=survey.csv");
header("Pragma: no-cache");
header("Expires: 0");
$survey_data = ($_POST["survey_data"]);
$fp = fopen('php://output', 'w');
$val = array("Track", "ID" , "Description", "Question", "N/A", "1 Star", "2 Stars", "3 Stars", "4 Stars", "5 Stars", "Total");
fputcsv($fp, $val);
foreach($survey_data as $key=>$q){
	$val = array(
		$q['track'],
		$q['id'],
		"{$q['survey_question_description']}",
		"{$q['question']}",
		$q['ratings'][0],$q['ratings'][1],$q['ratings'][2],$q['ratings'][3],$q['ratings'][4],$q['ratings'][5],
		$q['total']
	);
	fputcsv($fp, $val);
}
fclose($fp); ?>