<?php declare(strict_types=1);

namespace App\Infrastructure\Messaging\Command;

use App\Domain\Messaging\Bus;
use App\Infrastructure\Persistence\Vault\TimesheetVault;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsMessageHandler]
final readonly class RegisterTimesheetDeviceHandler {
	public function __construct(
		private Bus $bus,
		private HttpClientInterface $client
	) {
	}

	public function __invoke(RegisterTimesheetDeviceCommand $command): array {
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

		$this->bus->handle(new SaveTimesheetCredentialsCommand($content));

		return [
			'Go to this URL to register and sign in with your devalto\'s email: ' . $content['verification_uri_complete'],
		];
	}
}
