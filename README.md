# PHP CI/CD Laboratory Project

[![PHP CI Application](https://github.com/mariana-hrv/php-ci-cd-lab1/actions/workflows/php-ci.yml/badge.svg)](https://github.com/mariana-hrv/php-ci-cd-lab1/actions/workflows/php-ci.yml)

## Project Description

This project demonstrates Continuous Integration (CI)
for a PHP application using GitHub Actions.

It includes a simple calculator, automated unit tests,
code style validation, and GitHub Secrets integration.

## Features

- Addition of two numbers.
- Subtraction of two numbers.
- Automated unit testing with PHPUnit.
- Code style validation using Laravel Pint.
- Automatic CI checks using GitHub Actions.
- Secure handling of GitHub Secrets.

## Usage Examples

The Calculator class supports addition and subtraction.

### Addition

```php
$calculator = new Calculator();
$result = $calculator->add(2, 3);
// Expected result: 5
```

### Subtraction

```php
$calculator = new Calculator();
$result = $calculator->subtract(5, 2);
// Expected result: 3
```

## Technologies

- PHP 8.2
- Composer
- PHPUnit
- Laravel Pint
- Git and GitHub
- GitHub Actions

## Running Tests

Install project dependencies:

```bash
composer install
```

Run PHPUnit tests:

```bash
./vendor/bin/phpunit tests/Unit
```

Check code formatting:

```bash
./vendor/bin/pint --test
```

## CI Pipeline

The CI pipeline automatically runs when code is pushed
to the main branch or when a Pull Request targets main.

The pipeline performs the following steps:

1. Checkout repository.
2. Set up PHP 8.2.
3. Install Composer dependencies.
4. Check code formatting using Laravel Pint.
5. Run PHPUnit unit tests.
6. Demonstrate GitHub Secrets masking.

## Author

mariana-hrv
