<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

function printdbg($userdata)
{
	echo "All \$_POST values:<pre>";
	print_r($_POST);
	echo "</pre><hr>";
	echo "All \$data values:<pre>";
	print_r($userdata);
	echo "</pre><hr>";
	$CI =& get_instance();
	$sess = $CI->session->all_userdata();
	echo "All Session Data:<pre>";
	print_r($sess);
	echo "</pre><hr>";
}

function printdata($userdata)
{
	echo "User variable data:<pre>";
	print_r($userdata);
	echo "</pre><hr>";
}

function apiset($url, $json_data=array())
{
	if(is_array($json_data)) $json_data = json_encode($json_data);
	
	$hdrs = array(
		'Content-Type: application/json',
		'Content-Length: ' . strlen($json_data)
	);

	// if ($CI->session->userdata('islogged')) 
		// $hdrs[] = 'Authorization: Basic ' . base64_encode('user:123456'); //DEBUG!
		// $hdrs[] = 'Authorization: Basic ' . base64_encode($CI->session->userdata('login') . ':' . $CI->session->userdata('password'));
	
	$ch = curl_init($url);	
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,	1 );
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 	"POST");
	curl_setopt($ch, CURLOPT_POST,		   	1 );
	curl_setopt($ch, CURLOPT_POSTFIELDS,	 	$json_data);
	curl_setopt($ch, CURLOPT_HTTPHEADER, 		$hdrs);
	$result = curl_exec($ch);
	curl_close($ch);
	
	//debug output RAW data
	// echo '<pre>';
	// print_r($result);die;
	// echo '</pre>';

	// if ($result=='null') return FALSE;

	$result = json_decode($result);

	// if (isset($result->status) && $result->Message == 'An error has occurred.') return FALSE;

	return /*	(array)*/$result;
}

/* End of file dbg_helper.php */
/* Location: ./system/helpers/dbg_helper.php */