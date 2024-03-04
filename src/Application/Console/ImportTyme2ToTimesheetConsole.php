<?php declare(strict_types=1);

namespace App\Application\Console;

use App\Domain\Messaging\Bus;
use App\Domain\Model\DeviceAccessExpiredException;
use App\Infrastructure\Messaging\Command\ImportTyme2ToTimesheetCommand;
use App\Infrastructure\Messaging\Query\FetchTimesheetCredentialsQuery;
use LogicException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class ImportTyme2ToTimesheetConsole extends Command {
	public function __construct(
		private readonly Bus $bus,
		private readonly RegisterTimesheetDeviceConsole $registerDeviceConsole
	) {
		parent::__construct();
	}

	protected function configure(): void {
		$this->setName('app:import:tyme2-to-timesheet')
			->setDescription('Import Tyme2 entries to Timesheet.')
			->addArgument('filename', InputArgument::REQUIRED, 'Filename to import')
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

		$filename = $input->getArgument('filename');
		$noComment = $input->getOption('no-comment');
		$dryRun = $input->getOption('dry-run');

		if (!file_exists($filename)) {
			throw new LogicException('File not found');
		}

		try {
			$this->bus->handle(new ImportTyme2ToTimesheetCommand($filename, $noComment, $dryRun));
		} catch (DeviceAccessExpiredException) {
			$this->registerDeviceConsole->run(new ArrayInput([]), $output);
			$output->writeln('<error>Device access has expired. Please follow the instructions above and then run the command again.</error>');

			return 1;
		}

		return 0;
	}
}
