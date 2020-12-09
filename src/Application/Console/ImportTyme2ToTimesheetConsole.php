<?php

namespace App\Application\Console;

use App\Domain\Messaging\Bus;
use App\Infrastructure\Messaging\Command\ImportTyme2ToTimesheetCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class ImportTyme2ToTimesheetConsole extends Command {
	private Bus $bus;

	public function __construct(Bus $bus) {
		parent::__construct();
		$this->bus = $bus;
	}

	protected function configure() {
		$this->setName('app:import:tyme2-to-timesheet')
			->setDescription('Import Tyme2 entries to Timesheet.')
			->addArgument('filename', InputArgument::REQUIRED, 'Filename to import')
			->addOption('add-time-to-description', 't', InputOption::VALUE_NONE, 'Add time to description')
			->addOption('dry-run', null, InputOption::VALUE_NONE, 'Do not import time entries');
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$filename = $input->getArgument('filename');
		$addTimeToDescription = $input->getOption('add-time-to-description');
		$dryRun = $input->getOption('dry-run');

		if (!file_exists($filename)) {
			throw new \LogicException('File not found');
		}

		$this->bus->handle(new ImportTyme2ToTimesheetCommand($filename, $addTimeToDescription, $dryRun));

		return 0;
	}
}
