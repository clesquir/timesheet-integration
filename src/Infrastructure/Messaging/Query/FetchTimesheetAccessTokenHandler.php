<?php declare(strict_types=1);

namespace App\Infrastructure\Messaging\Query;

use App\Domain\Messaging\Bus;
use App\Infrastructure\Messaging\Command\GrantTimesheetDeviceCommand;
use App\Infrastructure\Messaging\Command\RefreshTimesheetTokenCommand;
use App\Infrastructure\Messaging\Command\SaveTimesheetCredentialsCommand;
use App\Infrastructure\Persistence\Vault\TimesheetVault;
use LogicException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

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
			throw new LogicException('Device not registered. Please run app:timesheet:device:register');
		}

		if (!isset($credentials['refresh_token'])) {
			if (!isset($credentials['device_code'])) {
				throw new LogicException('Device not registered. Please run app:timesheet:device:register');
			}

			$credentials = $this->bus->handle(new GrantTimesheetDeviceCommand($credentials['device_code']));
		}

		if (!isset($credentials['refresh_token'])) {
			throw new LogicException('Device not registered. Please run app:timesheet:device:register');
		}
		$credentials = $this->bus->handle(new RefreshTimesheetTokenCommand($credentials['refresh_token']));

		$this->bus->handle(new SaveTimesheetCredentialsCommand($credentials));

		return $credentials['access_token'];
	}
}
