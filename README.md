## Project: Symfony 4, GraphQL, MySQL
# Prerequisite:
- PHP 8.1
- MySQL 8.0
- Apache/Nginx
- Symfony-cli
- Composer2
- Symfony 4.4

# Steps
- Change the database credentials in .env file
- Run: bin/console doctrine:database:create
- Run: bin/console doctrine:migration:migrate
- Run: bin/console bin/console doctrine:schema:create
- Run: symfony server:start / any command you are familiar with to start the server
- Go to postman, import Hotels.postman_collection.json file in the root folder
- Test the queries and mutation there
- unit test files are in tests folder in the root folder
- bin/phpunit <relative path of file name>