<?php

namespace App\Infrastructure\Messaging\Query;

final class FetchTyme2TimeEntriesQuery {
	private string $filename;

	public function __construct(string $filename) {
		$this->filename = $filename;
	}

	public function filename(): string {
		return $this->filename;
	}
}
