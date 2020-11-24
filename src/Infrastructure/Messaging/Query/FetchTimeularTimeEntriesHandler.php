<?php

namespace App\Infrastructure\Messaging\Query;

use App\Domain\Messaging\Bus;
use App\Domain\Model\TimeEntry;
use App\Infrastructure\Persistence\Mapping\TimesheetMapping;
use App\Infrastructure\Persistence\Vault\TimeularVault;
use DateTime;
use DateTimeZone;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class FetchTimeularTimeEntriesHandler implements MessageHandlerInterface {
	private Bus $bus;

	private HttpClientInterface $client;

	private TimesheetMapping $timesheetMapping;

	private TimeularVault $timeularVault;

	private LoggerInterface $logger;

	public function __construct(
		Bus $bus,
		HttpClientInterface $client,
		TimesheetMapping $timesheetMapping,
		TimeularVault $timeularVault,
		LoggerInterface $logger
	) {
		$this->bus = $bus;
		$this->client = $client;
		$this->timesheetMapping = $timesheetMapping;
		$this->timeularVault = $timeularVault;
		$this->logger = $logger;
	}

	public function __invoke(FetchTimeularTimeEntriesQuery $query): array {
		$token = $this->bus->handle(new FetchTimeularTokenQuery());

		$date = $query->date();
		$rangeStart = new DateTime($date->format('Y-m-d 00:00:00'), new DateTimeZone('America/New_York'));
		$rangeStart->setTimezone(new DateTimeZone('UTC'));
		$rangeEnd = new DateTime($date->format('Y-m-d 23:59:59'), new DateTimeZone('America/New_York'));
		$rangeEnd->setTimezone(new DateTimeZone('UTC'));

		$response = $this->client->request(
			'GET',
			'https://api.timeular.com/api/v3/time-entries/' . $rangeStart->format('Y-m-d\TH:i:s.000') . '/' . $rangeEnd->format('Y-m-d\TH:i:s.000'),
			[
				'headers' => [
					'Content-Type' => 'application/json',
					'Authorization' => "Bearer $token",
				],
			]
		);

		$content = $response->toArray();

		$timeEntries = [];
		foreach ($content['timeEntries'] as $timeEntry) {
			if ($this->timesheetMapping->exists($timeEntry['activityId']) === false) {
				$this->logger->warning("The Timeular activity {$timeEntry['activityId']} is not mapped with Timesheet. It will be imported in 'Other'");
			}

			$timeEntries[] = TimeEntry::fromTimeularArray($timeEntry, $this->timesheetMapping);
		}

		return $timeEntries;
	}
}
