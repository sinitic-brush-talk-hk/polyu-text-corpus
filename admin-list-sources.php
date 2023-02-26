<?php
declare(strict_types = 1);
require 'lib.php';

$sources = Source::getAll();

?>
<!doctype html>
<meta charset=utf-8>
<title>Manage Sources</title>
<link href="style.css" rel=stylesheet>

<body class=admin>
<h1>資料集</h1>
<?php

echo '<table>';
echo '<col width=80><col width=300><col><col width=280>';
echo '<tr><th>#</th><th>Name</th><th>Description</th><th>Actions</th>';
foreach ($sources as $source) {
	echo '<tr>';
	echo '<td>' . pretty_print_html($source->id . '') . '</td>';
	echo '<td>' . pretty_print_html($source->name) . '</td>';
	echo '<td>' . pretty_print_html($source->desc) . '</td>';
	echo '<td>';
	if ($source->path !== null) {
		echo '<a href="./sources/' . urlencode($source->path) . '" download>下載 Excel</a><br>';
		echo '<a href="./admin-import.php?source=' . urlencode((string) $source->id) . '">重新匯入</a><br>';
		echo '<a href="./admin-content.php?source=' . urlencode((string) $source->id) . '">顯示已匯入資料</a>';
	}
	echo '</td>';
	echo '</tr>';
}
echo '</table>';

?>
<a href="index.php">返回主目錄</a>
