<?php

require_once ('utils/general_utils.php');
require_once ('utils/string_utils.php');

/** Class to handle simple command line arguments.
Each command line argument must take the form:
--key=value when specified on the actual command line.
So for example:
\code
php my_script.php --verbose=1 --input-file=data.xml
\endcode

Construct with an (optional) string specify
*/
class CmdLineArgs
{
    public function __construct($sDefaultArgs="")
    {
		global $argv;
		$this->parseDefaultArgs($sDefaultArgs);
		$this->parseCommandLine($argv);
    }

    public function parseCommandLine($aArgv)
    {
		$this->m_aArgv = $aArgv;
		$this->m_sPhpScriptPath = $this->m_aArgv[0];
		
		$this->m_aParsedArgs = array();
		foreach( $this->m_aArgv as $arg ) {
			if ( contains( $arg, '--' ) ) {
				$arg = $subject = str_replace ( '--' , '' , $arg);
				$a = explodeTrim('=', $arg);
				if ( $a[0] != '' ) {
                    $this->m_aParsedArgs[$a[0]] = $a[1];
//                    $this->m_aParsedArgs[$a[0]] = $a[1] != '' ? $a[1] : true;
				}
			}
		}
    }
    
	/** Get string value of command line argument. */
    public function getValueStr($key)
    {
		$val = '';
		if ( array_key_exists($key, $this->m_aParsedArgs) ) {
			$val = $this->m_aParsedArgs[$key];
		}
		else if (array_key_exists($key, $this->m_aDefaultArgs) ) {
			$val = $this->m_aDefaultArgs[$key];
		}
		return $val;
    }


	/** Get int/integer value of command line argument. */
    public function getValueInt ($settings_name)
    {
        $val = $this->getValueStr($settings_name);
        return $val == '' ? (int)0 : (int)$val;
    }
    
	/** Get float value of command line argument. */
    public function getValueFloat ($settings_name)
    {
        $val = $this->getValueStr($settings_name);
        return $val == '' ? (float)0 : (float)$val;
    }
    
    /** Get bool value of command line argument. */
    public function getValueBool ($settings_name)
    {
        $val = $this->getValueStr($settings_name);
        if ( icontains($val, 'false') ) return false;
        else if ( $val == '0')          return false;
        else if ( $val == '')           return false;
        else return true;
    }

    /** Check if argument is set. Use typically for checking command line argument withu a value. 
		Typically ---verbose or similar. */
    public function argumentIsSet($key)
    {
		$bIsSet = false;
		if ( array_key_exists($key, $this->m_aParsedArgs) ) {
			$bIsSet = true;
		}
		return $bIsSet;
    }

    public function helpSet	($aHelp) { $this->m_aHelp = $aHelp; }
    
    public function phpScriptPath	() { return $this->m_sPhpScriptPath; }
    public function parsedArgs		() { return $this->m_aParsedArgs; }
    public function defaultArgs		() { return $this->m_aDefaultArgs; }
    
    public function helpString() 
    { 
		$s = $this->m_aHelp['INTRO'] . "\n\n";
		foreach ( $this->m_aDefaultArgs as $argName => $defaultVal ) {
			$helpStr = $this->m_aHelp[$argName];
            $s .= "--{$argName}: [" . $defaultVal . "]\n";
            $s .= "           {$helpStr}\n\n";
		}
        $s .= "--help:\n";
        $s .= "           Print this help.\n\n";
		return $s; 
	}

	public function helpPrint()
	{
		print "\n" . $this->helpString() . "\n";
	}
	
	public function checkPrintHelp()
	{
        if ( $this->argumentIsSet('help')) {
            $this->helpPrint();
            exit(0);
        }
	}
	
    private function parseDefaultArgs($sDefaultArgs)
    {
		$this->m_aDefaultArgs = array();
		$aKeyVals = explodeTrim(';', $sDefaultArgs );
		foreach( $aKeyVals as $sKeyVal ) {
			$a = explodeTrim('=', $sKeyVal);
			if ( $a[0] != '' ) {
				$this->m_aDefaultArgs[$a[0]] = $a[1];
//////				$this->m_aDefaultArgs[$a[0]] = $a[1] != '' ? $a[1] : true;
			}
		}
    
    }
    
    private $m_aArgv;
    private $m_sPhpScriptPath;
    private $m_aParsedArgs;
    private $m_aDefaultArgs = array();
    private $m_aHelp = array();
}
?>