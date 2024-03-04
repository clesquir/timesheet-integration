<?php declare(strict_types=1);

namespace App\Infrastructure\Messaging\Command;

use App\Domain\Messaging\Bus;
use App\Domain\Model\TimeEntry;
use App\Infrastructure\Messaging\Query\FetchTimesheetAccessTokenQuery;
use App\Infrastructure\Persistence\Vault\TimesheetVault;
use DateTime;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsMessageHandler]
final readonly class ImportTimeEntriesToTimesheetHandler {
	public function __construct(
		private Bus $bus,
		private HttpClientInterface $client,
		private LoggerInterface $logger
	) {
	}

	public function __invoke(ImportTimeEntriesToTimesheetCommand $command): void {
		$token = $this->bus->handle(new FetchTimesheetAccessTokenQuery());

		$timeEntries = $command->timeEntries();
		usort(
			$timeEntries,
			function (TimeEntry $timeEntryA, TimeEntry $timeEntryB) {
				return $timeEntryA->startedAt() <=> $timeEntryB->startedAt();
			}
		);

		$sumHours = 0;
		$sumMinutes = 0;
		foreach ($timeEntries as $timeEntry) {
			$startedAt = new DateTime($timeEntry->startedAt()->format('Y-m-d H:i'));
			$stoppedAt = new DateTime($timeEntry->stoppedAt()->format('Y-m-d H:i'));
			$diff = date_diff($startedAt, $stoppedAt);
			$hours = $diff->format('%h');
			$sumHours += intval($hours);
			$minutes = $diff->format('%i');
			$sumMinutes += intval($minutes);
			if ($sumMinutes >= 60) {
				$sumHours++;
				$sumMinutes = $sumMinutes - 60;
			}

			$taskDescription = '';
			if ($command->noComment() === false) {
				$taskDescription = $timeEntry->escapedDescription() . TimeEntry::TIME_SEPARATOR .
					$startedAt->format('H') . 'H' . $startedAt->format('i') . ' - ' .
					$stoppedAt->format('H') . 'H' . $stoppedAt->format('i');
			}

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
				$this->client->request(
					'POST',
					TimesheetVault::BASE_URL . '/time/add/format/json',
					[
						'headers' => [
							'Content-Type' => 'application/json',
							'X-Requested-With' => 'XMLHttpRequest',
							'Authorization' => 'Bearer ' . $token,
						],
						'body' => $parameters,
					]
				);
			}
		}

		$this->logger->notice('Sum of the period: ' . $sumHours . 'h' . ($sumMinutes > 0 ? ' ' . $sumMinutes . 'min' : ''));
	}
}
