<?php

namespace App\Infrastructure\Messaging\Command;

use DateTimeImmutable;

final class ImportTimeularToTimesheetCommand {
	private DateTimeImmutable $date;

	private bool $noComment;

	private bool $dryRun;

	public function __construct(
		DateTimeImmutable $date,
		bool $noComment,
		bool $dryRun
	) {
		$this->date = $date;
		$this->noComment = $noComment;
		$this->dryRun = $dryRun;
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
