<?php

namespace App\Infrastructure\Messaging\Query;

use App\Infrastructure\Persistence\Vault\TimeularVault;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class FetchTimeularTokenHandler implements MessageHandlerInterface {
	private HttpClientInterface $client;

	private TimeularVault $timeularVault;

	public function __construct(
		HttpClientInterface $client,
		TimeularVault $timeularVault
	) {
		$this->client = $client;
		$this->timeularVault = $timeularVault;
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
