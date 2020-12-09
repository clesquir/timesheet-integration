<?php

namespace App\Infrastructure\Messaging\Command;

final class ImportTyme2ToTimesheetCommand {
	private string $filename;

	private bool $noComment;

	private bool $dryRun;

	public function __construct(
		string $filename,
		bool $noComment,
		bool $dryRun
	) {
		$this->filename = $filename;
		$this->noComment = $noComment;
		$this->dryRun = $dryRun;
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
