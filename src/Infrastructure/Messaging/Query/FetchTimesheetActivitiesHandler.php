<?php

namespace App\Infrastructure\Messaging\Query;

use App\Domain\Messaging\Bus;
use App\Domain\Model\Activity;
use App\Infrastructure\Persistence\Vault\TimesheetVault;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\BrowserKit\CookieJar;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsMessageHandler]
final class FetchTimesheetActivitiesHandler {
	public function __construct(
		private readonly Bus $bus,
		private readonly HttpClientInterface $client
	) {
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
