<?php

namespace App\Infrastructure\Messaging\Query;

use App\Infrastructure\Persistence\Vault\TimesheetVault;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class FetchTimesheetSessionIdHandler implements MessageHandlerInterface {
	private HttpClientInterface $client;

	private TimesheetVault $timesheetVault;

	public function __construct(
		HttpClientInterface $client,
		TimesheetVault $timesheetVault
	) {
		$this->client = $client;
		$this->timesheetVault = $timesheetVault;
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
