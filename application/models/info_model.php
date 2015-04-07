<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Info_model  extends CI_Model 
{
 
	function __construct()
	{
		// Call the Model constructor
		parent::__construct();
	}

	function get_all_glossary_letters()
	{
		$this->db->select('UCASE(LEFT(name, 1)) as letter', FALSE);
		$this->db->where('type', 'glossary');
        $this->db->where('deleted', 0);
		$this->db->order_by('name');
		return $this->db->get('Info')->result_array();
	}

	function get_glossary_by_letter($letter)
	{
		$this->db->where('type', 'glossary');
		
        if ($letter)
        {
			if ($letter == '#')
				$this->db->where_in('UCASE(LEFT(name, 1))', array('0','1','2','3','4','5','6','7','8','9'));
			else
				$this->db->where('UCASE(LEFT(name, 1)) LIKE', $letter);
        }

        $this->db->where('deleted', 0);
		$this->db->order_by('name');
		return $this->db->get('Info')->result_array();
	}

	function search_glossary_terms($needle)
	{
		$this->db->or_like('description', $needle);
		$this->db->order_by('name');
		$this->db->where('type', 'glossary');
		$this->db->where('deleted', 0);
		$this->db->or_like('name', $needle);
		return $this->db->get('Info')->result_array();
	}

	function get_all_faq()
	{
		$this->db->where('type', 'faq');
		$this->db->where('deleted', 0);
		$this->db->order_by('name');
		return $this->db->get('Info')->result_array();
	}

	function get_all_videos()
	{
		$this->db->where('type', 'video');
		$this->db->where('deleted', 0);
		$this->db->order_by('name');
		return $this->db->get('Info')->result_array();
	}

	function get_experts_list()
	{
        $this->db->where('deleted', 0);
		return $this->db->get('Experts')->result_array();
	}

	function add_info($insdata)
	{
		return $this->db->insert('Info', $insdata);
	}
	
	function update_info($info_id, $insdata)
	{
		return $this->db->where('idInfo', $info_id)->update('Info', $insdata);
	}

	function get_info_info_by_info_id($info_id)
	{
        $this->db->where('deleted', 0);
		return $this->db->where('idInfo', $info_id)->get('Info')->row_array();
	}

    function delete_info_by_id($info_id)
    {
        $this->db->where('idInfo', $info_id);
        return $this->db->update('Info', array('deleted' => $this->session->userdata('user_id')));
    }

	function delete_expert_by_id($expert_id)
	{
		$this->db->where('idExperts', $expert_id);
		return $this->db->update('Experts', array('deleted' => $this->session->userdata('user_id')));
	}

	function get_expert_info_by_expert_id($expert_id)
	{
        $this->db->where('deleted', 0);
		return $this->db->where('idExperts', $expert_id)->get('Experts')->row_array();
	}

    function add_expert($insdata)
    {
        return $this->db->insert('Experts', $insdata);
    }

    function update_expert($expert_id, $insdata)
    {
        return $this->db->where('idExperts', $expert_id)->update('Experts', $insdata);
    }
}

/* End of file info_model.php */
/* Location: ./application/model/info_model.php */