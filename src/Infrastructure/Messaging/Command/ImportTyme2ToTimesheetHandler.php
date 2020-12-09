<?php

namespace App\Infrastructure\Messaging\Command;

use App\Domain\Messaging\Bus;
use App\Infrastructure\Messaging\Query\FetchTyme2TimeEntriesQuery;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class ImportTyme2ToTimesheetHandler implements MessageHandlerInterface {
	private Bus $bus;

	public function __construct(Bus $bus) {
		$this->bus = $bus;
	}

	public function __invoke(ImportTyme2ToTimesheetCommand $command) {
		$entries = $this->bus->handle(new FetchTyme2TimeEntriesQuery($command->filename()));
		$this->bus->handle(new ImportTimeEntriesToTimesheetCommand($entries, $command->dryRun()));
	}
}
