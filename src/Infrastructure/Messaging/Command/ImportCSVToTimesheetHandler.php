<?php

namespace App\Infrastructure\Messaging\Command;

use App\Domain\Messaging\Bus;
use App\Infrastructure\Messaging\Query\FetchCSVTimeEntriesQuery;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class ImportCSVToTimesheetHandler implements MessageHandlerInterface {
	private Bus $bus;

	public function __construct(Bus $bus) {
		$this->bus = $bus;
	}

	public function __invoke(ImportCSVToTimesheetCommand $command) {
		$entries = $this->bus->handle(new FetchCSVTimeEntriesQuery($command->filename()));
		$this->bus->handle(new ImportTimeEntriesToTimesheetCommand($entries, $command->dryRun()));
	}
}
