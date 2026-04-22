<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Upload extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Upload_model');
        $this->load->model('Analysis_model');
        $this->load->library('upload');
        $this->load->helper('flask');
    }

    public function index() {
        $this->load->view('upload_form');
    }

    public function process() {
        $config['upload_path']   = './uploads/';
        $config['allowed_types'] = 'csv';
        $config['max_size']      = 5120;
        $config['encrypt_name']  = TRUE;

        $this->upload->initialize($config);

        if (!$this->upload->do_upload('csv_file')) {
            $data['error'] = $this->upload->display_errors();
            $this->load->view('upload_form', $data);
            return;
        }

        // Archivo subido correctamente
        $file_info = $this->upload->data();
        $filepath  = realpath('./uploads/' . $file_info['file_name']);

        // Guardar registro en uploads
        $upload_data = [
            'filename'      => $file_info['file_name'],
            'original_name' => $file_info['orig_name'],
            'file_size'     => $file_info['file_size'],
            'total_rows'    => 0,
            'status'        => 'processing'
        ];
        $upload_id = $this->Upload_model->save_upload($upload_data);

        // Llamar a Flask
        $analysis = call_flask_analyze($filepath);

        if ($analysis === FALSE) {
            // Flask no respondió
            $data['error'] = '<p>Error al conectar con el servicio de análisis. 
                             Verifica que Flask esté corriendo en el puerto 5000.</p>';
            $this->load->view('upload_form', $data);
            return;
        }

        // Guardar resultados en analysis_results
        $analysis_data = [
            'upload_id'       => $upload_id,
            'total_rows'      => $analysis['total_rows'],
            'total_columns'   => $analysis['total_columns'],
            'numeric_columns' => implode(',', $analysis['numeric_columns']),
            'text_columns'    => implode(',', $analysis['text_columns']),
            'stats'           => json_encode($analysis['stats'])
        ];
        $this->Analysis_model->save_analysis($analysis_data);

        // Actualizar status del upload
        $this->db->where('id', $upload_id)
                 ->update('uploads', [
                     'total_rows' => $analysis['total_rows'],
                     'status'     => 'analyzed'
                 ]);

        redirect('upload/results/' . $upload_id);
    }

    public function results($upload_id = 0) {
        $analysis = $this->Analysis_model->get_by_upload_id($upload_id);

        if (!$analysis) {
            show_404();
            return;
        }

        $data['analysis']  = $analysis;
        $data['stats']     = json_decode($analysis->stats, TRUE);
        $data['upload_id'] = $upload_id;

        $this->load->view('results', $data);
    }
}