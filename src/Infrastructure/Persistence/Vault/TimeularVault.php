<?php

namespace App\Infrastructure\Persistence\Vault;

final class TimeularVault {
	public function __construct(
		private readonly string $apiKey,
		private readonly string $apiSecret
	) {
	}

	public function apiKey(): string {
		return $this->apiKey;
	}

	public function apiSecret(): string {
		return $this->apiSecret;
	}
}
