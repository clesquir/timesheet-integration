<?php

namespace App\Infrastructure\Messaging\Command;

use DateTimeImmutable;

final class ImportCSVToTimesheetCommand {
	private DateTimeImmutable $date;

	private bool $dryRun;

	public function __construct(
		DateTimeImmutable $date,
		bool $dryRun
	) {
		$this->date = $date;
		$this->dryRun = $dryRun;
	}

	public function date(): DateTimeImmutable {
		return $this->date;
	}

	public function dryRun(): bool {
		return $this->dryRun;
	}
}
