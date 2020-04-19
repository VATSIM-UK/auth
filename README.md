# VATSIM UK Auth

Laravel based OAuth 2 server for service-wide SSO and authentication.

## Installation
1. It is recommended you use a container for development / hosting of the application. For Docker, [Laradock](https://laradock.io/) ships with all the required components to run the application. For Vagrant, Laravel's Homestead works well.
2. Run the following commands in the root of the project

For Development Environments:
```php
// Create environment file
    $ cp .env.example .env 

// Install Composer dependencies
    $ composer install

// Install frontend dependencies. (If on Windows VM, add --no-bin-links to command)
    $ yarn

// Setup Laravel
    $ php artisan key:generate
    $ php artisan migrate -vvv -n
    $ php artisan db:seed
    $ php artisan passport:keys
    $ php artisan passport:client --client --name="VATSIM UK ClientCredentials Client"

// Compile Assets
    $ yarn run dev
```

For Production Environments:
```php
// Create environment file
    $ cp .env.example .env 

// Install Composer dependencies
    $ composer install --no-dev --optimize-autoloader

// Install frontend dependencies. (If on Windows VM, add --no-bin-links to command)
    $ yarn --no-dev

// Setup Laravel
    $ php artisan key:generate
    $ php artisan migrate -vvv -n
    $ php artisan db:seed
    $ php artisan passport:keys
    $ php artisan passport:client --client --name="VATSIM UK ClientCredentials Client"

// Compile Assets
    $ yarn run prod
```
## Development

### Tips and Tricks
* **API Schema Caching** - Lighthouse, the GraphQL API we use in Auth, will by default cache the graphql schema. To disable this, add `LIGHTHOUSE_CACHE_ENABLE=false` into your `.env`.

### Useful Commands
* `$ php artisan user:super (id)` - Give a user all permissions
* `$ php artisan token:generate (id)` - Generate API token for user

## Testing

This project has 3 test suites:
* First, Laravel's PHPUnit test suite for Unit testing the PHP code in the application
* Secondly, Laravel's Dusk test suite for front-end integration and feature testing
* Finally, a Mocha test suite to unit test JS components and classes

### Running PHPUnit tests (with code coverage):

`$ phpunit (--coverage-html build)` () = Optional for code coverage

### Running Laravel Dusk Tests
For Dusk, Google Chrome must be installed on your testing machine. For Laradock, this should already be enabled in the env file by default. For Homestead it is as simple as enabling it in the [.yaml file](https://laravel.com/docs/6.x/homestead#installing-optional-features), and re-creating the box.

Then run the suite with `$ php artisan dusk`.

### Running Mocha Tests
`$ yarn run test`
