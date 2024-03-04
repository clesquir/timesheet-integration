<?php declare(strict_types=1);

namespace App\Application\Console;

use App\Domain\Messaging\Bus;
use App\Domain\Model\DeviceAccessExpiredException;
use App\Domain\Model\TimeEntry;
use App\Infrastructure\Messaging\Command\ImportTimeEntriesToTimesheetCommand;
use App\Infrastructure\Messaging\Query\FetchTimesheetCredentialsQuery;
use DateTimeImmutable;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class ImportDummyToTimesheetConsole extends Command {
	public function __construct(
		private readonly Bus $bus,
		private readonly RegisterTimesheetDeviceConsole $registerDeviceConsole
	) {
		parent::__construct();
	}

	protected function configure(): void {
		$this->setName('app:import:dummy-to-timesheet')
			->setDescription('Import Timeular entries to Timesheet.');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$credentials = $this->bus->handle(new FetchTimesheetCredentialsQuery());

		if ($credentials === null) {
			$this->registerDeviceConsole->run($input, $output);
			$output->writeln('<error>Device not registered. Please follow the instructions above and then run the command again.</error>');

			return 1;
		}

		try {
			$this->bus->handle(
				new ImportTimeEntriesToTimesheetCommand(
					[TimeEntry::fixtureWithStartedAtStoppedAt(new DateTimeImmutable('2020-01-01 08:00'), new DateTimeImmutable('2020-01-01 12:00'))],
					true,
					false
				)
			);
		} catch (DeviceAccessExpiredException) {
			$this->registerDeviceConsole->run($input, $output);
			$output->writeln('<error>Device access has expired. Please follow the instructions above and then run the command again.</error>');

			return 1;
		}

		return 0;
	}
}
