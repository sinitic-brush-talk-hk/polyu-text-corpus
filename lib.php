<?php
declare(strict_types = 1);

class Env {
	static $db = null;
}

$db = new PDO('sqlite:data/data.sqlite3');
$db->exec('PRAGMA foreign_keys = ON');
$db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
Env::$db = $db;

require 'data.source.php';
require 'data.content.php';
require 'vendor/autoload.php';

function pretty_print_html(?string $string): string {
	if ($string === null) {
		return '<span class=value_null>(沒有填寫)</span>';
	}
	return htmlspecialchars($string);
}

function array_group_by($array, $key): array {
	$result = [];
	foreach ($array as $row) {
		$result[$row->$key][] = $row;
	}
	return $result;
}
