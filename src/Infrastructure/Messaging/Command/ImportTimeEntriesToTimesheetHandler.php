<?php

namespace App\Infrastructure\Messaging\Command;

use App\Domain\Messaging\Bus;
use App\Domain\Model\TimeEntry;
use App\Infrastructure\Messaging\Query\FetchTimesheetSessionIdQuery;
use App\Infrastructure\Persistence\Vault\TimesheetVault;
use DateTime;
use Psr\Log\LoggerInterface;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\BrowserKit\CookieJar;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class ImportTimeEntriesToTimesheetHandler implements MessageHandlerInterface {
	private Bus $bus;

	private HttpClientInterface $client;

	private TimesheetVault $timesheetVault;

	private LoggerInterface $logger;

	public function __construct(
		Bus $bus,
		HttpClientInterface $client,
		TimesheetVault $timesheetVault,
		LoggerInterface $logger
	) {
		$this->bus = $bus;
		$this->client = $client;
		$this->timesheetVault = $timesheetVault;
		$this->logger = $logger;
	}

	public function __invoke(ImportTimeEntriesToTimesheetCommand $command) {
		$phpSession = $this->bus->handle(new FetchTimesheetSessionIdQuery());

		$timeEntries = $command->timeEntries();
		usort(
			$timeEntries,
			function(TimeEntry $timeEntryA, TimeEntry $timeEntryB) {
				return $timeEntryA->startedAt() > $timeEntryB->startedAt();
			}
		);

		$sumHours = 0;
		$sumMinutes = 0;
		foreach ($timeEntries as $timeEntry) {
			$startedAt = new DateTime($timeEntry->startedAt()->format('Y-m-d H:i'));
			$stoppedAt = new DateTime($timeEntry->stoppedAt()->format('Y-m-d H:i'));
			$diff = date_diff($startedAt, $stoppedAt);
			$hours = $diff->format('%h');
			$sumHours += $hours;
			$minutes = $diff->format('%i');
			$sumMinutes += $minutes;
			if ($sumMinutes >= 60) {
				$sumHours++;
				$sumMinutes = $sumMinutes - 60;
			}

			$taskDescription = $timeEntry->description() .
				TimeEntry::TIME_SEPARATOR .
				$startedAt->format('H') . 'H' . $startedAt->format('i') . ' - ' .
				$stoppedAt->format('H') . 'H' . $stoppedAt->format('i');

			$parameters = [
				'work_date' => $timeEntry->startedAt()->format('Y-m-d'),
				'hours' => $hours,
				'minutes' => $minutes,
				'activity_id' => $timeEntry->activityId(),
				'task_description' => $taskDescription,
				'issue' => $timeEntry->issue(),
			];

			$this->logger->notice('Importing to timesheet: ' . json_encode($parameters));

			if ($command->dryRun() === false) {
				$cookieJar = new CookieJar();
				$cookieJar->set(new Cookie('PHPSESSID', $phpSession));
				$client = new HttpBrowser($this->client, null, $cookieJar);
				$client->xmlHttpRequest(
					'POST',
					TimesheetVault::BASE_URL . '/time/add/format/json',
					$parameters,
					[],
					[
						'Content-Type' => 'application/json',
					]
				);
			}
		}

		$this->logger->notice('Sum of the period: ' . $sumHours . 'h' . ($sumMinutes > 0 ? ' ' . $sumMinutes . 'min' : ''));
	}
}
