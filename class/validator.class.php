<?php
/**
 * Validator class per la gestione dell'input tramite regole.
 * @author Carmelo San Giovanni
 * @package libss
 * @version 3.8
 * @copyright NetMDM 2010
 * 
 */
class validator{
	/**
	 * @var $rules, array di regole
	 */
	private $rules=array();
	/**
	 * 
	 * @var string $method, contiene il metodo usato
	 */
	private $method;

	
	public function __construct($method){
		$this->method=$method;
	}
	

	/**
	 * Metodo per l'aggiunta di una regola
	 * @param string $fieldname, contiene il nome del campo da controllare
	 * @param array $requisite, è un array che specifica il tipo di requisito e può essere
	 * array("required",true),array("minlength",10),array("maxlength",20),array("validmail",true)
	 * array("numeric","integer+"),array("numeric","integer-"),array("numeric","integer"),
	 * array("numeric","float+"),array("numeric","float-"),array("numeric","float")
	 */
	
	public function addRule($fieldname,$requisite,$errormsg){

			//se siamo nel post successivo all'inserimento non necessitiamo di aggiungere regole all'array perchè l'array delle regole è
			//già stato compilato precedentemente, dunque possiamo uscire dalla funzione
			$method= "_". strtoupper($this->method);
			//se $requisite non è un array completo interrompiamo l'esecuzione
			if (!is_array($requisite)) {
				die("VALIDATOR::addRule -> \$requisite must be an array...");
			} 
			//a questo punto possiamo compilare l'array dei requisiti
	   			$this->rules[]=array("fieldname"=>$fieldname,"requisite"=>$requisite[0],"value"=>$requisite[1],"errormsg"=>$errormsg);
	}
	
	/**
	 * Permette di controllare la validità di un captcha
	 * @return unknown_type
	 */
	public function checkCaptcha(){
		@session_start();
		$this->addRule("captcha",array("equal",$_SESSION['code']),"Security code errato!");
	}
	/**
	 * Funzione per la validazione dei campi
	 * Analizza l'array delle regole e verifica i requisiti
	 * @return mixed, può essere true, false o un messaggio di errore apposito.
	 */
	public function validate(){
		$method=(strtoupper($this->method)=="POST") ? $_POST : $_GET;
		$errorcode="";
		if (count($this->rules)>0)  {
			foreach ($this->rules as $rule){
				if (!isset($method[$rule['fieldname']])){
					//verifichiamo che il dato sia presente prima di effettuare i controlli
					$errorcode.=$rule['errormsg'].CRLF;
					continue;
				}
				switch ($rule['requisite']){
					case "required":
						if (strlen($method[$rule['fieldname']])<1) $errorcode.=$rule['errormsg'].CRLF;
						break;
					case "maxlength":
						if (strlen($method[$rule['fieldname']])>$rule['value']) $errorcode.=$rule['errormsg'].CRLF;
						break;
					case "equal":
						if ($method[$rule['fieldname']]!=$rule['value']) $errorcode.=$rule['errormsg'].CRLF;
						break; 
					case "minlength":
						if (strlen($method[$rule['fieldname']])<$rule['value']) $errorcode.=$rule['errormsg'].CRLF;
						break;
					case "maxlength":
						if (strlen($method[$rule['fieldname']])>$rule['value']) $errorcode.=$rule['errormsg'].CRLF;
						break;
					case "validmail":
						if (!preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,5})$/i", $method[$rule['fieldname']]))
						$errorcode.=$rule['errormsg'].CRLF;
						break;
					case "numeric":
						$error=false;
						switch(strtoupper($rule['value'])){
						case "INTEGER":
							$error=(!preg_match("/^(-){0,1}([0-9])+([0-9])*$/i", $method[$rule['fieldname']]));	
							break;
						case "INTEGER+":
							$error=(!preg_match("/^([0-9])+([0-9])*$/i", $method[$rule['fieldname']]));	
							break;
						case "INTEGER-":
							$error=(!preg_match("/^(-)+([0-9])+([0-9])*$/i", $method[$rule['fieldname']]));	
							break;
						case "FLOAT":
							$error=(!preg_match("/^(-)?[0-9]*\.?[0-9]+$/i", $method[$rule['fieldname']]));							
							break;
						case "FLOAT+":
							$error=(!preg_match("/^[0-9]*\.?[0-9]+$/i", $method[$rule['fieldname']]));							
							break;
						case "FLOAT-":
							$error=(!preg_match("/^-[0-9]*\.?[0-9]+$/i", $method[$rule['fieldname']]));							
							break;
						default:
							$error=(!preg_match("/^(-){0,1}([0-9])+([0-9])*$/i", $method[$rule['fieldname']]));	
					    }			
					    if ($error) $errorcode.=$rule['errormsg'].CRLF;	
					   	break;		
					/*case "permitextensions":
						foreach ($method[$rule['fieldname']] as $filename){
						}
						break;
					case "banextensions":
						break;	
					*/		
				}
			}
			if ($errorcode=="") return true;
				return $errorcode;
		}
	}
}
?>