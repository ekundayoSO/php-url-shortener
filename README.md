Long url shortener app using PHP

Steps in app directory:
1. Add .env file
2. Add your UNELMA_ACCESS_TOKEN to .env
3. Add .env to .gitignore file. If doesn't exist, create one yourself.
4. Add .env.example
   
Steps in php script:
In your php script after api url add:
  require_once dirname(__FILE__) . "/vendor/autoload.php";
  $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
  $dotenv->load();
  $accessToken = $_ENV['UNELMA_ACCESS_TOKEN'];

Step in terminal:
1. cd to php script directory
2. run -> composer vlucas/phpdotenv

Happy coding!!!
