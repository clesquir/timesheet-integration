<?php declare(strict_types=1);

namespace App\Infrastructure\Messaging\Command;

final readonly class GrantTimesheetDeviceCommand {
	public function __construct(
		private string $deviceCode
	) {
	}

	public function deviceCode(): string {
		return $this->deviceCode;
	}
}
