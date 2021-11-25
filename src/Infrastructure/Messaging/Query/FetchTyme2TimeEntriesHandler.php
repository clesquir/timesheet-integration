<?php

namespace App\Infrastructure\Messaging\Query;

use App\Domain\Messaging\Bus;
use App\Domain\Model\Activity;
use App\Domain\Model\TimeEntry;
use App\Infrastructure\Persistence\Mapping\TimesheetMapping;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class FetchTyme2TimeEntriesHandler implements MessageHandlerInterface {
	private Bus $bus;

	private LoggerInterface $logger;

	public function __construct(
		Bus $bus,
		LoggerInterface $logger
	) {
		$this->bus = $bus;
		$this->logger = $logger;
	}

	public function __invoke(FetchTyme2TimeEntriesQuery $query): array {
		$filename = $query->filename();

		$timesheetMapping = $this->timesheetMapping();

		$string = file_get_contents($filename);
		$content = json_decode($string, true);

		$timeEntries = [];
		foreach ($content['timed'] as $timeEntry) {
			$activityId = explode(' | ', $timeEntry['project'])[0];

			if (!is_numeric($activityId) || $timesheetMapping->exists($activityId) === false) {
				$this->logger->warning("The activity '$activityId' is not mapped with Timesheet. It will be imported in 'Other'");
				$activityId = TimesheetMapping::TIMESHEET_OTHER;
			}

			$timeEntries[] = TimeEntry::fromTyme2Array($timeEntry, $activityId);
		}

		return $timeEntries;
	}

	private function timesheetMapping(): TimesheetMapping {
		/** @var Activity[] $activities */
		$activities = $this->bus->handle(new FetchTimesheetActivitiesQuery());

		$mapping = [];
		foreach ($activities as $activity) {
			$mapping[$activity->id()] = $activity->id();
		}

		return TimesheetMapping::fromMapping($mapping);
	}
}
