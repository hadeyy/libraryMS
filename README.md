# Library Management System

System used to manage library inventory, book reservations and user activity.

## Getting Started

These instructions will get you a copy of the project up and running on your local machine for development and testing purposes. See deployment for notes on how to deploy the project on a live system.

### Prerequisites

To install this project on your local machine you will need:

**PHP**
* version 7.1.3 or higher
* PDO-SQLite extension enabled
* find documentation and downloads on the official site - http://www.php.net/

**Git**
* find documentation and downloads on the official site - https://git-scm.com/

**Composer**
* find documentation and downloads on the official site - https://getcomposer.org/


### Installing

Clone the project on your local machine
```
git clone https://github.com/hadeyy/libraryMS.git
```

Go to cloned project folder
```
cd libraryMS
```

Install necessary dependencies
```
composer install
```



## Running the tests

Run all tests

```
php bin/phpunit
```

## Deployment

Run a local server that will easily let you open the project in a browser
```
php bin/console s:r
```

After running this command in the terminal you will see the site and port to use to open the project in a browser.

## Built With

* [Symfony](https://symfony.com/) - The web framework used
* [Doctrine](https://www.doctrine-project.org/) - Database Management
* [Twig](https://twig.symfony.com/) - Template engine

## Authors

* **Evita Sivakova** - [hadeyy](https://github.com/hadeyy)

