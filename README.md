# Currency Exchange Web App

A Web application to view current currency Exchange rates and make transactions from one currency to another to be stored in a database.

## Set Up

 1. Install packages
 
composer install

symfony pecl install redis

2. Run docker containers

docker run --network myNetwork --name mariaDB1 -e MYSQL_ROOT_PASSWORD=root1234 -p -d 3306:3306 mariadb:latest

docker run --network myNetwork --name redis1 -p -d 3679:3679 redis:latest

3. Set Up Database
php bin/console doctrine:database:create

php bin/console make:migration

php bin/console doctrine:migrations:migrate

4. Start symfony server
symfony server:start


## Usage
once the containers and symfony server are start navigating to localhost:8000/exchange will take you to the homepage where you can see the latest currency exchange rates against the Euro.  The button to create a transaction will load a form where once submitted a new transaction will be created in the database.

Selecting the button to view transactions will then take the user to another page with a table of the transactions made from that user's IP address where one can edit the target currency of the transaction and thus updating the target ammount and exchange rate of that transaction aswell.

## License
[![CC BY 4.0][cc-by-shield]][cc-by]

This work is licensed under a
[Creative Commons Attribution 4.0 International License][cc-by].

[![CC BY 4.0][cc-by-image]][cc-by]
