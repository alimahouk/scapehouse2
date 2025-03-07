<?php
if($_SERVER["HTTP_HOST"] == "www.scapehouse.com"){
    header("HTTP/1.1 301 Moved Permanently");
    header("Location: http://scapehouse.com{$_SERVER["REQUEST_URI"]}");
    exit();    
}

$brb = false;

if (!$brb):
// Define path to application directory
defined('APPLICATION_PATH') || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

// Define application environment
defined('APPLICATION_ENV') || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(realpath(APPLICATION_PATH . '/../library') , get_include_path())));

/** Zend_Application */
require_once 'Zend/Application.php';

// Create application, bootstrap, and run
$application = new Zend_Application(APPLICATION_ENV, APPLICATION_PATH . '/configs/application.ini');
$application->bootstrap()->run();

else:
// Access forbidden:
header('HTTP/1.1 403 Forbidden');
?>

<!DOCTYPE html>
<html lang="en-US" id="scapehouse" class="403">
  	<head>
    	<title>Scapehouse</title>
    	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta http-equiv="Content-language" content="en" />
		<meta name="description" content="Homemade apps, for you." />
		<link rel="icon" type="image/png" href="/graphics/en/UI/favicon.png" />
		<style>
			body {
				background-color: #0073b9;
				color: #333;
				font: 14px 'lucida grande', tahoma, verdana, helvetica, arial, sans-serif;
				margin: 0;
			}
			
			#logo {
				display: block;
				margin: 10px auto;
			}
			
			h2 {
				color: #fff;
				font-size: 32px;
				text-align: center;
				text-shadow: 0 1px 1px #000;
			}
			
			p {
				background-color: #feffdf;
			    border: 1px solid #f5e082;
				margin: 10px auto;
				padding: 10px;
				text-align: center;
				text-shadow: 0 1px 1px #fff;
				-webkit-background-clip: padding-box;
				-webkit-border-radius: 5px;
				-webkit-box-shadow: 0 0 3px #000;
				width: 370px;
			}
		</style>
	</head>
	<body>
		<h1><img id="logo" src="/graphics/en/logos/scapehouse_withTransp_454px.png" alt="Scapehouse" width="454" height="109" /></h1>
		<h2>BRB.</h2>
		<p>Scapehouse is undergoing some regular maintenance.</p>
	</body>
</html>
<?php endif; ?>