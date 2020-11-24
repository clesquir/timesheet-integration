<?php

namespace App\Infrastructure\Persistence\Vault;

final class TimesheetVault {
	const BASE_URL = 'https://devalto.timesheet.wtf';

	private string $email;

	private string $password;

	public function __construct(string $email, string $password) {
		$this->email = $email;
		$this->password = $password;
	}

	public function email(): string {
		return $this->email;
	}

	public function password(): string {
		return $this->password;
	}

	public static function fixture() {
		return new self(uniqid(), uniqid());
	}
}
