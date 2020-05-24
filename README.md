# Food Scanner API, Backend

*The FullREST API for the [Lifyzer](https://lifyzer.com)'s food scanner apps.*


## Server Requirements

* 🐘 [PHP 7.1](http://php.net/releases/7_1_0.php) or higher
* 💾 MySQL/MariaDB 5.5.3 or higher
* 🎹 [Composer](https://getcomposer.org)


## Setup

1. Run `composer install` to install the project's dependencies.
2. Create a database and import `/_development/SQL/database.sql` file.
3. ⚠️ **Don't forget** to rename `.env.dist` to `.env` and edit the details in there such as the database, SMTP credentials and the private encryption key.


## Usage

The index file to be called for requesting the API is `FoodScanAppService.php`. Make a request like `/FoodScanAppService.php?Service=...`


## About

[Pierre-Henry Soria](https://pierrehenry.be), a super-passionate software engineer. Love learning and discovering new exciting things all the time! 🚀
