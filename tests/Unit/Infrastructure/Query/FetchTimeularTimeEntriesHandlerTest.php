<?php

namespace App\Tests\Unit\Infrastructure\Query;

use App\Domain\Model\TimeEntry;
use App\Infrastructure\Messaging\AppBus;
use App\Infrastructure\Messaging\Query\FetchTimeularTimeEntriesHandler;
use App\Infrastructure\Messaging\Query\FetchTimeularTimeEntriesQuery;
use App\Infrastructure\Messaging\Query\FetchTimeularTokenQuery;
use App\Infrastructure\Persistence\Mapping\TimesheetMapping;
use App\Tests\Collaborator\Infrastructure\Messaging\AlwaysSameHandlerForMessageClass;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\Messenger\MessageBus;

final class FetchTimeularTimeEntriesHandlerTest extends TestCase {
	public function test_it_returns_time_entries() {
		$bus = new AlwaysSameHandlerForMessageClass(new AppBus(new MessageBus()));
		$bus->replaceHandler(
			FetchTimeularTokenQuery::class,
			function() {
				return uniqid();
			}
		);

		$handler = new FetchTimeularTimeEntriesHandler(
			$bus,
			new MockHttpClient(
				[
					new MockResponse(json_encode(
						[
							'timeEntries' => [
								$entry1 = [
									'duration' => [
										'startedAt' => (new DateTimeImmutable())->setTimestamp(mt_rand())->format('Y-m-d H:i:s.u'),
										'stoppedAt' => (new DateTimeImmutable())->setTimestamp(mt_rand())->format('Y-m-d H:i:s.u'),
									],
									'note' => [
										'text' => uniqid(),
									],
									'activityId' => mt_rand(),
								],
								$entry2 = [
									'duration' => [
										'startedAt' => (new DateTimeImmutable())->setTimestamp(mt_rand())->format('Y-m-d H:i:s.u'),
										'stoppedAt' => (new DateTimeImmutable())->setTimestamp(mt_rand())->format('Y-m-d H:i:s.u'),
									],
									'note' => [
										'text' => uniqid(),
									],
									'activityId' => mt_rand(),
								],
							],
						]
					)),
				]
			),
			TimesheetMapping::fromMapping([]),
			new NullLogger()
		);
		$date = (new DateTimeImmutable())->setTimestamp(mt_rand());
		$resultEntries = $handler->__invoke(new FetchTimeularTimeEntriesQuery($date));

		self::assertCount(2, $resultEntries);
		/** @var TimeEntry $resultEntry */
		$resultEntry = $resultEntries[0];
		$requestEntry = TimeEntry::fromTimeularArray($entry1, TimesheetMapping::fromMapping([]));
		$this->assertTimeEntry($requestEntry, $resultEntry);

		$resultEntry = $resultEntries[1];
		$requestEntry = TimeEntry::fromTimeularArray($entry2, TimesheetMapping::fromMapping([]));
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
