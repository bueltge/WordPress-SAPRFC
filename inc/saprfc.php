<?
/*
	$Log: saprfc.php,v $
	Revision 1.7  2002/01/14 11:08:03  llaegner
	bugfix in Exporting Param: when export returns the -good value "0" empty() evaluates to an error-condition ( thanks Alexander Gouriev)
	
	Revision 1.6  2001/08/16 15:54:35  llaegner
	renamed saprfc_class.php to saprfc.php
	
	Revision 1.2  2001/08/16 15:52:23  llaegner
	bugfix: login => will be renamed to saprfc.php
	
	Revision 1.2  2001/08/16 08:14:28  llaegner
	added: new errorhandling with get/set/printStatus[Text]() -Functions
	
	Revision 1.1  2001/08/15 16:00:59  llaegner
	first public version of saprfc-class. provides methods for accessing sap-systems with the php_saprfc.dll
	
	Access the saprfc-php-extension via class interface
	created by lars laegner, btexx business technologies, august 2001
	
	// TODO's:
	//   - look for cookie "SAPRFC_LOGON", if logininfo is not given via the class
	//   - checkout php as an rfc-server (should be quite useful, when abap is boring ...)
	
	Example:
		see corresponding file "example_userlist.php"

*/

// For easy checking of BAPI-type values
if (!defined("SAPRFC_OK")) define("SAPRFC_OK",0);
if (!defined("SAPRFC_ERROR")) define("SAPRFC_ERROR",1);
if (!defined("SAPRFC_APPL_ERROR")) define ("SAPRFC_APPL_ERROR",2);


class saprfc {

	var $logindata;
	var $rfc_conn;
	var $func_id;
	var $show_status_flag;
	var $check_bapi_errors;
	var $status;
	var $status_infos;
	var $debug;
	var $call_function_result;

	function saprfc($config=array()) {
		$this->rfc_conn=false;
		$this->func_id=false;
		$this->show_status_flag=isset($config["show_errors"]) ? $config["show_errors"] : true;
		$this->check_bapi_errors=isset($config["check_bapi_errors"]) ? $config["check_bapi_errors"] : false;
		$this->status="";
		$this->status_infos="";
		$this->debug=isset($config["debug"]) ? $config["debug"] : false;
		$this->logindata=isset($config["logindata"])?$config["logindata"]:array();
		$this->call_function_result=false; // Result of the last executed sapaccess-call_function()
	}
	
	function setLoginData($logindata) {
		$this->logindata=$logindata;
	}

	// TODO: currently only login with logindata allowed
	// that means you have to set logindata for the class on creation
	// maybe we can use setcookie ("SAPRFC_LOGON", urlencode(serialize($this->logindata)),time()+7200);	
	function login() {
	   	if (!$this->rfc_conn)
	   	{
		   	$this->rfc_conn=@saprfc_open($this->logindata);
			if (!$this->rfc_conn) {
				return $this->setStatus(SAPRFC_ERROR,"saprfc::login()\nOpen RFC connection with saprfc_open() failed with error:\n".@saprfc_error());
			}
		}
		return SAPRFC_OK;
	}

	// Close RFC_Connection	
	function logoff() {
	   	if ($this->rfc_conn)
	   	{
			@saprfc_close($this->rfc_conn);
		}
	}

	// set Status and optionally show Errors
	function setStatus($status,$status_infos) {
		$this->status=$status;
		$this->status_infos=$status_infos;
		if ($this->show_status_flag &&
			$this->status!=SAPRFC_OK ) {
			$this->printStatus();
		}
		return $this->status;
	}		
	
	// Checks if last Call succeded
	function getStatus() {
		return $this->status;
	}
	
	// Returns actual Status/Error
	function getStatusText() {
		$statustext="";
		switch ($this->status) {
			case SAPRFC_OK:
				$statustext=$this->status_infos;
				break;
			case SAPRFC_APPL_ERROR:
				$statustext=$this->status_infos["TYPE"]." ".$this->status_infos["ID"]."-".$this->status_infos["NUMBER"].": ".$this->status_infos["MESSAGE"];
				break;
			case SAPRFC_ERROR:
				$statustext=$this->status_infos;
				break;
		}
		return $statustext;
	}

	// Returns actual Status/Error
	function getStatusTextLong() {
		$statustext="";
		switch ($this->status) {
			case SAPRFC_OK:
				$statustext.="<br><font size=4 color=green><pre>";
				$statustext.="No errors detected.";
				$statustext.="</font><br><font size=3 color=green><pre>";
				$statustext.="<br><b>".$this->getStatusText()."</b>";
				$statustext.="</pre></font>";
				break;
			case SAPRFC_APPL_ERROR:
				$statustext.="<br><font size=4 color=red><pre>";
				$statustext.="Application-Errors found during BAPI-Calls:";
				$statustext.="</font><br><font size=3 color=red><pre>";
				$statustext.="<br><b>".$this->getStatusText()."</b>";
				$statustext.="</pre></font>";
				break;
			case SAPRFC_ERROR:
				$statustext.="<br><font size=4 color=red><pre>";
				$statustext.="Errors found during saprfc-Calls:";
				$statustext.="</font><br><font size=3 color=red><pre>";
				$statustext.="<br><b>".$this->getStatusText()."</b>";
				$statustext.="</pre></font>";
				break;
		}
		return $statustext;
	}

	function printStatus() {
		echo $this->getStatusTextLong();
	}		
	
