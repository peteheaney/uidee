<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Uidee_ft extends EE_Fieldtype {

	var $info = array(
		'name'      => 'UIDee',
		'version'   => '1.0'
	);

	public function accepts_content_type($name)
	{
		return ($name == 'channel' || $name == 'grid');
	}

	private function _include_css()
	{
		ee()->cp->add_to_head('<style type="text/css">
			.uidee-error{
				margin:0;
				color:#CE0000;
				font-weight:bold;
				display:none;
			}
		</style>');

		$this->cache['css'] = TRUE;
	}

	private function _include_js()
		{
			$this->EE->cp->add_to_foot("<script type='text/javascript'>
				$(function(){
					function updateFieldsState(){
						if( $('#autoincrement').prop('checked') ){
							$('#length').prop('disabled', true);
							$('#lowercase').prop('disabled', true);
							$('#uppercase').prop('disabled', true);
							$('#numbers').prop('disabled', true);
							$('.length-required').hide();
						} else {
							$('#length').prop('disabled', false);
							$('#lowercase').prop('disabled', false);
							$('#uppercase').prop('disabled', false);
							$('#numbers').prop('disabled', false);
							$('.length-required').show();
						}
					}

					updateFieldsState();

					$('#autoincrement').on('change', function(){
						updateFieldsState();
					});

					$('form').on('submit', function(){
						var lengthError = false,
								checkboxesError = false;

						if( $('#field_type').val() === 'uidee' ){

							if( $('#autoincrement') === 'yes' ){
								return true;
							} else {
								if( $('#length').val() == ''){
									$('.uidee-error.length').show();
									lengthError = true;
								}

								if( !$('#lowercase').prop('checked') && !$('#uppercase').prop('checked') && !$('#numbers').prop('checked') ){
									$('.uidee-error.checkboxes').show();
									checkboxesError = true;
								}

								if(lengthError || checkboxesError){
									console.log('errors');
									return false;
								}

								return true;
							}

						} else {
							return true;
						}
					});
				});
			</script>");
		}

	function display_settings($data)
	{
		$this->_include_js();
		$this->_include_css();
		ee()->lang->loadfile('uidee');

		$autoincrement = isset($data['autoincrement']) ? $data['autoincrement'] : '';
		$prefix = isset($data['prefix']) ? $data['prefix'] : '';
		$suffix = isset($data['suffix']) ? $data['suffix'] : '';
		$length = isset($data['length']) ? $data['length'] : '';
		$lowercase = isset($data['lowercase']) ? $data['lowercase'] : '';
		$uppercase = isset($data['uppercase']) ? $data['uppercase'] : '';
		$numbers = isset($data['numbers']) ? $data['numbers'] : '';

		$autoincrement_attrs = array(
			'name' => 'autoincrement',
			'id' => 'autoincrement',
			'value' => 'yes'
		);

		if($autoincrement === 'yes'){
			$autoincrement_attrs['checked'] = TRUE;
		}

		$lowercase_attrs = array(
			'name' => 'lowercase',
			'id' => 'lowercase',
			'value' => 'yes'
		);

		if($lowercase === 'yes'){
			$lowercase_attrs['checked'] = TRUE;
		}

		$uppercase_attrs = array(
			'name' => 'uppercase',
			'id' => 'uppercase',
			'value' => 'yes'
		);

		if($uppercase === 'yes'){
			$uppercase_attrs['checked'] = TRUE;
		}

		$numbers_attrs = array(
			'name' => 'numbers',
			'id' => 'numbers',
			'value' => 'yes'
		);

		if($numbers === 'yes'){
			$numbers_attrs['checked'] = TRUE;
		}

		ee()->table->add_row(
			lang('autoincrement', 'autoincrement'),
			form_checkbox($autoincrement_attrs)
		);

		ee()->table->add_row(
			lang('prefix', 'prefix'),
			form_input(array('name' => 'prefix','id' => 'prefix','value' => $prefix))
		);

		ee()->table->add_row(
			lang('suffix', 'suffix'),
			form_input(array('name' => 'suffix','id' => 'suffix','value' => $suffix))
		);

		ee()->table->add_row(
			'<em class="required length-required">* </em>' . lang('length', 'length') . '<p class="uidee-error length">' . lang('length_error') . '</p>',
			form_hidden('uidee_field_fmt', 'none') .
			form_input(array('name' => 'length','id' => 'length','value' => $length))
		);

		ee()->table->add_row(
			array(
				'data'=> lang('checkboxes_error'),
				'colspan' => '2',
				'class' => 'uidee-error checkboxes'
			)
		);

		ee()->table->add_row(
			lang('lowercase', 'lowercase'),
			form_checkbox($lowercase_attrs)
		);

		ee()->table->add_row(
			lang('uppercase', 'uppercase'),
			form_checkbox($uppercase_attrs)
		);

		ee()->table->add_row(
			lang('numbers', 'numbers'),
			form_checkbox($numbers_attrs)
		);
	}
/*
	function validate_settings()
	{
		if( ee()->input->post('autoincrement') !== 'yes' ){
			ee()->form_validation->set_rules('length', 'lang:length', 'required|integer|greater_than[0]');
			ee()->form_validation->set_rules('lowercase', 'lang:lowercase', 'callback_check_settings_checkboxes');
		}
	}

	function check_settings_checkboxes()
	{
		if( ee()->input->post('lowercase') !== 'yes' &&
				ee()->input->post('uppercase') !== 'yes' &&
				ee()->input->post('numbers') !== 'yes'
			)
		{
			ee()->form_validation->set_message('check_settings_checkboxes', 'You must check at least one checkbox.');
			return FALSE;
		}
		return TRUE;
	}
*/
	function save_settings($data)
	{
		return array(
			'autoincrement' => ee()->input->post('autoincrement'),
			'prefix' => ee()->input->post('prefix'),
			'suffix' => ee()->input->post('suffix'),
			'length' => ee()->input->post('length'),
			'lowercase' => ee()->input->post('lowercase'),
			'uppercase' => ee()->input->post('uppercase'),
			'numbers' => ee()->input->post('numbers')
		);
	}

	function display_field($data)
	{
		ee()->lang->loadfile('uidee');

		if($data)
		{
			$value = $data;
		}
		else
		{
			$value = '';
			$this->_include_css();
		}

		$form = form_input($this->field_name, $value, 'id="'.$this->field_name.'" placeholder="'.lang('placeholder').'" readonly');

		return $form;
	}

	private function _uid($length, $lowercase, $uppercase, $numbers)
	{
		$unique = FALSE;

		$lowercase_str = "abcdefghijklmnopqrstuvwxyz";
		$uppercase_str = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
		$numbers_str = "0123456789";

		$chars = '';

		if($lowercase === 'yes')
		{
			$chars .= $lowercase_str;
		}

		if($uppercase === 'yes')
		{
			$chars .= $uppercase_str;
		}

		if($numbers === 'yes')
		{
			$chars .= $numbers_str;
		}

		$chars_length = strlen($chars);
		$uid;

		while(!$unique)
		{
			$uid = '';
			for ($i = 0; $i < $length; $i++){
					$uid .= $chars[rand(0, $chars_length - 1)];
			}

			if($this->_is_unique($uid)){
				$unique = TRUE;
			}
		}

		return $uid;

	}

	private function _is_unique($uid)
	{
		ee()->db->where('field_id_'.$this->field_id, $uid);
		ee()->db->where('channel_id', ee()->input->get('channel_id'));
		ee()->db->from('channel_data');
		$count = ee()->db->count_all_results();

		if($count === 0){
			return TRUE;
		}

		return FALSE;
	}

	private function _auto_id()
	{
		$channel_id = ee()->input->get('channel_id');

		ee()->db->select('field_id_'.$this->field_id);
		ee()->db->where('channel_id', ee()->input->get('channel_id'));
		ee()->db->order_by('field_id_'.$this->field_id, 'desc');
		ee()->db->limit(1);

		$query = ee()->db->get('channel_data');
		$last = $query->row('field_id_'.$this->field_id);
		if($last){
			$next = $last + 1;
		} else {
			$next = 1;
		}

		return $next;
	}

	/**
	 * Save Data
	 *
	 * @access  public
	 * @param   submitted field data
	 * @return  string to save
	 *
	 */
	function save($data)
	{
		if($data !== '')
		{
			return $data;
		}
		else
		{
			if($this->settings['autoincrement'] === 'yes'){
				$uid = $this->_auto_id();
			} else {
				$uid = $this->_uid(
					$this->settings['length'],
					$this->settings['lowercase'],
					$this->settings['uppercase'],
					$this->settings['numbers']
				);
			}

			return $this->settings['prefix'] . $uid . $this->settings['suffix'];
		}
	}

	/**
	 * Replace tag
	 *
	 * @access  public
	 * @param   field data
	 * @param   field parameters
	 * @param   data between tag pairs
	 * @return  replacement text
	 *
	 */
	function replace_tag($data, $params = array(), $tagdata = FALSE)
	{
		return $data;
	}
}

/* End of file ft.uidee.php */
/* Location: ./system/expressionengine/third_party/uidee/ft.uidee.php */
