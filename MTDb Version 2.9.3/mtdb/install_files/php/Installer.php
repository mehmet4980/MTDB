<?php

class Installer {

    /**
	 * PHP Extensions and their expected state
	 * (enabled, disabled) in order for this 
	 * app to work properly.
	 * 
	 * @var array
	 */
	private $extensions = array(
		array('name' => 'fileinfo', 'type' => 'extension', 'expected' => true),
		array('name' => 'mbstring', 'type' => 'extension', 'expected' => true),
		array('name' => 'pdo', 'type' => 'extension', 'expected' => true),
		array('name' => 'pdo_mysql', 'type' => 'extension', 'expected' => true),
		array('name' => 'gd', 'type' => 'extension', 'expected' => true),
		array('name' => 'Mcrypt', 'type' => 'extension', 'expected' => true),
		array('name' => 'mysql_real_escape_string', 'type' => 'extension', 'expected' => false),
		array('name' => 'curl', 'type' => 'extension', 'expected' => true),
		array('name' => 'putenv', 'type' => 'function', 'expected' => true),
		array('name' => 'getenv', 'type' => 'function', 'expected' => true),
	);

	/**
	 * Directories that need to be writable.
	 * 
	 * @var array
	 */
	private $dirs = array('/app/config', '/app/storage/logs', '/app/storage/cache', '/app/storage/views', '/app/storage/sessions', '/app/storage/meta',
        '/assets/uploads', '/assets/uploads/images', '/assets/uploads/avatars', '/avatars', '/avatars/bgs', '/imdb/bgs', '/imdb/cast', '/imdb/stills', '/imdb/episodes', '/imdb/posters');

	/**
	 * Holds the compatability check results.
	 * 
	 * @var array
	 */
	private $compatResults = array('problem' => false);

    public function __construct()
    {
        if (strpos($_SERVER['REQUEST_URI'], 'htaccess-test') > -1) {
            echo 'success'; exit;
        }

        $post = json_decode(file_get_contents('php://input'), true);
        $data = isset($post['data']) ? $post['data'] : array();
        
        if ($post && array_key_exists('handler', $post)) {
        	set_error_handler(function($severity, $message) {
        		echo json_encode(array('status' => 'error', 'message'=> $message));
        		exit;
        	});

        	try {
        		$this->{$post['handler']}($data);
        		restore_error_handler();
        	} catch (Exception $e) {
        		echo json_encode(array('status' => 'error', 'message'=> $e->getMessage()));
        		exit;
        	}
        }
    }

	/**
	 * Check for any issues with the server.
	 * 
	 * @return JSON
	 */
	public function checkForIssues()
	{
		$this->compatResults['extensions'] = $this->checkExtensions();
		$this->compatResults['folders']    = $this->checkFolders();
		$this->compatResults['phpVersion'] = $this->checkPhpVersion();

		return json_encode($this->compatResults);
	}

	/**
	 * Check if we've got required php version.
	 * 
	 * @return integer
	 */
	public function checkPhpVersion()
	{
		return version_compare(PHP_VERSION, '5.4.0');
	}

	/**
	 * Check if required folders are writable.
	 * 
	 * @return array
	 */
	public function checkFolders()
	{
		$checked = array();

		foreach ($this->dirs as $dir)
		{
            $path = BASE_PATH.$dir;

		 	$writable = is_writable($path);

		 	$checked[] = array('path' => realpath($path), 'writable' => $writable);

		 	if ( ! $this->compatResults['problem']) {
		 		$this->compatResults['problem'] = $writable ? false : true;
		 	}		 	
		}
		
		return $checked;
	}

	/**
	 * Check for any issues with php extensions.
	 * 
	 * @return array
	 */
	private function checkExtensions()
	{
		$problem = false;

		foreach ($this->extensions as $k => &$ext)
		{
			if ($ext['type'] === 'function') {
                $loaded = function_exists($ext['name']);
            } else {
                $loaded = extension_loaded($ext['name']);
            }

			//make notice if any extensions status
			//doesn't match what we need
			if ($loaded !== $ext['expected'])
			{
				$problem = true;
			}

			$ext['actual'] = $loaded;
		}

		$this->compatResults['problem'] = $problem;

		return $this->extensions;
	}

	/**
	 * Store admin account and basic details in db.
	 * 
	 * @param  array  $input
	 * @return void
	 */
	public function createAdmin($input)
	{
		$this->validateAdminCredentials($input);

        $this->bootFramework();

        //create admin account
        $input['activated'] = 1;
        $input['permissions'] = array('superuser' => 1);

        try {
            Sentry::createUser(array_except($input, 'password_confirmation'));
        } catch (Exception $e) {
            echo json_encode(array('status' => 'error', 'message' => $e->getMessage())); exit;
        }

        echo json_encode(array('status' => 'success')); exit;
	}

