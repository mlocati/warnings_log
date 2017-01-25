# WarningsLog

WarningsLog is a [concrete5](https://www.concrete5.org) package that collects PHP warnings ([see a preview](https://www.youtube.com/v/QpoIrOfzQEA?vq=hd720)).


### Features

- Logs warnings even before the core database connection is initialized
- Logs warnings even before concrete5 is installed
- Group warnings by location, counting number of hits, first/last occurrence
- Hide/show specific warnings
- Sort warnings by location, error type, ...
- Fast: everything is done via ajax calls


### Requirements

- concrete5 v8.0 beta3 or later
- directory `/application/src` must be writable
- `pdo_sqlite` PHP extension must be installed and enabled


### Installation

- `git clone https://github.com/mlocati/warnings_log.git <path-to-concrete5>/packages/warnings_log`
- Install WarningsLog via the concrete5 dashboard or via the `concrete/bin/concrete5 c5:package-install warnings_log` CLI command


### Log warnings even before concrete5 is installed

In order to log warnings that may occur during the installation of concrete5, you can execute this CLI command:

```
concrete/bin/concrete5 c5:config set \
   app.providers.core_whoops Application\Concrete\Error\Provider\WhoopsServiceProvider 
```
