<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Upload_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }
    public function save_upload($data) {
        $this->db->insert('uploads', $data);
        return $this->db->insert_id();
    }
    public function get_all_uploads(){
        return $this->db->order_by('created_at', 'DESC')->get('uploads')->result();
    }
}