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

final class FetchCSVTimeEntriesHandler implements MessageHandlerInterface {
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

	public function __invoke(FetchCSVTimeEntriesQuery $query): array {
		$filename = $query->filename();

		$string = file_get_contents($filename);
		$content = json_decode($string, true);

		$timeEntries = [];
		foreach ($content['timed'] as $timeEntry) {
			$activity_id = explode(' | ', $timeEntry['project'])[0];
			if (!is_numeric($activity_id)) {
				$this->logger->warning("The Timeular activity {$activity_id} is not mapped with Timesheet. It will be imported in 'Other'");
				$activity_id = TimesheetMapping::TIMESHEET_OTHER;
			}

			$timeEntries[] = TimeEntry::fromCSVArray($timeEntry, $activity_id);
		}

		return $timeEntries;
	}
}
