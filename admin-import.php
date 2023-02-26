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

$filename = $source->path;
$path = './sources/' . $filename;
if (!file_exists($path)) {
	die('File not found: ' . htmlspecialchars($filename ?: '(null)'));
}

$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($path);
$reader->setReadDataOnly(true);
$spreadsheet = $reader->load($path);

$worksheet = $spreadsheet->getActiveSheet();

$data = [];
$maxCols = 0;
foreach ($worksheet->getRowIterator() as $row) {
	$cellIterator = $row->getCellIterator();

	$content = [];
	foreach ($cellIterator as $i => $cell) {
		if ($i === 'A') {
			$sentence_id = intval($cell->getValue());
		} else if ($i === 'B') {
			$content_type = '' . $cell->getValue();
		} else {
			$content[] = '' . $cell->getValue();
		}
	}

	// Check there is content
	$contentStr = implode('', $content);
	if ($content_type === '' && $contentStr === '') {
		continue;
	}

	// Only the first cell has content. Trim the array
	if ($contentStr == $content[0]) {
		$content = [ $content[0] ];
	}

	// Update max number of cols
	$count = count($content);
	$maxCols = max($maxCols, $count);

	// Write to data array
	if ($count) {
		$data[] = (object) [
			'sentence_id' => $sentence_id,
			'content_type' => $content_type,
			'content' => $content,
		];
	}
}

$chunked = array_group_by($data, 'sentence_id');
foreach ($chunked as $sentenceData) {
	$maxCols = 0;
	foreach ($sentenceData as $row) {
		$len = strlen(rtrim(implode('', array_map(function($row) { return strlen($row) ? 1 : 0; }, $row->content)), '0'));
		$maxCols = max($maxCols, $len);
	}
	foreach ($sentenceData as $row) {
		if (count($row->content) > $maxCols) {
			$row->content = array_slice($row->content, 0, $maxCols);
		}
	}
}

if (isset($_POST['execute'])) {
	$rows = Content::addAll($source->id, $data);
	echo 'Inserted ' . $rows . ' rows';
	exit;
}

?>

<!doctype html>
<meta charset=utf-8>
<title>Import Data</title>
<link href="style.css" rel=stylesheet>

<body class=admin>
<h1>匯入資料</h1>
<table class=simple>
	<col width=200><col>
	<tr><th>ID</th><td><?=htmlspecialchars(isset($source->id) ? ($source->id . '') : '(null)');?></td>
	<tr><th>Name</th><td><?=htmlspecialchars($source->name ?: '(null)');?></td>
	<tr><th>Description</th><td><?=htmlspecialchars($source->desc ?: '(null)');?></td>
	<tr><th>Path</th><td><?=htmlspecialchars($source->path ?: '(null)')?></td>
</table>

<form method=post><input type=submit value="確認匯入" title="會自動覆寫已有資料"><input type=hidden name=execute value=yes></form>

<?php

$chunked = array_group_by($data, 'sentence_id');

foreach ($chunked as $sentenceData) {
	$mainSentence = array_filter($sentenceData, function($sentence) {
		return $sentence->content_type === 'sentence';
	});
	echo '<div class=sentence>';
	echo '<div class=sentence_id>' . $mainSentence[0]->sentence_id . '</div>' . "\r\n";
	echo '<div class=sentence_sentence>' . $mainSentence[0]->content[0] . '</div>';

	$maxCols = 0;
	foreach ($sentenceData as $row) {
		$len = strlen(rtrim(implode('', array_map(function($row) { return strlen($row) ? 1 : 0; }, $row->content)), '0'));
		$maxCols = max($maxCols, $len);
	}

	echo '<div class=sentence_analysis>';
	echo '<table>';
	echo '<tr><th>Sentence Id</th><th>Content Type</th><th colspan="' . $maxCols . '">Data</th>' . "\r\n";
	foreach ($sentenceData as $row) {
		echo '<tr>';
		echo '<td>' . htmlspecialchars('' . $row->sentence_id) . '</td>';
		echo '<td>' . htmlspecialchars($row->content_type) . '</td>';
		if (count($row->content) === 1) {
			echo '<td colspan="' . $maxCols . '">' . htmlspecialchars($row->content[0] ?: '') . '</td>';
		} else {
			foreach ($row->content as $cell) {
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
