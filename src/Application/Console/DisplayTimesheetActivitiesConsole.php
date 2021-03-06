<?php

namespace App\Application\Console;

use App\Domain\Messaging\Bus;
use App\Domain\Model\Activity;
use App\Infrastructure\Messaging\Query\FetchTimesheetActivitiesQuery;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class DisplayTimesheetActivitiesConsole extends Command {
	private Bus $bus;

	public function __construct(Bus $bus) {
		parent::__construct();
		$this->bus = $bus;
	}

	protected function configure() {
		$this->setName('app:activities:timesheet:display')
			->setDescription('Displays Timesheet activities.');
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$tbl = new Table($output);

		$tbl->setHeaders(
			[
				'Id',
				'Name',
			]
		);

		/** @var Activity[] $activities */
		$activities = $this->bus->handle(new FetchTimesheetActivitiesQuery());

		foreach ($activities as $activity) {
			$tbl->addRow(
				[
					$activity->id(),
					$activity->name(),
				]
			);
		}

		$tbl->render();

		return 0;
	}
}
