<?php

namespace App\Tests\Collaborator\Infrastructure\Messaging;

use App\Domain\Messaging\Bus;
use Closure;

final class TestBus implements Bus {
	private Bus $bus;
	private array $replaced_handlers;

	public function __construct(Bus $bus) {
		$this->bus = $bus;
	}

	public function replaceHandler(string $messageClass, Closure $handler): void {
		$this->replaced_handlers[$messageClass] = $handler;
	}

	public function clear() {
		$this->replaced_handlers = [];
	}

	public function handle($message) {
		if (isset($this->replaced_handlers[get_class($message)])) {
			return call_user_func($this->replaced_handlers[get_class($message)], $message);
		}
		return $this->bus->handle($message);
	}
}
