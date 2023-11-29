<?php declare(strict_types=1);

namespace App\Infrastructure\Messaging\Command;

use App\Infrastructure\Persistence\Vault\TimesheetVault;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsMessageHandler]
final readonly class RegisterDeviceHandler {
	public function __construct(
		private HttpClientInterface $client
	) {
	}

	public function __invoke(RegisterDeviceCommand $query): array {
		$response = $this->client->request(
			'POST',
			TimesheetVault::KEYCLOAK_DEVICE_AUTH,
			[
				'headers' => [
					'Content-Type' => 'application/json',
				],
				'body' => [
					'client_id' => TimesheetVault::KEYCLOAK_CLIENT_ID,
				]
			]
		);
		$content = $response->toArray();

		$filesystem = new Filesystem();
		if ($filesystem->exists(TimesheetVault::CREDENTIALS_FILE)) {
			$filesystem->remove(TimesheetVault::CREDENTIALS_FILE);
		}

		$filesystem->dumpFile(TimesheetVault::CREDENTIALS_FILE, json_encode(['device_code' => $content['device_code']]));

		return [
			'Go to this URL to register and sign in with your devalto\'s email: ' . $content['verification_uri_complete'],
		];
	}
}
