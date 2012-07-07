<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Prereg_model extends CI_Model {
    public function choose_collection(){
        $this->load-> model('core_model');
        return $this->core_model->connect_to_db()->prereg;
    }

    public function write_to_cache($doc){
        $collection = $this->choose_collection();
        $collection->insert($doc);
    }

    public function query_module_code($module_code){
        $collection = $this->choose_collection();

        $query = array('module_code' => $module_code);
        return $collection->findOne($query);
    }
}