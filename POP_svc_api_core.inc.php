<?php
//********************************************************************************
//********************************************************************************
// Service API Core Class Plugin
//********************************************************************************
//********************************************************************************
// Started: 7/17/2012
// Updated: 7/17/2012
//********************************************************************************
//********************************************************************************
abstract class SERVICE_API_CORE
{
	//========================================================================
	// Class Members (Variables)
	//========================================================================
	protected $env;
	protected $trans_name;
	protected $handle;
	protected $server;
	protected $server_ip;
	protected $port;
	protected $params;
	protected $trans_xml;
    protected $success;
    protected $nodata;
    protected $error;
    protected $response;
    protected $results;
    protected $error_results;

	//========================================================================
	// Constructor
	//========================================================================
	public function __construct($trans_name=false)
	{
		$this->trans_name = $trans_name;
		$this->reset();
	}

	//========================================================================
	// Destructor
	//========================================================================
	public function __destruct() {}

	//========================================================================
	// To String
	//========================================================================
    public function __toString()
    {    	
    	ob_start();
    	print "<pre>\n";
    	print "Parameters: ";
    	print_r($this->params);
    	print "\nResults: ";
    	print_r($this->results);
    	print "\nError Results: ";
    	print_r($this->error_results);
    	print "</pre>\n";
    	return ob_get_clean() . "<br/>\n";
    }

	//========================================================================
	// Reset Statuses
	//========================================================================
	protected function reset($type=false)
	{
		if (strtolower($type) == 'all') {
			$this->trans_name = false;
			$this->params = array();
			$this->trans_xml = false;
		}
		$this->success = false;
		$this->nodata = true;
		$this->error = false;
		$this->response = false;
		$this->results = array();
		$this->error_results = array();
	}

	//========================================================================
	// Status / Value Functions
	//========================================================================
    public function isSuccessful() { return $this->success; }
    public function isError() { return $this->error; }
    public function isNoData() { return $this->nodata; }
    public function getParamters() { return $this->params; }
    public function getResponse() { return $this->response; }
    public function getResults() { return $this->results; }
    public function getErrorResults() { return $this->error_results; }

	//========================================================================
	// Set Tranaction Name Function
	//========================================================================
    public function set_trans_name($n)
    {
    	$this->trans_name = (string)$n;
    }

	//========================================================================
	// Set Parameter Function
	//========================================================================
    public function setParam($key, $val)
    {
    	$this->params[$key] = $val;
    }

	//========================================================================
	// Process Transaction
	//========================================================================
	public function process()
    {
    	$this->reset();
		return false;
    }

	//========================================================================
	// Parse Results
	//========================================================================
    private function parseResults() {}

}
//********************************************************************************
//********************************************************************************
// End Service API Core Class
//********************************************************************************
//********************************************************************************

?>
