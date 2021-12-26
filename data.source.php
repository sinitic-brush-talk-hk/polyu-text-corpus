<?php
declare(strict_types = 1);

class Source {
	public int $id;
	public ?string $name;
	public ?string $desc;
	public ?string $path;

	public function __construct($data) {
		$this->id = (int) $data->id;
		$this->name = $data->name;
		$this->desc = $data->desc;
		$this->path = $data->path;
	}

	public static function add($name, $desc, $path) {
		$q = Env::$db->prepare('INSERT INTO source (name, desc, path) VALUES (?, ?, ?)');
		$q->execute([$name, $desc, $path]);
	}

	public static function getById($id) : ?Source {
		$q = Env::$db->prepare('SELECT * FROM source WHERE id = ?');
		$q->execute([$id]);
		$data = $q->fetch();
		if ($data != null) {
			return new Source($data);
		}
		return '';
	}

	public static function getAll() : array {
		$list = [];
		$q = Env::$db->query('SELECT * FROM source');
		while ($data = $q->fetch()) {
			$list[] = new Source($data);
		}
		return $list;
	}
}

// Sources::add('《孫中山與宮崎滔天筆談資料》', null, '1897-MiyasakiHorizontal.xls');
