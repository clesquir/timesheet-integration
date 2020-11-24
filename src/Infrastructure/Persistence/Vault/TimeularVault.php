<?php

namespace App\Infrastructure\Persistence\Vault;

final class TimeularVault {
	private string $apiKey;

	private string $apiSecret;

	public function __construct(string $apiKey, string $apiSecret) {
		$this->apiKey = $apiKey;
		$this->apiSecret = $apiSecret;
	}

	public function apiKey(): string {
		return $this->apiKey;
	}

	public function apiSecret(): string {
		return $this->apiSecret;
	}

	public static function fixture() {
		return new self(uniqid(), uniqid());
	}
}
