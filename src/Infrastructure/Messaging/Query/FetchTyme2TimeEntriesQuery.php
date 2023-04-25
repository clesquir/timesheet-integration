<?php

namespace App\Infrastructure\Messaging\Query;

final class FetchTyme2TimeEntriesQuery {
	public function __construct(
		private readonly string $filename
	) {
	}

	public function filename(): string {
		return $this->filename;
	}
}
