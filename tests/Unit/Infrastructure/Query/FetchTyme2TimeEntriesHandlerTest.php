<?php

namespace App\Tests\Unit\Infrastructure\Query;

use App\Domain\Model\Activity;
use App\Domain\Model\TimeEntry;
use App\Infrastructure\Messaging\AppBus;
use App\Infrastructure\Messaging\Query\FetchTimesheetActivitiesQuery;
use App\Infrastructure\Messaging\Query\FetchTyme2TimeEntriesHandler;
use App\Infrastructure\Messaging\Query\FetchTyme2TimeEntriesQuery;
use App\Tests\Collaborator\Infrastructure\Messaging\AlwaysSameHandlerForMessageClass;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\Messenger\MessageBus;

final class FetchTyme2TimeEntriesHandlerTest extends TestCase {
	public function test_it_returns_time_entries() {
		$bus = new AlwaysSameHandlerForMessageClass(new AppBus(new MessageBus()));
		$bus->replaceHandler(
			FetchTimesheetActivitiesQuery::class,
			function() {
				return [
					Activity::create(1, uniqid()),
					Activity::create(2, uniqid()),
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

	private function assertTimeEntry(TimeEntry $requestEntry, TimeEntry $resultEntry): void {
		self::assertSame($requestEntry->startedAt()->format('Y-m-d H:i'), $resultEntry->startedAt()->format('Y-m-d H:i'));
		self::assertSame($requestEntry->stoppedAt()->format('Y-m-d H:i'), $resultEntry->stoppedAt()->format('Y-m-d H:i'));
		self::assertSame($requestEntry->activityId(), $resultEntry->activityId());
		self::assertSame($requestEntry->description(), $resultEntry->description());
		self::assertSame($requestEntry->issue(), $resultEntry->issue());
	}
}
