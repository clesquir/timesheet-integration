<?php

namespace App\Infrastructure\Persistence\Vault;

final class TimesheetVault {
	const BASE_URL = 'https://devalto.timesheet.wtf';

	public function __construct(
		private readonly string $email,
		private readonly string $password
	) {
	}

	public function email(): string {
		return $this->email;
	}

	public function password(): string {
		return $this->password;
	}
}
