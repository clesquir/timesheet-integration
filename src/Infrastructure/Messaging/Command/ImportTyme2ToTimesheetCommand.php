<?php

namespace App\Infrastructure\Messaging\Command;

final class ImportTyme2ToTimesheetCommand {
	private string $filename;

	private bool $addTimeToDescription;

	private bool $dryRun;

	public function __construct(
		string $filename,
		bool $addTimeToDescription,
		bool $dryRun
	) {
		$this->filename = $filename;
		$this->addTimeToDescription = $addTimeToDescription;
		$this->dryRun = $dryRun;
	}

	public function filename(): string {
		return $this->filename;
	}

	public function addTimeToDescription(): bool {
		return $this->addTimeToDescription;
	}

	public function dryRun(): bool {
		return $this->dryRun;
	}
}
