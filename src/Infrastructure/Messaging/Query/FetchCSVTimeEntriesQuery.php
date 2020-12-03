<?php

namespace App\Infrastructure\Messaging\Query;

use DateTimeImmutable;

final class FetchCSVTimeEntriesQuery {
	private string $filename;

	public function __construct(string $filename) {
		$this->filename = $filename;
	}

	public function filename() : string {
		return $this->filename;
	}
}
