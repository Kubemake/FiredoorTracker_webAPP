<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

function verifyLogged($type='user') //can be checked exactly for admin rgihts if send type=admin
{
	$CI = & get_instance();
	$CI->session->set_flashdata('refferer', current_url());

	$cook = unserialize($CI->input->cookie('islogged')); //собираем куки  //надо сделать чтобы данные в сессию свежие попадали а не из куки старые!!
	if (isset($cook['islogged']) && $cook['islogged']>0)
	{
		$CI->session->set_userdata($cook); //сохраняем в сессию куки
	 	return TRUE;
	}
	$logged_in = $CI->session->userdata('islogged');
	$is_admin = $CI->session->userdata('isadmin');

	if ($logged_in == FALSE)
	{
		die('<script type="text/javascript">window.location = "/user/login"</script>');
		// redirect('/user/login', 'refresh');
		// return FALSE;
	}
	if ($type == 'admin' && $is_admin < 1) {
		die('<script type="text/javascript">window.location = "/user/login"</script>');
		// redirect('/user/login', 'refresh');
		// return FALSE;
	}
	return TRUE;
}

function addDataTable($type='css')
{
	if ($type=='css')
	{
		return '<link rel="stylesheet" type="text/css" href="//cdn.datatables.net/plug-ins/3cfcc339e89/integration/bootstrap/3/dataTables.bootstrap.css">
			<link rel="stylesheet" type="text/css" href="//cdn.datatables.net/responsive/1.0.3/css/dataTables.responsive.css">';
	}
	else
	{
		return '<script type="text/javascript" src="//cdn.datatables.net/1.10.4/js/jquery.dataTables.min.js"></script>
			<script type="text/javascript" src="//datatables.net/release-datatables/extensions/TableTools/js/dataTables.tableTools.min.js"></script>
			<script type="text/javascript" src="//cdn.datatables.net/responsive/1.0.3/js/dataTables.responsive.min.js"></script>
			<script type="text/javascript" src="//cdn.datatables.net/plug-ins/3cfcc339e89/integration/bootstrap/3/dataTables.bootstrap.min.js"></script>
			<script type="text/javascript">
				$(document).ready(function() {
					dtable = $(\'.table\').dataTable({
						responsive: true,
						paging: false,
						info: false,
						dom: \'T<"clear">lfrtip\',
						tableTools: {
							"sRowSelect": "single",
							"aButtons": []
						}
					});
				});
			</script>';
	}
}

function send_mail($to, $subj='', $body='')
{
	return mail($to, $subj, $body, "From: " . $_SERVER['HTTP_HOST'] . "\r\n" . "Content-type: text/html; charset=UTF-8" . "\r\n");
}

function msg($type, $text)
{
	return '<div class="alert alert-'.$type.' alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>'.$text.'</div>';
}

function jsonencode($data)
{
	$data = json_encode($data);
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
	return $data;
}

function pass_crypt($pass)
{
	$CI = & get_instance();
	return md5($CI->config->item('salt') . $pass . $CI->config->item('salt') . 'u');
}

function make_buildings_dropdown($buildingsdata)
{
	$output = '<ul class="dropdown-menu multi-level" role="menu" aria-labelledby="dropdownMenu">' . "\n";

	foreach ($buildingsdata as $element) {

		if ($element['parent'] != 0) continue;
		
		$result = '';

		$result = _make_buildings_dropdown_childs($element['idBuildings']);

		if (!empty($result))
		{
			$output .= '<li class="dropdown-submenu"><a tabindex="-1" data-id="' . $element['idBuildings'] . '" href="#">' . $element['name'] . '</a>' . "\n";
			$output .= $result;
			$output .= '</li>' . "\n";
		}
		else
		{
			$output .= '<li><a data-id="' . $element['idBuildings'] . '" href="#">' . $element['name'] . '</a></li>' . "\n";
		}
		
	}
	$output .= '</ul>' . "\n";

	return $output;
}