	/**
	 * Insert db credentials if needed, create schema and seed the database.
	 * 
	 * @param  array  $input
	 * @return array
	 */
	public function createDb($input)
	{
        if ($message = $this->validateDbCredentials($input)) {
            echo json_encode(array('status' => 'error', 'message' => $message)); exit;
        }

        $this->insertDBCredentials($input);

        $this->bootFramework();

        $this->prepareDatabaseForMigration($input);

        //$this->generateAppKey();
       
        try {
            Artisan::call('migrate:install');
        } catch (Exception $e) {}

        Artisan::call('migrate');

        try {
            Artisan::call('db:seed');
        } catch (Exception $e) {}

        $this->putAppInProductionEnv();

        echo json_encode(array('status' => 'success')); exit;
	}

    private function validateAdminCredentials($input)
    {
        if ( ! isset($input['username'])) { echo json_encode(array('status' => 'error', 'message' => 'Please specify the administrator username.')); exit; }
        if ( ! isset($input['email'])) { echo json_encode(array('status' => 'error', 'message' => 'Please specify the administrator email address.')); exit; }
        if ( ! isset($input['password'])) { echo json_encode(array('status' => 'error', 'message' => 'Please specify the administrator password.')); exit; }
        if ( ! isset($input['password_confirmation'])) { echo json_encode(array('status' => 'error', 'message' => 'Please confirm the administrator password.')); exit; }
        if ( ! filter_var($input['email'], FILTER_VALIDATE_EMAIL)) { echo json_encode(array('status' => 'error', 'message' => 'Please enter a valid emails address.')); exit; }
        if (strlen($input['password']) < 4) { echo json_encode(array('status' => 'error', 'message' => 'Password must be at least 4 characters length')); exit; }
        if (strlen($input['username']) < 3) { echo json_encode(array('status' => 'error', 'message' => 'Username must be at least 4 characters length')); exit; }
        if (strcmp($input['password'], $input['password_confirmation'])) { echo json_encode(array('status' => 'error', 'message' => 'Specified password does not match the confirmed password')); exit; }
    }

    private function validateDbCredentials($input)
    {
        $credentials = array_merge(array(
            'host'     => null,
            'database' => null,
            'username' => null,
            'password' => null
        ), $input);

        $db =  'mysql:host='.$credentials['host'].';dbname='.$credentials['database'];

        try {
            $db = new PDO($db, $credentials['username'], $credentials['password'], array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
        } catch(PDOException $e) {
            return $e->getMessage();
        }
    }

    /**
     * Prepare for migration by putting new db credentials
     * into already loaded config and .env file
     *
     * @param $input
     */
    private function prepareDatabaseForMigration($input)
    {
        //load our new env variables and make sure environment is
        //local for migration/seeding as otherwise it will error out
        $dotenv = new Dotenv\Dotenv(BASE_PATH.'/app/config', 'env.example');
        $dotenv->load();
        App::detectEnvironment(function(){ return 'local'; });

        //get default database connection in case user is not using mysql
        $default = Config::get('database.default');

        if (empty($input)) {
            $input = array(
                'host'     => getenv('DB_HOST'),
                'database' => getenv('DB_DATABASE'),
                'username' => getenv('DB_USERNAME'),
                'password' => getenv('DB_PASSWORD'),
                'prefix'   => getenv('DB_PREFIX'),
            );
        }

        //set new database credentials into config so
        //existing database connection gets updated with them
        foreach($input as $key => $value) {
            if ( ! $value) $value = '';
            Config::set("database.connections.$default.$key", $value);
        }
    }

	/**
	 * Insert user supplied db credentials into .env file.
	 * 
	 * @param  array   $input
	 * @return void
	 */
	private function insertDBCredentials(array $input)
	{
        $content = file_get_contents('app/config/env.example');

        foreach ($input as $key => $value) {
            if ( ! $value) $value = '';
            $content = preg_replace("/(.*?DB_$key=)(.*?)\\n/msi", '${1}'.$value."\n", $content);
        }

		//put new credentials in a .env file
        file_put_contents('app/config/env.example', $content);
	}

    /**
     * Generate new app key and put it into .env file.
     */
    private function generateAppKey()
    {
        //if user has created/modified .env file manually we can bail
        if ( ! Session::get('needToHandleEnvFile', true)) return;
        Session::forget('needToHandleEnvFile');

        $content = file_get_contents('app/config/.env');

        //set app key while we're editing .env file
        $key = str_random(32);
        $content = preg_replace("/(.*?APP_KEY=).*?(.+?)\\n/msi", '${1}'.$key."\n", $content);

        file_put_contents('app/config/.env', $content);
    }

    /**
     * Change app env to production and set debug to false in .env file.
     */
    private function putAppInProductionEnv()
    {
        $content = file_get_contents('app/config/env.example');

        //mark as installed
        $content = preg_replace("/(.*?INSTALLED=).*?(.+?)\\n/msi", '${1}1'."\n", $content);

        //set env to production
        $content = preg_replace("/(.*?APP_ENV=).*?(.+?)\\n/msi", '${1}production'."\n", $content);

        //set debug to false
        $content = preg_replace("/(.*?APP_DEBUG=).*?(.+?)\\n/msi", '${1}false'."\n", $content);

        //set base url for env
        $content = preg_replace("/(.*?BASE_URL=).*?(.+?)\\n/msi", '${1}'.url()."\n", $content);

        file_put_contents('app/config/env.example', $content);
    }

    private function bootFramework()
    {
        require BASE_PATH.'/bootstrap/autoload.php';

        $dotenv = new Dotenv\Dotenv(BASE_PATH.'/app/config', 'env.example');
        $dotenv->load();

        $app = require_once BASE_PATH.'/bootstrap/start.php';
        
        $app->boot();
    }

    public function finalizeInstallation()
    {
        $this->bootFramework();

        $this->putAppInProductionEnv();

        rename('app/config/env.example', 'app/config/.env');

        $this->handleHtaccessFile();

        try {
            $this->deleteInstallationFiles();
        } catch (Exception $e) {}

        echo json_encode(array('status' => 'success', 'message' => 'success')); exit;
    }

    private function deleteInstallationFiles()
    {
        $dir = BASE_PATH.'/install_files';

        $it = new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS);
        $files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);

