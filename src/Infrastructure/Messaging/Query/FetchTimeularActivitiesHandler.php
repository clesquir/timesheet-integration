<?php

namespace App\Infrastructure\Messaging\Query;

use App\Domain\Messaging\Bus;
use App\Domain\Model\Activity;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsMessageHandler]
final readonly class FetchTimeularActivitiesHandler {
	public function __construct(
		private Bus $bus,
		private HttpClientInterface $client
	) {
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
			$activities[] = Activity::create($activity['id'], $activity['name'], true);
		}

		return $activities;
	}
}
