<?php declare(strict_types=1);

namespace App\Domain\Messaging;

interface Bus {
	public function handle(mixed $message): mixed;
} 
