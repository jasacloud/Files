<?PHP
	$return = array(
					"return"=>"error",
					"returnmessage"=>"QRCode not found!",
					"class_method"=> "ImageServer::getQRCode()"
				);
				header("Content-Type: application/json");
				echo json_encode($return);
	
?>