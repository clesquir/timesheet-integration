<?php

namespace App\Infrastructure\Messaging\Command;

final class ImportTyme2ToTimesheetCommand {
	private bool $dryRun;

	private string $filename;

	public function __construct(
		bool $dryRun,
		string $filename
	) {
		$this->dryRun = $dryRun;
		$this->filename = $filename;
	}

	public function dryRun(): bool {
		return $this->dryRun;
	}

	public function filename(): string {
		return $this->filename;
	}
}
