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


## [Used APIs](https://github.com/Lifyzer/Food-Scanner-API-Backend/issues/4)

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
