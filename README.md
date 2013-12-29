#Openbadges Podcast

This is a Symfony app that spiders the archive.org site for media files tagged with 'openbadges' and serves up the
found files as a podcast RSS feed.

##Installation
* Checkout the Repo
* Install the vendor scripts by running `composer install`
* Copy `app/config/parameters.yml.dist` file to `app/config/parameters.yml` and adjust the values if necessary
* Run `app/console doctrine:migrations:migrate`
* Set up a cronjob to run `app/console colinfrei:openbadgespodcast:spider` regularly

[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/colinfrei/OpenBadgesPodcast/badges/quality-score.png?s=4c79d31c2b3f41cdc8cb3b18f4f1df537d860a76)](https://scrutinizer-ci.com/g/colinfrei/OpenBadgesPodcast/)
