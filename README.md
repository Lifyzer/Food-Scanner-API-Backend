# Food Scanner API, Backend

🍳 The YUMMIEST™ REST API for the Lifyzer Solution™ food scanner apps 😋 Thanks to the power of data 🤖, it will return back to its client apps the right result we expect 🥥

## Server Requirements

* 🐘 [PHP 7.4](https://www.php.net/releases/7_4_0.php) or higher
* 💾 MySQL/MariaDB 5.5.3 or higher
* 🎹 [Composer](https://getcomposer.org)


## Setup

1. Run `composer install` to install the project's dependencies.
2. Create a MySQL database and import `/_development/SQL/database.sql` file.
3. ⚠️ **Don't forget** to rename `.env.dist` to `.env`. Edit the details in there such as the database, SMTP credentials, private encryption key and 3rd-party food API keys.


## Usage

The index file to be called for requesting the API is `FoodScanAppService.php`. Make a request like `/FoodScanAppService.php?Service=...` to start using the food API.


## [3rd-party APIs used](https://github.com/Lifyzer/Food-Scanner-API-Backend/issues/4)

* Open Food Facts
* FoodData Central (FDC) - US market
* The Open Food Repo


## About

[Pierre-Henry Soria](https://pierrehenry.be). A super passionate software engineer. I love learning and discovering new, exciting things all the time! 🚀

☕️ Are you enjoying my work? **[Offer me a coffee](https://ko-fi.com/phenry)** and boost the software development at the same time! 💪

[![@phenrysay][twitter-image]](https://twitter.com/phenrysay "Follow Me on Twitter") [![pH-7][github-image]](https://github.com/pH-7 "Follow me GitHub @pH-7")


<!-- GitHub's Markdown reference links -->
[twitter-image]: https://img.shields.io/badge/Twitter-1DA1F2?style=for-the-badge&logo=twitter&logoColor=white
[github-image]: https://img.shields.io/badge/GitHub-100000?style=for-the-badge&logo=github&logoColor=white

## Offline validation and deployment limitations

Run `php tests/request-dispatch.php` to exercise the real entrypoint routing with
offline database/security/service doubles. This checks failed authentication,
token response handling, malformed JSON, and UTF-8 preservation without credentials.
Run `composer validate --no-check-publish` and `composer audit --locked` for the
dependency contract. Composer resolves against PHP 7.4; the tests also run on PHP 8.5.
The original project's licence metadata still needs clarification.

These checks do not validate a live database, mobile-client compatibility or
provider calls. The legacy token protocol still requires a dedicated review of
account ownership, token expiry, recovery endpoints and shared-secret handling
before this API is suitable for production deployment. Request routing fixes alone
do not establish complete authentication or authorisation.
