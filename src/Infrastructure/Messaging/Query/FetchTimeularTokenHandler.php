<?php

namespace App\Infrastructure\Messaging\Query;

use App\Infrastructure\Persistence\Vault\TimeularVault;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsMessageHandler]
final class FetchTimeularTokenHandler {
	public function __construct(
		private readonly HttpClientInterface $client,
		private readonly TimeularVault $timeularVault
	) {
	}

	public function __invoke(FetchTimeularTokenQuery $query): string {
		$response = $this->client->request(
			'POST',
			'https://api.timeular.com/api/v3/developer/sign-in',
			[
				'headers' => [
					'Content-Type' => 'application/json',
				],
				'json' => [
					'apiKey' => $this->timeularVault->apiKey(),
					'apiSecret' => $this->timeularVault->apiSecret(),
				]
			]
		);

		return $response->toArray()['token'];
	}
}
