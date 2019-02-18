# Zaparkujto.cz

## Cron

**CLI**
```php
php index.php <command>
```

**HTTP**
```
wget <url>/cron/<presenter>/<action>?_token=<token>
```

### Email notifications

Send email notification that reservations expired.

```php
php index.php app:email:notification
```

```php
wget <url>/cron/email/notification
```

### Cancel reserved reservations

```php
php index.php app:reservation:cancelReserved
```

```php
wget <url>/cron/reservation/cancelReserved
```

## Tests

### 1-time

```sh
$ tests/tester
```

### Watch

```sh
$ tests/tester-watching
```

### Coverage

```sh
$ tests/tester-coverage
```

## CodeSniffer

### Sniff

```sh
$ bin/codesniffer/phpcs <folder>
```

### Fix

```sh
$ bin/codesniffer/phpcbf <folder> --no-patch
```