<?php

namespace App\Infrastructure\Messaging\Command;

use DateTimeImmutable;

final class ImportTimeularToTimesheetCommand {
	public function __construct(
		private readonly DateTimeImmutable $date,
		private readonly bool $noComment,
		private readonly bool $dryRun
	) {
	}

	public function date(): DateTimeImmutable {
		return $this->date;
	}

	public function noComment(): bool {
		return $this->noComment;
	}

	public function dryRun(): bool {
		return $this->dryRun;
	}
}
