<?php

namespace App\Domain\Model;

final readonly class Activity {
	private function __construct(
		private int $id,
		private string $name,
		private bool $isActive
	) {
	}

	public function id(): int {
		return $this->id;
	}

	public function name(): string {
		return $this->name;
	}

	public function isActive(): bool {
		return $this->isActive;
	}

	public static function create(int $id, string $name, bool $isActive): self {
		return new self($id, $name, $isActive);
	}
}
