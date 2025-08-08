<ul class="question-options">
<?php 
$oCount = 0;
foreach($q['options'] as $oKey=>$o) {
	$oCount ++;
	include (TEMPLATEPATH . '/survey-question-option.php');
}
?>
</ul>
