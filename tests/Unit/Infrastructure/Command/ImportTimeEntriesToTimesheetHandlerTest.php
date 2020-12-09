<?php

namespace App\Tests\Unit\Infrastructure\Command;

use App\Domain\Model\TimeEntry;
use App\Infrastructure\Messaging\AppBus;
use App\Infrastructure\Messaging\Command\ImportTimeEntriesToTimesheetCommand;
use App\Infrastructure\Messaging\Command\ImportTimeEntriesToTimesheetHandler;
use App\Infrastructure\Messaging\Query\FetchTimesheetSessionIdQuery;
use App\Infrastructure\Persistence\Vault\TimesheetVault;
use App\Tests\Collaborator\Infrastructure\Messaging\TestBus;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\Messenger\MessageBus;

final class ImportTimeEntriesToTimesheetHandlerTest extends TestCase {
	public function test_it_adds_entries_to_timesheet_chronologically() {
		$bus = new TestBus(new AppBus(new MessageBus()));
		$bus->replaceHandler(
			FetchTimesheetSessionIdQuery::class,
			function() {
				return uniqid();
			}
		);

		$workDates = [];
		$handler = new ImportTimeEntriesToTimesheetHandler(
			$bus,
			$client = new MockHttpClient(
				[
					function(string $method, string $url, array $options) use (&$workDates) {
						parse_str($options['body'], $body);
						$workDates[] = $body['work_date'];
						return new MockResponse('[]');
					},
					function(string $method, string $url, array $options) use (&$workDates) {
						parse_str($options['body'], $body);
						$workDates[] = $body['work_date'];
						return new MockResponse('[]');
					},
					function(string $method, string $url, array $options) use (&$workDates) {
						parse_str($options['body'], $body);
						$workDates[] = $body['work_date'];
						return new MockResponse('[]');
					}
				],
				TimesheetVault::BASE_URL
			),
			TimesheetVault::fixture(),
			new NullLogger()
		);
		$handler->__invoke(new ImportTimeEntriesToTimesheetCommand(
			[
				$timeEntry1 = TimeEntry::fixtureWithStartedAtStoppedAt(new \DateTimeImmutable('2020-03-01'), new \DateTimeImmutable('2020-02-01')),
				$timeEntry2 = TimeEntry::fixtureWithStartedAtStoppedAt(new \DateTimeImmutable('2020-01-01'), new \DateTimeImmutable('2020-01-01')),
				$timeEntry3 = TimeEntry::fixtureWithStartedAtStoppedAt(new \DateTimeImmutable('2020-02-01'), new \DateTimeImmutable('2020-02-01')),
			],
			false,
			false
		));

		self::assertCount(3, $workDates);
		self::assertSame($timeEntry2->startedAt()->format('Y-m-d'), $workDates[0]);
		self::assertSame($timeEntry3->startedAt()->format('Y-m-d'), $workDates[1]);
		self::assertSame($timeEntry1->startedAt()->format('Y-m-d'), $workDates[2]);
	}
}