	// Call RFC-Function in SAP		
	function callFunction($func_name="",$parameters) {
		/* typical call:
			$result=$saprfc->call_function("RFC_SYSTEM_INFO",
									array(	array("EXPORT","SYSTEM","MBS")
											array("IMPORT","CODEPAGE")
											array("IMPORT","DBNAME")
											array("TABLE","APPLLIST",array())
									)
								);
		*/
		
		// Initialize Variables
		$result_data=array();
		$this->call_function_result=false;
						
		// Check SAPRFC-Installation
		if (! extension_loaded ("saprfc")) {
			return $this->setStatus(SAPRFC_ERROR,"saprfc::callFunction()\n SAPRFC-Extension.dll not loaded.");
		}
		
		// Validate given data
		if (empty($func_name)) {
			return $this->setStatus(SAPRFC_ERROR,"saprfc::callFunction():\n No Function-Name given");
		}
		
		// Move Parameters to local Arrays
		$func_params_import=array();		
		$func_params_tables=array();
		$func_params_export=array();
		foreach ($parameters as $key => $param) {
			$type=$param[0];
			$name=$param[1];
			$value=isset($param[2])?$param[2]:"";
			switch ($type) {
				case "IMPORT":
					$func_params_import[$name]=$value;
					break;
				case "EXPORT":
					$func_params_export[$name]="";
					break;
				case "TABLE":
					$func_params_tables[$name]=$value;
					if (!is_array($value)) {
						return $this->setStatus(SAPRFC_ERROR,"saprfc::callFunction()\n Wrong Parameter-Value for Table-Parameter ".$name.". We expected an Array.");
					}
					break;
				default:
					return $this->setStatus(SAPRFC_ERROR,"saprfc::callFunction()\n Wrong Parameter-Type '".$type."'. Must be IMPORT, EXPORT or TABLE.");
			}
		}
		
		// Do Login (only done in method login(), if necessary)	
		if ($this->login()==SAPRFC_ERROR) {
			return $this->getStatus();
		}
		
		// Discover Function in SAP
		$this->func_id=@saprfc_function_discover($this->rfc_conn,$func_name);
		if (!$this->func_id) {
			return $this->setStatus(SAPRFC_ERROR,"saprfc::callFunction()\n Function module '".$func_name."' seems not to exist. function saprfc_function_discover() failed.");
		}

		// Set Import-Parameters		
		foreach ($func_params_import as $name => $value) {
			$rc=@saprfc_import($this->func_id,$name,$value);
			if (empty($rc)) {
				return $this->setStatus(SAPRFC_ERROR,"saprfc::callFunction('".$func_name."')\n Import-Parameter=".$name. " could not be set. (Does it exist?)");
			}
		}
		// Set Table-Parameters	(importing-values)
		foreach ($func_params_tables as $name => $value) {
			$rc=@saprfc_table_init($this->func_id,$name);
			if (empty($rc)) {
				return $this->setStatus(SAPRFC_ERROR,"saprfc::callFunction('".$func_name."')\n Table-Parameter=".$name. " could not be set. (Does it exist?)");
			}
			foreach ($value as $key => $data) {
				@saprfc_table_append($this->func_id,$name,$data);
			}
		}

		// Execute Function
		$result = @saprfc_call_and_receive ($this->func_id);
		if ($result != SAPRFC_OK)
		{
			if ($result == SAPRFC_EXCEPTION ) {
				return $this->setStatus(SAPRFC_ERROR,"saprfc::callFunction('".$func_name."')\n saprfc_call_and_receive(): Exception raised: ".@saprfc_exception($this->func_id));
			} else {
				return $this->setStatus(SAPRFC_ERROR,"saprfc::callFunction('".$func_name."')\n saprfc_call_and_receive(): Call error: ".@saprfc_error($this->func_id));
			}
		}
	   
		// Get Exporting-Parameters
		foreach ($func_params_export as $name =>$value) {
			$rc=@saprfc_export($this->func_id,$name);
//			if (empty($rc)) { // llaegner removed (Reason: when export returns the -good- value "0", then empty also evaluates to true (thanks Alexander Gouriev)
			if (!isset($rc)) {
				return $this->setStatus(SAPRFC_ERROR,"saprfc::callFunction('".$func_name."')\n Export-Parameter=".$name. " could not be set (Does it exist?)".@saprfc_error($this->func_id));
			} else {
				$result_data[$name]=$rc;
			}
		}

		// Get Table-Parameters
		foreach ($func_params_tables as $name => $content) {
			// Ausgabe-Tabelle initialisieren
			$result_data[$name]=array(); 
			$rows=@saprfc_table_rows($this->func_id,$name);
			for ($i=1; $i<=$rows; $i++)
			{
				$result_data[$name][$i]=@saprfc_table_read ($this->func_id,$name,$i);
			}
		}

		// Save function-call result for later analysis
		$this->call_function_result=$result_data;
		
		// Echo Debug-Information, if flagged
		if ($this->debug)
			@saprfc_function_debug_info($this->func_id);
			
		// Falls es ein BAPI-Aufruf ist, Fehler raussuchen
		if ($this->check_bapi_errors) {
			$bapi_return=@saprfc_export($this->func_id,"RETURN");
			if (isset($bapi_return) && 
					is_array($bapi_return) && 
					isset($bapi_return["MESSAGE"]) && 
					$bapi_return["NUMBER"] != 0) {
					
				// FINISH FUNCTION-CALL
				$this->setStatus(SAPRFC_APPL_ERROR,$bapi_return);
				return $result_data;	
			}
		}
		// Close Function-Execution and free results removed because it sometimes stops completly executing PHP!!
		//@saprfc_function_free($this->func_id);
		
		// FINISH FUNCTION-CALL
		$this->setStatus(SAPRFC_OK,"call function '".$func_name."' successfull.");
		return $result_data;	
	}
	
}

?>
