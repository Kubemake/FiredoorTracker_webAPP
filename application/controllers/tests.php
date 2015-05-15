<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Tests extends CI_Controller {

	private $url = 'http://firedoortracker.org/service/dispatcher';

	function __construct()
	{
		parent::__construct();
		verifyLogged();
		$this->load->model('service_model');
	}

	function _show($text, $array)
	{
		echo '<span><a href=\'' . $this->url . '?json=' . json_encode($array) . '\' target="_blank">' . $text . '</a>: </span>';
		$out = apiset($this->url, $array);
		print_r($out); echo '<br>';
		return $out;
	}
	function index()
	{
	
		echo '<style>body{margin-left:250px;}span{margin-left:-250px;display:inline-block;width:250px;}</style>' . "\n";
		
		echo 'I. <b>auth</b>' . '<br>';
		$this->_show('1. empty login && password', 				array('type' => 'auth', 'login' => '', 					'password' => ''));
		$this->_show('2. empty login', 							array('type' => 'auth', 'login' => '',					'password' => '124'));
		$this->_show('3. empty password', 						array('type' => 'auth', 'login' => 'any@login@err',		'password' => ''));
		$this->_show('4. wrong login', 							array('type' => 'auth', 'login' => 'any@login@err',		'password' => '124'));
		$this->_show('5. wrong password', 						array('type' => 'auth', 'login' => 'm@test.nor',		'password' => '1241'));
		$out = $this->_show('6. normal login', 					array('type' => 'auth', 'login' => 'm@test.nor',		'password' => '124'));

		$token = $out->token;

//----------------------------------
		echo '<br>II. <b>get_profile_data AND token</b>' . '<br>';
		$this->_show('1. without token', 						array('type' => 'get_profile_data'));
		$this->_show('2. empty token', 							array('type' => 'get_profile_data', 'token' => ''));
		$this->_show('3. wrong token', 							array('type' => 'get_profile_data', 'token' => 'wrong_token'));
		$this->_show('4. get normal', 							array('type' => 'get_profile_data', 'token' => $token));

//----------------------------------
		echo '<br>III. <b>update_profile_data</b>' . '<br>';
		$this->_show('1. without update data', 					array('type' => 'update_profile_data', 'token' => $token));
		$this->_show('2. wrong update paramer', 				array('type' => 'update_profile_data', 'token' => $token, 'wrong_param' => 'wrong_param_value'));
		$this->_show('3. very long paramer', 					array('type' => 'update_profile_data', 'token' => $token, 'firstName' => random_string('alnum', 260)));
		$this->_show('4. get normal with one param', 			array('type' => 'update_profile_data', 'token' => $token, 'firstName' => random_string('alnum', 6)));
		$this->_show('5. get more then one param', 				array('type' => 'update_profile_data', 'token' => $token, 'firstName' => random_string('alnum', 6), 'officePhone' => random_string('numeric', 12)));

//----------------------------------
		echo '<br>IV. <b>get_inspection_list_by_user</b>' . '<br>';
		$out = apiset($this->url, array('type' => 'auth', 'login' => 'empt_ins@test.nor','password' => '124'));
		$this->_show('1. get empty inspection list', 			array('type' => 'get_inspection_list_by_user', 'token' => $out->token));
		$out = $this->_show('2. get non-empty inspection list', array('type' => 'get_inspection_list_by_user', 'token' => $token));
		foreach ($out->inspections as $value) {
			$inspection_id = $value->id;
			$aperture_id   = $value->aperture_id;
			break;
		}
		

//----------------------------------
		echo '<br>V. <b>get_aperture_overview_info</b>' . '<br>';
		$this->_show('1. without aperture_id param', 			array('type' => 'get_aperture_overview_info', 'token' => $token));
		$this->_show('2. empty aperture_id param', 				array('type' => 'get_aperture_overview_info', 'token' => $token, 'aperture_id' => ''));
		$this->_show('3. get normal', 							array('type' => 'get_aperture_overview_info', 'token' => $token, 'aperture_id' => $aperture_id));

//----------------------------------
		echo '<br>VI. <b>get_aperture_issues</b>' . '<br>';
		$this->_show('1. without input params', 				array('type' => 'get_aperture_issues', 'token' => $token));
		$this->_show('2. without inspection_id param', 			array('type' => 'get_aperture_issues', 'token' => $token, 'wall_Rating' => 'Smoke Wall', 'smoke_Rating' => 'Smoke Rated Door', 'material' => 'Aluminum', 'rating' => '45'));
		$this->_show('3. empty inspection_id param', 			array('type' => 'get_aperture_issues', 'token' => $token, 'inspection_id' => '', 'wall_Rating' => 'Smoke Wall', 'smoke_Rating' => 'Smoke Rated Door', 'material' => 'Aluminum', 'rating' => '45'));
		$this->_show('4. without wall_Rating param', 			array('type' => 'get_aperture_issues', 'token' => $token, 'inspection_id' =>  $inspection_id, 'smoke_Rating' => 'Smoke Rated Door', 'material' => 'Aluminum', 'rating' => '45'));
		$this->_show('5. empty wall_Rating param', 				array('type' => 'get_aperture_issues', 'token' => $token, 'inspection_id' =>  $inspection_id, 'wall_Rating' => '', 'smoke_Rating' => 'Smoke Rated Door', 'material' => 'Aluminum', 'rating' => '45'));
		$this->_show('6. without smoke_Rating param', 			array('type' => 'get_aperture_issues', 'token' => $token, 'inspection_id' =>  $inspection_id, 'wall_Rating' => 'Smoke Wall', 'material' => 'Aluminum', 'rating' => '45'));
		$this->_show('7. empty smoke_Rating param', 			array('type' => 'get_aperture_issues', 'token' => $token, 'inspection_id' =>  $inspection_id, 'wall_Rating' => 'Smoke Wall', 'smoke_Rating' => '', 'material' => 'Aluminum', 'rating' => '45'));
		$this->_show('8. without material param', 				array('type' => 'get_aperture_issues', 'token' => $token, 'inspection_id' =>  $inspection_id, 'wall_Rating' => 'Smoke Wall', 'smoke_Rating' => 'Smoke Rated Door', 'rating' => '45'));
		$this->_show('9. empty material param', 				array('type' => 'get_aperture_issues', 'token' => $token, 'inspection_id' =>  $inspection_id, 'wall_Rating' => 'Smoke Wall', 'smoke_Rating' => 'Smoke Rated Door', 'material' => '', 'rating' => '45'));
		$this->_show('10. without rating param', 				array('type' => 'get_aperture_issues', 'token' => $token, 'inspection_id' =>  $inspection_id, 'wall_Rating' => 'Smoke Wall', 'smoke_Rating' => 'Smoke Rated Door', 'material' => 'Aluminum'));
		$this->_show('11. empty rating param', 					array('type' => 'get_aperture_issues', 'token' => $token, 'inspection_id' =>  $inspection_id, 'wall_Rating' => 'Smoke Wall', 'smoke_Rating' => 'Smoke Rated Door', 'material' => 'Aluminum', 'rating' => ''));
		$this->_show('12. wrong update paramer', 				array('type' => 'get_aperture_issues', 'token' => $token, 'inspection_id' =>  $inspection_id, 'wall_Rating' => 'Smoke Wall', 'smoke_Rating' => 'Smoke Rated Door', 'material' => 'Aluminum', 'rating' => '45', 'wrong_param' => 'wrong_param_value'));
		$out = $this->_show('13. get normal', 					array('type' => 'get_aperture_issues', 'token' => $token, 'inspection_id' =>  $inspection_id, 'wall_Rating' => '1 Hour', 'smoke_Rating' => 'Yes', 'material' => 'Glass', 'rating' => '45 Minute', 'width' => '30', 'height' => '84', 'Building' => '4'));
		$this->_show('13. updated aperture overview',			array('type' => 'get_aperture_overview_info', 'token' => $token, 'aperture_id' => $aperture_id));

//----------------------------------
		echo '<br>V. <b>update_inspection_data</b>' . '<br>';

		$user = $this->service_model->get_token_info($token);
		$user_id = $user['user_id'];
		
		$this->_show('1. without input params',					array('type' => 'update_inspection_data', 'token' => $token));
		$this->_show('2. without inspection_id param', 			array('type' => 'update_inspection_data', 'token' => $token, 'status' => 1, 'idFormFields' => 5, 'selected' => 1));
		$this->_show('3. empty inspection_id param', 			array('type' => 'update_inspection_data', 'token' => $token, 'inspection_id' => '', 'status' => 1, 'idFormFields' => 5, 'selected' => 1));
		$this->_show('4. without status param', 				array('type' => 'update_inspection_data', 'token' => $token, 'inspection_id' => $inspection_id, 'idFormFields' => 5, 'selected' => 1));
		$this->_show('5. empty status param', 					array('type' => 'update_inspection_data', 'token' => $token, 'inspection_id' => $inspection_id, 'status' => '', 'idFormFields' => 5, 'selected' => 1));
		$this->_show('6. without idFormFields param', 			array('type' => 'update_inspection_data', 'token' => $token, 'inspection_id' => $inspection_id, 'status' => 1, 'selected' => 1));
		$this->_show('7. empty idFormFields param', 			array('type' => 'update_inspection_data', 'token' => $token, 'inspection_id' => $inspection_id, 'status' => 1, 'idFormFields' => '', 'selected' => 1));
		$this->_show('8. without selected param', 				array('type' => 'update_inspection_data', 'token' => $token, 'inspection_id' => $inspection_id, 'status' => 1, 'idFormFields' => 5));

		$this->_show('9. set non-compliant answer again', 		array('type' => 'update_inspection_data', 'token' => $token, 'inspection_id' => $inspection_id, 'status' => 2, 'idFormFields' => 6, 'selected' => 1));
		echo '<span>Operation results: </span>';print_r($this->service_model->get_curent_questions_with_status_answers($inspection_id, $user_id)); echo '<br>';
		$this->_show('10. set second non-compliant answer', 	array('type' => 'update_inspection_data', 'token' => $token, 'inspection_id' => $inspection_id, 'status' => 3, 'idFormFields' => 7, 'selected' => 1));
		echo '<span>Operation results: </span>';print_r($this->service_model->get_curent_questions_with_status_answers($inspection_id, $user_id)); echo '<br>';
		$this->_show('11. set compliant answer', 				array('type' => 'update_inspection_data', 'token' => $token, 'inspection_id' => $inspection_id, 'status' => 1, 'idFormFields' => 5, 'selected' => 1));
		echo '<span>Operation results: </span>';print_r($this->service_model->get_curent_questions_with_status_answers($inspection_id, $user_id)); echo '<br>';
		$this->_show('12. set non-compliant answer', 			array('type' => 'update_inspection_data', 'token' => $token, 'inspection_id' => $inspection_id, 'status' => 4, 'idFormFields' => 6, 'selected' => 1));
		echo '<span>Operation results: </span>';print_r($this->service_model->get_curent_questions_with_status_answers($inspection_id, $user_id)); echo '<br>';
		$this->_show('13. set second non-compliant answer', 	array('type' => 'update_inspection_data', 'token' => $token, 'inspection_id' => $inspection_id, 'status' => 5, 'idFormFields' => 7, 'selected' => 1));
		echo '<span>Operation results: </span>';print_r($this->service_model->get_curent_questions_with_status_answers($inspection_id, $user_id)); echo '<br>';
		$this->_show('14. set compliant answer again', 			array('type' => 'update_inspection_data', 'token' => $token, 'inspection_id' => $inspection_id, 'status' => 1, 'idFormFields' => 5, 'selected' => 1));
		echo '<span>Operation results: </span>';print_r($this->service_model->get_curent_questions_with_status_answers($inspection_id, $user_id)); echo '<br>';
		$this->_show('15. unset answer', 						array('type' => 'update_inspection_data', 'token' => $token, 'inspection_id' => $inspection_id, 'status' => 1, 'idFormFields' => 5, 'selected' => ''));
		echo '<span>Operation results: </span>';print_r($this->service_model->get_curent_questions_with_status_answers($inspection_id, $user_id)); echo '<br>';

//----------------------------------
		echo '<br>VI. <b>set_inspection_confirmation</b>' . '<br>';
		$this->_show('1. without inspection_id param', 			array('type' => 'set_inspection_confirmation', 'token' => $token));
		$this->_show('2. empty inspection_id param', 			array('type' => 'set_inspection_confirmation', 'token' => $token, 'inspection_id' => ''));
		$this->_show('3. get normal', 							array('type' => 'set_inspection_confirmation', 'token' => $token, 'inspection_id' => $inspection_id));
		$this->_show('Operation results',						array('type' => 'get_inspection_list_by_user', 'token' => $token));

//----------------------------------
		echo '<br>VII. <b>get_locations_tree</b>' . '<br>';
		$this->_show('1. get normal ', 							array('type' => 'get_locations_tree', 'token' => $token));

//----------------------------------
		echo '<br>VIII. <b>check_door_uid</b>' . '<br>';
		$this->_show('1. without barcode param', 				array('type' => 'check_door_uid', 'token' => $token));
		$this->_show('2. empty barcode param',					array('type' => 'check_door_uid', 'token' => $token, 'barcode' => ''));
		$this->_show('3. get normal ', 							array('type' => 'check_door_uid', 'token' => $token, 'barcode' => 1));

	}



}

/* End of file tests.php */
/* Location: ./application/controllers/tests.php */