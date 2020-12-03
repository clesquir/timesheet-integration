<?php

namespace App\Infrastructure\Messaging\Command;

use App\Domain\Model\TimeEntry;

final class ImportTimeEntriesToTimesheetCommand {
	/** @var TimeEntry[] */
	private array $timeEntries;

	private bool $dryRun;

	public function __construct(
		array $timeEntries,
		bool $dryRun
	) {
		$this->timeEntries = $timeEntries;
		$this->dryRun = $dryRun;
	}

	public function timeEntries(): array {
		return $this->timeEntries;
	}

	public function dryRun(): bool {
		return $this->dryRun;
	}
}