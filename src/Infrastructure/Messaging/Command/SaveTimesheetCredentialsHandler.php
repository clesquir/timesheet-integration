<?php declare(strict_types=1);

namespace App\Infrastructure\Messaging\Command;

use App\Infrastructure\Persistence\Vault\TimesheetVault;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class SaveTimesheetCredentialsHandler {
	public function __construct(
	) {
	}

	public function __invoke(SaveTimesheetCredentialsCommand $command): void {
		$filesystem = new Filesystem();

		if ($filesystem->exists(TimesheetVault::CREDENTIALS_FILE)) {
			$filesystem->remove(TimesheetVault::CREDENTIALS_FILE);
		}

		$filesystem->dumpFile(TimesheetVault::CREDENTIALS_FILE, json_encode($command->credentials()));
	}
}
