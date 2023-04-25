<?php

namespace App\Infrastructure\Messaging\Command;

final class ImportTyme2ToTimesheetCommand {
	public function __construct(
		private readonly string $filename,
		private readonly bool $noComment,
		private readonly bool $dryRun
	) {
	}

	public function filename(): string {
		return $this->filename;
	}

	public function noComment(): bool {
		return $this->noComment;
	}

	public function dryRun(): bool {
		return $this->dryRun;
	}
}
