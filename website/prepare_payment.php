<?php 
	if(!isset($steam->steamid))
	{ 
		echo "<script>window.close();</script>";
		header('Location: ./index.php');
		exit;
	}
?>

<html>

	<head>
		<style>
		.centered
		{
		  position: fixed;
		  top: 50%;
		  left: 50%;
		  /* bring your own prefixes */
		  transform: translate(-50%, -50%);
		}
		</style>
		<script src="vendor/jquery/jquery.min.js"></script>
	</head>
	
	<body>
		<img class="centered" src="./images/loading.gif">
	
		<?php
			ini_set('error_reporting', E_ALL); // or error_reporting(E_ALL);
			ini_set('display_errors', '1');
			ini_set('display_startup_errors', '1');
			
			require __DIR__ . '/vendor/autoload.php';

			$configs = parse_ini_file('./configs/config.ini', true, INI_SCANNER_RAW);
			
			if(!isset($_GET["package-id"]))
			{ 
				echo "<script>window.close();</script>";
				header('Location: ./index.php');
				exit;
			}
			
			$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
			$domainName = $_SERVER['HTTP_HOST'].pathinfo($_SERVER['PHP_SELF'], PATHINFO_DIRNAME);
			
			$url = $protocol.$domainName.'/index.php';
			
			$config = array(
				'apikey' => $configs["steam"]["api_key"], // Steam API KEY
				'domainname' =>  $configs["steam"]["domain_name"], // Displayed domain in the login-screen
				'loginpage' => $url, // Returns to last page if not set
				'logoutpage' => $url
			);
			
			$steam = new Vikas5914\SteamAuth($config);
			
			echo '<script>$.get("create_payment.php?package-id='.$_GET["package-id"].'&steamid='.$steam->steamid.'", function(data, status){ window.location.href = data;});</script>';
		?>
	</body>
	
</html>
