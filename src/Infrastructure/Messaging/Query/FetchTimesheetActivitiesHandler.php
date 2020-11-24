<?php

namespace App\Infrastructure\Messaging\Query;

use App\Domain\Messaging\Bus;
use App\Domain\Model\Activity;
use App\Infrastructure\Persistence\Vault\TimesheetVault;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\BrowserKit\CookieJar;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class FetchTimesheetActivitiesHandler implements MessageHandlerInterface {
	private Bus $bus;

	private HttpClientInterface $client;

	private TimesheetVault $timesheetVault;

	public function __construct(
		Bus $bus,
		HttpClientInterface $client,
		TimesheetVault $timesheetVault
	) {
		$this->bus = $bus;
		$this->client = $client;
		$this->timesheetVault = $timesheetVault;
	}

	public function __invoke(FetchTimesheetActivitiesQuery $query): array {
		$phpSession = $this->bus->handle(new FetchTimesheetSessionIdQuery());

		$cookieJar = new CookieJar();
		$cookieJar->set(new Cookie('PHPSESSID', $phpSession));
		$client = new HttpBrowser($this->client, null, $cookieJar);
		$client->xmlHttpRequest(
			'POST',
			TimesheetVault::BASE_URL . '/project/list/format/json',
			[],
			[],
			[
				'Content-Type' => 'application/json',
			]
		);

		$projects = json_decode($client->getResponse()->getContent(), true)['projects'];

		$activities = [];
		foreach ($projects as $project) {
			$projectName = $project['name'];

			if ($project['employees'] !== '') {
				foreach ($project['activities'] as $activity) {
					$activities[] = Activity::create($activity['id'], $projectName . ' - ' . $activity['name']);
				}
			}
		}

		return $activities;
	}
}
