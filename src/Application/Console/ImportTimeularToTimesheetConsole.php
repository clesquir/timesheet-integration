<?php

namespace App\Application\Console;

use App\Domain\Messaging\Bus;
use App\Infrastructure\Messaging\Command\ImportTimeularToTimesheetCommand;
use DateTimeImmutable;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class ImportTimeularToTimesheetConsole extends Command {
	private Bus $bus;

	public function __construct(Bus $bus) {
		parent::__construct();
		$this->bus = $bus;
	}

	protected function configure() {
		$this->setName('app:import:timeular-to-timesheet')
			->setDescription('Import Timeular entries to Timesheet.')
			->addArgument('date', InputArgument::REQUIRED, 'Date to import')
			->addOption('dry-run', null, InputOption::VALUE_NONE, 'Do not import time entries');
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$date = new DateTimeImmutable($input->getArgument('date'));
		$dryRun = $input->getOption('dry-run');

		$this->bus->handle(new ImportTimeularToTimesheetCommand($date, $dryRun));

		return 0;
	}
}
