<?php declare(strict_types=1);

namespace App\Application\Console;

use App\Domain\Messaging\Bus;
use App\Domain\Model\DeviceAccessExpiredException;
use App\Infrastructure\Messaging\Command\ImportTimeularToTimesheetCommand;
use App\Infrastructure\Messaging\Query\FetchTimesheetCredentialsQuery;
use DateTimeImmutable;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class ImportTimeularToTimesheetConsole extends Command {
	public function __construct(
		private readonly Bus $bus,
		private readonly RegisterTimesheetDeviceConsole $registerDeviceConsole
	) {
		parent::__construct();
	}

	protected function configure(): void {
		$this->setName('app:import:timeular-to-timesheet')
			->setDescription('Import Timeular entries to Timesheet.')
			->addArgument('date', InputArgument::REQUIRED, 'Date to import')
			->addOption('no-comment', null, InputOption::VALUE_NONE, 'Do not add comments')
			->addOption('dry-run', null, InputOption::VALUE_NONE, 'Do not import time entries');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$credentials = $this->bus->handle(new FetchTimesheetCredentialsQuery());

		if ($credentials === null) {
			$this->registerDeviceConsole->run(new ArrayInput([]), $output);
			$output->writeln('<error>Device not registered. Please follow the instructions above and then run the command again.</error>');

			return 1;
		}

		$date = new DateTimeImmutable($input->getArgument('date'));
		$noComment = $input->getOption('no-comment');
		$dryRun = $input->getOption('dry-run');

		try {
			$this->bus->handle(new ImportTimeularToTimesheetCommand($date, $noComment, $dryRun));
		} catch (DeviceAccessExpiredException) {
			$this->registerDeviceConsole->run(new ArrayInput([]), $output);
			$output->writeln('<error>Device access has expired. Please follow the instructions above and then run the command again.</error>');

			return 1;
		}

		return 0;
	}
}
