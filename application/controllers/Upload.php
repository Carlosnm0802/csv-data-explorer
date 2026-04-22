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
    // ---- HISTORIAL ----
public function history() {
    $data['uploads'] = $this->Upload_model->get_uploads_with_analysis();
    $this->load->view('history', $data);
}

// ---- EXPORTAR CSV ----
public function export_csv($upload_id = 0) {
    $analysis = $this->Analysis_model->get_by_upload_id($upload_id);

    if (!$analysis) {
        show_404();
        return;
    }

    $stats = json_decode($analysis->stats, TRUE);

    // Configurar headers para descarga
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="analisis_' . $upload_id . '.csv"');

    $output = fopen('php://output', 'w');

    // Encabezados del CSV
    fputcsv($output, [
        'Columna', 'Mínimo', 'Máximo', 'Promedio',
        'Mediana', 'Desv. Estándar', 'Nulos', 'Válidos'
    ]);

    // Filas de datos
    foreach ($stats as $col) {
        fputcsv($output, [
            $col['column'],
            $col['min'],
            $col['max'],
            $col['mean'],
            $col['median'],
            $col['std'],
            $col['nulls'],
            $col['valid']
        ]);
    }

    fclose($output);
    exit;
}

// ---- EXPORTAR PDF ----
public function export_pdf($upload_id = 0) {
    $analysis = $this->Analysis_model->get_by_upload_id($upload_id);

    if (!$analysis) {
        show_404();
        return;
    }

    $stats = json_decode($analysis->stats, TRUE);

    // Configurar headers para descarga PDF
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="analisis_' . $upload_id . '.pdf"');

    // Llamar al endpoint de Flask para generar el PDF
    $flask_url = 'http://localhost:5000/export-pdf';

    $payload = json_encode([
        'upload_id'    => (int) $upload_id,
        'total_rows'   => $analysis->total_rows,
        'total_columns'=> $analysis->total_columns,
        'stats'        => $stats
    ]);

    $ch = curl_init($flask_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_POST,           TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS,     $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER,     [
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);

    $pdf_content = curl_exec($ch);
    $http_code   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code !== 200 || !$pdf_content) {
        show_error('No se pudo generar el PDF. Verifica que Flask esté corriendo.');
        return;
    }

    echo $pdf_content;
    exit;
}
}