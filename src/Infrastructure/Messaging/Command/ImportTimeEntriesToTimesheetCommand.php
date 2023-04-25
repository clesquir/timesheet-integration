<?php

namespace App\Infrastructure\Messaging\Command;

use App\Domain\Model\TimeEntry;

final class ImportTimeEntriesToTimesheetCommand {
	public function __construct(
		private readonly array $timeEntries,
		private readonly bool $noComment,
		private readonly bool $dryRun
	) {
	}

	/**
	 * @return TimeEntry[]
	 */
	public function timeEntries(): array {
		return $this->timeEntries;
	}

	public function noComment(): bool {
		return $this->noComment;
	}

	public function dryRun(): bool {
		return $this->dryRun;
	}
}
