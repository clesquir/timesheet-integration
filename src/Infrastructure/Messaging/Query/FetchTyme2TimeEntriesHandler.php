<?php

namespace App\Infrastructure\Messaging\Query;

use App\Domain\Messaging\Bus;
use App\Domain\Model\Activity;
use App\Domain\Model\TimeEntry;
use App\Infrastructure\Persistence\Mapping\TimesheetMapping;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class FetchTyme2TimeEntriesHandler {
	const LOG_MESSAGE_NOT_MAPPED = "The activity '%s' is not mapped with Timesheet. It will be imported in 'Other'";
	const LOG_MESSAGE_INACTIVE = "The activity '%s' is not active in Timesheet. It might need to be changed";

	public function __construct(
		private Bus $bus,
		private LoggerInterface $logger
	) {
	}

	public function __invoke(FetchTyme2TimeEntriesQuery $query): array {
		$filename = $query->filename();

		/** @var Activity[] $activities */
		$activities = $this->bus->handle(new FetchTimesheetActivitiesQuery());

		$timesheetMapping = [];
		$activitiesById = [];
		foreach ($activities as $activity) {
			$timesheetMapping[$activity->id()] = $activity->id();
			$activitiesById[$activity->id()] = $activity;
		}

		$timesheetMapping = TimesheetMapping::fromMapping($timesheetMapping);

		$string = file_get_contents($filename);
		$content = json_decode($string, true);

		$timeEntries = [];
		foreach ($content['timed'] as $timeEntry) {
			$activityId = explode(' | ', $timeEntry['project'])[0];

			if (!is_numeric($activityId) || $timesheetMapping->exists($activityId) === false) {
				$this->logger->warning(sprintf(self::LOG_MESSAGE_NOT_MAPPED, $activityId));
				$activityId = TimesheetMapping::TIMESHEET_OTHER;
			} else {
				$activity = $activitiesById[$activityId] ?? null;

				if (!$activity?->isActive()) {
					$this->logger->warning(sprintf(self::LOG_MESSAGE_INACTIVE, $activityId));
				}
			}

			$timeEntries[] = TimeEntry::fromTyme2Array($timeEntry, $activityId);
		}

		return $timeEntries;
	}
}
