<?php

namespace App\Infrastructure\Messaging\Command;

use DateTimeImmutable;

final class ImportTimeularToTimesheetCommand {
	private DateTimeImmutable $date;

	private bool $addTimeToDescription;

	private bool $dryRun;

	public function __construct(
		DateTimeImmutable $date,
		bool $addTimeToDescription,
		bool $dryRun
	) {
		$this->date = $date;
		$this->addTimeToDescription = $addTimeToDescription;
		$this->dryRun = $dryRun;
	}

	public function date(): DateTimeImmutable {
		return $this->date;
	}

	public function addTimeToDescription(): bool {
		return $this->addTimeToDescription;
	}

	public function dryRun(): bool {
		return $this->dryRun;
	}
}
