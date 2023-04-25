<?php

namespace App\Infrastructure\Messaging\Query;

use DateTimeImmutable;

final class FetchTimeularTimeEntriesQuery {
	public function __construct(
		private readonly DateTimeImmutable $date
	) {
	}

	public function date(): DateTimeImmutable {
		return $this->date;
	}
}
