<?php declare(strict_types=1);

namespace App\Infrastructure\Persistence\Vault;

final readonly class TimesheetVault {
	const KEYCLOAK_DEVICE_AUTH = 'https://keycloak.deval.to/auth/realms/DevAlto/protocol/openid-connect/auth/device';
	const KEYCLOAK_TOKEN = 'https://keycloak.deval.to/auth/realms/DevAlto/protocol/openid-connect/token';
	const KEYCLOAK_CLIENT_ID = 'timesheet';
	const BASE_URL = 'https://devalto-stg.timesheet.wtf';
	const CREDENTIALS_FILE = '/tmp/timesheet-credentials.json';
}
