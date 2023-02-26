<?php
declare(strict_types = 1);
require 'lib.php';

use \PhpOffice\PhpSpreadsheet\IOFactory;

if (!isset($_GET['source'])) {
	die('Missing $source');
}

if (!ctype_digit($_GET['source'])) {
	die('Bad Param $source');
}

$source = Source::getById($_GET['source']);

?>

<!doctype html>
<meta charset=utf-8>
<title>Manage Sources</title>
<link href="style.css" rel=stylesheet>

<body class=admin>
<h1>檢視資料</h1>
<table class=simple>
	<col width=200><col>
	<tr><th>ID</th><td><?=htmlspecialchars(isset($source->id) ? ($source->id . '') : '(null)');?></td>
	<tr><th>Name</th><td><?=htmlspecialchars($source->name ?: '(null)');?></td>
	<tr><th>Description</th><td><?=htmlspecialchars($source->desc ?: '(null)');?></td>
	<tr><th>Path</th><td><?=htmlspecialchars($source->path ?: '(null)')?></td>
</table>

<?php

$data = Content::getBySourceId($source->id);
$chunked = array_group_by($data, 'sentence_id');

foreach ($chunked as $sentenceData) {
	$mainSentence = array_filter($sentenceData, function($sentence) {
		return $sentence->content_type === 'sentence';
	});
	echo '<div class=sentence>';
	echo '<div class=sentence_id>' . $mainSentence[0]->sentence_id . '</div>' . "\r\n";
	echo '<div class=sentence_sentence>' . $mainSentence[0]->content[0] . '</div>';

	echo '<div class=sentence_analysis>';
	$maxCols = 0;
	foreach ($sentenceData as $row) {
		$len = strlen(rtrim(implode('', array_map(function($row) { return strlen($row) ? 1 : 0; }, $row->content)), '0'));
		$maxCols = max($maxCols, $len);
	}

	echo '<table>';
	echo '<tr><th>Type</th><th colspan="' . $maxCols . '">Data</th>' . "\r\n";
	foreach ($sentenceData as $row) {
		echo '<tr>';
		echo '<td>' . htmlspecialchars($row->content_type) . '</td>';
		if (count($row->content) === 1) {
			echo '<td colspan="' . $maxCols . '">' . htmlspecialchars($row->content[0] ?: '') . '</td>';
		} else {
			foreach ($row->content as $i => $cell) {
				echo '<td>' . htmlspecialchars($cell ?: '') . '</td>';
			}
		}
		echo '</tr>' . "\r\n";
	}
	echo '</table>' . "\r\n";
	echo '</div>';
	echo '</div>';
}
?>
<a href="admin-list-sources.php">返回資料集</a>
