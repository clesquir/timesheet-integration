<?php

namespace App\Domain\Model;

final class Activity {
	private function __construct(
		private readonly int $id,
		private readonly string $name
	) {
	}

	public function id(): int {
		return $this->id;
	}

	public function name(): string {
		return $this->name;
	}

	public static function create(int $id, string $name): self {
		return new self($id, $name);
	}
}
