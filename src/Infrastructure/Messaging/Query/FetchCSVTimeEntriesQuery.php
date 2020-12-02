<?php

namespace App\Infrastructure\Messaging\Query;

use DateTimeImmutable;

final class FetchCSVTimeEntriesQuery {
	private DateTimeImmutable $date;

	public function __construct(DateTimeImmutable $date) {
		$this->date = $date;
	}

	public function date(): DateTimeImmutable {
		return $this->date;
	}
}
