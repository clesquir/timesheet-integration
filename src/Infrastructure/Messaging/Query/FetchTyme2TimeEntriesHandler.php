<?php

namespace App\Infrastructure\Messaging\Query;

use App\Domain\Model\TimeEntry;
use App\Infrastructure\Persistence\Mapping\TimesheetMapping;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class FetchTyme2TimeEntriesHandler implements MessageHandlerInterface {
	private TimesheetMapping $timesheetMapping;

	private LoggerInterface $logger;

	public function __construct(
		TimesheetMapping $timesheetMapping,
		LoggerInterface $logger
	) {
		$this->timesheetMapping = $timesheetMapping;
		$this->logger = $logger;
	}

	public function __invoke(FetchTyme2TimeEntriesQuery $query): array {
		$filename = $query->filename();

		$string = file_get_contents($filename);
		$content = json_decode($string, true);

		$timeEntries = [];
		foreach ($content['timed'] as $timeEntry) {
			$activityId = explode(' | ', $timeEntry['project'])[0];

			if ($this->timesheetMapping->exists($activityId) === false) {
				$this->logger->warning("The activity {$activityId} is not mapped with Timesheet. It will be imported in 'Other'");
				$activityId = TimesheetMapping::TIMESHEET_OTHER;
			}

			$timeEntries[] = TimeEntry::fromTyme2Array($timeEntry, $activityId);
		}

		return $timeEntries;
	}
}
