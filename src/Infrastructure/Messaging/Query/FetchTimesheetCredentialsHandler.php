<?php declare(strict_types=1);

namespace App\Infrastructure\Messaging\Query;

use App\Infrastructure\Persistence\Vault\TimesheetVault;
use Exception;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class FetchTimesheetCredentialsHandler {
	public function __construct(
	) {
	}

	public function __invoke(FetchTimesheetCredentialsQuery $query): array {
		$filesystem = new Filesystem();
		if ($filesystem->exists(TimesheetVault::CREDENTIALS_FILE)) {
			return json_decode(file_get_contents(TimesheetVault::CREDENTIALS_FILE), true);
		} else {
			throw new Exception('No device code found. Please run app:device:register');
		}
	}
}
