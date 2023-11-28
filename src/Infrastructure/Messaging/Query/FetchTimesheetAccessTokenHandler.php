<?php declare(strict_types=1);

namespace App\Infrastructure\Messaging\Query;

use App\Infrastructure\Persistence\Vault\TimesheetVault;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsMessageHandler]
final readonly class FetchTimesheetAccessTokenHandler {
	public function __construct(
		private HttpClientInterface $client,
		private TimesheetVault $timesheetVault
	) {
	}

	public function __invoke(FetchTimesheetAccessTokenQuery $query): string {
		$response = $this->client->request(
			'POST',
			TimesheetVault::KEYCLOAK_TOKEN,
			[
				'headers' => [
					'Content-Type' => 'application/json',
				],
				'body' => [
					'grant_type' => 'urn:ietf:params:oauth:grant-type:device_code',
					'client_id' => TimesheetVault::KEYCLOAK_CLIENT_ID,
					'device_code' => $this->timesheetVault->deviceCode(),
				],
			]
		);
		$content = $response->toArray();

		return $content['access_token'];
	}
}
