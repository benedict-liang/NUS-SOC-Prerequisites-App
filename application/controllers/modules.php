<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Modules extends CI_Controller {

	public function module_tree($module_code = 'error'){
		
		if(isset($_GET['module_code'])){
			$module_code = $_GET['module_code'];
		}

		$info_arr = $this->build_module_info($module_code);

		$data['module_title'] = $info_arr[0];
		$data['module_info_content'] = $info_arr[1];
	
		$subview['page_content'] = $this->load->view('module_graph_results/module_tree', $data, true);

		$graph_function = '$(document).ready(function() {setup_graph("'.$module_code.'", "'.base_url().'")});';
		$subview['additional_scripts'] = '<script type="text/javascript">'.$graph_function.'</script>';
		$this->load->view('core_module_graph', $subview);
	}

	public function build_module_info($module_code = 'error'){

		$this->load->model('module_cache_model');
		$mod = $this->module_cache_model->query_module_code($module_code);

		$key_title = array(
			'Description',
			'Module Credit',
			'Workload',
			'Prerequisites',
			'Preclusions',
			'Cross-listing'
			);

		$arr_keys = array(
			'module_desc',
			'module_credit',
			'module_workload',
			'module_prerequisites',
			'module_preclusions',
			'module_crosslist'
			);

		//Module Information Title
		$html_title = '<div class="mod_title">';
		$html_title .= '<h1>'.$mod['module_code'].' - '.$mod['module_title'].'</h1>';
		$html_title .= '</div>';
		
		//Rest of Module Information
		$html = '<div class="mod_info">';

		for($i=0; $i<count($arr_keys); $i++){
			$html .= '<h2>'.$key_title[$i].'</h2>';
			$html .= '<h3>'.$mod[$arr_keys[$i]].'</h3>';
		}

		$html .= '</div>';

		$html_arr = array($html_title, $html);

		return $html_arr;
	}



	public function get_module_list($module_code = 'error'){

		if(isset($_GET['module_code'])){
			$module_code = $_GET['module_code'];
		}
		
		$this->load->model('prereg_model');
		$cached_mod = $this->prereg_model->query_module_code($module_code);

		if(!$cached_mod){

			$this->load->model('module_cache_model');
			$mod = $this->module_cache_model->query_module_code($module_code);

			$prereg_arr = $mod['prereg'];

			if(empty($prereg_arr)){
				echo null;
			}
			else{
				$result_array = array();
				$num = 1;
				foreach($prereg_arr as $prereg_sub){
					$temp_arr = $this->process_submods($module_code, $prereg_sub, $num);
					
					//stores flattened array
					foreach($temp_arr as $temp){
						array_push($result_array, $temp);
					}

					$num++;
				}
				$cache_arr = array();
				$cache_arr['_id'] = $module_code;
				$cache_arr['module_code'] = $module_code;
				$cache_arr['data'] = $result_array;

				$this->prereg_model->write_to_cache($cache_arr);

				echo json_encode($result_array);
			}
			//$this->output->set_content_type('application/json')
				//->set_output(json_encode($result_array));
		}
		else{

			echo json_encode($cached_mod['data']);
		}
	}

	//Recursively get data
	public function process_submods($parent_mod, $mod_array, $num){
		$arr = array();
		
		foreach($mod_array as $mod){
			array_push($arr, array($mod, $parent_mod, $num));

			$mod = $this->module_cache_model->query_module_code($mod);
			$prereg_arr = $mod['prereg'];

			if(!empty($prereg_arr)){
				$num = 1;
				foreach($prereg_arr as $prereg_sub){
					$temp_arr = $this->process_submods($mod['module_code'], $prereg_sub, $num);
					
					//stores flattened array
					foreach($temp_arr as $temp){
						array_push($arr, $temp);
					}
					$num++;
				}
			}
		}

		return $arr;
	}
}