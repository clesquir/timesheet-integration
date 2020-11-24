<?php

namespace App\Infrastructure\Messaging\Query;

use App\Domain\Messaging\Bus;
use App\Domain\Model\Activity;
use App\Infrastructure\Persistence\Vault\TimeularVault;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class FetchTimeularActivitiesHandler implements MessageHandlerInterface {
	private Bus $bus;

	private HttpClientInterface $client;

	private TimeularVault $timeularVault;

	public function __construct(
		Bus $bus,
		HttpClientInterface $client,
		TimeularVault $timeularVault
	) {
		$this->bus = $bus;
		$this->client = $client;
		$this->timeularVault = $timeularVault;
	}

	public function __invoke(FetchTimeularActivitiesQuery $query): array {
		$token = $this->bus->handle(new FetchTimeularTokenQuery());

		$response = $this->client->request(
			'GET',
			'https://api.timeular.com/api/v3/activities',
			[
				'headers' => [
					'Content-Type' => 'application/json',
					'Authorization' => "Bearer $token",
				],
			]
		);

		$content = $response->toArray();

		$activities = [];
		foreach ($content['activities'] as $activity) {
			$activities[] = Activity::create($activity['id'], $activity['name']);
		}

		return $activities;
	}
}