        foreach($files as $file) {
            if ($file->isDir()){
                rmdir($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }

        @rmdir($dir);

        @file_put_contents(BASE_PATH.'/index.php', str_replace("if (file_exists(__DIR__.'/install_files')) { require_once __DIR__.'/install_files/install.php'; exit; }", '', file_get_contents(BASE_PATH.'/index.php')));
    }

    private function testAndFixHtaccessFile()
    {
        $response = $this->htaccessTest();

        if ($response === 404 || $response === 500) {
            $this->htaccessAddSlash();

            $response = $this->htaccessTest();

            if ($response === 404 || $response === 500) {
                $this->htaccessRemoveSlash();

                $this->htaccessDisableMultiViews();

                $response = $this->htaccessTest();

                if ($response === 404 || $response === 500) {
                    $this->htaccessEnableMultiViews();
                    return (array('status' => 'error', 'message' => 'htacces error'));          
                }
            
            }
        }

        return (array('status' => 'success', 'message' => 'success'));  
    }

    private function htaccessDisableMultiViews()
    {
        $path = BASE_PATH.'/.htaccess';
        file_put_contents($path, str_replace('Options -MultiViews', '', file_get_contents($path)));
    }

    private function htaccessEnableMultiViews()
    {
        $path = BASE_PATH.'/.htaccess';
        $contents = file_get_contents($path);

        if (strrpos($contents, 'Options -MultiViews') === false) {
            file_put_contents($path, str_replace('<IfModule mod_negotiation.c>', "<IfModule mod_negotiation.c>\n\t\tOptions -MultiViews", $contents));
        }
    }

    private function htaccessAddSlash()
    {
        $path = BASE_PATH.'/.htaccess';
        file_put_contents($path, str_replace('RewriteRule ^ index.php [L]', 'RewriteRule ^ /index.php [L]', file_get_contents($path)));
    }

    private function htaccessRemoveSlash()
    {
        $path = BASE_PATH.'/.htaccess';
        file_put_contents($path, str_replace('RewriteRule ^ /index.php [L]', 'RewriteRule ^ index.php [L]', file_get_contents($path)));
    }

    private function htaccessTest()
    {
        $url = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]htaccess-test";

        $handle = curl_init();
        curl_setopt($handle, CURLOPT_URL, $url);
        curl_setopt($handle, CURLOPT_HEADER, true);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($handle, CURLOPT_TIMEOUT, 6);

        $response = curl_exec($handle);

        curl_close($handle);

        if (strpos($response, '404 Not Found') > -1) {
            return 404;
        }

        if (strpos($response, '500 Internal Server Error') > -1) {
            return 500;
        }

        return 'success';
    }

    public function handleHtaccessFile()
    {
        $path = BASE_PATH.'/.htaccess';

        if ( ! file_exists($path)) {
            $contents =
'<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews
    </IfModule>

    RewriteEngine On

    # Redirect Trailing Slashes...
            RewriteRule ^(.*)/$ /$1 [L,R=301]

    # Handle Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>';

        file_put_contents($path, $contents);

        }
    }
}