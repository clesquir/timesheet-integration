<?php declare(strict_types=1);

namespace App\Application\Console;

use App\Domain\Messaging\Bus;
use App\Domain\Model\Activity;
use App\Domain\Model\DeviceAccessExpiredException;
use App\Infrastructure\Messaging\Query\FetchTimesheetActivitiesQuery;
use App\Infrastructure\Messaging\Query\FetchTimesheetCredentialsQuery;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class DisplayTimesheetActivitiesConsole extends Command {
	public function __construct(
		private readonly Bus $bus,
		private readonly RegisterTimesheetDeviceConsole $registerDeviceConsole
	) {
		parent::__construct();
	}

	protected function configure(): void {
		$this->setName('app:timesheet:activities:display')
			->setDescription('Displays Timesheet activities.');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$credentials = $this->bus->handle(new FetchTimesheetCredentialsQuery());

		if ($credentials === null) {
			$this->registerDeviceConsole->run($input, $output);
			$output->writeln('<error>Device not registered. Please follow the instructions above and then run the command again.</error>');

			return 1;
		}

		$tbl = new Table($output);

		$tbl->setHeaders(
			[
				'Id',
				'Name',
			]
		);

		try {
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
		} catch (DeviceAccessExpiredException) {
			$this->registerDeviceConsole->run($input, $output);
			$output->writeln('<error>Device access has expired. Please follow the instructions above and then run the command again.</error>');

			return 1;
		}

		return 0;
	}
}
