<?php

namespace App\Domain\Model;

use App\Infrastructure\Persistence\Mapping\TimesheetMapping;
use DateTime;
use DateTimeImmutable;
use DateTimeZone;

final class TimeEntry {
	const TIME_SEPARATOR = '<br />-----------<br />';

	private function __construct(
		private readonly DateTimeImmutable $startedAt,
		private readonly DateTimeImmutable $stoppedAt,
		private readonly int $activityId,
		private readonly string $description,
		private readonly string $issue
	) {
	}

	public function startedAt(): DateTimeImmutable {
		return $this->startedAt;
	}

	public function stoppedAt(): DateTimeImmutable {
		return $this->stoppedAt;
	}

	public function activityId(): int {
		return $this->activityId;
	}

	public function description(): string {
		return $this->description;
	}

	public function escapedDescription(): string {
		return preg_replace_callback('/([<>=?])/', function($matches) {return urlencode($matches[1]);}, $this->description);
	}

	public function issue(): string {
		return $this->issue;
	}

	public static function create(
		DateTimeImmutable $startedAt,
		DateTimeImmutable $stoppedAt,
		int $activityId,
		string $description,
		string $issue
	): self {
		return new self(
			$startedAt,
			$stoppedAt,
			$activityId,
			$description,
			$issue
		);
	}

	public static function fromTimeularArray(array $timeEntry, TimesheetMapping $timesheetMapping): self {
		$startedAt = new DateTime($timeEntry['duration']['startedAt'], new DateTimeZone('UTC'));
		$startedAt->setTimezone(new DateTimeZone('America/New_York'));
		$stoppedAt = new DateTime($timeEntry['duration']['stoppedAt'], new DateTimeZone('UTC'));
		$stoppedAt->setTimezone(new DateTimeZone('America/New_York'));
		$note = $timeEntry['note']['text'] ?? '';
		$note = preg_replace('/<{{\|t\|[0-9]+\|}}>/', '', $note);
		$issue = $timeEntry['note']['tags'][0]['label'] ?? '';

		return self::create(
			DateTimeImmutable::createFromMutable($startedAt),
			DateTimeImmutable::createFromMutable($stoppedAt),
			$timesheetMapping->get($timeEntry['activityId']),
			$note,
			$issue
		);
	}

	public static function fromTyme2Array(array $timeEntry, int $timesheetActivityId): self {
		$startedAt = new DateTime($timeEntry['timeStart'], new DateTimeZone('UTC'));
		$startedAt->setTimezone(new DateTimeZone('America/New_York'));
		$stoppedAt = new DateTime($timeEntry['timeEnd'], new DateTimeZone('UTC'));
		$stoppedAt->setTimezone(new DateTimeZone('America/New_York'));

		$note = $timeEntry['notes'];
		preg_match('/^(\S*) - .*/', $timeEntry['subtask'], $matches);

		$issue = "";
		if (count($matches) >= 2) {
			$issue = $matches[1];
		}

		return self::create(
			DateTimeImmutable::createFromMutable($startedAt),
			DateTimeImmutable::createFromMutable($stoppedAt),
			$timesheetActivityId,
			$note,
			$issue
		);
	}

	public static function fixtureWithStartedAtStoppedAt(DateTimeImmutable $startedAt, DateTimeImmutable $stoppedAt): self {
		return self::create(
			$startedAt,
			$stoppedAt,
			TimesheetMapping::TIMESHEET_OTHER,
			uniqid(),
			uniqid()
		);
	}
}
