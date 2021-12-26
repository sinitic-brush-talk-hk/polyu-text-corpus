<?php
declare(strict_types = 1);
require 'lib.php';

$sources = Source::getAll();

?>
<!doctype html>
<meta charset=utf-8>
<title>Manage Sources</title>
<link href="style.css" rel=stylesheet>

<h1>資料集</h1>
<?php

echo '<table>';
echo '<col width=80><col width=300><col><col width=200>';
echo '<tr><th>#</th><th>Name</th><th>Description</th><th>Actions</th>';
foreach ($sources as $source) {
	echo '<tr>';
	echo '<td>' . pretty_print_html($source->id . '') . '</td>';
	echo '<td>' . pretty_print_html($source->name) . '</td>';
	echo '<td>' . pretty_print_html($source->desc) . '</td>';
	echo '<td>';
	if ($source->path !== null) {
		echo '<a href="./sources/' . urlencode($source->path) . '" download>Download</a> ';
		echo '<a href="./admin-import.php?source=' . urlencode($source->id) . '">Import</a> ';
	}
	echo '</td>';
	echo '</tr>';
}
echo '</table>';
