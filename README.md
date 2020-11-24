# Setup dev

Run `sh resources/dev/setup.sh`

# Setup Vaults

Create a file .env.dev.local and enter the following content with your values:

```
TIMEULAR_API_KEY=
TIMEULAR_API_SECRET=
TIMESHEET_EMAIL=
TIMESHEET_PASSWORD=
```

# Generate Timeular API key and secret

Go to https://profile.timeular.com/, 
click on "Refresh API key", 
copy and paste both key and secret in the file created at the previous step.

# Generate mapping file

Fill the mapping file located in config/timesheetMappings.yaml 
using the following format `TIMEULAR_ACTIVITY: TIMESHEET_ACTIVITY`.

To get the list of Timeular activities, execute this command:

`docker-compose run app bash -c "console app:activities:timeular:display"`

To get the list of Timesheet activities, execute this command:

`docker-compose run app bash -c "console app:activities:timesheet:display"`

# Import entry from Timeular

## Check what would be imported

`docker-compose run app bash -c "console app:import:timeular-to-timesheet -v --dry-run 2020-01-01"`

## Import

`docker-compose run app bash -c "console app:import:timeular-to-timesheet -v 2020-01-01"`

# Run tests

`docker-compose run --rm app vendor/phpunit/phpunit/phpunit`

# Shell

`docker-compose run --rm app bash`
