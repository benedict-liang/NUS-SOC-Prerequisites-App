<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Module_pages extends CI_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -  
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in 
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see http://codeigniter.com/user_guide/general/urls.html
	 */
	public function index()
	{	
		$this->load->helper(array('form', 'url'));
		$this->load->library('form_validation');

		$this->form_validation->set_rules('select_mods', 'Select Modules', 'callback_dropdown_check');

		if($this->form_validation->run()){
			redirect('/modules/module_tree?module_code='.set_value('select_mods'), 'refresh');
		}
		else{

			$data['dropdown_select_content'] = $this->build_dropdown_options();
			$subview['page_content'] = $this->load->view('module_graph_pages/main_page', $data, true);
			$subview['additional_scripts'] = '';
			$this->load->view('core_module_graph', $subview);	
		}
		
	}

	public function dropdown_check($str){
		if($str === null or $str === '-'){
			$this->form_validation->set_message('dropdown_check', 'The %s field must be a real module code');
			return FALSE;
		}
		else{
			return TRUE;
		}
	}

	public function build_dropdown_options(){
		$this->load->model('module_cache_model');
		$mod_list_arr = $this->module_cache_model->query_id('all_modules_list');

		if(!$mod_list_arr){
			$this->populate_db_with_modules();
			$mod_list_arr = $this->module_cache_model->query_id('all_modules_list');
		}

		$html = '<option> </option>';

		foreach($mod_list_arr['list'] as $mod_code){
			$html .= '<option>'.$mod_code.'</option>';
		}

		return $html;
	}

	public function populate_db_with_modules(){
		$url = "http://www.comp.nus.edu.sg/undergraduates/useful_course_schedule.html";
		$this->load->model('module_cache_model');
		$ch = curl_init();

		$options = array(
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true);

		curl_setopt_array($ch, $options);

		$html_code = curl_exec($ch);
		
		$pattern = "http:\/\/ivle7.nus.edu.sg\/lms\/Account\/NUSBulletin\/msearch_view.aspx\?modeCode=[A-Z]{2}[0-9]{4}&acadYear=[0-9]{4}\/[0-9]{4}&semester=[1,2]";
		preg_match_all("/".$pattern."/", $html_code, $match_arr);
		
		$arr = array();
		$json_arr = array();
		$all_modules_arr = array();
		for($i=0; $i<count($match_arr[0]); $i++){
			$arr = $this->get_module_array($match_arr[0][$i]);
			$json_arr[] = $arr;
			array_push($all_modules_arr, $arr['module_code']);
			$this->module_cache_model->write_to_cache($arr);
		}
		
		$all_modules_doc['_id'] = "all_modules_list";
		$all_modules_doc['list'] = $all_modules_arr;
		$this->module_cache_model->write_to_cache($all_modules_doc);

		//$this->output->set_content_type('application/json')
			//->set_output(json_encode($json_arr));
	}

	public function get_module_array($mod_url){

		$ch_mod = curl_init();
		$options_mod = array(
			CURLOPT_URL => $mod_url,
			CURLOPT_RETURNTRANSFER => true);
		curl_setopt_array($ch_mod, $options_mod);
		$mod_url_code = curl_exec($ch_mod);

		$url_patterns = array(
			'Module Code',
			'Module Title',
			'Description',
			'Module Credit',
			'Workload',
			'Prerequisites',
			'Preclusions',
			'Cross-listing'
			);

		$arr_keys = array(
			'module_code', 
			'module_title', 
			'module_desc',
			'module_credit',
			'module_workload',
			'module_prerequisites',
			'module_preclusions',
			'module_crosslist'
			);

		$module = array();

		for($i=0; $i<count($url_patterns); $i++){
			$start = strpos("<b>".$mod_url_code."</b>", $url_patterns[$i]);

			
			if($start!== false){
				$start = strpos($mod_url_code, "<font", $start);
				$start = strpos($mod_url_code, ">", $start);
				$start+=1;
				$end = strpos($mod_url_code, "</font>", $start);
				$module[$arr_keys[$i]] = substr($mod_url_code, $start, $end-$start);
			}
			else{
				$module[$arr_keys[$i]] = "-";
			}
		}

		$module['_id'] = $module['module_code'];
		$module['prereg'] = $this->get_prereg($module['module_prerequisites']);
		
		return $module;
	}

	public function get_prereg($prereg_string){
		$module_code_pattern = "[A-Z]{2,3}[0-9]{3,4}[A-Z]{0,2}";
		$open_brackets_pattern = "\[|\(|\{";
		$close_brackets_pattern = "\]|\)|\}";
	
		$brackets_arr = array("[", "(", "{", "]", ")", "}");
		$brackets_arr_quoted = array("\[", "\(", "\{", "\]", "\)", "\}");
		
		$result = array();
		
		//remove unneccessary symbols
		$prereg_string = preg_replace("/\.|\"|\'|AY[0-9]{4}|\\r\\n/", " ", $prereg_string);
		$prereg_string = preg_replace("/\,/", " and ", $prereg_string);
		
		//add spaces to brackets
		for($i=0; $i<3; $i++){
			$prereg_string = preg_replace("/".$brackets_arr_quoted[$i]."/", 
				$brackets_arr[$i]."  ", $prereg_string);
		}
		for($i=3; $i<6; $i++){
			$prereg_string = preg_replace("/".$brackets_arr_quoted[$i]."/", 
				" ".$brackets_arr[$i], $prereg_string);
		}
		
		//split by whitespace
		$split_arr = preg_split("/ /", $prereg_string);

		//get patterns that matter
		$split_arr = preg_grep("/".$module_code_pattern."|".$open_brackets_pattern
			."|".$close_brackets_pattern."|and|or/", $split_arr);
		


		$result = $this->get_prereg_data($split_arr);
		return $result;
	}

	public function get_prereg_data($split_arr){
		$module_code_pattern = "[A-Z]{2,3}[0-9]{3,4}[A-Z]{0,2}";
		$open_brackets_pattern = "\[|\(|\{";
		$close_brackets_pattern = "\]|\)|\}";
	
		$brackets_arr = array("[", "(", "{", "]", ")", "}");
		$brackets_arr_quoted = array("\[", "\(", "\{", "\]", "\)", "\}");

		$stack = array();
		$results = array();
		//modules with OR stay in the same array

		foreach($split_arr as $word){
			//check if it's a module code
			//if stack is empty, create new array with module code in it
			
			if(preg_match("/".$module_code_pattern."/", $word)){
				if(empty($stack)){
					array_push($stack, array($word));
				}
				else{
					$elm = array_pop($stack);
					if(is_array($elm)){
						array_push($elm, $word);
						array_push($stack, $elm);
					}
					elseif(preg_match("/or/", $elm)){
						$temp = array_pop($stack);
						if($temp !== NULL and is_array($temp)){
							array_push($temp, $word);
							array_push($stack, $temp);
						}
						elseif(!is_array($temp)){

						}
						else{
							echo "Error: module code check error for ".$word;
						}
					}
					else{
						//think about what to do with this
						array_push($stack, $word);
					}
				}

			}
			//if or, push to stack ensure no repeats
			elseif(preg_match("/^or$/", $word)){
				$elm = array_pop($stack);

				if(is_array($elm)){
					array_push($stack, $elm);
					array_push($stack, $word);
				}
				elseif($elm === NULL or preg_match("/^or$/", $elm)){
					continue;
				}
				elseif(preg_match("/".$close_brackets_pattern."/", $elm)
					and !empty($results)){
					array_push($stack, array_pop($results));
					array_push($stack, $word);
				}
				else{
					array_push($stack, $elm);
					array_push($stack, $word);
				}
			}
			//if and, clear stack
			elseif(preg_match("/^and$/", $word)){
				foreach($stack as $elm){
					if(is_array($elm)){
						array_push($results, $elm);
					}
				}
				$stack = array();
			}
			elseif(preg_match("/".$close_brackets_pattern."/", $word)){
				array_push($stack, $word);
			}
			else{
				continue;
			}
		}

		foreach($stack as $elm){
			if(is_array($elm)){
				array_push($results, $elm);
			}
		}

		return $results;
	}
}

/* End of file pages.php */
/* Location: ./application/controllers/pages.php */