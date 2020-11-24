<?php

namespace App\Infrastructure\Persistence\Mapping;

use Psr\Log\LoggerInterface;

final class TimesheetMapping {
	const TIMESHEET_OTHER = 42;

	private array $timesheetMapping;

	private LoggerInterface $logger;

	public function __construct(
		array $timesheetMappingParameter,
		LoggerInterface $logger
	) {
		$this->timesheetMapping = $timesheetMappingParameter;
		$this->logger = $logger;
	}

	public function exists(int $externalActivityId): bool {
		return isset($this->timesheetMapping[$externalActivityId]);
	}

	public function get(int $externalActivityId): int {
		return $this->timesheetMapping[$externalActivityId] ?? self::TIMESHEET_OTHER;
	}

	public static function fromMapping(
		array $mapping,
		LoggerInterface $logger
	): self {
		return new self($mapping, $logger);
	}
}
