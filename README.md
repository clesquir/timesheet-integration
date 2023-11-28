# Setup dev

Run `sh resources/dev/setup.sh`

# Register device

Run `docker-compose run app bash -c "console app:device:register -v"`

# Setup Vaults

Copy .env.dev.local.dist to .env.dev.local and fill the variables with your values.

# Generate Timeular API key and secret

Go to https://profile.timeular.com/, 
click on "Refresh API key", 
copy and paste both key and secret in the file created at the previous step.

# Generate mapping file

Fill the mapping file located in config/timesheetMappings.yaml 
using the following format `TIMEULAR_ACTIVITY: TIMESHEET_ACTIVITY`.

To get the list of Timeular activities, execute this command:

`docker-compose run app bash -c "console app:activities:timeular:display -v"`

To get the list of Timesheet activities, execute this command:

`docker-compose run app bash -c "console app:activities:timesheet:display -v"`

# Import entry from Timeular

## Check what would be imported

`docker-compose run app bash -c "console app:import:timeular-to-timesheet -v --dry-run 2020-01-01"`

## Import

`docker-compose run app bash -c "console app:import:timeular-to-timesheet -v 2020-01-01"`

# Import entry from Tyme2

## Check what would be imported

`docker-compose run app bash -c "console app:import:tyme2-to-timesheet -v --dry-run JSON_FILENAME"`

## Import

`docker-compose run app bash -c "console app:import:tyme2-to-timesheet -v JSON_FILENAME"`

# Run tests

`docker-compose run --rm app vendor/phpunit/phpunit/phpunit`

# Shell

`docker-compose run --rm app bash`
