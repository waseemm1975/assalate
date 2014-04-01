<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class timing extends CI_Controller {

	/**
	 * Follow these first examples.
	 *

	 */
	
	public function Get()
	{
		
		$url = $this->input->get('url');
		$debug = $this->input->get('debug');
		$http_test = substr($url, 0, 7);
		
		if($http_test !== 'http://'){
			$url = 'http://'.$url;
		}
		
		$data['prayer_times'] = $this->scrap_that($url);
				
		if(empty($debug)){	
			$this->load->view('encode', $data);
		}else {
			$this->load->view('print', $data);
		}
	}
	
	
	///////////////////////////////////////////////////////////////////////////////////////
	//
	// you have nothing to change below, it's all processing and communication processors //
	//
	///////////////////////////////////////////////////////////////////////////////////////
	
	private function scrap_that($url, $iframe_url = 0)
	{
				
		$this->wlwg->autoload();
							
		$param_table = new wlWgParam( 
		  '<table'
		);
		$param_table->setTagContains('faj');

		$param_iframes = new wlWgParam( 
		  '<iframe'
		);
		
		$params = array($param_table,$param_iframes);
		
		$wc = new wlWgProcessor( 
			$url, 
			$params
		);
		
		$raw = $wc->get();
		
		$raw_iframes = implode('', $raw[1]);
		
		$raw = implode('', $raw[0]);
		
		$cleared = $this->build_arr($raw);
				
		$locate = $this->analyse_locate($cleared);
			
			/*
			///// this line is only for debuging purposes
			echo '<pre>';
			
			$cc_data = array(
				'raw' => htmlspecialchars($raw), 
				'iframe' => htmlspecialchars($raw_iframes), 
				'cleared' => $cleared, 
				'locate' => $locate
			);
			
			print_r($cc_data);
			
			die();
			*/
		
		if(empty($locate)){
						
			if(empty($iframe_url)){
			
				$iframe_urls = $this->custom_functions->matchi_koulchi('/src="(.*?)"/ms', $raw_iframes, 1);
				
				if(empty($iframe_urls)){
					$iframe_urls = $this->custom_functions->matchi_koulchi("/src='(.*?)'/ms", $raw_iframes, 1);
				}
				
				if(!empty($iframe_urls)){
					
					foreach($iframe_urls as $single_url){
					
						$iframe_analysis[] = $this->scrap_that($single_url,1);
					}
					
					array_filter($iframe_analysis);
					
					if(empty($iframe_analysis)){
					
						return array('response_status' => 'no prayer times on site');
						
					}else {
						
						$iframe_keys = array_keys($iframe_analysis);
					
						return array($iframe_analysis[$iframe_keys[0]]);
					}	
					
				}else {
				
					return array('response_status' => 'no prayer times on site');
					
				}
				
			}else {
			
				return 0;
				
			}
			
		}else {
			
			$filterd_loc =  array_filter($locate);
			
			if(count($filterd_loc) > 3){
			
				return array('response_status' => 'ok', 'response1' => $filterd_loc);
			
			}else {
			
				return array('response_status' => 'Bad data format / unable to get clean data');
			
			}
			
		}
			
	}
	
	private function build_arr($raw)
	{
		
		$rez = array();
		
		$tables = $this->custom_functions->matchi_koulchi('/<table(.*?)<\/table/msi', $raw, 0);
		
		if(!empty($tables)){
		
			foreach ($tables as $tKey => $s_table){
			
				$rows = $this->custom_functions->matchi_koulchi('/<tr(.*?)<\/tr/msi', $s_table, 0);
				
				if(!empty($rows)){
				
					foreach ($rows as $rKey => $s_row){
					
						$cellules = $this->custom_functions->matchi_koulchi('/<td(.*?)<\/td/msi', $s_row, 0);						
						
						if(!empty($cellules)){
						
							$clean_cells = array();
							
							foreach ($cellules as $cel){
							
									$clean_cells[] = trim(strip_tags($cel));
							
							}
							
							$rez[$tKey][$rKey] = $clean_cells;
						
						}
					
					}
				}	
			
			}
		
		}
		
		return $rez;
		
	}
		
	private function analyse_locate($arr)
	{
		$rez = array();
		
		$key_map = array_keys($arr);
		
		foreach($key_map as $key){
			
			if(empty($rez)){
			
				if(isset($arr[$key][0])){
				
					$pos = $this->fajr_test($arr[$key][0][0]);
					
					$pos2 = $this->fajr_test($arr[$key][1][0]);
					
					$pos3 = $this->fajr_test($arr[$key][2][0]);
				
					if ($pos == TRUE) {
					
						foreach($arr[$key] as $key => $trueRow){
							if($key < 9) {
								if(isset($trueRow[2])){
									$rez[$trueRow[0]] = array('Salah' => $trueRow[1], 'Iqama' => $trueRow[2]);
								}else {
									$rez[$trueRow[0]] = $trueRow[1];
								}	
							}
						}
					
					}else if($pos2 == TRUE) {
						
						unset($arr[$key][0]);
						
						foreach($arr[$key] as $key => $trueRow){
							
							if($key < 9) {
								if(isset($trueRow[2])){
									$rez[$trueRow[0]] = array('Salah' => $trueRow[1], 'Iqama' => $trueRow[2]);
								}else {
									$rez[$trueRow[0]] = $trueRow[1];
								}	
							}
						}
					
					}else if($pos3 == TRUE) {
						
						unset($arr[$key][0]);
						unset($arr[$key][1]);
						
						foreach($arr[$key] as $key => $trueRow){
							
							if($key < 9) {
								if(isset($trueRow[2])){
										$rez[$trueRow[0]] = array('Salah' => $trueRow[1], 'Iqama' => $trueRow[2]);
								}else {
									$rez[$trueRow[0]] = $trueRow[1];
								}
							}
						}
					
					}
					
				
				}else if(isset($arr[$key][1])){
				
					$pos = $this->fajr_test($arr[$key][1][0]);
					$pos2 = $this->fajr_test($arr[$key][2][0]);
					$pos3 = $this->fajr_test($arr[$key][3][0]);
				
					if ($pos == TRUE) {
					
						foreach($arr[$key] as $key => $trueRow){
							if($key < 9) {
								if(isset($trueRow[2])){
									$rez[$trueRow[0]] = array('Salah' => $trueRow[1], 'Iqama' => $trueRow[2]);
								}else {
									$rez[$trueRow[0]] = $trueRow[1];
								}
							}
						}
					
					}else if($pos2 == TRUE) {
						
						unset($arr[$key][1]);
						
						foreach($arr[$key] as $key => $trueRow){
							if($key < 9) {
								if(isset($trueRow[2])){
									$rez[$trueRow[0]] = array('Salah' => $trueRow[1], 'Iqama' => $trueRow[2]);
								}else {
									$rez[$trueRow[0]] = $trueRow[1];
								}
							}
						}
					
					}else if($pos3 == TRUE) {
						
						unset($arr[$key][1]);
						unset($arr[$key][2]);
						
						foreach($arr[$key] as $key => $trueRow){
							if($key < 9) {
								if(isset($trueRow[2])){
									$rez[$trueRow[0]] = array('Salah' => $trueRow[1], 'Iqama' => $trueRow[2]);
								}else {
									$rez[$trueRow[0]] = $trueRow[1];
								}
							}
						}
					
					}
				
				}else if(isset($arr[$key][2])){
				
					$pos = $this->fajr_test($arr[$key][2][0]);
					$pos2 = $this->fajr_test($arr[$key][3][0]);
					$pos3 = $this->fajr_test($arr[$key][4][0]);
					
					if ($pos == TRUE) {
					
						foreach($arr[$key] as $key => $trueRow){
							if($key < 9) {
								if(isset($trueRow[2])){
									$rez[$trueRow[0]] = array('Salah' => $trueRow[1], 'Iqama' => $trueRow[2]);
								}else {
									$rez[$trueRow[0]] = $trueRow[1];
								}
							}
						}
					
					}else if($pos2 == TRUE) {
						
						unset($arr[$key][2]);
						
						foreach($arr[$key] as $key => $trueRow){
							if($key < 9) {
								if(isset($trueRow[2])){
									$rez[$trueRow[0]] = array('Salah' => $trueRow[1], 'Iqama' => $trueRow[2]);
								}else {
									$rez[$trueRow[0]] = $trueRow[1];
								}
							}
						}
					
					}else if($pos3 == TRUE) {
						
						unset($arr[$key][2]);
						unset($arr[$key][3]);
						
						foreach($arr[$key] as $key => $trueRow){
							if($key < 9) {
								if(isset($trueRow[2])){
									$rez[$trueRow[0]] = array('Salah' => $trueRow[1], 'Iqama' => $trueRow[2]);
								}else {
									$rez[$trueRow[0]] = $trueRow[1];
								}
							}
						}
					
					}
				
				}else if(isset($arr[$key][3])){
				
					$pos = $this->fajr_test($arr[$key][3][0]);
					$pos2 = $this->fajr_test($arr[$key][4][0]);
					$pos3 = $this->fajr_test($arr[$key][5][0]);
					
					if ($pos == TRUE) {
					
						foreach($arr[$key] as $key => $trueRow){
							if($key < 9) {
								if(isset($trueRow[2])){
									$rez[$trueRow[0]] = array('Salah' => $trueRow[1], 'Iqama' => $trueRow[2]);
								}else {
									$rez[$trueRow[0]] = $trueRow[1];
								}
							}
						}
					
					}else if($pos2 == TRUE) {
						
						unset($arr[$key][4]);
						
						foreach($arr[$key] as $key => $trueRow){
							if($key < 9) {
								if(isset($trueRow[2])){
									$rez[$trueRow[0]] = array('Salah' => $trueRow[1], 'Iqama' => $trueRow[2]);
								}else {
									$rez[$trueRow[0]] = $trueRow[1];
								}
							}
						}
					
					}else if($pos3 == TRUE) {
						
						unset($arr[$key][4]);
						unset($arr[$key][5]);
						
						foreach($arr[$key] as $key => $trueRow){
							if($key < 9) {
								if(isset($trueRow[2])){
									$rez[$trueRow[0]] = array('Salah' => $trueRow[1], 'Iqama' => $trueRow[2]);
								}else {
									$rez[$trueRow[0]] = $trueRow[1];
								}
							}
						}
					
					}
				
				}else if(isset($arr[$key][4])){
				
					$pos = $this->fajr_test($arr[$key][4][0]);
					$pos2 = $this->fajr_test($arr[$key][5][0]);
					$pos3 = $this->fajr_test($arr[$key][6][0]);
					
					if ($pos == TRUE) {
					
						foreach($arr[$key] as $key => $trueRow){
							if($key < 9) {
								if(isset($trueRow[2])){
									$rez[$trueRow[0]] = array('Salah' => $trueRow[1], 'Iqama' => $trueRow[2]);
								}else {
									$rez[$trueRow[0]] = $trueRow[1];
								}
							}
						}
					
					}else if($pos2 == TRUE) {
						
						unset($arr[$key][4]);
						
						foreach($arr[$key] as $key => $trueRow){
							if($key < 9) {
								if(isset($trueRow[2])){
									$rez[$trueRow[0]] = array('Salah' => $trueRow[1], 'Iqama' => $trueRow[2]);
								}else {
									$rez[$trueRow[0]] = $trueRow[1];
								}
							}
						}
					
					}else if($pos3 == TRUE) {
						
						unset($arr[$key][4]);
						unset($arr[$key][5]);
						
						foreach($arr[$key] as $key => $trueRow){
							if($key < 9) {
								if(isset($trueRow[2])){
									$rez[$trueRow[0]] = array('Salah' => $trueRow[1], 'Iqama' => $trueRow[2]);
								}else {
									$rez[$trueRow[0]] = $trueRow[1];
								}
							}
						}
					
					}
				
				}else if(isset($arr[$key][5])){
				
					$pos = $this->fajr_test($arr[$key][5][0]);
					$pos2 = $this->fajr_test($arr[$key][6][0]);
					$pos3 = $this->fajr_test($arr[$key][7][0]);
					
					if ($pos == TRUE) {
					
						foreach($arr[$key] as $key => $trueRow){
							if($key < 9) {
								if(isset($trueRow[2])){
									$rez[$trueRow[0]] = array('Salah' => $trueRow[1], 'Iqama' => $trueRow[2]);
								}else {
									$rez[$trueRow[0]] = $trueRow[1];
								}
							}
						}
					
					}else if($pos2 == TRUE) {
						
						unset($arr[$key][5]);
						
						foreach($arr[$key] as $key => $trueRow){
							if($key < 9) {
								if(isset($trueRow[2])){
									$rez[$trueRow[0]] = array('Salah' => $trueRow[1], 'Iqama' => $trueRow[2]);
								}else {
									$rez[$trueRow[0]] = $trueRow[1];
								}
							}
						}
					
					}else if($pos3 == TRUE) {
						
						unset($arr[$key][5]);
						unset($arr[$key][6]);
						
						foreach($arr[$key] as $key => $trueRow){
							if($key < 9) {
								if(isset($trueRow[2])){
									$rez[$trueRow[0]] = array('Salah' => $trueRow[1], 'Iqama' => $trueRow[2]);
								}else {
									$rez[$trueRow[0]] = $trueRow[1];
								}
							}
						}
					
					}
				
				}else if(isset($arr[$key][6])){
				
					$pos = $this->fajr_test($arr[$key][6][0]);
					$pos2 = $this->fajr_test($arr[$key][7][0]);
					$pos3 = $this->fajr_test($arr[$key][8][0]);
					
					if ($pos == TRUE) {
					
						foreach($arr[$key] as $key => $trueRow){
							if($key < 9) {
								if(isset($trueRow[2])){
									$rez[$trueRow[0]] = array('Salah' => $trueRow[1], 'Iqama' => $trueRow[2]);
								}else {
									$rez[$trueRow[0]] = $trueRow[1];
								}
							}
						}
					
					}else if($pos2 == TRUE) {
						
						unset($arr[$key][6]);
						
						foreach($arr[$key] as $key => $trueRow){
							if($key < 9) {
								if(isset($trueRow[2])){
									$rez[$trueRow[0]] = array('Salah' => $trueRow[1], 'Iqama' => $trueRow[2]);
								}else {
									$rez[$trueRow[0]] = $trueRow[1];
								}
							}
						}
					
					}else if($pos3 == TRUE) {
						
						unset($arr[$key][6]);
						unset($arr[$key][7]);
						
						foreach($arr[$key] as $key => $trueRow){
							if($key < 9) {
								if(isset($trueRow[2])){
									$rez[$trueRow[0]] = array('Salah' => $trueRow[1], 'Iqama' => $trueRow[2]);
								}else {
									$rez[$trueRow[0]] = $trueRow[1];
								}
							}
						}
					
					}
				
				}else if(isset($arr[$key][7])){
				
					$pos = $this->fajr_test($arr[$key][7][0]);
					$pos2 = $this->fajr_test($arr[$key][8][0]);
					$pos3 = $this->fajr_test($arr[$key][9][0]);
					
					if ($pos == TRUE) {
					
						foreach($arr[$key] as $key => $trueRow){
							if($key < 9) {
								if(isset($trueRow[2])){
									$rez[$trueRow[0]] = array('Salah' => $trueRow[1], 'Iqama' => $trueRow[2]);
								}else {
									$rez[$trueRow[0]] = $trueRow[1];
								}
							}
						}
					
					}else if($pos2 == TRUE) {
						
						unset($arr[$key][7]);
						
						foreach($arr[$key] as $key => $trueRow){
							if($key < 9) {
								if(isset($trueRow[2])){
									$rez[$trueRow[0]] = array('Salah' => $trueRow[1], 'Iqama' => $trueRow[2]);
								}else {
									$rez[$trueRow[0]] = $trueRow[1];
								}
							}
						}
						
					}else if($pos3 == TRUE) {
						
						unset($arr[$key][7]);
						unset($arr[$key][8]);
						
						foreach($arr[$key] as $key => $trueRow){
							if($key < 9) {
								if(isset($trueRow[2])){
									$rez[$trueRow[0]] = array('Salah' => $trueRow[1], 'Iqama' => $trueRow[2]);
								}else {
									$rez[$trueRow[0]] = $trueRow[1];
								}
							}
						}
					
					}
				
				}
			
			}
			
		}
		
		return $rez;
		
	}
	
	private function fajr_test($string)
	{
	
		$result = strpos($string, 'ajr');
		
		if($result == FALSE){
		
			$result = strpos($string, 'ajir');
		
		}
		
		if($result == FALSE){
		
			$result = strpos($string, 'AJIR');
		
		}
		
		if($result == FALSE){
		
			$result = strpos($string, 'AJR');
		
		}
		
		if($result == FALSE){
		
			$result = strpos($string, 'AJAR');
		
		}
		
		if($result == FALSE){
		
			$result = strpos($string, 'ajar');
		
		}

		if($result == FALSE){
		
			$result = strpos($string, 'Faj');
		
		}
		
		if($result == FALSE){
		
			$result = strpos($string, 'faj');
		
		}
		
		if($result == FALSE){
		
			$result = strpos($string, 'Fajir');
		
		}
		
		if($result == FALSE){
		
			$result = strpos($string, 'fajir');
		
		}
		
		if($result == FALSE){
		
			$result = strpos($string, 'FAJIR');
		
		}
		
		if($result == FALSE){
		
			$result = strpos($string, 'FAJR');
		
		}
	
		return $result;
	}
	
	public function index()
	{
		
		$arr = get_class_methods('timing');
		
		echo 'this is a list of all the functions', '<pre>';
		print_r($arr);
		
	}
	
	
	
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */