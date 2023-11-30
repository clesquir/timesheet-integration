<?php

namespace App\Application\Console;

use App\Domain\Messaging\Bus;
use App\Domain\Model\Activity;
use App\Infrastructure\Messaging\Query\FetchTimeularActivitiesQuery;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class DisplayTimeularActivitiesConsole extends Command {
	public function __construct(
		private readonly Bus $bus
	) {
		parent::__construct();
	}

	protected function configure(): void {
		$this->setName('app:timeular:activities:display')
			->setDescription('Displays Timeular activities.');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$tbl = new Table($output);

		$tbl->setHeaders(
			[
				'Id',
				'Name',
			]
		);

		/** @var Activity[] $activities */
		$activities = $this->bus->handle(new FetchTimeularActivitiesQuery());

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
