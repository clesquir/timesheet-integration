<?php declare(strict_types=1);

namespace App\Application\Console;

use App\Domain\Messaging\Bus;
use App\Infrastructure\Messaging\Command\RegisterTimesheetDeviceCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class RegisterTimesheetDeviceConsole extends Command {
	public function __construct(
		private readonly Bus $bus
	) {
		parent::__construct();
	}

	protected function configure(): void {
		$this->setName('app:timesheet:device:register')
			->setDescription('Register device.');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$output->write(
			array_map(
				function(string $line) {
					return "<info>$line</info>";
				},
				$this->bus->handle(new RegisterTimesheetDeviceCommand())
			),
			true
		);

		return 0;
	}
}
