<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Upload extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Upload_model');
        $this->load->library('upload');
    }

    // Muestra el formulario
    public function index() {
        $this->load->view('upload_form');
    }

    // Procesa el archivo
    public function process() {
        $config['upload_path']   = './uploads/';
        $config['allowed_types'] = 'csv';
        $config['max_size']      = 5120; // 5MB máximo
        $config['encrypt_name']  = TRUE; // nombre aleatorio para evitar colisiones

        $this->upload->initialize($config);

        if (!$this->upload->do_upload('csv_file')) {
            // Hubo un error al subir
            $data['error'] = $this->upload->display_errors();
            $this->load->view('upload_form', $data);
        } else {
            // Subida exitosa
            $file_info = $this->upload->data();

            // Guardar en base de datos
            $upload_data = array(
                'filename'      => $file_info['file_name'],
                'original_name' => $file_info['orig_name'],
                'file_size'     => $file_info['file_size'],
                'total_rows'    => 0,
                'status'        => 'uploaded'
            );

            $upload_id = $this->Upload_model->save_upload($upload_data);

            // Redirigir a resultados (lo construimos el Día 4)
            redirect('upload/success/' . $upload_id);
        }
    }

    // Página temporal de éxito
    public function success($upload_id = 0) {
        $data['upload_id'] = $upload_id;
        $this->load->view('upload_success', $data);
    }
}