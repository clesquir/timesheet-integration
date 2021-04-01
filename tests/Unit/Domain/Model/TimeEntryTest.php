<?php

namespace App\Tests\Unit\Domain\Model;

use App\Domain\Model\TimeEntry;
use App\Infrastructure\Persistence\Mapping\TimesheetMapping;
use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use PHPUnit\Framework\TestCase;

final class TimeEntryTest extends TestCase {
	public function test_it_creates_time_entry_from_timeular() {
		$startedAt = (new DateTime('now', new DateTimeZone('UTC')))->setTimestamp(mt_rand());
		$stoppedAt = (new DateTime('now', new DateTimeZone('UTC')))->setTimestamp(mt_rand());

		$timesheetMapping = TimesheetMapping::fromMapping(
			[$timeularActivity = mt_rand() => $timesheetActivity = mt_rand()]
		);
		$timeEntry = TimeEntry::fromTimeularArray(
			[
				'duration' => [
					'startedAt' => $startedAt->format('Y-m-d H:i:s.u'),
					'stoppedAt' => $stoppedAt->format('Y-m-d H:i:s.u'),
				],
				'note' => [
					'text' => $note = uniqid(),
				],
				'activityId' => $timeularActivity,
			],
			$timesheetMapping
		);

		$startedAt->setTimezone(new DateTimeZone('America/New_York'));
		$stoppedAt->setTimezone(new DateTimeZone('America/New_York'));

		self::assertSame($startedAt->format('Y-m-d H:i'), $timeEntry->startedAt()->format('Y-m-d H:i'));
		self::assertSame($stoppedAt->format('Y-m-d H:i'), $timeEntry->stoppedAt()->format('Y-m-d H:i'));
		self::assertSame($note, $timeEntry->description());
		self::assertSame($timesheetActivity, $timeEntry->activityId());
		self::assertSame('', $timeEntry->issue());
	}

	public function test_it_create_time_entry_from_timeular_with_issue_number() {
		$textNote = uniqid();
		$timeEntry = TimeEntry::fromTimeularArray(
			[
				'duration' => [
					'startedAt' => $startedAt = (new DateTimeImmutable())->setTimestamp(mt_rand())->format('Y-m-d H:i:s.u'),
					'stoppedAt' => $stoppeddAt = (new DateTimeImmutable())->setTimestamp(mt_rand())->format('Y-m-d H:i:s.u'),
				],
				'note' => [
					'text' => $note = '<{{|t|' . mt_rand() . '|}}>' . $textNote . '<{{|t|' . mt_rand() . '|}}>',
					'tags' => [
						[
							'label' => $issue = uniqid(),
						],
					],
				],
				'activityId' => mt_rand(),
			],
			TimesheetMapping::fromMapping([])
		);
		self::assertSame($textNote, $timeEntry->description());
		self::assertSame($issue, $timeEntry->issue());
	}

	public function test_it_create_time_entry_from_timeular_with_unknown_activity_id() {
		$timeEntry = TimeEntry::fromTimeularArray(
			[
				'duration' => [
					'startedAt' => $startedAt = (new DateTimeImmutable())->setTimestamp(mt_rand())->format('Y-m-d H:i:s.u'),
					'stoppedAt' => $stoppeddAt = (new DateTimeImmutable())->setTimestamp(mt_rand())->format('Y-m-d H:i:s.u'),
				],
				'note' => [
					'text' => $note = uniqid(),
				],
				'activityId' => mt_rand(),
			],
			TimesheetMapping::fromMapping([])
		);
		self::assertSame(TimesheetMapping::TIMESHEET_OTHER, $timeEntry->activityId());
	}
}
