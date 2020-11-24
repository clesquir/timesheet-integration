<?php

namespace App\Domain\Model;

final class Activity {
	private int $id;

	private string $name;

	private function __construct(int $id, string $name) {
		$this->id = $id;
		$this->name = $name;
	}

	public function id(): int {
		return $this->id;
	}

	public function name(): string {
		return $this->name;
	}

	public static function create(int $id, string $name) {
		return new self($id, $name);
	}
}
