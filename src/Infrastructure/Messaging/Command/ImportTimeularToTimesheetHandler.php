<?php

namespace App\Infrastructure\Messaging\Command;

use App\Domain\Messaging\Bus;
use App\Infrastructure\Messaging\Query\FetchTimeularTimeEntriesQuery;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class ImportTimeularToTimesheetHandler {
	public function __construct(
		private readonly Bus $bus
	) {
	}

	public function __invoke(ImportTimeularToTimesheetCommand $command): void {
		$entries = $this->bus->handle(new FetchTimeularTimeEntriesQuery($command->date()));
		$this->bus->handle(new ImportTimeEntriesToTimesheetCommand($entries, $command->noComment(), $command->dryRun()));
	}
}
