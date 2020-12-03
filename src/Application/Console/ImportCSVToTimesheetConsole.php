<?php

namespace App\Application\Console;

use App\Domain\Messaging\Bus;
use App\Infrastructure\Messaging\Command\ImportCSVToTimesheetCommand;
use App\Infrastructure\Messaging\Command\ImportTimeularToTimesheetCommand;
use DateTimeImmutable;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class ImportCSVToTimesheetConsole extends Command {
	private Bus $bus;

	public function __construct(Bus $bus) {
		parent::__construct();
		$this->bus = $bus;
	}

	protected function configure() {
		$this->setName('app:import:csv-to-timesheet')
			->setDescription('Import Tyme2 entries to Timesheet.')
			->addArgument('filename', InputArgument::REQUIRED, 'filename to import')
			->addOption('dry-run', null, InputOption::VALUE_NONE, 'Do not import time entries');
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$filename = $input->getArgument('filename');
		$dryRun = $input->getOption('dry-run');

		if (!file_exists($filename)) {
			throw new \LogicException('file not found');
		}

		$this->bus->handle(new ImportCSVToTimesheetCommand($dryRun, $filename));

		return 0;
	}
}
