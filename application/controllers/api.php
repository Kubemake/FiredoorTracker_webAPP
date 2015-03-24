<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Api extends CI_Controller {

	function __construct()
	{
		parent::__construct();
		$this->load->model('api_model');
	}
	
	function show($type, $action, $param='default')
	{
		// echo time();
		$t=array( //for open-serp type
									array(
										'host'	=> 'zaycev.fm',
										'isAd'	=> true
									),
									array(
										'host'	=> 'otvet.mail.ru',
										'isAd'	=> null
									),
									array(
										'host'	=> 'RuPark.com',
										'isAd'	=> null
									)
								);
		echo '<pre>';
		print_r(serialize($t));die();
		$out = unserialize(file_get_contents(APPPATH.'data/'.$type.'-'.$action.'-'.$param));
		printdata(json_decode($out['report']));
		echo 'ok';
	}

	function auth($action='login', $param='default')
	{
		$data = $_GET;
		file_put_contents(APPPATH.'data/auth-'.$action.'-'.$param, serialize($data));

		if ($action=='login')
		{
			if (!isset($data['code'])) //mean login with phone numb.
			{
				if (isset($data['phone']) && !empty($data['phone']))
				{
					$user = $this->api_model->get_user_data_by_phone($data['phone']);

					if (empty($user)) //check for empty
					{
						$ans = array(
							'result'	=> 'fail',
							'error'		=> 'Некорректный номер телефона'
						);
					}
					else  //if all ok save sess data and make result_ok array
					{
						$user['code']  = '123';
						$this->api_model->set_session_data($this->session->userdata('session_id'), $user['phone'], $user['code']);
						
						$ans = array(
							'result' 	=> 'ok',
							'phone'		=> $user['phone'],
							'sid'		=> $this->session->userdata('session_id')
						);
					}
				}
			}
			else
			{
				if (isset($data['phone']) && !empty($data['phone']) && isset($data['code']) && !empty($data['code']))
				{
					$user = $this->api_model->get_session_data($data['sid'], $data['phone'], $data['code']);
					if (empty($user)) {
						$ans = array(
							'result'	=> 'fail',
							'error'		=> 'Некорректный код.'
						);
					}
					else
					{
						$user = $this->api_model->get_user_data_by_phone($user['phone']);
						$ans = array(
							'result' 	=> 'ok',
							'user' 		=> array(
								'ctime' 		=> $user['ctime'],  			//DATETIME user registration
								'id' 			=> $user['id'],					//user ID
								'jobs_done' 	=> $user['jobs_done'],			//Like in referrer ?
								'jobs_fail' 	=> $user['jobs_fail'],
								'money' 		=> $user['money'],				//like earned?
								'money_earned' 	=> $user['money_earned'],		//and wtf this
								'payout_mode'	=> $user['payout_mode'],
								'phone' 		=> $user['phone'],				//like login
								'region' 		=> $user['region'],				//requester country(region?) by it IP
								'region_id'		=> $user['region_id'],			//requester country(region?) id by it IP
								'registered_at' => $user['registered_at'],		//like ctime
								'ya_id' 		=> $user['ya_id']				//may be yandex id 
							),
							'referrer' => array(
								'earned' 	=> $user['ref_earned'],
								'jobs_done' => $user['ref_jobs_done'],
								'link' 		=> $user['ref_link'], 				//реф ссылка пользователя
								'referrals' => $user['ref_referrals']
							)
						);
					}
				}
			}
		}
		elseif ($action=='status')  //reason may be user or server
		{
			$user =  $this->api_model->get_user_data_by_sid($data['sid']);
			if (empty($user)) {
				$ans = array(
					'result'	=> 'fail',
					'error'		=> 'Неизвестный пользователь.'
				);
			}
			else
			{
				$this->api_model->update_user_data($user['id'],array('status' => $data['status']));
				$ans = array(
					'result'	=> 'ok',
					'status'		=> $data['status']
				);
			}
		}



			/*'sid'		=> '123123123',
			'session'	=> 'sessiondata123',
			'user' 		=> array(
				'ctime' 		=> '2015-01-21 17:51:43',  	//DATETIME user registration
				'id' 			=> '141288',				//user ID
				'jobs_done' 	=> '10',					//Like in referrer ?
				'jobs_fail' 	=> '0',
				'money' 		=> '222.0000',				//like earned?
				'money_earned' 	=> '322.0000',				//and wtf this
				'payout_mode'	=> 'manual',
				'phone' 		=> '79896224279',			//like login
				'region' 		=> 'Люксембург',			//requester country(region?) by it IP
				'region_id'		=> '21204',					//requester country(region?) id by it IP
				'registered_at' => '21 января 2015',		//like ctime
				'ya_id' 		=> '1825'					//may be yandex id 
			),
			'referrer' => array(
				'earned' => 5,
				'jobs_done' => 1,
				'link' => 'http://superpizdato.com/?r=Skckrkekakm', //реф ссылка пользователя
				'referrals' => 1
			),
			'phone' 	=> @$data['phone']*/
		// );
		// echo '<pre>';
		// print_r($this->session->all_userdata());
		$return = json_encode($ans);
		
		$this->sendit($return);
	}
	
	function profile($action='index', $mode='default')
	{
		$requestdata = $_REQUEST; //read incomming data
		$ans = array();
		if ($action == 'index') {
			// GET /api/profile?sid=10809c97a0d11b9f5e45ab9480ff5cdf&v=f0.34.3 HTTP/1.1
			// {"referrer":{"earned":0,"jobs_done":0,"link":"http://pagetester.ru/?r=kzoouwub",      "referrals":0},"result":"ok","user":{"ctime":"2015-01-21 17:51:43","id":"141288","jobs_done":"1","jobs_fail":"0","money":"0.0000","money_earned":"2.0000","payout_mode":"manual","phone":"79896224279","region":"Люксембург","region_id":"21204","registered_at":"21 января 2015","ya_id":"1825"}}
			$user =  $this->api_model->get_user_data_by_sid($requestdata['sid']);
			$ans = array(
				'result' 	=> 'ok',
				'user' 		=> array(
					'ctime' 		=> $user['ctime'],  			//DATETIME user registration
					'id' 			=> $user['id'],					//user ID
					'jobs_done' 	=> $user['jobs_done'],			//Like in referrer ?
					'jobs_fail' 	=> $user['jobs_fail'],
					'money' 		=> $user['money'],				//like earned?
					'money_earned' 	=> $user['money_earned'],		//and wtf this
					'payout_mode'	=> $user['payout_mode'],
					'phone' 		=> $user['phone'],				//like login
					'region' 		=> $user['region'],				//requester country(region?) by it IP
					'region_id'		=> $user['region_id'],			//requester country(region?) id by it IP
					'registered_at' => $user['registered_at'],		//like ctime
					'ya_id' 		=> $user['ya_id']				//may be yandex id 
				),
				'referrer' => array(
					'earned' 	=> $user['ref_earned'],
					'jobs_done' => $user['ref_jobs_done'],
					'link' 		=> $user['ref_link'], 				//реф ссылка пользователя
					'referrals' => $user['ref_referrals']
				)
			);

			/*$ans = array(
				'referrer' => array(
					'earned' => 5,
					'jobs_done' => 1,
					'link' => 'http://superpizdato.com/?r=Skckrkekakm', //реф ссылка пользователя
					'referrals' => 1
				),
				'result' => 'ok',
				'user' => array(
					'ctime' 		=> '2015-01-21 17:51:43',  	//DATETIME user registration
					'id' 			=> '141288',				//user ID
					'jobs_done' 	=> '10',					//Like in referrer ?
					'jobs_fail' 	=> '0',
					'money' 		=> '222.0000',				//like earned?
					'money_earned' 	=> '322.0000',				//and wtf this
					'payout_mode'	=> 'manual',
					'phone' 		=> '79896224279',			//like login
					'region' 		=> 'Люксембург',			//requester country(region?) by it IP
					'region_id'		=> '21204',					//requester country(region?) id by it IP
					'registered_at' => '21 января 2015',		//like ctime
					'ya_id' 		=> '1825'					//may be yandex id 
				)
			);	*/
		}
			
		file_put_contents(APPPATH.'data/profile-'.$action.'-'.$mode, serialize($requestdata)); //DEBUG
		
		$return = json_encode($ans);
		
		$this->sendit($return);
	}

	function job($action, $param='default')
	{
		$ans = array();
		$requestdata = $_REQUEST; //read incomming data
		$user =  $this->api_model->get_user_data_by_sid($requestdata['sid']);
		switch ($action) {
			case 'get': // GET /api/job/get?muteErrors=true&sid=10809c97a0d11b9f5e45ab9480ff5cdf&v=f0.34.3 HTTP/1.1
				// {"job":null,"result":"ok"}
				$processed_job = $this->api_model->get_user_processed_job_by_user_id(@$user['id']);
				if (!empty($processed_job)) {
					$ans = array(
						'job'		=> null,
						'result' 	=> 'ok'
					);
				}
				else
				{
					$new_job = $this->api_model->get_new_job();
					if (empty($new_job))
					{
						$ans = array(
							'job'		=> null,
							'result' 	=> 'ok'
						);
					}
					else
					{
						$i=1;
						$ans = array(
							'job'		=> array(
								'id' 		=> $new_job[0]['idjob'],
								'status'	=> $new_job[0]['status'],
								'cost'		=> $new_job[0]['cost']
							)
						);
						foreach ($new_job as $step) {
							$ans['job']['steps'][$step['step']-1] = array(
								'desc'		=> $step['desc'], //may be as array. [site] - for step > 1 and take it from prev state.steps
								'type' 		=> $step['type'], 	//action type: open-serp, follow-any-site,follow-site,login-yandex,close-tab,close-prev-tab,surfing-site
								'url'		=> $step['url'],
								'url_re'	=> $step['url_re'],			//wtf??
								'url_ne'	=> $step['url_ne'],		//wtf2???
								'links'		=> unserialize($step['links']),
								'wait'		=> $step['wait'],  //how must to wait while not autofail task seconds
								'query'		=> $step['query'],
								'kw'		=> $step['kw']
							);
						}
						$ans['job']['state']['step'] = 1;
						// echo '<pre>';
						// print_r($ans);die();
					}
				}
				/*$ans = array(
					'job'		=> array(
						'id' 		=> '333',
						'status'	=> 'wait',  //"progress", "done"
						'cost'		=> '2.6',
						'steps'		=> array(	//all setps description
							1			=> array(
								'desc'		=> 'В этом задании надо научиться работать с кодом  плугина', //may be as array. [site] - for step > 1 and take it from prev state.steps
								'type' 		=> ' open-serp', 	//action type: open-serp, follow-any-site,follow-site,login-yandex,close-tab,close-prev-tab,surfing-site
								'url'		=> 'http://yandex.ru',
								'url_re'	=> 'zaycev.fm',			//wtf??
								'url_ne'	=> 'RuPark.com',		//wtf2???
								'links'		=> array( //for open-serp type
									array(
										'host'	=> 'zaycev.fm',
										'isAd'	=> true
									),
									array(
										'host'	=> 'otvet.mail.ru',
										'isAd'	=> null
									),
									array(
										'host'	=> 'RuPark.com',
										'isAd'	=> null
									)
								),
								'wait'		=> 60*60*24,  //how must to wait while not autofail task seconds
								'query'		=> 'yandsearch?lr=21204&text=йцу',
								'kw'		=> 'йцу'
							),
							2		=> array(
								'desc'		=> 'В этом задании надо научиться обрабатывать [site]',
								'type' 		=> 'open-serp',
								'url'		=> 'http://php.net',
								'links'		=> array('http://yandex.ru'),
								'wait'		=> 60*60*24
							),
						),
						/*'state'	=> array(
							'status'	=> 'done',  //"cancel" or "fail"
							'step'		=> 1,		// Current Step
							'steps'		=> array(
								1 			=> array(
									'time'		=> date('c'), //timestamp, when accepted task
									'waitDone'	=> null,		//timestamp, when complite task
									'url'		=> 'http://feth.ru',
									'links'		=> array('http://yandex.ru'),
								),
							)
						),
						'tabs'	=> array(
							'tabs' 			=> array(
								'14' => 'http://bash.in'
							),
							'urls'			=> array(
								'http://bash.in' => '14'
							),
				            'tags'			=> array(
				            	'iAmTag'		=>array(
				            		'http://bash.in' => '14'
				            	)
				            ),
				            'ignore_tabs' 	=> array(
								'14' => 'http://bash.in'
				            ),
				            'jobId' 		=> '333'
						),
					),
					'result' 	=> 'ok'
				);*/
			break;
			case 'start': // GET /api/job/start?id=1&sid=42022db7b08d9dfb485a2134846cb609&v=f0.34.5 HTTP/1.1
				$this->api_model->update_job_data($requestdata['id'],'progress');
				$this->api_model->add_user_job_data($requestdata['id'], $user['id'], 'progress');
				$ans = array(
					'result' 	=> 'ok'
				);
			break;
			case 'report': // GET /api/job/get?muteErrors=true&sid=10809c97a0d11b9f5e45ab9480ff5cdf&v=f0.34.3 HTTP/1.1
				// {"code":"report_parse_error","error":null,"result":"fail"}
				$ans = array(
					'result' 	=> 'ok'
				);
			break;
			default:
				$ans = array(
					'result' 	=> 'ok'
				);
			break;
		}

		file_put_contents(APPPATH.'data/job-'.$action.'-'.$param, serialize($requestdata)); //DEBUG

		$return = json_encode($ans);
		
		$this->sendit($return);
	}

	function feedback($action, $param='default')
	{
		$data = $_REQUEST;
		file_put_contents(APPPATH.'data/feedback-'.$action.'-'.$param, serialize($data));
	}

	function sendit($data)
	{
		$data = preg_replace_callback(				//remove RUS text escaping
		    '/\\\\u([0-9a-f]{4})/i',
		    function ($matches) {
		        $sym = mb_convert_encoding(
		                pack('H*', $matches[1]), 
		                'UTF-8', 
		                'UTF-16'
		                );
			    return $sym;
		    },
		    $data
		);
		$data = str_replace('\\/', '/',$data); 	//remove / escaping
		header("Content-Type: application/json; charset=utf-8");
		header("Content-Length: ".strlen($data));
		header("Access-Control-Allow-Methods: GET, POST");
		header("Access-Control-Allow-Origin: resource://jid1-dtklj1qrcntaiq-at-jetpack2");
		header("access-control-allow-credentials: true");
		header("Cache-Control: no-cache");
		echo $data;
	}
}
/* End of file service.php */
/* Location: ./application/controllers/service.php */