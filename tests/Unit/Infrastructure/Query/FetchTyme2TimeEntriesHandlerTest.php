<?php

namespace App\Tests\Unit\Infrastructure\Query;

use App\Domain\Model\Activity;
use App\Domain\Model\TimeEntry;
use App\Infrastructure\Messaging\AppBus;
use App\Infrastructure\Messaging\Query\FetchTimesheetActivitiesQuery;
use App\Infrastructure\Messaging\Query\FetchTyme2TimeEntriesHandler;
use App\Infrastructure\Messaging\Query\FetchTyme2TimeEntriesQuery;
use App\Infrastructure\Persistence\Mapping\TimesheetMapping;
use App\Tests\Collaborator\Infrastructure\Messaging\AlwaysSameHandlerForMessageClass;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\ErrorHandler\BufferingLogger;
use Symfony\Component\Messenger\MessageBus;

final class FetchTyme2TimeEntriesHandlerTest extends TestCase {
	public function test_it_returns_time_entries() {
		$bus = new AlwaysSameHandlerForMessageClass(new AppBus(new MessageBus()));
		$bus->replaceHandler(
			FetchTimesheetActivitiesQuery::class,
			function() {
				return [
					Activity::create(1, uniqid(), true),
					Activity::create(2, uniqid(), true),
				];
			}
		);

		$handler = new FetchTyme2TimeEntriesHandler(
			$bus,
			new NullLogger()
		);
		$resultEntries = $handler->__invoke(new FetchTyme2TimeEntriesQuery(__DIR__ . '/_files/tyme2.json'));

		self::assertCount(2, $resultEntries);
		/** @var TimeEntry $resultEntry */
		$resultEntry = $resultEntries[0];
		$requestEntry = TimeEntry::create(
			new DateTimeImmutable('2023-01-01 04:00'),
			new DateTimeImmutable('2023-01-01 05:00'),
			1,
			'notes1',
			'111'
		);
		$this->assertTimeEntry($requestEntry, $resultEntry);

		$resultEntry = $resultEntries[1];
		$requestEntry = TimeEntry::create(
			new DateTimeImmutable('2023-01-01 05:00'),
			new DateTimeImmutable('2023-01-01 06:15'),
			2,
			'notes2',
			'222'
		);
		$this->assertTimeEntry($requestEntry, $resultEntry);
	}

	public function test_it_converts_to_other_if_not_present_in_timesheet(): void {
		$bus = new AlwaysSameHandlerForMessageClass(new AppBus(new MessageBus()));
		$bus->replaceHandler(
			FetchTimesheetActivitiesQuery::class,
			function() {
				return [
					Activity::create(1, uniqid(), true),
					Activity::create(3, uniqid(), true),
				];
			}
		);

		$handler = new FetchTyme2TimeEntriesHandler(
			$bus,
			$logger = new BufferingLogger()
		);
		$resultEntries = $handler->__invoke(new FetchTyme2TimeEntriesQuery(__DIR__ . '/_files/tyme2.json'));

		self::assertCount(2, $resultEntries);
		/** @var TimeEntry $resultEntry */
		$resultEntry = $resultEntries[0];
		$requestEntry = TimeEntry::create(
			new DateTimeImmutable('2023-01-01 04:00'),
			new DateTimeImmutable('2023-01-01 05:00'),
			1,
			'notes1',
			'111'
		);
		$this->assertTimeEntry($requestEntry, $resultEntry);

		$resultEntry = $resultEntries[1];
		$requestEntry = TimeEntry::create(
			new DateTimeImmutable('2023-01-01 05:00'),
			new DateTimeImmutable('2023-01-01 06:15'),
			TimesheetMapping::TIMESHEET_OTHER,
			'notes2',
			'222'
		);
		$this->assertTimeEntry($requestEntry, $resultEntry);

		$logs = $logger->cleanLogs();
		self::assertCount(1, $logs);
		self::assertSame(sprintf(FetchTyme2TimeEntriesHandler::LOG_MESSAGE_NOT_MAPPED, 2), $logs[0][1]);
	}

	public function test_it_logs_a_warning_if_activity_is_inactive_in_timesheet(): void {
		$bus = new AlwaysSameHandlerForMessageClass(new AppBus(new MessageBus()));
		$bus->replaceHandler(
			FetchTimesheetActivitiesQuery::class,
			function() {
				return [
					Activity::create(1, uniqid(), true),
					Activity::create(2, uniqid(), false),
				];
			}
		);

		$handler = new FetchTyme2TimeEntriesHandler(
			$bus,
			$logger = new BufferingLogger()
		);
		$resultEntries = $handler->__invoke(new FetchTyme2TimeEntriesQuery(__DIR__ . '/_files/tyme2.json'));

		self::assertCount(2, $resultEntries);
		/** @var TimeEntry $resultEntry */
		$resultEntry = $resultEntries[0];
		$requestEntry = TimeEntry::create(
			new DateTimeImmutable('2023-01-01 04:00'),
			new DateTimeImmutable('2023-01-01 05:00'),
			1,
			'notes1',
			'111'
		);
		$this->assertTimeEntry($requestEntry, $resultEntry);

		$resultEntry = $resultEntries[1];
		$requestEntry = TimeEntry::create(
			new DateTimeImmutable('2023-01-01 05:00'),
			new DateTimeImmutable('2023-01-01 06:15'),
			2,
			'notes2',
			'222'
		);
		$this->assertTimeEntry($requestEntry, $resultEntry);

		$logs = $logger->cleanLogs();
		self::assertCount(1, $logs);
		self::assertSame(sprintf(FetchTyme2TimeEntriesHandler::LOG_MESSAGE_INACTIVE, 2), $logs[0][1]);
	}

	private function assertTimeEntry(TimeEntry $requestEntry, TimeEntry $resultEntry): void {
		self::assertSame($requestEntry->startedAt()->format('Y-m-d H:i'), $resultEntry->startedAt()->format('Y-m-d H:i'));
		self::assertSame($requestEntry->stoppedAt()->format('Y-m-d H:i'), $resultEntry->stoppedAt()->format('Y-m-d H:i'));
		self::assertSame($requestEntry->activityId(), $resultEntry->activityId());
		self::assertSame($requestEntry->description(), $resultEntry->description());
		self::assertSame($requestEntry->issue(), $resultEntry->issue());
	}
}
