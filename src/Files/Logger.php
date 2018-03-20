<?PHP
/**
	new Logger($_SERVER['DOCUMENT_ROOT']."/log/LOGGED.log", $_SERVER['PHP_SELF'].":".__LINE__."  ".$data);
	new Logger("default", $_SERVER['PHP_SELF'].":".__LINE__."  ".$data);
 **/

class Logger extends System{
	private $files;
	private $username;
	public function __construct($logfile="default",$data,$fmode="a+"){
		$default_root = LOG_ROOT."LOG".date('[Y-m-d]').".log";
		$this->username = isset($_SESSION['s_username']) ? $_SESSION['s_username'] : "undefined";
		if($logfile=="default"||$logfile==NULL){
			$logfile=$default_root;
		}
		else if(!is_object($logfile)){
			try{
				$ext = pathinfo($logfile, PATHINFO_EXTENSION);
				$logfile = str_replace('.'.$ext, '', $logfile).date('[Y-m-d]').'.'.$ext;
			}
			catch(Exception $e){
				echo "Error: " . $e->getMessage();
			}
		}
		
		if(file_exists(dirname($logfile))){
			$this->files = fopen($logfile,$fmode);
			if($this->files){
				//if(fwrite($this->files, date('[Y-m-d H:i:s]'). PHP_EOL ."" . $this->username ." @ " . $data . PHP_EOL)){
				//if(fwrite($this->files, date('[Y-m-d H:i:s]'). " " . $this->username ." @ " . $data . PHP_EOL)){
				if(fwrite($this->files, "[".parent::getDateTime2()."]" . " " . $this->username ." @ " . $data . PHP_EOL)){
					fclose($this->files);
					return TRUE;
				}
			}
			return FALSE;
		}
	}
	
	public function test(){
	
		return ["returnval"=>true,"returnmsg"=>"You access test on Logger!","data"=>$this->data_source];
	}
	
	//Logger::writelog($_SERVER['DOCUMENT_ROOT']."/log/LOGGED.log", $_SERVER['PHP_SELF'].":".__LINE__."  ".$data);
	public static function writelog($logfile,$data,$fmode="a+"){
		$default_root = LOG_ROOT."LOG".date('[Y-m-d]').".log";
		$username = isset($_SESSION['s_username']) ? $_SESSION['s_username'] : "undefined";
		if($logfile=="default"||$logfile==NULL){
			$logfile=$default_root;
		}
		if(file_exists(dirname($logfile))){
			$files = fopen($logfile,$fmode);
			if($files){
				//if(fwrite($files, date('[Y-m-d H:i:s]'). PHP_EOL ."" . $username ." @ " . $data . PHP_EOL)){
				//if(fwrite($files, date('[Y-m-d H:i:s]'). " " . $username ." @ " . $data . PHP_EOL)){
				if(fwrite($files, "[".parent::getDateTime2()."]" . " " . $username ." @ " . $data . PHP_EOL)){
					fclose($files);
					return TRUE;
				}
			}
			return FALSE;
		}
	}
}

?>