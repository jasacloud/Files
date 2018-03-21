<?PHP

use \QRCode;
/* GET:
	
*/
class ImageServer{
	var $default_host;
	var $width = NULL;
	var $height = NULL;
	var $imgname = NULL;
	var $imgratio = FALSE;
	var $imgextension = NULL;
	var $imgroot = "";
	var $baseurl = NULL;
	var $imgsource;
	public function __construct(){
		$this->default_host = (isset($_SERVER["HTTPS"])&&$_SERVER["HTTPS"]=="on"?"https":"http") . "://".$_SERVER["HTTP_HOST"];
		
		$this->imgroot= (defined(IMGROOT)) ? IMGROOT : "/img/img.upload/";
		new Logger($_SERVER['DOCUMENT_ROOT']."/log/ImageServer.class.log", $_SERVER['PHP_SELF'].":". __LINE__ ."  "."Image:__construct() loaded!");
	}
	public function setImageRoot($rootimage=NULL){
		if(isset($rootimage) && $rootimage !=NULL){
			$this->imgroot = $rootimage;
		}
		else{
			$this->imgroot = "/img/img.upload/";
		}
	}
	
	public function getQRCode(){
		$path = isset($_SERVER["REQUEST_URI"]) ? $_SERVER["REQUEST_URI"] : "";
		if(preg_match("/(qrcode)\/([^\/]+)\/S([0-9]+)\/P([0-9]+)\/?/",$path,$matches)){
			$string = urldecode($matches[2]);
			QRcode::png($string,false , "L", $matches[3], $matches[4]);
			
		}
		else{
			$return = array(
				"return"=>"error",
				"returnmessage"=>"QRCode not found!",
				"class_method"=> "ImageServer::getQRCode()"
			);
			header("Content-Type: application/json");
			echo json_encode($return);
		}
	}
	
	public function getImage($uri=NULL){
		
		$path = isset($uri) ? $uri : isset($_SERVER["REQUEST_URI"]) ? $_SERVER["REQUEST_URI"] : "";
		
		if(preg_match('/\/(img)\/([\.0-9a-zA-Z_\-]+)\/(SZ)([0-9]+)x([0-9]+)\/(jpg|png|gif)/',$path,$matches)){ /* [~/img/$IMAGE_NAME/SZ400x450/(png|jpg|gif)~] */
			$x = new SimpleImage($this->imgroot.$matches[2]);
			$x->resize($matches[4],$matches[5]);
			$x->output($matches[6]);
		}
		else if(preg_match('/\/(img)\/([\.0-9a-zA-Z_\-]+)\/(SZ|SW)([0-9]+)\/(jpg|png|gif)/',$path,$matches)){ /* [~/img/$IMAGE_NAME/(SZ|SW)400/(png|jpg|gif)~] */
			$x = new SimpleImage($this->imgroot.$matches[2]);
			$x->fit_to_width($matches[4]);
			$x->output($matches[5]);
		}
		else if(preg_match('/\/(img)\/([\.0-9a-zA-Z_\-]+)\/(SH)([0-9]+)\/(jpg|png|gif)/',$path,$matches)){  /* [~/img/$IMAGE_NAME/(SH)400/(png|jpg|gif)~] */
			$x = new SimpleImage($this->imgroot.$matches[2]);
			$x->fit_to_height($matches[4]);
			$x->output($matches[5]);
		}
		else if(preg_match('/\/(img)\/([\.0-9a-zA-Z_\-]+)\/(SZ)([0-9]+)x([0-9]+)\/?$/',$path,$matches)){  /* [~/img/$IMAGE_NAME/(SZ)400x450/~] */
			$x = new SimpleImage($this->imgroot.$matches[2]);
			$x->resize($matches[4],$matches[5]);
			$x->output();
		}
		else if(preg_match('/\/(img)\/([\.0-9a-zA-Z_\-]+)\/(SZ|SW)([0-9]+)\/?$/',$path,$matches)){  /* [~/img/$IMAGE_NAME/(SZ|SW)400/~] */
			try{
				$x = new SimpleImage($this->imgroot.$matches[2]);
				$x->fit_to_width($matches[4]);
				$x->output();
			}
			catch(Exception $e){
				return array("error"=>"OK");
			}
		}
		else if(preg_match('/\/(img)\/([\.0-9a-zA-Z_\-]+)\/(SH)([0-9]+)\/?$/',$path,$matches)){  /* [~/img/$IMAGE_NAME/(SH)400/~] */
			$x = new SimpleImage($this->imgroot.$matches[2]);
			$x->fit_to_height($matches[4]);
			$x->output();
		}
		else if(preg_match('/\/(img)\/([\.0-9a-zA-Z_\-]+)\/?$/',$path,$matches)){  /* [~/img/$IMAGE_NAME/(SH)400/~] */
			$x = new SimpleImage($this->imgroot.$matches[2]);
			$x->output();
		}
		else{
			$return = array(
			"return"=>"error",
			"returnmessage"=>"Image not found!",
			"class_method"=> "ImageServer::getImage()"
			);
			header("Content-Type: application/json");
			echo json_encode($return);
		}
	}
	
