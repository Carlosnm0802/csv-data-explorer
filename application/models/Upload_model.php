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
    public function get_uploads_with_analysis() {
    $this->db->select('
        uploads.id,
        uploads.original_name,
        uploads.file_size,
        uploads.total_rows,
        uploads.status,
        uploads.created_at,
        analysis_results.id as analysis_id
    ');
    $this->db->from('uploads');
    $this->db->join(
        'analysis_results',
        'analysis_results.upload_id = uploads.id',
        'left'
    );
    $this->db->order_by('uploads.created_at', 'DESC');
    return $this->db->get()->result();
}
}