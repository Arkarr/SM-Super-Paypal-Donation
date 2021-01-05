<?php
	ini_set('error_reporting', E_ALL); // or error_reporting(E_ALL);
	ini_set('display_errors', '1');
	ini_set('display_startup_errors', '1');
	
	require __DIR__ . '/vendor/autoload.php';
	
	use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;
	use PayPalCheckoutSdk\Core\PayPalHttpClient;
	use PayPalCheckoutSdk\Core\SandboxEnvironment;
	
	$configs = parse_ini_file('./configs/config.ini', true, INI_SCANNER_RAW);
	
	if(!isset($_GET["token"]))
	{ 
		echo "<script>window.close();</script>";
		header('Location: ./index.php');
		exit;
	}
	
	// Creating an environment
	$clientId = $configs["paypal"]["client_id"];
	$clientSecret = $configs["paypal"]["client_secret"];

	$environment = new SandboxEnvironment($clientId, $clientSecret);
	$client = new PayPalHttpClient($environment);
	
	// Here, OrdersCaptureRequest() creates a POST request to /v2/checkout/orders
	// $response->result->id gives the orderId of the order created above
	$request = new OrdersCaptureRequest($_GET["token"]);
	$request->prefer('return=representation');
	
	$failed = false;
	
	$id = "<null>";
	$status	= "<null>";
	$steamid = "<null>";
	$packageid = "<null>";
	$payer_email = "<null>";
	
	try
	{
		// Call API with your client and get a response for your call
		$response = $client->execute($request);
		
		// If call returns body in response, you can get the deserialized version from the result attribute of the response
		//echo "<pre>";
		//print_r($response);
		//echo "</pre>";
		
		$response = $response->result;
		
		$id = $response->id;
		$status	= $response->status;
		$tmp = explode("|", $response->purchase_units[0]->reference_id, 2);
		$steamid = $tmp[0];
		$packageid = $tmp[1];
		$payer_email = $response->payer->email_address;
		
		//TODO: Send email with confirmation and save record in database...
	}
	catch (PayPalHttp\HttpException $ex)
	{
		//echo $ex->statusCode;
		//echo "<pre>";
		//print_r($ex->getMessage());
		//echo "</pre>";
		//TODO: Handle errors
		$failed = true;
	}
	
	if($failed === false)
	{
		$db = $configs["database"];
		
		// Create connection
		$conn = new mysqli($db["server"], $db["user"], $db["password"], $db["name"]);
		
		// Check connection
		if($conn->connect_error)
		{
		  die("Connection failed: " . $conn->connect_error);
		}

		$sql = "UPDATE payments SET status='".$status."' WHERE paypal_id='".$id."'";

		$conn->query($sql);
		/*if ($conn->query($sql) === TRUE){
		  echo "Record updated successfully";
		} else {
		  echo "Error updating record: " . $conn->error;
		}*/
		$conn->close();
	}
?>

<!DOCTYPE html>
<html lang="en">
<head>

  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="">
  <meta name="author" content="">

  <title><?php echo $configs["site"]["shop_name"]; ?></title>

  <!-- Bootstrap core CSS -->
  <link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">

  <!-- Custom styles for this template -->
  <link href="css/shop-readpage.css?v=1.2" rel="stylesheet">

</head>
<body>
<div class="jumbotron text-center">
	<div class="container">
		<h1 class="display-3">Thank You!</h1>
		<p class="lead"><strong>Please check your email (<?php echo $payer_email ?>)</strong> for more information about your order.</p>
		
		<p>
		Order details<br/> 
		<table>
			<tr>
				<th>Package name</th>
				<th>Order</th>
				<th>Steam ID</th>
			</tr>
			<tr>
			<?php
				$package = $configs["package ".$packageid];
				echo "<td>".$package["name"]." -  $".$package["price"]."</td>";
				echo "<td>".$status."</td>";
				echo "<td>".$steamid."</td>";
			?> 
			</tr>
		</table>
		</p>
		<hr>
		<p>
			Having trouble? <a target="_blank" href="http://steamcommunity.com/profiles/<?php echo $configs["steam"]["your_steamid_64"] ?>">Contact us</a>
		</p>
		<hr>
		<p id="auto-close">
			This window will close itself in 20 seconds...
		</p>
	</div>  
</div> 

<!-- Bootstrap core JavaScript -->
<script src="vendor/jquery/jquery.min.js"></script>
<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

<script>
var counter = 20;
var updateText = function()
{
	counter--;
	$("#auto-close").text("This window will close itself in "+counter+" seconds...");
	setTimeout(updateText, 1000);
	
	if(counter == 0)
	{
		window.close();
	}
}
setTimeout(updateText, 1000);
</script>
</body>
</html>