function make_children_answers($root_question, $question_id, $issues)
{
	$output = '';
	if (isset($issues['issues'][$question_id]['answers']))
	{
		$output = '<ul class="dropdown-menu noclose pull-middle pull-right" data-label-placement="false"  data-placeholder="false">';
		foreach ($issues['issues'][$question_id]['answers'] as $answer)
		{
			$output .= '<li' . (($answer['nextQuestionId'] != $root_question) ? ' class="dropdown-submenu"' : '') . '>';
			$output .= '<input 
		    				type="checkbox" 
		    				id="id' . $answer['name'] . '" 
		    				name="' . $answer['name'] . '" 
		    				value="' . $answer['idFormFields'] . '" 
		    				' . ((!empty($answer['selected'])) ? ' checked="checked"' : '') . '
		    			>
		    			<label for="id' . $answer['name'] . '">' . (($answer['nextQuestionId'] != $root_question) ? '<a href="#" tabindex="-2" data-toggle="dropdown">' . $answer['label'] . '</a>': $answer['label']);

			if ($answer['label'] == 'Other' && $answer['nextQuestionId'] == $root_question)
			{
				$output .= ' <input class="form-control" text" type="text" style="display: inline;width: auto;" value="' .@$answer['selected'] . '" onkeyup="$(\'#' . $answer['name'] .'tex\').val($(this).val())">';
			}

			$output .= '</label>';
		    
		    if ($answer['nextQuestionId'] != $root_question)
		    	$output .= make_children_answers($root_question, $answer['nextQuestionId'], $issues);

		    $output .= '</li>';
		}
		$output .= '</ul>';
	}
	
	// file_put_contents($_SERVER['DOCUMENT_ROOT'].'/out', '###'.$output);die;
	return $output;
}

function _make_buildings_dropdown_childs($parent_id)
{
	$CI = & get_instance();
	$CI->load->model('user_model');
	$buildings = $CI->user_model->get_all_buildings_by_parent($parent_id);

		if (empty($buildings)) return '';

	$output = '<ul class="dropdown-menu">' . "\n";
	foreach ($buildings as $building)
	{
		$result = _make_buildings_dropdown_childs($building['idBuildings']);

		if (!empty($result))
		{
			$output .= '<li class="dropdown-submenu"><a tabindex="-1" data-id="' . $building['idBuildings'] . '" href="#">' . $building['name'] . '</a>' . "\n";
			$output .= $result;
			$output .= '</li>' . "\n";
		}
		else
		{
			$output .= '<li><a data-id="' . $building['idBuildings'] . '" href="#">' . $building['name'] . '</a></li>' . "\n";
		}
	}
	$output .= '</ul>' . "\n";

	return $output;
}

function translate($str)
{
	// $str = (iconv('UTF-8','UTF-8',$str)==$str) ? $str : iconv('Windows-1251','UTF-8',$str);
	// $str = mb_strtolower(_clear($str), 'UTF-8');
	// $str = _clear($str);
	$pattern = array
	(
		'а' => 'a',		'б' => 'b',		'в' => 'v',
		'г' => 'g',		'д' => 'd',		'е' => 'e',
		'ё' => 'e',		'ж' => 'zh',	'з' => 'z',
		'и' => 'i',		'й' => 'y',		'к' => 'k',
		'л' => 'l',		'м' => 'm',		'н' => 'n',
		'о' => 'o',		'п' => 'p',		'р' => 'r',
		'с' => 's',		'т' => 't',		'у' => 'u',
		'ф' => 'f',		'х' => 'h',		'ц' => 'c',
		'ч' => 'ch',	'ш' => 'sh',	'щ' => 'sch',
		'ь' => '',		'ы' => 'y',		'ъ' => '',
		'э' => 'e',		'ю' => 'yu',	'я' => 'ya',
		'А' => 'a',		'Б' => 'b',		'В' => 'v',
		'Г' => 'g',		'Д' => 'd',		'Е' => 'e',
		'Ё' => 'e',		'Ж' => 'zh',	'З' => 'z',
		'И' => 'i',		'Й' => 'y',		'К' => 'k',
		'Л' => 'l',		'М' => 'm',		'Н' => 'n',
		'О' => 'o',		'П' => 'p',		'Р' => 'r',
		'С' => 's',		'Т' => 't',		'У' => 'u',
		'Ф' => 'f',		'Х' => 'h',		'Ц' => 'c',
		'Ч' => 'ch',	'Ш' => 'sh',	'Щ' => 'sch',
		'Ь' => '',		'Ы' => 'y',		'Ъ' => '',
		'Э' => 'e',		'Ю' => 'yu',	'Я' => 'ya'
	);
	$str = str_replace('  ', ' ',strtr($str, $pattern));
	$str = clear($str);
	$str = str_replace(' ', '-', $str);
	return strtolower($str);
}

