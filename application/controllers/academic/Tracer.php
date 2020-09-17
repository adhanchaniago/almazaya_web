<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Tracer extends Admin_Controller {

	public function __construct() {
		parent::__construct();
		$this->load->model([
			'm_tracer',
			'm_academic_years'
		]);
		$this->pk = M_tracer::$pk;
		$this->table = M_tracer::$table;
	}
 
	public function index() {
		$this->vars['title'] = 'Kelas';
		$this->vars['academic'] = $this->vars['academic_references'] = $this->vars['tracer'] = TRUE;
		$this->vars['academic_years_dropdown'] = json_encode([0 => 'Pilih'] + $this->m_academic_years->dropdown(), JSON_HEX_APOS | JSON_HEX_QUOT);
		$this->vars['content'] = 'academic/tracer';
		$this->load->view('backend/index', $this->vars);
	}

	public function pagination() {
		if ($this->input->is_ajax_request()) {
			$keyword = trim($this->input->post('keyword', true));
			$page_number = _toInteger($this->input->post('page_number', true));
			$limit = _toInteger($this->input->post('per_page', true));
			$offset = ($page_number * $limit);
			$query = $this->m_tracer->get_where($keyword, 'rows', $limit, $offset);
			$total_rows = $this->m_tracer->get_where($keyword);
			$total_page = $limit > 0 ? ceil(_toInteger($total_rows) / _toInteger($limit)) : 1;
			$this->vars['total_page'] = _toInteger($total_page);
			$this->vars['total_rows'] = _toInteger($total_rows);
			$this->vars['rows'] = $query->result();
			$this->output
				->set_content_type('application/json', 'utf-8')
				->set_output(json_encode($this->vars, JSON_HEX_APOS | JSON_HEX_QUOT))
				->_display();
			exit;
		}
	}

	public function save() {
		if ($this->input->is_ajax_request()) {
			$id = _toInteger($this->input->post('id', true));
			if ($this->validation( $id )) {
				$dataset = $this->dataset();
				$dataset[(_isNaturalNumber( $id ) ? 'updated_by' : 'created_by')] = __session('user_id');
				if (!_isNaturalNumber( $id )) $dataset['created_at'] = date('Y-m-d H:i:s');
				$query = $this->model->upsert($id, $this->table, $dataset);
				$this->vars['status'] = $query ? 'success' : 'error';
				$this->vars['message'] = $query ? 'Data Anda berhasil disimpan.' : 'Terjadi kesalahan dalam menyimpan data';
			} else {
				$this->vars['status'] = 'error';
				$this->vars['message'] = validation_errors();
			}
			$this->output
				->set_content_type('application/json', 'utf-8')
				->set_output(json_encode($this->vars, JSON_HEX_APOS | JSON_HEX_QUOT))
				->_display();
			exit;
		}
	}

	private function dataset() {
		return [
			'nama' => $this->input->post('nama', true),
			'sekolah' => $this->input->post('sekolah', true),
			'academic_year' => $this->input->post('academic_year', true)];
	}

	private function validation() {
		$this->load->library('form_validation');
		$val = $this->form_validation;
		$val->set_rules('nama', 'Nama Lengkap', 'trim|required');
		$val->set_rules('sekolah', 'Sekolah', 'trim|required');
		$val->set_rules('academic_year', 'Tahun Ajaran', 'trim');
		$val->set_message('required', '{field} harus diisi');
		$val->set_error_delimiters('<div>&sdot; ', '</div>');
		return $val->run();
	}

	public function tracer_reports() {
		if ($this->input->is_ajax_request()) {
			$query = $this->m_tracer->tracer_reports();
			$this->output
				->set_content_type('application/json', 'utf-8')
				->set_output(json_encode($query->result(), JSON_HEX_APOS | JSON_HEX_QUOT))
				->_display();
			exit;
		}
	}
}