<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
 * Custom config settings
 */
$config['user_session_timeout'] = 7200; //API settings

$config['salt'] = '9bRXdJ_0GgsV'; //API settings

$config['wall_rates'] = array(
	1 => '1h Fire Wall',
	2 => '2h Fire Wall',
	3 => '3h Fire Wall',
	4 => 'Smoke Wall',
	5 => 'Wall Not Rated'
);

$config['rates_types'] = array(
	1 => 'Smoke Rated Door',
	2 => 'Fire Rated Door'
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


