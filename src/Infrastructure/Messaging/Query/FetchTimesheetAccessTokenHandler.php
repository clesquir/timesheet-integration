<?php declare(strict_types=1);

namespace App\Infrastructure\Messaging\Query;

use App\Domain\Messaging\Bus;
use App\Infrastructure\Persistence\Vault\TimesheetVault;
use LogicException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsMessageHandler]
final readonly class FetchTimesheetAccessTokenHandler {
	public function __construct(
		private Bus $bus,
		private HttpClientInterface $client
	) {
	}

	public function __invoke(FetchTimesheetAccessTokenQuery $query): string {
		$credentials = $this->bus->handle(new FetchTimesheetCredentialsQuery());

		if ($credentials === null) {
			throw new LogicException('Device not registered');
		}

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
					'device_code' => $credentials['device_code'],
				],
			]
		);
		$content = $response->toArray();

		return $content['access_token'];
	}
}
