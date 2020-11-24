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
	private Bus $bus;

	public function __construct(Bus $bus) {
		parent::__construct();
		$this->bus = $bus;
	}

	protected function configure() {
		$this->setName('app:activities:timeular:display')
			->setDescription('Displays Timeular activities.');
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
