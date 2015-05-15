<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
 * Custom config settings
 */
$config['user_session_timeout'] = 7200; //API settings

$config['salt'] = '9bRXdJ_0GgsV'; //API settings

$config['wall_rates'] = array(
	// 1 => '1h Fire Wall',
	1 => '1 Hour',
	// 2 => '2h Fire Wall',
	2 => '2 Hour',
	// 3 => '3h Fire Wall',
	3 => '3 Hour',
	// 4 => 'Smoke Wall',
	// 5 => 'Wall Not Rated'
	4 => 'Non-Rated Wall'
);

$config['rates_types'] = array(
	// 1 => 'Smoke Rated Door',
	1 => 'Yes',
	// 2 => 'Fire Rated Door'
	2 => 'No'
);

$config['door_rating'] = array(
	1 => '0',
	2 => '20',
	3 => '45',
	4 => '60',
	5 => '90',
	6 => '180'
);

$config['door_matherial'] = array(
	1 => 'Wood (Particle Board)',
	2 => 'Wood (Mineral Core)',
	3 => 'Hollow Metal',
	4 => 'Glass',
	5 => 'Lead Lined',
	6 => 'Aluminum'
);

$config['door_state'] = array(
	1 => 'Compliant',
	2 => 'Maintenance',
	3 => 'Repair',
	4 => 'Replace',
	5 => 'Recertify'
);


$config['four_params_conditions'] = array(
	// 'Smoke Rated Door' => array(
	'Yes' => array(
		'Wood (Particle Board)'	=> array('20 Minute (Smoke)' => 1),
		'Wood (Mineral Core)'	=> array('45 Minute (Smoke)' => 1, '60 Minute (Smoke)' => 1, '90 Minute (Smoke)' => 1),
		'Hollow Metal'			=> array('20 Minute (Smoke)' => 1, '45 Minute (Smoke)' => 1, '60 Minute (Smoke)' => 1, '90 Minute (Smoke)' => 1, '180 Minute (Smoke)' => 1),
		'Glass'					=> array('20 Minute (Smoke)' => 1, '45 Minute (Smoke)' => 1, '60 Minute (Smoke)' => 1, '90 Minute (Smoke)' => 1),
		'Lead Lined'			=> array('20 Minute (Smoke)' => 1, '45 Minute (Smoke)' => 1)
	),
	// 'Fire Rated Door'  => array(
	'No'  => array(
		'Wood (Particle Board)'	=> array('20 Minute' => 1),
		'Wood (Mineral Core)'	=> array('45 Minute' => 1, '60 Minute' => 1, '90 Minute' => 1),
		'Hollow Metal'			=> array('20 Minute' => 1, '45 Minute' => 1, '60 Minute' => 1, '90 Minute' => 1, '180 Minute' => 1),
		'Glass'					=> array('20 Minute' => 1, '45 Minute' => 1, '60 Minute' => 1, '90 Minute' => 1),
		'Lead Lined'			=> array('20 Minute' => 1, '45 Minute' => 1)
	)
);

$config['min_req_door_rating'] = array(
	'1 Hour'	 		=> array('Yes' => '45 Minute Smoke', 'No' => '45 Minute'),
	'2 Hour'	 		=> array('Yes' => '90 Minute Smoke', 'No' => '90 Minute'),
	'3 Hour'	 		=> array('Yes' => '180 Minute Smoke', 'No' => '180 Minute'),
	'Non-Rated Wall' 	=> array('Yes' => 'Smoke', 'No' => 'N/A'),
);