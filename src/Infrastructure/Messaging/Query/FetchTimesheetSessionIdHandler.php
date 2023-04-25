<?php

namespace App\Infrastructure\Messaging\Query;

use App\Infrastructure\Persistence\Vault\TimesheetVault;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsMessageHandler]
final class FetchTimesheetSessionIdHandler {
	public function __construct(
		private readonly HttpClientInterface $client,
		private readonly TimesheetVault $timesheetVault
	) {
	}

	public function __invoke(FetchTimesheetSessionIdQuery $query): string {
		$client = new HttpBrowser($this->client);
		$loginPath = '/employee/login';
		$client->xmlHttpRequest(
			'POST',
			TimesheetVault::BASE_URL . $loginPath,
			[
				'email' => $this->timesheetVault->email(),
				'password' => $this->timesheetVault->password(),
				'saveLoginInfo' => 'on',
			],
			[],
			[
				'Content-Type' => 'application/json',
			]
		);
		$cookie = $client->getCookieJar();

		return $cookie->get('PHPSESSID', $loginPath)->getValue();
	}
}
