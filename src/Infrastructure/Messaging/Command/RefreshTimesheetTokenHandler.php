<?php declare(strict_types=1);

namespace App\Infrastructure\Messaging\Command;

use App\Infrastructure\Persistence\Vault\TimesheetVault;
use LogicException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

#[AsMessageHandler]
final readonly class RefreshTimesheetTokenHandler {
	public function __construct(
		private HttpClientInterface $client
	) {
	}

	public function __invoke(RefreshTimesheetTokenCommand $command): array {
		try {
			$response = $this->client->request(
				'POST',
				TimesheetVault::KEYCLOAK_TOKEN,
				[
					'headers' => [
						'Content-Type' => 'application/json',
					],
					'body' => [
						'grant_type' => 'refresh_token',
						'client_id' => TimesheetVault::KEYCLOAK_CLIENT_ID,
						'refresh_token' => $command->refreshToken(),
					],
				]
			);
		} catch (Throwable) {
			throw new LogicException('Device access has expired. Please run app:timesheet:device:register');
		}

		return $response->toArray();
	}
}
