<?php

namespace App\Infrastructure\Messaging\Command;

use App\Domain\Messaging\Bus;
use App\Infrastructure\Messaging\Query\FetchTyme2TimeEntriesQuery;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class ImportTyme2ToTimesheetHandler {
	public function __construct(
		private readonly Bus $bus
	) {
	}

	public function __invoke(ImportTyme2ToTimesheetCommand $command): void {
		$entries = $this->bus->handle(new FetchTyme2TimeEntriesQuery($command->filename()));
		$this->bus->handle(new ImportTimeEntriesToTimesheetCommand($entries, $command->noComment(), $command->dryRun()));
	}
}
