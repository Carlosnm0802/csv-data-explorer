<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Analysis_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    public function save_analysis($data) {
        $this->db->insert('analysis_results', $data);
        return $this->db->insert_id();
    }

    public function get_by_upload_id($upload_id) {
        return $this->db->where('upload_id', $upload_id)
                        ->get('analysis_results')
                        ->row();
    }
}