function clear($query)
{
	$pattern = array
	(
		'+',	'—',	'.',	',',	'=',
		'?',	'%',	';',	':',	'^',
		'$',	'#',	'!',	'@',	'№',
		'_',	'/',	'|',	'[',	']',
		'{',	'}',	'&',	'*',	'(',
		')',	'<',	'>',	'-',	'"',
		'»',	'«',	'~',	'`',	"'",
		'amp',	'nbsp',	'quot',	'®',	'©',
		'™',	'•',	'¦',	'?',	'·',
		'›',	'?'
	);
	$query = str_replace($pattern, ' ', $query);
	return preg_replace('|\s+|', ' ', trim($query));
}

function has_permission($rule_name)
{
	$CI = & get_instance();
	$CI->db->select('rr.value');
	$CI->db->from('Rules r');
	$CI->db->join('RolesRules rr', 'rr.idRules = r.idRules');
	$CI->db->where('rr.idRoles', $CI->session->userdata('user_role'));
	$CI->db->where('rr.UserId', $CI->session->userdata('user_parent'));
	$CI->db->where('r.name', $rule_name);
	$result = $CI->db->get()->row_array();
	if (isset($result['value']) && !empty($result['value']) && $result['value'] > 0)
		return TRUE;

	return FALSE;
}
 
function get_image_by_height($full_img_path, $height, $action, $width=false) //image path from root site path like /images/12/img.jpg
{
	$CI = & get_instance();
	if (empty($full_img_path)) return FALSE;
	$CI->load->library('image_lib');
	
	$url = explode('/', $full_img_path);
	$img = array_pop($url);
	$url = implode('/', $url) . '/';

	preg_match('#(.*)\.([\w]+)$#si', $img, $img);

	$newfile = $_SERVER['DOCUMENT_ROOT'] . $url . $img[1] . '_' . $height . '.' . $img[2];

	if (!file_exists($newfile))
	{
		$config['source_image'] = $_SERVER['DOCUMENT_ROOT'] . $full_img_path;
		$config['new_image'] = $newfile;
		$config['create_thumb'] = FALSE;
		$config['maintain_ratio'] = TRUE;
		$config['height'] = $height;
		if ($width) 
		{
			$config['width'] = $width;
			$config['maintain_ratio'] = FALSE;
		}
		else
		{
			$sizes = getimagesize($_SERVER['DOCUMENT_ROOT'] . $full_img_path);
			$a = $sizes[0] / $sizes[1];
			$w = ceil($height * $a);
			$config['width'] = $w;
		}
		$CI->image_lib->initialize($config);
		if ( !$CI->image_lib->{$action}())
		    echo $CI->image_lib->display_errors();
		$CI->image_lib->clear();
	}

	$url = $url . $img[1] . '_' . $height . '.' . $img[2];
	return $url;
}

function file_force_download($file, $name) {
    // сбрасываем буфер вывода PHP, чтобы избежать переполнения памяти выделенной под скрипт
    // если этого не сделать файл будет читаться в память полностью!
    if (ob_get_level()) {
      ob_end_clean();
    }
    // заставляем браузер показать окно сохранения файла
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename=' . $name);
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($file));
    // читаем файл и отправляем его пользователю
    readfile($file);
	exit();
}

function data_force_download($data, $name) {
    // сбрасываем буфер вывода PHP, чтобы избежать переполнения памяти выделенной под скрипт
    // если этого не сделать файл будет читаться в память полностью!
    if (ob_get_level()) {
      ob_end_clean();
    }
    // заставляем браузер показать окно сохранения файла
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename=' . $name);
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . strlen($data));
    // читаем файл и отправляем его пользователю
    print($data);
	exit();
}

/* End of file common_helper.php */
/* Location: ./system/helpers/common_helper.php */