<?php
	
	ini_set('error_reporting', E_ALL); // or error_reporting(E_ALL);
	ini_set('display_errors', '1');
	ini_set('display_startup_errors', '1');
	
	require __DIR__ . '/vendor/autoload.php';

	$configs = parse_ini_file('./configs/config.ini', true, INI_SCANNER_RAW);

	$nbrPackages = array();
	preg_match_all('/package ([0-9]+)/', file_get_contents('./configs/config.ini'), $nbrPackages);
	$nbrPackages = count($nbrPackages[0]);
	
	$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
	$domainName = $_SERVER['HTTP_HOST'].pathinfo($_SERVER['PHP_SELF'], PATHINFO_DIRNAME);;
	
	$url = $protocol.$domainName.'/index.php';
	
	$config = array(
		'apikey' => $configs["steam"]["api_key"], // Steam API KEY
		'domainname' =>  $configs["steam"]["domain_name"], // Displayed domain in the login-screen
		'loginpage' => $url, // Returns to last page if not set
		'logoutpage' => $url
	);

	$steam = new Vikas5914\SteamAuth($config);
	
	if(isset($_GET["disconnect"]))
		$steam->logout();

	$db = $configs["database"];
		
	// Create connection
	$conn = new mysqli($db["server"], $db["user"], $db["password"], $db["name"]);
	
	// Check connection
	if($conn->connect_error)
	{
	  die("Connection failed: " . $conn->connect_error);
	}

	$sql = "CREATE TABLE IF NOT EXISTS `payments` ( `id` INT NOT NULL AUTO_INCREMENT ,  `paypal_id` VARCHAR(200) NOT NULL ,  `steamid` VARCHAR(100) NOT NULL ,  `status` VARCHAR(45) NOT NULL ,  `package_id` INT(10) NOT NULL , `sourcemod_group` VARCHAR(200) NOT NULL , PRIMARY KEY  (`id`)) ENGINE = InnoDB;";

	$conn->query($sql);
	$conn->close();

	
?>

<!DOCTYPE html>
<html lang="en">

<head>

  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">/
  <meta name="description" content="">
  <meta name="author" content="">
  
  <script>
	function popup(pageURL, title, w, h)
	{
		var left = (screen.width / 2)  - (w / 2);
		var top  = (screen.height / 2) - (h / 2);
		var targetWin = window.open(pageURL, title, 'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=no, copyhistory=no, width='+w+', height='+h+', top='+top+', left='+left);
	}
  </script>

  <title><?php echo $configs["site"]["shop_name"]; ?></title>

  <!-- Bootstrap core CSS -->
  <link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">

  <!-- Custom styles for this template -->
  <link href="css/shop-homepage.css?v=1.2" rel="stylesheet">

</head>

<body>

  <!-- Navigation -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
    <div class="container">
      <a class="navbar-brand" href="#"><?php echo $configs["site"]["shop_name"]; ?></a>
      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarResponsive">
        <ul class="navbar-nav ml-auto">
          <li class="nav-item active">
            <a class="nav-link" href="#">Home
              <span class="sr-only">(current)</span>
            </a>
          </li>
          <li class="nav-item">
            <a >
			<?php
				
				if ($steam->loggedIn())
					echo "<a href='./index.php?disconnect=true' class='nav-link'>Hello " . $steam->personaname . "!</a>";
				else
					echo "<a href='".$steam->loginUrl()."' class='nav-link'>Login</a>";
			?>
			</a>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Page Content -->
  <div class="container">

    <div class="row">

      <div class="col-lg-3">

        <img class="my-4" width="100%" src="./images/srv_logo.png"/>
        <div class="list-group">
          <div class="list-group-item"><b>Packages names</b></div>
          <?php
			for ($i = 0; $i < $nbrPackages; $i++)
				echo "<div class=\"list-group-item\">". $configs["package " . ($i+1)]["name"] ."</div>";
          ?>
        </div>

      </div>
      <!-- /.col-lg-3 -->

      <div class="col-lg-9">

        <div id="carouselExampleIndicators" class="carousel slide my-4" data-ride="carousel">
          <ol class="carousel-indicators">
			<?php
				for ($i = 0; $i < $nbrPackages; $i++)
					echo '<li data-target="#carouselExampleIndicators" data-slide-to="'.$i.'" '.($i == 0 ? 'class="active"' : '').'></li>';
			?>
          </ol>
          <div class="carousel-inner" role="listbox">
			<?php
				for ($i = 0; $i < $nbrPackages; $i++)
				{
					echo '<div class="carousel-item '.($i == 0 ? "active" : "").'">';					
					echo '<img class="d-block img-fluid" src="'.$configs["package " . ($i+1)]["img"].'" alt=" Slide n°'.($i+1).'">';
					echo '</div>';
				}
			?>
          </div>
		  
          <a class="carousel-control-prev" href="#carouselExampleIndicators" role="button" data-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="sr-only">Previous</span>
          </a>
          <a class="carousel-control-next" href="#carouselExampleIndicators" role="button" data-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="sr-only">Next</span>
          </a>
        </div>

        <div class="row">

			<?php
			
				for ($i = 0; $i < $nbrPackages; $i++)
				{
					echo "<div class=\"col-lg-4 col-md-6 mb-4\">\n";
					echo "	<div class=\"card h-100\">\n";
					echo "		<a href=\"#\"><img class=\"card-img-top\" src=\"".$configs["package " . ($i+1)]["img"]."\" alt=\"\"></a>\n";
					echo "		<div class=\"card-body\">\n";
					echo "			<h4 class=\"card-title\">\n";
					echo "				<a href=\"#\">".$configs["package " . ($i+1)]["name"]."</a>\n";
					echo "			</h4>\n";
					echo "			<h5>$".$configs["package " . ($i+1)]["price"]."</h5>\n";
					echo "			<p class=\"card-text\">".$configs["package " . ($i+1)]["description"]."</p>\n";
					echo "		</div>\n";
					echo "		<div class=\"card-footer\">\n";
					echo 		($steam->loggedIn() ?
								"<a class=\"btn btn-primary btn-sm\" role=\"button\" onclick=\"popup('./prepare_payment.php?package-id=".($i+1)."', 'test', 750, 880)\">Purchase for ".$steam->personaname."</a>" : "<a class=\"btn btn-primary btn-sm\" role=\"button\" href='".$steam->loginUrl()."' class='nav-link'>Login to steam to buy</a>")."\n";
					echo "		</div>\n";
					echo "	</div>\n";
					echo "</div>";

				}				
			?>

        </div>
        <!-- /.row -->

      </div>
      <!-- /.col-lg-9 -->

    </div>
    <!-- /.row -->

  </div>
  <!-- /.container -->

  <!-- Footer -->
  <footer class="py-5 bg-dark">
    <div class="container">
      <p class="m-0 text-center text-white">Copyright &copy; <?php echo $configs["site"]["shop_name"]; ?></p>
    </div>
    <!-- /.container -->
  </footer>

  <!-- Bootstrap core JavaScript -->
  <script src="vendor/jquery/jquery.min.js"></script>
  <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

</body>

</html>
