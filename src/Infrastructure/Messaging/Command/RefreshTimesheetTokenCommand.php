<?php declare(strict_types=1);

namespace App\Infrastructure\Messaging\Command;

final readonly class RefreshTimesheetTokenCommand {
	public function __construct(
		private string $refreshToken
	) {
	}

	public function refreshToken(): string {
		return $this->refreshToken;
	}
}
