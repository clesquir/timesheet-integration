<?php

namespace App\Domain\Model;

use App\Infrastructure\Persistence\Mapping\TimesheetMapping;
use DateTime;
use DateTimeImmutable;
use DateTimeZone;

final class TimeEntry {
	const TIME_SEPARATOR = '<br />-----------<br />';

	private DateTimeImmutable $startedAt;

	private DateTimeImmutable $stoppedAt;

	private int $activityId;

	private string $description;

	private string $issue;

	private function __construct(
		DateTimeImmutable $startedAt,
		DateTimeImmutable $stoppedAt,
		int $activityId,
		string $description,
		string $issue
	) {
		$this->startedAt = $startedAt;
		$this->stoppedAt = $stoppedAt;
		$this->activityId = $activityId;
		$this->description = $description;
		$this->issue = $issue;
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

	public function issue(): string {
		return $this->issue;
	}

	public static function fromTimeularArray(array $timeEntry, TimesheetMapping $timesheetMapping): self {
		$startedAt = new DateTime($timeEntry['duration']['startedAt'], new DateTimeZone('UTC'));
		$startedAt->setTimezone(new DateTimeZone('America/New_York'));
		$stoppedAt = new DateTime($timeEntry['duration']['stoppedAt'], new DateTimeZone('UTC'));
		$stoppedAt->setTimezone(new DateTimeZone('America/New_York'));
		$note = $timeEntry['note']['text'];
		$note = preg_replace('/<{{\|t\|[0-9]+\|}}>/', '', $note);
		$issue = $timeEntry['note']['tags'][0]['label'] ?? '';

		return new self(
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

		return new self(
			DateTimeImmutable::createFromMutable($startedAt),
			DateTimeImmutable::createFromMutable($stoppedAt),
			$timesheetActivityId,
			$note,
			$issue
		);
	}

	public static function fixtureWithStartedAtStoppedAt(DateTimeImmutable $startedAt, DateTimeImmutable $stoppedAt) {
		return new self(
			$startedAt,
			$stoppedAt,
			TimesheetMapping::TIMESHEET_OTHER,
			uniqid(),
			uniqid()
		);
	}
}
