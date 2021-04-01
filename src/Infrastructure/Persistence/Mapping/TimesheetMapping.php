<?php

namespace App\Infrastructure\Persistence\Mapping;

final class TimesheetMapping {
	const TIMESHEET_OTHER = 42;

	private array $timesheetMapping;

	public function __construct(
		array $timesheetMappingParameter
	) {
		$this->timesheetMapping = $timesheetMappingParameter;
	}

	public function exists(int $externalActivityId): bool {
		return isset($this->timesheetMapping[$externalActivityId]);
	}

	public function get(int $externalActivityId): int {
		return $this->timesheetMapping[$externalActivityId] ?? self::TIMESHEET_OTHER;
	}

	public static function fromMapping(
		array $mapping
	): self {
		return new self($mapping);
	}
}
