<?php
/**
* encrypt/decrypt helper class.
*
* @author         thejw23
* @copyright     (c) 2009 thejw23
* @license        http://www.opensource.org/licenses/isc-license.txt
* @version         0.1
*   
*/

class simple_crypt {

	public $position = 8;
	public $secret_prefix = 'start_';
	public $secret_suffix = '_end';
	public $len_position = 0; //must be different than $position and $position+1 !;
	
	// max encrypt id = 999999999
	// max len_hex = FF --- len must not be longer that 2 chars

	public function hexstr($hex)
	{
	$string="";
	for ($i=0;$i<strlen($hex)-1;$i+=2)
		$string.=chr(hexdec($hex[$i].$hex[$i+1]));
	return $string;
	}

	public function strhex($string) {
	$hex = '';
	$len = strlen($string);
	
	for ($i = 0; $i < $len; $i++) {
		$hex .= str_pad(dechex(ord($string[$i])), 2, 0, STR_PAD_LEFT);
	}    
	return $hex;  
	}

	public function encrypt($id=0,$ishex=false)
	{
		($ishex) ? $hex=$id : $hex=dechex($id);
		
		$id=$this->secret_prefix.$id.$this->secret_suffix;

		$len_hex=dechex(strlen($hex));
		if (strlen($len_hex)===1)
			$len_hex='0'.$len_hex;

		$encoded=substr_replace(md5($id), $hex, $this->position, 0);

		if ($this->len_position === 0)
			return $encoded=substr_replace($encoded, $len_hex, strlen($encoded), 0);
		else
			return $encoded=substr_replace($encoded, $len_hex, $this->len_position, 0);      
	}
	
	
	public function decrypt($id=0, $ishex=false)
	{
		if ($this->len_position === 0)
			$len=($id[strlen($id)-2].$id[strlen($id)-1]);
		else          
			$len=($id[$this->len_position].$id[$this->len_position+1]);
		
		$len=hexdec($len);
		
		if (($this->len_position < $this->position) AND ($this->len_position!==0))     
			$dec=substr($id,$this->position+2,$len);
		else
			$dec=substr($id,$this->position,$len);
		
		if ($ishex)
			return ($id==$this->encrypt($dec,true)) ? $this->hexstr($dec): 0;
		return ($id==$this->encrypt(hexdec($dec))) ? hexdec($dec): 0;
	}
}

?>