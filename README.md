Dastone HDV Symfony Application
========================

The "Symfony Demo Application" is a reference application created to show how
to develop applications following the [Symfony Best Practices][1].

You can also learn about these practices in [the official Symfony Book][5].

Requirements
------------

  * PHP 8.3.0 or higher;
  * PDO-SQLite PHP extension enabled;
  * and the [usual Symfony application requirements][2].

Installation
------------

There is a way of installing this project depending on your needs:

**This.** [Download Composer][6] and use the `composer` binary installed
on your computer to run these commands:

```bash
# ...or you can clone the code repository and install its dependencies
git clone https://github.com/hoepjhsha/ptpmhdv-nhom-7-symfony-final.git
cd ptpmhdv-nhom-7-symfony-final/
composer install
```

Usage
-----

There's no need to configure anything before running the application. There are
2 different ways of running this application depending on your needs:

**Option 1.** [Download Symfony CLI][4] and run this command:

```bash
cd ptpmhdv-nhom-7-symfony-final/
symfony serve
```

Then access the application in your browser at the given URL (<https://localhost:8000> by default).

**Option 2.** Use a web server like Nginx or Apache to run the application
(read the documentation about [configuring a web server for Symfony][3]).

On your local machine, you can run this command to use the built-in PHP web server:

```bash
cd ptpmhdv-nhom-7-symfony-final/
php -S localhost:8000 -t public/
```

Tests
-----

Execute this command to run tests:

```bash
cd ptpmhdv-nhom-7-symfony-final/
./bin/phpunit
```

[1]: https://symfony.com/doc/current/best_practices.html
[2]: https://symfony.com/doc/current/setup.html#technical-requirements
[3]: https://symfony.com/doc/current/setup/web_server_configuration.html
[4]: https://symfony.com/download
[5]: https://symfony.com/book
[6]: https://getcomposer.org/
