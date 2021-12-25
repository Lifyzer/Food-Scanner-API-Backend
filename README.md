# Food Scanner API, Backend

🍳 The YUMMIEST™ REST API for the Lifyzer Solution™ food scanner apps 😋 Thanks to the power of data🤖, it will return back to its client apps the right result we expect 🥥

## Server Requirements

* 🐘 [PHP 7.1](http://php.net/releases/7_1_0.php) or higher
* 💾 MySQL/MariaDB 5.5.3 or higher
* 🎹 [Composer](https://getcomposer.org)


## Setup

1. Run `composer install` to install the project's dependencies.
2. Create a database and import `/_development/SQL/database.sql` file.
3. ⚠️ **Don't forget** to rename `.env.dist` to `.env`. Edit the details in there such as the database, SMTP credentials, private encryption key and 3rd-party food API keys.


## Usage

The index file to be called for requesting the API is `FoodScanAppService.php`. Make a request such as `/FoodScanAppService.php?Service=...`


## About

[Pierre-Henry Soria](https://pierrehenry.be), a super-passionate software engineer. Love learning and discovering new exciting things all the time! 🚀
