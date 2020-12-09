<?php

namespace App\Infrastructure\Messaging\Command;

use App\Domain\Messaging\Bus;
use App\Infrastructure\Messaging\Query\FetchTimeularTimeEntriesQuery;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class ImportTimeularToTimesheetHandler implements MessageHandlerInterface {
	private Bus $bus;

	public function __construct(Bus $bus) {
		$this->bus = $bus;
	}

	public function __invoke(ImportTimeularToTimesheetCommand $command) {
		$entries = $this->bus->handle(new FetchTimeularTimeEntriesQuery($command->date()));
		$this->bus->handle(new ImportTimeEntriesToTimesheetCommand($entries, $command->noComment(), $command->dryRun()));
	}
}
