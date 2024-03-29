<?php declare(strict_types=1);

namespace App\Infrastructure\Messaging\Command;

use App\Domain\Model\DeviceAccessExpiredException;
use App\Infrastructure\Persistence\Vault\TimesheetVault;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

#[AsMessageHandler]
final readonly class GrantTimesheetDeviceHandler {
	public function __construct(
		private HttpClientInterface $client
	) {
	}

	public function __invoke(GrantTimesheetDeviceCommand $command): array {
		try {
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
						'device_code' => $command->deviceCode(),
					],
				]
			);

			return $response->toArray();
		} catch (Throwable) {
			throw new DeviceAccessExpiredException('Device access has expired. Please run app:timesheet:device:register');
		}
	}
}
