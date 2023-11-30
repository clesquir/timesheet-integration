<?php declare(strict_types=1);

namespace App\Infrastructure\Messaging\Command;

final readonly class SaveTimesheetCredentialsCommand {
	public function __construct(
		private array $credentials
	) {
	}

	public function credentials(): array {
		return $this->credentials;
	}
}
