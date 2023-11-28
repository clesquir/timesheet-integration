<?php declare(strict_types=1);

namespace App\Infrastructure\Messaging\Query;

use App\Domain\Messaging\Bus;
use App\Domain\Model\Activity;
use App\Infrastructure\Persistence\Vault\TimesheetVault;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsMessageHandler]
final readonly class FetchTimesheetActivitiesHandler {
	public function __construct(
		private Bus $bus,
		private HttpClientInterface $client
	) {
	}

	public function __invoke(FetchTimesheetActivitiesQuery $query): array {
		$token = $this->bus->handle(new FetchTimesheetAccessTokenQuery());

		$response = $this->client->request(
			'GET',
			TimesheetVault::BASE_URL . '/project/list?format=json',
			[
				'headers' => [
					'Content-Type' => 'application/json',
					'Authorization' => "Bearer $token",
				],
			],
		);

		$projects = $response->toArray()['projects'];

		$activities = [];
		foreach ($projects as $project) {
			$projectName = $project['name'];
			$isActive = boolval($project['status']);

			if ($project['employees'] !== '') {
				foreach ($project['activities'] as $activity) {
					$activities[] = Activity::create($activity['id'], $projectName . ' - ' . $activity['name'], $isActive);
				}
			}
		}

		return $activities;
	}
}
