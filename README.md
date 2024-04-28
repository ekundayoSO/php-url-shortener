Long url shortener app using PHP

Steps in app directory:
1. Add .env file
2. Add your UNELMA_ACCESS_TOKEN to .env
3. Add .env to .gitignore file. If doesn't exist, create one yourself.
4. Add .env.example
   
Steps in php script:
In your php script after api url add:
1. require_once dirname(__FILE__) . "/vendor/autoload.php";
2. $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
3. $dotenv->load();
4. $accessToken = $_ENV['UNELMA_ACCESS_TOKEN'];

Step in terminal:
1. cd to php script directory
2. run -> composer vlucas/phpdotenv

. Register and get your access token here:
1. https://unelmacloud.com/register

Happy coding!!!
