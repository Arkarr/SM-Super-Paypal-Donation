<?php
	ini_set('error_reporting', E_ALL); // or error_reporting(E_ALL);
	ini_set('display_errors', '1');
	ini_set('display_startup_errors', '1');
	
	require __DIR__ . '/vendor/autoload.php';
	
	if(!isset($_GET["package-id"]) || !isset($_GET["steamid"]))
	{ 
		echo "<script>window.close();</script>";
		header('Location: ./index.php');
		exit;
	}

	$id = $_GET["package-id"];

	$configs = parse_ini_file('./configs/config.ini', true, INI_SCANNER_RAW);
	
	if(!isset($configs["package ".$id]))
	{ 
		echo "<script>window.close();</script>";
		header('Location: ./index.php');
		exit;
	}
	
	$package = $configs["package ".$id];

	use PayPalCheckoutSdk\Core\PayPalHttpClient;
	use PayPalCheckoutSdk\Core\ProductionEnvironment;
	
	// Creating an environment
	$clientId = $configs["paypal"]["client_id"];
	$clientSecret = $configs["paypal"]["client_secret"];

	$environment = new ProductionEnvironment($clientId, $clientSecret);
	$client = new PayPalHttpClient($environment);

	// Construct a request object and set desired parameters
	// Here, OrdersCreateRequest() creates a POST request to /v2/checkout/orders
	use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
	$request = new OrdersCreateRequest();
	$request->prefer('return=representation');
	
	$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
	$domainName = $_SERVER['HTTP_HOST'].pathinfo($_SERVER['PHP_SELF'], PATHINFO_DIRNAME);
	
	$url = $protocol.$domainName.'/read_payment.php';
	
	$request->body =[
					 "intent" => "CAPTURE",
					 "purchase_units" => [[
						 "reference_id" => $_GET["steamid"]."|".$id,
						 "amount" =>
						 [
							 "value" => $package["price"],
							 "currency_code" => "USD",
							 "description" => $package["description"]
						 ]
					 ]],
					 "application_context" =>
					 [
							"cancel_url" => $url,
							"return_url" => $url,
							"brand_name" => $configs["paypal"]["brand_name"]." - ".$package["name"],
							'shipping_preference' => 'NO_SHIPPING'
					 ],
					 "item" =>
					 [
						"name" => $package["name"],
						"unit_amount" => $package["price"],
						"quantity" => 1,
						"description" => $package["description"]					
					 ]
					];
	try
	{
		// Call API with your client and get a response for your call
		$response = $client->execute($request);
		
		// If call returns body in response, you can get the deserialized version from the result attribute of the response
		// echo "<pre>";
		// print_r($response);
		// echo "</pre>";
		
		if($response->statusCode != 201)
		{
			echo "<script>window.close();</script>";
			header('Location: ./index.php');
			exit;
		}

		$db = $configs["database"];
	
		// Create connection
		$conn = new mysqli($db["server"], $db["user"], $db["password"], $db["name"]);
		
		// Check connection
		if($conn->connect_error)
		{
		  die("Connection failed: " . $conn->connect_error);
		}

		$sql = "INSERT INTO payments (paypal_id, steamid, status, package_id, sourcemod_group) VALUES ('".$response->result->id."', '".$_GET["steamid"]."', '".$response->result->status."', '".$_GET["package-id"]."', '".$package["sourcemod_group"]."')";

		$conn->query($sql);
		/*if ($conn->query($sql) === TRUE){
		  echo "Record updated successfully";
		} else {
		  echo "Error updating record: " . $conn->error;
		}*/
		$conn->close();
		
		echo $response->result->links[1]->href;
	}
	catch (HttpException $ex)
	{
		echo $ex->statusCode;
		print_r($ex->getMessage());
	}
?>