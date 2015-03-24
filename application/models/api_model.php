<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Api_model  extends CI_Model 
{
 
	function __construct()
	{
		// Call the Model constructor
		parent::__construct();
	}

	function get_user_data_by_phone($phone)
	{
		$this->db->where('phone',$phone);
		return $this->db->get('pt_users')->row_array();
	}
	
	function set_session_data($sid, $phone, $code)
	{
		$arr = array(
			'session_id' => $sid, 
			'phone'      => $phone,
			'code'       => $code,
			'expires'    => time() + $this->config->item('user_session_timeout')*1000
		);
        $insert_query = $this->db->insert_string('pt_sessions', $arr);
        $insert_query = str_replace('INSERT INTO', 'INSERT IGNORE INTO', $insert_query);
        $this->db->query($insert_query); 
	}

    function get_session_data($sid, $phone, $code)
    {
        $arr = array(
            'session_id' => $sid, 
            'phone'      => $phone,
            'code'       => $code
        );
        $this->db->where($arr);
        return $this->db->get('pt_sessions')->row_array();
    }

    function get_user_data_by_sid($sid)
    {
    	$this->db->from('pt_sessions s');
    	$this->db->where('s.session_id', $sid);
    	$this->db->join('pt_users u', 'u.phone=s.phone');
    	return $this->db->get()->row_array();
    }

    function get_user_processed_job_by_user_id($user_id)
    {
    	$this->db->from('pt_user_jobs uj');
    	$this->db->join('pt_jobs j', 'uj.pt_jobs_idjob=j.idjob');
        $this->db->where('uj.pt_users_id', $user_id);
        $this->db->where('uj.status', 'progress');
    	return $this->db->get()->result_array();
    }

    function get_new_job()
    {
    	$this->db->from('pt_jobs j');
    	$this->db->join('pt_job_steps js', 'js.pt_jobs_idjob = j.idjob');
    	$this->db->join('pt_steps st', 'st.idstep=js.pt_steps_idstep');
    	$this->db->where('j.status', 'wait');
    	$this->db->order_by('j.idjob', 'asc');
    	return $this->db->get()->result_array();
    }

    function update_user_data($user_id,$update_data)
    {
        $this->db->where('id', $user_id);
        $this->db->update('pt_users', $update_data);
    }
    
    function update_job_data($job_id,$status)
    {
        $this->db->where('idjob', $job_id);
        $this->db->update('pt_jobs', array('status' => $status));
    }
    
    function add_user_job_data($job_id, $user_id, $status)
    {
        $array = array(
            'pt_jobs_idjob' => $job_id,
            'pt_users_id'   => $user_id,
            'status'        => $status
        );
        $this->db->insert('pt_user_jobs', $array);
    }

}