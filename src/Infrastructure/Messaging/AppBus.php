<?php declare(strict_types=1);

namespace App\Infrastructure\Messaging;

use App\Domain\Messaging\Bus;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Webmozart\Assert\Assert;

final class AppBus implements Bus {
	public function __construct(
		private readonly MessageBusInterface $messageBus
	) {
	}

	public function handle(mixed $message): mixed {
		try {
			$envelope = $this->messageBus->dispatch($message);
		} catch (HandlerFailedException $e) {
			throw $e->getNestedExceptions()[0];
		}

		/** @var HandledStamp[] $handledStamps */
		$handledStamps = $envelope->all(HandledStamp::class);

		Assert::count(
			$handledStamps,
			1,
			"Exactly %d handled stamp was expected for message " . get_class($message) . ". Got %d."
		);

		return $handledStamps[0]->getResult();
	}
}