	function dropImage(){
		$path = isset($_SERVER["REQUEST_URI"]) ? $_SERVER["REQUEST_URI"] : "";
		$this->setImageRoot($_SERVER["DOCUMENT_ROOT"]."/img/img.upload/");
		if(preg_match('/\/(drop)\/(img)\/([\.0-9a-zA-Z_\-]+)?/',$_SERVER["REQUEST_URI"],$matches)){ /* [~/drop/img/$IMAGE_NAME~] */
			if(unlink($this->imgroot.$matches[3])){
				echo '{"return":"S0000"}';
			}
			else{
				$return = array(
				"return"=>"error",
				"returnmessage"=>"Image not found!",
				"class_method"=> "ImageServer::getImage()".$this->imgroot.$matches[3]
				);
				header("Content-Type: application/json");
				echo json_encode($return);
			}
		}
	}
	
	function uploadImage(){
		if(isset($_SERVER['REQUEST_METHOD'])&&$_SERVER['REQUEST_METHOD']=='POST' && isset($_FILES)){
			
			preg_match("/image\/(png|gif|jpeg|jpeg|bmp|ico)/",$_FILES["image_upload"]["type"],$matches_ext);
			
			$image_filter = array(
			"image_save_name" => isset($_POST["image_save_name"]) ? $_POST["image_save_name"] : "",
			"image_save_path" => isset($_POST["image_save_path"]) ? $_POST["image_save_path"] : "",
			"image_save_extension" => isset($_POST["image_save_extension"]) && !empty($_POST["image_save_extension"]) ? $_POST["image_save_extension"] : "png",
			"image_original_extension" => $matches_ext[1],
			"image_temp" => $_FILES["image_upload"]["tmp_name"],
			"image_original_name" => $_FILES["image_upload"]["name"],
			"image_original_size" => $_FILES["image_upload"]["size"]
			);
			
			$image_filter["image_save_full_path"] = $image_filter["image_save_path"].$image_filter["image_save_name"].".".$image_filter["image_save_extension"];
			
			if(count($image_filter) == count(array_filter($image_filter))){
				$image_string = file_get_contents($image_filter["image_temp"]);
				$image_string_encode = base64_encode($image_string);
				try {
					$image_create = new SimpleImage();
					$image_create->load_base64($image_string_encode);
					$image_create->save($image_filter["image_save_full_path"]);
					header("Content-Type: application/json");
					
					//response for image tumb. result :
					$image_filter["initialPreview"]=array('<img src="'.$this->default_host.'/E-Commerce/img/'.$image_filter["image_save_name"].".".$image_filter["image_save_extension"].'/SZ200/png" class="file-preview-image">');
					$image_filter["initialPreviewConfig"]=array(array(
						//"caption" => "Animal-1.jpg",
						"width" => "120px",
						"url" => "/E-Commerce/drop/img/".$image_filter["image_save_name"].".".$image_filter["image_save_extension"],
						"key" => "1"
						)
					);
					
					echo json_encode($image_filter);
				}
				catch(Exception $e) {
					echo 'Error: ' . $e->getMessage();
				}
			}
			else{
				$return = array(
				"return"=>"error",
				"returnmessage"=>"The request is incomplete",
				"class_method"=> "ImageServer::uploadImage()",
				"image_filter"=> print_r($image_filter)
				);
				header("Content-Type: application/json");
				echo json_encode($return);
			}
		}
		else{
			$return = array(
			"return"=>"error",
			"returnmessage"=>"The request is incomplete NULL FILE or POST",
			"class_method"=> "ImageServer::uploadImage()"
			);
			header("Content-Type: application/json");
			echo json_encode($return);
		}
	}
}
?>