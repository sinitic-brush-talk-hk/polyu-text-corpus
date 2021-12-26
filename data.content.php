<?php
declare(strict_types = 1);

class Content {
	public int $id;
	public int $source_id;
	public int $sentence_id;
	public ?string $content_type;
	public ?object $path;

	public function __construct($data) {
		$this->id = (int) $data->id;
		$this->source_id = (int) $data->source_id;
		$this->sentence_id = (int) $data->sentence_id;
		$this->content_type = $data->content_type;
		$this->content = json_decode($data->content);
	}

	public static function addAll(int $source_id, $content) : int{
		$rows = 0;
		Env::$db->beginTransaction();
		Content::removeBySourceId($source_id);
		foreach ($content as $row) {
			Content::add($source_id, $row->sentence_id, $row->content_type, $row->content);
			$rows++;
		}
		Env::$db->commit();
		return $rows;
	}

	public static function add(int $source_id, int $sentence_id, string $content_type, array $content) {
		$q = Env::$db->prepare('INSERT INTO content (source_id, sentence_id, content_type, content) VALUES (?, ?, ?, ?)');
		$q->execute([$source_id, $sentence_id, $content_type, json_encode($content)]);
	}

	public static function removeBySourceId(int $source_id) {
		$q = Env::$db->prepare('DELETE FROM content WHERE source_id = ?');
		$q->execute([$source_id]);
	}

	public static function getBySourceId(int $source_id) : array {
		$list = [];
		$q = Env::$db->query('SELECT * FROM content WHERE source_id = ?');
		$q->execute([ $source_id ]);
		while ($data = $q->fetch()) {
			$list[] = new Content($data);
		}
		return $list;
	}

	public static function getAll() : array {
		$list = [];
		$q = Env::$db->query('SELECT * FROM content');
		while ($data = $q->fetch()) {
			$list[] = new Content($data);
		}
		return $list;
	}
}

// Sources::add('《孫中山與宮崎滔天筆談資料》', null, '1897-MiyasakiHorizontal.xls');
