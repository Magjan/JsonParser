<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

//todo a lot of refactoring and splitting code to classes

class Page extends Controller {
    private $oracle_connection;
    private $per_page = 10; // grid records per page
    private $he_encoding;

    private $db_username;
    private $db_password;
    private $db_instance;
    private $db_encoding;
    private $db_schema_prefix   = '';

    private $show_susp_members_grid = true;

    function __construct() {
		
		
       parent::__construct();
       
		$this->he_encoding              = $this->config->item('he_encoding');
        $this->db_username              = $this->config->item('db_username');
        $this->db_password              = $this->config->item('db_password');
        $this->db_instance              = $this->config->item('db_instance');
        $this->db_encoding              = $this->config->item('db_encoding');
        $this->db_schema_prefix         = $this->config->item('db_schema_prefix');
        $this->max_components_on_line   = $this->aml_html->get_max_components_on_line();

        $this->savesettingsurl          = site_url('page/savesettings');
        $this->savecolpropurl           = site_url('page/savecolumnproperty');
        $this->returnurl                = site_url('page/returnoperations');
        $this->operationstreeurl        = site_url('page/datasource/operationstree');
        $this->getclientbyofflineaccurl = site_url('page/datasource/getclientbyaccount');
        $this->sendkfmurl               = site_url('page/sendtokfm');
        $this->preparedurl              = site_url('page/setprepared');
    }
	
	

    function test() 
	{
        $msg  = 'Ferol912!';
        $cr = $this->aml_aes->crypt_str($msg);
        $unpacked = unpack("H*",$cr);
        print 'encrypting  ' . $cr . ' to AES:<br>';
        //print $unpacked[1];    
    }
   function test2($pass =/*'4a6a478f8f610dd3007d44be99af213c1cc1c115d35d4ad7c14599e6b54972610b62319a38e68a298f383761819575ab89e3912f8412657054948a0b2fe93a16b9cd22dcf73449ba6da7f9626198f0c9'*/
'4f554a48576c5a6f4e6d38764d6d5a695358647a536c5132576b4a48555430394f6a72587558353046486769763448653671756b41694b4c '   ) 
	{
	/*'6cf32a020eeba8d55c1a9abdfb53fd42a059835686a07358e47abb5bfa031a5d41007a6dcece03c92ab7e597d0cc4a627760f85e7683a447b3acada5d53edf9bb0704280c61c296d2cdd19b8a5afb72f34d4dec2'*/
	$decrypted_pass = @$this->aml_aes->decrypt_str(pack("H*", $pass));
	//$decrypted_pass = "test";
	print $decrypted_pass;
	}
	
		
    function get_value_from_config($input_string, $input_param)
	{
		//1--- для строк
		//2--- для чисел
		if(is_null($input_string)){
			return '';
		}
		
		if($input_param == 1){
			$first = strpos($input_string, '= \'');
			$second = strpos($input_string, '\';', $first);
			$out_res = substr(str_replace('\';','',$input_string), $first+3, $second);
			return $out_res;
		}
		elseif($input_param == 2)
		{
			$first = strpos($input_string, '] = ');
			$second = strpos($input_string, ';' ,$first);
			$out_res = substr(str_replace(';','',$input_string), $first+3, $second);
			return $out_res;
		}
	}
	
	function cpp() 
	{
		
    $object = new COM('ClassLibrary2.Class1')or die ("Unable to create COM object");
    //$cr = $object->con();
	//die(var_dump($cr));
    
	$lines = file('D:\inetpub\wwwroot\officer_ca\system\application\config\config.php');
	
	//die(var_dump($lines));
		
	$AppDescsAppID = trim($this->get_value_from_config($lines[424], 1));
	$Query = trim($this->get_value_from_config($lines[425], 1));
		
	//die(var_dump($AppDescsAppID.'-------------------'. $Query));
	
		$set_connection_params = $object->Connection_Options($AppDescsAppID, $Query);
	
		try{ 
			//$string = $object->con();
			//DIE(var_dump($return_recieve));
			$return_recieve = $object->CyberArk_Conn();
			DIE(var_dump($return_recieve));
		} 
		catch (RuntimeException $e) { 
			die(var_dump('123123123'));
			echo($e->getMessage());
			//die(var_dump($e->getMessage()));
		}
	}
	
    function update_translate() {
        $this->aml_auth->check_auth();
        $can_admin = $this->aml_security->check_privilege(24); // ADMIN USERS
        if (!$can_admin) {
            $this->aml_html->error_page(array(('Отсутствуют права для данного действия.')));
        }
        $stid = $this->aml_oracle->execute("SELECT table_name
                                            FROM  aml_user.xxx_tables  u
                                            WHERE u.TABLE_NAME LIKE 'TB_DICT%'", __LINE__);
        //todo add union
        /*  select view_name
            from user_views
        */
        $directories = array();
        while($r = oci_fetch_array($stid)) {
            $directories[] = $r['TABLE_NAME'];
        }
        foreach($directories as $d) {
            $stid = $this->aml_oracle->execute("SELECT P_LONGNAME FROM " . $d . " t", __LINE__);
            $rows = array('<?php');
            while ($r = oci_fetch_array($stid)) {
                $rows[] = '    ("' . str_replace('"','\\"',$r['P_LONGNAME']) . '");';
            }
            $fp = fopen('./locale/sources/' . $d . '.php','w');
            fwrite($fp, implode("\n", $rows));
            fclose($fp);
        }
    }

    function help($action){
		
		
        $this->aml_auth->check_auth();

        switch($action){
            case 'manual':
                $file_content = file_get_contents(getcwd() . '/files/complydesk_manual.pdf');
                $this->aml_html->output_file("Manual.pdf", $file_content, "application/pdf");
                break;
            case 'about':
                $q = <<<ENDL
                        SELECT MAX(p_date_update)  dt
                              FROM tb_audit_all t0
                             WHERE t0.p_username = :uname
                               AND t0.p_action_type = 'LOGON-SUCCESS'
                               AND t0.p_date_update <
                                   (SELECT MAX(P_DATE_UPDATE)
                                      FROM tb_audit_all t1
                                     WHERE t1.p_username = t0.p_username
                                       AND t1.p_action_type = 'LOGON-SUCCESS')
ENDL;

                $stid = $this->aml_oracle->execute($q, __LINE__,array(':uname' => $this->aml_auth->get_username()));
                $last_login = oci_fetch_array($stid, OCI_ASSOC);
                $vars['last_success_login'] =  !empty($last_login['DT'])? $last_login['DT']: "";
				/*
				**
				**
				*///test for PKG_USER_PASS
				 $pr ="
					begin
					  -- Call the procedure
					  pkg_user_pass.check_expired_day_count(iv_user_name => :iv_user_name,
															ov_result => :ov_result,
															ov_result_text => :ov_result_text);
					end;";
					//$stid = $this->aml_oracle->execute($pr, __LINE__,array(':iv_user_name' => $this->aml_auth->get_username()));
				//	$stid = $this->aml_oracle->execute_size($pr, __LINE__,array(':iv_user_name' => $this->aml_auth->get_username(), ':ov_result' => &$ov_result,':ov_result_text' => &$ov_result_text));
					$vars['ov_result'] =  $ov_result;
					$vars['ov_result_text'] =  $ov_result_text;
					//s die(var_dump($ov_result));
				//
				
                $q = <<< ENDL
                    SELECT *
                    FROM tb_audit_all t
                    WHERE t.p_username = :uname
                       AND t.p_action_type = 'LOGON-FAIL'
                       AND t.p_date_update >
                           (SELECT MAX(p_date_update)
                              FROM tb_audit_all t0
                             WHERE t0.p_username = t.p_username
                               AND t0.p_action_type = 'LOGON-SUCCESS'
                               AND t0.p_date_update <
                                   (SELECT MAX(P_DATE_UPDATE)
                                      FROM tb_audit_all t1
                                     WHERE t1.p_username = t.p_username
                                       AND t1.p_action_type = 'LOGON-SUCCESS'))
                    ORDER BY p_date_update
ENDL;

                $stid = $this->aml_oracle->execute($q, __LINE__,array(':uname' => $this->aml_auth->get_username()));
                $vars['failed_attempts'] = array();
                while($r = oci_fetch_array($stid, OCI_ASSOC | OCI_RETURN_LOBS)){
                    $vars['failed_attempts'][] = $r;
                }

                $vars['content'] = $this->load->view('help/about', $vars, true);
                break;
			case 'aml_history':
				$stid = $this->aml_oracle->execute("select * from TB_UPDATES_HISTORY order by P_VERSION_DATE asc", __LINE__);
				oci_fetch_all($stid, $vars['changes']);
				$vars['page_name'] = ("История версий");
				$vars['content']= $this->load->view("help/history", $vars, true);
				break;
        }
        $this->aml_context->set_general_vars($vars);
        $this->load->view('main', $vars);
    }
	
	


    function index() {
		
        $this->aml_auth->check_auth();
		
        $this->help('about');
    }

    function show_error() {
        $page_errors = $this->native_session->flashdata('page_errors');
        if ($page_errors === FALSE) {
            header('Location: ' . site_url(''));
            die();
        }
        $this->native_session->set_flashdata('page_errors', $page_errors);
        $vars['content'] = '<ul class="error-messages"><li>' . implode('</li><li>', $page_errors) . '</li><li style="text-align:center"><input type="button" value="Назад" onclick="javascript:history.back(-1)"></li></ul>';
        $this->aml_context->set_general_vars($vars);
        $this->load->view('main', $vars);
    }

    // возвращает системное ли поле или нет  (никогда не редактируется)
    function _is_system_field($field_name) {
        return $this->aml_metainfo->is_system_field($field_name);
    }

    // возвращает индекс поля в массиве table info
    function _get_field_index(&$ti /*table info*/, $field_name) {
        return $this->aml_metainfo->get_field_index($ti, $field_name);
    }

    // возвращает true, если поле сущесвует в таблице, false иначе
    function _check_table_field_exists(&$ti /*table info*/, $field_name) {
        for($i = 0; $i < count($ti['COLUMN_NAME']); $i++) {
            if ($ti['COLUMN_NAME'][$i] == $field_name) {
                return true;
            }
        }
        return false;
    }

    // получает от грида данные по пользовательским сортировкам, и генерит ORDER BY блок
    function _get_sorting_clause(&$fields_info, $additional_field_names = null) {
        $result = '';

        for($i = 0; $i < count($fields_info['COLUMN_NAME']); $i++) {
            if ($fields_info['COLUMN_NAME'][$i] == strtoupper($this->input->post('sidx'))) {
                $order_by_field = $fields_info['COLUMN_NAME'][$i];
                break;
            }
        }
        if (is_array($additional_field_names) && empty($order_by_field)) {
            foreach($additional_field_names as $f) {
                if (strtoupper($f) == strtoupper($this->input->post('sidx'))) {
                    $order_by_field = strtoupper($f);
                    break;
                }
            }
        }

        if (!empty($order_by_field)) {
            $result = 'ORDER BY ' . $order_by_field;
            if ($this->input->post('sord') == 'asc' || $this->input->post('sord') == 'desc') {
                $result .= ' ' . $this->input->post('sord');
            }
        }
        return $result;
    }

    function viewtbl($table_name, $id) {
        $this->aml_auth->check_auth();
        $this->show_susp_members_grid = false;
        $this->_edit($table_name, $id, 0, 1);
    }

    // рисует страницу просмотра данных, на входе код таблицы, PK записи, либо несколько ID разделенных пробелами
    function viewdata($tb_idx, $in_id_list = 0, $return_as_string = false) {
        $this->aml_auth->check_auth(); // checkauth
        // вх данные
        $in_id_list = explode(' ', $in_id_list);
        if (count($in_id_list)) {
            $id_list = array();
            $records_per_page_limit = 1;
            foreach($in_id_list as $id) {
                if (intval($id) > 0) {
                    $id_list[] = intval($id);
                }
                $records_per_page_limit++;
                // проверка на переполнение
                if ($records_per_page_limit > 200) {
                    break;
                }
            }
        } else {
            $id_list = array(intval($in_id_list));
        }

        $id_list = implode(',', $id_list);

        if ($id_list <= 0) {
            $this->aml_html->error_page(array(sprintf(('Неверное значение параметра %s'), 'id')));
        }

        $tables = array(
            'online'     => 'TB_ONLINEOPERATIONS',
            'offline'    => 'TB_OFFLINEOPERATIONS',
            'suspicious' => 'TB_SUSPICIOUSOPERATIONS'
        );

        switch($tb_idx) {
            case 'online':
                $table_name = 'TB_ONLINEOPERATIONS';
                $can_do = $this->aml_security->check_privilege(13);
                if (!$can_do) {
                    $this->aml_html->error_page(array(('Отсутствуют права для данного действия.')));
                }
                break;
            case 'offline':
                $table_name = 'TB_OFFLINEOPERATIONS';
                $can_do = $this->aml_security->check_privilege(14) || $this->aml_security->check_privilege(11) || $this->aml_security->check_privilege(12)|| $this->aml_security->check_privilege(56) || $this->aml_security->check_privilege(77);
                if (!$can_do) {
                    $this->aml_html->error_page(array(('Отсутствуют права для данного действия.')));
                }
                break;
            case 'suspicious':
                $table_name = 'TB_SUSPICIOUSOPERATIONS';

                $can_do = $this->aml_security->check_privilege(11) || $this->aml_security->check_privilege(12)|| $this->aml_security->check_privilege(56);
                if (!$can_do) {
                    $this->aml_html->error_page(array(('Отсутствуют права для данного действия.')));
                }
                break;
            default:
                $this->aml_html->error_page(array(sprintf(('Неверное значение параметра %s'), 'table_name')));
        }

        $stid = $this->aml_oracle->execute('SELECT * FROM ' . $table_name . ' t WHERE t.ID IN (' . $id_list . ')',__LINE__);
        $nrows = oci_fetch_all($stid, $results);

        if (!$nrows) {
            $this->aml_html->error_page(array(sprintf('Не найдена запись с ID: %d', $id_list)));
        }

        // начинаем рисовать форму просмотра
        $fields_info = $this->aml_metainfo->get_table_info($table_name);
        $vars['results'] = $results;
        $vars['fields_info'] = $fields_info;
        $vars['hide_header'] = true;
        $vars['nrows'] = $nrows;
        $vars['run_js'] = 'var greetz_from_kerb = "hello there ;)";';
        $vars['content'] = $this->load->view('view-record', $vars,true);
        if ($return_as_string) {
            return $vars;
        } else {
            $this->load->view('main', $vars);
        }
    }

    // генерация hidden поля (ID записи)
    function _create_pk_control($table_name, &$fi, $idx, $value, $array_index = 1) {
        return $this->aml_html->create_pk_control($table_name, $fi, $idx, $value, $array_index = 1);
    }

    // генерация hidden параметра
    function _create_hidden_control($table_name, $param_name, $param_value, $array_index) {
        return  $this->aml_html->create_hidden_control($table_name, $param_name, $param_value, $array_index);
    }

    // генерация контрола формы для редактирования
    function _create_control($table_name, &$fi, $idx, $value, $array_index = 1) {
        return $this->aml_html->create_control($table_name, $fi, $idx, $value, $array_index);
    }

    // рисует форму-заглушку (пустую) для фаундера, и возвращает кусок HTML
    function create_founder_stub_form($member_id, $next_founder_number) {
        $results_founders = null;
        $this->aml_auth->check_auth();

        $stid = $this->aml_oracle->execute('SELECT * FROM TB_SUSPICIOUSFOUNDERS t WHERE rownum = 1', __LINE__);
        $nrows = oci_fetch_all($stid, $results_founders);

        foreach($results_founders as $field_name => $dataval){
            if ($field_name == "ID"){
                $results_founders[$field_name][0] = "0";
            } else {
                $results_founders[$field_name][0] = "";
            }
        }
        print $this->aml_html->create_founder_form($results_founders,0,$next_founder_number, $member_id);
    }

    // рисует форму-заглушку (пустую) для мембера, и возвращает кусок HTML
    function create_member_stub_form($by_credit = true, $reference_operation_id=0) {
        $results_members = null;
        $this->aml_auth->check_auth();

        //$this->_get_connection();
        $stid = $this->aml_oracle->execute('SELECT * FROM TB_SUSPICIOUSMEMBERS t WHERE rownum = 1', __LINE__);
        $nrows = oci_fetch_all($stid, $results_members);

        foreach($results_members as $field_name => $dataval){
            if ($field_name == "ID"){
                $results_members[$field_name][0] = "0";
            } else {
                $results_members[$field_name][0] = "";
            }
        }
        print $this->_create_member_form($results_members, 0, $by_credit, 4, $reference_operation_id);
    }

    function _create_form($table_name, $rec_id) {
        return $this->aml_html->create_form($table_name, $rec_id);
        $start_from = 0;
        $i = 0;
        $results = array();
        $output = '';
        $ti = $this->aml_metainfo->get_table_info($table_name,1);
        $q = sprintf("SELECT * FROM " . $table_name . " t WHERE t.ID = %d", $rec_id);
        $stid = $this->aml_oracle->execute($q, __LINE__);
        oci_fetch_all($stid, $results);
        for ($j = 0; $j < count($ti['COLUMN_NAME']); $j++) {
            if ($ti['COLUMN_NAME'][$j] == 'ID') {
                $output .= $this->_create_pk_control($table_name,$ti, $j, $results[$ti['COLUMN_NAME'][$j]][$i], $start_from + 1) . "\n";
            } else if ($ti['P_VISIBILITY_EDIT_BOOL'][$j] == 1) {
                $component_number++;
                $output .= $this->_create_control($table_name, $ti, $j, $results[$ti['COLUMN_NAME'][$j]][$i], $start_from + 1) . "\n";
                if ($component_number % $this->max_components_on_line == 0) {
                    $output .= '<br style="clear:both">';
                }
            }
        }
        return $output;
    }

    // рисует форму редактирования мембера, заполняет поля данными из массива с индексом $i
    function _create_member_form($results_members, $i, $by_credit_bool, $start_from = 0, $reference_operation_id = 0, $hide_delete = 0) {
        return $this->aml_html->create_member_form($results_members, $i, $by_credit_bool, $start_from, $reference_operation_id, $hide_delete);
    }

     // рисует форму редактирования, обрабатывает процесс редактирования записи
    function edit($table_name = null, $id = null, $ignore_readonly = 0, $readonly = 0) {
        $this->aml_auth->check_auth();
        $this->_edit($table_name, $id, $ignore_readonly, $readonly);
    }

    // рисует форму редактирования, обрабатывает процесс редактирования записи
    function _edit($table_name = null, $id = null, $ignore_readonly = 0, $readonly = 0) {
        $this->aml_auth->check_auth();

        $output = '';
        $results = null;
        $id = intval($id);
        $show_rollback_button = false;
        $vars = array();

        if ($id <= 0 && !in_array($table_name,array('fields_metainfo','job')) ) {
            $this->aml_html->error_page(array(sprintf(('Неверное значение параметра %s'), 'id')));
        }
        switch($table_name) {
            case 'online':
                if ($this->input->post('op')) {
                    $online_op_ti    = $this->aml_metainfo->get_table_info('TB_ONLINEOPERATIONS');

                    $online_op_update         = array();
                    $online_op_update_values  = array();
                    $online_op_update_pk      = array();
                    $online_op_extra          = array();

                    $params_idx = 1;
                    $varslist = array_keys($_POST);

                    foreach($varslist as $postvar) {
                        if (substr($postvar,0,strlen("TB_ONLINEOPERATIONS")) == "TB_ONLINEOPERATIONS") {
                            $tmp = substr($postvar, strlen("TB_ONLINEOPERATIONS") + 1);
                            $member_number = intval(substr($tmp,0, strpos($tmp,'_')));
                            // правильно ли передали название поля?
                            if ($member_number <= 0) {
                                continue;
                            }
                            $field_name = substr($tmp,strpos($tmp,'_') + 1);

                            // Primary Key ?
                            if ($field_name == 'ID') {
                                $online_op_update_pk[$member_number] = floatval($this->input->post($postvar));
                                continue;
                            }

                            if ($this->_is_system_field($field_name)) {
                                continue;
                            }

                            // проверить, можно ли это поле редактировать юзеру
                            $field_idx = $this->_get_field_index($online_op_ti, $field_name);

                            if ($field_idx === null) {
                                continue;
                            }
                            if ($online_op_ti['P_EDITABLE_BOOL'][$field_idx] != 1) {
                                continue;
                            }

                            // можно редактировать, пишем в базу
                            switch($online_op_ti['DATA_TYPE'][$field_idx]) {
                                case 'DATE':
                                    if (preg_match($this->config->item('regexp_date'), $this->input->post($postvar))){
                                        $online_op_update[$member_number][] = $field_name . ' = TO_DATE(:param' . $params_idx . ',\'' . $this->config->item('date_format') . '\')';
                                        $online_op_update_values[$member_number]['param' . $params_idx] = $this->input->post($postvar);
                                    } else if (preg_match($this->config->item('regexp_date_long'), $this->input->post($postvar))) {
                                        $online_op_update[$member_number][] = $field_name . ' = TO_DATE(:param' . $params_idx . ',\'' . $this->config->item('date_format_long') . '\')';
                                        $online_op_update_values[$member_number]['param' . $params_idx] = $this->input->post($postvar);
                                    } else {
                                        // передали корявую дату ?!, игнорим
                                    }
                                    break;
                                case 'NUMBER':
                                    $online_op_update[$member_number][] = $field_name . ' = :param' . $params_idx;
                                    $online_op_update_values[$member_number]['param' . $params_idx] = $this->input->post($postvar) == "" ? null : floatval(str_replace(" ","",$this->input->post($postvar)));
                                    break;
                                default:
                                    $online_op_update[$member_number][] = $field_name . ' = :param' . $params_idx;
                                    $online_op_update_values[$member_number]['param' . $params_idx] = $this->input->post($postvar);
                            }

                            $params_idx++;
                        }
                    }

                    foreach($online_op_update as $k => $v) {
                        // новая запись, нужен пустой insert?
                        $new_id = 9999999999;
                        if ($online_op_update_pk[$k] == 0) {
                            $stid =
                                $this->aml_oracle->execute(
                                    "BEGIN " .
                                    "  :new_id_sz100 := GetID(); " .
                                    "  INSERT INTO TB_ONLINEOPERATIONS(ID) VALUES(:new_id_sz100); " .
                                    "END;",__LINE__, array(':new_id_sz100' => &$new_id)
                                );
                            $online_op_update_pk[$k] = $new_id;
                        }

                        $bindings = array(':id' => $online_op_update_pk[$k]);
                        foreach($online_op_update_values[$k] as $param_name => $param_value) {
                            $bindings[':' . $param_name] = $online_op_update_values[$k][$param_name];
                        }
                        $update = "UPDATE TB_ONLINEOPERATIONS SET " . implode(',', $v) . ", P_USERNAME='".$this->aml_auth->get_username()."', P_DATE_UPDATE=sysdate WHERE ID = :id";
                        $stid = $this->aml_oracle->execute($update, __LINE__, $bindings,true, OCI_DEFAULT);
                    }
                    $this->aml_oracle->commit();

                    print '<script type="text/javascript">try { window.opener.$("#grid1").trigger("reloadGrid"); } catch(e){}; window.close();</script>';
                    die();
                }

                $readonly = 0;
                $output = '<fieldset class="viewdata">';
                $output .= '<legend>' . ('Онлайн операция') . '</legend>';
                $output .= $this->aml_html->create_form('TB_ONLINEOPERATIONS', $id);
                $output .= '<br style="clear:both" /><br style="clear:both" /></fieldset>';

                $vars['id'] = $id;
                $vars['hide_header'] = true;
                break;
            case 'offline':
                $readonly = 1;
                $output = '<fieldset class="viewdata">';
                $output .= '<legend>' . ('Проведенная операция') . '</legend>';
                $output .= $this->_create_form('TB_OFFLINEOPERATIONS', $id);
                $output .= '</fieldset>';

                $vars['id'] = $id;
                $vars['grid'] = $this->aml_metainfo->get_js_table_properties('TB_OFF_MEMBERS');
                $vars['hide_header'] = true;
                if ($this->show_susp_members_grid){
                    $output .= $this->load->view('view-off-operation-members', $vars, true);
                }
				/*$sql = 'SELECT f.* FROM tb_cor_accounts f, tb_offlineoperations o WHERE o.id = :sid and o.id = f.P_OFFLINEOPERATIONID'; 
				$bindings = array(':sid' => $id);
				$stid3 = $this->aml_oracle->execute($sql, __LINE__, $bindings);
				$tor = oci_fetch_array($stid3, OCI_ASSOC);
				if (count($tor['ID'][0]) != 0) {
					$sql1 = 'SELECT f.* FROM tb_cor_accounts f, tb_offlineoperations o WHERE o.id = :sid and o.id = f.P_OFFLINEOPERATIONID'; 
					$bindings1 = array(':sid' => $id);
					$stid4 = $this->aml_oracle->execute($sql1, __LINE__, $bindings1);
					$output .= '<fieldset class="viewdata"><legend>Кор. счета</legend>';
					while ($cor = oci_fetch_array($stid4, OCI_ASSOC)) {
						$output .= '<fieldset class="oddviewdata"><legend>Кор. счет</legend>';
						$output .= $this->aml_html->create_form('TB_COR_ACCOUNTS,off', $cor['ID'],0,'ID');
						$output .= '</fieldset>';
					}
					$output .= '</fieldset>';
				}*/
                break;
            case 'audit':
                $vars['hide_header'] = true;
                $this->aml_html->set_readonly(true);
                $output = '<fieldset class="viewdata">';
                $output .= '<legend>' . ('Событие (аудит)') . '</legend>';
                $output .= $this->aml_html->create_form('TB_AUDIT_ALL', $id);
                $output .= '</fieldset>';
                break;
            case 'branch':
                if ($this->input->post('op')) {
                    $branch_ti    = $this->aml_metainfo->get_table_info('TB_BRANCH',1);
                    $branch_update         = array();
                    $branch_update_values  = array();
                    $branch_update_pk      = array();
                    $branch_extra          = array();
                    $params_idx = 1;
                    $varslist = array_keys($_POST);

                    foreach($varslist as $postvar) {
                        if (substr($postvar,0,strlen("TB_BRANCH")) == "TB_BRANCH") {
                            $tmp = substr($postvar, strlen("TB_BRANCH") + 1);
                            $member_number = intval(substr($tmp,0, strpos($tmp,'_')));
                            // правильно ли передали название поля?
                            if ($member_number <= 0) {
                                continue;
                            }
                            $field_name = substr($tmp,strpos($tmp,'_') + 1);

                            // Primary Key ?
                            if ($field_name == 'ID') {
                                $branch_update_pk[$member_number] = floatval($this->input->post($postvar));
                                continue;
                            }

                            if ($this->_is_system_field($field_name)) {
                                continue;
                            }

                            // проверить, можно ли это поле редактировать юзеру
                            $field_idx = $this->_get_field_index($branch_ti, $field_name);

                            if ($field_idx === null) {
                                continue;
                            }
                            if ($branch_ti['P_EDITABLE_BOOL'][$field_idx] != 1) {
                                continue;
                            }

                            // можно редактировать, пишем в базу
                            switch($branch_ti['DATA_TYPE'][$field_idx]) {
                                case 'DATE':
                                    if (preg_match($this->config->item('regexp_date'), $this->input->post($postvar))){
                                        $branch_update[$member_number][] = $field_name . ' = TO_DATE(:param' . $params_idx . ',\'' . $this->config->item('date_format') . '\')';
                                        $branch_update_values[$member_number]['param' . $params_idx] = $this->input->post($postvar);
                                    } else if (preg_match($this->config->item('regexp_date_long'), $this->input->post($postvar))) {
                                        $branch_update[$member_number][] = $field_name . ' = TO_DATE(:param' . $params_idx . ',\'' . $this->config->item('date_format_long') . '\')';
                                        $branch_update_values[$member_number]['param' . $params_idx] = $this->input->post($postvar);
                                    } else {
                                        // передали корявую дату ?!, игнорим
                                    }
                                    break;
                                case 'NUMBER':
                                    $branch_update[$member_number][] = $field_name . ' = :param' . $params_idx;
                                    $branch_update_values[$member_number]['param' . $params_idx] = $this->input->post($postvar) == "" ? null : floatval(str_replace(" ","",$this->input->post($postvar)));
                                    break;
                                default:
                                    $branch_update[$member_number][] = $field_name . ' = :param' . $params_idx;
                                    $branch_update_values[$member_number]['param' . $params_idx] = $this->input->post($postvar);
                            }
                            $params_idx++;
                        }
                    }

                    foreach($branch_update as $k => $v) {
                        // новая запись, нужен пустой insert?
                        $new_id = 9999999999;
                        if ($branch_update_pk[$k] == 0) {
                            $stid =
                                $this->aml_oracle->execute(
                                    "BEGIN " .
                                    "  :new_id_sz100 := GetID(); " .
                                    "  INSERT INTO TB_BRANCH(ID) VALUES(:new_id_sz100); " .
                                    "END;",__LINE__, array(':new_id_sz100' => &$new_id)
                                );
                            $branch_update_pk[$k] = $new_id;
                        }

                        $bindings = array(':id' => $branch_update_pk[$k]);
                        foreach($branch_update_values[$k] as $param_name => $param_value) {
                            $bindings[':' . $param_name] = $branch_update_values[$k][$param_name];
                        }
                        $update = 'UPDATE TB_BRANCH SET ' . implode(',', $v) . ' WHERE ID = :id';
                        $stid = $this->aml_oracle->execute($update, __LINE__, $bindings,true, OCI_DEFAULT);
                    }
                    $this->aml_oracle->commit();

                    print '<script type="text/javascript">window.close();</script>';
                    //header('Location: ' . site_url('page/managebranches'));
                    die();
                }
                $vars['hide_header'] = true;
                $output = '<fieldset class="viewdata">';
                $output .= '<legend>' . ('Филиал') . '</legend>';
                $output .= $this->_create_form('TB_BRANCH', $id);
                $output .= '</fieldset>';
                break;
            case 'client':
                $vars['hide_header'] = true;

                if ($this->input->post('op')) {
                    $members_ti    = $this->aml_metainfo->get_table_info('TB_SUSPICIOUSMEMBERS');
                    $founders_ti   = $this->aml_metainfo->get_table_info('TB_SUSPICIOUSFOUNDERS');

                    $susp_members_update         = array();
                    $susp_members_update_values  = array();
                    $susp_members_update_pk      = array();
                    $susp_members_extra          = array();
                    $susp_founders_update        = array();
                    $susp_founders_update_values = array();
                    $susp_founders_update_pk     = array();
                    $susp_founders_extra         = array();

                    $params_idx = 1;
                    $varslist = array_keys($_POST);

                    foreach($varslist as $postvar) {
                        if (substr($postvar,0,strlen("TB_SUSPICIOUSMEMBERS")) == "TB_SUSPICIOUSMEMBERS") {
                            $tmp = substr($postvar, strlen("TB_SUSPICIOUSMEMBERS") + 1);
                            $member_number = intval(substr($tmp,0, strpos($tmp,'_')));
                            // правильно ли передали название поля?
                            if ($member_number <= 0) {
                                continue;
                            }
                            $field_name = substr($tmp,strpos($tmp,'_') + 1);

                            // Primary Key ?
                            if ($field_name == 'ID') {
                                $susp_members_update_pk[$member_number] = floatval($this->input->post($postvar));
                                continue;
                            }

                            // проверить, существует поле с таким именем в таблице
                            if (!$this->_check_table_field_exists($members_ti, $field_name)) {
                                if ($field_name == 'BY_CREDIT_BOOL') {
                                    $susp_members_extra[$member_number]['BY_CREDIT_BOOL'] = intval($this->input->post($postvar));
                                }
                                if ($field_name == 'REFERENCE_OPERATION_ID') {
                                    $susp_members_extra[$member_number]['REFERENCE_OPERATION_ID'] = intval($this->input->post($postvar));
                                }
                                continue;
                            }

                            if ($this->_is_system_field($field_name)) {
                                continue;
                            }

                            // проверить, можно ли это поле редактировать юзеру
                            $field_idx = $this->_get_field_index($members_ti, $field_name);

                            if ($field_idx === null) {
                                continue;
                            }
                            if ($members_ti['P_EDITABLE_BOOL'][$field_idx] != 1) {
                                continue;
                            }

                            // можно редактировать, пишем в базу
                            switch($members_ti['DATA_TYPE'][$field_idx]) {
                                case 'DATE':
                                    if (preg_match($this->config->item('regexp_date'), $this->input->post($postvar))){
                                        $susp_members_update[$member_number][] = $field_name . ' = TO_DATE(:param' . $params_idx . ',\'' . $this->config->item('date_format') . '\')';
                                        $susp_members_update_values[$member_number]['param' . $params_idx] = $this->input->post($postvar);
                                    } else if (preg_match($this->config->item('regexp_date_long'), $this->input->post($postvar))) {
                                        $susp_members_update[$member_number][] = $field_name . ' = TO_DATE(:param' . $params_idx . ',\'' . $this->config->item('date_format_long') . '\')';
                                        $susp_members_update_values[$member_number]['param' . $params_idx] = $this->input->post($postvar);
                                    } else {
                                        // передали корявую дату ?!, игнорим
                                    }
                                    break;
                                case 'NUMBER':
                                    $susp_members_update[$member_number][] = $field_name . ' = :param' . $params_idx;
                                    $susp_members_update_values[$member_number]['param' . $params_idx] = $this->input->post($postvar) == "" ? null : floatval(str_replace(" ","",$this->input->post($postvar)));
                                    break;
                                default:
                                    $susp_members_update[$member_number][] = $field_name . ' = :param' . $params_idx;
                                    $susp_members_update_values[$member_number]['param' . $params_idx] = $this->input->post($postvar);
                            }

                            $params_idx++;
                        } else if (substr($postvar,0,strlen("TB_SUSPICIOUSFOUNDERS")) == "TB_SUSPICIOUSFOUNDERS") {
                            $tmp = substr($postvar, strlen("TB_SUSPICIOUSFOUNDERS") + 1);
                            $founder_number = intval(substr($tmp,0, strpos($tmp,'_')));
                            // правильно ли передали название поля?
                            if ($founder_number <= 0) {
                                continue;
                            }
                            $field_name = substr($tmp,strpos($tmp,'_') + 1);

                            // Primary Key ?
                            if ($field_name == 'ID') {
                                $susp_founders_update_pk[$founder_number] = floatval($this->input->post($postvar));
                                continue;
                            }
                            // проверить, существует поле с таким именем в таблице
                            if (!$this->_check_table_field_exists($founders_ti, $field_name)) {
                                if ($field_name == 'PARENT_MEMBER_ID') {
                                    $susp_founders_extra[$founder_number]['PARENT_MEMBER_ID'] = intval($this->input->post($postvar));
                                }
                                continue;
                            }

                            if ($this->_is_system_field($field_name)) {
                                continue;
                            }

                            // проверить, можно ли это поле редактировать юзеру
                            $field_idx = $this->_get_field_index($founders_ti, $field_name);
                            if ($field_idx === null) {
                                continue;
                            }
                            if ($founders_ti['P_EDITABLE_BOOL'][$field_idx] != 1) {
                                continue;
                            }

                            // можно редактировать, пишем в базу
                            switch($founders_ti['DATA_TYPE'][$field_idx]) {
                                case 'DATE':
                                    if (preg_match($this->config->item('regexp_date'), $this->input->post($postvar))){
                                        $susp_founders_update[$founder_number][] = $field_name . ' = TO_DATE(:param' . $params_idx . ',\'' . $this->config->item('date_format') . '\')';
                                        $susp_founders_update_values[$founder_number]['param' . $params_idx] = $this->input->post($postvar);
                                    } else if (preg_match($this->config->item('regexp_date_long'), $this->input->post($postvar))) {
                                        $susp_founders_update[$founder_number][] = $field_name . ' = TO_DATE(:param' . $params_idx . ',\'' . $this->config->item('date_format_long') . '\')';
                                        $susp_founders_update_values[$founder_number]['param' . $params_idx] = $this->input->post($postvar);
                                    } else {
                                        // передали корявую дату ?!, игнорим
                                    }
                                    break;
                                case 'NUMBER':
                                    $susp_founders_update[$founder_number][] = $field_name . ' = :param' . $params_idx;
                                    $susp_founders_update_values[$founder_number]['param' . $params_idx] = $this->input->post($postvar) == "" ? null : floatval(str_replace(" ","",$this->input->post($postvar)));
                                    break;
                                default:
                                    $susp_founders_update[$founder_number][] = $field_name . ' = :param' . $params_idx;
                                    $susp_founders_update_values[$founder_number]['param' . $params_idx] = $this->input->post($postvar);
                            }
                            $params_idx++;
                        }
                    }

                    foreach($susp_members_update as $k => $v) {
                        // новая запись, нужен пустой insert?
                        $new_id = 9999999999;
                        if ($susp_members_update_pk[$k] == 0) {
                            $stid =
                                $this->aml_oracle->execute("BEGIN " .
                                                    "  :new_id_sz100 := GetID(); " .
                                                    "  INSERT INTO TB_SUSPICIOUSMEMBERS(ID,P_CLIENTID) VALUES(:new_id_sz100, /*'C' || :new_id_sz100*/ NULL); " .
                                                    "END;",__LINE__, array(':new_id_sz100' => &$new_id)
                                );
                            $susp_members_update_pk[$k] = $new_id;
                        }

                        $bindings = array(':id' => $susp_members_update_pk[$k]);
                        foreach($susp_members_update_values[$k] as $param_name => $param_value) {
                            $bindings[':' . $param_name] = $susp_members_update_values[$k][$param_name];
                        }
                        $update = 'UPDATE TB_SUSPICIOUSMEMBERS SET ' . implode(',', $v) . ' WHERE ID = :id';
                        $stid = $this->aml_oracle->execute($update, __LINE__, $bindings,true, OCI_DEFAULT);

                        // нужно ли проставить линки в полях операции?
                        if ($susp_members_extra[$k]['REFERENCE_OPERATION_ID'] > 0) {
                            if ($susp_members_extra[$k]['BY_CREDIT_BOOL']) {
                                $field = 't.P_CREDITCLIENTID = :clid';
                                $field_control = ' AND t.P_CREDITCLIENTID IS NULL';
                            } else {
                                $field = 't.P_DEBITCLIENTID = :clid';
                                $field_control = ' AND t.P_DEBITCLIENTID IS NULL';
                            }

                            $new_client_id = 'C' . $new_id;
                            $q = 'UPDATE TB_SUSPICIOUSOPERATIONS t SET ' . $field . ' WHERE t.ID = :id1 ' . $field_control;
                            $stid = $this->aml_oracle->execute($q, __LINE__, array(':clid' => $new_client_id,':id1' => $susp_members_extra[$k]['REFERENCE_OPERATION_ID']));
                        }
                    }

                    foreach($susp_founders_update as $k => $v) {
                        // новая запись, нужен пустой insert?
                        $new_id = 9999999999;
                        if ($susp_founders_update_pk[$k] == 0) {
                            $stid =
                                $this->aml_oracle->execute("BEGIN " .
                                                    "  :new_id_sz100 := GetID(); " .
                                                    "  INSERT INTO TB_SUSPICIOUSFOUNDERS(ID,P_SUSPICIOUSMEMBERID) VALUES(:new_id_sz100, :member_id); " .
                                                    "END;", __LINE__, array(':new_id_sz100' => &$new_id, ':member_id' => $susp_founders_extra[$k]['PARENT_MEMBER_ID'])
                                );
                            $susp_founders_update_pk[$k] = $new_id;
                        }

                        $bindings = array(':id' => $susp_founders_update_pk[$k]);
                        foreach($susp_founders_update_values[$k] as $param_name => $param_value) {
                            $bindings[':' . $param_name] = $susp_founders_update_values[$k][$param_name];
                        }
                        $update = 'UPDATE TB_SUSPICIOUSFOUNDERS SET ' . implode(',', $v) . ' WHERE ID = :id';
                        $stid = $this->aml_oracle->execute($update,__LINE__, $bindings,true, OCI_DEFAULT);
                    }
                    $this->aml_oracle->commit();
                }
                $q = 'SELECT * FROM TB_SUSPICIOUSMEMBERS t WHERE t.id = :id';
                $stid = $this->aml_oracle->execute($q, __LINE__, array(':id' => $id));
                oci_fetch_all($stid, $results);

                $this->aml_html->set_deletable(true);
                $output .= $this->aml_html->create_member_form($results,0,false,0,0,1);

                $vars['hide_header'] = true;
                $vars['create_credit_client_link'] = strlen(trim($results['P_CREDITCLIENTID'][0])) > 0 ? false : true;
                $vars['create_debit_client_link']  = strlen(trim($results['P_DEBITCLIENTID'][0])) > 0  ? false : true;
                $vars['credit_url']        = site_url('page/create_member_stub_form/1/' . $results['ID'][0]);
                $vars['debit_url']         = site_url('page/create_member_stub_form/0/' . $results['ID'][0]);
                $vars['add_founder_url']   = site_url('page/create_founder_stub_form');
                $vars['delete_member_url'] = site_url('page/deleteitem/member');
                $vars['id'] = $id;
                $vars['delete_founder_url']  = site_url('page/deleteitem/founder');
                $vars['conf_delete_str'] = ('Удалить?');

                $vars['run_js'] = $this->load->view('js-members-founders', $vars, true);
                break;
            case 'user':
                $can_admin = $this->aml_security->check_privilege(24); // ADMIN

                if (!$can_admin) {
                    $this->aml_html->error_page(array(('Отсутствуют права для данного действия.')));
                }
                $vars['hide_header'] = true;
                $users_ti = $this->aml_metainfo->get_table_info('TB_USERS',1);
                // нажали кнопку сохранения
                if ($this->input->post('op')) {
                    $update = array();
                    $update_values = array();
                    $update_types = array();
                    $fields_params = array();
                    $params_idx = 0;
                    if (trim($this->input->post('reason')) == '') {
                        print <<< ENDL
                        <script type="text/javascript">alert("Незаполнена причина изменений"); window.close();</script>
ENDL;
                        die();
                    }

                    for($i = 0; $i < count($users_ti['COLUMN_NAME']); $i++) {
                        if ($users_ti['COLUMN_NAME'][$i] == 'ID' ||
                            $users_ti['COLUMN_NAME'][$i] == 'P_PASSWORD' ||
                            $users_ti['COLUMN_NAME'][$i] == 'P_USERNAME' ||
                            $users_ti['COLUMN_NAME'][$i] == 'P_LOCKED_BOOL') {
                            continue;
                        }
                        $postvar = 'TB_USERS_1_' . $users_ti['COLUMN_NAME'][$i];
                        $field_name = $users_ti['COLUMN_NAME'][$i];
                        if (isset($_POST[$postvar])) {
                            switch($users_ti['DATA_TYPE'][$field_idx]) {
                                case 'DATE':
                                    if (preg_match($this->config->item('regexp_date'), $this->input->post($postvar))){
                                        $update[] = $field_name . ' = TO_DATE(:param' . $params_idx . ',\'' . $this->config->item('date_format') . '\')';
                                        $update_values[0]['param' . $params_idx] = $this->input->post($postvar);
                                        $update_types['param' . $params_idx] = 'DATE';
                                    } else if (preg_match($this->config->item('regexp_date_long'), $this->input->post($postvar))) {
                                        $update[] = $field_name . ' = TO_DATE(:param' . $params_idx . ',\'' . $this->config->item('date_format_long') . '\')';
                                        $update_values[0]['param' . $params_idx] = $this->input->post($postvar);
                                        $update_types['param' . $params_idx] = 'DATE';
                                    } else {
                                        // передали корявую дату ?!, игнорим
                                    }
                                    break;
                                case 'NUMBER':
                                    $update[] = $field_name . ' = :param' . $params_idx;
                                    $update_values[0]['param' . $params_idx] = floatval($this->input->post($postvar));
                                    $update_types['param' . $params_idx] = 'NUMBER';
                                    break;
                                default:
                                	if($users_ti['P_EDIT_TYPE'][$fields_idx]=='date'){
	                                   if (preg_match($this->config->item('regexp_date'), $this->input->post($postvar))){
	                                        $update[] = $field_name . ' = TO_DATE(:param' . $params_idx . ',\'' . $this->config->item('date_format') . '\')';
	                                        $update_values[0]['param' . $params_idx] = $this->input->post($postvar);
	                                        $update_types['param' . $params_idx] = 'DATE';
	                                   } else if (preg_match($this->config->item('regexp_date_long'), $this->input->post($postvar))) {
	                                        $update[] = $field_name . ' = TO_DATE(:param' . $params_idx . ',\'' . $this->config->item('date_format_long') . '\')';
	                                        $update_values[0]['param' . $params_idx] = $this->input->post($postvar);
	                                        $update_types['param' . $params_idx] = 'DATE';
	                                   } else {
	                                        // передали корявую дату ?!, игнорим
	                                   }
                                	} else {
	                                    $update[] = $field_name . ' = :param' . $params_idx;
	                                    $update_values['param' . $params_idx] = $this->input->post($postvar);
	                                    $update_types['param' . $params_idx] = 'VARCHAR2';
	                                    if($users_ti['P_EDIT_TYPE'][$fields_idx]=='number'){
	                                    	$update_values['param'.$params_idx] *= 1;
	                                    }
                                    }
                            }
                            $fields_params['param' . $params_idx] = $field_name;
                            $params_idx++;
                        }
                    }

                    $q = 'SELECT * FROM tb_users t WHERE t.id = :id';
                    $bindings = array(':id' => $id);
                    $stid = $this->aml_oracle->execute($q, __LINE__, $bindings);
                    $user = oci_fetch_array($stid, OCI_ASSOC);
                    if (!empty($user['P_DELETED_DATE'])) {
                        $this->aml_html->error_page(array(('Данный пользователь был удален.')));
                    }

                    $update_sql = 'UPDATE TB_USERS SET ' . implode(',', $update) . ' WHERE ID = :id';

                    $bindings = array(':id' => $id);
                    $bindings_type = array(':id' => 'NUMBER');
                    $edited_fields = '';
                    foreach($update_values as $param_name => $param_value) {
                        $bindings[':' . $param_name] = $param_value;
                        $bindings_type[':' . $param_name] = $update_types[$param_name];
                        if ($param_value != $user[$fields_params[$param_name]]) {
                            $edited_fields .= $this->aml_metainfo->get_field_caption($users_ti, $fields_params[$param_name]) . ': ' . $user[$fields_params[$param_name]] . ' -> ' . $param_value . "\n";
                        }
                    }
                    $this->aml_admcontrol->execute($update_sql, __LINE__, $bindings, $bindings_type, 'USERS', ('Редактирование пользователя') . ' "' . $user['P_USERNAME'] . '" ' . "\n" . $edited_fields, $this->input->post('reason'));
                   // $stid = @$this->aml_oracle->execute($update_sql, __LINE__, $bindings,false);
                   
					$this->aml_oracle->commit();
                    print '<script type="text/javascript"> window.close();</script>';
                     
                    die();
                }

                $stid = $this->aml_oracle->execute('SELECT * FROM TB_USERS t WHERE t.ID = :id1', __LINE__, array(':id1' => $id));
                $nrows = oci_fetch_all($stid, $results_users);

                $output .= '<fieldset class="viewdata"><legend>' . ('Редактирование пользователя') . '</legend>';
                $output .= '<br style="clear:both" />';

                if (!empty($results_users['P_DELETED_DATE'][0])) {
                    $this->aml_html->set_readonly(true);
                    $readonly = true; // hide save button
                }
                $cur_group = 0;
                for($j = 0; $j < count($users_ti['COLUMN_NAME']); $j++) {
                    if ($users_ti['COLUMN_NAME'][$j] == 'P_PASSWORD' ||
                        $users_ti['COLUMN_NAME'][$j] == 'P_LOCKED_BOOL') {
                        continue;
                    }

                    if($users_ti['P_GROUPS'][$j]!=$cur_group){
                    	if($cur_group != 0){
                    		$output .= '</fieldset>';
                    	}
                    	if($users_ti['P_GROUPS'][$j]>0){
                    		$output .= "<fieldset class='inner_fieldset'><legend>".$users_ti['GROUP_NAME'][$j]."</legend>";
                    	}
                    	$cur_group = $users_ti['P_GROUPS'][$j];
                    }

                    if ($users_ti['COLUMN_NAME'][$j] == 'ID') {
                        $output .= $this->aml_html->create_pk_control('TB_USERS',$users_ti, $j, $results_users[$users_ti['COLUMN_NAME'][$j]][0], $i + 1) . "\n";
                    } else if ($users_ti['P_VISIBILITY_EDIT_BOOL'][$j] != 0) {
                        $output .= $this->aml_html->create_control('TB_USERS',$users_ti, $j, $results_users[$users_ti['COLUMN_NAME'][$j]][0], $i + 1) . "\n";
                    }
                }
                if($cur_group!=0){
                	$output .= "</fieldset>";
                }
                $output .= '<input type="hidden" name="reason" id="reason">';
                $output .= '<br style="clear:both" />';
                $output .= '<br style="clear:both" />';
                $output .= '</fieldset>';
                break;
            case 'fields_metainfo':
                $can_admin = $this->aml_security->check_privilege(24); // ADMIN
                if (!$can_admin) {
                    $this->aml_html->error_page(array(('Отсутствуют права для данного действия.')));
                }
                // нажали кнопку сохранения
                if ($this->input->post('op')) {
                    $mi_update           = array();
                    $mi_update_values    = array();
                    $mi_update_pk        = array();

                    $mi_ti = $this->aml_metainfo->get_table_info('TB_FIELDS_METAINFO');

                    $params_idx = 1;
                    $varslist = array_keys($_POST);

                    foreach($varslist as $postvar) {
                        // проверка значений для таблицы TB_FIELDS_METAINFO
                        if (substr($postvar,0,strlen("TB_FIELDS_METAINFO")) == "TB_FIELDS_METAINFO") {

                            $tmp = substr($postvar, strlen("TB_FIELDS_METAINFO") + 1);
                            $mi_number = intval(substr($tmp,0, strpos($tmp,'_')));

                            if ($mi_number <= 0) {
                                continue;
                            }
                            $field_name = substr($tmp,strpos($tmp,'_') + 1);

                            // Primary Key ?
                            if ($field_name == 'ID') {
                                $mi_update_pk[$mi_number] = floatval($this->input->post($postvar));
                                continue;
                            }

                            // проверить, существует поле с таким именем в таблице
                            if (!$this->aml_metainfo->check_table_field_exists($mi_ti, $field_name)) {
                                continue;
                            }

                            if ($this->_is_system_field($field_name)) {
                                continue;
                            }

                            // проверить, можно ли это поле редактировать юзеру
                            $field_idx = $this->aml_metainfo->get_field_index($mi_ti, $field_name);
                            if ($field_idx === null) {
                                continue;
                            }

                            // можно редактировать, пишем в базу
                            switch($mi_ti['DATA_TYPE'][$field_idx]) {
                                case 'DATE':
                                    if (preg_match($this->config->item('regexp_date'), $this->input->post($postvar))){
                                        $mi_update[$mi_number][] = $field_name . ' = TO_DATE(:param' . $params_idx . ',\'' . $this->config->item('date_format') . '\')';
                                        $mi_update_values[$mi_number]['param' . $params_idx] = $this->input->post($postvar);
                                    } else if (preg_match($this->config->item('regexp_date_long'), $this->input->post($postvar))) {
                                        $mi_update[$mi_number][] = $field_name . ' = TO_DATE(:param' . $params_idx . ',\'' . $this->config->item('date_format_long') . '\')';
                                        $mi_update_values[$mi_number]['param' . $params_idx] = $this->input->post($postvar);
                                    } else {
                                        // передали корявую дату ?!, игнорим
                                    }
                                    break;
                                case 'NUMBER':
                                    $mi_update[$mi_number][] = $field_name . ' = :param' . $params_idx;
                                    $mi_update_values[$mi_number]['param' . $params_idx] = $this->input->post($postvar) === "" ? null : floatval($this->input->post($postvar));
                                    break;
                                default:
                                    $mi_update[$mi_number][] = $field_name . ' = :param' . $params_idx;
                                    $mi_update_values[$mi_number]['param' . $params_idx] = $this->input->post($postvar);
                            }
                            $params_idx++;
                        }
                    }

                    //положить данные в базу
                    foreach($mi_update as $k => $v) {
                        $update = 'UPDATE TB_FIELDS_METAINFO SET ' . implode(',', $v) . ' WHERE ID = :id';

                        $bindings = array(':id' => $mi_update_pk[$k]);
                        foreach($mi_update_values[$k] as $param_name => $param_value) {
                            $bindings[':' . $param_name] = $mi_update_values[$k][$param_name];
                        }

                        $stid = $this->aml_oracle->execute($update, __LINE__, $bindings);
                    }

                }
				header('Location: ' . site_url('page/managegui/' . htmlentities($this->input->post('what'), ENT_QUOTES, 'utf-8')));
                break;
            case 'suspicious':
                $vars['hide_header'] = true;
				$readonly_role = $this->aml_security->check_privilege(56) || $this->aml_security->check_privilege(91); // Readonly role
				if ($readonly_role) {
					$readonly = 1;
				}
				else {
				$readonly = 0;
				}
				
                //$this->aml_context->set_general_vars($vars);

                $suspicious_ti = $this->aml_metainfo->get_table_info('TB_SUSPICIOUSOPERATIONS',1);
                $members_ti    = $this->aml_metainfo->get_table_info('TB_SUSPICIOUSMEMBERS',1);
                $founders_ti   = $this->aml_metainfo->get_table_info('TB_SUSPICIOUSFOUNDERS',1);
				//$cor_ti   = $this->aml_metainfo->get_table_info('TB_COR_ACCOUNTS',1);
				
                $show_rollback_button = true;
                // нажали кнопку сохранения
                if ($this->input->post('op')) {
                    $susp_opers_update           = array();
                    $susp_opers_update_values    = array();
                    $susp_opers_update_pk        = array();
                    $susp_members_update         = array();
                    $susp_members_update_values  = array();
                    $susp_members_update_pk      = array();
                    $susp_members_extra          = array();
                    $susp_founders_update        = array();
                    $susp_founders_update_values = array();
                    $susp_founders_update_pk     = array();
                    $susp_founders_extra         = array();
					//$susp_cor_update        = array();
					//$susp_cor_update_values = array();
					//$susp_cor_update_pk     = array();
					//$susp_cor_extra         = array();
					
                    $params_idx = 1;
                    $varslist = array_keys($_POST);

                    foreach($varslist as $postvar) {
                        // проверка значений для таблицы TB_SUSPICIOUSOPERATIONS
                        if (substr($postvar,0,strlen("TB_SUSPICIOUSOPERATIONS")) == "TB_SUSPICIOUSOPERATIONS") {
                            $tmp = substr($postvar, strlen("TB_SUSPICIOUSOPERATIONS") + 1);
                            $susp_oper_number = intval(substr($tmp,0, strpos($tmp,'_')));
                            // правильно ли передали название поля?
                            if ($susp_oper_number <= 0) {
                                continue;
                            }
                            $field_name = substr($tmp,strpos($tmp,'_') + 1);

                            // Primary Key ?
                            if ($field_name == 'ID') {
                                $susp_opers_update_pk[$susp_oper_number] = floatval($this->input->post($postvar));
                                continue;
                            }

                            // проверить, существует поле с таким именем в таблице
                            if (!$this->_check_table_field_exists($suspicious_ti, $field_name)) {
                                continue;
                            }

                            if ($this->_is_system_field($field_name)) {
                                continue;
                            }

                            // проверить, можно ли это поле редактировать юзеру
                            $field_idx = $this->_get_field_index($suspicious_ti, $field_name);
                            if ($field_idx === null) {
                                continue;
                            }
                            if ($suspicious_ti['P_EDITABLE_BOOL'][$field_idx] != 1) {
                                continue;
                            }

                            // можно редактировать, пишем в базу
                            switch($suspicious_ti['DATA_TYPE'][$field_idx]) {
                                case 'DATE':
                                    if (preg_match($this->config->item('regexp_date'), $this->input->post($postvar))){
                                        $susp_opers_update[$susp_oper_number][] = $field_name . ' = TO_DATE(:param' . $params_idx . ',\'' . $this->config->item('date_format') . '\')';
                                        $susp_opers_update_values[$susp_oper_number]['param' . $params_idx] = $this->input->post($postvar);
                                    } else if (preg_match($this->config->item('regexp_date_long'), $this->input->post($postvar))) {
                                        $susp_opers_update[$susp_oper_number][] = $field_name . ' = TO_DATE(:param' . $params_idx . ',\'' . $this->config->item('date_format_long') . '\')';
                                        $susp_opers_update_values[$susp_oper_number]['param' . $params_idx] = $this->input->post($postvar);
                                    } else {
                                        // передали корявую дату ?!, игнорим
                                    }
                                    break;
                                case 'NUMBER':
                                    $susp_opers_update[$susp_oper_number][] = $field_name . ' = :param' . $params_idx;
                                    $susp_opers_update_values[$susp_oper_number]['param' . $params_idx] = $this->input->post($postvar) == "" ? null : floatval(str_replace(" ","",$this->input->post($postvar)));
                                    break;
                                default:
                                    $susp_opers_update[$susp_oper_number][] = $field_name . ' = :param' . $params_idx;
                                    $susp_opers_update_values[$susp_oper_number]['param' . $params_idx] = $this->input->post($postvar);
                            }
                            $params_idx++;
                        }
						
						/*// проверка значений для таблицы TB_COR_ACCOUNTS
						if (substr($postvar,0,strlen("TB_COR_ACCOUNTS")) == "TB_COR_ACCOUNTS") {
							$tmp = substr($postvar, strlen("TB_COR_ACCOUNTS") + 1);
							$susp_cor_number = intval(substr($tmp,0, strpos($tmp,'_')));
							// правильно ли передали название поля?
							if ($susp_cor_number <= 0) {
								continue;
							}
							$field_name = substr($tmp,strpos($tmp,'_') + 1);
							 // Primary Key ?
							if ($field_name == 'ID') {
								$susp_cor_update_pk[$susp_cor_number] = floatval($this->input->post($postvar));
								continue;
							}
							// проверить, существует поле с таким именем в таблице
							if (!$this->_check_table_field_exists($cor_ti, $field_name)) {
								continue;
							}
							if ($this->_is_system_field($field_name)) {
								continue;
							}
							// проверить, можно ли это поле редактировать юзеру
							$field_idx = $this->_get_field_index($cor_ti, $field_name);
							if ($field_idx === null) {
								continue;
							}
							if ($cor_ti['P_EDITABLE_BOOL'][$field_idx] != 1) {
								continue;
							}
							// можно редактировать, пишем в базу
							switch($cor_ti['DATA_TYPE'][$field_idx]) {
								case 'DATE':
									if (preg_match($this->config->item('regexp_date'), $this->input->post($postvar))){
										$susp_cor_update[$susp_cor_number][] = $field_name . ' = TO_DATE(:param' . $params_idx . ',\'' . $this->config->item('date_format') . '\')';
										$susp_cor_update_values[$susp_cor_number]['param' . $params_idx] = $this->input->post($postvar);
									} else if (preg_match($this->config->item('regexp_date_long'), $this->input->post($postvar))) {
										$susp_cor_update[$susp_cor_number][] = $field_name . ' = TO_DATE(:param' . $params_idx . ',\'' . $this->config->item('date_format_long') . '\')';
										$susp_cor_update_values[$susp_cor_number]['param' . $params_idx] = $this->input->post($postvar);
									} else {
									// передали корявую дату ?!, игнорим
									}
								break;
								case 'NUMBER':
									$susp_cor_update[$susp_cor_number][] = $field_name . ' = :param' . $params_idx;
									$susp_cor_update_values[$susp_cor_number]['param' . $params_idx] = $this->input->post($postvar) == "" ? null : floatval(str_replace(" ","",$this->input->post($postvar)));
								break;
								default:
									$susp_cor_update[$susp_cor_number][] = $field_name . ' = :param' . $params_idx;
									$susp_cor_update_values[$susp_cor_number]['param' . $params_idx] = $this->input->post($postvar);
							}
							$params_idx++;
						}*/
                    }
					
                    // валяем update-ы
                    //$updates = array();
                    foreach($susp_opers_update as $k => $v) {
                        $update = 'UPDATE TB_SUSPICIOUSOPERATIONS SET ' . implode(',', array_merge($v, array('P_DATE_UPDATE = SYSDATE', 'P_USERNAME = :currentusername'))) . ' WHERE ID = :id';

                        $bindings = array(
                            ':currentusername' => $this->aml_auth->get_username(),
                            ':id' => $susp_opers_update_pk[$k]
                        );
                        foreach($susp_opers_update_values[$k] as $param_name => $param_value) {
                            $bindings[':' . $param_name] = $susp_opers_update_values[$k][$param_name];
                        }
                        $stid = $this->aml_oracle->execute($update, __LINE__, $bindings);
                    }

					/*foreach($susp_cor_update as $k => $v) {
						$update = 'UPDATE TB_COR_ACCOUNTS SET ' . implode(',', $v) . ' WHERE ID = :id';
						$bindings = array(
							':id' => $susp_cor_update_pk[$k]
						);
						foreach($susp_cor_update_values[$k] as $param_name => $param_value) {
							$bindings[':' . $param_name] = $susp_cor_update_values[$k][$param_name];
						}
						$stid = $this->aml_oracle->execute($update, __LINE__, $bindings);
					}*/
					
                    foreach($susp_members_update as $k => $v) {
                        // новая запись, нужен пустой insert?
                        $new_id = 9999999999;
                        if ($susp_members_update_pk[$k] == 0) {
                            $stid =
                                $this->aml_oracle->execute("BEGIN " .
                                                    "  :new_id_sz100 := GetID(); " .
                                                    "  INSERT INTO TB_SUSPICIOUSMEMBERS(ID,P_CLIENTID) 
													VALUES(:new_id_sz100, 'C' || :new_id_sz100); " .
                                                    "END;",__LINE__, array(':new_id_sz100' => &$new_id)
                                );
                            $susp_members_update_pk[$k] = $new_id;
                        }

                        $bindings = array(':id' => $susp_members_update_pk[$k]);
                        foreach($susp_members_update_values[$k] as $param_name => $param_value) {
                            $bindings[':' . $param_name] = $susp_members_update_values[$k][$param_name];
                        }
                        $update = 'UPDATE TB_SUSPICIOUSMEMBERS SET ' . implode(',', $v) . ' WHERE ID = :id';
                        $stid = $this->aml_oracle->execute($update, __LINE__, $bindings,true, OCI_DEFAULT);

                        // нужно ли проставить линки в полях операции?
                        if ($susp_members_extra[$k]['REFERENCE_OPERATION_ID'] > 0) {
                            if ($susp_members_extra[$k]['BY_CREDIT_BOOL']) {
                                $field = 't.P_CREDITCLIENTID = :clid';
                                $field_control = ' AND t.P_CREDITCLIENTID IS NULL';
                            } else {
                                $field = 't.P_DEBITCLIENTID = :clid';
                                $field_control = ' AND t.P_DEBITCLIENTID IS NULL';
                            }

                            $new_client_id = 'C' . $new_id;
                            $q = 'UPDATE TB_SUSPICIOUSOPERATIONS t SET ' . $field . ' WHERE t.ID = :id1 ' . $field_control;
                            $stid = $this->aml_oracle->execute($q, __LINE__, array(':clid' => $new_client_id,':id1' => $susp_members_extra[$k]['REFERENCE_OPERATION_ID']));
                        }
                    }

                    foreach($susp_founders_update as $k => $v) {
                        // новая запись, нужен пустой insert?
                        $new_id = 9999999999;
                        if ($susp_founders_update_pk[$k] == 0) {
                            $stid =
                                $this->aml_oracle->execute("BEGIN " .
                                                    "  :new_id_sz100 := GetID(); " .
                                                    "  INSERT INTO TB_SUSPICIOUSFOUNDERS(ID,P_SUSPICIOUSMEMBERID) VALUES(:new_id_sz100, :member_id); " .
                                                    "END;", __LINE__, array(':new_id_sz100' => &$new_id, ':member_id' => $susp_founders_extra[$k]['PARENT_MEMBER_ID'])
                                );
                            $susp_founders_update_pk[$k] = $new_id;
                        }

                        $bindings = array(':id' => $susp_founders_update_pk[$k]);
                        foreach($susp_founders_update_values[$k] as $param_name => $param_value) {
                            $bindings[':' . $param_name] = $susp_founders_update_values[$k][$param_name];
                        }
                        $update = 'UPDATE TB_SUSPICIOUSFOUNDERS SET ' . implode(',', $v) . ' WHERE ID = :id';
                        $stid = $this->aml_oracle->execute($update,__LINE__, $bindings,true, OCI_DEFAULT);
                    }
                    
					/*foreach($susp_cor_update as $k => $v) {
						// новая запись, нужен пустой insert?
						$new_id = 9999999999;
						$ddd = "SELECT P_OFFLINEOPERATIONID FROM TB_SUSPICIOUSOPERATIONS WHERE id = :id";
						$binds2 = array(':id'=>$id);
						$st = $this->aml_oracle->execute($ddd,__LINE__,$binds2);
						oci_fetch_all($st, $res);
						$offId = $res['P_OFFLINEOPERATIONID'][0];
						if ($susp_cor_update_pk[$k] == 0) {
							$stid =
							$this->aml_oracle->execute("BEGIN " .
								"  :new_id_sz100 := GetID(); " .
								"  INSERT INTO TB_COR_ACCOUNTS(ID,P_OFFLINEOPERATIONID) VALUES(:new_id_sz100,:bankId); " .
								"END;", __LINE__, array(':new_id_sz100' => &$new_id, ':bankId'=>$offId)
							);
							$susp_cor_update_pk[$k] = $new_id;
						}
						$bindings = array(':id' => $susp_cor_update_pk[$k]);
						foreach($susp_cor_update_values[$k] as $param_name => $param_value) {
							$bindings[':' . $param_name] = $susp_cor_update_values[$k][$param_name];
						}
						$update = 'UPDATE TB_COR_ACCOUNTS SET ' . implode(',', $v) . ' WHERE ID = :id';
						$stid = $this->aml_oracle->execute($update,__LINE__, $bindings,true, OCI_DEFAULT);
					}*/
					
					$this->aml_oracle->commit();
					
                    print '<script type="text/javascript">window.close();</script>';
                    die();
                }

                // основная запись
                $stid = $this->aml_oracle->execute('SELECT * FROM TB_SUSPICIOUSOPERATIONS t WHERE t.ID = :id',__LINE__,array(':id' => $id));
                $nrows = oci_fetch_all($stid, $results);

                if (!$nrows) {
                    $this->aml_html->error_page(array(sprintf(('Запись не найдена, ID: %d'), $id)));
                }

                $component_number = 0;
                $output .= '<fieldset class="viewdata"><legend>' .('Операция') . '</legend>';
                $cur_group = 0;
                for($i = 0; $i < count($suspicious_ti['COLUMN_NAME']); $i++) {
                    if($suspicious_ti['P_GROUPS'][$i]!=$cur_group){
                    	if($cur_group != 0){
                    		$output .= '</fieldset>';
                    	}
                    	if($suspicious_ti['P_GROUPS'][$i]!=0){
                    		$output .= "<fieldset class='inner_fieldset'><legend>".$suspicious_ti['GROUP_NAME'][$i]."</legend>";
                    	}
                    	$cur_group = $suspicious_ti['P_GROUPS'][$i];
                    }
                    if ($suspicious_ti['COLUMN_NAME'][$i] == 'ID') {
                        $output .= $this->_create_pk_control('TB_SUSPICIOUSOPERATIONS',$suspicious_ti, $i, $results[$suspicious_ti['COLUMN_NAME'][$i]][0]) . "\n";
                    } else if ($suspicious_ti['P_VISIBILITY_EDIT_BOOL'][$i] == 1) {
                        $component_number++;

                        if ($suspicious_ti['P_DIRECTORY_OBJECT'][$i] == 'TB_DICT_SUSPIC_TYPE'){
                            $susp_where = array();
                            $susp_oper_privileges = array(21, 15, 17, 12); // suspicious operations roles
                            $fm_oper_privileges = array(20, 19, 16, 11);   // financial monitoring operations roles
                            if ($this->aml_security->check_privileges_with_or($susp_oper_privileges)) {
                                $susp_where[] = " P_CODE >1 ";
                            }
                            if ($this->aml_security->check_privileges_with_or($fm_oper_privileges)) {
                                $susp_where[] = " P_CODE = '1' ";
                            }
                            $susp_where_sql = implode(' OR ', $susp_where);
                        } else {
                            $susp_where_sql = '';
                        }

                        $output .= $this->aml_html->create_control('TB_SUSPICIOUSOPERATIONS',$suspicious_ti, $i, $results[$suspicious_ti['COLUMN_NAME'][$i]][0],1, $susp_where_sql) . "\n";
                        if ($component_number % $this->max_components_on_line == 0) {
                            //$output .= '<br style="clear:both">';
                        }
                    }
                }
                if($cur_group!=0){
                	$output .= "</fieldset>";
                }
                $output .= '<br style="clear:both">';
                $output .= '</fieldset>';

                $output .= '<fieldset class="viewdata"><legend>' .('Прикрепленные файлы') . '</legend>';
                $q = 'SELECT * FROM TB_SUSPIC_ATTACHMENTS t WHERE t.p_suspic_id = :id';
                $stid = $this->aml_oracle->execute($q, __LINE__, array(':id' => $id));
                $vars['files'] = array();
                $vars['readonly'] = $readonly;
                $vars['operation_id'] = $id;
                $vars['files_type'] = 'suspic_attachments';
                while ($f = oci_fetch_array($stid,OCI_ASSOC + OCI_RETURN_NULLS)) {
                    $vars['files'][] = $f;
                }
                $output .= $this->load->view('operations/attached-files', $vars, true);
                $output .= '</fieldset>';
				// ########  Вывод связной формы ########
				if ($id >0){
					$vars['parent_id'] = $id;
					$vars['grid_linked'] = $this->aml_metainfo->get_js_table_properties('VW_LINKED_FORMS');
					$output .= $this->load->view('linkedforms/view', $vars, true);
				}
				// ########  Вывод связной формы ########


                // теперь участники
                $members_ti = $this->aml_metainfo->get_table_info('TB_SUSPICIOUSMEMBERS');

                $stid = $this->aml_oracle->execute('SELECT *
                                                     FROM TB_SUSPICIOUSMEMBERS t
                                                     WHERE t.P_CLIENTID = TO_CHAR(:id1) OR t.P_CLIENTID = TO_CHAR(:id2)', __LINE__,
                                                    array(':id1' => $results['P_CREDITCLIENTID'][0],
                                                          ':id2' => $results['P_DEBITCLIENTID'][0]));

                $nrows = oci_fetch_all($stid, $results_members);

                $vars['create_credit_client_link'] = strlen(trim($results['P_CREDITCLIENTID'][0])) > 0 ? false : true;
                $vars['create_debit_client_link']  = strlen(trim($results['P_DEBITCLIENTID'][0])) > 0  ? false : true;
                $vars['credit_url']        = site_url('page/create_member_stub_form/1/' . $results['ID'][0]);
                $vars['debit_url']         = site_url('page/create_member_stub_form/0/' . $results['ID'][0]);
                $vars['add_founder_url']   = site_url('page/create_founder_stub_form');

                $vars['id'] = $id;
                $vars['delete_founder_url']  = site_url('page/deleteitem/founder');
                $vars['conf_delete_str'] = ('Удалить?');

                $susp_members_str = ("Участники операции");

                $this->aml_context->set_general_vars($vars);
                if(!$ignore_readonly and $readonly){
                	$vars['readonly'] = true;
                } else {
                	$vars['readonly'] = false;
                }
                $vars['grid'] = $this->aml_metainfo->get_js_table_properties('TB_SUSP_MEMBERS');
                $output .= $this->load->view('edit-operation-members-grid', $vars, true);
				$vars['masks'] = 1;
				
                if ($ignore_readonly) {
                    $output .= '<script type="text/javascript">jQuery(document).ready(function () { jQuery(\'input,select,textarea\').attr(\'disabled\', false) });</script>';
                } else if (!$ignore_readonly && $readonly) {
                    $output .= '<script type="text/javascript">jQuery(document).ready(function () { jQuery(\'input,select,textarea\').attr(\'disabled\', true) })</script>';
                }
                break;
            case 'job':
                if ($this->input->post('op')) {
					$params_idx = 1;
					$varslist = array_keys($_POST);
					$table_name = 'VW_JOB_RUNING';
					$job = $this->input->post('VW_JOB_RUNING_'.$params_idx.'_JOB')*1;
					$longname = trim($this->input->post('VW_JOB_RUNING_'.$params_idx.'_P_LONGNAME'));
					$what = trim($this->input->post('VW_JOB_RUNING_'.$params_idx.'_WHAT'));
					$interval = trim($this->input->post('VW_JOB_RUNING_'.$params_idx.'_INTERVAL'));
					$p_job_exists = 1;
					if(in_array($interval, array('','null','NULL'))){
						$p_job_exists = 0;
						$interval = 'NULL';
					}

					if(!$job){
						$job = 9999999999;
						$bindings = array(':job'=>&$job, ':what'=>$what, ':interval'=>$interval);
						$stid = $this->aml_oracle->execute("BEGIN DBMS_JOB.SUBMIT(:job, :what, SYSDATE, :interval); COMMIT; END;", __LINE__, $bindings);
						if($job and $stid){
							$bindings = array(':job'=>$job, ':what'=>$what, ':longname'=>$longname);
							$this->aml_oracle->execute("insert into tb_user_job (job, p_what, p_longname, p_job_exists)
								values (:job, :what, :longname, '".($p_job_exists)."')", __LINE__, $bindings);
						}
					} else {
						$bindings = array(':job'=>$job, ':what'=>$what, ':interval'=>$interval);
						$stid = $this->aml_oracle->execute("BEGIN DBMS_JOB.CHANGE(:job, :what, SYSDATE, :interval); COMMIT; END;", __LINE__, $bindings);
						if($stid){
							$bindings = array(':job'=>$job, ':what'=>$what, ':longname'=>$longname);
							$stid = $this->aml_oracle->execute("select job from tb_user_job where job = '".$job."'", __LINE__, null, false);
							$job_exists = oci_fetch_row($stid);
							if($job_exists[0] > 0){
								$this->aml_oracle->execute("update tb_user_job set p_what=:what, p_longname=:longname, p_job_exists='".$p_job_exists."' where job=:job", __LINE__, $bindings);
							} else {
								$this->aml_oracle->execute("insert into tb_user_job (job, p_what, p_longname, p_job_exists)
									values (:job, :what, :longname, '".($p_job_exists)."')", __LINE__, $bindings);
							}
						}
					}


                    $this->aml_oracle->commit();

                    print '<script type="text/javascript">window.close();</script>';
                    //header('Location: ' . site_url('page/managebranches'));
                    die();
                }
                $vars['hide_header'] = true;
                $output = '<fieldset class="viewdata">';
                $output .= '<legend>' . ('Задача') . '</legend>';
                $output .= $this->aml_html->create_form('VW_JOB_RUNING', $id, 0 , 'JOB');
                $output .= '</fieldset>';
            	break;
			case 'ipdl_job':
			$q= "TRUNCATE TABLE TB_IPDL_PARAMS";
				$stid = $this->aml_oracle->execute($q, OCI_DEFAULT);
			$stid =$this->aml_oracle->execute(
                                    "BEGIN " .
                                    "  INSERT INTO TB_IPDL_PARAMS(ID) VALUES(:new_id_sz100); " .
                                    "END;",__LINE__, array(':new_id_sz100' => '1'));
									
									
                if ($this->input->post('op')) {
                    $branch_ti    = $this->aml_metainfo->get_table_info('TB_IPDL_PARAMS',1);
                    $branch_update         = array();
                    $branch_update_values  = array();
                    $branch_update_pk      = array();
                    $branch_extra          = array();
                    $params_idx = 1;
                    $varslist = array_keys($_POST);

                    foreach($varslist as $postvar) {
                        if (substr($postvar,0,strlen("TB_IPDL_PARAMS")) == "TB_IPDL_PARAMS") {
                            $tmp = substr($postvar, strlen("TB_IPDL_PARAMS") + 1);
                            $member_number = intval(substr($tmp,0, strpos($tmp,'_')));
                            // правильно ли передали название поля?
                            if ($member_number <= 0) {
                                continue;
                            }
                            $field_name = substr($tmp,strpos($tmp,'_') + 1);

                            // Primary Key ?
							
                            if ($field_name == 'ID') {
                                $branch_update_pk[$member_number] = floatval($this->input->post($postvar));
                                continue;
                            }
							
							
                            if ($this->_is_system_field($field_name)) {
                                continue;
                            }

                            // проверить, можно ли это поле редактировать юзеру
                            $field_idx = $this->_get_field_index($branch_ti, $field_name);
							
                            if ($field_idx === null) {
                                continue;
                            }
                            if ($branch_ti['P_EDITABLE_BOOL'][$field_idx] != 1) {
                                continue;
                            }

                            // можно редактировать, пишем в базу
                            switch($branch_ti['DATA_TYPE'][$field_idx]) {
                                case 'DATE':
                                    if (preg_match($this->config->item('regexp_date'), $this->input->post($postvar))){
                                        $branch_update[$member_number][] = $field_name . ' = TO_DATE(:param' . $params_idx . ',\'' . $this->config->item('date_format') . '\')';
                                        $branch_update_values[$member_number]['param' . $params_idx] = $this->input->post($postvar);
                                    } else if (preg_match($this->config->item('regexp_date_long'), $this->input->post($postvar))) {
                                        $branch_update[$member_number][] = $field_name . ' = TO_DATE(:param' . $params_idx . ',\'' . $this->config->item('date_format_long') . '\')';
                                        $branch_update_values[$member_number]['param' . $params_idx] = $this->input->post($postvar);
                                    } else {
                                        // передали корявую дату ?!, игнорим
                                    }
                                    break;
                                case 'NUMBER':
                                    $branch_update[$member_number][] = $field_name . ' = :param' . $params_idx;
                                    $branch_update_values[$member_number]['param' . $params_idx] = $this->input->post($postvar) == "" ? null : floatval(str_replace(" ","",$this->input->post($postvar)));
                                    break;
                                default:
                                    $branch_update[$member_number][] = $field_name . ' = :param' . $params_idx;
                                    $branch_update_values[$member_number]['param' . $params_idx] = $this->input->post($postvar);
                            }
                            $params_idx++;
                        }
                    }

                    foreach($branch_update as $k => $v) {
                        // новая запись, нужен пустой insert?
                        $new_id = '1';
                        // if ($branch_update_pk[$k] == 0) {
                            // $stid =
                                // $this->aml_oracle->execute(
                                    // "BEGIN " .
                                     
                                    // "  INSERT INTO TB_IPDL_PARAMS(ID) VALUES(:new_id_sz100); " .
                                    // "END;",__LINE__, array(':new_id_sz100' => &$new_id)
                                // );
                            
                        // }
							$branch_update_pk[$k] = $new_id;
                        $bindings = array(':id' => $branch_update_pk[$k]);
                        foreach($branch_update_values[$k] as $param_name => $param_value) {
                            $bindings[':' . $param_name] = $branch_update_values[$k][$param_name];
                        }
						//die(var_dump($v));	
                        $update = 'UPDATE TB_IPDL_PARAMS SET ' . implode(',', $v) . ' WHERE ID = :id';
						//die(var_dump($bindings));	
                        $stid = $this->aml_oracle->execute($update, __LINE__, $bindings,true, OCI_DEFAULT);
                    }
                    $this->aml_oracle->commit();

                    print '<script type="text/javascript">window.close();</script>';
                    //header('Location: ' . site_url('page/managebranches'));
                    die();
                }
                $vars['hide_header'] = true;
                $output = '<fieldset class="viewdata">';
                $output .= '<legend>' . ('Филиал') . '</legend>';
                $output .= $this->_create_form('TB_IPDL_PARAMS', $id);
				 
                $output .= '</fieldset>';
				
				break;
				
            default:
                $this->aml_html->error_page(array(sprintf(('Неверное значение параметра %s'), 'id')));
                break;
        }

        $output = form_open('page/edit/' . $table_name . '/' . $id.'" onsubmit="return confirm(\''.('Сохранить изменения?').'\');', array('id' => 'edit-' . $table_name, 'class' => 'check-required-field-form')) . $output;
        $output .= '<fieldset class="viewdata" style="border:none;background:transparent">';
       // if ( $table_name =='ipdl_job') {
            // $output .= '<a href="javascript:job_ipdl_button(\'' . $ipdl_job_id . '\')" class="button-link float">'.('Сохрнаить настройки').'</a>';
			 
        // }
	   
		if (!$readonly /*&& $table_name !='ipdl_job'*/) {
            $output .= ' <input type="submit" style="clear:none" name="op" value="' . ('Сохранить') . '" class="aml-submit"><script language="javascript">var editable_client = true;</script>';
        }
        if ($show_rollback_button) {
            $output .= ' <input type="button" style="clear:none" name="op" value="' . ('Отменить изменения') . '" class="aml-submit" onclick="location.reload()">';

        }
        $output .= '</fieldset>';
        $output .= form_close();

        $vars['content'] = $output;
        @$vars['run_js'] .= ';var greetz_from_kerb = "hi there ;)";';  // чтобы в шаблон загрузился javascript
        $this->load->view('main', $vars);
    }
	/*function add_cor_acc () {
		$this->aml_auth->check_auth(); // checkauth
		$id = $this->input->post('id');
		if ($id <= 0) {
			print '[false, "' . sprintf(('Неверное значение параметра %s'), 'id') .'", 0]';
			die();
		}
		$can_edit = $this->aml_security->check_privilege(13); // 2 - EDIT
		if (!$can_edit) {
			print '[false, "' . ('Отсутствуют права для данного действия.') . '", 0]';
			die();
		}
		$rand_Id = rand(1,8000);
		$output .= '<fieldset class="oddviewdata" id="cor_'.$rand_Id.'"><legend>Кор. счет</legend>';
		$output .= $this->aml_html->create_form('TB_COR_ACCOUNTS',$rand_Id , $rand_Id + 1,'ID','');
		$output .= '    <input type="button" value="' . ('Удалить') . '" style="margin: 10px 0px 10px 18px;cursor:pointer;padding:3px;width:160px;" class="del_button aml-submit dl-table-submit" onclick="if (!confirm(\'Удалить?\')) { return; }; $(\'#cor_' . $rand_Id . '\').remove()">';
		$output .= '</fieldset>';
		print $output;
	}
	function delete_cor_acc () {
		$this->aml_auth->check_auth(); // checkauth
		$id = $this->input->post('id');
		if ($id <= 0) {
			print '[false, "' . sprintf(('Неверное значение параметра %s'), 'id') .'", 0]';
			die();
		}
		$can_edit = $this->aml_security->check_privilege(13); // 2 - EDIT
		if (!$can_edit) {
			print '[false, "' . ('Отсутствуют права для данного действия.') . '", 0]';
			die();
		}
		$q = "DELETE FROM TB_COR_ACCOUNTS WHERE ID = :id";
		$binds = array(':id' => $id);
		$stid = $this->aml_oracle->execute($q,__LINE__,$binds);
		print 'Кор. счет успешно удален!';
	}*/
	
	function _edit_client($id, $param1=null){
		$susp = intval($this->input->post('P_SUSPICIOUS_BOOL'));
		if($susp.'' != $_POST['P_SUSPICIOUS_BOOL'].''){
			echo "alert('Недопустимое значение')";
			return;
		}
		$stid = $this->aml_oracle->execute("update tb_suspiciousmembers set p_suspicious_bool='".$susp."' where id = '".$id."'", __LINE__, null, false);
		if (!$stid) {
			$err = $this->aml_oracle->get_last_error();
			echo "alert('".$err['message']."')";
		} else {
			echo $susp;
			oci_free_statement($stid);
		}
		return;
	}

    function editdata($what, $param1 = null) {
        $this->aml_auth->check_auth(); // checkauth

        // вх данные
        $id = intval($this->input->post('id'));
        if ($id <= 0) {
            print '[false, "' . sprintf(('Неверное значение параметра %s'), 'id') .'", 0]';
            die();
        }

        $can_edit = $this->aml_security->check_privilege(13); // 2 - EDIT
        if (!$can_edit) {
            print '[false, "' . ('Отсутствуют права для данного действия.') . '", 0]';
            die();
        }

        switch($what) {
            case 'online':
                $table_name = 'TB_ONLINEOPERATIONS';
                break;
            case 'monitoring':
            	return $this->_edit_client($id, $param1);
            	break;
            default:
                print '[false, "' . sprintf(('Неверное значение параметра %s'), 'table name') . '", 0]';
                die();
        }

        // проверим, что такая запись есть в таблице
        $cnt = 0;
        $stid = $this->aml_oracle->execute("SELECT COUNT(1) cnt FROM " . $table_name .  " t WHERE id = :id",__LINE__,
            array(':id' => $id,
                  'CNT' => &$cnt));
        oci_fetch($stid);
        oci_free_statement($stid);
        if ($cnt == 0) {
            $msg = str_replace('"', '\\"', sprintf(('Не найдена запись с parent_id: %d'), $id));
            print '[false, "'  .$msg . '", 0]';
            die();
        }

        // обновление записи
        $client     = $this->input->post('client');
        $clientdate = $this->input->post('clientdate');
        $amount     = floatval($this->input->post('amount'));
        $tax        = floatval($this->input->post('tax'));
        $note       = $this->input->post('note');

        $update_set = array();
        $update_values = array();

        $fields_info = $this->aml_metainfo->get_table_info($table_name);
        for($i = 0; $i < count($fields_info['COLUMN_NAME']); $i++) {
            if ($fields_info['P_EDITABLE_BOOL'][$i] != 1) {
                continue;
            }
            if (!isset($_POST[$fields_info['COLUMN_NAME'][$i]])) {
                continue;
            }
            switch ($fields_info['DATA_TYPE'][$i]) {
                case 'DATE':
                    $dtvalue = $this->input->post($fields_info['COLUMN_NAME'][$i]);
                    if (empty($dtvalue)) {
                        $update_set[] = $fields_info['COLUMN_NAME'][$i] . ' = NULL';
                        //$update_values[':param' . $i] = html_entity_decode($this->input->post($fields_info['COLUMN_NAME'][$i]));
                    } else if (preg_match($this->config->item('regexp_date'), $dtvalue)) {
                        $update_set[] = $fields_info['COLUMN_NAME'][$i] . ' = TO_DATE(:param'  . $i . ',\'' . $this->config->item('date_format') . '\')';
                        $update_values[':param' . $i] = html_entity_decode($this->input->post($fields_info['COLUMN_NAME'][$i]));
                    } else if  (preg_match($this->config->item('regexp_date_long'), $dtvalue)) {
                        $update_set[] = $fields_info['COLUMN_NAME'][$i] . ' = TO_DATE(:param'  . $i . ',\'' . $this->config->item('date_format_long') . '\')';
                        $update_values[':param' . $i] = html_entity_decode($this->input->post($fields_info['COLUMN_NAME'][$i]));
                    } else {
                        print sprintf(('[false, "Неверное значение даты для поля %s = %s. Допустимыми являются значения в формате %s или %s",0]'), $fields_info['P_FIELD_CAPTION'][$i], $dtvalue, $this->config->item('date_format'), $this->config->item('date_format_long'));
                        //print '[false, "Неверное значение даты для поля \'' . $fields_info['P_FIELD_CAPTION'][$i] . ' = ' . $dtvalue . '\' - допустимыми являются значения в формате ДД.ММ.ГГГГ либо ДД.ММ.ГГГГ ЧЧ.ММ.СС ", 0]';
                        die();
                    }
                    break;
                case 'VARCHAR2':
                    $update_set[] = $fields_info['COLUMN_NAME'][$i] . ' = :param' . $i;
                    $update_values[':param' . $i] = html_entity_decode($this->input->post($fields_info['COLUMN_NAME'][$i]));
                    break;
                case 'NUMBER':
                    if ($fields_info['P_EDIT_TYPE'][$i] == 'checkbox') {
                        $checkbox = $this->input->post($fields_info['COLUMN_NAME'][$i]);
                        // для чекбоксов допустимы только 0 и 1
                        if ($checkbox == 0 || $checkbox == 1) {
                            $update_set[] = $fields_info['COLUMN_NAME'][$i] . ' = :param' . $i;
                            $update_values[':param' . $i] = $checkbox;
                        }
                    } else {
                        $update_set[] = $fields_info['COLUMN_NAME'][$i] . ' = :param' . $i;
                        $update_values[':param' . $i] = $this->input->post($fields_info['COLUMN_NAME'][$i]);
                    }
                    break;
                default:
            }
        }

        if (count($update_set) > 0) {
            for($i = 0; $i < count($fields_info['COLUMN_NAME']); $i++) {
                if ($fields_info['COLUMN_NAME'][$i] == 'P_DATE_UPDATE'){
                    $update_set[] = 'P_DATE_UPDATE = SYSDATE';
                    break;
                }
            }

            $logusername = false;
            for($i = 0; $i < count($fields_info['COLUMN_NAME']); $i++) {
                if ($fields_info['COLUMN_NAME'][$i] == 'P_DATE_UPDATE'){
                    $update_set[] = 'P_USERNAME = :currentusername';
                    $logusername = true;
                    break;
                }
            }

            $sql = 'UPDATE ' . $table_name . ' t SET ' . implode(',', $update_set) . ' WHERE t.ID = :id';

            $bindings = array(':id' => $id);
            if ($logusername){
                $bindings[':currentusername'] = $this->aml_auth->get_username();
            }

            foreach($update_values as $k => $v ) {
                //oci_bind_by_name($stid, $k, $update_values[$k]);
                $bindings[$k] = $update_values[$k];
            }

            $stid = $this->aml_oracle->execute($sql, __LINE__, $bindings, false);
            if (!$stid) {
                $e = oci_error($stid);
                print '[false, "DB ERROR: ' . htmlentities($e['message'], ENT_QUOTES, $this->he_encoding) . '",0]';
                die();
            }
            oci_free_statement($stid);

        }
        print '[true, "", 1]';
    }

    // формирование данных для jqGrid
    function _output_xml($page, $total_pages, $cnt, $results, $fields_info) {
        $s = "<?xml version='1.0' encoding='utf-8'?>\n";
        $s .=  "<rows>\n";
        $s .= "\t<page>".$page."</page>\n";                 // текущая страница
        $s .= "\t<total>".$total_pages."</total>\n";        // всего страниц
        $s .= "\t<records>". $cnt ."</records>";            // всего записей
        $s .= "\n";
        // цикл по записям
        // $fields_info['COLUMN_NAME'][0] - имя первого поля

        for ($i = 0; $i < count($results[$fields_info['COLUMN_NAME'][0]]); $i++) {
            $s .= "\t";
            $s .= "<row id='" . $results['ID'][$i] . "'>\n";
            for($j = 0; $j < count($fields_info['COLUMN_NAME']); $j++) {
                // проверим, не нужно ли скрывать колонку
                if ($fields_info['P_VISIBILITY_GRID_BOOL'][$j] != 1) {
                    //continue;
                }

                $value = $results[$fields_info['COLUMN_NAME'][$j]][$i];

                if ($fields_info['P_EDIT_TYPE'][$j] == 'checkbox') {
                    // checkbox
                } else if ($fields_info['DATA_TYPE'][$j] == 'VARCHAR2') {
                    $value = '<![CDATA[' . $value . ']]>';
                }

                $s .= "\t\t<cell>" . $value . "</cell>\n";
            }
            $s .= "\t</row>\n";
        }
        $s .= "</rows>";
        print $s;
    }

    function savecolumnproperty($grid, $column, $property, $value) {
        $this->aml_auth->check_auth();
        if (!is_numeric($column)) {
            return;
        }
        switch($grid) {
            case 'suspicious':
            case 'online':
            case 'offline':
                break;
            default:
                return;
        }
        switch($property) {
            case 'width':
                if (!is_numeric($value)) {
                    return;
                }
                break;
            case 'sortname':
                if (!preg_match('#^[A-Z0-9_]+$#', $value)) {
                    return;
                }
                break;
            case 'sortorder':
                if (!$value != 'desc' && $value != 'asc') {
                    return;
                }
                break;
            default:
                return;

        }
        $this->native_session->set_userdata('ui.jqgrid. ' . $grid . ' .column_' . $column . '_' . $property, $value);
        print 'column ' . $grid . '.' . $column . ' property ' . $property . ' = ' . $value . ' saved.';
    }

    // сохранение пользовательских фильтров в сессию
    function savesettings($grid = ''){
        $this->aml_context->savesettings($grid);
    }

    function changepwd() {
        $this->aml_auth->check_auth(true); // checkauth, true = ignore_redirect
        $tdata = array();
        $vars['page_name'] = ('Смена пароля');

        if ($this->input->post('op')) {
            $pwd    = $this->input->post('pwd');
            $pwd2   = $this->input->post('pwd2');
            $oldpwd = $this->input->post('oldpwd');
			 $userid = $this->aml_auth->get_uid();
            $ok = true;
            if ($pwd !== $pwd2) {
                $this->native_session->set_flashdata('emsg', array(('Пароль и подтверждение не совпадают')));
                $ok = false;
            } else {
                // не менее 8 символов
                if (mb_strlen($pwd,'utf-8') < 8) {
                    $this->native_session->set_flashdata('emsg', array(sprintf(('Пароль должен быть не менее %d символов'),8)));
                    $ok = false;
                }
				/*
				**
				**
				*///test for PKG_USER_PASS
				//die(var_dump($uid));
				 $pr ="begin
					pkg_user_pass.check_password(in_user_id => :in_user_id,
							   iv_password => :iv_password,
							   ov_result => :ov_result,
							   ov_result_text => :ov_result_text);
							   end;";
					 
					$stid_pr = $this->aml_oracle->execute_size($pr, __LINE__,array(':in_user_id' => intval($userid), ':iv_password' => $pwd,           ':ov_result' => &$ov_result, ':ov_result_text' => &$ov_result_text));
					
					$vars['ov_result'] =  $ov_result;
					$vars['ov_result_text'] =  $ov_result_text;
					//die(var_dump($ov_result_text));
				
				 if ($ov_result != 0 ) {
                    $this->native_session->set_flashdata('emsg', array(sprintf(($ov_result_text))));
                    $ok = false;
                }
               
	
                // проверка старого пароля
                if ($ok) {
                    if ($this->config->item('aes_enable') == 1) {
                        $q = 'SELECT * FROM TB_USERS t WHERE t.id = :id';
                        $stid  = $this->aml_oracle->execute($q,__LINE__, array(':id' => $userid));
                        $nrows = oci_fetch_all($stid, $results);
                        if ($nrows == 1){
                            $decrypted_pass = $this->aml_aes->decrypt_str(pack("H*", $results['P_PASSWORD'][0]));
                            if ($decrypted_pass != $oldpwd) {
                                $this->native_session->set_flashdata('emsg', array(('Старый пароль неверен')));
                                $ok = false;
                            }
                        } else {
                            $this->native_session->set_flashdata('emsg', array(('Старый пароль неверен')));
                            $ok = false;
                        }
                    } else {
                        $q = 'SELECT count(*) cnt FROM TB_USERS t WHERE t.id = :id AND t.p_password = md5(:pwd)';
                        $stid  = $this->aml_oracle->execute($q,__LINE__, array(':id' => $userid, ':pwd' => $oldpwd));
                        list($cnt) = oci_fetch_array($stid);
                        if ($cnt != 1) {
                            $this->native_session->set_flashdata('emsg', array(('Старый пароль неверен')));
                            $ok = false;
                        }
                    }
                }

                if ($ok) {
                    if ($pwd == $oldpwd) {
                        $this->native_session->set_flashdata('emsg', array(('Ваш новый пароль совпадает с текущим паролем')));
                        $ok = false;
                    }
                }

                // проверка по истории
                if ($ok) {
                    $q = 'SELECT COUNT(*) cnt FROM TB_PWD_HISTORY t WHERE t.p_user_id = :id AND md5(:pwd) = t.p_password';
                    $stid  = $this->aml_oracle->execute($q,__LINE__, array(':id' => $userid,':pwd' => $pwd));
                    list($cnt) = oci_fetch_array($stid);
                    if ($cnt > 0) {
                        $this->native_session->set_flashdata('emsg', array(('Ваш пароль совпадает с одним из 12 последних введеных паролей')));
                        $ok = false;
                    }
                }

                if ($ok) {
                    if (!preg_match('#[a-zа-яё]+#', $pwd)) {
		                // проверка в нижнем регистре
                        $this->native_session->set_flashdata('emsg', array(sprintf(('Пароль должен содержать хотя бы один символ в нижнем регистре'))));
                        $ok = false;
                    } elseif (!preg_match('#[A-ZА-ЯЁ]+#', $pwd)) {
		                // проверка в верхнем регистре
                        $this->native_session->set_flashdata('emsg', array(sprintf(('Пароль должен содержать хотя бы один символ в верхнем регистре'))));
                        $ok = false;
                    }/*Добавил Адилет по заявке 9942 27.07.2018*/
					elseif(!preg_match('/[0-9]/', $pwd))
					{
						$this->native_session->set_flashdata('emsg', array(sprintf(('Пароль должен содержать хотя бы одну цифру'))));
						 $ok = false;
					}
					/*************************************************************************************/ 
					else {
		                // проверка спецсимволов
	                    $special_chars = "~`!@#$%^&*()_-+=\\][{}:;\"'?/>.<,";
	                    $sca = str_split($special_chars);
	                    for($i = 0; $i < count($sca); $i++) {
	                        $sca[$i] = preg_quote($sca[$i], "/");
	                    }
	                    $regexp = implode("|", $sca);
	                    if (!preg_match("/(" . $regexp . "|\\d)+/", $pwd)) {
	                        $this->native_session->set_flashdata('emsg', array(sprintf(('Пароль должен содержать хотя бы один спецсимвол (%s) или цифру'),$special_chars)));
	                        $ok = false;
	                    }

	                }
                }

                // проверка 24 часа
                if ($ok) {
                    $q = 'SELECT COUNT(*) cnt FROM TB_USERS t WHERE t.id = :id AND t.p_pwd_changedate > SYSDATE - 1.0';
                    $stid = $this->aml_oracle->execute($q,__LINE__, array(':id' => $userid));
                    list($cnt) = oci_fetch_array($stid);
                    if ($cnt > 0) {
                        $this->native_session->set_flashdata('emsg', array(('Пароль можно менять только 1 раз в 24 часа')));
                        $ok = false;
                    }
                }

                // смена пароля
                if ($ok) {
                    if ($this->config->item('aes_enable') == 1) {
                        $aes_pass = unpack("H*",$this->aml_aes->crypt_str($pwd));
                        $q = 'UPDATE TB_USERS t SET t.P_PASSWORD = :pwd, t.p_pwd_changedate = sysdate, p_require_pwd_reset_bool = 0 WHERE t.id = :id';
                        $stid = $this->aml_oracle->execute($q,__LINE__, array(':pwd' => $aes_pass[1], ':id' => $userid),true,OCI_DEFAULT);
                    } else {
                        $q = 'UPDATE TB_USERS t SET t.P_PASSWORD = md5(:pwd), t.p_pwd_changedate = sysdate, p_require_pwd_reset_bool = 0 WHERE t.id = :id';
                        $stid = $this->aml_oracle->execute($q,__LINE__, array(':pwd' => $pwd, ':id' => $userid),true,OCI_DEFAULT);
                    }

                    $q = 'SELECT * FROM TB_PWD_HISTORY t WHERE t.p_user_id = :id ORDER BY createdate';
                    $stid = $this->aml_oracle->execute($q,__LINE__, array(':id' => $userid), true, OCI_DEFAULT);

                    $rows = array();
                    while($r = oci_fetch_array($stid)) {
                        $rows[] = $r;
                    }
                    if (count($rows) == 12) {
                        $q = 'DELETE FROM TB_PWD_HISTORY t WHERE t.id = :id';
                        $stid = $this->aml_oracle->execute($q, __LINE__,array(':id' => $rows[0]['ID']),true, OCI_DEFAULT);
                    }
                    $q = 'INSERT INTO TB_PWD_HISTORY(id,p_user_id,p_password) VALUES(GetID(),:user_id,md5(:pass))';
                    $stid = $this->aml_oracle->execute($q, __LINE__, array(':user_id' => $userid, ':pass' => $pwd), true, OCI_DEFAULT);
                    oci_commit($this->aml_oracle->oracle_connection);

                    $this->native_session->set_userdata('require_pwd_reset', 0);
                    $this->native_session->set_userdata('password_expired',  0);
                }
            }

            if (!$ok) {
                header('Location: ' . site_url('page/changepwd'));
                return;
            } else {
                $base_url = site_url();
                $password_changed = ("Пароль изменен!");
                $vars['run_js'] = <<< ENDL
            \$(document).ready(function () {
                alert("{$password_changed}");
                location.href = '{$base_url}';
            });
ENDL;
            }
        }

        $this->aml_context->set_general_vars($tdata);
        $vars['content'] = $this->load->view('changepwd', $tdata, true);
        $this->load->view('main', $vars);
    }

    function deleteitem($what, $id, $p1 = null, $p2 = null) {
        $this->aml_auth->check_auth();


        $id  = floatval($id);
        if ($id <= 0) {
            return false;
        }
        switch($what) {
            case 'member':
                $can_edit = $this->aml_security->check_privilege(12) ||  $this->aml_security->check_privilege(56);
                if (!$can_edit) {
                    $this->aml_html->error_page(array(('Отсутствуют права для данного действия.')));
                }

                $by_credit_bool = ($p1 == 'credit' ? 1 : 0);
                $operation_id = floatval($p2);
                $q = "BEGIN delete_susp_member(:param1, :param2, :param3); COMMIT; END;";
                $stid = $this->aml_oracle->execute($q, __LINE__, array(':param1' => $id,':param2' => $by_credit_bool, ':param3' => $operation_id));
                break;
            case 'founder':
                $q = "DELETE FROM TB_SUSPICIOUSFOUNDERS t WHERE t.ID = '".$id."'";
                $stid = $this->aml_oracle->execute($q, __LINE__);
                $this->aml_oracle->commit();
/*
                $memberid = -9999999999;
                $q = "BEGIN pr_delete_founder(:f_id, :m_id); COMMIT; END;";
                $stid = $this->aml_oracle->execute($q, __LINE__, array(':f_id' => $id, ':m_id' => &$memberid));
                print $memberid;
*/
                die();
                break;
            case "branch":
                $q = "DELETE FROM TB_BRANCH t WHERE t.ID = :id1";
                $stid = $this->aml_oracle->execute($q, __LINE__, array(':id1' => $id));
                break;
            default:
                return false;
        }
    }

    function datasource($what = null, $param2 = null, $param3 = null) {
        $this->aml_auth->check_auth(); // checkauth

        // вх. параметры
        $page = intval($this->input->post('page'));
        if ($page <= 0) {
            $page = 1;
        }
        $rows = intval($this->input->post('rows'));
        if ($rows <= 0) {
            $rows = $this->per_page;
        }

        // рассчитанные переменные
        $start  = ($page - 1) * $rows + 1;
        $end    = $page * $rows + 1;
        $data   = array();
        $w1     = '';
        $w2     = '';
        $wvalue = '';

        switch ($what) {
            case 'edit_off_operation_members':
                $id = floatval($param2);
                $can_do = $this->aml_security->check_privilege(14) || $can_do = $this->aml_security->check_privilege(77) || $can_do = $this->aml_security->check_privilege(91); // VIEW
                if (!$can_do) {
                    $this->aml_html->error_page(array(('Отсутствуют права для данного действия.')));
                }

                $fields_info = $this->aml_metainfo->get_table_info('TB_OFF_MEMBERS');
                $order_by    = $this->aml_grid->get_sorting_clause($fields_info);

                $q = "SELECT COUNT(*) cnt FROM TB_OFF_MEMBERS t WHERE t.P_OPERATIONID = :op_id";
                $cnt = 0;
                $stid = $this->aml_oracle->execute($q, __LINE__, array(':op_id' => $id, 'CNT' => &$cnt));
                oci_execute($stid);
                oci_fetch($stid);

                list($select_fields, $joins) = $this->aml_metainfo->get_joins($fields_info);

                $bindings = array(':v1' => $start, ':v2' => $end, ':op_id' => $id);
                $q = "SELECT * FROM ( SELECT rownum as rn, f.* FROM (SELECT ".implode(',',$select_fields)." FROM TB_OFF_MEMBERS t ".implode(' ',$joins)." WHERE t.P_OPERATIONID = :op_id ". $order_by.") f ) WHERE rn >= :v1 and rn < :v2 ";
                $stid = $this->aml_oracle->execute($q, __LINE__, $bindings);
                $nrows = oci_fetch_all($stid, $results);
                oci_free_statement($stid);

                $total_pages = ceil($cnt / $rows);
                header("Content-type: text/xml;charset=utf-8");
                print $this->aml_grid->output_xml($page, $total_pages, $cnt, $results, $fields_info);
                break;
            case 'edit_operation_members':
                $can_do = $this->aml_security->check_privilege(12) || $this->aml_security->check_privilege(11) ||
                          $this->aml_security->check_privilege(15) || $this->aml_security->check_privilege(19) || 
						  $this->aml_security->check_privilege(77) || $this->aml_security->check_privilege(56) || 
						  $can_do = $this->aml_security->check_privilege(91);
                if (!$can_do) {
                    $this->aml_html->error_page(array(('Отсутствуют права для данного действия.')));
                }
                // check for del operation
                if ($this->input->post('oper') == 'del') {
                    $id = floatval($this->input->post('id'));
                    $q = 'DELETE FROM TB_SUSP_MEMBERS t WHERE t.id = :id';
                    $bindings = array(
                        ':id' => $id
                    );
                    $this->aml_oracle->execute($q, __LINE__, $bindings);
                } else {
                    $id = floatval($param2);

                    $fields_info = $this->aml_metainfo->get_table_info('TB_SUSP_MEMBERS');
                    $order_by    = $this->aml_grid->get_sorting_clause($fields_info);

                    $q = "SELECT COUNT(*) cnt FROM TB_SUSP_MEMBERS t WHERE t.P_SUSPICIOUSOPERATIONID = :op_id";
                    $cnt = 0;
                    $stid = $this->aml_oracle->execute($q, __LINE__, array(':op_id' => $id, 'CNT' => &$cnt));
                    oci_execute($stid);
                    oci_fetch($stid);

					list($select_fields, $joins) = $this->aml_metainfo->get_joins($fields_info);

                    $bindings = array(':v1' => $start, ':v2' => $end, ':op_id' => $id);
                    $q = "SELECT * FROM ( SELECT rownum as rn, f.* FROM (SELECT ".implode(',',$select_fields)." FROM TB_SUSP_MEMBERS t ".implode(' ',$joins)." WHERE t.P_SUSPICIOUSOPERATIONID = :op_id ".$order_by.") f ) WHERE rn >= :v1 and rn < :v2 ";
                    $stid = $this->aml_oracle->execute($q, __LINE__, $bindings);
                    $nrows = oci_fetch_all($stid, $results);
                    oci_free_statement($stid);

                    $total_pages = ceil($cnt / $rows);
                    header("Content-type: text/xml;charset=utf-8");
                    print $this->aml_grid->output_xml($page, $total_pages, $cnt, $results, $fields_info);
                }
                break;
  case 'ajax-directory':
                $search    = $this->input->post('search');
				


                $q_dict = "SELECT COUNT(*) cnt
                      FROM  user_objects  t
                      WHERE t.OBJECT_NAME LIKE '%DICT_%'
                      AND t.OBJECT_NAME = :directory";
                $cnt = 0;

				$bind = array(':directory' => $param2, 'CNT' => &$cnt);
				//$bind = array(':directory' => $param2);
                $stid = $this->aml_oracle->execute($q_dict, __LINE__, $bind);
                oci_execute($stid);
				
                oci_fetch($stid);

				
				

                if ($cnt > 0) {
				// Проверяем, на существование полей если true то вызываем справочник КАТО
					if ( $param3 == 'TB_SUSPICIOUSMEMBERS_1_REG_AREA_KATO_ID' || $param3 == 'TB_SUSPICIOUSMEMBERS_1_SEAT_AREA_KATO_ID' || $param3 == 'TB_SUSPICIOUSMEMBERS_1_P_MEMBERACBIRTHPLACE') {
						$where .= " WHERE UNIT_LEVEL = 0";
						$q  = "SELECT * FROM DICT_KATO t ".$where." AND UPPER('[ ' || ID || ' ] ' || t.NAME_RUS) LIKE UPPER(:search) OR ID LIKE :search ORDER BY NAME_RUS";
						
						$q2 = "SELECT * FROM DICT_KATO t ".$where." ORDER BY NAME_RUS";
					}
					elseif (  $param3 == 'TB_SUSPICIOUSMEMBERS_1_REG_REGION_KATO_ID' || $param3 == 'TB_SUSPICIOUSMEMBERS_1_SEAT_REGION_KATO_ID') {
						if ( $_POST['parent_id'] != '') {
							$and = " AND PARENT_ID = ".$_POST['parent_id'];
						}
						else {
							$and = "";
						}
						$where .= " WHERE UNIT_LEVEL = 1".$and;
						$q  = "SELECT * FROM DICT_KATO t ".$where." AND UPPER('[ ' || ID || ' ] ' || t.NAME_RUS) LIKE UPPER(:search) OR ID LIKE :search ORDER BY NAME_RUS";
						$q2 = "SELECT * FROM DICT_KATO t ".$where." ORDER BY NAME_RUS";
						 
					 
					}
					elseif($param3 == 'TB_SUSPICIOUSMEMBERS_1_REG_KATO_ID' || $param3 == 'TB_SUSPICIOUSMEMBERS_1_SEAT_KATO_ID') {					 
						$q  = "select name_rus, id from ( select sys_connect_by_path(name_rus, ' -> ') NAME_RUS, id
								from dict_kato k
								start with parent_id = ".$_POST['parent_id']."
								connect by prior id = parent_id
								order siblings by k.NAME_RUS ) where (UPPER('[ ' || ID || ' ] ' || NAME_RUS) LIKE UPPER(:search) OR ID LIKE :search)";
						 
						$q2 = "select name_rus, id from ( select sys_connect_by_path(name_rus, ' -> ') NAME_RUS, id
								from dict_kato k
								start with parent_id = ".$_POST['parent_id']."
								connect by prior id = parent_id
								order siblings by k.NAME_RUS )";}
					else {
					// В противном случае используем обычный запрос
						$q = "SELECT P_ID, P_CODE, P_LONGNAME FROM " . $param2 . " t WHERE UPPER('[ ' || P_CODE || ' ] ' || t.P_LONGNAME) LIKE UPPER(:search) OR P_CODE LIKE :search ORDER BY P_CODE";
						//fix #15351 15.02.2021 t.toksabayeva 
					}  
                    $stid = $this->aml_oracle->execute($q, __LINE__, array(':search' => '%' . trim($search) . '%'));
                    $found = false;
                    $output = '<ul>';
				//	print"<pre>";die(var_dump($stid,$q,trim($search)));
                   // $output .= '<li rel="">&nbsp;</li>';
                    $output .= '<li rel="">&nbsp;</li>';
					if ($param2 == 'DICT_KATO') {
						if ($search != "") {
							while($r = oci_fetch_array($stid, OCI_ASSOC)) {
								$found = true;
								$output .= '<li rel="' . $r['ID'] . '">' .  str_replace($search,'<strong>' . $search . '</strong>', '[ ' . $r['ID'] . ' ] ' . htmlspecialchars($r['NAME_RUS'],ENT_QUOTES, 'utf-8')) . '</li>';
							}
						}
						else {
							$stid2 = $this->aml_oracle->execute($q2, __LINE__);
							while($r = oci_fetch_array($stid2, OCI_ASSOC)) {
								$found = true;
								$output .= '<li rel="' . $r['ID'] . '"> [' .  $r['ID'] . ' ] ' . $r['NAME_RUS'] . '</li>';
							}
						}
					}
					else {
						while($r = oci_fetch_array($stid, OCI_ASSOC)) {
							$found = true;
							$output .= '<li rel="' . $r['P_ID'] . '">' .  str_replace($search,'<strong>' . $search . '</strong>', '[ ' . $r['P_CODE'] . ' ] ' . htmlspecialchars($r['P_LONGNAME'],ENT_QUOTES, 'utf-8')) . '</li>';
						}
					}
                    if (!$found) {
                        $output .= '<li>' . ('не найдено') . '</li>';
                    }
                    $output .= '</ul>';
                } else {
				
				$output .= '<li>' . ('не найдено') . '</li>';
                }

                print $output;
                die();

                break;
            case 'kfm_log':
                $fields_info = array(
                    'COLUMN_NAME'             => array('ID','STATUS','P_MESS_NUMBER','P_BANKOPERATIONID', 'P_OPERATIONDATETIME','P_SENDDATE', 'P_SUSPICIOUSTYPECODE', 'P_CURRENCYCODE', 'P_BASEAMOUNT', 'P_REFERENS'),
                    'DATA_TYPE'               => array('NUMBER','VARCHAR2', 'VARCHAR2','DATE', 'DATE', 'NUMBER', 'NUMBER', 'NUMBER', 'NUMBER', 'VARCHAR2'),
                    'P_VISIBILITY_GRID_BOOL'  => array(0, 1, 1, 1, 1, 1, 1, 1, 1, 1)
                );
                $order_by    = $this->aml_grid->get_sorting_clause($fields_info);
                $where       = $this->aml_grid->get_where_clause($fields_info);

                $extra_where = array();
                $uisettings = $this->native_session->userdata('ui.kfm_log');

                switch ($uisettings['status']) {
                    case 0: // все
                        break;
                    case 1: // успешно
                        $extra_where[] = ' t.ORDER_FIELD = 1 ';
                        break;
                    case 2: // нет ответа
                        $extra_where[] = ' t.ORDER_FIELD = 2 ';
                        break;
                    case 3: // ошибки
                        $extra_where[] = ' t.ORDER_FIELD = 3 ';
                        break;
                    case 4: // отбраковано
                        $extra_where[] = ' t.ORDER_FIELD = 4 ';
                        break;
                    case 5: // ошибки
                        $extra_where[] = ' t.ORDER_FIELD = 5 ';
                        break;
                    case 6: // ошибки
                        $extra_where[] = ' t.ORDER_FIELD = 6 ';
                        break;
                    case 7: // ошибки
                        $extra_where[] = ' t.ORDER_FIELD = 7 ';
                        break;
                    case 8: // ошибки
                        $extra_where[] = ' t.ORDER_FIELD = 8 ';
                        break;
					case 11: // SDFO
                        $extra_where[] = ' t.ORDER_FIELD = 11 ';
                        break;
					case 12: // SDFO
                        $extra_where[] = ' t.ORDER_FIELD = 12 ';
                        break;		
                }

                if (!empty($uisettings['date_from'])) {
                    $extra_where[] = "t.P_SENDDATE >= TO_DATE('" . $uisettings['date_from'] . "','" . $this->config->item('date_format') .  "') ";
                }
                if (!empty($uisettings['date_until'])) {
                    $extra_where[] = "t.P_SENDDATE < TO_DATE('" . $uisettings['date_until'] . "','" . $this->config->item('date_format')  . "') + 1";
                }

                $where_str = "(t.P_OPERATIONSTATUS = -1";
                if ($this->aml_security->check_privilege(21)){
                    $where_str .= " or t.P_OPERATIONSTATUS >= 2"; // подозрит
                }
                if ($this->aml_security->check_privilege(20)) {
                    $where_str .= " or t.P_OPERATIONSTATUS = 1"; // подл. фм
                }
				if ($this->aml_security->check_privilege(91)){
					$where_str = "(t.P_OPERATIONSTATUS != -1 and t.P_OPERATIONSTATUS >= 1";
				}
                $where_str .= ")";
                $extra_where[] = $where_str;

                if ($where != null) {
                    $w1 = 'WHERE ' . $where[0] . ' AND ' . implode (" AND ",$extra_where);;
                    $w2 = 'AND ' . $where[0] . ' AND ' . implode (" AND ",$extra_where);;
                    $wvalue = $where[1];
                } else {
                    if (count($extra_where)) {
                        $w1 = 'WHERE ' . implode (" AND ",$extra_where);
                        $w2 = 'AND ' . implode (" AND ",$extra_where);
                    }
                }

                // кол-во записей в табле
                $cnt = 0;
                $bindings = array('CNT' => &$cnt);

                if ($wvalue != '') {
                    $bindings[':s'] = $wvalue;
                }

                $q = "SELECT COUNT(*) CNT FROM  VW_KFM_JOURNAL t $w1";
				//die(var_dump($q));
                $stid = $this->aml_oracle->execute($q, __LINE__, $bindings);

                oci_fetch($stid);
                oci_free_statement($stid);
                $total_pages = ceil($cnt / $rows);
				//die(var_dump($cnt));

                $bindings = array(':v1' => $start, ':v2' => $end);
                if ($wvalue != '') {
                    $bindings[':s'] = $wvalue;
                }
                // выборка страницы
                $stid = $this->aml_oracle->execute("SELECT *  FROM (
                                                       SELECT rownum rn, vt.* FROM
                                                         (SELECT /*t.ID,
                                                                 t.STATUS,
                                                                 t.STATUS_CODE,
                                                                 t.P_BANKOPERATIONID,
                                                                 t.P_OPERATIONDATETIME,
                                                                 t.P_SENDDATE,
                                                                 t.P_SUSPICIOUSTYPECODE,
                                                                 t.P_CURRENCYCODE,
                                                                 t.P_BASEAMOUNT,
																 t.P_MESS_NUMBER*/ *
                                                          FROM   VW_KFM_JOURNAL t
                                                          $w1
                                                          $order_by) vt ) WHERE rn >= :v1 and rn < :v2 ", __LINE__, $bindings);

                $nrows = oci_fetch_all($stid, $results);
                oci_free_statement($stid);

                header("Content-type: text/xml;charset=utf-8");
                print $this->aml_grid->output_xml($page, $total_pages, $cnt, $results, $fields_info);
                break;
            case 'clients_off_operations':
		        $fields_info   = $this->aml_metainfo->get_table_info('TB_OFFLINEOPERATIONS', 0, 0);
		        $order_by      = $this->aml_grid->get_sorting_clause($fields_info);
				$where         = $this->aml_grid->get_where_clause_multi('TB_OFFLINEOPERATIONS');
		        $uisettings    = $this->native_session->userdata('ui.client_off_operations');

				list($select_fields, $joins) = $this->aml_metainfo->get_joins($fields_info);

                $client_id   = intval($param2);

                $extra_where = array(' 1 = 1 ');
                if (count($this->aml_auth->get_branches()) > 0) {
                    $extra_where[] = ' t.P_ISSUEDBID IN (' . implode (",", $this->aml_auth->get_branches()) . ') ';
                }
		        if (!empty($uisettings['date_from'])) {
		            $extra_where[] = "t.P_OPERATIONDATETIME >= TO_DATE('" . $uisettings['date_from'] . "','" . $this->config->item('date_format') . "') ";
		        }
		        if (!empty($uisettings['date_until'])) {
		            $extra_where[] = "t.P_OPERATIONDATETIME < TO_DATE('" . $uisettings['date_until'] . "','" . $this->config->item('date_format') . "') + 1";
		        }

                if ($where != null) {
                    $w1 = 'WHERE ' . $where[0] . ' AND ' . implode (" AND ",$extra_where);
                    $w2 = 'AND ' . $where[0] . ' AND ' . implode (" AND ",$extra_where);
                    $wvalue = $where[1];
                } else {
                    if (count($extra_where)) {
                        $w1 = 'WHERE ' . implode (" AND ",$extra_where);
                        $w2 = 'AND ' . implode (" AND ",$extra_where);
                    }
                }

                // найдем p_client_id
                $p_clientid = 0;
//                $q = "SELECT NVL(MAX(P_CLIENTID),0) P_CLIENTID FROM " . $this->db_schema_prefix . "TB_SUSPICIOUSMEMBERS t WHERE t.id = :client_id";
                $q = "SELECT NVL(MAX(P_BSCLIENTID),0) P_BSCLIENTID FROM " . $this->db_schema_prefix . "TB_SUSPICIOUSMEMBERS t WHERE t.id = :client_id";
                $stid = $this->aml_oracle->execute($q, __LINE__, array('P_BSCLIENTID' => &$p_clientid, ':client_id' => $client_id));
                oci_fetch($stid);
                oci_free_statement($stid);

                $bindings = array('CNT' => &$cnt, ':p_clientid' => $p_clientid);
                if ($wvalue != '') {
		            foreach($wvalue as $k => $w) {
		                $bindings[$k] = $w;
		            }
                }

                // кол-во записей в табле
                $cnt = 0;

                $q = "Select count(1) cnt From tb_offlineoperations t, tb_off_members tom
						where tom.p_operationid = t.id
						and tom.p_bsclientid = :p_clientid ".$w2;

                $stid = $this->aml_oracle->execute($q, __LINE__, $bindings);

                oci_fetch($stid);
                oci_free_statement($stid);
                $total_pages = ceil($cnt / $rows);

                //
                $unprocessed_str = str_replace("'", "''", ("Необработанные"));
                $processed_str = str_replace("'", "''", ("Обработанные"));
                $archived_str = str_replace("'", "''", ("Архивные"));

                $bindings = array(':v1' => $start, ':v2' => $end, ':p_clientid' => $p_clientid);
                if ($wvalue != '') {
		            foreach($wvalue as $k => $w) {
		                $bindings[$k] = $w;
		            }
                }
                // выборка страницы
                $stid = $this->aml_oracle->execute("SELECT *  FROM (
                                               SELECT rownum rn, vt.* FROM
                                                 (SELECT ".implode(',',$select_fields)."
                                                  FROM TB_OFFLINEOPERATIONS t
 												  LEFT JOIN TB_OFF_MEMBERS m1 ON m1.P_OPERATIONID = t.id
												  ".implode(' ',$joins)."
                                                  WHERE  (m1.P_BSCLIENTID = :p_clientid)
                                                  $w2
                                                  $order_by) vt ) WHERE rn >= :v1 and rn < :v2 ", __LINE__, $bindings);
                $nrows = oci_fetch_all($stid, $results);
                oci_free_statement($stid);

                header("Content-type: text/xml;charset=utf-8");
                print $this->aml_grid->output_xml($page, $total_pages, $cnt, $results, $fields_info, array(), true);
                die();
                break;
            case 'clients_operations':
		        $fields_info   = $this->aml_metainfo->get_table_info('TB_SUSPICIOUSOPERATIONS', 0, 0);
		        $order_by      = $this->aml_grid->get_sorting_clause($fields_info);
				$where         = $this->aml_grid->get_where_clause_multi('TB_SUSPICIOUSOPERATIONS');
		        $uisettings    = $this->native_session->userdata('ui.client_susp_operations');

				list($select_fields, $joins) = $this->aml_metainfo->get_joins($fields_info);

                $client_id = intval($param2);

                $extra_where = array(' 1 = 1 ');
                if (count($this->aml_auth->get_branches()) > 0) {
                    $extra_where[] = ' t.P_ISSUEDBID IN (' . implode (",", $this->aml_auth->get_branches()) . ') ';
                }
		        if (!empty($uisettings['date_from'])) {
		            $extra_where[] = "t.P_OPERATIONDATETIME >= TO_DATE('" . $uisettings['date_from'] . "','" . $this->config->item('date_format') . "') ";
		        }
		        if (!empty($uisettings['date_until'])) {
		            $extra_where[] = "t.P_OPERATIONDATETIME < TO_DATE('" . $uisettings['date_until'] . "','" . $this->config->item('date_format') . "') + 1";
		        }

                if ($where != null) {
                    $w1 = 'WHERE ' . $where[0] . ' AND ' . implode (" AND ",$extra_where);
                    $w2 = 'AND ' . $where[0] . ' AND ' . implode (" AND ",$extra_where);
                    $wvalue = $where[1];
                } else {
                    if (count($extra_where)) {
                        $w1 = 'WHERE ' . implode (" AND ",$extra_where);
                        $w2 = 'AND ' . implode (" AND ",$extra_where);
                    }
                }

                // найдем p_client_id
                $p_clientid = 0;
                $q = "SELECT NVL(MAX(P_BSCLIENTID),0) P_BSCLIENTID FROM " . $this->db_schema_prefix . "TB_SUSPICIOUSMEMBERS t WHERE t.id = :client_id";
                $stid = $this->aml_oracle->execute($q, __LINE__, array('P_BSCLIENTID' => &$p_clientid, ':client_id' => $client_id));
                oci_fetch($stid);
                oci_free_statement($stid);

                $bindings = array('CNT' => &$cnt, ':p_clientid' => $p_clientid);
                if ($wvalue != '') {
		            foreach($wvalue as $k => $w) {
		                $bindings[$k] = $w;
		            }
                }

                // кол-во записей в табле
                $cnt = 0;
                $q = "Select count(1) cnt From tb_suspiciousoperations t, tb_susp_members tom
						where tom.p_suspiciousoperationid = t.id
						and tom.p_bsclientid = :p_clientid ". $w2;

                $stid = $this->aml_oracle->execute($q, __LINE__, $bindings);
                oci_fetch($stid);
                oci_free_statement($stid);
                $total_pages = ceil($cnt / $rows);

                $unprocessed_str = str_replace("'", "''", ("Необработанные"));
                $processed_str = str_replace("'", "''", ("Обработанные"));
                $archived_str = str_replace("'", "''", ("Архивные"));

                $bindings = array(':v1' => $start, ':v2' => $end, ':p_clientid' => $p_clientid);
                if ($wvalue != '') {
		            foreach($wvalue as $k => $w) {
		                $bindings[$k] = $w;
		            }
                }
                // выборка страницы
                $stid = $this->aml_oracle->execute("SELECT *  FROM (
                                               SELECT rownum as rn, vt.* FROM
                                                 (SELECT ".implode(',',$select_fields)."
                                                  FROM TB_SUSPICIOUSOPERATIONS t
                                                  LEFT JOIN TB_SUSP_MEMBERS m1 ON m1.P_SUSPICIOUSOPERATIONID = t.id
												  ".implode(' ',$joins)."
                                                  WHERE  (m1.P_BSCLIENTID = :p_clientid)
                                                  ".$w2."
                                                  $order_by) vt ) WHERE rn >= :v1 and rn < :v2 ", __LINE__, $bindings);

             
			   $nrows = oci_fetch_all($stid, $results);
                oci_free_statement($stid);

                header("Content-type: text/xml;charset=utf-8");
                print $this->aml_grid->output_xml($page, $total_pages, $cnt, $results, $fields_info, array(), true);

                break;
            case 'clients':

		        $fields_info   = $this->aml_metainfo->get_table_info('TB_SUSPICIOUSMEMBERS', 0, 0);
		        $order_by      = $this->aml_grid->get_sorting_clause($fields_info);
		        $where         = $this->aml_grid->get_where_clause_multi('TB_SUSPICIOUSMEMBERS');

                $extra_where = array(' 1 = 1 ');

                if ($where != null) {
                    $w1 = 'WHERE ' . $where[0] . ' AND ' . implode (" AND ",$extra_where);
                    $w2 = 'AND ' . $where[0] . ' AND ' . implode (" AND ",$extra_where);
                    $wvalue = $where[1];
                } else {
                    if (count($extra_where)) {
                        $w1 = 'WHERE ' . implode (" AND ",$extra_where);
                        $w2 = 'AND ' . implode (" AND ",$extra_where);
                    }
                }
                // кол-во записей в табле
                $cnt = 0;
                $bindings = array('CNT' => &$cnt);

                if ($wvalue != '') {
		            foreach($wvalue as $k => $w) {
		                $bindings[$k] = $w;
		            }
                }

                $stid = $this->aml_oracle->execute("SELECT COUNT(*) cnt FROM TB_SUSPICIOUSMEMBERS $w1",__LINE__, $bindings);

                oci_fetch($stid);
                oci_free_statement($stid);
                $total_pages = ceil($cnt / $rows);

                $bindings = array(':v1' => $start, ':v2' => $end);
                if ($wvalue != '') {
		            foreach($wvalue as $k => $w) {
		                $bindings[$k] = $w;
		            }
                }
                // выборка страницы

				list($select_fields, $joins) = $this->aml_metainfo->get_joins($fields_info);

		        $q = "SELECT * FROM ( SELECT rownum as rn, f.* FROM ( SELECT ".implode(',',$select_fields)." FROM TB_SUSPICIOUSMEMBERS t ".implode(' ',$joins)." ".$w1." ".$order_by.") f ) WHERE rn >= :v1 and rn < :v2 ";
				$stid = $this->aml_oracle->execute($q, __LINE__, $bindings);

                $nrows = oci_fetch_all($stid, $results);
                oci_free_statement($stid);

                header("Content-type: text/xml;charset=utf-8");
                print $this->aml_grid->output_xml($page, $total_pages, $cnt, $results, $fields_info, array(), true);
                break;
            case 'operations_for_kfm': // for this 1 status only
            case 'monitoring':
            case 'suspicious':
                $fields_info = $this->aml_metainfo->get_table_info('TB_SUSPICIOUSOPERATIONS', 0, 0);
                $order_by    = $this->aml_grid->get_sorting_clause($fields_info);
/*                $where       = $this->aml_grid->get_where_clause($fields_info); */
                $where       = $this->aml_grid->get_where_clause_multi('TB_SUSPICIOUSOPERATIONS');
                /*
                    2 => ('Необработанные'),
                    //3 => ('Обработанные'),
                    4 => ('Архивные'),
                    5 => ('Удаленные'),
                    6 => ('На доработку')
					15 => ('Очередь На Отправку')
					                 * */

                $extra_where = $extra_where_cnt = array('1 = 1');
                $uisettings = $this->native_session->userdata('ui.' . $what);
				//print"<pre>";die(var_dump($uisettings));
				
				if(isset($uisettings['status'])){
						$uisettings['status'] = explode('_',$uisettings['status']);

						switch($uisettings['status'][1]){
							case 1: //совершено
								$extra_where[] = "t.P_MESS_STATUS=1";
							break;
							case 2: //приостановлено
								$extra_where[] = "t.P_MESS_STATUS in (2,3,4,5)";
							break;
						}
				
				}

                // suspic and monitoring
                if ($what != 'operations_for_kfm') {
                    if ($what == 'suspicious' && $this->aml_security->check_privilege(12) || $this->aml_security->check_privilege(91) ) {
                        $extra_where[] = "t.P_OPERATIONSTATUS >= 2"; // подозрит old version p_suspicioustypecode
                        $extra_where_cnt[] = "t.P_OPERATIONSTATUS >= 2";
                   } else if ($what == 'monitoring' && $this->aml_security->check_privilege(11) ||$this->aml_security->check_privilege(56) || $this->aml_security->check_privilege(91)) {
                        $extra_where[] = "t.P_OPERATIONSTATUS = 1"; // подл. фм
                        $extra_where_cnt[] = "t.P_OPERATIONSTATUS = 1"; // подл. фм
                    } else {
                        $extra_where[] = ' 1 = 0 '; //no rights to view susp ops
                        $extra_where_cnt[] = ' 1 = 0 '; //no rights to view susp ops
                    }
				if(isset($uisettings['status'])){
                    switch ($uisettings['status'][0]) {
                        case 2: // необработанные
                            $extra_where[] = ' (t.p_sendtokfmbool = 0 OR t.p_sendtokfmbool IS NULL) ';
                            break;
                        case 4: // архивные
                            $extra_where[] = ' t.p_sendtokfmbool = 2 ';
                            break;
                        case 5: // удаленные
                            $extra_where[] = ' t.p_sendtokfmbool = 3 ';
                            break;
                        case 6: // на доработку
                            $extra_where[] = ' t.p_sendtokfmbool = 5 ';
                            break;
						case 15: // Очередь На Отправку
                            $extra_where[] = ' t.p_sendtokfmbool = 15 ';
                            break;
                    }
					 }
                }
                else { // send to kfm
                    $allowed_ops = array();
				
                    if ( $this->aml_security->check_privilege(15) || $this->aml_security->check_privilege(35) || $this->aml_security->check_privilege(91) ) {
                        $allowed_ops[] = "t.P_OPERATIONSTATUS >= 2"; // подозрит - old version t.p_suspicioustypecode > 100
                    }
                    if ($this->aml_security->check_privilege(19) || $this->aml_security->check_privilege(34) ||  $this->aml_security->check_privilege(77) || $this->aml_security->check_privilege(56) || $this->aml_security->check_privilege(91)) {
                        $allowed_ops[] = "t.P_OPERATIONSTATUS = 1"; // подл. фм
                    }

                    if (count($allowed_ops) == 2) {
                        // have all permission, do nothing
                    } else if (count($allowed_ops) == 1) {
                        $extra_where[] = implode('', $allowed_ops);
                        $extra_where_cnt[] = implode('', $allowed_ops);
                    } else { // no permission
                        $extra_where[] = ' 1 = 0 ';
                        $extra_where_cnt[] = ' 1 = 0 ';
                    }
			if(isset($uisettings['status'])){
                    switch ($uisettings['status'][0]) {
                        case 3: // отправлено
                            $extra_where[] = " t.p_sendtokfmbool = 1 ";
                            break;
                        case 8: // на отправку
                            $extra_where[] = ' t.p_sendtokfmbool = 4 ';
                            $extra_where[] = " t.id not in (select distinct p_operationid from tb_send_to_kfm) ";
                            break;
                        case 9: // отбраковано КФМ
                            $extra_where[] = ' t.p_sendtokfmbool = 6 ';
                            break;
                        case 10: // success
                            $extra_where[] = ' t.p_sendtokfmbool = 7 ';
                            break;
                        case 12: // ошибка при отправке
                        	$extra_where[] = ' t.p_sendtokfmbool = 4 ';
                            $extra_where[] = " t.id in (select distinct p_operationid from tb_send_to_kfm) ";
                            break;
                         case 14:
                         	$extra_where[] = 't.p_sendtokfmbool = 12';
                         	break;
                         case 13:
                         	$extra_where[] = 't.p_sendtokfmbool = 13';
                         	break;
                    }
				}
					
					
                }

                if (count($this->aml_auth->get_branches()) > 0) {
                    $extra_where[] = ' t.P_ISSUEDBID IN (' . implode (",", $this->aml_auth->get_branches()) . ') ';
                    $extra_where_cnt[] = ' t.P_ISSUEDBID IN (' . implode (",", $this->aml_auth->get_branches()) . ') ';
                }

                if (!empty($uisettings['date_from'])) {
                    $extra_where[] = "t.P_OPERATIONDATETIME >= TO_DATE('" . $uisettings['date_from'] . "','" . $this->config->item('date_format') . "') ";
                    $extra_where_cnt[] = "t.P_OPERATIONDATETIME >= TO_DATE('" . $uisettings['date_from'] . "','" . $this->config->item('date_format') . "') ";
                }
                if (!empty($uisettings['date_until'])) {
                    $extra_where[] = "t.P_OPERATIONDATETIME < TO_DATE('" . $uisettings['date_until'] . "','" . $this->config->item('date_format') . "') + 1";
                    $extra_where_cnt[] = "t.P_OPERATIONDATETIME < TO_DATE('" . $uisettings['date_until'] . "','" . $this->config->item('date_format') . "') + 1";
                }

                if ($where != null) {
                    $w1 = 'WHERE ' . $where[0] . ' AND ' . implode (" AND ",$extra_where);
                    $wc1 = 'WHERE ' . $where[0] . ' AND ' . implode (" AND ",$extra_where_cnt);
                    $w2 = 'AND ' . $where[0] . ' AND ' . implode (" AND ",$extra_where);
                    $wvalue = $where[1];
                } else {
                    $w1 = 'WHERE ' . implode (" AND ",$extra_where);
                    $wc1 = 'WHERE ' . implode (" AND ",$extra_where_cnt);
                    $w2 = 'AND ' . implode (" AND ",$extra_where);
                }

                // кол-во записей в табле
                $cnt = 0;
                if ($wvalue) {
                    $bindings = $wvalue;
                }
                $bindings['CNT'] = &$cnt;

                $q = "SELECT COUNT(*) cnt FROM " . $this->db_schema_prefix . "TB_SUSPICIOUSOPERATIONS t " . $w1;
              
			   $stid = $this->aml_oracle->execute($q, __LINE__, $bindings);
                oci_fetch($stid);
                oci_free_statement($stid);
                $total_pages = ceil($cnt / $rows);
				
					$bindings2 = array();

                // выборка страницы
                if ($wvalue) {
                    $bindings = $bindings2 = $wvalue;
                }
                $bindings[':v1'] = $start;
                $bindings[':v2'] = $end;

                list($select_fields, $joins) = $this->aml_metainfo->get_joins($fields_info);

                $q = "SELECT * FROM ( SELECT rownum as rn, f.* FROM (SELECT ".implode(',',$select_fields).",
                                            NULL p_virtual_members
                      FROM " . $this->db_schema_prefix . "TB_SUSPICIOUSOPERATIONS t ".implode(' ',$joins)." ".$w1." ".$order_by.") f ) WHERE rn >= :v1 and rn < :v2 ";

                $stid = $this->aml_oracle->execute($q, __LINE__, $bindings);
                $nrows = oci_fetch_all($stid, $results);
                oci_free_statement($stid);
				//print"<pre>";die(var_DUMP($q,$bindings));
				$send_to_kfm = array(0=>2, 1=>3, 3=>5, 4=>8, 5=>6, 6=>9, 7=>10, 8=>11, 12=>14, 13=>13, 15=>15);
				$q = "SELECT P_SENDTOKFMBOOL, DECODE(t.P_MESS_STATUS, 1,1, 2,2, 3,2, 4,2, 5,2, 0) as P_MESS_STATUS, count(t.ID) AS CNT
						from TB_SUSPICIOUSOPERATIONS t ".$wc1." group by t.P_SENDTOKFMBOOL, DECODE(t.P_MESS_STATUS, 1,1, 2,2, 3,2, 4,2, 5,2, 0)
						order by DECODE(t.P_MESS_STATUS, 1,1, 2,2, 3,2, 4,2, 5,2, 0)";

				$stid = $this->aml_oracle->execute($q, __LINE__, $bindings2);
				oci_fetch_all($stid, $results_cnt);
	
				$q = "SELECT DECODE(t.P_MESS_STATUS, 1,1, 2,2, 3,2, 4,2, 5,2, 0) as P_MESS_STATUS, count(t.ID) AS CNT
						from TB_SUSPICIOUSOPERATIONS t ".$wc1."
						and t.id in (select distinct p_operationid from tb_send_to_kfm) and t.p_sendtokfmbool='4'
						group by DECODE(t.P_MESS_STATUS, 1,1, 2,2, 3,2, 4,2, 5,2, 0)
						order by DECODE(t.P_MESS_STATUS, 1,1, 2,2, 3,2, 4,2, 5,2, 0)";
				
				$stid = $this->aml_oracle->execute($q, __LINE__, $bindings2);
				oci_fetch_all($stid, $error_results_cnt);

				$count_by_status = array();

				for($i=0;$i<count($error_results_cnt['P_MESS_STATUS']);$i++){
					$count_by_status['12'.($error_results_cnt['P_MESS_STATUS'][$i]?'_'.$error_results_cnt['P_MESS_STATUS'][$i]:'')] = $error_results_cnt['CNT'][$i];
				}

				for($i=0;$i<count($results_cnt['P_SENDTOKFMBOOL']);$i++){
					if($results_cnt['P_SENDTOKFMBOOL'][$i]==4){
						$results_cnt['CNT'][$i] -= $count_by_status['12'.($results_cnt['P_MESS_STATUS'][$i]?'_'.$results_cnt['P_MESS_STATUS'][$i]:'')];
					}
					$count_by_status[$send_to_kfm[$results_cnt['P_SENDTOKFMBOOL'][$i]].($results_cnt['P_MESS_STATUS'][$i]?'_'.$results_cnt['P_MESS_STATUS'][$i]:'')] = $results_cnt['CNT'][$i];
				}

                header("Content-type: text/xml;charset=utf-8");
				 
			   print $this->aml_grid->output_xml($page, $total_pages, $cnt, $results, $fields_info, $count_by_status);

                break;
            case 'online';
                $fields_info = $this->aml_metainfo->get_table_info('TB_ONLINEOPERATIONS', 0, 0);
                $order_by    = $this->aml_grid->get_sorting_clause($fields_info);
                $where       = $this->aml_grid->get_where_clause_multi('TB_ONLINEOPERATIONS');

                $extra_where = array();
                $uisettings = $this->native_session->userdata('ui.online');

                switch ($uisettings['status']) {
					case 0:
						/*Для вывода всех операций с % совпадения >= попроговому значению*/
						$q1 = "Select P_VALUE from TB_PARAMS where ID = 3705826";
						$r = $this->aml_oracle->execute($q1,__LINE__);
						$pr = oci_fetch_all($r, $percent);
                        $extra_where[] = ' P_IS_IPDL = 0 and P_SIMILARPERCENT >= '.$percent["P_VALUE"][0].' ';
                        break;
                    case 1:
                        $extra_where[] = ' NVL(t.P_COMPLIANCEAPPROVALBOOL,0) = 1 and P_IS_IPDL = 0 ';
                        break;
                    case 2:
                        $extra_where[] = ' NVL(t.P_COMPLIANCEAPPROVALBOOL,0) = 0 and P_IS_IPDL = 0 ';
                        break;
                    case 3:
                        $extra_where[] = ' NVL(t.P_COMPLIANCEAPPROVALBOOL,0) = 2 and P_IS_IPDL = 0 ';
                        break;
						// IPDl
                    case 4:
                       $q1 = "Select P_VALUE from TB_PARAMS where ID = 3705826";
						$r = $this->aml_oracle->execute($q1,__LINE__);
						$pr = oci_fetch_all($r, $percent);
                        $extra_where[] = ' P_IS_IPDL = 1 and P_SIMILARPERCENT >= '.$percent["P_VALUE"][0].' ';
                        break;
						
					case 5:
                        $extra_where[] = ' NVL(t.P_IPDL_APROVE,0) = 1 and P_IS_IPDL = 1';
                        break;
					case 6:
                        $extra_where[] = ' NVL(t.P_IPDL_APROVE,0) = 0 and P_IS_IPDL = 1';
                        break;
					case 7:
                        $extra_where[] = ' NVL(t.P_IPDL_APROVE,0) = 2 and P_IS_IPDL = 1';
                        break;
                }

                if (count($this->aml_auth->get_branches()) > 0) {
                    $extra_where[] = ' t.P_ISSUEDBID IN (' . implode (",", $this->aml_auth->get_branches()) . ') ';
                }

                if (!empty($uisettings['date_from'])) {
                    $extra_where[] = "t.P_DATE_INSERT >= TO_DATE('" . $uisettings['date_from'] . "','"  . $this->config->item('date_format') .  "') ";
                }
                if (!empty($uisettings['date_until'])) {
                    $extra_where[] = "t.P_DATE_INSERT < TO_DATE('" . $uisettings['date_until'] . "','" . $this->config->item('date_format') . "') + 1";
                }

                if ($where != null) {
                    $w1 = 'WHERE ' . $where[0] . ' AND ' . implode (" AND ",$extra_where);
                    $w2 = 'AND ' . $where[0] . ' AND ' . implode (" AND ",$extra_where);
                    $wvalue = $where[1];
                }  else {
                    $w1 = 'WHERE ' . implode (" AND ",$extra_where);
                    $w2 = 'AND ' . implode (" AND ",$extra_where);
                }

                // кол-во записей в табле
                $cnt = 0;
                $bindings = array('CNT' => &$cnt);
                if ($wvalue) {
                    foreach($wvalue as $k => $w) {
                        $bindings[$k] = $w;
                    }
                }
                $q = "SELECT COUNT(*) cnt FROM TB_ONLINEOPERATIONS t " . $w1;
				//die($q);
                $stid = $this->aml_oracle->execute($q, __LINE__, $bindings);
                oci_fetch($stid);
                oci_free_statement($stid);
                $total_pages = ceil($cnt / $rows);

                // выборка страницы
                $bindings = array(':v1' => $start,':v2' => $end);
                if ($wvalue) {
                    foreach($wvalue as $k => $w) {
                        $bindings[$k] = $w;
                    }
                }
                list($select_fields, $joins) = $this->aml_metainfo->get_joins($fields_info);
				$q1 = "Select P_VALUE from TB_PARAMS where ID = 3705826";
				$r = $this->aml_oracle->execute($q1,__LINE__);
				$pr = oci_fetch_all($r, $percent);
				
                $q = "SELECT * FROM ( SELECT rownum as rn, f.* FROM (SELECT ".implode(',',$select_fields)." FROM TB_ONLINEOPERATIONS t ".implode(' ',$joins)." ".$w1." ".$order_by."  ) f ) WHERE rn >= :v1 and rn < :v2 ";
                
				$stid = $this->aml_oracle->execute($q,__LINE__,$bindings);
                $nrows = oci_fetch_all($stid, $results);
                oci_free_statement($stid);
				//die(var_dump($results));
                header("Content-type: text/xml;charset=utf-8");
                print $this->aml_grid->output_xml($page, $total_pages, $cnt, $results, $fields_info);

                break;
            case 'offline':
                // кол-во записей в табле
                $fields_info = $this->aml_metainfo->get_table_info('TB_OFFLINEOPERATIONS', 0, 0);
                $order_by    = $this->aml_grid->get_sorting_clause($fields_info);
                $where       = $this->aml_grid->get_where_clause_multi('TB_OFFLINEOPERATIONS');

                $extra_where = array();
                $uisettings = $this->native_session->userdata('ui.offline');

                if (count($this->aml_auth->get_branches()) > 0) {
                    $extra_where[] = ' t.P_ISSUEDBID IN (' . implode (",", $this->aml_auth->get_branches()) . ') ';
                }

                if (!empty($uisettings['date_from'])) {
                    $extra_where[] = "t.P_OPERATIONDATETIME >= TO_DATE('" . $uisettings['date_from'] . "','" . $this->config->item('date_format') . "') ";
                }
                if (!empty($uisettings['date_until'])) {
                    $extra_where[] = "t.P_OPERATIONDATETIME < TO_DATE('" . $uisettings['date_until'] . "','" . $this->config->item('date_format') . "') + 1";
                }

                if ($where != null) {
                    $w1 = 'WHERE ' . $where[0] . ' AND ' . implode (" AND ",$extra_where);
                    $w2 = 'AND ' . $where[0] . ' AND ' . implode (" AND ",$extra_where);
                    $wvalue = $where[1];
                } else {
                    $w1 = 'WHERE ' . implode (" AND ",$extra_where);
                    $w2 = 'AND ' . implode (" AND ",$extra_where);
                }

                // кол-во записей в табле
                $cnt = 0;
                $bindings = array('CNT' => &$cnt);
                if ($wvalue) {
                    foreach($wvalue as $k => $w) {
                        $bindings[$k] = $w;
                    }
                }
                $q = "SELECT COUNT(*) cnt FROM " . $this->db_schema_prefix . "TB_OFFLINEOPERATIONS t " . $w1;
                $stid = $this->aml_oracle->execute($q,__LINE__, $bindings);
                oci_fetch($stid);
                $total_pages = ceil($cnt / $rows);

                // выборка страницы
                $bindings = array(':v1' => $start,':v2' => $end);
                if ($wvalue) {
                    foreach($wvalue as $k => $w) {
                        $bindings[$k] = $w;
                    }
                }
                list($select_fields, $joins) = $this->aml_metainfo->get_joins($fields_info);

                $q = "SELECT * FROM ( SELECT rownum as rn, f.* FROM (SELECT ".implode(',',$select_fields)." FROM " . $this->db_schema_prefix . "TB_OFFLINEOPERATIONS t ".implode(' ',$joins)." ".$w1." ".$order_by.") f ) WHERE rn >= :v1 and rn < :v2 ";
                $stid = $this->aml_oracle->execute($q,__LINE__,$bindings);
                $nrows = oci_fetch_all($stid, $results);
                oci_free_statement($stid);

                header("Content-type: text/xml;charset=utf-8");
                print $this->aml_grid->output_xml($page, $total_pages, $cnt, $results, $fields_info);
                break;
            case 'audit':/*Изменил tb_audit_all на vw_audit_all Адилет по заявке 9942*/
                $fields_info = $this->aml_metainfo->get_table_info('TB_AUDIT_ALL');
                $order_by    = $this->aml_grid->get_sorting_clause($fields_info);
                $where       = $this->aml_grid->get_where_clause_multi('TB_AUDIT_ALL');

                $uisettings = $this->native_session->userdata('ui.audit');

                if($where){
                	$where[0] = "where ".$where[0];
                } else {
                	$where[0] = "where 1=1";
                }

		        $q = 'SELECT a.*,rownum rn FROM VW_AUDIT_ALL a';
		        $values = array();
		        if ($uisettings['date_from']) {
		            $where[0] .= " and p_date_update >= TO_DATE('" . $uisettings['date_from'] . "', '" . $this->config->item('date_format') . "')";
		        }
		        if ($uisettings['date_until']) {
		            $where[0] .= " and p_date_update < TO_DATE('" . $uisettings['date_until'] . "','" . $this->config->item('date_format') . "') + 1";
		        }
		        if ( isset($uisettings['username']) && $uisettings['username']) {
		            $where[0] .= " and p_username = '".$uisettings['username']."'";
		        }

                // кол-во записей в табле
                $cnt = 0;
                $bindings = array('CNT' => &$cnt);
                if ( isset($where[1]) && $where[1]) {
                    foreach($where[1] as $k => $w) {
                        $bindings[$k] = $w;
                    }
                }
                $q = "SELECT COUNT(*) cnt FROM " . $this->db_schema_prefix . "VW_AUDIT_ALL a " . $where[0];
                $stid = $this->aml_oracle->execute($q,__LINE__, $bindings);
                oci_fetch($stid);
                $total_pages = ceil($cnt / $rows);

				$q = "select * from (select rownum as rn, f.* from ( select a.* from VW_AUDIT_ALL a ".$where[0].' '.$order_by.") f ) WHERE rn >= :v1 and rn < :v2 ";

                $bindings = array(':v1' => $start,':v2' => $end);
                if ( isset($where[1]) && $where[1]) {
                    foreach($where[1] as $k => $w) {
                        $bindings[$k] = $w;
                    }
                }

                $stid = $this->aml_oracle->execute($q,__LINE__,$bindings);
                $nrows = oci_fetch_all($stid, $results);
                oci_free_statement($stid);

            	header("Content-type: text/xml;charset=utf-8");
                print $this->aml_grid->output_xml($page, $total_pages, $cnt, $results, $fields_info);
                break;
            case 'operationstree':
                $can_use_reports = $this->aml_security->check_privilege(5); // REPORTS
                if (!$can_use_reports) {
                    print '<li><a href="#">' . ('Отсутствуют права для данного действия.') . '</a></li>';
                    return;
                }

                $uisettings = $this->native_session->userdata('ui.operationstree');
                if (empty($uisettings['date_from'])) {
                    $uisettings['date_from'] = '01.01.1900';
                }
                if (empty($uisettings['date_until'])) {
                    $uisettings['date_until'] = '01.01.2050';
                }

                $by_credit = true;
                $post_id = $this->input->post('id');

                if (empty($post_id)) {
                    if ($param2 == 'debit') {
                        $by_credit = false;
                    }
                    $accno = $uisettings['account'];
                } else {
                    list($whatpart, $accno) = explode('_', $post_id);
                    if ($whatpart == 'CREDIT') {
                        $by_credit = true;
                    } else {
                        $by_credit = false;
                    }
                    $accno = htmlentities($accno, ENT_QUOTES, 'utf-8');
                }

                $not_specified_str = ('Не указан');

                if (!$by_credit) {
					$stid = $this->aml_oracle->execute("
						select * from (
							select
								nvl(m2.p_account, '{$not_specified_str}') account,
								max(m2.p_name) credname,
								count(m2.p_account) cnt,
								sum(o.p_baseamount) sumvalue,
								sumstr(o.id || ',') id_list
							from tb_offlineoperations o, tb_off_members m1, tb_off_members m2
							where
								o.id = m1.p_operationid and
								o.id = m2.p_operationid and
								m1.p_clientrole = 1 and
								m1.p_account = to_char(:acc) and
								m2.p_clientrole = 2 and
								m2.p_account is not null and
								m2.p_account != '-' and
								m1.p_account != m2.p_account and
								o.p_operationdatetime between to_date(:dt1, 'DD.MM.YYYY') and to_date(:dt2, 'DD.MM.YYYY') and
								" . $this->aml_auth->get_branches_sql('o.P_ISSUEDBID') . "
								group by m2.p_account
						)
						order by sumvalue desc", __LINE__, array(':acc' => $accno,':dt1' => $uisettings['date_from'], ':dt2' => $uisettings['date_until']),false);

                    $result = '';
                    if ($stid) {
                        while($row = oci_fetch_array($stid, OCI_ASSOC)) {
                            $linkurl = '#nojs'; // site_url('page/viewdata/offline/' . $row['ID_LIST'])
//                            $result .= '<li id="DEBIT_' . $row['ACCOUNT'] . '"><a rel="' . $row['ID_LIST'] . '" class="operations-tree-link" href="' . $linkurl . '" title="'.htmlentities($row['CREDNAME'], ENT_QUOTES, 'utf-8').'">' . $row['ACCOUNT'] . ', ' . ('кол-во:') . ' ' . $row['CNT'] . ', <strong>' . $this->aml_html->nf($row['SUMVALUE']) . ' KZT</strong></a><ul></ul></li>';
                            $result .= '<li id="DEBIT_' . $row['ACCOUNT'] . '"><a rel="' . $row['ID_LIST'] . '" class="operations-tree-link" href="' . $linkurl . '" title="'.$row['ACCOUNT'].'">' . htmlentities($row['CREDNAME'], ENT_QUOTES, 'utf-8') . ', ' . ('кол-во:') . ' ' . $row['CNT'] . ', <strong>' . $this->aml_html->nf($row['SUMVALUE']) . ' KZT</strong></a><ul></ul></li>';
                        }
                    }
                } else {
					$stid = $this->aml_oracle->execute("
						select * from (
							select
								nvl(m1.p_account, '{$not_specified_str}') account,
								max(m1.p_name) debitname,
								count(m1.p_account) cnt,
								sum(o.p_baseamount) sumvalue,
								sumstr(o.id || ',') id_list
							from tb_offlineoperations o, tb_off_members m1, tb_off_members m2
							where
								o.id = m1.p_operationid and
								o.id = m2.p_operationid and
								m1.p_clientrole = 1 and
								m1.p_account != '-' and
								m1.p_account is not null and
								m2.p_clientrole = 2 and
								m2.p_account = to_char(:acc) and
								m1.p_account != m2.p_account and
								o.p_operationdatetime between to_date(:dt1, 'DD.MM.YYYY') and to_date(:dt2, 'DD.MM.YYYY') and
								" . $this->aml_auth->get_branches_sql('o.P_ISSUEDBID') . "
								group by m1.p_account
						)
						order by sumvalue desc", __LINE__, array(':acc' => $accno,':dt1' => $uisettings['date_from'], ':dt2' => $uisettings['date_until']),false);

                    $result = '';
                    if($stid) {
                        while($row = oci_fetch_array($stid, OCI_ASSOC)) {
                            $linkurl = '#nojs'; // site_url('page/viewdata/offline/' . $row['ID_LIST'])
//                            $result .= '<li id="CREDIT_' . $row['ACCOUNT'] . '"><a rel="' . $row['ID_LIST'] . '" class="operations-tree-link" href="' . $linkurl . '" title="'.htmlentities($row['DEBITNAME'], ENT_QUOTES, 'utf-8').'">' . $row['ACCOUNT'] . ', ' . ('кол-во:') . ' ' . $row['CNT'] . ', <strong>' . $this->aml_html->nf($row['SUMVALUE']) . ' KZT</strong></a><ul></ul></li>';
                            $result .= '<li id="CREDIT_' . $row['ACCOUNT'] . '"><a rel="' . $row['ID_LIST'] . '" class="operations-tree-link" href="' . $linkurl . '" title="'.$row['ACCOUNT'].'">' . htmlentities($row['DEBITNAME'], ENT_QUOTES, 'utf-8') . ', ' . ('кол-во:') . ' ' . $row['CNT'] . ', <strong>' . $this->aml_html->nf($row['SUMVALUE']) . ' KZT</strong></a><ul></ul></li>';
                        }
                    }
                }

                if (empty($result)) {
                    $result = '<li>' . ('Данные отсутствуют') . '</li>';
                }
                print $result;
                break;
			case 'jobs':
                $can_use_reports = $this->aml_security->check_privilege(38);
                if (!$can_use_reports) {
                    print '<li><a href="#">' . ('Отсутствуют права для данного действия.') . '</a></li>';
                    return;
                }
                $fields_info = $this->aml_metainfo->get_table_info('VW_JOB_RUNING');
                $order_by    = $this->aml_grid->get_sorting_clause($fields_info);
		        $where		 = $this->aml_grid->get_where_clause_multi('VW_JOB_RUNING');

		        if($where != null){
					$w1 = 'WHERE ' . $where[0];
		            $wvalue = $where[1];
				} else {
					$w1 = '';
				}

				$bindings = array('CNT' => &$cnt);
				if ($wvalue) {
				    foreach($wvalue as $k => $w) {
				        $bindings[$k] = $w;
				    }
				}

				$cnt = 0;
                $stid = $this->aml_oracle->execute("SELECT COUNT(*) cnt FROM " . $this->db_schema_prefix . "VW_JOB_RUNING ".$w1,__LINE__, $bindings);
                oci_fetch($stid);
                $total_pages = ceil($cnt / $rows);


				$bindings = array(':v1' => $start,':v2' => $end);
				$bindings2 = array();
				if ($wvalue) {
				    foreach($wvalue as $k => $w) {
				        $bindings[$k] = $w;
				        $bindings2[$k] = $w;
				    }
				}

				list($select_fields, $joins) = $this->aml_metainfo->get_joins($fields_info, $no_id = 1);
				$q = "select * from (select rownum as rn, f.* from ( select ".implode(',',$select_fields)." from VW_JOB_RUNING t ".implode(' ',$joins).' '.$w1." ".$order_by.") f ) WHERE rn >= :v1 and rn < :v2";
				$stid = $this->aml_oracle->execute($q, __LINE__, $bindings);
                $nrows = oci_fetch_all($stid, $results);
                oci_free_statement($stid);
            	header("Content-type: text/xml;charset=utf-8");
                print $this->aml_grid->output_xml($page, $total_pages, $cnt, $results, $fields_info, array(), false, 'JOB');

				break;
        }
    }

    // отправить в СДФО
    function sendtosdfo() {
        $sql =
        "BEGIN " .
        " UPDATE TB_SUSPICIOUSOPERATIONS t SET t.P_USERNAME = :username, t.P_DATE_UPDATE = SYSDATE,P_COMMENT='Отправлено в СДФО'  WHERE t.id = :id; " .
        " COMMIT; ".
        " :result := Pkg_SDFO_Exchange.Send(:id,:strerr); " .
        " COMMIT; ".
        "END; ";
        $this->_send_to_sdfo_or_kfm($sql);
    }

    /**
     * @description Сохранить значение в сессии "на 1 раз", до 1го считывания
     */
    function setflashvalue() {
        $this->aml_auth->check_auth();
        $pname  = $this->input->post('pname');
        $pvalue = $this->input->post('pvalue');
        switch($pname) {
            case 'operations_list':
                if (preg_match('#[0-9,]+#', $pvalue)) {
                    $this->native_session->set_flashdata($pname, $pvalue);
                } else {
                    print ('ERR Неверное значение параметра');
                }
                break;
        }
    }

    // отправить в КФМ
    function sendtokfm() {
        $sql =
        "BEGIN " .
        " GET_SYSTEM_RULE(:id, :strerr); " .
        " UPDATE TB_SUSPICIOUSOPERATIONS t SET t.P_USERNAME = :username, t.P_DATE_UPDATE = sysdate, t.p_Sendtokfmbool = 15  WHERE t.id = :id; " .
		" COMMIT; " .
        " :result := pkg_sdfo_kfm_exchange_new_ca.send_kfm(:id,:strerr); " .
        " COMMIT; " .
        "END; ";
        $this->_send_to_sdfo_or_kfm($sql);
    }

    // отправить в СДФО или КМФ
    function _send_to_sdfo_or_kfm($sql) {
        $this->aml_auth->check_auth(); // checkauth
        //$c = $this->_get_connection();
        $can_edit = $this->aml_security->check_privilege(15) || $this->aml_security->check_privilege(19);
        if (!$can_edit) {
            print ('Отсутствуют права для данного действия.');
            return;
        }
        $this->native_session->set_flashdata('processing_results', null);
        $processing_results = array();
        $records = explode(',',$this->input->post('records'));
        foreach($records as $r){
            $id = intval($r);
			
            $strerr = str_repeat(' ',8000);
            $result = -999999;

            if ($id) {
                $username = $this->aml_auth->get_username();
                $stid = $this->aml_oracle->execute($sql,__LINE__, array(':id' => &$id, ':result' => &$result,':strerr' => &$strerr,':username' => $username), false);

                if (!$stid) {
                    $err = $this->aml_oracle->get_last_error();
                    $err['message'] = preg_replace("#ORA-.*#", "", $err['message']);

                    $processing_results[] = sprintf(("Ошибка при обработке операции, ID = %d, "), $id) . htmlentities($err['message'],ENT_QUOTES,'utf-8');
                } else {
                    if ($result == 0) {
                        $processing_results[] = sprintf(("Ошибка при обработке операции, ID = %d, "), $id) . htmlentities($strerr . ' result code: ' . $result ,ENT_QUOTES,'utf-8');
                    } else {
                        $processing_results[] = sprintf(('Операция отправлена в КФМ успешно. ID = %d'), $id);
                    }
                }
            } else {
                $processing_results[] = sprintf(('Неверный ID операции (primary key = %d)'), $id);
            }
        }
        $processing_results[] = ('Обработка завершена');
        print implode("\n",$processing_results);
    }

    function processing_results() {
        $vars['processing_results'] = $this->native_session->flashdata('processing_results');
        $vars['content'] = $this->load->view('processing-results', $vars, true);
        $this->load->view('main', $vars);
    }

    function offline_to_suspic() {
        $can_edit = $this->aml_security->check_privilege(12) || $this->aml_security->check_privilege(11);
        if (!$can_edit) {
            print "alert('" . ('Отсутствуют права для данного действия.') . "')";
            return;
        }
        $code = intval($this->input->post('code'));
        $suspic_type = intval($this->input->post('suspic_type'));
        $reason = intval($this->input->post('reason'));
        $susp_1 = intval($this->input->post('susp_1'));
        $susp_2 = intval($this->input->post('susp_2'));
        $susp_3 = intval($this->input->post('susp_3'));
        $DetByBranch2 = intval($this->input->post('DetByBranch'));
		//die(var_dump($_POST));
        if ($code <= 0) {
            print "alert('" . ('Неверный код сценария.') . "')";
            return;
        }

        $records = explode(",", $this->input->post('records'));
		$iCount = 0;
		$iErCount = 0;
		$strE = '';
		$tmp = '';
        foreach($records as $id) {
            if (is_numeric($id)){
                $id = floatval($id);
				$strerr = str_repeat(' ',8000);
                $stid = $this->aml_oracle->execute('BEGIN pr_off_to_suspic(:id,:suspic_type,:code,:reason,:susp_1,:susp_2,:susp_3,:det_by_branch,:strerr); COMMIT; END;',__LINE__,
                	array(':id' => &$id, ':suspic_type'=> &$suspic_type, ':code' => $code, ':reason'=>$reason,
                		  ':susp_1'=>$susp_1, ':susp_2'=>$susp_2, ':susp_3'=>$susp_3, ':det_by_branch'=>$DetByBranch , ':strerr'=>&$strerr));
				if (trim($strerr)!=''){
				  $iErCount = $iErCount + 1;
				  $tmp = $strerr;
				}else {
				  $iCount = $iCount + 1;
				}
            }
        }
		if (($iErCount>0)&&($iCount>0)){
			$strE = 'Данные успешно перенесены для :'. $iCount . '\n Выполнено с ошибками: '.$iErCount.'\n Текст ошибки: '.$tmp;
		}
		elseif ($iErCount==0){
			$strE = 'Данные перенесены.';
		}
		else {
			$strE = $strerr;
		}

        //print "alert('" . ('Данные перенесены.') . "')";
		print 'alert("'. $strE . ' ");';
    }

    function offline_operations($type=0) {
        $can_view = $this->aml_security->check_privilege(14)  || $this->aml_security->check_privilege(77) || $this->aml_security->check_privilege(91); // can view?
        if (!$can_view) {
            $this->aml_html->error_page(array(('Отсутствуют права для данного действия.')));
        }
        $this->_operations('offline', $type);
    }

    function online_operations() {
        $can_view = $this->aml_security->check_privilege(13); // can view?
        if (!$can_view) {
            $this->aml_html->error_page(array(('Отсутствуют права для данного действия.')));
        }
        $this->_operations('online');
    }

    function kfm_log($status = 0){
        $this->aml_auth->check_auth(); // checkauth

        $can_edit = $this->aml_security->check_privilege(20) || $this->aml_security->check_privilege(21) || $this->aml_security->check_privilege(91);
        if (!$can_edit) {
            $this->aml_html->error_page(array(('Отсутствуют права для данного действия.')));
        }

        $dataurl     = site_url('page/datasource/kfm_log');
        $fullviewurl = site_url('kfm/log');

        $uisettings = $this->native_session->userdata('ui.kfm_log');
        if ($status > 0){
            $uisettings['status'] = $status;
            $this->native_session->set_userdata('ui.kfm_log', $uisettings);
        }

        if ( isset($uisettings['per_page']) && intval($uisettings['per_page']) > 0) {
            $per_page = intval($uisettings['per_page']);
        } else {
            $per_page = $this->per_page;
        }

        $select = $this->aml_context->html_get_statuses('kfm_log');

        $q = <<<ENDL
select count(decode(order_field, 1,1, null)) status_1_cnt,
       count(decode(order_field, 2,1, null)) status_2_cnt,
       count(decode(order_field, 3,1, null)) status_3_cnt,
       count(decode(order_field, 4,1, null)) status_4_cnt,
       count(decode(order_field, 5,1, null)) status_5_cnt,
       count(decode(order_field, 6,1, null)) status_6_cnt,
	   count(decode(order_field, 11,1, null)) status_11_cnt,
	   count(decode(order_field, 12,1, null)) status_12_cnt
from vw_kfm_journal t
where 1 = 1
ENDL;

        $bindings = array();
        $expressions = array();

        if (preg_match($this->config->item('regexp_date'), $uisettings['date_from'])) {
            $bindings[':dt_from'] = $uisettings['date_from'];
            $q .= " and t.P_SENDDATE > TO_DATE(:dt_from,'" . $this->config->item('date_format') . "') ";
        }
        if (preg_match($this->config->item('regexp_date'), $uisettings['date_until'])) {
            $bindings[':dt_until'] = $uisettings['date_until'];
            $q .= " and t.P_SENDDATE < TO_DATE(:dt_until,'" . $this->config->item('date_format') . "') + 1 ";
        }

        $where_str = " and (t.P_OPERATIONSTATUS = -1";
        if ($this->aml_security->check_privilege(21)){
            $where_str .= " or t.P_OPERATIONSTATUS = 2"; // подозрит
        }
        if ($this->aml_security->check_privilege(20)) {
            $where_str .= " or t.P_OPERATIONSTATUS = 1"; // подл. фм
        }
		if ($this->aml_security->check_privilege(91)) {
            $where_str .= " or t.P_OPERATIONSTATUS != -1"; // подл. фм
        }
        $where_str .= ")";
        $q .= $where_str;
		
		//die(var_dump($q));

        $stid = $this->aml_oracle->execute($q,__LINE__,$bindings);
        $vars['ops_count'] = oci_fetch_array($stid, OCI_ASSOC);

        $varsjs = array(
            'dataurl'        => $dataurl,
            'per_page'       => $per_page,
            'fullviewurl'    => $fullviewurl,
            'savecolpropurl' => $this->savecolpropurl,
            'select'         => $select,
            'uisettings'     => $uisettings,
            'savesettingsurl'=> $this->savesettingsurl,
            'status'         => $status,
            'what'         => ""
        );
        $vars['uisettings'] = $uisettings;
        $vars['page_name'] = ('Журнал обмена с КФМ');
        $this->aml_context->set_general_vars($vars);
        $vars['run_js'] = $this->load->view('kfm/js-kfmlog', $varsjs, true);
        $vars['content'] = $this->load->view('kfm/kfmlog', $vars, true);
        $this->load->view('main', $vars);
    }

//    function kfm_xml_file($direction, $id) {
//        $this->aml_auth->check_auth(); // checkauth
//        $can_edit = $this->aml_security->check_privilege(2); // edit
//        if (!$can_edit) {
//            $this->aml_html->error_page(array(('Отсутствуют права для данного действия.')));
//        }
//        $id = intval($id);
//        if ($id <= 0) {
//            $this->aml_html->error_page(array(sprintf(('Неверное значение параметра %s'), 'id')));
//        }
//
//        switch ($direction) {
//            case 'incoming':
//                $p_message_guid = 0;
//                $p_data = '';
//
//                $q = 'SELECT s.P_MESSAGE_GUID,r.P_DATA
//                      FROM tb_receive_from_kfm r, tb_send_to_kfm s
//                      WHERE r.id = :id1
//                      AND r.p_sendid = s.id';
//                $stid = $this->aml_oracle->execute($q, __LINE__, array(':id1' => $id));
//                list($p_message_guid, $p_data) = oci_fetch_array($stid,OCI_RETURN_LOBS);
//                if (!$p_message_guid) {
//                    $this->aml_html->error_page(array(sprintf(('Не найдена запись с ID: %d', $id))));
//                }
//
//                header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
//                header ("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");
//                header ("Cache-Control: no-cache, must-revalidate");
//                header ("Pragma: no-cache");
//                header ("Content-Description: PHP Generated Data" );
//                header ('Content-Disposition: attachment; filename="' . $p_message_guid .  '.xml"');
//                header ('Content-Type: text/xml; charset=utf-8');
//                print $p_data;
//                break;
//            case 'outgoing':
//                $p_message_guid = 0;
//                $p_data = '';
//
//                $q = 'SELECT S.P_MESSAGE_GUID,s.P_DATA FROM tb_send_to_kfm s WHERE s.id = :id1';
//                $stid = $this->aml_oracle->execute($q, __LINE__, array(':id1' => $id));
//                list($p_message_guid, $p_data) = oci_fetch_array($stid,OCI_RETURN_LOBS);
//                if (!$p_message_guid) {
//                    $this->aml_html->error_page(array(sprintf(('Не найдена запись с ID: %d', $id))));
//                }
//
//                header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
//                header ("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");
//                header ("Cache-Control: no-cache, must-revalidate");
//                header ("Pragma: no-cache");
//                header ("Content-Description: PHP Generated Data" );
//                header ('Content-Disposition: attachment; filename="' . $p_message_guid .  '.xml"');
//                header ('Content-Type: text/xml; charset=utf-8');
//                print $p_data;
//                break;
//            default:
//                $this->aml_html->error_page(array(sprintf(('Неверное значение параметра %s'), 'URL')));
//        }
//    }

    function _html_table($field_captions, $rows) {
        if (!is_array($rows) || !count($rows)) {
            $rows = array();
        }
        if (!is_array($field_captions)) {
            if (count($rows)) {
                $field_captions = array_keys($rows[0]);
            } else {
                $field_captions = array('no_data');
            }
        }

        return $this->load->view('std-table',
                                    array('field_captions' => $field_captions,
                                          'rows' => $rows),
                               true);
    }

    function _html_get_select($options,$attr = null, $selected = null){
        $r = '<select';
        foreach($attr as $k => $v){
            $r .= ' ' . $k . '="' . $v . '"';
        }
        $r .= ">";

        foreach ($options as $id => $value) {
            $r .= '<option value="' . $id . '"';
            if ($id == $selected) {
                $r .= ' selected="selected">';
            } else {
                $r .= '>';
            }
            $r .= ($value) . '</option>';
        }
        $r .= '</select>';
        return $r;
    }

    function _html_get_conditions($selected_id){
        $conditions = array();
        $stid = $this->aml_oracle->execute('SELECT * FROM TB_CONDITIONS t',__LINE__);
        while($r = oci_fetch_array($stid, OCI_ASSOC)) {
            $conditions[$r['ID']] = htmlentities($r['P_CONDITION_CODE'], ENT_QUOTES, 'utf-8');
        }
        $conditions_html = $this->_html_get_select($conditions, array('name' => 'P_CONDITION_ID'), $selected_id);
        return $conditions_html;
    }

	function log_operation() {
		$this->aml_auth->check_auth();
       //$this->_check_permission();
		
		/*$can_do = $this->aml_security->check_privilege(25) || $this->aml_security->check_privilege(41); // admin control
        if (!$can_do) {
            $this->aml_html->error_page(array(('Отсутствуют права для данного действия.')));
        }*/
		
        $vars = array();
         
		$vars['ui'] = "log_operation";
		
		$vars['uisettings'] = $this->native_session->userdata('ui.log_operation');
        $this->aml_context->set_general_vars($vars);
        $vars['grid'] = $this->aml_metainfo->get_js_table_properties('TB_OPERATION_LOG');
        
        $vars['page_name'] = ('Логи по загруженным операциям');
		$vars['type']="log";
        $vars['content'] = $this->load->view('log_operation/view.php',$vars, true);
		$cur_date_from =  @$_SESSION[$ui]['date_from'];
		$cur_date_until = @$_SESSION[$ui]['date_until'];
		$vars['left_col_content'] = "";
      
        $this->load->view('main', $vars);
	}
	function diag_log_operation() {
		$this->aml_auth->check_auth();
       //$this->_check_permission();
		
		/*$can_do = $this->aml_security->check_privilege(25) || $this->aml_security->check_privilege(41); // admin control
        if (!$can_do) {
            $this->aml_html->error_page(array(('Отсутствуют права для данного действия.')));
        }*/
		
        $vars = array();
         
		$vars['ui'] = "diag_log_operation";
		
		$vars['uisettings'] = $this->native_session->userdata('ui.diag_log_operation');
        $this->aml_context->set_general_vars($vars);
        $vars['grid'] = $this->aml_metainfo->get_js_table_properties('TB_OPERATION_LOG');
        
        $vars['page_name'] = ('Диагностическое сообщение');
		$vars['type']="diag";
        $vars['content'] = $this->load->view('log_operation/view.php',$vars, true);
		$cur_date_from = $_SESSION[$ui]['date_from'];
		$cur_date_until = $_SESSION[$ui]['date_until'];
		$vars['left_col_content'] = "";
      
        $this->load->view('main', $vars);
	}
	function datasource_log($type= null) {
		// вх. параметры
        $page = intval($this->input->post('page'));
		
		$count_by_status = array();
		 
        if ($page <= 0) {
            $page = 1;
        }
        $rows = intval($this->input->post('rows'));
		
        if ($rows <= 0) {
            $rows = $this->per_page;
        }
        // рассчитанные переменные
        $start  = ($page - 1) * $rows + 1;
        $end    = $page * $rows + 1;
        $data   = array();
        $w1     = '';
        $w2     = '';
        $wvalue = '';
        
        $extra_where =$extra_where_cnt = array('1 = 1');
        $bindings = array();
		
		$fields_info   = $this->aml_metainfo->get_table_info('TB_OPERATION_LOG', 0, 0);
        $order_by      = $this->aml_grid->get_sorting_clause($fields_info);
        $where         = $this->aml_grid->get_where_clause_multi('TB_OPERATION_LOG');

       

		switch($type)
		{
			case 'log':
        $uisettings = $this->native_session->userdata('ui.log_operation');

        //todo optimize sql using bind variables
        if (!empty($uisettings['date_from'])) {
            $extra_where[] = "t.P_DATE >= TO_DATE('" . $uisettings['date_from'] . "','" . $this->config->item('date_format') . "') ";
            $extra_where_cnt[] = "t.P_DATE >= TO_DATE('" . $uisettings['date_from'] . "','" . $this->config->item('date_format') . "') ";
        }
        if (!empty($uisettings['date_until'])) {
            $extra_where[] = "t.P_DATE < TO_DATE('" . $uisettings['date_until'] . "','" . $this->config->item('date_format') . "') + 1";
            $extra_where_cnt[] = "t.P_DATE < TO_DATE('" . $uisettings['date_until'] . "','" . $this->config->item('date_format') . "') + 1";
        }
          //die(var_dump($extra_where));
        if ($where != null) {
            $w1 = 'WHERE ' . $where[0] . ' AND ' . implode (" AND ",$extra_where);
            $wc1 = 'AND ' . $where[0] . ' AND ' . implode (" AND ",$extra_where_cnt);
            $w2 = 'AND ' . $where[0] . ' AND ' . implode (" AND ",$extra_where);
            $wvalue = $where[1];
        } else {
            $w1 = 'WHERE ' . implode (" AND ",$extra_where);
            $wc1 = 'AND ' . implode (" AND ",$extra_where_cnt);
            $w2 = 'AND ' . implode (" AND ",$extra_where);
        }
        // кол-во записей в табле
        $cnt = 0;
        $bindings = array('CNT' => &$cnt);
        if ($wvalue) {
            foreach($wvalue as $k => $w) {
                $bindings[$k] = $w;
            }
        }
        $q = "SELECT COUNT(*) cnt FROM TB_OPERATION_LOG t where t.p_error like '%Общее количество%'".$wc1;
		//die(var_dump($q));
        $stid = $this->aml_oracle->execute($q, __LINE__, $bindings);
        oci_fetch($stid);
        oci_free_statement($stid);
		
        $total_pages = ceil($cnt / $rows);
		
       
        // выборка страницы
        $bindings = array(':v1' => $start,':v2' => $end);
        $bindings2 = array();
        if ($wvalue) {
            foreach($wvalue as $k => $w) {
                $bindings[$k] = $w;
                $bindings2[$k] = $w;
            }
        }
		//die(var_dump($bindings));
		list($select_fields, $joins) = $this->aml_metainfo->get_joins($fields_info, 0);
        
         $q = "SELECT * FROM ( SELECT " . implode(',', $select_fields) . ",rownum rn FROM TB_OPERATION_LOG t WHERE rownum < :v2  and t.p_error like '%Общее количество%'" . $w2 . ") WHERE rn >= :v1 ".$order_by;

          //die(var_dump($q));        
		break;
		 case 'diag':
		 $uisettings = $this->native_session->userdata('ui.diag_log_operation');

        //todo optimize sql using bind variables
        if (!empty($uisettings['date_from'])) {
            $extra_where[] = "t.P_DATE >= TO_DATE('" . $uisettings['date_from'] . "','" . $this->config->item('date_format') . "') ";
            $extra_where_cnt[] = "t.P_DATE >= TO_DATE('" . $uisettings['date_from'] . "','" . $this->config->item('date_format') . "') ";
        }
        if (!empty($uisettings['date_until'])) {
            $extra_where[] = "t.P_DATE < TO_DATE('" . $uisettings['date_until'] . "','" . $this->config->item('date_format') . "') + 1";
            $extra_where_cnt[] = "t.P_DATE < TO_DATE('" . $uisettings['date_until'] . "','" . $this->config->item('date_format') . "') + 1";
        }
             //die(var_dump(  $extra_where));
        if ($where != null) {
            $w1 = 'WHERE ' . $where[0] . ' AND ' . implode (" AND ",$extra_where);
            $wc1 = 'AND ' . $where[0] . ' AND ' . implode (" AND ",$extra_where_cnt);
            $w2 = 'AND ' . $where[0] . ' AND ' . implode (" AND ",$extra_where);
            $wvalue = $where[1];
        } else {
            $w1 = 'WHERE ' . implode (" AND ",$extra_where);
            $wc1 = 'AND ' . implode (" AND ",$extra_where_cnt);
            $w2 = 'AND ' . implode (" AND ",$extra_where);
        }
          //die(var_dump($w1));
        // кол-во записей в табле
        $cnt = 0;
        $bindings = array('CNT' => &$cnt);
        if ($wvalue) {
            foreach($wvalue as $k => $w) {
                $bindings[$k] = $w;
            }
        }
        
        $q = "SELECT COUNT(*) cnt FROM TB_OPERATION_LOG t where t.p_error not  like '%Общее количество%' ".$wc1;
		
        $stid = $this->aml_oracle->execute($q, __LINE__, $bindings);
        oci_fetch($stid);
        oci_free_statement($stid);
		
        $total_pages = ceil($cnt / $rows);
		
       
        // выборка страницы
        $bindings = array(':v1' => $start,':v2' => $end);
        $bindings2 = array();
        if ($wvalue) {
            foreach($wvalue as $k => $w) {
                $bindings[$k] = $w;
                $bindings2[$k] = $w;
            }
        }
		list($select_fields, $joins) = $this->aml_metainfo->get_joins($fields_info);
			$q = "SELECT * FROM (  select " .$this->aml_metainfo->get_fieldslist_csv($fields_info, 't') . ",rownum rn from TB_OPERATION_LOG t where rownum < :v2  and t.p_error not like '%Общее количество%'" . $w2 .") WHERE rn >= :v1 ".$order_by;
        //die(var_dump($q));
		break;
		}
	   //$q = "SELECT * FROM (SELECT " . $this->aml_metainfo->get_fieldslist_csv($fields_info, 't') . ",rownum rn FROM TB_OPERATION_LOG t WHERE rownum < :v2 " . $w2 . " ORDER BY ID) WHERE rn >= :v1 " . $order_by;
        //die(var_dump($q));
		//die(var_dump($bindings));
		$stid = $this->aml_oracle->execute($q,__LINE__,$bindings);
        $nrows = oci_fetch_all($stid, $results);
        oci_free_statement($stid);

       


        header("Content-type: text/xml;charset=utf-8");
        print $this->aml_grid->output_xml($page, $total_pages, $cnt, $results, $fields_info, $count_by_status);
		
    }
    function _operations($what = 'online', $type=0) {
		
		
        $this->aml_auth->check_auth(); // checkauth
        $double_click_js_handler = '';
        $period = ('Период');
        $status_str = ('Статус');
        $from_str = ('Период с');
        $till_str = ('по');
        $apply_str = ('Установить');
		$toolbar_buttons = "";


        switch($what) {
            case 'online':
                $can_do = $this->aml_security->check_privilege(13);
                if (!$can_do) {
                    $this->aml_html->error_page(array(('Отсутствуют права для данного действия.')));
                }

                $tablename = 'TB_ONLINEOPERATIONS';
                $uisettings = $this->native_session->userdata('ui.online');
                $vars['uisettings'] = $uisettings;
                if (intval($uisettings['per_page']) > 0) {
                    $per_page = intval($uisettings['per_page']);
                } else {
                    $per_page = $this->per_page;
                }

                $statuses = $this->aml_context->html_get_statuses('online','status',true);
                $select = "<select id='status' onchange='set_search_status();' style='margin-right:10px;'>";
                foreach($statuses as $status_key => $status_name){
                	$select .= "<option value='".$status_key."'".($status_key==$uisettings['status']?" selected='selected'":"").">".$status_name."</option>";
                }
                $select .= "</select>";

				$toolbar_buttons .= <<<ENDLEXTRABTNS
				jQuery('#t_grid1').append("<div style='margin-top:1px;margin-left:10px;'>{$status_str}: {$select}<input type='button' value='{$period}: {$uisettings['date_from']} - {$uisettings['date_until']}' onclick='select_period();' id='select_period'></div>");
ENDLEXTRABTNS;

                $double_click_js_handler = <<<ENDLJSH
                    ondblClickRow: function (rowid, iRow, iCol, e) {
                        wnd = window.open(base_url + '/page/edit/online/' + rowid);
                        wnd.focus();
                    },
ENDLJSH;

                $vars['active_link'] = 'online';
                $grid_caption = $vars['page_name'] = ('Онлайн операции');
                break;
            case 'offline':
                $can_do = $this->aml_security->check_privilege(14) || $this->aml_security->check_privilege(77)  || $this->aml_security->check_privilege(91);
                if (!$can_do) {
                    $this->aml_html->error_page(array(('Отсутствуют права для данного действия.')));
                }

                $tablename = 'TB_OFFLINEOPERATIONS';
                $uisettings = $this->native_session->userdata('ui.offline');
				
				
				
                $vars['uisettings'] = $uisettings;
                if (isset($uisettings['per_page']) && intval($uisettings['per_page']) > 0) {
                    $per_page = intval($uisettings['per_page']);
                } else {
                    $per_page = $this->per_page;
                }

                $fi = array(
                    'P_CODE' => array('SUSPIC_TYPE_ID','CODE_ID','REASON_ID','SUSPICIOUS_ID','SUSPICIOUS_ID','SUSPICIOUS_ID','DETECTED_BY_FILIAL'),
                    'P_DIRECTORY_OBJECT' => array('TB_DICT_SUSPIC_TYPE','TB_DICT_OPERCODE','TB_DICT_REASON','TB_DICT_SUSPICIOUS','TB_DICT_SUSPICIOUS','TB_DICT_SUSPICIOUS','TB_DICT_YES_NO'),
                    'DATA_TYPE' => array('NUMBER','NUMBER','NUMBER','NUMBER','NUMBER','NUMBER','NUMBER'),
                    'COLUMN_NAME' => array('SUSPIC_TYPE_ID','CODE_ID','REASON_ID','SUSPICIOUS_ID','SUSPICIOUS_ID','SUSPICIOUS_ID','DETECTED_BY_FILIAL'),
                    'P_FIELD_CAPTION' => array('Основание для подачи сообщения','Код вида операции','Категория подозрительности','1-й Код признака подозрительности','2-й Код признака подозрительности','3-й Код признака подозрительности','Выявленно филиалом'),
                    'P_EDITABLE_BOOL' => array(1,1,1,1,1,1,1)
                );


                $susp_where = array();
                $susp_oper_privileges = array(21, 15, 17, 12); // suspicious operations roles
                $fm_oper_privileges = array(20, 19, 16, 11);   // financial monitoring operations roles
                if ($this->aml_security->check_privileges_with_or($susp_oper_privileges)) {
                    $susp_where[] = " P_CODE = '2' ";
                } if ($this->aml_security->check_privileges_with_or($susp_oper_privileges)) {
                    $susp_where[] = " P_CODE = '3' ";
                } if ($this->aml_security->check_privileges_with_or($susp_oper_privileges)) {
                    $susp_where[] = " P_CODE = '4' ";
                } if ($this->aml_security->check_privileges_with_or($susp_oper_privileges)) {
                    $susp_where[] = " P_CODE = '5' ";
                } if ($this->aml_security->check_privileges_with_or($susp_oper_privileges)) {
                    $susp_where[] = " P_CODE = '6' ";
                } if ($this->aml_security->check_privileges_with_or($susp_oper_privileges)) {
                    $susp_where[] = " P_CODE = '7' ";
                }
                if ($this->aml_security->check_privileges_with_or($fm_oper_privileges)) {
                    $susp_where[] = " P_CODE = '1' ";
                }
                $susp_where_sql = implode(' OR ', $susp_where);

                $vars['tb_dict_opercode_select'] = $this->aml_html->create_control('TB_DICT_SUSPIC_TYPE', $fi, 0, '', 1, $susp_where_sql)
												 . $this->aml_html->create_control('TB_DICT_YES_NO', $fi, 6, '', 1, '')
                								 . $this->aml_html->create_control('TB_DICT_OPERCODE', $fi, 1, '', 1, '')
                								 . $this->aml_html->create_control('TB_DICT_REASON', $fi, 2, '', 1, '')
                								 . $this->aml_html->create_control('TB_DICT_SUSPICIOUS', $fi, 3, '', 1, '')
                								 . $this->aml_html->create_control('TB_DICT_SUSPICIOUS', $fi, 4, '', 2, '')
                								 . $this->aml_html->create_control('TB_DICT_SUSPICIOUS', $fi, 5, '', 3, '');
                								 
                $vars['active_link'] = 'offline';
                $grid_caption = $vars['page_name'] = ('Проведенные операции');
                $fullviewurl = site_url('page/edit/offline');
                $double_click_js_handler = <<<ENDLJSH
                    ondblClickRow: function (rowid, iRow, iCol, e) {
                        var newWindow = window.open('{$fullviewurl}/' + rowid, '_blank');
                        newWindow.focus();
                    },
ENDLJSH;

                if($type==0){
	                if ($this->aml_security->check_privilege(11) || $this->aml_security->check_privilege(12)){
	                    $send_to_susp_btn = "<input type='button' id='btn_offline_to_susp' value='" . ('В ФМ/Подозрительные') . "'>";
	                } else {
	                    $send_to_susp_btn = '';
	                }
				} else {
					$vars['hide_header'] = true;
					$send_to_susp_btn = "<input type='button' value='".('Добавить в историю операций')."' onclick='add_to_history(".$type.");'>";
				}
                $toolbar_buttons .= <<<ENDLEXTRABTNS
                jQuery('#t_grid1').append("<div style='margin-top:1px'>{$send_to_susp_btn}   <input type='button' value='{$period}: {$uisettings['date_from']} - {$uisettings['date_until']}' onclick='select_period();' id='select_period'></div>");
ENDLEXTRABTNS;
                break;
            default:
                die('invalid parameter');
        }
        $vars['content'] = $this->load->view('online-operations', $vars, true);

        $dataurl         = site_url('page/datasource/' . $what);   // !!! строка не должна содержать символы " или '
        $editurl         = site_url('page/editdata/' . $what);     // !!! строка не должна содержать символы " или '
        $datahisturl     = site_url('page/datasource/onlinehist'); // !!! строка не должна содержать символы " или '
		
		$deleteurl = "";
		$addurl = "";

        list($jqgrid_titles1, $jqgrid_models1) = $this->aml_metainfo->get_js_table_properties($tablename, 0, 0);
        $varsjs = array(
            'what'                    => $what,
            'dataurl'                 => $dataurl,
            'deleteurl'               => $deleteurl,
            'addurl'                 => $addurl,
            'dataurl'                 => $dataurl,
            'editurl'                 => $editurl,
            'jqgrid_titles1'          => $jqgrid_titles1,
            'jqgrid_models1'          => $jqgrid_models1,
            'per_page'                => $per_page,
            'grid_caption'            => $grid_caption,
            'double_click_js_handler' => $double_click_js_handler,
            'datahisturl'             => $datahisturl,
            'savecolpropurl'          => $this->savecolpropurl,
            'toolbar_buttons'         => $toolbar_buttons,
            'savesettingsurl'         => $this->savesettingsurl,
            'search_cols'         => "",
            'grid_extra_options'      => "multiselect: true, /* - для грида мониторинга*/ multiboxonly: true,"
        );
        $vars['run_js'] = $this->load->view('js-operations.php', $varsjs, true);
        $this->aml_context->set_general_vars($vars);
        $this->load->view('main', $vars);
    }

    // вкл/выкл активнсть сценария
    // входные данные должны быть отфильтрованы
    function _toggle_scenario_activity($scenario_id, $type, $to_status, $reason) {
        $p_iscompiled = 999999;
        $q = 'SELECT P_ISCOMPILED FROM ' . $this->db_schema_prefix . 'TB_SCENARIOS t WHERE id = :aml_id';
        $stid = $this->aml_oracle->execute($q, __LINE__, array(':aml_id' => $scenario_id,'P_ISCOMPILED' => &$p_iscompiled));
        oci_fetch($stid);

        if ($p_iscompiled != 1) {
            return false;
        }

        $bindings = array(':active_bool' => $to_status,':aml_id' => $scenario_id);
        $binding_types = array(':active_bool' => 'NUMBER',':aml_id' => 'NUMBER');

        if($to_status==1){
        	$activation = ('Активация ');
        } else {
        	$activation = ('Деактивация ');
        }

		$type_text = array(
			'P_ISACTIVE' => ('сценария'),
			'P_FOR_ON' => ('проверки online'),
			'P_FOR_OFF' => ('проверки offline')
		);

        // -------------------------------------------------------------------------------------------------------------
        $q = 'UPDATE ' . $this->db_schema_prefix . 'TB_SCENARIOS t SET t.'.$type.' = :active_bool WHERE id = :aml_id AND t.P_ISCOMPILED = 1';
        //$this->aml_admcontrol->execute($q, __LINE__, $bindings, $binding_types,'SCENARIOS', $activation.$type_text[$type], $reason);
        $stid = @$this->aml_oracle->execute($q, __LINE__, $bindings,false);
        return true;
    }

    function managebranches($what = null){
        $this->aml_auth->check_auth();
        $can_admin_users = $this->aml_security->check_privilege(24); // ADMIN USERS
        if (!$can_admin_users) {
            $this->aml_html->error_page(array(('Отсутствуют права для данного действия.')));
        }
		
		$rows = array();

        switch($what) {
            case 'add':
                $newid = -9999999;
                $this->aml_oracle->execute_size("BEGIN :newid := GetID(); INSERT INTO TB_BRANCH(ID, P_ORGNAME) VALUES(:newid,'Новый филиал'); END;",__LINE__,array(':newid' => &$newid));
                header('Location: ' . site_url('page/edit/branch/' . $newid));
                die();
                break;
            default:
                $stid = $this->aml_oracle->execute('SELECT * FROM TB_BRANCH t ORDER BY t.P_ORGNAME', __LINE__);
                while ($r = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
                    $r['EDIT_LINK'] = '<a target="_blank" href="' . site_url('page/edit/branch/' . $r['ID']) . '">' . $this->aml_html->img('edit.png') . '</a>';
                    $r['DEL_LINK']  = '<a class="del_branch_button" href="' . site_url('page/deleteitem/branch/' . $r['ID']) . '">' . $this->aml_html->img('trash.png') . '</a>';
                    $rows[] = $r;
                }

                $vars['content'] = $this->aml_html->button_link(array('link' => 'page/managebranches/add', 'link_text' => ('Добавить филиал'), 'attr' => array('class' => 'button-link','target' => '_blank')));
                $vars['content'] .= '<div style="margin-top:10px">' .
                        $this->_html_table(array(
                            ('ID'),
                            ('№ филиала'),
                            ('Организация'),
                            ('Область'),
                            ('Орг. код'),
                            ('Район'),
                            ('Номер дома'),
                            ('Номер офиса'),
                            ('Почтовый индекс'),
                            ('Город'),
                            ('Улица'),
                            ('Email'),
                            ('Телефон'),
                            ('РНН'),
                            ('БИН'),
                            ('Код отправителя'),
                            ('Код получателя'),
                            ('Имя контейнера'),
                            ('Ред.'),
                            ('Уд.')
                        ),
                        $rows) . '</div>';
        }

        $this->aml_context->set_general_vars($vars);
        $this->load->view('main', $vars);
    }

    // страница управления интерфейсом
    function managegui($what = null){
        $this->aml_auth->check_auth();
        //$this->_get_connection();
        $vars['active_link'] = 'managegui';
        $vars['page_name'] = ('Настройка интерфейса');

        $can_admin_users = $this->aml_security->check_privilege(24); // ADMIN USERS
        if (!$can_admin_users) {
            $this->aml_html->error_page(array(('Отсутствуют права для данного действия.')));
        }

        $stid = $this->aml_oracle->execute("SELECT DISTINCT utc.table_name,utc.comments
                                     FROM aml_user.xxx_tab_comments utc, TB_FIELDS_METAINFO t
                                     WHERE utc.table_name = t.table_name order by utc.comments",__LINE__);
        oci_fetch_all($stid, $results);
        $vars['tables']  = $results;

        $vars['run_js'] = $this->load->view('js-managegui',array(), true);

		$q = "SELECT t.*, g.P_ORDER FROM TB_FIELDS_METAINFO t left join TB_GROUPS g on g.ID=t.P_GROUPS WHERE t.table_name = :tbl ORDER BY g.P_ORDER, t.p_order_number";
        $stid = $this->aml_oracle->execute($q, __LINE__,  array(':tbl' => $what));
        oci_fetch_all($stid, $results_table);
        $vars['table_info'] = $results_table;

		// список групп
        $stid = $this->aml_oracle->execute("select * from TB_GROUPS order by P_ORDER",__LINE__);
        oci_fetch_all($stid, $results);
        $vars['groups_list'] = $results;

        // список справочников
        $stid = $this->aml_oracle->execute("select ut.table_name,utc.comments
                                       from aml_user.xxx_tab_comments utc,  aml_user.xxx_tables  ut
                                      where utc.table_name = ut.TABLE_NAME
                                        and  ut.table_name like 'TB_DICT%' ",__LINE__);
        oci_fetch_all($stid, $results_dirs);
        $vars['directories_list'] = $results_dirs;
        $vars['what'] = $what;

        $vars['content'] = $this->load->view('manage-gui', $vars, true);
        $this->aml_context->set_general_vars($vars);
        $this->load->view('main', $vars);
    }

    function managepatterns($what = null, $p2 = null, $p3 = null) {
        $this->aml_auth->check_auth();
        $vars['active_link'] = 'managescenarios';
        $vars['page_name'] = ('Подозрительные схемы');

        $can_manage_scenarios = $this->aml_security->check_privilege(23); // ADMIN REPORTS
        if (!$can_manage_scenarios) {
            $this->aml_html->error_page(array(('Отсутствуют права для данного действия.')));
        }

        switch($what) {
            case 'delresult':
                $id = intval($p2);
                if ($id <= 0) {
                    $this->aml_html->error_page(array(sprintf(('Неверное значение параметра %s'), 'id')));
                }
                $redirect_id = intval($p3);
                if ($redirect_id <= 0) {
                    $this->aml_html->error_page(array(sprintf(('Неверное значение параметра %s'), 'redirect_id')));
                }
                $q = "DELETE FROM TB_SUSP_OPERATIONS_GROUP t WHERE t.ID = :id";
                $stid = $this->aml_oracle->execute($q, __LINE__,array(':id' => $id));
                header('Location:' . site_url('page/managepatterns/view/' . $redirect_id));
                die();

                break;
            case 'addcondition':
                $level_id = intval($p2);
                if ($level_id <= 0) {
                    $this->aml_html->error_page(array(sprintf(('Неверное значение параметра %s'), 'level_id')));
                }

                $stid = $this->aml_oracle->execute("SELECT p_type FROM TB_PATTERNS WHERE id = (select p_pattern_id from tb_pattern_levels where id = :id)",__LINE__, array(':id' => $level_id));
                list($vars['p_type']) = oci_fetch_array($stid);

                if(in_array($vars['p_type'],array(1,2))){
                	$query = "select nvl(p_long_name, nvl(p_field_caption, column_name)) ru_col_name, column_name from tb_fields_metainfo where table_name = 'TB_OFFLINEOPERATIONS'";
                	$stid = $this->aml_oracle->execute($query, __LINE__);
               		oci_fetch_all($stid, $vars['operation_fields']);
                	$query = "select nvl(p_long_name, nvl(p_field_caption, column_name)) ru_col_name, column_name from tb_fields_metainfo where table_name = 'TB_OFF_MEMBERS'";
                	$stid = $this->aml_oracle->execute($query, __LINE__);
                	oci_fetch_all($stid,$vars['member_fields']);
                }

                $vars['action'] = 'page/managepatterns/addcondition/' . $level_id;
                if ($this->input->post('op')) {
                    $p_expression      = $this->input->post('P_EXPRESSION');
                    $p_condition_id    = intval($this->input->post('P_CONDITION_ID'));
                    $p_condition_value = $this->input->post('P_CONDITION_VALUE');
                    $p_description     = $this->input->post('P_DESCRIPTION');
                    $p_grouping_bool   = $this->input->post('P_GROUPING_BOOL');

                    $stid = $this->aml_oracle->execute("INSERT INTO TB_PATTERN_LEVEL_CONDITIONS
                                                 (id,p_pattern_level_id,p_expression,p_condition_id,p_condition_value,p_description,p_grouping_bool)
                                                 VALUES(GetID(),:p_pattern_level_id,:p_expression, :p_condition_id,:p_condition_value,:p_description,:p_grouping_bool)",
                        __LINE__, array(':p_pattern_level_id' => $level_id, ':p_expression' => $p_expression, ':p_condition_id' => $p_condition_id,
                            ':p_condition_value' => $p_condition_value, ':p_description' => $p_description, ':p_grouping_bool' => $p_grouping_bool));
                    header('Location: ' . site_url('page/managepatterns/editlevel/' . $level_id));
                    die();
                }
                $vars['vars']['CONDITION'] = $this->_html_get_conditions(0); // 0 - не выбрана запись
                $vars['content'] = $this->load->view('pattern-level-condition-edit', $vars, true);
                break;
            case 'editcondition':
                $id = intval($p2);
                if ($id <= 0) {
                    $this->aml_html->error_page(array(sprintf(('Неверное значение параметра %s'), 'scheme_id')));
                }

                $stid = $this->aml_oracle->execute("SELECT tl.P_LEVEL,
                                                    tl.ID,
                                                    tl.P_PATTERN_ID,
                                                    tp.PATTERN_NAME,
                                                    tc.P_DESCRIPTION
                                             FROM TB_PATTERN_LEVEL_CONDITIONS tc
                                             LEFT JOIN TB_PATTERN_LEVELS tl ON (tc.p_pattern_level_id = tl.id)
                                             LEFT JOIN TB_PATTERNS tp ON (tl.p_pattern_id = tp.id)
                                             WHERE tc.ID = :id", __LINE__, array(':id' => $id));

                list($p_level,
                     $level_id,
                     $pattern_id,
                     $pattern_name,
                     $condition_description) = oci_fetch_array($stid);
                $vars['page_name'] = '<a href="' . site_url('page/managepatterns') . '">' . ('Подозрительные схемы') . '</a> &raquo; ' .
                                     '<a href="' . site_url('page/managepatterns/edit/' . $pattern_id) . '">' . $pattern_name . '</a> &raquo; ' .
                                     '<a href="' . site_url('page/managepatterns/editlevel/' . $level_id) . '">' . ('уровень') . ' ' . $p_level . '</a> &raquo; ' .
                                     ('условие:') . ' ' . $condition_description;

                $q = 'SELECT t.* FROM TB_PATTERN_LEVEL_CONDITIONS t WHERE t.id = :id';
                $stid = $this->aml_oracle->execute($q, __LINE__, array(':id' => $id));
                $vars['vars'] = oci_fetch_array($stid, OCI_ASSOC);

               	$query = "select nvl(p_long_name, nvl(p_field_caption, column_name)) ru_col_name, column_name from tb_fields_metainfo where table_name = 'TB_OFFLINEOPERATIONS'";
               	$stid = $this->aml_oracle->execute($query, __LINE__);
              		oci_fetch_all($stid, $vars['operation_fields']);
               	$query = "select nvl(p_long_name, nvl(p_field_caption, column_name)) ru_col_name, column_name from tb_fields_metainfo where table_name = 'TB_OFF_MEMBERS'";
               	$stid = $this->aml_oracle->execute($query, __LINE__);
               	oci_fetch_all($stid,$vars['member_fields']);

                $vars['action'] = 'page/managepatterns/editcondition/' . $vars['vars']['ID'];

                if ($this->input->post('op')) {
                    $p_expression = $this->input->post('P_EXPRESSION');
                    $p_condition_id = intval($this->input->post('P_CONDITION_ID'));
                    $p_condition_value = $this->input->post('P_CONDITION_VALUE');
                    $p_description = $this->input->post('P_DESCRIPTION');
                    $p_grouping_bool = $this->input->post('P_GROUPING_BOOL');

                    $bindings = array(':p_expression' => $p_expression,
                                      ':p_condition_id' => $p_condition_id,
                                      ':p_condition_value' => $p_condition_value,
                                      ':p_description' => $p_description,
                                      ':p_grouping_bool' => $p_grouping_bool,
                                      ':id' => $id);
                    $stid = $this->aml_oracle->execute("UPDATE TB_PATTERN_LEVEL_CONDITIONS t
                                                         SET    p_expression = :p_expression,
                                                                p_condition_id = :p_condition_id,
                                                                p_condition_value = :p_condition_value,
                                                                p_description = :p_description,
                                                                p_grouping_bool = :p_grouping_bool
                                                         WHERE t.id = :id", __LINE__, $bindings);
                    header('Location: ' . site_url('page/managepatterns/editlevel/' . $vars['vars']['P_PATTERN_LEVEL_ID']));
                    die();
                }


                $vars['vars']["CONDITION"] = $this->_html_get_conditions($vars['vars']['P_CONDITION_ID']);
                $vars['content'] = $this->load->view('pattern-level-condition-edit', $vars, true);
                break;
            case 'deletecondition':
                $id = intval($p2);
                if ($id <= 0) {
                    $this->aml_html->error_page(array(sprintf(('Неверное значение параметра %s'), 'level_condition_id')));
                }
                $q = "SELECT p_pattern_level_id FROM TB_PATTERN_LEVEL_CONDITIONS t WHERE t.ID = :id";
                $stid = $this->aml_oracle->execute($q, __LINE__, array(':id' => $id));
                list($level_id) = oci_fetch_array($stid);

                $q = "DELETE FROM TB_PATTERN_LEVEL_CONDITIONS t WHERE t.ID = :id";
                $stid = $this->aml_oracle->execute($q, __LINE__, array(':id' => $id));
                header('Location: ' . site_url('page/managepatterns/editlevel/' . $level_id));
                die();
                break;
            case 'addlevel':
                $scheme_id = intval($p2);
                if ($scheme_id <= 0) {
                    $this->aml_html->error_page(array(sprintf(('Неверное значение параметра %s'), 'scheme_id')));
                }
                $level_id = -9999999999;
                $p_level = 1;
                $p_conditions_function = 'AND';
                $p_description = ('Новый уровень');

                $bindings = array(
                    ':p_pattern_id' => $scheme_id,
                    ':p_level' =>  &$p_level,
                    ':p_conditions_function' => $p_conditions_function,
                    ':p_description' => $p_description,
                    ':level_id' => &$level_id
                );
                $stid = $this->aml_oracle->execute("
                BEGIN
                :level_id := GetID();
                    INSERT INTO TB_PATTERN_LEVELS(id,p_pattern_id, p_level, p_conditions_function, p_description)
                    VALUES(:level_id, :p_pattern_id, :p_level, :p_conditions_function, :p_description);
                END;", __LINE__, $bindings);
                header('Location: ' . site_url('page/managepatterns/editlevel/' . $level_id));
                die();

                break;
            case 'editlevel':
                $level_id = intval($p2);
                if ($level_id <= 0) {
                    $this->aml_html->error_page(array(sprintf(('Неверное значение параметра %s'), 'level_id')));
                }

                $delete_str = ('Удалить?');
                $vars['action'] = 'page/managepatterns/editlevel/' . $level_id;
                $saved = '';
                if ($this->input->post('op')) {
                    $conditions_function = $this->input->post('P_CONDITIONS_FUNCTION');
                    $pdesc               = $this->input->post('P_DESCRIPTION');
                    $p_level             = $this->input->post('P_LEVEL');
                    $stid = $this->aml_oracle->execute("UPDATE TB_PATTERN_LEVELS t
                                                         SET t.P_LEVEL = :p_level,
                                                             t.P_CONDITIONS_FUNCTION = :cf,
                                                             t.P_DESCRIPTION = :pdesc
                                                         WHERE t.ID = :id", __LINE__, array(':id' => $level_id, ':cf' => $conditions_function, ':pdesc' => $pdesc, ':p_level' => $p_level));
                    $saved = '<ul class="info-messages"><li>' . ('Изменения сохранены') . '</li></ul>';
                }

                $stid = $this->aml_oracle->execute("SELECT t.P_LEVEL, t.P_CONDITIONS_FUNCTION, t.P_DESCRIPTION, t.ID, t.P_PATTERN_ID, tp.PATTERN_NAME
                                                     FROM TB_PATTERN_LEVELS t
                                                     LEFT JOIN TB_PATTERNS tp ON (t.p_pattern_id = tp.id)
                                                     WHERE t.ID = :id", __LINE__, array(':id' => $level_id));
                list($p_level, $p_conditions_function, $p_description, $level_id, $pattern_id, $pattern_name) = oci_fetch_array($stid);
                $vars['page_name'] = '<a href="' . site_url('page/managepatterns') . '">' . ('Подозрительные схемы') . '</a> &raquo; ' .
                                     '<a href="' . site_url('page/managepatterns/edit/' . $pattern_id) . '">' . $pattern_name . '</a> &raquo; ' .
                                     ('уровень') . ' ' . $p_level;

                $stid = $this->aml_oracle->execute("SELECT t.*,tc.p_condition_code
                                             FROM TB_PATTERN_LEVEL_CONDITIONS t
                                             LEFT JOIN TB_CONDITIONS tc ON (t.P_CONDITION_ID = tc.ID)
                                             WHERE t.p_pattern_level_id = :level_id",__LINE__, array(':level_id' => $level_id));

                while($r = oci_fetch_array($stid, OCI_ASSOC)) {
                    $rows[] = array(
                        'P_EXPRESSION'       => $r['P_EXPRESSION'] . ' ' . $r['P_CONDITION_CODE'] . ' ' . $r['P_CONDITION_VALUE'],
                        'P_DESCRIPTION'      => $r['P_DESCRIPTION'],
                        'P_GROUPING_BOOL'    => $r['P_GROUPING_BOOL'] == 1 ? 'для GROUP BY' : 'для WHERE',
                        'EDIT_LINK'          => array('#data' => '<a href="' . site_url('page/managepatterns/editcondition/' . $r['ID']) . '">' . $this->aml_html->img('scenario_edit.png') . '</a>', '#attributes' => array('style' => 'text-align:center')), //'page/managepatterns/editcondition/' . $r['ID'], 'link_text' => 'редактировать')),
                        'DELETE_LINK'        => array('#data' => '<a onclick="return confirm(\'' . $delete_str . '\')" href="' . site_url('page/managepatterns/deletecondition/' . $r['ID']) . '">' . $this->aml_html->img('trash.png') . '</a>', '#attributes' => array('style' => 'text-align:center'))//$this->aml_html->button_link(array('link' => 'page/managepatterns/deletecondition/' . $r['ID'], 'link_text' => 'удалить', 'attr' => array('onclick' => "return confirm('Удалить?')")))
                    );
                }
                if($p_conditions_function == 'AND') {
                    $and_selected = ' selected="selected"';
                    $or_selected = '';
                } else {
                    $and_selected = '';
                    $or_selected = ' selected="selected"';
                }

                $varsjs = array(
                    'p_level'       => $p_level,
                    'p_description' => $p_description,
                    'and_selected'  => $and_selected,
                    'or_selected'   => $or_selected,
                    'saved'         => $saved
                );

                $vars['content'] .= '<fieldset class="viewdata" style="background:transparent;width:800px;margin-top:10px;margin-left:10px"><legend>' . ('Условия') . '</legend>';
                $vars['content'] .= $this->aml_html->button_link(array('link' => 'page/managepatterns/addcondition/' . $level_id, 'link_text' => ('Добавить условие'), 'attr' => array('class' => 'button-link')));
                $vars['content'] .= $this->aml_html->br();
                $vars['content'] .=  $this->_html_table(array(('Условие'),('Описание'),('Группировка'),('Редактировать'),('Удалить')), $rows);
                $vars['content'] .= '</fieldset>';
                $vars['content'] .= $this->aml_html->br();
                $vars['content'] .= '<fieldset class="viewdata" style="background:transparent;width:800px;margin-top:10px;margin-left:10px"><legend>' . ('Параметры уровня') . '</legend>';
                $vars['content'] .= form_open('page/managepatterns/editlevel/' . $level_id);
                $vars['content'] .= $this->load->view('managepatterns-editlevel', $varsjs, true);
                $vars['content'] .= form_close();
                break;
            case 'deletelevel':
                $id = intval($p2);
                if ($id <= 0) {
                    $this->aml_html->error_page(array(sprintf(('Неверное значение параметра %s'), 'level_id')));
                }
                $stid = $this->aml_oracle->execute("SELECT P_PATTERN_ID FROM TB_PATTERN_LEVELS t WHERE t.ID = :id",__LINE__, array(':id' => $id));
                list($scheme_id) = oci_fetch_array($stid);

                $stid = $this->aml_oracle->execute("DELETE FROM TB_PATTERN_LEVELS t WHERE t.ID = :id",__LINE__,array(':id' => $id));
                header('Location: ' . site_url('page/managepatterns/edit/' . $scheme_id));
                die();
                break;
            case 'add':
                $vars['action'] = site_url('page/managepatterns/add');
                $pattern_name = $this->input->post('PATTERN_NAME');
                $p_type       = intval($this->input->post('P_TYPE'));
                if ($p_type != 1 && $p_type != 2) {
                    $p_type = 1;
                }
                if ($this->input->post('op')) {
                    $stid = $this->aml_oracle->execute("INSERT INTO TB_PATTERNS(id, pattern_name, p_type) VALUES(GetID(), :pattern_name, :p_type)", __LINE__, array(':pattern_name' => $pattern_name,':p_type' => $p_type));
                    header('Location: ' . site_url('page/managepatterns'));
                    die();
                }
                $vars['vars']['PATTERN_NAME'] = ('Название схемы');
                $vars['content'] = '<fieldset class="viewdata" style="background:transparent;width:550px;margin-top:10px;margin-left:10px"><legend>' .('Добавление схемы') . '</legend>';
                $vars['content'] .= $this->load->view('pattern-add', $vars, true);
                $vars['content'] .= '</fieldset>';
                break;
            case 'edit':
                $scheme_id = intval($p2);
                if ($scheme_id <= 0) {
                    $this->aml_html->error_page(array(sprintf(('Неверное значение параметра %s'), 'scheme_id')));
                }

                $saved = '';
                if ($this->input->post('op')) {
                    $pname = $this->input->post('PATTERN_NAME');
                    $bindings = array(':pname' => $pname, ':id' => $scheme_id);
                    $stid = $this->aml_oracle->execute("UPDATE TB_PATTERNS t SET t.pattern_name = :pname WHERE t.id = :id",__LINE__, $bindings);
                    $saved = '<ul class="info-messages"><li>' . ('Изменения сохранены') . '</li></ul>';
                }

                $stid = $this->aml_oracle->execute("SELECT t.pattern_name,t.p_type FROM TB_PATTERNS t WHERE t.ID = :id", __LINE__, array(':id' => $scheme_id));
                list($pattern_name, $p_type) = oci_fetch_array($stid);
                $vars['page_name'] = '<a href="' . site_url('page/managepatterns') . '">' . ('Подозрительные схемы') . '</a> &raquo; ' . $pattern_name;

                $stid = $this->aml_oracle->execute("SELECT * FROM TB_PATTERN_LEVELS t WHERE t.P_PATTERN_ID = :id ORDER BY p_level", __LINE__, array(':id' => $scheme_id));

                $levels_cnt = 0;
                while($r = oci_fetch_array($stid, OCI_ASSOC)) {
                    $rows[] = array(
                        'P_LEVEL'          => $r['P_LEVEL'],
                        'P_DESCRIPTION'    => $r['P_DESCRIPTION'],
                        'P_EDIT_LINK'      => array('#data' => '<a href="' . site_url('page/managepatterns/editlevel/' . $r['ID']) . '">' . $this->aml_html->img('scenario_edit.png') . '</a>',
                                                    '#attributes' => array('style' => 'text-align:center')),
                        'P_DEL_LINK'       => array('#data' => '<a href="' . site_url('page/managepatterns/deletelevel/' . $r['ID']) . '">' . $this->aml_html->img('trash.png') . '</a>',
                                                    '#attributes' => array('style' => 'text-align:center'))
                    );
                    $levels_cnt++;
                }
                $vars['content'] .= '<fieldset class="viewdata" style="background:transparent;width:550px;margin-top:10px;margin-left:10px"><legend>' .('Уровни') . '</legend>';


                if ($levels_cnt < 1 || $p_type == 1) {
                    $vars['content'] .= $this->aml_html->button_link(array('link' => 'page/managepatterns/addlevel/' . $scheme_id, 'link_text' => ('добавить уровень'), 'attr' => array('class' => 'button-link')));
                    $vars['content'] .= $this->aml_html->br();
                } else {
                    $vars['content'] .= ('Для схемы-графа можно добавлять не более 1 уровня');
                    $vars['content'] .= $this->aml_html->br();
                    $vars['content'] .= $this->aml_html->br();
                }
                $vars['content'] .= $this->_html_table(array(('Уровень'), ('Комментарий'), ('Редактировать'), ('Удалить')), $rows);
                $vars['content'] .= '</fieldset>';
                $vars['content'] .= $this->aml_html->br();
                $vars['content'] .= '<fieldset class="viewdata" style="background:transparent;width:550px;margin-top:10px;margin-left:10px"><legend>' .('Параметры схемы') . '</legend>';
                $vars['content'] .= form_open('page/managepatterns/edit/' . $scheme_id);

                $varsjs = array(
                    'pattern_name'   => $pattern_name,
                    'saved'          => $saved
                );
                $vars['content'] .= $this->load->view('managepatterns-edit', $varsjs, true);
                $vars['content'] .= form_close();
                break;
            case 'delete':
                $id = intval($p2);
                if ($id <= 0) {
                    $this->aml_html->error_page(array(sprintf(('Неверное значение параметра %s'), 'scheme_id')));
                }
                $stid = $this->aml_oracle->execute("DELETE FROM TB_PATTERNS t WHERE t.ID = :id",__LINE__, array(':id' => $id));
                header('Location: ' . site_url('page/managepatterns'));
                die();
                break;
            case 'results':
                $id = intval($p2);
                if ($id <= 0) {
                    $this->aml_html->error_page(array(sprintf(('Неверное значение параметра %s'), 'scheme_id')));
                }

                $sql = <<< ENDL
SELECT level, t.*,
CASE WHEN EXISTS(SELECT 1
FROM TB_SUSP_OPERATIONS_TREE t0
WHERE t0.parent_id = t.id) THEN 1 ELSE 0 END children
FROM TB_SUSP_OPERATIONS_TREE  t
WHERE t.P_GROUP_ID = :group_id
CONNECT BY PRIOR id = parent_id
START WITH parent_id IS NULL
ENDL;

                $stid = $this->aml_oracle->execute($sql,__LINE__,array(':group_id' => $id));
                $rows = array();
                while ($r = oci_fetch_array($stid)) {
                    $rows[] = $r;
                }

                $content = '<ul>' . $this->aml_html->build_tree($rows,null) . '</ul>';

                $vars['content'] = '<div id="managepatterns-view-tree">' . $content ."</div>";
                $vars['run_js'] = <<<ENDL
        \$(document).ready(function () {
            \$('#managepatterns-view-tree').jstree({
                "plugins" : [ "themes", "html_data" ],
                "core" : {
                    "animation" : 0
                }
            });
            \$('#managepatterns-view-tree').jstree("open_all");

        });
ENDL;

                break;
            case 'graphdata':
                $account = $p2;
                if (!preg_match('#[0-9a-z]+#i', $account)) {
                    $this->aml_html->error_page(array(sprintf(('Неверное значение параметра %s'), '"' . ('номер счета') . '"')));
                }

                $id = intval($p3);
                if ($id <= 0) {
                    $this->aml_html->error_page(array(sprintf(('Неверное значение параметра %s'), 'scheme_id')));
                }

                $sql = "SELECT level, t.*
                        FROM TB_SUSP_OPERATIONS_TREE  t
                        WHERE t.P_GROUP_ID = :group_id
                        CONNECT BY PRIOR id = parent_id
                        START WITH parent_id IS NULL AND p_debit_acc = :debit_acc
                        ORDER BY p_group_id,id";

                $stid = $this->aml_oracle->execute($sql,__LINE__, array(':group_id' => $id, ':debit_acc' => $account));
                $level = 0;
                $rows = array();
                while ($r = oci_fetch_array($stid)) {
                    $r['P_DEBIT_ORG'] = preg_replace('# {2,}#',' ',str_replace("\n",'',str_replace(' ',' ',str_replace('"',' ',str_replace('&','&amp;',$r['P_DEBIT_ORG'])))));
                    $r['P_CREDIT_ORG'] = preg_replace('# {2,}#',' ',str_replace("\n",'',str_replace(' ',' ',str_replace('"',' ',str_replace('&','&amp;',$r['P_CREDIT_ORG'])))));
                    $r['P_SUM'] = $this->aml_html->nf($r['P_SUM']);
                    $rows[] = $r;
                }

                $nodes = '';
                $edges = '';
                $started = false;
                $z = 0;

                $nodes .= <<< ENDL
                    <Node id="1" name="{$rows[0]['P_DEBIT_ORG']} {$rows[0]['P_DEBIT_ACC']}" desc="" nodeColor="0xAA0000" nodeSize="12" nodeClass="earth" nodeIcon="center" x="10" y="10" />
ENDL;
                $sum_str = ('сумма');
                $op_quantity_str = ('кол-во операций');
                foreach($rows as $i) {
                    /*if($started && intval($i['PARENT_ID']) == 0) {
                        break;
                    }*/
                    if(!$started && intval($i['PARENT_ID']) == 0) {
                        $started = true;
                    }

                    if (empty($i['PARENT_ID'])) {
                        $i['PARENT_ID'] = 1;
                    }


                    $nodes .= <<< END

<Node id="{$i['ID']}" name="{$i['P_CREDIT_ORG']} {$i['P_CREDIT_ACC']}, {$sum_str}: {$i['P_SUM']}KZT, {$op_quantity_str}: {$i['P_OPERATIONS_COUNT']}" desc="" nodeColor="0x333333" nodeSize="12" nodeClass="leaf" nodeIcon="center" x="10" y="10" />
END;


                    $edges .= <<<END

<Edge fromID="{$i['PARENT_ID']}" toID="{$i['ID']}" edgeLabel="Good" flow="10" edgeClass="rain" edgeIcon="Good" />
END;



                }
                header('Content-type: application/xml;charset=utf-8');
                print <<< ENDL
<Graph>
       {$nodes}
       {$edges}
</Graph>
ENDL;
                die();
                break;
            case 'map':
                $account = $p2;
                if (!preg_match('#^[a-z0-9]+$#i', $account)) {
                    $this->aml_html->error_page(array(sprintf(('Неверное значение параметра %s'), '"' . ('номер счета') . '"')));
                }
                $id  = intval($p3);
                if ($id <= 0) {
                    $this->aml_html->error_page(array(sprintf(('Неверное значение параметра %s'), 'id')));
                }

                $stid = $this->aml_oracle->execute("SELECT t.id,
                                                    t.pattern_name,
                                                    TO_CHAR(tg.p_period_beg, '" . $this->config->item('date_format') . "') period_beg,
                                                    TO_CHAR(tg.p_period_end, '" . $this->config->item('date_format') . "') period_end,
                                                    tg.id p_group_id,
                                                    tt.P_DEBIT_ACC,
                                                    tt.P_DEBIT_ORG,
                                                    tt.P_SUM,
                                                    NVL(tt.P_ORIGIN,398) p_origin,
                                                    NVL(tt.P_DESTINATION,398) p_destination
                                             FROM TB_PATTERNS t
                                             LEFT JOIN TB_SUSP_OPERATIONS_GROUP tg ON (t.id = tg.p_pattern_id)
                                             LEFT JOIN TB_SUSP_OPERATIONS_TREE tt ON (tg.ID = tt.P_GROUP_ID)
                                             WHERE  tt.P_GROUP_ID = :id
                                             AND    tt.P_DEBIT_ACC = :debit_acc ",__LINE__, array(':id' => $id,':debit_acc' => $account));

                $data = array();
                $map_type = 'kz';
                while ($r = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
                     if ($r['P_ORIGIN'] != 398 || $r['P_DESTINATION'] != 398) {
                         $map_type = 'world';
                     }
                     if ($r['P_ORIGIN'] == 398 && $r['P_DESTINATION'] == 398) {
                         continue;
                     }
                     $data[] = array(
                         'P_SENDER'   => $r['P_ORIGIN'],
                         'P_RECEIVER' => $r['P_DESTINATION'],
                         'P_AMOUNT'   => $r['P_SUM']
                     );
                }

                $xml_file =  $this->aml_map->make_xml($data,$map_type);
                $vars['content'] = $this->aml_map->map($xml_file);
                break;
            case 'graph':
                $account = $p2;
                if (!preg_match('#^[a-z0-9]+$#i', $account)) {
                    $this->aml_html->error_page(array(sprintf(('Неверное значение параметра %s'), '"' . ('номер счета') . '"')));
                }
                $id  = intval($p3);
                if ($id <= 0) {
                    $this->aml_html->error_page(array(sprintf(('Неверное значение параметра %s'), 'id')));
                }

                $stid = $this->aml_oracle->execute("SELECT t.id,
                                                    t.pattern_name,
                                                    TO_CHAR(tg.p_period_beg, '" . $this->config->item('date_format') . "') period_beg,
                                                    TO_CHAR(tg.p_period_end, '" . $this->config->item('date_format') . "') period_end,
                                                    tg.id p_group_id,
                                                    tt.P_DEBIT_ACC,
                                                    tt.P_DEBIT_ORG
                                             FROM TB_PATTERNS t
                                             LEFT JOIN TB_SUSP_OPERATIONS_GROUP tg ON (t.id = tg.p_pattern_id)
                                             LEFT JOIN TB_SUSP_OPERATIONS_TREE tt ON (tg.ID = tt.P_GROUP_ID)
                                             WHERE  tt.P_GROUP_ID = :id
                                             AND    tt.P_DEBIT_ACC = :debit_acc ",__LINE__, array(':id' => $id,':debit_acc' => $account));
                list($pattern_id, $pattern_name, $period_beg, $period_end, $group_id, $acc, $org) = oci_fetch_array($stid);
                $vars['page_name'] = '<a href="' . site_url('page/managepatterns') . '">' . ('Подозрительные схемы') . '</a> &raquo; ' .
                                     '<a href="' . site_url('page/managepatterns/view/' . $pattern_id) . '">' . $pattern_name . '</a> &raquo; ' .
                                     '<a href="' . site_url('page/managepatterns/founditems/' . $id) . '">' . $period_beg . '—' . $period_end . '</a> &raquo; ' .
                                     $acc . ' ' . $org;
                /* /page_name */

                $data_path = 'index.php/page/managepatterns/graphdata/' . $account . '/' . $id;
                $base_url = $this->config->item('base_url');
                $vars['content'] = <<< ENDJS
                    <div id="showgraph" width="100" height="100">
                        <p>
                            To view this page ensure that Adobe Flash Player version
                            10.0.0 or greater is installed.
                        </p>
                    </div>
                    <script type="text/javascript">
                        <!-- For version detection, set to min. required Flash Player version, or 0 (or 0.0.0), for no version detection. -->
                        var swfVersionStr = "10.0.0";
                        <!-- To use express install, set to playerProductInstall.swf, otherwise the empty string. -->
                        var xiSwfUrlStr = "playerProductInstall.swf";
                        var flashvars = {base_url : '{$base_url}', data_path: '{$data_path}', presentation: 'tree'};
                        var params = {};
                        params.quality = "high";
                        params.bgcolor = "#ffffff";
                        params.allowscriptaccess = "sameDomain";
                        params.allowfullscreen = "true";
                        var attributes = {};
                        attributes.id = "showgraph";
                        attributes.name = "showgraph";
                        attributes.align = "middle";
                        swfobject.embedSWF(
                            "{$base_url}swf/ShowGraph.swf", "showgraph",
                            screen.width - 20 , screen.height - 200,
                            swfVersionStr, xiSwfUrlStr,
                            flashvars, params, attributes);
                        <!-- JavaScript enabled so display the flashContent div in case it is not replaced with a swf object. -->
                        swfobject.createCSS("#showgraph", "display:block;text-align:left;");
                    </script>

ENDJS;

                break;
            case 'founditems':
                $id = intval($p2);
                if ($id <= 0) {
                    $this->aml_html->error_page(array(sprintf(('Неверное значение параметра %s'), 'id')));
                }

                /*page_name*/
                $stid = $this->aml_oracle->execute("SELECT t.id,
                                                            t.pattern_name,
                                                            TO_CHAR(tg.p_period_beg, '" . $this->config->item('date_format') . "') period_beg,
                                                            TO_CHAR(tg.p_period_end, '" . $this->config->item('date_format') . "') period_end
                                                     FROM TB_PATTERNS t
                                                     LEFT JOIN TB_SUSP_OPERATIONS_GROUP tg ON (t.id = tg.p_pattern_id)
                                                     WHERE  tg.ID = :id", __LINE__, array(':id' => $id));

                list($pattern_id, $pattern_name, $period_beg, $period_end) = oci_fetch_array($stid);
                $vars['page_name'] = '<a href="' . site_url('page/managepatterns') . '">' . ('Подозрительные схемы') . '</a> &raquo; ' .
                                     '<a href="' . site_url('page/managepatterns/view/' . $pattern_id) . '">' . $pattern_name . '</a> &raquo; ' .
                                     $period_beg . '—' . $period_end;
                /* /page_name */

                $stid = $this->aml_oracle->execute("SELECT  t.P_DEBIT_ACC, t.P_DEBIT_ORG
                                                    FROM TB_SUSP_OPERATIONS_TREE t
                                                    WHERE t.P_GROUP_ID = :id
                                                    AND t.PARENT_ID IS NULL
                                                    GROUP BY t.P_DEBIT_ACC,t.P_DEBIT_ORG
                                                    ORDER BY t.P_DEBIT_ACC", __LINE__, array(':id' => $id));


                $rows = array();
                while($r = oci_fetch_array($stid)) {
                    $rows[] = array(
                        //'<a href="' . site_url('page/managepatterns/graph/' .$r['P_DEBIT_ACC'] . ' ' . $r['P_DEBIT_ORG'] . '</a>',
                        '<a href="' . site_url('page/managepatterns/graph/' . $r['P_DEBIT_ACC'] . '/' . $id) . '">' . $r['P_DEBIT_ACC'] . ' ' . $r['P_DEBIT_ORG'] . '</a>'
                        //'<a href="' . site_url('page/managepatterns/map/' . $r['P_DEBIT_ACC'] . '/' . $id) . '">' . ('карта') . '</a>'
                    );
                }

                $vars['content'] = $this->aml_html->br();
                $vars['content'] .= '<div style="float:left;clear:both;margin-left:8px">';
                $vars['content'] .= $this->_html_table(array(sprintf(('Результат проверки за период: %s — %s'),$period_beg, $period_end)), $rows);
                $vars['content'] .= '</div>';
                break;
            case 'view':
                $st = -9999;  // job status
                $stid = $this->aml_oracle->execute("BEGIN :st := pkg_processes.GetStatus('BUILD_SUSPICIOUS_TREE'); END;",__LINE__, array(':st' => &$st));

                // 15.08.11 - сделал передачу в операцию getStatus переменной $st по ссылке (&$st);

                $id = intval($p2);
                if ($id <= 0) {
                    $this->aml_html->error_page(array(sprintf(('Неверное значение параметра %s'), 'scheme_id')));
                }
                $stid = $this->aml_oracle->execute("SELECT pattern_name, p_type FROM TB_PATTERNS t WHERE t.ID = :id",__LINE__, array(':id' => $id));
                list($pattern_name, $p_type) = oci_fetch_array($stid);

                $vars['page_name'] = '<a href="' . site_url('page/managepatterns') . '">' . ('Подозрительные схемы') . '</a> &raquo; ' . $pattern_name;

                if ($p_type == 1) {
                    $stid = $this->aml_oracle->execute("SELECT
                                                        t.p_level,
                                                        t.p_conditions_function,
                                                        tc.*,
                                                        tcn.p_condition_code
                                                 FROM TB_PATTERN_LEVELS t
                                                 LEFT JOIN TB_PATTERN_LEVEL_CONDITIONS tc ON(t.ID = tc.P_PATTERN_LEVEL_ID)
                                                 LEFT JOIN TB_CONDITIONS tcn ON (tc.P_CONDITION_ID = tcn.ID)
                                                 WHERE t.P_PATTERN_ID = :p_id
                                                 ORDER BY p_level",__LINE__, array(':p_id' => $id));
                    $rows = array();
                    $levels = array();
                    while($r = oci_fetch_array($stid, OCI_ASSOC)) {
                        $levels[] = $r;
                    }
                    $clevel = 0;

                    $levels_html = '<ul>';
                    foreach($levels as $lvl) {
                        if ($clevel != $lvl['P_LEVEL']) {
                            $levels_html .= "<li>" . ('Уровень') . " " . $lvl['P_LEVEL'] . ", " . ('функция условий') . " " . $lvl['P_CONDITIONS_FUNCTION'] . "</li>";
                            $levels_html .= $this->aml_html->br();
                            $clevel = $lvl['P_LEVEL'];
                        }
                        $levels_html .= $lvl['P_EXPRESSION'] . ' ' . $lvl['P_CONDITION_CODE'] . ' ' . $lvl['P_CONDITION_VALUE'];
                        $levels_html .= $this->aml_html->br();
                    }
                    $levels_html .= '</ul>';

                    $vars['content'] = '';
                    $vars['content'] .= $this->aml_html->br();

                    if ($st != 1) {
                        $vars['content'] .= form_open('page/managepatterns/view/' . $id);
                        $vars['content'] .= $this->load->view('managepatterns-newtask', array() , true);
                        $vars['content'] .= form_close();
                        $vars['content'] .= $this->aml_html->br();
                    } else {
                        $vars['content'] .= $this->load->view('managepatterns-waitfortask', array(), true);
                        $vars['content'] .= form_close();
                        $vars['content'] .= $this->aml_html->br();
                    }

                    $vars['content'] .= '<div class="simple-text">' . ('Результаты предыдущих проверок') . '</div>';
                    $vars['content'] .= $this->aml_html->br();
                    $stid = $this->aml_oracle->execute("SELECT t.id,t.p_task_started, tu.p_username, TO_CHAR(t.p_period_beg,'" . $this->config->item('date_format') . "'), TO_CHAR(t.p_period_end,'" . $this->config->item('date_format') . "')
                                                         FROM   TB_SUSP_OPERATIONS_GROUP t
                                                         LEFT JOIN TB_USERS tu ON (t.p_user_id = tu.id)
                                                         WHERE  t.P_PATTERN_ID = :p_id
                                                         ORDER BY t.p_task_started",__LINE__, array(':p_id' => $id));
                    $click_to_view_str = ('Нажмите, чтобы просмотреть найденные записи');
                    $delete_str = ('Удалить?');
                    $rows = array();
                    while($r = oci_fetch_array($stid,OCI_ASSOC + OCI_RETURN_NULLS)) {
                        $r['P_TASK_STARTED'] = '<a href="' . site_url('page/managepatterns/founditems/' . $r['ID']) . '" title="' . $click_to_view_str . '">' . $r['P_TASK_STARTED'] . '</a>';
                        $r['DELETE_LINK'] = array('#attributes' => array('style' => 'text-align:center'),'#data' => '<a onclick="return confirm(\'' . $delete_str . '\')" href="' . site_url('page/managepatterns/delresult/' . $r['ID']) . '/' . $id . '">' . $this->aml_html->img('trash.png') . '</a>');

                        unset($r['ID']);
                        $rows[] = $r;
                    }
                    $vars['content'] .= $this->_html_table(array(('Дата запуска задачи'),('Пользователь'),('Период с '),('Период по'), ('Удалить')), $rows);

                    if ($st == 1) {
                        $vars['run_js'] = <<<RUNJS
                            setTimeout(function () {
                                location.href = base_url + '/page/managepatterns/view/{$id}'
                            },
                            /*минуты*/1 * 60 * 1000);
RUNJS;
                    }

                    if ($this->input->post('op') && $st != 1) { // job status != 1
                        $clevel = null;
                        $param_idx = 1;
                        $expression_values = array();
                        $acc = $this->input->post('acc');
                        $dt1 = $this->input->post('date_from');
                        $dt2 = $this->input->post('date_until');
                        if (!preg_match('#\d{2}\.\d{2}.\d{4}#', $dt1)) {
                            $dt1 = null;
                        }
                        if (!preg_match('#\d{2}\.\d{2}.\d{4}#', $dt2)) {
                            $dt2 = null;
                        }

                        $mytree[0] = array(
                            'PARENT' => 0,
                            'DATA'   => array(array('CREDITACCOUNT' => $acc))
                        );
                        $clevel_number = 0;    //  номер уровня пп
                        $plsql_declare = '';
                        $plsql_start   = '';
                        $plsql_end     = '';

                        $levels2 = array();
                        foreach($levels as $lvl) {
                            $levels2[$lvl['P_LEVEL']][] = $lvl;
                        }

                        foreach($levels2 as $lvl) {
                            $clevel_number++;
                            $expressions = array();
                            $expressions_txt = '';
                            $g_expressions = array();
                            $g_expressions_txt = '';
                            $tabulator = str_repeat("\t", $clevel_number);

                            // 1. внутри по уровням
                            foreach($lvl as $i_lvl) {
                                //  1.1 сборка условий
                                if ($i_lvl['P_CONDITION_CODE'] == 'IS NULL' || $i_lvl['P_CONDITION_CODE'] == 'IS NOT NULL') {
                                    if ($i_lvl['P_GROUPING_BOOL']) {
                                        $g_expressions[] = $i_lvl['P_EXPRESSION'] . ' ' . $i_lvl['P_CONDITION_CODE'];
                                    } else {
                                        $expressions[] = $i_lvl['P_EXPRESSION'] . ' ' . $i_lvl['P_CONDITION_CODE'];
                                    }
                                } else if ($i_lvl['P_CONDITION_CODE'] == 'IN') {
                                    if ($i_lvl['P_GROUPING_BOOL']) {
                                        $g_expressions[] = $i_lvl['P_EXPRESSION'] . ' IN (' . $i_lvl['P_CONDITION_VALUE'] . ')';
                                    } else {
                                        $expressions[] = $i_lvl['P_EXPRESSION'] . ' IN (' . $i_lvl['P_CONDITION_VALUE'] . ')';
                                    }
                                } else {
                                    if ($i_lvl['P_GROUPING_BOOL']) {
                                        $g_expressions[] = $i_lvl['P_EXPRESSION'] . ' ' . $i_lvl['P_CONDITION_CODE'] . ' ' . $i_lvl['P_CONDITION_VALUE'];
                                    } else {
                                        $expressions[] = $i_lvl['P_EXPRESSION'] . ' ' . $i_lvl['P_CONDITION_CODE'] . ' ' . $i_lvl['P_CONDITION_VALUE'];
                                    }
                                }
                                $param_idx++;
                            }
                            if (count($expressions)) {
                                $expressions_txt = implode(' ' . $i_lvl['P_CONDITIONS_FUNCTION'] . ' ', $expressions);
                            }
                            if (count($g_expressions)) {
                                $g_expressions_txt = implode(' ' . $i_lvl['P_CONDITIONS_FUNCTION'] . ' ', $g_expressions);
                            }

                            if (trim($expressions_txt)) {
                                $expressions_txt = $tabulator . 'AND ' . $expressions_txt;
                            }
                            if (trim($g_expressions_txt)) {
                                $g_expressions_txt = $tabulator . 'HAVING ' . $g_expressions_txt;
                            }

                            // условие для предыдущего цикла
                            $plsql_cond = ($clevel_number == 1) ?
                                    'm2.p_account IS NOT NULL' :
                                    'm2.p_account = i' . ($clevel_number - 1) . '.p_accountcredit AND m2.p_account IS NOT NULL';

                            $plsql_cond .= ' ' . $expressions_txt;
                            $branch_cond = $this->aml_auth->get_branches_sql('o.P_ISSUEDBID');

                            $plsql_local = <<<ENDL
SELECT sumstr(o.id||',') id_list,
{$tabulator}       sum(o.p_baseamount) baseamount,
{$tabulator}       m1.p_account p_accountcredit,
{$tabulator}       m2.p_account p_accountdebit,
{$tabulator}       count(*) p_operations_count,
{$tabulator}       max(m2.p_name) p_debit_org,
{$tabulator}       max(m1.p_name) p_credit_org
{$tabulator}FROM tb_offlineoperations o, tb_off_members m1, tb_off_members m2
{$tabulator}WHERE $plsql_cond
{$tabulator}AND m1.p_account IS NOT NULL and m1.p_account != '-' AND m2.p_account IS NOT NULL and m2.p_account != '-' and m2.p_operationid=o.id and m1.p_operationid=o.id and m1.p_clientrole='1' and m2.p_clientrole='2'
{$tabulator}AND o.p_operationdatetime BETWEEN to_date(dt1,'DD.MM.YYYY') AND to_date(dt2,'DD.MM.YYYY')
{$tabulator}AND $branch_cond
{$tabulator}GROUP BY m2.p_account, m1.p_account

ENDL;

                            $plsql_local .= $tabulator . ' ' . $g_expressions_txt;
                            $plsql_start .= $tabulator . "FOR i" . $clevel_number . " IN (" . $plsql_local . ") LOOP\n";
                            $plsql_end    = $tabulator . "END LOOP;/* end of level " . $clevel_number . " */\n" . $plsql_end;
                        }
                        $plsql_end    = $tabulator . "IF MOD(TotalRecords,500) = 0 THEN COMMIT; END IF; " . $plsql_end;

                        $plsql_declare .= $tabulator . "TotalRecords NUMBER := 0;\n";
                        $plsql_declare .= $tabulator . "lvl0 NUMBER := NULL;\n";
                        $plsql_declare .= $tabulator . "dt1 VARCHAR2(30) := " . ($dt1 == null ? 'NULL' : "'{$dt1}'") . ";\n";
                        $plsql_declare .= $tabulator . "dt2 VARCHAR2(30) := " . ($dt2 == null ? 'NULL' : "'{$dt2}'") . ";\n";
                        $plsql_declare .= $tabulator . "u_id NUMBER := " . $this->aml_auth->get_uid() . ";\n";
                        $plsql_declare .= $tabulator . "group_id NUMBER;\n";
                        $plsql_declare .= $tabulator . "pattern_id NUMBER := " . $id . ";\n";

                        // добавим insert-ты
                        for($i = 1; $i <= $clevel_number; $i++) {
                            $plsql_declare .= $tabulator . "lvl" . $i . " NUMBER;\n";
                            $prev_i = $i - 1;

                            $plsql_body .= $tabulator . "IF GetRecordInsertRequired(lvl{$prev_i}, i{$i}.p_accountdebit, i{$i}.p_accountcredit, group_id) = 1 THEN\n";
                            $plsql_body .= $tabulator . "\t";
                            $plsql_body .= $tabulator . "lvl" . $i . " := GetID();\n";
                            $plsql_body .= $tabulator . "\t";
                            $plsql_body .= $tabulator . "TotalRecords := TotalRecords +1 ;\n";
                            $plsql_body .= $tabulator . "\t";
                            $plsql_body .= $tabulator . "INSERT INTO TB_SUSP_OPERATIONS_TREE(id, p_group_id, parent_id, p_debit_acc, p_credit_acc, p_sum, p_debit_org, p_credit_org, p_operations_count, p_id_list) " .
                                           "VALUES(lvl{$i}, group_id, lvl{$prev_i},  i{$i}.p_accountdebit, i{$i}.p_accountcredit, i{$i}.baseamount, i{$i}.p_debit_org, i{$i}.p_credit_org, i{$i}.p_operations_count, i{$i}.id_list); \n" ;
                            $plsql_body .= $tabulator . "END IF;\n";
                        }


                        $plsql_final = "CREATE OR REPLACE PROCEDURE JOB\$RUNSCHEMA AS \n";
                        $plsql_final .= $plsql_declare;
                        $plsql_final .= "BEGIN\n";
                        $plsql_final .= "pkg_processes.StartProcess('BUILD_SUSPICIOUS_TREE');\n";
                        $plsql_final .= "group_id := GetID();\n";
                        $plsql_final .= "INSERT INTO TB_SUSP_OPERATIONS_GROUP(id, p_pattern_id,p_user_id, p_task_started, p_task_stopped, p_period_beg, p_period_end) VALUES(group_id,pattern_id, u_id, sysdate, null,dt1, dt2);\n";
                        $plsql_final .= $plsql_start;
                        $plsql_final .= $plsql_body;
                        $plsql_final .= $plsql_end;
                        $plsql_final .= "COMMIT;\n";
                        $plsql_final .= "pkg_processes.EndProcess('BUILD_SUSPICIOUS_TREE');\n";
                        $plsql_final .= "EXCEPTION \n";
                        $plsql_final .= "WHEN OTHERS THEN \n";
                        $plsql_final .= "dbg\$insert('ERR_WebInterfaceJob', substr(sqlerrm, 1, 4000));\n";
                        $plsql_final .= "pkg_processes.ChangeStatus('BUILD_SUSPICIOUS_TREE', -1); \n";
                        $plsql_final .= "END;";
                        // собрали plsql блок, создаем процедуру

                        $stid = $this->aml_oracle->execute($plsql_final,__LINE__);
                        $job_id = -99999;
                        $job_sql = "BEGIN sys.dbms_job.submit(job =>:job_id, what => 'BEGIN JOB\$RUNSCHEMA;END;', next_date => SYSDATE, interval => null); END;";

                        $stid = $this->aml_oracle->execute($job_sql,__LINE__,array(':job_id' => &$job_id));

                        $result_page = site_url('page/managepatterns/view/' . $id);
                        $varsjs = array(
                            'result_page' => $result_page
                        );
                        $vars['content'] .= $this->load->view('managepatterns-taskstarted', $varsjs, true);
                    }
                } else if ($p_type == 2) {
                    if ($this->input->post('op')){
                        $stid = $this->aml_oracle->execute("
                                                     SELECT
                                                            t.p_level,
                                                            t.p_conditions_function,
                                                            tc.*,
                                                            tcn.p_condition_code
                                                     FROM TB_PATTERN_LEVELS t
                                                     LEFT JOIN TB_PATTERN_LEVEL_CONDITIONS tc ON(t.ID = tc.P_PATTERN_LEVEL_ID)
                                                     LEFT JOIN TB_CONDITIONS tcn ON (tc.P_CONDITION_ID = tcn.ID)
                                                     WHERE t.P_PATTERN_ID = :p_id
                                                     ORDER BY p_level",__LINE__, array(':p_id' => $id));

                        $rows = $conditions = $row = $expressions = array();
                        while ($row = oci_fetch_array($stid)) {
                            $c_function  = ($row['P_CONDITIONS_FUNCTION']?$row['P_CONDITIONS_FUNCTION']:"AND");
                            if ($row['P_CONDITION_CODE'] == 'IS NULL' || $row['P_CONDITION_CODE'] == 'IS NOT NULL') {
                                if ($row['P_GROUPING_BOOL']) {
                                    $g_expressions[] = $row['P_EXPRESSION'] . ' ' . $row['P_CONDITION_CODE'];
                                } else {
                                    $expressions[]   = $row['P_EXPRESSION'] . ' ' . $row['P_CONDITION_CODE'];
                                }
                            } else if ($row['P_CONDITION_CODE'] == 'IN') {
                                if ($row['P_GROUPING_BOOL']) {
                                    $g_expressions[] = $row['P_EXPRESSION'] . ' IN (' . $row['P_CONDITION_VALUE'] . ')';
                                } else {
                                    $expressions[]   = $row['P_EXPRESSION'] . ' IN (' . $row['P_CONDITION_VALUE'] . ')';
                                }
                            } else if($row['P_CONDITION_CODE']) {
                                if ($row['P_GROUPING_BOOL']) {
                                    $g_expressions[] = $row['P_EXPRESSION'] . ' ' . $row['P_CONDITION_CODE'] . ' ' . $row['P_CONDITION_VALUE'];
                                } else {
                                    $expressions[]   = $row['P_EXPRESSION'] . ' ' . $row['P_CONDITION_CODE'] . ' ' . $row['P_CONDITION_VALUE'];
                                }
                            }
                        }

                        $bindings = array();
                        if (preg_match($this->config->item('regexp_date'), $this->input->post('date_from'))) {
                            $bindings[':dt_from'] = $this->input->post('date_from');
                            $expression_dt = " AND o.P_OPERATIONDATETIME > TO_DATE(:dt_from,'" . $this->config->item('date_format') . "') ";
                        }
                        if (preg_match($this->config->item('regexp_date'), $this->input->post('date_until'))) {
                            $bindings[':dt_until'] = $this->input->post('date_until');
                            $expression_dt .= " AND o.P_OPERATIONDATETIME < TO_DATE(:dt_until,'" . $this->config->item('date_format') . "') + 1 ";
                        }
                        $expressions[] = $this->aml_auth->get_branches_sql('t.P_ISSUEDBID');
                        if(!$c_function){$c_function = 'AND';}

                        if (count($expressions)) {
                            $expressions_txt   = implode(' '.$c_function.' ', $expressions);
                        }
                        if (count($g_expressions)) {
                            $g_expressions_txt = implode(' '.$c_function.' ', $g_expressions);
                        }
                        if (trim($expressions_txt)) {
                            $expressions_txt = "WHERE m1.p_operationid=o.id and m2.p_operationid=o.id and m1.p_account!='-' and m2.p_account!='-'
                            	and m1.p_clientrole='1' and m2.p_clientrole='2' ".$expression_dt." AND (".$expressions_txt.") and m1.p_countrycode != m2.p_countrycode";
                        } else {
                        	$expressions_txt = "where m1.p_countrycode != m2.p_countrycode";
                        }
                        if (trim($g_expressions_txt)) {
                            $g_expressions_txt = ' HAVING ' . $g_expressions_txt;
                        }

                        $q = "SELECT
                                  SUM(o.p_baseamount) p_baseamount,
                                  MAX(m1.p_countrycode) p_origin,
                                  MAX(m2.p_countrycode) p_destination,
                                  COUNT(*) p_count
                              FROM TB_OFFLINEOPERATIONS o, tb_off_members m1, tb_off_members m2
                              {$expressions_txt}
                              GROUP BY m1.P_COUNTRYCODE, m2.P_COUNTRYCODE
                              {$g_expressions_txt}";

                        $stid = $this->aml_oracle->execute($q, __LINE__,$bindings);

                        $map_type = 'kz';
                        while ($r = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
                             if ($r['P_ORIGIN'] != 398 || $r['P_DESTINATION'] != 398) {
                                 $map_type = 'world';
                             }
                             if ($r['P_ORIGIN'] == 398 && $r['P_DESTINATION'] == 398) {
                                 continue;
                             }
                             $data[] = array(
                                 'P_SENDER'   => $r['P_ORIGIN'],
                                 'P_RECEIVER' => $r['P_DESTINATION'],
                                 'P_AMOUNT'   => $r['P_BASEAMOUNT'],
                                 'P_COUNT'    => $r['P_COUNT']
                             );
                        }
                        $xml_file =  $this->aml_map->make_xml($data,$map_type);
                        $vars['content'] = $this->aml_map->map($xml_file);
                    } else {
                        $vars['content'] = form_open('page/managepatterns/view/' . $id);
                        $vars['content'] .= $this->load->view('managepatterns-newtask', array(), true);
                        $vars['content'] .= form_close();
                    }
                }
                break;
            default:
                $stid = $this->aml_oracle->execute("SELECT * FROM TB_PATTERNS",__LINE__);
                $vars['content'] .= $this->aml_html->br();
                $vars['content'] .= $this->aml_html->button_link(array('link' => 'page/managepatterns/add', 'link_text' => ('Добавить схему'), 'attr' => array('class' => 'button-link')));
                $vars['content'] .= $this->aml_html->br();

                $rows = array();
                while($r = oci_fetch_array($stid, OCI_ASSOC)) {
                    $rows[] = array(
                        'VIEW' => '<a href="' . site_url('page/managepatterns/view/' . $r['ID']) . '">' . $r['PATTERN_NAME'] . '</a>',
                        'EDIT' => array('#attributes' => array('style' => 'text-align:center'),'#data' => '<a href="' . site_url('page/managepatterns/edit/' . $r['ID']) . '">' . $this->aml_html->img('scenario_edit.png') . '</a>'),
                        'DEL'  => array('#attributes' => array('style' => 'text-align:center'),'#data' => '<a onclick="return confirm(\'' . ('Удалить?') . '\')" href="' . site_url('page/managepatterns/delete/' . $r['ID']) . '">' . $this->aml_html->img('trash.png') . '</a>')
                    );
                }
                $vars['content'] .= $this->_html_table(array(('Наименование схемы'),('Редактировать'),('Удалить')), $rows);
        }

        $this->aml_context->set_general_vars($vars);
        $this->load->view('main', $vars);
    }

    function _build_tree($a) {
        $ret = '<ul>';
        foreach($a as $k => $i) {
            if (is_array($i)) {
                $ret .= "<li>" . $k . $this->_build_tree($i) .  '</li>';
            } else {
                $ret .= "<li>" . $k . '</li>';
            }
        }
        $ret .= '</ul>';
        return $ret;
    }

    // страница управления сценариями
    function managescenarios($what = null, $p1 = null) {
        $this->aml_auth->check_auth();
        //$this->_get_connection();
        $vars['active_link'] = 'managescenarios';
        $vars['page_name'] = ('Управление сценариями');

        $can_do = $this->aml_security->check_privilege(24); // ADMIN
        if (!$can_do) {
            $this->aml_html->error_page(array(('Отсутствуют права для данного действия.')));
        }

        switch($what) {
            case 'delete':
                $scenario_id = intval($p1);
                if ($scenario_id <= 0) {
                    $this->aml_html->error_page(array(sprintf(('Неверное значение параметра %s'), 'scenario_id')));
                }
                $reason = $this->input->post('reason');
                if (trim($reason) == '') {
                    print $this->aml_html->js_alert("Комментарий обязателен для заполнения");
                    return;
                }

                $p_code = '';
                $stid = $this->aml_oracle->execute('SELECT P_CODE FROM TB_SCENARIOS WHERE ID = :aml_id',__LINE__, array(':aml_id' => $scenario_id, 'P_CODE' => &$p_code));
                oci_fetch($stid);

                $q = "BEGIN  DelByCode(:objectname); END;";
//                $stid = $this->aml_oracle->execute($q,__LINE__, array(':objectname' => $p_code) );

                $bindings = array(':objectname' => $p_code) ;
                $binding_types = array(':objectname' => 'VARCHAR2');
                //$this->aml_admcontrol->execute($q,__LINE__, $bindings, $binding_types, 'SCENARIOS', sprintf(('Удаление сценария %s'), $p_code),$reason);
                $stid = @$this->aml_oracle->execute($q, __LINE__, $bindings,false);
                print $this->aml_html->js_alert("Сценарий будет удален после подтверждения администратором");

                die();
                break;
            case 'add':
                $scenario_str = ('Новый сценарий');
                $reason = $this->input->post('reason');
                $new_scenario_sql =
                "DECLARE \n" .
                "   in_Code       varchar2(4000); \n" .
                "   in_IsActive   number; \n" .
                "   in_IsCompiled number; \n" .
                "   out_template  clob; \n" .
                "   out_error     varchar2(4000); \n" .
                "   new_id        number; \n" .
                "BEGIN \n" .
                "    CreateProc('{$scenario_str}', \n" .
                "                1, \n" .
                "                in_code, \n" .
                "                out_template, \n" .
                "                in_IsActive, \n" .
                "                in_IsCompiled, \n" .
                "                out_error, \n" .
                "                new_id); \n" .
                "END;";
                $body = '';
                $error = '';
                $code = '';
                $order = '';
                $scenario_id = 9999999999;
                if (trim($reason) == '') {
                    $output = $this->aml_html->js_alert("Не указан комментарий");
                    print $output;
                    return;
                }

                //$this->aml_admcontrol->execute($new_scenario_sql, __LINE__, array(), null, 'SCENARIO', ('Создание нового сценария'), $reason);
                $stid = @$this->aml_oracle->execute($new_scenario_sql, __LINE__, $bindings,false);
                $output .= $this->aml_html->js_alert("Сценарий создан и ожидает подтверждения администратором");
                print $output;
                die();
                break;
            case 'edit':
                $scenario_id = intval($p1);
                if ($scenario_id <= 0) {
                    $this->aml_html->error_page(array(sprintf(('Неверное значение параметра %s'), 'scenario_id')));
                }
                if ($this->input->post('op')) {
                    $reason = $this->input->post('reason');
                    if (empty($reason)) {
                        $this->aml_html->error_page(array(('Комментарий обязателен для заполнения')));
                    }

                    $upd_scenario_sql =
                    "BEGIN \n" .
                    "    EditProc( \n" .
                    "      :in_code, \n" .
                    "      :in_Description, \n" .
                    "      :in_Order, \n" .
                    "      :in_Body, \n" .
                    "      :out_IsActive, \n" .
                    "      :out_IsCompiled, \n" .
                    "      :out_Error \n" .
                    "    ); \n" .
                    "END;";

                    $code        = $this->input->post('p_code');
                    $description = $this->input->post('p_description');
                    $order       = $this->input->post('p_order');
                    $body        = $this->input->post('p_body');
                    $isactive    = 0;
                    $iscompiled  = 0;
                    $error       = '';

                    $bindings = array(
                         ':in_code' => $code,
                         ':in_Description' => $description,
                         ':in_Order' => $order,
                         ':in_Body' => $body,
                         ':out_IsActive' => &$isactive,
                         ':out_IsCompiled' => &$iscompiled,
                         ':out_Error' => &$error
                    );

                    $binding_types = array(
                         ':in_code'        => 'VARCHAR2',
                         ':in_Description' => 'VARCHAR2',
                         ':in_Order'       => 'NUMBER',
                         ':in_Body'        => 'CLOB',
                         ':out_IsActive'   => 'NUMBER',
                         ':out_IsCompiled' => 'NUMBER',
                         ':out_Error'      => 'VARCHAR2'
                    );
                    //$this->aml_admcontrol->execute($upd_scenario_sql, __LINE__, $bindings, $binding_types,'SCENARIOS', ('Редактирование сценария ') . $code, $reason);

                    $stid = $this->aml_oracle->execute($upd_scenario_sql,__LINE__, $bindings);
                    if (!empty($error)) {
                        $this->aml_html->error_page(array(sprintf(('Произошла ошибка компиляции объекта: %s')), $error));
                    }

                    header('Location: ' . site_url('page/managescenarios'));
                    die();
                }
                $q = 'SELECT * FROM ' . $this->db_schema_prefix . 'TB_SCENARIOS t WHERE t.ID = :aml_id';
                $stid = $this->aml_oracle->execute($q,__LINE__, array(':aml_id' => $scenario_id));
                $row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_LOBS);

                $vars['run_js'] = <<<ENDLJS
                \$(document).ready(function() {
                    \$('textarea.resizable:not(.processed)').TextAreaResizer();
                });
ENDLJS;

                $vars['scenario'] = $row;
                $vars['content'] = $this->load->view('scenario-edit', $vars, true);
                break;
            case 'activate':
            case 'deactivate':
            case 'set_online':
            case 'unset_online':
            case 'set_offline':
            case 'unset_offline':
                $scenario_id = intval($p1);
                $reason = $this->input->post('reason');
                if ($scenario_id <= 0) {
                    $this->aml_html->error_page(array(sprintf(('Неверное значение параметра %s'), 'scenario_id')));
                }
                if (trim($reason) == '') {
                    $this->aml_html->js_alert('Не указан комментарий');
                    return;
                }

                $bool = array(
                	'activate' => array('P_ISACTIVE',1),
                	'deactivate' => array('P_ISACTIVE',0),
                	'set_online' => array('P_FOR_ON',1),
                	'unset_online' => array('P_FOR_ON',0),
                	'set_offline' => array('P_FOR_OFF',1),
                	'unset_offline' => array('P_FOR_OFF',0)
                );

                if($this->_toggle_scenario_activity($scenario_id, $bool[$what][0], $bool[$what][1], $reason)){
                	echo $this->aml_html->js_alert('Изменение сценария ожидает подтверждения другими администратором');
                } else {
					echo $this->aml_html->js_alert(('Нельзя сделать активным объект, который не откомпилирован.'));
                }
                //header('Location: ' . site_url('page/managescenarios'));
                die();
                break;
            default:
                $q = 'SELECT * FROM ' . $this->db_schema_prefix . 'TB_SCENARIOS t ORDER BY t.p_order';
                $stid = $this->aml_oracle->execute($q,__LINE__);
                oci_fetch_all($stid, $results);
                $vars['scenarios'] = $results;
                $vars['content'] = $this->load->view('scenarios', $vars, true);
        }
        $this->aml_context->set_general_vars($vars);
        $this->load->view('main', $vars);
    }
    
	function special_report(){//Создал функцию Адилет по заявке 9942 25.08.2018
	$page_name = ('Отчет по пользователям: ' );
	$vars['page_name'] = $page_name;
	$vars['content'] = $this->load->view('view_special',$vars,true);
	$this->aml_context->set_general_vars($vars);
	$this->load->view('main',$vars);
	}
	
	function run_report(){//Создал функцию Адилет по заявке 9942 09.08.2018
      $this->load->helper('download');
	  $login = $this->aml_auth->get_username();
	  
	  $data ="";
	  
	  list($user_ip, $user_comp_name, $user_mac_addr) = $this->aml_security->get_user_data();

               $q1 = "INSERT INTO tb_audit_all(id,p_table,p_rec_id,p_username,p_date_update,p_action_type,p_edit_fields,p_ip,p_computer_name,p_mac_address) " .
                     "VALUES(GetID(), '-', 0, NVL(UPPER(:login),'NULL'), sysdate, 'Отчет по пользователям',:logtxt, :ip, :comp_name, :mac_addr)";
                $values = array(':login' => $login, ':logtxt' => 'Запуск отчета по пользователям login: ' . $login . ', ip: ' . $user_ip, ':ip'=>$user_ip, ':comp_name'=>$user_comp_name, ':mac_addr'=>$user_mac_addr);
                $this->aml_oracle->execute($q1,__LINE__, $values);
			
 
 $q = "select (substr('163595',1,6)||
       (chr(9) || substr(Transliterate(m.p_firstname),1,30) || chr(9) || substr(Transliterate(m.p_secondname),1,30) || chr(9) ||
       substr(Transliterate(m.p_username),1,50) || chr(9) || null || chr(9) || null || chr(9) ||
       substr((rtrim(sumstr(distinct(Transliterate(s.p_name) || ', ')), ', ')),1,70) || chr(9) ||
       substr(Transliterate(s.p_description),1,512) || chr(9) || substr(Transliterate(m.p_username),1,70) || chr(9) ||
       substr((rtrim(sumstr(distinct(Transliterate(r.p_rolename) || ', ')), ', ')),1,512) || chr(9) || null ||
       chr(9) || null || chr(9) || null || chr(9) || null || chr(9) || substr(Transliterate(DECODE(m.p_pwd_never_expire_bool, 1, 'F1', 0, '', '')),1,2) ||
       chr(9) || null || chr(9) || null|| CHR(13)||CHR(10)))  as DD
  from tb_role_groups s,
       tb_user_groups t,
       tb_users       m,
       tb_group_roles v,
       tb_roles       r
 where m.p_deleted_date is null
 and t.id_user(+) = m.id
 and t.id_group = s.id(+)
 and v.id_group(+) = s.id
 and r.id(+) = v.id_role
 group by m.p_firstname, m.p_secondname, m.p_username, s.p_description, m.p_pwd_never_expire_bool";
 
				  
		$stid = $this->aml_oracle->execute($q,__LINE__);
		while($res = oci_fetch_array($stid,OCI_ASSOC)){
		$K = '163595';
		$data = $data . $res['DD'];
		}
		
        $data = rtrim($data,"
		");
		$today = "EERS" . $K . ".txt";
		force_download($today, $data);
		}
	
	
    function managereports($what = null,$p1 = null, $p2 = null) {
        $vars = array();
        $this->aml_auth->check_auth();
        $can_use_reports = $this->aml_security->check_privilege(5) || $this->aml_security->check_privilege(91); // REPORTS
        if (!$can_use_reports) {
            $this->aml_html->error_page(array(('Отсутствуют права для данного действия.')));
        }

        $vars['page_name'] = ('Управление отчетами');
        $vars['content']   = '';
        switch($what) {
            case 'delete':
                $can_admin = $this->aml_security->check_privilege(24); // ADMIN
                if (!$can_admin) {
                    $this->aml_html->error_page(array(('Отсутствуют права для данного действия.')));
                }
                $id = intval($p1);
                if ($id <= 0) {
                    $this->aml_html->error_page(array(sprintf(('Неверное значение параметра %s'), 'id')));
                }
                $reason = $this->input->post('reason');
                if(empty($reason)) {
                    print $this->aml_html->js_alert('Комментарий обязателен для заполнения');
                    return;
                }
                $q = 'SELECT * FROM TB_REPORTS t WHERE t.ID = :id';
                $bindings = array(':id' => $id);
                $stid = $this->aml_oracle->execute($q, __LINE__, $bindings);
                $report = oci_fetch_array($stid, OCI_ASSOC | OCI_RETURN_NULLS);

                $q = 'DELETE FROM TB_REPORTS t WHERE t.ID = :id';
                $bindings = array(':id' => $id);
                $binding_types = array(':id' => 'NUMBER');
                $stid = $this->aml_oracle->execute($q, __LINE__, $bindings);
                //$this->aml_admcontrol->execute($q, __LINE__, $bindings, $binding_types, 'REPORTS', sprintf(('Удаление отчета %s'), $report['REPORT_NAME']), $reason);
                print $this->aml_html->js_alert("Отчет будет удален после подтверждения вторым администратором");
                die();
                break;
            case 'add':
                $can_admin = $this->aml_security->check_privilege(24); // ADMIN
                if (!$can_admin) {
                    $this->aml_html->error_page(array(('Отсутствуют права для данного действия.')));
                }
                $reason = $this->input->post('reason');
                if (empty($reason)) {
                    print $this->aml_html->js_alert("Комментарий обязателен для заполнения");
                    return;
                }
                $id = 9999999999;
                $q = "INSERT INTO tb_reports (id, report_name, view_name, description, order_number)  " .
                     "VALUES (GetID(), '" . ('Новый отчет') . "', 'no view', '" . ('Новый отчет') . "', 100) ";

                //$this->aml_admcontrol->execute($q, __LINE__, null, null, 'REPORTS', ('Создание отчета'), $reason);
                $stid = $this->aml_oracle->execute($q, __LINE__, array(':id' => &$id));

                print $this->aml_html->js_alert("Отчет будет добавлен после одобрения вторым администратором");
                return;
                break;
            case 'deletearchive':
                $id = floatval($p1);
                if ($id <= 0) {
                    $this->aml_html->error_page(array(sprintf(('Неверное значение параметра %s'), 'id')));
                }
                $id2 = intval($p2);
                $this->aml_oracle->execute('DELETE FROM TB_REPORT_HISTORY t WHERE t.ID = :id',__LINE__, array(':id' => $id));
                header('Location:' .site_url('page/managereports/run/' . $id2));
                die();
                break;
            case 'edit':
                $can_admin = $this->aml_security->check_privilege(24); // ADMIN
                if (!$can_admin) {
                    $this->aml_html->error_page(array(('Отсутствуют права для данного действия.')));
                }
                $id = intval($p1);
                if ($id <= 0) {
                    $this->aml_html->error_page(array(sprintf(('Неверное значение параметра %s'), 'id')));
                }

                $vars['saved'] = '';
                if ($this->input->post('op')) {
                    $report_name = $this->input->post('REPORT_NAME');
                    $description = $this->input->post('DESCRIPTION');
                    $order_number = intval($this->input->post('ORDER_NUMBER'));
                    $xml_report = $this->input->post('XML_REPORT');
                    $xsl_text = $this->input->post('XSL_TEXT');
                    $reason = $this->input->post('reason');
                    if (empty($reason)) {
                        $this->aml_html->error_page(array(('Комментарий обязателен для заполнения')));
                    }

                    $bindings = array(
                        ':report_name'  => $report_name,
                        ':description'  => $description,
                        ':order_number' => $order_number,
                        ':xml_report'   => $xml_report,
                        ':xsl_text'     => $xsl_text,
                        ':id'           => $id
                    );
                    $binding_types = array(
                        ':report_name'  => 'VARCHAR2',
                        ':description'  => 'VARCHAR2',
                        ':order_number' => 'NUMBER',
                        ':xml_report'   => 'CLOB',
                        ':xsl_text'     => 'CLOB',
                        ':id'           => 'NUMBER'
                    );
                    $q = "UPDATE TB_REPORTS t
                          SET t.REPORT_NAME = :report_name,
                              t.DESCRIPTION = :description,
                              t.ORDER_NUMBER = :order_number,
                              t.XML_REPORT = :xml_report,
                              t.XSL_TEXT = :xsl_text
                          WHERE t.ID = :id";

                    //$this->aml_admcontrol->execute($q,__LINE__, $bindings, $binding_types, 'REPORTS', ('Редактирование отчета'), $reason);
                    $stid = @$this->aml_oracle->execute($q, __LINE__, $bindings,false);
                    $vars['saved'] = '<ul class="info-messages"><li>' . ('Изменения вступят в силу после подтверждения вторым администратором') . '</li></ul>';
                }
                $q = "SELECT * FROM TB_REPORTS t WHERE t.id = :id";
                $stid = $this->aml_oracle->execute($q, __LINE__, array(':id' => $id));
                $vars['data'] = oci_fetch_array($stid,OCI_ASSOC + OCI_RETURN_LOBS);
                $vars['action'] = 'page/managereports/edit/' . $id;
                $vars['content'] = $this->load->view('report-edit', $vars, true);
                break;
            case 'download':
                $report_id = intval($p1);
                if ($report_id <= 0) {
                    $this->aml_html->error_page(array(sprintf(('Неверное значение параметра %s'), 'id')));
                }
                $stid = $this->aml_oracle->execute('SELECT P_REPORT_BODY FROM TB_REPORT_HISTORY t WHERE t.ID = :id',__LINE__, array(':id' => $report_id));
                list($data) = oci_fetch_array($stid, OCI_RETURN_LOBS);

                $this->aml_html->output_file(date($this->config->item('php_date_format')) . '.xls', $data, 'application/vnd.ms-excel; charset=utf-8');
                die();
                break;
           case 'run': 
		  /* 
				// Обновленный Запуск отчетов 29,05,13
				
				
				$report_id = intval($p1);
				 
				$post = array();
				foreach ( $_POST as $key => $value ) // получаем значения формы
				{
					if ($key =='op')
						continue;
					$post[$key] = $this->input->post($key);
				}
				$noxsl = ($p2 == 'noxsl') ? true : false;
                if ($report_id <= 0) {
                    $this->aml_html->error_page(array(sprintf(('Неверное значение параметра %s'), 'id')));
                }
				//$q = "begin pkg_report.get_report_params(:id_report); end;";
				$q = "SELECT name, param_type FROM TB_REPORT_PARAMETERS WHERE report_id = :id_report"; //получение параметров для отчёта
				$bindings = array(':id_report'=>$report_id);
				$stid = $this->aml_oracle->execute($q, __LINE__, $bindings);
				$params = "<?xml version=\"1.0\"?>
				<report>";
				
				//if ()
				
				while($r = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS))
				{
				
					$params .="<parameter>
					<param_name>".$r['NAME']."</param_name>
					<param_value>".$post[$r['NAME']]."</param_value>
					</parameter>";
				}
				
				$params .= "</report>";	
				$rep_username = $this->aml_auth->get_username();
				//die(var_dump($rep_username));
				if ($this->input->post('op')) 
				{
                    //die(var_dump($_POST['P5']));
					//формирование xml отчёта	
					$q = "BEGIN :return_result := RUN_REPORT_PROC(:rep_id, :rep_params, :user_id); END;"; 
					$tb_rep_hist_id = 9999999999;
					$bindings = array(':return_result'=>&$tb_rep_hist_id,':rep_id'=>$report_id, ':rep_params'=>$params, ':user_id'=>$rep_username);
					//die(var_dump($bindings));
					$stid = $this->aml_oracle->execute($q, __LINE__, $bindings);
				}
                elseif ($this->input->post('op2') &&  ( $_POST['P3'] != "" || $_POST['P4'] != ""   || $_POST['P5'] != "" ) )  {
                    $q = "BEGIN :return_result := RUN_REPORT_PROC(:rep_id, :rep_params, :user_id); END;"; 
                    $tb_rep_hist_id = 9999999999;
                    $bindings = array(':return_result'=>&$tb_rep_hist_id,':rep_id'=>$report_id, ':rep_params'=>$params, ':user_id'=>$rep_username);
                    //die(var_dump($bindings));
                    $stid = $this->aml_oracle->execute($q, __LINE__, $bindings);

                } 
               
				 
               
               

				$q_param = "select * from TB_REPORT_PARAMETERS WHERE REPORT_ID = :r_id"; 
				$bind_param = array(':r_id' =>$report_id);
				$stid_param = $this->aml_oracle->execute($q_param, __LINE__, $bind_param);

				$block = '';
                $vars['content'] = '<fieldset class="viewdata" style="background:transparent;width:800px;margin-top:10px;margin-left:10px"><legend>' . ('Параметры отчета') . '</legend>';
                $vars['content'] .= form_open('page/managereports/run/' . $report_id . ($noxsl ? '/noxsl' : ''),array('class' => 'check-required-field-form','style' => 'padding:10px'));
				$block = '<table cellspacing="0" cellpadding="5">';
                while($res_param = oci_fetch_array($stid_param))  {
                       $name = strtoupper((string)($res_param['NAME']));
                        $title = htmlspecialchars($res_param['PARAM_NAME'], ENT_QUOTES, 'utf-8');
                        $mandatory_class = ($res_param['REQUIRED'] == '1') ? ' required-field' : '';
                        $mandatory_marker = ($res_param['REQUIRED'] == '1') ? '<span style="color:red;font-weight:bold">* </span>' : '';
                        $val = htmlspecialchars($post[$res_param['PARAM_NAME']], ENT_QUOTES, 'utf-8');
						
                        switch($res_param['PARAM_TYPE']) {
						
                            case 'N':
                                $block .= <<<ENDL
                                <tr><td><label for="{$name}">{$mandatory_marker} {$title}</label></td>
                                <td><input size="50" class="numeric{$mandatory_class}" id="{$name}" name="{$name}" value="{$val}" type="text"></td></tr>
                
ENDL;
                                break;
                            case 'D':
								
                                $block .= <<<ENDL
                                <tr><td><label for="{$name}">{$mandatory_marker} {$title}</label></td>
                                <td><input size="50" class="datepicker{$mandatory_class}" id="{$name}" name="{$name}" value="{$val}" type="text"></td></tr>
ENDL;
                                break;
                            case 'V': 
                                $block .= <<<ENDL
                                <tr><td><label for="{$name}">{$mandatory_marker} {$title}</label></td>
                                <td><input size="50" class="{$mandatory_class}" id="{$name}" name="{$name}" value="{$val}" type="text"></td></tr>
ENDL;
								break;
							case 'L' :
								$stid = $this->aml_oracle->execute("select * from ".$res_param['DIRECTORY_LIST']." order by id asc", __LINE__);
								//die("select * from ".$p->directory);
								//$stid = $this->aml_oracle->execute("select * from ".$p->directory." ",__LIST__);
								oci_fetch_all($stid, $dir_data);
								$block .= "<tr><td><label for=".$name.">".$mandatory_marker." ".$title."</label></td><td>";
								$block .= "<select style='width:290px;' class=".$mandatory_class." id=".$name." name=".$name.">";
								if($res_param['DIRECTORY_LIST'] == 'TB_USERS'){
									$block .= "<option value='all'>Все</option>";
								for($i=0;$i<count($dir_data['ID']);$i++){
									$block .= "<option value='".$dir_data['P_USERNAME'][$i].($dir_data['ID'][$i]==$val?" selected":"")."'>".$dir_data['P_USERNAME'][$i]."</option>";
								}
								$block .= "</select></td></tr>";
								}
								if($res_param['DIRECTORY_LIST'] == 'TB_DICT_CURRENCY'){
								$block .= "<option value='0'>пусто</option>";
								for($i=0;$i<count($dir_data['ID']);$i++){
									$block .= "<option value='".$dir_data['P_CODE'][$i].($dir_data['ID'][$i]==$val?" selected":"")."'>".$dir_data['P_NAME'][$i]."</option>";
								}
								$block .= "</select></td></tr>";
								}
								if($res_param['DIRECTORY_LIST'] == 'TB_DICT_DOCCATEGORY'){
								$block .= "<option value='0'>пусто</option>";								
								for($i=0;$i<count($dir_data['ID']);$i++){
									$block .= "<option value='".$dir_data['P_CODE'][$i].($dir_data['ID'][$i]==$val?" selected":"")."'>".$dir_data['P_LONGNAME'][$i]."</option>";
								}
								$block .= "</select></td></tr>";
								}
								if($res_param['DIRECTORY_LIST'] == 'TB_DICT_DATABASE'){
								$block .= "<option value='0'>пусто</option>";								
								for($i=0;$i<count($dir_data['ID']);$i++){
									$block .= "<option value='".$dir_data['P_CODE'][$i].($dir_data['ID'][$i]==$val?" selected":"")."'>".$dir_data['P_LONGNAME'][$i]."</option>";
								}
								$block .= "</select></td></tr>";
								}
								if($res_param['DIRECTORY_LIST'] == 'TB_DICT_BRANCH'){
								$block .= "<option value=''>пусто</option>";								
								for($i=0;$i<count($dir_data['ID']);$i++){
									$block .= "<option value='".$dir_data['P_CODE'][$i].($dir_data['ID'][$i]==$val?" selected":"")."'>".$dir_data['P_LONGNAME'][$i]."</option>";
								}
								$block .= "</select></td></tr>";
								}
								
								break;
                        }
                    
                    
                
				}
				
				$block .= "</table>";
                $vars['content'] .= $block;
				
				// для формирования отчета № 30
				if ($report_id == '39')  {
					$vars['content'] .= '<input type="submit" class="dl-table-submit" name="op2" onclick= "validate()"   value="' . ('Запустить') . '">';
				}
				else{
					$vars['content'] .= '<input type="submit" class="dl-table-submit" name="op"  value="' . ('Запустить') . '">';
				}
				// По отчету номер 30
				$vars['content'] .= form_close();
                $vars['content'] .= '</fieldset>';
                $vars['content'] .= '<script language="javascript">';
				$vars['content'] .= '
					function validate() {
				 
					if (   $("#P3 ").val() == ""  && $("#P4 ").val() == "" && $("#P5 ").val() == ""  ) {
                        $("#P3 ").addClass("required-field");
                        $("#P4 ").addClass("required-field");
                        $("#P5 ").addClass("required-field");
							alert("Не заполнено одно из обязательных полей Номер счета, Наименование или ИИН  ");
							  return;	  
					}
                    else {
                              $("#P3 ").removeClass("required-field");
                              $("#P4 ").removeClass("required-field");
                              $("#P5 ").removeClass("required-field");

                    }
                } ';
                $vars['content'] .= '</script>';
                
				
				$vars['content'] .= '<fieldset class="viewdata" style="background:transparent;width:800px;margin-top:10px;margin-left:10px;padding:10px"><legend>' . ('Архив отчетов') . '</legend>';
				
                $stid = $this->aml_oracle->execute('SELECT id, p_report_id, p_date, p_username, p_report_body, decode(p_ready,1, \'Да\' , 0, \'Нет\', \'Ошибка\' ) p_ready  ' .
                                                   'FROM TB_REPORT_HISTORY t '.
                                                   'WHERE t.p_report_id = :report_id ' .
                                                   'ORDER BY t.p_date DESC', __LINE__, array(':report_id' => $report_id));
                $rows = array();
                while($r = oci_fetch_array($stid, OCI_ASSOC)) {
                    $rows[] = array(
						
                        'P_DATE'     => $r['P_DATE'],
                        'P_USERNAME' => $r['P_USERNAME'],
						 'P_READY'	=> $r['P_READY'],
                        'P_DL_LINK'  => array('#data' => '<a href="' . site_url('page/save_report_for_xsl/' . $r['ID'] . '/' . $report_id) . '">' . $this->aml_html->img('save.png') . '</a>', '#attributes' => array('style' => 'text-align:center','title' => ('Скачать сформированный отчет'))),
                        'P_DEL_LINK' => array('#data' => '<a href="' . site_url('page/managereports/deletearchive/' . $r['ID']) . '/' . $report_id . '">' . $this->aml_html->img('trash.png') . '</a>', '#attributes' => array('style' => 'text-align:center','onclick' => 'return confirm(\'' . ('Удалить?') . ' \')'))
                    );
                }
				// Обновленный Запуск отчетов 29,05,13
				
                $vars['content'] .= $this->_html_table(array(('Дата'),('Пользователь'),('Готов.'), ('Скачать'), ('Удалить')), $rows);
                $vars['content'] .= '</fieldset>';
                break;
            default:
                $q = "SELECT ID,ORDER_NUMBER,REPORT_NAME FROM TB_REPORTS t ORDER BY t.ORDER_NUMBER";
                $rows = array();
                $stid = $this->aml_oracle->execute($q,__LINE__);
                while($r = oci_fetch_array($stid, OCI_ASSOC | OCI_RETURN_NULLS)) {
                    $rows[] = $r;
                }
                $vars['rows'] = $rows;
                $vars['content'] = $this->load->view('reports/index', $vars, true);
        }
        $this->aml_context->set_general_vars($vars);
        $this->load->view('main', $vars); */
		
		$report_id = intval($p1);
                $noxsl = ($p2 == 'noxsl') ? true : false;
                if ($report_id <= 0) {
                    $this->aml_html->error_page(array(sprintf(('Неверное значение параметра %s'), 'id')));
                }
                $q = 'SELECT * FROM TB_REPORTS t WHERE t.ID = :id';
                $stid = $this->aml_oracle->execute($q, __LINE__, array(':id' => $report_id));
                $report = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_LOBS);
                $result = '';
                $resultset = '<reportdata>';
                $resultset .= "\n";

                $vars['page_name'] = ('Отчет:') . ' ' . $report['REPORT_NAME'];

                $sxml = simplexml_load_string($report['XML_REPORT']);
                if ($this->input->post('op')) {
                	ini_set("max_execution_time",'600');
                    $report_start_date = date($this->config->item('php_date_format'));
                    //if (is_array($sxml->queries->query)) {
						
                        foreach($sxml->queries->query as $q) {
                            $params = array();
                            foreach($sxml->report_parameters->param as $p) {
                                if (strpos(strtoupper($q[0]), ':' . strtoupper($p->name))) {
                                   	
                                   	if($p->datatype=='NUMBER'){
                                   		$params[':' . $p->name] = str_replace(' ','',$params[':' . $p->name]);
                                   	}/*else if($p->datatype=='DATE'){
										$params[':' . $p->name] = 'TO_DATE(\'' . $this->input->post(strtoupper($p->name)) . '\',\'' . $this->config->item('date_format') . '\')';
									}*/else{
										$params[':' . $p->name] = $this->input->post(strtoupper($p->name));
									}
                                }
                            }
                            $att = $q->attributes();
                            $id = trim($att['id']);
                            if (!empty($id)) {
                                $resultset .= '<resultset id="' . $id . '">';
                                $resultset .= "\n";
                            }
							//print"<pre>";die(var_dump($sxml->report_parameters,trim($q), $params));
                            $stid = $this->aml_oracle->execute(trim($q), __LINE__, $params);
                            while($r = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)){
                                $resultset .= '<row>';
                                $resultset .= "\n";
                                foreach($r as $field => $val) {
                                    $resultset .= '<' . $field . '>' . htmlspecialchars('' . $val,ENT_NOQUOTES,'utf-8') . '</' . $field . '>';
                                    $resultset .= "\n";
                                }
                                $resultset .= '</row>';
                                $resultset .= "\n";
                            };

                            if (!empty($id)) {
                                $resultset .= '</resultset>';
                                $resultset .= "\n";
                            }
                        }
                    //}
                    $resultset .= '<report_parameters>';
                    $resultset .= "\n";
                    foreach($sxml->report_parameters->param as $p) {
                        $resultset .= '<parameter id="' . strtoupper($p->name) . '">';
                        $resultset .= '<name>' . $p->title . '</name>';
                        if($p->directory){
                        	$stid = $this->aml_oracle->execute("select P_LONGNAME from ".$p->directory." where P_CODE = '".$this->input->post(strtoupper($p->name))."'", __LINE__);
                        	oci_fetch_all($stid, $result_name);
                        	$resultset .= "<value>".$result_name['P_LONGNAME'][0]."</value>";
                        } else {
                        	$resultset .= '<value>' . htmlspecialchars($this->input->post(strtoupper($p->name)), ENT_NOQUOTES, 'utf-8') . '</value>';
                        }
                        $resultset .= '</parameter>';
                        $resultset .= "\n";
                    }

                    // username
                    $resultset .= '<parameter id="USERNAME">';
                    $resultset .= '<name>' . ('Пользователь') . '</name>';
                    $resultset .= '<value>' . $this->aml_auth->get_username() . '</value>';
                    $resultset .= '</parameter>';
                    $resultset .= "\n";

                    // username
                    $resultset .= '<parameter id="REPORT_DATE">';
                    $resultset .= '<name>' . ('Дата отчета') . '</name>';
                    $resultset .= '<value>' . $report_start_date . '</value>';
                    $resultset .= '</parameter>';
                    $resultset .= "\n";

                    $resultset .= '</report_parameters>';
                    $resultset .= "\n";
                    $resultset .= '</reportdata>';

                    if (!empty($report['XSL_TEXT']) && !$noxsl) {
                        $data = $this->aml_html->apply_xsl_for_xml($report['XSL_TEXT'], $resultset);

                        $bindings = array(
                            ':report_id' => $report_id,
                            ':p_username' => $this->aml_auth->get_username(),
                            ':report_body' => $data
                        );
                        $q = "INSERT INTO tb_report_history (id, p_report_id, p_username, p_report_body) VALUES(GetID(), :report_id, :p_username, :report_body)";
                        $this->aml_oracle->execute($q, __LINE__, $bindings);

                        $this->aml_html->output_file(date($this->config->item('php_date_format')) .  '.xls', $data, 'application/vnd.ms-excel; charset=utf-8');
                    } else {
                        $this->aml_html->output_file(date($this->config->item('php_date_format')) .  '.xml', $resultset, 'application/xml; charset=utf-8');
                    }
                    die();
                }

                $block = '';
                $vars['content'] = '<fieldset class="viewdata" style="background:transparent;width:800px;margin-top:10px;margin-left:10px"><legend>' . ('Параметры отчета') . '</legend>';
                $vars['content'] .= form_open('page/managereports/run/' . $report_id . ($noxsl ? '/noxsl' : ''),array('class' => 'check-required-field-form','style' => 'padding:10px'));


                if($sxml->report_parameters->param != null) {
                	$block = '<table cellspacing="0" cellpadding="5">';
                    foreach($sxml->report_parameters->param as $p) {
                        $name = strtoupper((string)($p->name));
                        $title = htmlspecialchars($p->title, ENT_QUOTES, 'utf-8');
                        $mandatory_class = ($p->required == '1') ? ' required-field' : '';
                        $mandatory_marker = ($p->required == '1') ? '<span style="color:red;font-weight:bold">* </span>' : '';
                        $val = htmlspecialchars($this->input->post($name), ENT_QUOTES, 'utf-8');

                        switch(strtoupper($p->datatype)) {
                            case 'NUMBER':
                                $block .= <<<ENDL
                                <tr><td><label for="{$name}">{$mandatory_marker} {$title}</label></td>
                                <td><input size="50" class="numeric{$mandatory_class}" id="{$name}" name="{$name}" value="{$val}" type="text"></td></tr>
ENDL;
                                break;
                            case 'DATE':
                                $block .= <<<ENDL
                                <tr><td><label for="{$name}">{$mandatory_marker} {$title}</label></td>
                                <td><input size="50" class="datepicker{$mandatory_class}" id="{$name}" name="{$name}" value="{$val}" type="text"></td></tr>
ENDL;
                                break;
                            case 'VARCHAR2': case 'VARCHAR':
                                $block .= <<<ENDL
                                <tr><td><label for="{$name}">{$mandatory_marker} {$title}</label></td>
                                <td><input size="50" class="{$mandatory_class}" id="{$name}" name="{$name}" value="{$val}" type="text"></td></tr>
ENDL;
								break;
							case 'LIST':
							
								$stid = $this->aml_oracle->execute("select * from ".$p->directory." order by p_code asc",__LINE__);
								oci_fetch_all($stid, $dir_data);
								$block .= "<tr><td><label for=".$name.">".$mandatory_marker." ".$title."</label></td><td>";
								$block .= "<select style='width:290px;' class=".$mandatory_class." id=".$name." name=".$name.">";
								for($i=0;$i<count($dir_data['ID']);$i++){
									$block .= "<option value='".$dir_data['P_CODE'][$i].($dir_data['P_CODE'][$i]==$val?" selected":"")."'>".$dir_data['P_LONGNAME'][$i]."</option>";
								}
								$block .= "</select></td></tr>";
								break;
                        }
                    }
                    $block .= "</table>";
                }
                $vars['content'] .= $block;
                $vars['content'] .= '<input type="submit" class="dl-table-submit" name="op" value="' . ('Запустить') . '">';
                $vars['content'] .= form_close();
                $vars['content'] .= '</fieldset>';

                $vars['content'] .= '<fieldset class="viewdata" style="background:transparent;width:800px;margin-top:10px;margin-left:10px;padding:10px"><legend>' . ('Архив отчетов') . '</legend>';

                $stid = $this->aml_oracle->execute('SELECT id, p_report_id, p_date, p_username, p_report_body ' .
                                                   'FROM TB_REPORT_HISTORY t '.
                                                   'WHERE t.p_report_id = :report_id ' .
                                                   'ORDER BY t.p_date DESC', __LINE__, array(':report_id' => $report_id));
                $rows = array();
                while($r = oci_fetch_array($stid, OCI_ASSOC)) {
                    $rows[] = array(
                        'P_DATE'     => $r['P_DATE'],
                        'P_USERNAME' => $r['P_USERNAME'],
                        'P_DL_LINK'  => array('#data' => '<a href="' . site_url('page/managereports/download/' . $r['ID']) . '">' . $this->aml_html->img('save.png') . '</a>', '#attributes' => array('style' => 'text-align:center','title' => ('Скачать сформированный отчет'))),
                        'P_DEL_LINK' => array('#data' => '<a href="' . site_url('page/managereports/deletearchive/' . $r['ID']) . '/' . $report_id . '">' . $this->aml_html->img('trash.png') . '</a>', '#attributes' => array('style' => 'text-align:center','onclick' => 'return confirm(\'' . ('Удалить?') . ' \')'))
                    );
                }
                $vars['content'] .= $this->_html_table(array(('Дата'),('Пользователь'),('Скачать'), ('Уд.')), $rows);
                $vars['content'] .= '</fieldset>';

                break;
            default:
                $q = "SELECT ID,ORDER_NUMBER,REPORT_NAME FROM TB_REPORTS t ORDER BY t.ORDER_NUMBER";
                $rows = array();
                $stid = $this->aml_oracle->execute($q,__LINE__);
                while($r = oci_fetch_array($stid, OCI_ASSOC | OCI_RETURN_NULLS)) {
                    $rows[] = $r;
                }
                $vars['rows'] = $rows;
                $vars['content'] = $this->load->view('reports/index', $vars, true);
        }
        $this->aml_context->set_general_vars($vars);
        $this->load->view('main', $vars);
		
    }

	function save_report_for_xsl ($id, $report_id) {
		$q = "SELECT * FROM TB_REPORT_HISTORY WHERE  P_REPORT_ID = :rep_id AND P_READY = 1 AND P_XSL_READY = 0 AND ID = :id";
		$bind = array(':rep_id'=>$report_id, ':id'=>$id);
		$stid = $this->aml_oracle->execute($q, __LINE__, $bind);
		$i = 0;
		
		while($r = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_LOBS)) {
			$q_xsl = "SELECT * FROM TB_REPORTS WHERE ID = :rep_id";
			$bind2 = array(':rep_id'=>$report_id);
			$stid_xsl = $this->aml_oracle->execute($q_xsl, __LINE__, $bind2);
			$r_xsl = oci_fetch_array($stid_xsl, OCI_ASSOC + OCI_RETURN_LOBS);
			if(!$r_xsl['XSL_TEXT'])
			{
				continue;
			}
			
			$data = $this->aml_html->apply_xsl_for_xml($r_xsl['XSL_TEXT'], $r['P_REPORT_BODY']);
			//die(var_dump($r['P_REPORT_BODY']));
			$bindings = array(
				':report_body' => $data,
				':rep_hist_id' => $id
			);
			$q = "UPDATE TB_REPORT_HISTORY  SET P_REPORT_BODY = :report_body, P_XSL_READY = 1 WHERE ID = :rep_hist_id";
			$stid_res = $this->aml_oracle->execute($q, __LINE__, $bindings);
		}
		
		header("Location:" . site_url('page/managereports/download/' . $id));
	}
	
    function monitoring() {
        $this->aml_auth->check_auth(); // checkauth
        $can_do = $this->aml_security->check_privilege(11) || $this->aml_security->check_privilege(56) || $this->aml_security->check_privilege(91);
        if (!$can_do) {
            $this->aml_html->error_page(array(('Отсутствуют права для данного действия.')));
        }

        $this->_suspicious_operations(
            array(
                'active_link'  => 'monitoring',
                'dataurl'      => site_url('page/datasource/monitoring'),
                'show_sources' => true,
                'uisection'    => 'monitoring',
                'page_name'    => ('Операции, подлежащие ФМ')
            )
        );
    }

    // операции на вкладке подозрительные
    function suspicious() {
        $this->aml_auth->check_auth(); // checkauth
        //$c = $this->_get_connection();
        $can_do = $this->aml_security->check_privilege(12) || $this->aml_security->check_privilege(56) || $this->aml_security->check_privilege(91);
        if (!$can_do) {
            $this->aml_html->error_page(array(('Отсутствуют права для данного действия.')));
        }
        $this->_suspicious_operations(
            array(
                'active_link'  => 'suspicious',
                'dataurl'      => site_url('page/datasource/suspicious'),
                'show_sources' => true,
                'uisection'    => 'suspicious',
                'page_name'    => ('Подозрительные операции')
            )
        );
    }

    function _suspicious_operations($params = array()) {
        $vars['content'] = $this->load->view('monitoring', array(), true);
        $vars['page_name'] = $params['page_name'];
        $dataurl         = $params['dataurl'];     // !!! строка не должна содержать символы " или '
        $editurl         = site_url('page/edit/suspicious');           // !!! строка не должна содержать символы " или '
        $previewurl         = site_url('page/viewtbl/suspicious');           // !!! строка не должна содержать символы " или '

        $sendurl         = site_url('page/sendtosdfo');                // !!! строка не должна содержать символы " или '
        $sendkfmurl      = site_url('page/sendtokfm');                 // !!! строка не должна содержать символы " или '
        $viewdataurl     = site_url('page/viewsourceoperations');      // !!! строка не должна содержать символы " или '
        $extrainfourl    = site_url('page/viewextrainfo');             // !!! строка не должна содержать символы " или '
        $archiveurl      = site_url('page/archive/online');            // !!! строка не должна содержать символы " или '
        $undo_archiveurl = site_url('page/archive/online_restore');

        /* 1 таблица = TB_SUSPICIOUSOPERATIONS */
	
        list($jqgrid_titles1, $jqgrid_models1) = $this->aml_metainfo->get_js_table_properties('TB_SUSPICIOUSOPERATIONS', 0, 0);
        /* 2 таблица = TB_SUSPICIOUSMEMBERS */
        list($jqgrid_titles2, $jqgrid_models2) = $this->aml_metainfo->get_js_table_properties('TB_SUSPICIOUSMEMBERS');
        /* 3 таблица = TB_SUSPICIOUSFOUNDERS */
        list($jqgrid_titles3, $jqgrid_models3) = $this->aml_metainfo->get_js_table_properties('TB_SUSPICIOUSFOUNDERS');

        $uisettings = $this->native_session->userdata('ui.' . $params['uisection']);
        if (isset($uisettings['per_page']) && intval($uisettings['per_page']) > 0) {
            $per_page = intval($uisettings['per_page']);
        } else {
            $per_page = $this->per_page;
        }

        if (isset($params['show_sources']) && $params['show_sources']) {
            $sources = '<input type="button" id="t_grid1_btn_source" value="' . ('Источники') . '">';
        } else {
            $sources = '';
        }

        $status_str = ('Статус');
        $from_str = ('Период с');
        $till_str = ('по');
        $apply_str = ('Установить');
        $reason_must_present_str = ('Причина должна быть обязательно указана!');

        $grid_dbl_click = '';
        $grid1detail_dbl_click = '';
        $grid2detail_dbl_click = '';
        $to_sdfo_str = ('В СДФО');
        $to_archive = ('В архив');
        $from_archive = ('Из архива');
        $to_kfm = ('В КФМ');
        $move_to_prepared_str = ('На отправку');
        $add_operation_str = ('Добавить операцию');
        $fm1_str = ('ФМ-1');
		$fm1_str_copy = ('Создать копию ФМ-1');//15251													   
        if($params['uisection']=='monitoring'){
        	$suspic_kind = 1;
        } else if ($params['uisection']=='suspicious'){
        	$suspic_kind = 2;
        }

//            jQuery('#t_grid1').append('<div style="margin-top:1px"><input type="button" id="btn_add_operation" value=" + " title="{$add_operation_str}"><input type="button" id="btn_fm1" value="${fm1_str}"><input type="button" id="btn_move_to_prepared" value="{$move_to_prepared_str}"><input type="button" id="t_grid1_btn_archive" value="{$to_archive}"><input type="button" style="display:none" id="t_grid1_btn_undo_archive" value="{$from_archive}">{$to_kfm_button}{$sources}<input type="button" id="t_grid1_btn_extrainfo" value="{$params['active_link']}"></div>');
if ($this->aml_security->check_privilege(91)){
	$toolbar_buttons = <<<ENDLTOOLB
            jQuery('#t_grid1').append('<div style="margin-top:1px">&nbsp;&nbsp;&nbsp;{$sources}&nbsp;&nbsp;&nbsp;<input type="button" id="select_period" onclick="select_period();" value="Период: {$uisettings['date_from']} - {$uisettings['date_until']}"></div>');
            jQuery('.left_col_bottom_content').append('<center style="font-weight:bold;padding-bottom:5px;">Период</center>{$from_str}: <input type="text" class="datepicker" id="date_from" value="{$uisettings['date_from']}"><br/>{$till_str} <input type="text" class="datepicker" id="date_until" value="{$uisettings['date_until']}"><input type="button" value="{$apply_str}" id="fix_date" val="{$params['active_link']}" onclick="set_search_dates(this);" style="width:90px;">&nbsp;&nbsp;&nbsp;<input type="button" onclick="cancel_select_period()" value="Отмена" style="width:90px;" />');
ENDLTOOLB;
}
else if($this->aml_security->check_privilege(97))
{	
	@$toolbar_buttons = <<<ENDLTOOLB
            jQuery('#t_grid1').append('<div style="margin-top:1px"><input type="button" id="btn_add_operation" value=" + " title="{$add_operation_str}" suspic_kind="{$suspic_kind}">&nbsp;&nbsp;&nbsp;<input type="button" id="btn_fm1" value="${fm1_str}"><input type="button" id="btn_fm1_copy" value="${fm1_str_copy}"><input type="button" style="display:none" id="t_grid1_btn_undo_archive" value="{$from_archive}">{$to_kfm_button}{$sources}&nbsp;&nbsp;&nbsp;<input type="button" id="select_period" onclick="select_period();" value="Период: {$uisettings['date_from']} - {$uisettings['date_until']}"></div>');
            jQuery('.left_col_bottom_content').append('<center style="font-weight:bold;padding-bottom:5px;">Период</center>{$from_str}: <input type="text" class="datepicker" id="date_from" value="{$uisettings['date_from']}"><br/>{$till_str} <input type="text" class="datepicker" id="date_until" value="{$uisettings['date_until']}"><input type="button" value="{$apply_str}" id="fix_date" val="{$params['active_link']}" onclick="set_search_dates(this);" style="width:90px;">&nbsp;&nbsp;&nbsp;<input type="button" onclick="cancel_select_period()" value="Отмена" style="width:90px;" />');
ENDLTOOLB;
}
else{
        @$toolbar_buttons = <<<ENDLTOOLB
            jQuery('#t_grid1').append('<div style="margin-top:1px"><input type="button" id="btn_add_operation" value=" + " title="{$add_operation_str}" suspic_kind="{$suspic_kind}"><input type="button" id="btn_move_to_prepared" value="{$move_to_prepared_str}">&nbsp;&nbsp;&nbsp;<input type="button" id="btn_fm1" value="${fm1_str}"><input type="button" id="btn_fm1_copy" value="${fm1_str_copy}"><input type="button" style="display:none" id="t_grid1_btn_undo_archive" value="{$from_archive}">{$to_kfm_button}{$sources}&nbsp;&nbsp;&nbsp;<input type="button" id="select_period" onclick="select_period();" value="Период: {$uisettings['date_from']} - {$uisettings['date_until']}"><input type="button" id="t_grid1_btn_archive" value="{$to_archive}"></div>');
            jQuery('.left_col_bottom_content').append('<center style="font-weight:bold;padding-bottom:5px;">Период</center>{$from_str}: <input type="text" class="datepicker" id="date_from" value="{$uisettings['date_from']}"><br/>{$till_str} <input type="text" class="datepicker" id="date_until" value="{$uisettings['date_until']}"><input type="button" value="{$apply_str}" id="fix_date" val="{$params['active_link']}" onclick="set_search_dates(this);" style="width:90px;">&nbsp;&nbsp;&nbsp;<input type="button" onclick="cancel_select_period()" value="Отмена" style="width:90px;" />');
ENDLTOOLB;
}
        $select_one_operation_str = ('Выберите 1 операцию, для просмотра источников!');
        $archive_reason_str = ("Причина добавления в архив?");

        $vars['run_js'] = <<<ENDL
        var preview_url = '{$previewurl}';
        var lastsel2;

        jQuery(document).ready(function() {
            jQuery.extend(jQuery.jgrid.edit, { viewPagerButtons : false });
            jQuery.extend(jQuery.jgrid.search, { sopt : ['eq','ne', 'lt', 'le', 'gt', 'ge','bw'] });

            grid1width = jQuery('#page').width();
            divider = 1.9;
            screenHeight = screen.height;

            if(screenHeight <= 720) {
                divider = 3;
            } else if (screenHeight <= 800){
                divider = 1.8;
            } else if (screenHeight <= 900){
                divider = 2.1;
            } else if (screenHeight <= 960){
                divider = 2;
            } else if (screenHeight <= 1024){
                divider = 1.47;
            }
            gridheight = screenHeight / divider;

            jQuery("#grid1").jqGrid({
                url:'{$dataurl}',
                datatype: "xml",
                mtype: 'post',
                autoencode: true,
                colNames:[{$jqgrid_titles1}],
                colModel:[{$jqgrid_models1}],
                rowNum: {$per_page},
                rowList:[10,20,50,100, 500],
                pager: '#grid1pager',
                viewrecords: true,
                //caption: "{$params["page_name"]}",
                multiselect: true, /* - для грида мониторинга*/
                multiboxonly: true,
                /*autowidth:true,*/
                shrinkToFit:false,
                ondblClickRow: function (rowid, iRow, iCol, e) {
                    var newWnd = window.open('{$editurl}/' + rowid,'_blank');
                    newWnd.focus();
                },
                resizeStop: function (newwidth, index) {
                    jQuery.get('{$this->savecolpropurl}/suspicious/' + index + '/width/' + newwidth);
                },
                /*onSortCol: function (index,iCol,sortorder) {
                    jQuery.get('{$this->savecolpropurl}/suspicious/' + index + '/sortname/' + index);
                    jQuery.get('{$this->savecolpropurl}/suspicious/' + index + '/sortorder/' + sortorder);
                },*/
                width: grid1width,
                height: gridheight,
                toolbar: [true,"top"]
            }).navGrid('#grid1pager',{add:false, del:false, edit:false},{},{},{},{multipleSearch:true});


            jQuery("#grid1").jqGrid('gridResize', {});

            {$toolbar_buttons}

            jQuery('#btn_send_sdfo').click(function () {
                var selectedRecords = jQuery('#grid1').jqGrid('getGridParam','selarrrow');
                jQuery.post('{$sendurl}',{records: selectedRecords.join(',')}, function(data) {
                    eval(data);
                });
            });

            jQuery('#btn_send_kfm').click(function () {
                var selectedRecords = jQuery('#grid1').jqGrid('getGridParam','selarrrow');
                jQuery.post('{$sendkfmurl}',{records: selectedRecords.join(',')}, function(data) {
                    eval(data);
					$('.jq-grid').trigger('reloadGrid');
                });
            });
			
            jQuery('#t_grid1_btn_source').click(function () {
                var selectedRecords = jQuery('#grid1').jqGrid('getGridParam','selarrrow');
                if (selectedRecords.length > 1) {
                    alert('{$select_one_operation_str}');
                } else if(selectedRecords.length == 0) {
                    alert('Необходимо выбрать запись!');
                    return;
                } else {
                    var newWindow = window.open('{$viewdataurl}/' + selectedRecords[0],'_blank');
                    newWindow.focus();
                }
            });

            jQuery('#t_grid1_btn_extrainfo').click(function () {
                var selectedRecords = jQuery('#grid1').jqGrid('getGridParam','selarrrow');
                if (selectedRecords.length > 1) {
                    alert('{$select_one_operation_str}');
                } else if(selectedRecords.length == 0) {
                    alert('Необходимо выбрать запись!');
                    return;
                } else {
                    var newWindow = window.open('{$extrainfourl}/' + selectedRecords[0],'_blank');
                    newWindow.focus();
                }
            });

            jQuery('#t_grid1_btn_archive').click(function () {
                var selectedRecords = jQuery('#grid1').jqGrid('getGridParam','selarrrow');
                if(selectedRecords.length == 0){
                    alert('Необходимо выбрать запись!');
                    return;
                }

                reason = prompt("{$archive_reason_str}");
                if (reason.length) {
                    var selectedRecords = jQuery('#grid1').jqGrid('getGridParam','selarrrow');
                    jQuery.post('{$archiveurl}', {records: selectedRecords.join(','), 'reason': reason }, function(data) {
                        try {
                            eval(data);
                        } catch(e) {
                            alert('Error dispatching json answer. ' + e + data);
                        }
                    });
                } else {
                    alert("{$reason_must_present_str}");
                }
            });

            jQuery('#t_grid1_btn_undo_archive').click(function () {
                var selectedRecords = jQuery('#grid1').jqGrid('getGridParam','selarrrow');
                jQuery.post('{$undo_archiveurl}', {records: selectedRecords.join(',')}, function(data) {
                    try {
                        eval(data);
                    } catch(e) {
                        alert('Error dispatching json answer. ' + e + data);
                    }
                });

            });

            jQuery('#setMonitoringPrefs').click(function () {
                jQuery.post('{$this->savesettingsurl}',
                    { uisection : '{$params['uisection']}',
                      status: jQuery('#status').val(),
                      date_from: jQuery('#date_from').val(),
                      date_until: jQuery('#date_until').val(),
                      per_page : jQuery("#grid1").jqGrid('getGridParam','rowNum')
                    },function(data) {
                        eval(data);
                    }
                );
            });

            $('#btn_move_to_prepared').click(function () {
                var selectedRecords = jQuery('#grid1').jqGrid('getGridParam','selarrrow');
                if(selectedRecords.length == 0){
                    alert('Необходимо выбрать запись!');
                    return;
                }

                var selectedRecords = jQuery('#grid1').jqGrid('getGridParam','selarrrow');
                $.post('{$this->preparedurl}',{records: selectedRecords.join(',')},
                function(data) {
                    eval(data);
                });
            });

            $('#btn_fm1').click(function () {
                var selectedRecords = jQuery('#grid1').jqGrid('getGridParam','selarrrow');
                if(selectedRecords.length == 0){
                    alert('Необходимо выбрать запись!');
                    return;
                }
                newWnd = window.open(base_url + '/operations/fm1/' + selectedRecords[0]);
                newWnd.focus();
            });
			
            $('#btn_fm1_copy').click(function () {
                var selectedRecords = jQuery('#grid1').jqGrid('getGridParam','selarrrow');
                if(selectedRecords.length == 0){
                    alert('Необходимо выбрать запись!');
                    return;
                }
                newWnd = window.open(base_url + '/page/fm1_copy/' + selectedRecords[0]);
                newWnd.focus();
            });	   

            if ($('#status').val() == 4) {
                $('#t_grid1_btn_undo_archive').show();
                $('#t_grid1_btn_archive').hide();
            } else {
                $('#t_grid1_btn_undo_archive').hide();
                $('#t_grid1_btn_archive').show();
            }

            $('#status').change(function () {
                if ($('#status').val() == 4) {
                    $('#t_grid1_btn_undo_archive').show();
                    $('#t_grid1_btn_archive').hide();
                } else {
                    $('#t_grid1_btn_undo_archive').hide();
                    $('#t_grid1_btn_archive').show();
                }
            });
			
			
        });
ENDL;
		$ui = "ui.".$params['uisection'];
		$vars['left_col'] = true;
		$cur_status = isset($_SESSION[$ui]['status'])? $_SESSION[$ui]['status']:0;
		$cur_date_from = isset($_SESSION[$ui]['date_from'])? $_SESSION[$ui]['date_from']:"";
		$cur_date_until = isset($_SESSION[$ui]['date_until'])? $_SESSION[$ui]['date_until']:"";

		$statuses = $this->aml_context->html_get_statuses($params['uisection'],'status',true);
		$vars['left_col_content'] = "";
        foreach($statuses as $status_val){
			$vars['left_col_content'] .= "<span class='status_title'>".$status_val['0_0']."</span><ul class='status_selector'>";
			unset($status_val['0_0']);
			foreach($status_val as $sub_key=>$sub_val){
				$vars['left_col_content'] .= "<li val='".$sub_key."'".($cur_status==$sub_key?" class='active'":"").">".$sub_val." [<span>0</span>]</li>";
			}
	        $vars['left_col_content'] .= "</ul>";
        }
/*
        $vars['left_col_content'] = "<span class='status_title'>".('Статус записи')."</span><ul class='status_selector'>";
        foreach($statuses as $status_key => $status_val){
			$vars['left_col_content'] .= "<li val='".$status_key."'".($cur_status==$status_key?" class='active'":"").">".$status_val." [<span>0</span>]</li>";
        }
        $vars['left_col_content'] .= "</ul>";
*/
        $this->aml_context->set_general_vars($vars);
        $this->load->view('main', $vars);
    }
	
//14072begin
    function fm1_copy($operation_id = 0) {
        $this->aml_auth->check_auth();
        $operation_id = floatval($operation_id);		
         $q = <<<ENDL
            begin
                :result := twooperationscopy(old_oid => :p_operation_id);			 
            end;
ENDL;
        $result = 0;
        $bindings = array(
            ':result'         => &$result,
            ':p_operation_id' => &$operation_id
        );		
        $stid = $this->aml_oracle->execute($q, __LINE__, $bindings);
        if ($result == 1) {
			header('Location: ' . site_url('page/suspicious'));
            die();
        } else {
            $this->aml_html->error_page(array(('Ошибка при дублирование операции')));
        }
    }
//14072end 

    function createsuspop($suspic_kind){
        if (!$this->aml_auth->is_authorized()) {
            header('Location: ' . site_url(''));
            die();
        }
        $can_do = $this->aml_security->check_privilege(11) || $this->aml_security->check_privilege(12);
        if (!$can_do) {
            $this->aml_html->error_page(array(('Отсутствуют права для данного действия.')));
        }
        $suspic_kind *= 1;
        $rec_id = 9999999999;
        $q = "BEGIN " .
             "   :rec_id := GetId(); " .
             "   INSERT INTO TB_SUSPICIOUSOPERATIONS(ID,P_BANKOPERATIONID,P_OPERATIONDATETIME,P_USERNAME,P_SUSPIC_KIND) VALUES(:rec_id, ' ', sysdate,:uname,:suspic_kind); " .
			 "END;\n";
        $this->aml_oracle->execute($q, __LINE__, array(':rec_id' => &$rec_id, ':uname' => $this->aml_auth->get_username(), ":suspic_kind"=>$suspic_kind));
        header('Location: ' . site_url('page/edit/suspicious/' . $rec_id));
    }

    function login() {
		
		
		 
        if ($this->aml_auth->is_authorized()) {
            header('Location: ' . site_url(''));
            die();
        }
        $tdata = array();
        $nrows = 0;
		/*Добавил Адилет 23.07.2018 по заявке 9942 заблокировать пользователя если он не заходил 100 дней*/
	//	$q = "UPDATE TB_USERS t SET t.P_LOCKED_BOOL = 1, T.P_LAST_LOGIN_DATE = SYSDATE WHERE SYSDATE-T.P_LAST_LOGIN_DATE >= 100 and t.P_LOCKED_BOOL= 0";
//		$stid = $this->aml_oracle->execute($q, __LINE__, null);
		/******************************************************************************************************/
        if($this->input->post('op')) {
			
			

            

            $username = $this->input->post('login');
            $login = mb_strtoupper($username,'utf8');
            $password = $this->input->post('password');
            if (mb_strlen($password) == 0) {
                $this->native_session->set_flashdata('emsg', array(('Поле пароль обязательно к заполнению')));
                header('Location: ' . site_url('page/login'));
                die();
            }

           


            if($this->config->config['auth_type']=='local' || in_array($login,explode(',',$this->config->config['omniadmin_users']))){
	            if ($this->config->item('aes_enable') == 1) {
	                $q = "SELECT t.*, CASE WHEN t.p_pwd_changedate < SYSDATE - " . floatval($this->config->item('password_expire')) . " AND P_PWD_NEVER_EXPIRE_BOOL = 0 THEN 1 ELSE 0  END password_expired  FROM tb_users t WHERE UPPER(p_username) = UPPER(:v_username) AND P_DELETED_DATE IS NULL";
	                
             

                $stid = $this->aml_oracle->execute($q, __LINE__, array(':v_username' => $login/*, ':v_password' => $aes_pass*/));
	               
               
                   
                    $nrows = oci_fetch_all($stid, $results);

                 

	                if ($nrows > 0) {


                       

                        $decrypted_pass = $this->aml_aes->decrypt_str(pack("H*", $results['P_PASSWORD'][0]));

                        
	                    try {
	                       

                           

	                    } catch(Exception $e){
	                        $nrows = 0;
	                    }
	                    if ($decrypted_pass != $password) {
	                        $nrows = 0;
	                    }
	                }

                

	            } else {
	                $q = "SELECT t.*, CASE WHEN t.p_pwd_changedate < SYSDATE - " . floatval($this->config->item('password_expire')) . " AND P_PWD_NEVER_EXPIRE_BOOL = 0 THEN 1 ELSE 0  END password_expired  FROM tb_users t WHERE UPPER(p_username) = UPPER(:v_username) AND P_DELETED_DATE IS NULL";
	                $stid = $this->aml_oracle->execute($q, __LINE__, array(':v_username' => $login, ':v_password' => $password));
	                $nrows = oci_fetch_all($stid, $results);
	            }
            } else if ($this->config->config['auth_type']=='ldap'){
            	if($this->config->config['ldap_ssl']){
            		putenv('LDAPTLS_REQCERT=never');
            	}
            	$ldap = @ldap_connect("ldap".($this->config->config['ldap_ssl']?"s":"")."://".$this->config->config['ldap_host'],$this->config->config['ldap_port']);
            	if(!$ldap){
                    $this->native_session->set_flashdata('emsg', array(('Невозможно подключиться к LDAP-серверу')));
                    header('Location: ' . site_url('page/login'));
            	} else {
	            	ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
					ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);

					if((strpos($username,'@')*1) <= 1){
						$username .= $this->config->config['ldap_domain'];
					}

 					$bind = @ldap_bind($ldap,$username,$password);
					if ($bind){
						$login = mb_substr($login, 0, strpos($username, '@')*1, 'utf-8');
						$username = mb_substr($username, 0, strpos($username, '@')*1, 'utf-8');
		                $q = "SELECT * FROM tb_users t WHERE p_username = :v_username";
		                $stid = $this->aml_oracle->execute($q, __LINE__, array(':v_username' => $login));
		                $nrows = oci_fetch_all($stid, $results);
		                if($nrows==0){
		                	$filter = str_replace('%username%',$username,$this->config->config['ldap_filter']);
							$result = ldap_search($ldap,$this->config->config['ldap_base'],$filter);
							$result_ent = ldap_get_entries($ldap,$result);
							if($result_ent['count']==0){
			                    $this->native_session->set_flashdata('emsg', array(('Пользователь не найден')));
			                    header('Location: ' . site_url('page/login'));
							} else {
								$user_id = 9999999999;
			                	$q = "BEGIN
			                	:user_id := GetID();
			                	insert into TB_USERS (ID, P_USERNAME, P_FIRSTNAME, P_SECONDNAME, P_EMAIL) values
			                			(:user_id, '".$login."',
			                			'".$result_ent[0][$this->config->config['ldap_field']['name']][0]."',
			                			'".$result_ent[0][$this->config->config['ldap_field']['surname']][0]."',
			                			'".$result_ent[0][$this->config->config['ldap_field']['email']][0]."'); END;";
								$this->aml_oracle->execute($q,__LINE__, array(':user_id'=>&$user_id));
				                $q = "SELECT * FROM tb_users t WHERE UPPER(p_username) = UPPER(:v_username)";
				                $stid = $this->aml_oracle->execute($q, __LINE__, array(':v_username' => $login));
				                $nrows = oci_fetch_all($stid, $results);
			                }
			                if($this->config->config["ldap_import_groups"]){
								$groups = $result_ent[0][$this->config->config["ldap_field"]["group"]];
								$new_groups = array();
								for($i=0;$i<$groups['count'];$i++){
									$group = explode('=',$groups[$i]);
									$group = explode(',',$group[1]);
									$new_groups[] = mb_strtoupper($group[0],'utf-8');
								}
								if(count($new_groups)){
									$this->aml_oracle->execute("
										insert into tb_user_groups (id_user, id_group)
											(select '".$user_id."', rg.id
											 from tb_role_groups rg
											 where rg.p_name in ('".implode("','",$new_groups)."')
											)"
										, __LINE__, null);
								}
			                }
		                } else if ($results['P_DELETED_DATE'][0]!=''){
		                    $this->native_session->set_flashdata('emsg', array(('Ваша учетная запись заблокирована, обратитесь к администратору')));
		                    header('Location: ' . site_url('page/login'));
		                } else if($this->config->config["ldap_import_groups"]) {
		                	$filter = str_replace('%username%',$username,$this->config->config['ldap_filter']);
							$result = ldap_search($ldap,$this->config->config['ldap_base'],$filter);
							$result_ent = ldap_get_entries($ldap,$result);

							$groups = $result_ent[0][$this->config->config["ldap_field"]["group"]];
							$new_groups = array();
							for($i=0;$i<$groups['count'];$i++){
								$group = explode('=',$groups[$i]);
								$group = explode(',',$group[1]);
								$new_groups[] = mb_strtoupper($group[0], 'utf-8');
							}
							if(count($new_groups)){
								$this->aml_oracle->execute("
									delete from tb_user_groups
									where id_user='".$results['ID'][0]."'
										and id_group not in
											(select id from tb_role_groups
											 where p_type='0'
											 	or p_name in
											 		('".implode("','",$new_groups)."')
											 )"
									, __LINE__, null, false);
								$this->aml_oracle->execute("
									insert into tb_user_groups (id_user, id_group)
										(select '".$results['ID'][0]."', rg.id
										 from tb_role_groups rg
										 where not exists
										 	(select 1 from tb_user_groups UG
										 	 where UG.ID_GROUP = rg.id
										 	 	and ug.id_user = '".$results['ID'][0]."'
										 	 )
									 	 	and rg.p_name in ('".implode("','",$new_groups)."')
										)"
									, __LINE__, null, false);
							} else {
								$this->aml_oracle->execute("delete from tb_user_groups where id_user='".$results['ID'][0]."' and id_group not in (select id from tb_role_groups where p_type='0')");
							}
		                }
 						$this->aml_oracle->commit();
					} else {
	                    $this->native_session->set_flashdata('emsg', array(('Неверные имя пользователя и пароль')));
	                    header('Location: ' . site_url('page/login'));
					}
				}
			}

            if ($nrows) {

            
				//Добавил Адилет по заявке 9942 11.09.2018
				if ($results['P_LOCKED_BOOL'][0] == 1) {
                    $this->native_session->set_flashdata('emsg', array(('Ваша учетная запись заблокирована, обратитесь к администратору')));
                    header('Location: ' . site_url('page/login'));
                    die();
                }
				
					
                /***************************************************/
                $authdata = array($results['ID'][0],
                                  $results['P_USERNAME'][0],
                                  $results['P_REQUIRE_PWD_RESET_BOOL'][0],
                                  $results['PASSWORD_EXPIRED'][0],
                                  $results['P_BRANCHLIST'][0],
                                  $results['P_SECONDNAME'][0] . ' ' . $results['P_FIRSTNAME'][0] . ' ' . $results['P_MIDDLENAME'][0]
                );
                $this->aml_auth->set_authorized($authdata);
				
			

                $q = 'UPDATE TB_USERS t SET t.P_INVALID_PWD_TRIES = 0 WHERE t.id = :id';
               // $this->aml_oracle->execute($q, __LINE__, array(':id' => $results['ID'][0]));
	
                $uisettings = array(
                    'status'     => 1,
                    'date_from'  => date($this->config->item('php_date_format'), time() - 3600 * 24 * 7),
                    'date_until' => date($this->config->item('php_date_format'), time())
                );
                // дефолтовые значения фильтров для только что залогинившихся юзеров
                $this->native_session->set_userdata('ui.online', $uisettings);

                $uisettings = array(
                    'status'     => 2,
                    'date_from'  => date($this->config->item('php_date_format'), time() - 3600 * 24 * 7),
                    'date_until' => date($this->config->item('php_date_format'), time())
                );
                $this->native_session->set_userdata('ui.offline', $uisettings);
                $this->native_session->set_userdata('ui.audit', $uisettings);

                $uisettings = array(
                    'status'     => '2_1',
                    'date_from'  => date($this->config->item('php_date_format'), time() - 3600 * 24 * 7),
                    'date_until' => date($this->config->item('php_date_format'), time())
                );
                $this->native_session->set_userdata('ui.monitoring', $uisettings);
                $this->native_session->set_userdata('ui.suspicious', $uisettings);
                $this->native_session->set_userdata('ui.archive', $uisettings);

                $uisettings = array(
                    'status'     => 0,
                    'date_from'  => date($this->config->item('php_date_format'), time() - 3600 * 24 * 7),
                    'date_until' => date($this->config->item('php_date_format'), time())
                );
                $this->native_session->set_userdata('ui.kfm_log', $uisettings);

                $uisettings = array(
                    'status'     => '8_1',
                    'date_from'  => date($this->config->item('php_date_format'), time() - 3600 * 24 * 7),
                    'date_until' => date($this->config->item('php_date_format'), time())
                );
                $this->native_session->set_userdata('ui.operations_for_kfm', $uisettings);
/*
                $uisettings = array(
                    'status'     => 2,
                    'date_from'  => date($this->config->item('php_date_format'), time() - 3600 * 24 * 7),
                    'date_until' => date($this->config->item('php_date_format'), time())
                );
*/
                $this->native_session->set_userdata('ui.clients_view', array('status' => 3));
                $this->native_session->set_userdata('ui.admcontrol', array('status' => 0));
                $this->native_session->set_userdata('ui.audit_dict', array('status' => 0));
                $this->native_session->set_userdata('ui.admcontrol_dir', array('status' => 0));

                $uisettings = array(
                    'status'     => 1,
                    'date_from'  => date($this->config->item('php_date_format'), time() - 3600 * 24 * 7),
                    'date_until' => date($this->config->item('php_date_format'), time())
                );
                $this->native_session->set_userdata('ui.kfm_extra', $uisettings);

                $uisettings = array(
                    'date_from'  => date($this->config->item('php_date_format'), time() - 3600 * 24 * 7),
                    'date_until' => date($this->config->item('php_date_format'), time())
                );
                $this->native_session->set_userdata('ui.client_susp_operations', $uisettings);
                $this->native_session->set_userdata('ui.client_off_operations', $uisettings);
				
				$uisettings = array(
                    'date_from'  => date($this->config->item('php_date_format'), time() ),
                    'date_until' => date($this->config->item('php_date_format'), time())
                );
				$this->native_session->set_userdata('ui.log_operation', $uisettings);
				$uisettings = array(
                    'date_from'  => date($this->config->item('php_date_format'), time() ),
                    'date_until' => date($this->config->item('php_date_format'), time())
                );
                $this->native_session->set_userdata('ui.diag_log_operation', $uisettings);

                list($user_ip, $user_comp_name, $user_mac_addr) = $this->aml_security->get_user_data();

                $q = "INSERT INTO tb_audit_all(id,p_table,p_rec_id,p_username,p_date_update,p_action_type,p_edit_fields,p_ip,p_computer_name,p_mac_address) " .
                     "VALUES(GetID(), '-', 0, NVL(UPPER(:login),'NULL'), sysdate, 'LOGON-SUCCESS',:logtxt, :ip, :comp_name, :mac_addr)";
                $values = array(':login' => $login, ':logtxt' => 'login: ' . $login . ', ip: ' . $user_ip, ':ip'=>$user_ip, ':comp_name'=>$user_comp_name, ':mac_addr'=>$user_mac_addr);
                $this->aml_oracle->execute($q,__LINE__, $values);

				
				$q = 'UPDATE TB_USERS t SET t.P_LAST_LOGIN_DATE = sysdate   WHERE UPPER(t.P_USERNAME) = :login';
                $values = array(':login' => $login);
               // $this->aml_oracle->execute($q,__LINE__, $values);
				
//                syslog(LOG_INFO, sprintf('Login to AML succeeded. Username: %s', $login));

                header('Location: ' . site_url('page'));
                die();
            }
	               
			else {
						 
                if ($results['P_LOCKED_BOOL'][0] == 1) {
                    $this->native_session->set_flashdata('emsg', array(('Ваша учетная запись заблокирована, обратитесь к администратору')));
                    header('Location: ' . site_url('page/login'));
                    die();
                }
               list($user_ip, $user_comp_name, $user_mac_addr) = $this->aml_security->get_user_data();

                $q = "INSERT INTO tb_audit_all(id,p_table,p_rec_id,p_username,p_date_update,p_action_type,p_edit_fields,p_ip,p_computer_name,p_mac_address) " .
                     "VALUES(GetID(), '-', 0, NVL(UPPER(:login),'NULL'), sysdate, 'LOGON-FAIL',:logtxt, :ip, :comp_name, :mac_addr)";
                $values = array(':login' => $login, ':logtxt' => 'login: ' . $login . ', ip: ' . $user_ip, ':ip'=>$user_ip, ':comp_name'=>$user_comp_name, ':mac_addr'=>$user_mac_addr);
                $this->aml_oracle->execute($q,__LINE__, $values);
                $this->native_session->set_flashdata('emsg', array(('Неверное имя пользователя или пароль')));

                $q = 'UPDATE TB_USERS t SET t.P_INVALID_PWD_TRIES = P_INVALID_PWD_TRIES + 1 WHERE UPPER(t.P_USERNAME) = :login';
                $values = array(':login' => $login);
                $this->aml_oracle->execute($q,__LINE__, $values);

                $q = 'SELECT * FROM TB_USERS t WHERE t.P_USERNAME = :login AND t.P_LOCKED_BOOL = 0';
                $stid = $this->aml_oracle->execute($q,__LINE__, $values);
                $user = oci_fetch_array($stid, OCI_ASSOC);
                if(is_array($user)) {
                    if ($user['P_INVALID_PWD_TRIES'] >= 3 && $user['P_USERNAME'] != 'ADMIN') {
                        /*$q = "INSERT INTO tb_audit_all(id,p_table,p_rec_id,p_username,p_date_update,p_action_type,p_edit_fields) " .
                             "VALUES(GetID(), '-', 0, :login, sysdate, 'LOGON-FAIL',:logtxt)";
                        $values = array(':login' => $login,':logtxt' => 'login: ' . $login . ', ip: ' . $_SERVER['REMOTE_ADDR'] . ', account has been blocked');
                        $this->aml_oracle->execute($q,__LINE__, $values);*/
						list($user_ip, $user_comp_name, $user_mac_addr) = $this->aml_security->get_user_data();//Добавил Адилет по заявке 9942 09.08.2018
                        $q = "INSERT INTO tb_audit_all(id,p_table,p_rec_id,p_username,p_date_update,p_action_type,p_edit_fields,p_ip,p_computer_name,p_mac_address) " .
                             "VALUES(GetID(), '-', 0, :login, sysdate, 'LOGON-FAIL',:logtxt,:ip, :comp_name, :mac_addr)";
                        $values = array(':login' => $login,':logtxt' => 'login: ' . $login . ', ip: ' . $_SERVER['REMOTE_ADDR'] . ', account has been blocked',':ip'=>$user_ip, ':comp_name'=>$user_comp_name, ':mac_addr'=>$user_mac_addr);
                        $this->aml_oracle->execute($q,__LINE__, $values);

                        $q = 'UPDATE TB_USERS t SET t.P_LOCKED_BOOL = 1 WHERE UPPER(t.P_USERNAME) = :login';
                        $values = array(':login' => $login);
                        $this->aml_oracle->execute($q,__LINE__, $values);

//                        syslog(LOG_WARNING, sprintf('Login to AML failed 3 times in sequence. The account has been blocked for  %s', $login));
                    }
                }

//                syslog(LOG_WARNING, sprintf('Login to AML failed. Username: %s', $login));

                header('Location: ' . site_url('page/login'));
                die();
            }
        }
        $this->aml_context->set_general_vars($tdata);
        $vars['content'] = $this->load->view('login-form', $tdata, true);
        $this->load->view('main', $vars);
    }

    function logoff() {
	$login = $this->aml_auth->get_username();
	list($user_ip, $user_comp_name, $user_mac_addr) = $this->aml_security->get_user_data();

	$q = "INSERT INTO tb_audit_all(id,p_table,p_rec_id,p_username,p_date_update,p_action_type,p_edit_fields,p_ip,p_computer_name,p_mac_address) " .
		 "VALUES(GetID(), '-', 0, NVL(UPPER(:login),'NULL'), sysdate, 'LOGOFF-SUCCESS',:logtxt, :ip, :comp_name, :mac_addr)";
	$values = array(':login' => $login, ':logtxt' => 'login: ' . $login . ', ip: ' . $user_ip, ':ip'=>$user_ip, ':comp_name'=>$user_comp_name, ':mac_addr'=>$user_mac_addr);
 
	$this->aml_oracle->execute($q,__LINE__, $values);
        $this->aml_auth->logoff();
        header('Location: ' . site_url('page/login'));
        die();
    }

    function viewsourceoperations($parent_id = null) {
        $this->aml_auth->check_auth();
        $can_do = $this->aml_security->check_privilege(11) || $this->aml_security->check_privilege(12) || $this->aml_security->check_privilege(56) || $this->aml_security->check_privilege(91);
        if (!$can_do) {
            $this->aml_html->error_page(array(('Отсутствуют права для данного действия.')));
        }

        $output = '';
        if ($parent_id != null) {
            $parent_id = intval($parent_id);
            if ($parent_id <= 0) {
                $this->aml_html->error_page(array(sprintf(('Неверное значение параметра %s'), 'operation_id')));
            }
            $use_session_params = false;
        } else {
            $ops = $this->native_session->flashdata('operations_list');
            if (!$ops) {
                $this->aml_html->error_page(array(sprintf(('Неверное значение параметра %s'), 'operations_list')));
            }
            $use_session_params = true;
        }

        if (!$use_session_params) {
            $q = 'SELECT t.ID, t.P_OFFIDSUM, t.P_OFFIDSOURCE, o.P_BANKOPERATIONID, o.P_OPERATIONDATETIME FROM ' . $this->db_schema_prefix . 'TB_SUSP_HISTORY t left join TB_OFFLINEOPERATIONS o on o.id=t.P_OFFIDSUM WHERE t.P_SUSPOPERATIONID = :id order by o.P_DATE_INSERT asc';
            $stid = $this->aml_oracle->execute($q,__LINE__, array(':id' => $parent_id));
            $nrows = oci_fetch_all($stid, $results);

            if (!$nrows) {
                $this->aml_html->error_page(array(sprintf(('Не найдена запись с parent_id: %d'), $parent_id)));
            }

			$operation_view = "";
		    if(count($results['P_OFFIDSUM'])>0){
		        $vars['left_col'] = true;
				if ($this->aml_security->check_privilege(91)){
		        $vars['left_col_content']  = "<ul class='ul_operations_switcher'>";
				}
				else{
		        $vars['left_col_content']  = "<div class='add_offline_operation'><a href='#' class='button-link' onclick='add_offline_operation(".$parent_id.");return false;'>".("Добавить")."</a></div><ul class='ul_operations_switcher'>";
				}
            	for($i=0;$i<count($results['ID']);$i++){
            		$vars['left_col_content'] .= "<li".($i==0?" class='active'":"")." histid='".$results['ID'][$i]."' operid='".$results['P_OFFIDSUM'][$i]."' onclick=\"show_operation(this);\">";
            		if($results['P_OFFIDSOURCE'][$i] != $results['P_OFFIDSUM'][$i]){
	            		$vars['left_col_content'] .= "<img src='".$this->config->config['base_url']."images/cancel.jpg' title='".('Удалить')."'/>";
            		}
            		$vars['left_col_content'] .= "№".$results['P_BANKOPERATIONID'][$i]."<br/>".$results['P_OPERATIONDATETIME'][$i]."</li>";
            	}
		        $vars['left_col_content'] .= "</ul>";

            	$output .= "<script language='javascript'>jQuery('document').ready(function(){
            			var doc_height = jQuery(window).height();
            			jQuery('#view_selected_operation iframe').height(doc_height-15);
            			jQuery('.ul_operations_switcher').css('overflow-y','scroll').height(doc_height-50);
            			jQuery('#operation_preview').css('display','none');
            			jQuery('#content table:eq(0)').css({'width':'100%'});
            		});</script>";
            	$output .= "<div id='view_selected_operation'><iframe src='".$this->config->config['base_url']."index.php/page/edit/offline/".$results['P_OFFIDSUM'][0]."' width='100%;' frameBorder='0'  /></div>";
            } else {
                $this->aml_html->error_page(array(('Для данной операции нет операций-источников')));
            }
        } else {
            if (!empty($ops)) {
                $this->viewdata('offline', str_replace(',',' ', $ops));
            } else {
                $this->aml_html->error_page(array(('Для данной операции нет операций-источников')));
            }
        }
        $vars['hide_header'] = true;
        $vars['content'] = $output;
        $this->aml_context->set_general_vars($vars);
        $this->load->view('main', $vars);
    }

    function viewextrainfo($operation_id = 0) {
        $this->aml_auth->check_auth();
        $can_do = $this->aml_security->check_privilege(11) || $this->aml_security->check_privilege(12) ||$this->aml_security->check_privilege(56);
        if (!$can_do) {
            $this->aml_html->error_page(array(('Отсутствуют права для данного действия.')));
        }

        $operation_id = intval($operation_id);
        if ($operation_id <= 0) {
            $this->aml_html->error_page(array(sprintf(('Неверное значение параметра %s'), 'operation_id')));
        }

        $q = 'SELECT P_OPERATIONEXTRAINFO, P_BANKOPERATIONID FROM ' . $this->db_schema_prefix . 'TB_SUSPICIOUSOPERATIONS t WHERE t.ID = :id';
        $stid = $this->aml_oracle->execute($q,__LINE__, array(':id' => $operation_id));
        $nrows = oci_fetch_all($stid, $results);
        if (!$nrows) {
            $this->aml_html->error_page(array(sprintf('Не найдена запись с ID: %d', $operation_id)));
        }

        if (!empty($results['P_OPERATIONEXTRAINFO'][0])) {
            $vars['content'] = "<div style='font-family:Tahoma'><strong>" . ("Дополнительная информация по операции №") . $results['P_BANKOPERATIONID'][0] . ":</strong> <br /><br />" . nl2br($results['P_OPERATIONEXTRAINFO'][0]) . '</div>';
            $vars['hide_header'] = true;
            $this->load->view('main',$vars);
        } else {
            $this->aml_html->error_page(array(('Для данной операции нет операций-источников')));
        }
    }

    function setprepared() {
        $can_do = $this->aml_security->check_privilege(12) || $this->aml_security->check_privilege(11);
        if (!$can_do) {
            $this->aml_html->error_page(array(('Отсутствуют права для данного действия.')));
        }

        $records = explode(',', $this->input->post('records'));
        $processing_results = array();

        foreach($records as $next) {
            if (is_numeric($next) && $next > 0) {
                $strerr = str_repeat(' ',8000);
                $stid = $this->aml_oracle->execute("BEGIN
                                               get_system_rule(:id, :strerr);
                                               GetIDKFM('".$next."');
                                               UPDATE TB_SUSPICIOUSOPERATIONS t SET t.P_SENDTOKFMBOOL = 4 WHERE t.ID = :id;
                                            END;",__LINE__, array(':id' => intval($next), ':strerr' => &$strerr), false);

                if (!$stid) {
                    $err = $this->aml_oracle->get_last_error();
                    $message = str_replace('ORA-'.$err['code'].':','',$err['message']);
                    $message_part = explode('ORA',$message);
                    $message = $message_part[0];
                    $processing_results[] = "<b>".sprintf(("Ошибка при обработке операции, ID = %d, "), $next) ."</b>". $message;
                    
                } else {
                    $processing_results[] = "<b>".sprintf(('Операция передана на утверждение. ID = %d'), $next)."</b><br/>";
                }
            }
        }

        print "try { jQuery('.jq-grid').trigger('reloadGrid'); } catch(e) {} \n";
        print "div_alert('<a href=\"javascript:void(0);\" onclick=\"cancel_select_period();return false;\" style=\"float:right;font-size:15px;text-decoration:none;\">X</a>" . str_replace(array("\n","\r"),'',nl2br(implode("\n",  $processing_results))) . "<center><input type=\"button\" style=\"width:50px;margin-top:50px;\" onclick=\"cancel_select_period();return false;\" value=\"OK\"></center>')";
    }

    function returnoperations() {
        $can_use_kfm = $this->aml_security->check_privilege(15) ||
                       $this->aml_security->check_privilege(19) ||
                       $this->aml_security->check_privilege(21) ||
                       $this->aml_security->check_privilege(20); // can use kfm?
        if (!$can_use_kfm) {
            $this->aml_html->error_page(array(('Отсутствуют права для данного действия.')));
        }
        $records = explode(',', $this->input->post('records'));
        $reason = $this->input->post('comment');
        $in_id = array();
        foreach($records as $next){
            if (is_numeric($next) && $next > 0) {
//				$this->aml_oracle->execute('UPDATE TB_SUSPICIOUSOPERATIONS t SET t.P_SENDTOKFMBOOL = 5, t.P_COMMENT = t.P_COMMENT || chr(10) || :cmt WHERE t.ID = :id ',__LINE__, array(':id' => $next, ':cmt' => $reason));
            	$in_id[] = $next;
            }
        }
        if(count($in_id) > 0){
			$this->aml_oracle->execute('UPDATE TB_SUSPICIOUSOPERATIONS t SET t.P_SENDTOKFMBOOL = 5, t.P_COMMENT = t.P_COMMENT || chr(10) || :cmt WHERE t.ID in ('.implode(',',$in_id).') ',__LINE__, array(':cmt' => $reason));
			$this->aml_oracle->commit();
		}
        print "try { jQuery('.jq-grid').trigger('reloadGrid'); } catch(e) {} \n";
    }

    function allowoperations($type){
        $can_use_kfm = $this->aml_security->check_privilege(15) || $this->aml_security->check_privilege(21);
        if (!$can_use_kfm) {
            $this->aml_html->error_page(array(('Отсутствуют права для данного действия.')));
        }
        $records = explode(',', $this->input->post('records'));
        $reason = $this->input->post('comment');
        $in_id = array();
        foreach($records as $next){
            if (is_numeric($next) && $next > 0) {
            	$in_id[] = $next;
            }
        }
        if(count($in_id) > 0){
			$this->aml_oracle->execute("UPDATE TB_SUSPICIOUSOPERATIONS t SET t.P_SENDTOKFMBOOL = '".($type==1?13:12)."', t.P_COMMENT = t.P_COMMENT || chr(10) || :cmt WHERE t.ID in (".implode(',',$in_id).") and t.P_MESS_STATUS in (2,3,4,5)",__LINE__, array(':cmt' => $reason));
			$this->aml_oracle->commit();
		}
        print "try { jQuery('.jq-grid').trigger('reloadGrid'); } catch(e) {} \n";
    }

    function reports($report_id = null, $begin_date = null, $end_date = null) {
        // список отчетов
        $this->aml_auth->check_auth();
        $vars['active_link'] = 'reports';
        $vars['page_name'] = ('Отчеты');

        $can_use_reports = $this->aml_security->check_privilege(5) || $this->aml_security->check_privilege(91); // can use reports?
        if (!$can_use_reports) {
            $this->aml_html->error_page(array(('Отсутствуют права для данного действия.')));
        }

        if (empty($report_id)) {
            $q = "SELECT * FROM " . $this->db_schema_prefix . "TB_REPORTS    ORDER BY order_number";
            $stid = $this->aml_oracle->execute($q, __LINE__);
            $nrows = oci_fetch_all($stid, $results);
            $vars['reports_list'] = $results;
            $vars['content'] = $this->load->view('reports-list', $vars, true);
        }

        $this->aml_context->set_general_vars($vars);
        $this->load->view('main', $vars);
    }
	
	function reports_podft($report_id = null, $begin_date = null, $end_date = null) {
        // список отчетов
        $this->aml_auth->check_auth();
        $vars['active_link'] = 'reports';
        $vars['page_name'] = ('Отчеты');

        $can_use_reports = $this->aml_security->check_privilege(40); // can use reports?
        if (!$can_use_reports) {
            $this->aml_html->error_page(array(('Отсутствуют права для данного действия.')));
        }

        if (empty($report_id)) {
            $q = "SELECT * FROM " . $this->db_schema_prefix . "TB_REPORTS where P_TYPE = 2 ORDER BY order_number ";
            $stid = $this->aml_oracle->execute($q, __LINE__);
            $nrows = oci_fetch_all($stid, $results);
            $vars['reports_list'] = $results;
            $vars['content'] = $this->load->view('reports-list', $vars, true);
        }

        $this->aml_context->set_general_vars($vars);
        $this->load->view('main', $vars);
    }

    function operationstree(){
        $this->aml_auth->check_auth();
         $vars['page_name'] = ('Денежные потоки');

        $can_use_reports = $this->aml_security->check_privilege(22);
        if (!$can_use_reports) {
            $this->aml_html->error_page(array(('Отсутствуют права для данного действия.')));
        }

        $vars['run_js'] = <<<ENDL
        jQuery(document).ready(function () {
            jQuery('#t1').jstree({
                "plugins" : [ "themes", "html_data" ],
                "html_data" : {
                    "ajax" : {
                        "type" : "POST",
                        "url" : "{$this->operationstreeurl}/debit",
                        "data" : function (n) {
                            if (n == -1) {
                                return {}
                            } else {
                                return { id : n.attr ? n.attr("id") : ""};
                            }
                        }
                    }
                }
            });
            jQuery('#t2').jstree({
                "plugins" : [ "themes", "html_data" ],
                "html_data" : {
                    "ajax" : {
                        "type" : "POST",
                        "url" : "{$this->operationstreeurl}",
                        "data" : function (n) {
                            if (n == -1) {
                                return {}
                            } else {
                                return { id : n.attr ? n.attr("id") : ""};
                            }
                        }
                    }
                }
            });
            \$('#setTreeOperationsPrefs').click(function () {
                hasError = false;
                if (\$('#account').val() == '') {
                    \$('#account').addClass('error-value');
                    hasError = true;
                } else {
                    \$('#account').removeClass('error-value');
                }

                if(hasError) {
                    return;
                }

                \$.post('{$this->getclientbyofflineaccurl}', { account : \$('#account').val() }, function (data) {
                    \$('#client-name').html(data);
                });

                \$.post('{$this->savesettingsurl}',
                    {
                      uisection : 'operationstree',
                      date_from: \$('#date_from').val(),
                      date_until: \$('#date_until').val(),
                      account : \$('#account').val()
                    },function(data) {
                        location.reload();
                    }
                );

            });
        });
ENDL;
        $ot = $this->native_session->userdata('ui.operationstree');

        $q = 'SELECT om.p_name FROM tb_offlineoperations op, tb_off_members om WHERE om.p_operationid=op.id and om.p_account = :acc';
        $stid = $this->aml_oracle->execute($q, __LINE__, array(':acc' => $ot['account']), false);
        $err = $this->aml_oracle->get_last_error();
        list($vars['clientname']) = oci_fetch_array($stid);
        $vars['ot'] = $ot;

        $vars['content'] = $this->load->view('operationstree', $vars, true);
        $this->aml_context->set_general_vars($vars);
        $this->load->view('main', $vars);
    }

    function archive($what = null) {
       $this->aml_auth->check_auth();
       $can_do = $this->aml_security->check_privilege(11) || $this->aml_security->check_privilege(12);

       switch($what){
           case 'online_restore':
               $commit = false;
               $records = explode(',',$this->input->post('records'));
               foreach($records as $r){
                   $id = intval($r);
                   if ($id <= 0) {
                       continue;
                   }
                   $q = 'UPDATE TB_SUSPICIOUSOPERATIONS t SET P_SENDTOKFMBOOL = 1 WHERE id = :id';
                   $stid = $this->aml_oracle->execute($q, __LINE__, array(':id' => $id), true, OCI_DEFAULT);
                   $commit = true;
               }
               if ($commit) {
                   oci_commit($this->aml_oracle->oracle_connection);
                   print "jQuery('#grid1').trigger('reloadGrid');";
               }
               break;
           case 'online':
               $commit = false;
               $records = explode(',',$this->input->post('records'));
               $reason  = $this->input->post('reason');
               foreach($records as $r){
                   $id = intval($r);
                   if ($id <= 0) {
                       continue;
                   }
                   $q = "UPDATE TB_SUSPICIOUSOPERATIONS t SET P_SENDTOKFMBOOL = 2, P_USERNAME = :p_username,P_COMMENT = P_COMMENT ||chr(10)|| :p_comment, P_DATE_UPDATE = sysdate WHERE id = :id";
                   $stid = $this->aml_oracle->execute($q, __LINE__, array(':id' => $id, ':p_username' => $this->aml_auth->get_username(),':p_comment' => "[".date('d.m.Y H:i:s').' '.$this->aml_auth->get_username()."] ".$reason), false, OCI_DEFAULT);
                   $commit = true;
               }
               if ($commit) {
                   oci_commit($this->aml_oracle->oracle_connection);
                   print "jQuery('#grid1').trigger('reloadGrid');";
               }
               break;
           default:
       }
    }

    function audit($page = 0){
        $page = intval($page);
        $per_page = 500;

        $this->aml_auth->check_auth();
        $can_admin = $this->aml_security->check_privilege(24) || $this->aml_security->check_privilege(39); // USERS
        if (!$can_admin) {
            $this->aml_html->error_page(array(('Отсутствуют права для данного действия.')));
        }
		$vars['uisettings'] = $this->native_session->userdata('ui.audit');
		$stid = $this->aml_oracle->execute("SELECT p_username FROM tb_users ORDER BY p_username",__LINE__);
        $users_select = "<select id='username' style='width:130px'><option value='0'>Все</option>";
        while($u = oci_fetch_array($stid)) {
        	$users_select .= "<option value='".@$u['P_USERNAME']."'".(@$vars['uisettings']['username']==@$u['P_USERNAME']?" selected":"").">".@$u['P_USERNAME']."</option>";
        }
        $users_select .= "</select>";
        $vars['users'] = $users_select;
        $vars['grid']       = $this->aml_metainfo->get_js_table_properties('TB_AUDIT_ALL');
        $vars['uisettings'] = $this->native_session->userdata('ui.audit');


 
        $vars['page_name'] = ('Журнал аудита');
        $vars['content'] = $this->load->view('audit', $vars,true);

        $this->aml_context->set_general_vars($vars);
        $this->load->view('main', $vars);
    }

    function warnings() {
        $this->aml_auth->check_auth();
        $vars['page_name'] = ('Страница предупреждений');
        $vars['content'] = 'Страница предупреждений';

        $q = 'SELECT * FROM VW_NOTIFICATIONS t WHERE t.P_ROLE IN (' . implode(',',$this->aml_security->get_user_privileges($this->aml_auth->get_uid())) . ')';
        $stid = $this->aml_oracle->execute($q, __LINE__);
        $rows = array();
        while ($r = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
            $r['P_LINK'] = '<a href="' . site_url(trim($r['P_LINK'])) . '">' . ('просмотр') . '</a>';
            unset($r['P_ROLE']);
            $rows[] = $r;
        }

		$vars['run_js'] = "jQuery('document').ready(function(){jQuery('#content a').attr('target','_blank');});";
        $vars['content'] = '<div style="margin-top:10px">' .$this->_html_table(array(('Критичность'), ('Описание'), ('Ссылка')), $rows) . '</div>';

        $this->aml_context->set_general_vars($vars);
        $this->load->view('main', $vars);
    }

    function events() {
        $this->aml_auth->check_auth();
        $q = 'SELECT COUNT(*) FROM VW_NOTIFICATIONS t WHERE t.P_ROLE IN (' . implode(',',$this->aml_security->get_user_privileges($this->aml_auth->get_uid())) . ')';
        $stid = $this->aml_oracle->execute($q,__LINE__);
        list($events) = oci_fetch_array($stid);
        print "bpsSetEvents({$events});";
    }

    function settings($action = ''){
        $this->aml_auth->check_auth();
        $vars['active_link'] = 'settings';
        $vars['page_name'] = ('Настройки');

        $can_admin_users = $this->aml_security->check_privilege(24); // ADMIN USERS
        if (!$can_admin_users) {
            $this->aml_html->error_page(array(('Отсутствуют права для данного действия.')));
        }

	    if ($this->input->post('op')) {

	    	$delete_params = $update_params = $insert_params = $update_vals = array();
	        $stid = $this->aml_oracle->execute("select * from TB_PARAMS order by P_NAME",__LINE__);
	        oci_fetch_all($stid, $results);

	    	for($i=0;$i<count($results['ID']);$i++){
	    		if($this->input->post('TB_PARAMS_'.$results['ID'][$i].'_DELETE')==1){
	    			$delete_params[] = $results['ID'][$i];
	    		} else if(array($results['P_CODE'][$i],$results['P_NAME'][$i],$results['P_VALUE'][$i])!=array(mb_strtoupper($this->input->post('TB_PARAMS_'.$results['ID'][$i].'_P_CODE'),'utf-8'),$this->input->post('TB_PARAMS_'.$results['ID'][$i].'_P_NAME'),$this->input->post('TB_PARAMS_'.$results['ID'][$i].'_P_VALUE'))){
	    			$update_params[] = $results['ID'][$i];
	    			$update_vals[$results['ID'][$i]] = array('p_code'=>mb_strtoupper($this->input->post('TB_PARAMS_'.$results['ID'][$i].'_P_CODE'),'utf-8'),'p_name'=>$this->input->post('TB_PARAMS_'.$results['ID'][$i].'_P_NAME'),'p_value'=>$this->input->post('TB_PARAMS_'.$results['ID'][$i].'_P_VALUE'));
	    		}
	    	}
	    	if(is_array($_POST['TB_PARAMS_0_P_CODE']) and count($_POST['TB_PARAMS_0_P_CODE'])){
	    		for($i=0;$i<count($_POST['TB_PARAMS_0_P_CODE']);$i++){
	    			$insert_params[] = array('p_code'=>strtoupper($_POST['TB_PARAMS_0_P_CODE'][$i]), 'p_name'=>$_POST['TB_PARAMS_0_P_NAME'][$i], 'p_value'=>$_POST['TB_PARAMS_0_P_VALUE'][$i]);
	    		}
	    	}

	    	if(count($insert_params)>0){
	    		foreach($insert_params as $insert){
		            $new_id = 9999999999;
	                $stid =
	                    $this->aml_oracle->execute(
	                        "BEGIN " .
	                        "  :new_id_sz100 := GetID(); " .
	                        "  INSERT INTO TB_PARAMS(ID, P_CODE, P_NAME, P_VALUE) VALUES(:new_id_sz100, :p_code, :p_name, :p_value); " .
	                        "END;",__LINE__, array(':new_id_sz100'=>&$new_id,':p_code'=>$insert['p_code'],':p_name'=>$insert['p_name'],':p_value'=>$insert['p_value'])
	                    );
	    		}
	    	}
			if(count($update_params)){
				foreach($update_params as $update_id){
		            $bindings = array(':id' => $update_id);
		            foreach($update_vals[$update_id] as $param_name => $param_value) {
		                $bindings[':' . $param_name] = $param_value;
		            }
		            $update = 'UPDATE TB_PARAMS SET P_CODE=:p_code, P_NAME=:p_name, P_VALUE=:p_value WHERE ID = :id';
		            $stid = $this->aml_oracle->execute($update, __LINE__, $bindings,true, OCI_DEFAULT);
				}
			}
			if(count($delete_params)){
				$delete = "DELETE FROM TB_PARAMS WHERE ID IN (".implode(',',$delete_params).")";
				$this->aml_oracle->execute($delete,__LINE__);
			}

	        $this->aml_oracle->commit();
	    }

		// список полей настроек
        $stid = $this->aml_oracle->execute("select * from TB_PARAMS order by P_NAME",__LINE__);
        oci_fetch_all($stid, $results);
        $vars['settings'] = $results;

        $vars['content'] = $this->load->view('manage-settings', $vars, true);
        $this->aml_context->set_general_vars($vars);
        $this->load->view('main', $vars);
    }



    //Добавление функционала для настройки автоматической отправки.
    // 16.10.2014 I.Liizkov
    // -- BEGIN -- 
	function e_send_settings($action = ''){
        $this->aml_auth->check_auth();
        $vars['active_link'] = 'settings';
        $vars['page_name'] = ('Настройки Отправки Уведомлений');

        $can_admin_users = $this->aml_security->check_privilege(24); // ADMIN USERS
        if (!$can_admin_users) {
            $this->aml_html->error_page(array(('Отсутствуют права для данного действия.')));
        }

        if ($this->input->post('op')) {
		//die(var_dump($_POST));
            $delete_params = $update_params = $insert_params = $update_vals = array();
            $stid = $this->aml_oracle->execute("select * from EMAIL_ALERT_PARAMS",__LINE__);

            oci_fetch_all($stid, $results);

            for($i=0;$i<count($results['ID']);$i++){
                if($this->input->post('EMAIL_ALERT_PARAMS_'.$results['ID'][$i].'_DELETE')==1){
                    $delete_params[] = $results['ID'][$i];
                } else if(array($results['ALERT_NAME'][$i],
								$results['MIN_VALUE'][$i],
								$results['MAX_VALUE'][$i],
								$results['CHECK_TIME'][$i])
				!=array(mb_strtoupper(	$this->input->post('EMAIL_ALERT_PARAMS_'.$results['ID'][$i].'_ALERT_NAME'),'utf-8'),
										$this->input->post('EMAIL_ALERT_PARAMS_'.$results['ID'][$i].'_MIN_VALUE'),
										$this->input->post('EMAIL_ALERT_PARAMS_'.$results['ID'][$i].'_MAX_VALUE'),
										$this->input->post('EMAIL_ALERT_PARAMS_'.$results['ID'][$i].'_CHECK_TIME'))){
                   
					$update_params[] = $results['ID'][$i];
                    $update_vals[$results['ID'][$i]] = array('alert_name'=>mb_strtoupper($this->input->post('EMAIL_ALERT_PARAMS_'.$results['ID'][$i].'_ALERT_NAME'),'utf-8'),
											'min_value'=>$this->input->post('EMAIL_ALERT_PARAMS_'.$results['ID'][$i].'_MIN_VALUE'),
											'max_value'=>$this->input->post('EMAIL_ALERT_PARAMS_'.$results['ID'][$i].'_MAX_VALUE'),
											'check_time'=>$this->input->post('EMAIL_ALERT_PARAMS_'.$results['ID'][$i].'_CHECK_TIME'));
					   
				}
				//die(var_dump($results['ALERT_NAME'][$i])."  ".$this->input->post('EMAIL_ALERT_PARAMS_'.$results['ID'][$i].'_ALERT_NAME')   );
				//die(var_dump($results['MIN_VALUE'][$i])."  ".$this->input->post('EMAIL_ALERT_PARAMS_'.$results['ID'][$i].'_MIN_VALUE')   );

            }
            if(is_array($_POST['EMAIL_ALERT_PARAMS_0_ALERT_NAME']) and count($_POST['EMAIL_ALERT_PARAMS_0_ALERT_NAME'])){
                for($i=0;$i<count($_POST['EMAIL_ALERT_PARAMS_0_ALERT_NAME']);$i++){
                    $insert_params[] = array('alert_name'=>strtoupper($_POST['EMAIL_ALERT_PARAMS_0_ALERT_NAME'][$i]), 'min_value'=>$_POST['EMAIL_ALERT_PARAMS_0_MIN_VALUE'][$i], 'max_value'=>$_POST['EMAIL_ALERT_PARAMS_0_MAX_VALUE'][$i], 'check_time'=>$_POST['EMAIL_ALERT_PARAMS_0_CHECK_TIME'][$i]);
                }
            }

            if(count($insert_params)>0){
                foreach($insert_params as $insert){
                    $new_id = 9999999999;
                    $stid =
                        $this->aml_oracle->execute(
                            "BEGIN " .
                            "  INSERT INTO EMAIL_ALERT_PARAMS(ALERT_NAME, MIN_VALUE, MAX_VALUE, CHECK_TIME, TRY_COUNT) VALUES(:alert_name, :min_value, :max_value,:check_time,:try_count); " .
                            "END;",__LINE__, array(':alert_name'=>$insert['ALERT_NAME'],':min_value'=>$insert['MIN_VALUE'],':max_value'=>$insert['MAX_VALUE'],':check_time'=>$insert['CHECK_TIME'])
                        );
                }
                            
            }
			 
            if(count($update_params)){
			//die(var_dump($update_params));
                foreach($update_params as $update_id){
                    $bindings = array(':id' => $update_id);
                    foreach($update_vals[$update_id] as $param_name => $param_value) {
                        $bindings[':' . $param_name] = $param_value;
                    }
					//die(var_dump( $results['MIN_VALUE']));
                    $update = 'UPDATE EMAIL_ALERT_PARAMS SET ALERT_NAME=:alert_name, MIN_VALUE=:min_value, MAX_VALUE=:max_value, CHECK_TIME=:check_time WHERE ID = :id ';
                    
                    $stid = $this->aml_oracle->execute($update, __LINE__, $bindings,true, OCI_DEFAULT);
                }
				
            }
             
            if(count($delete_params)){
                $delete = "DELETE FROM EMAIL_ALERT_PARAMS WHERE ID IN (".implode(',',$delete_params).")";
                $this->aml_oracle->execute($delete,__LINE__);
            }

            $this->aml_oracle->commit();
        }
        
        // список полей настроек
        $stid = $this->aml_oracle->execute("select * from EMAIL_ALERT_PARAMS",__LINE__);
        oci_fetch_all($stid, $results);
        $vars['settings'] = $results;

        $vars['content'] = $this->load->view('manage-e-send-settings', $vars, true);
        $this->aml_context->set_general_vars($vars);
        $this->load->view('main', $vars);
    }
	
	function e_recp_settings($action = ''){
        $this->aml_auth->check_auth();
        $vars['active_link'] = 'settings';
        $vars['page_name'] = ('Настройка списка получателей');

        $can_admin_users = $this->aml_security->check_privilege(24); // ADMIN USERS
        if (!$can_admin_users) {
            $this->aml_html->error_page(array(('Отсутствуют права для данного действия.')));
        }

        if ($this->input->post('op')) {
		 // die(var_dump($_POST));
            $delete_params = $update_params = $insert_params = $update_vals = array();
            $stid = $this->aml_oracle->execute("select * from EMAIL_ALERT_RECIPIENTS",__LINE__);

            oci_fetch_all($stid, $results);

            

            for($i=0;$i<count($results['ID']);$i++){
                if($this->input->post('EMAIL_ALERT_RECIPIENTS_'.$results['ID'][$i].'_DELETE')==1){
                    $delete_params[] = $results['ID'][$i];
                } else if(array($results['ALERT_NAME'][$i],
								$results['RECIPIENT_EMAIL'][$i])
				!=array(mb_strtoupper(	$this->input->post('EMAIL_ALERT_RECIPIENTS_'.$results['ID'][$i].'_ALERT_NAME'),'utf-8'),
										$this->input->post('EMAIL_ALERT_RECIPIENTS_'.$results['ID'][$i].'_RECIPIENT_EMAIL') )){
                   
					$update_params[] = $results['ID'][$i];
                    $update_vals[$results['ID'][$i]] = array('alert_name'=>mb_strtoupper($this->input->post('EMAIL_ALERT_RECIPIENTS_'.$results['ID'][$i].'_ALERT_NAME'),'utf-8'),
											'recipient_email'=>$this->input->post('EMAIL_ALERT_RECIPIENTS_'.$results['ID'][$i].'_RECIPIENT_EMAIL') );
					   
				}
				//die(var_dump($results['ALERT_NAME'][$i])." -  ".$this->input->post('EMAIL_ALERT_RECIPIENTS_'.$results['ID'][$i].'_ALERT_NAME')   );
				 //die(var_dump($results['RECIPIENT_EMAIL'][$i])."  ".$this->input->post('EMAIL_ALERT_RECIPIENTS_'.$results['ID'][$i].'_RECIPIENT_EMAIL')   );

            }
            if(is_array($_POST['EMAIL_ALERT_RECIPIENTS_0_ALERT_NAME']) and count($_POST['EMAIL_ALERT_RECIPIENTS_0_ALERT_NAME'])){
                for($i=0;$i<count($_POST['EMAIL_ALERT_RECIPIENTS_0_ALERT_NAME']);$i++){
                    $insert_params[] = array('alert_name'=>strtoupper($_POST['EMAIL_ALERT_RECIPIENTS_0_ALERT_NAME'][$i]), 'recipient_email'=>$_POST['EMAIL_ALERT_RECIPIENTS_0_RECIPIENT_EMAIL'][$i]);
                }
            }
			
			if(count($insert_params)>0){
                foreach($insert_params as $insert){
				//die(var_dump($insert['alert_name']));
                    $new_id = 9999999999;
                    $stid =
                        $this->aml_oracle->execute(
                            "BEGIN " .
							"  :new_id_sz100 := GetID(); " .
                            "  INSERT INTO EMAIL_ALERT_RECIPIENTS(ALERT_NAME, RECIPIENT_EMAIL,ID) VALUES(:alert_name, :recipient_email,:new_id_sz100 ); " .
                            "END;",__LINE__, array(':alert_name'=>$insert['alert_name'],':recipient_email'=>$insert['recipient_email'],':new_id_sz100'=>&$new_id)
                        );
                }
                            
            }
			
			
            if(count($update_params)){
			//die(var_dump($update_params));
                foreach($update_params as $update_id){
                    $bindings = array(':id' => $update_id);
                    foreach($update_vals[$update_id] as $param_name => $param_value) {
                        $bindings[':' . $param_name] = $param_value;
                    }
					  //die(var_dump($bindings));
                    $update = 'UPDATE EMAIL_ALERT_RECIPIENTS SET ALERT_NAME=:alert_name, RECIPIENT_EMAIL=:recipient_email WHERE ID = :id ';
                    
                    $stid = $this->aml_oracle->execute($update, __LINE__, $bindings,true, OCI_DEFAULT);
                }
				
            }
			if(count($delete_params)){
			 //die(var_dump($delete_params));
                $delete = "DELETE FROM EMAIL_ALERT_RECIPIENTS WHERE ID IN (".implode(',',$delete_params).")";
                $this->aml_oracle->execute($delete,__LINE__);
            }
              
            $this->aml_oracle->commit();
        }

        $fi = array(
                    'P_CODE' => array('NAME' ),
                    'P_DIRECTORY_OBJECT' => array('TB_DICT_EMAIL_ALERTS'  ),
                    'DATA_TYPE' => array('VARCHAR2' ),
                    'COLUMN_NAME' => array('NAME' ),
                    'P_FIELD_CAPTION' => array('Выберете категорию'),
                    'P_EDITABLE_BOOL' => array(1)
                );
             
                $vars['dlg_add_email_recp'] = $this->aml_html->create_control('TB_DICT_EMAIL_ALERTS', $fi, 0, '', 1,'')
                                                 . $this->aml_html->create_control('TB_DICT_YES_NO', $fi, 6, '', 1, '');
                                                 
        
        // список полей настроек
        $stid = $this->aml_oracle->execute("select * from EMAIL_ALERT_RECIPIENTS",__LINE__);
        oci_fetch_all($stid, $results);
        $vars['settings'] = $results;
 
		
        $vars['content'] = $this->load->view('manage-recp-send-settings', $vars, true);
        //var_dump($vars['tb_dict_opercode_select']);
        //var_dump($fi);
        $this->aml_context->set_general_vars($vars);
        $this->load->view('main', $vars);
    }
	function e_text_settings($action = ''){
        $this->aml_auth->check_auth();
        $vars['active_link'] = 'settings';
        $vars['page_name'] = ('Настройка текста сообщений');

        $can_admin_users = $this->aml_security->check_privilege(24); // ADMIN USERS
        if (!$can_admin_users) {
            $this->aml_html->error_page(array(('Отсутствуют права для данного действия.')));
        }

        if ($this->input->post('op')) {
		  //die(var_dump($_POST));
            $delete_params = $update_params = $insert_params = $update_vals = array();
            $stid = $this->aml_oracle->execute("select * from EMAIL_ALERT_TEXT",__LINE__);

            oci_fetch_all($stid, $results);

            for($i=0;$i<count($results['ID']);$i++){
                if($this->input->post('EMAIL_ALERT_TEXT_'.$results['ID'][$i].'_DELETE')==1){
                    $delete_params[] = $results['ID'][$i];
                } else if(array($results['ALERT_NAME'][$i],
								$results['SUBJECT'][$i],
								$results['TEXT'][$i])
				!=array(mb_strtoupper(	$this->input->post('EMAIL_ALERT_TEXT_'.$results['ID'][$i].'_ALERT_NAME'),'utf-8'),
										$this->input->post('EMAIL_ALERT_TEXT_'.$results['ID'][$i].'_SUBJECT'),
										$this->input->post('EMAIL_ALERT_TEXT_'.$results['ID'][$i].'_TEXT'))){
                   
					$update_params[] = $results['ID'][$i];
                    $update_vals[$results['ID'][$i]] = array('alert_name'=>mb_strtoupper($this->input->post('EMAIL_ALERT_TEXT_'.$results['ID'][$i].'_ALERT_NAME'),'utf-8'),
											'subject'=>$this->input->post('EMAIL_ALERT_TEXT_'.$results['ID'][$i].'_SUBJECT'), 
											'text'=>$this->input->post('EMAIL_ALERT_TEXT_'.$results['ID'][$i].'_TEXT'));
				}
				 //die(var_dump( ('EMAIL_ALERT_TEXT_'.$results['ID'][$i].'_ALERT_NAME')));
				 //die(var_dump($results['ALERT_NAME'][$i])."  ".$this->input->post('EMAIL_ALERT_TEXT_'.$results['ID'][$i].'_ALERT_NAME')   );
				 // die(var_dump($results['SUBJECT'][$i])." - ".$this->input->post('EMAIL_ALERT_TEXT_'.$results['ID'][$i].'_SUBJECT')   );

            }
            if(is_array($_POST['EMAIL_ALERT_TEXT_0_ALERT_NAME']) and count($_POST['EMAIL_ALERT_TEXT_0_ALERT_NAME'])){
                for($i=0;$i<count($_POST['EMAIL_ALERT_TEXT_0_ALERT_NAME']);$i++){
                    $insert_params[] = array('alert_name'=>strtoupper($_POST['EMAIL_ALERT_TEXT_0_ALERT_NAME'][$i]), 'subject'=>$_POST['EMAIL_ALERT_TEXT_0_SUBJECT'][$i], 'text'=>$_POST['EMAIL_ALERT_TEXT_0_TEXT'][$i]);
                }
            }
            if(count($update_params)){
			//die(var_dump($update_params));
                foreach($update_params as $update_id){
                    $bindings = array(':id' => $update_id);
                    foreach($update_vals[$update_id] as $param_name => $param_value) {
                        $bindings[':' . $param_name] = $param_value;
                    }
					 //die(var_dump($bindings));
                    $update = 'UPDATE EMAIL_ALERT_TEXT SET ALERT_NAME=:alert_name, SUBJECT=:subject,TEXT=:text WHERE ID = :id ';
                    
                    $stid = $this->aml_oracle->execute($update, __LINE__, $bindings,true, OCI_DEFAULT);
                }
				
            }
              
            $this->aml_oracle->commit();
        }
        
        // список полей настроек
        $stid = $this->aml_oracle->execute("select * from EMAIL_ALERT_TEXT",__LINE__);
        oci_fetch_all($stid, $results);
        $vars['settings'] = $results;

        $vars['content'] = $this->load->view('manage-e-text-settings', $vars, true);
        $this->aml_context->set_general_vars($vars);
        $this->load->view('main', $vars);
    }
    //Добавление функционала для настройки автоматической отправки.
    // 16.10.2014 I.Liizkov
    // -- END --

    function cron_emails(){
    	$query = "SELECT * FROM VW_NOTIFICATIONS";
    	$stid = $this->aml_oracle->execute($query, __LINE__, null, false);
		oci_fetch_all($stid, $notes);
		if(!is_array($notes) or count($notes) < 1){return;}

		$roles = array();
		$count_notes = count($notes['P_ROLE']);
		for($i=0;$i<$count_notes;$i++){
			$role_str[$notes['P_ROLE'][$i]] .= "<tr><td>".$notes['P_SEVERITY'][$i]."</td><td>".$notes['P_DESCRIPTION'][$i]."</td><td>".$this->config->config['base_url']."index.php/".trim($notes['P_LINK'][$i])."</td></tr>";
		}

		$query = "select distinct a.P_USER_ID, b.P_EMAIL, P_FIRSTNAME, P_SECONDNAME, P_USERNAME from TB_USER_ROLES a left join TB_USERS b on b.ID = a.P_USER_ID where a.P_ROLE_ID in (".implode(',',array_keys($role_str)).")";
    	$stid = $this->aml_oracle->execute($query, __LINE__, null, false);
		oci_fetch_all($stid, $users);

		if(!is_array($users) or count($users) < 1){return;}

		$ids = array();
		$count_users = count($users['P_USER_ID']);
		for($i=0; $i<$count_users; $i++){
			$ids[] = $users['P_USER_ID'][$i];
			$email[$users['P_USER_ID'][$i]] = $users['P_EMAIL'][$i];
			$username[$users['P_USER_ID'][$i]] = ($users['P_FIRSTNAME'][$i] || $users['P_SECONDNAME'][$i] ? $users['P_FIRSTNAME'][$i].' '.$users['P_SECONDNAME'][$i] : $users['P_USERNAME'][$i]);
		}
		$query = "select distinct p_user_id, p_role_id from (
			select P_USER_ID, P_ROLE_ID
				from TB_USER_ROLES
				where P_USER_ID in (".implode(',',$ids).")
					and P_ROLE_ID in (".implode(',',array_keys($role_str)).")
			union
				select ug.id_user as P_USER_ID, gr.id_role as P_ROLE_ID
				from tb_user_groups ug, tb_group_roles gr
				where gr.id_group = ug.id_group
					and ug.id_user in (".implode(',',$ids).")
					and gr.id_role in (".implode(',',array_keys($role_str)).")
		)";
    	$stid = $this->aml_oracle->execute($query, __LINE__, null, false);
		oci_fetch_all($stid, $user_roles);

		$count_user_roles = count($user_roles['P_USER_ID']);
		for($i=0; $i<$count_user_roles; $i++){
			$user_text[$user_roles['P_USER_ID'][$i]] .= $role_str[$user_roles['P_ROLE_ID'][$i]];
		}
		$dir = dirname($_SERVER['SCRIPT_FILENAME']).'/system/application/helpers/phpmailer/';
		include_once($dir."class.phpmailer.php");

		$mail = new PHPMailer();
		$mail->IsSMTP();
		$mail->SMTPDebug  = 2;
		$mail->SMTPAuth   = $this->config->config['SMTPAuth'];
		$mail->SMTPSecure = $this->config->config['SMTPSecure'];
		$mail->Host       = $this->config->config['Host'];
		$mail->Port       = $this->config->config['Port'];
		$mail->Username   = $this->config->config['Username'];
		$mail->Password   = $this->config->config['Password'];

		$mail->SetFrom('no-reply@aml.info', 'AML Robot');
		$mail->Subject    = "=?UTF-8?B?".base64_encode(("Уведомления AML"))."?=\r\n";
		$mail->AltBody    = ("To view the message, please use an HTML compatible email viewer!"); // optional, comment out and test

		foreach($user_text as $user_id => $text_content){
			$content = "<table cellspacing='0' cellpadding='0' border='1'><tr>
				<td>".('Критичность')."</td>
				<td>".('Описание')."</td>
				<td>".('Ссылка')."</td></tr>".$text_content."</table>";
			$mail->MsgHTML($content);
			$mail->AddAddress($email[$user_id], "=?UTF-8?B?".base64_encode($username[$user_id])."?=\r\n");
			if(!$mail->Send()) {
			  echo "Mailer to ".$username[$user_id]." Error: " . $mail->ErrorInfo;
			} else {
			  echo "Message to ".$username[$user_id]." sent!";
			}
			$mail->ClearAddresses();
		}
	}

	function managerolegroups($action='', $id=0){
        $this->aml_auth->check_auth();
        $vars['active_link'] = 'settings';
        $vars['page_name'] = ('Группы доступа');

        $can_admin_users = $this->aml_security->check_privilege(24);
        if (!$can_admin_users) {
            $this->aml_html->error_page(array(('Отсутствуют права для данного действия.')));
        }
        if(!in_array($action, array('view', 'group_info', 'save_group', 'delete_group', 'load_users' ,'add_users', 'delete_user', 'load_roles', 'add_roles', 'delete_role', 'refresh'))){
        	$action = '';
        }
        $id*=1;

		if ($action=='' or $action=='view'){
			// Загрузка списка групп
			$ldap_groups = $system_groups = array();
			$stid = $this->aml_oracle->execute("select * from tb_role_groups", __LINE__);
			oci_fetch_all($stid, $groups);

			for($i=0;$i<count($groups['ID']);$i++){
				if($groups['P_TYPE'][$i]==1){
					$ldap_groups[] = array($groups['P_NAME'][$i], $groups['ID'][$i]);
				} else {
					$system_groups[] = array($groups['P_NAME'][$i], $groups['ID'][$i], $groups['P_DESCRIPTION'][$i]);
				}
			}

			$vars['ldap_groups'] = $ldap_groups;
			$vars['system_groups'] = $system_groups;
			$vars['groups_home_url'] = site_url("page/managerolegroups");
			$vars['load_roles'] = site_url('page/managerolegroups/load_roles');
			$vars['load_users'] = site_url('page/managerolegroups/load_users');
			$vars['add_roles'] = site_url('page/managerolegroups/add_roles');
			$vars['add_users'] = site_url('page/managerolegroups/add_users');
			$vars['delete_role'] = site_url('page/managerolegroups/delete_role');
			$vars['delete_user'] = site_url('page/managerolegroups/delete_user');
			$vars['view_group_url'] = site_url('page/managerolegroups/view');
			$vars['view_group_info_url'] = site_url('page/managerolegroups/group_info');
			$vars['save_group_url'] = site_url('page/managerolegroups/save_group');
			$vars['delete_group_url'] = site_url('page/managerolegroups/delete_group');

			$vars['content'] = $this->load->view('managerolegroups', $vars, true);
			if($action=='view' and $id>0){
				$vars['run_js'] = "jQuery(document).ready(function(){upload_rolegroup_info('".$id."');});";
			}
			$this->aml_context->set_general_vars($vars);
			$this->load->view('main', $vars);
		} else if($action=='refresh'){
		    if($this->config->config['auth_type'] != 'ldap'){
				header("Location: ".site_url("page/managerolegroups"));
				return;
			}
			// Обновление данных по группам из AD
	       	if($this->config->config['ldap_ssl']){
	       		putenv('LDAPTLS_REQCERT=never');
	       	}
	       	$ldap = @ldap_connect("ldap".($this->config->config['ldap_ssl']?"s":"")."://".$this->config->config['ldap_host'],$this->config->config['ldap_port']);
			if(!$ldap){
				header("Location: ".site_url("page/managerolegroups"));
				return;
			}
			ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
			ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);
			$bind = @ldap_bind($ldap,$this->config->config["ldap_default_user"].$this->config->config['ldap_domain'],$this->config->config["ldap_default_pwd"]);
			if(!$bind){
				header("Location: ".site_url("page/managerolegroups"));
				return;
			}

			// Считываем из AD список групп
			$result = @ldap_search($ldap,$this->config->config['ldap_base_groups'],$this->config->config["ldap_groups_filter"]);
			$groups = ldap_get_entries($ldap,$result);
			if($groups['count'] < 1){
				header("Location: ".site_url("page/managerolegroups"));
				return;
			}

			// Считываем из AD список пользователей
			$result = @ldap_search($ldap,$this->config->config['ldap_base'],str_replace('%username%','*',$this->config->config["ldap_filter"]));
			$users = ldap_get_entries($ldap,$result);

			if($users['count'] < 1){
				header("Location: ".site_url("page/managerolegroups"));
				return;
			}
			else if($users['count']==1000 or $users['count']==1500){
    			$alphabet = range('a','z');
    			foreach($alphabet as $letter){
			        $result = @ldap_search($ldap,$this->config->config['ldap_base'],str_replace('%username%',$letter.'*',$this->config->config["ldap_filter"]));
			        $users = ldap_get_entries($ldap,$result);
			        for($i=0;$i<$users['count'];$i++){
				        $username[$users[$i]['distinguishedname'][0]] = $users[$i][$this->config->config['ldap_field']['username']][0];
			        }
    			}
			}
			else {
    			// В списке групп отображается поле "distinguishedname" всех пользователей. Для передачи данных в БД выбираем логины данных пользователей
			    for($i=0;$i<$users['count'];$i++){
				    $username[$users[$i]['distinguishedname'][0]] = $users[$i][$this->config->config['ldap_field']['username']][0];
			    }
			}

			$query = "declare l_words lt_words := lt_words();
					  i number := 1;
					  result number;
					  begin
					";
			for($i=0;$i<$groups['count'];$i++){
				$gu = array();
				for($j=0;$j<$groups[$i][$this->config->config["ldap_group_field"]["member"]]['count'];$j++){
					$name = trim($username[$groups[$i][$this->config->config["ldap_group_field"]["member"]][$j]]);
					if($name != ''){
						$gu[] = $username[$groups[$i][$this->config->config["ldap_group_field"]["member"]][$j]];
					}
				}
				$query .= "l_words.extend();
				l_words(i):='".mb_strtoupper($groups[$i][$this->config->config["ldap_group_field"]["name"]][0],'utf-8')."|".$groups[$i][$this->config->config["ldap_group_field"]["description"]][0].'|'.mb_strtoupper(implode('|',$gu),'utf-8')."|';
				i:=i+1;
				";
			}
			$query .= "result := fn_ChangeGroups(l_words);";
			$query.= "end;";
			$stid = $this->aml_oracle->execute($query, __LINE__);
			if($stid){
				header("Location: ".site_url("page/managerolegroups"));
				return;
			} else {
				echo "fail";
			}
		} else if($action=='group_info'){
			// Отображение данных по определенной группе (ответ в формате JSON)
			$stid = $this->aml_oracle->execute("select * from tb_role_groups where id = '".$id."'", __LINE__, null, false);
			oci_fetch_all($stid, $group_info);
			$stid = $this->aml_oracle->execute("select u.id, u.p_username from tb_user_groups t left join tb_users u on u.id = t.id_user where t.id_group='".$id."'", __LINE__, null, false);
			oci_fetch_all($stid, $group_users);
			$stid = $this->aml_oracle->execute("select g.id, g.p_rolename from tb_group_roles t left join tb_roles g on g.id = t.id_role where t.id_group='".$id."'", __LINE__, null, false);
			oci_fetch_all($stid, $group_roles);
		    header("Content-type: text/html;charset=utf-8");
			echo "{'name':'".$group_info['P_NAME'][0]."','description':'".$group_info['P_DESCRIPTION'][0]."', 'type':'".$group_info['P_TYPE'][0]."',".
			 "'user_ids':['".implode("','",$group_users['ID'])."'], 'user_names':['".implode("','",$group_users['P_USERNAME'])."'], ".
			 "'role_ids':['".implode("','", $group_roles['ID'])."'], 'role_names':['".implode("','",$group_roles['P_ROLENAME'])."']}";
		} else if($action=='save_group'){
			// Добавление/Изменение группы
			header("Content-type: text/plain;charset=utf-8");
			$id = $this->input->post('id');
			$groupname = trim(htmlspecialchars(str_replace("'",'',$this->input->post('groupname'))));
			$description = trim(htmlspecialchars(str_replace("'",'',$this->input->post('description'))));
			$stid = $this->aml_oracle->execute("select 1, p_type from tb_role_groups where p_name = '".$groupname."'".($id?" and id!='".$id."'":""), __LINE__, null, false);
			if(!$stid){
				$err = $this->aml_oracle->get_last_error();
				die($err['message']);
			}
			$result = oci_fetch_row($stid);
			if($result[0]){
				echo ("Такая группа уже есть");
 			} else if ($id and $result[1]==1) {
 				echo ("Группу нельзя редактировать");
 			} else {
 				if($id){
 					$stid = $this->aml_oracle->execute("select p_name from tb_role_groups where id = '".$id."'", __LINE__, null, false);
					if(!$stid){
						$err = $this->aml_oracle->get_last_error();
						die($err['message']);
					}
					$group_result = oci_fetch_row($stid);
					if($group_result[0] == ''){
						die("Группа не найдена");
					}
 					$q = "update tb_role_groups set p_name = '".$groupname."', p_description='".$description."' where id = '".$id."'";
 					$d = "Изменение группы доступа ".$group_result[0];
 				} else {
 					$q = "insert into tb_role_groups (id, p_name, p_type, p_description) values (GetID(), '".$groupname."', 0, '".$description."')";
 					$d = "Создание группы доступа ".$groupname;
 				}

 				$stid = $this->aml_oracle->execute("insert into TB_ADMIN_AUDIT (id, p_system_description, p_user_description, p_status, p_system, p_user_create, p_date_create, p_sql)
 					values (GetID(), '".$d."', '-', '0', 'GROUPS', '".$this->aml_auth->get_username()."', sysdate, :sql) ", __LINE__ , array(':sql'=>$q), false);
//				$stid = $this->aml_oracle->execute($q, __LINE__ , null, false);
				if(!$stid){
					$err = $this->aml_oracle->get_last_error();
					die($err['message']);
				}
				$this->aml_oracle->commit();
				echo "success_".($id?"edit":"add");
 			}
		} else if ($action=='delete_group'){
			header("Content-type: text/plain;charset=utf-8");
			$id = $this->input->post('id');
			$stid = $this->aml_oracle->execute("select p_name from tb_role_groups where id = '".$id."'", __LINE__, null, false);
			if(!$stid){
				$err = $this->aml_oracle->get_last_error();
				die($err['message']);
			}
			$group_result = oci_fetch_row($stid);
			if($group_result[0] == ''){
				die("Группа не найдена");
			}
/*Добавил Адилет по заявке 9942 09.08.2018*/
list($user_ip, $user_comp_name, $user_mac_addr) = $this->aml_security->get_user_data();
$d1 = "Удаление группы доступа ".$group_result[0];

$login_user = $this->aml_auth->get_username();

$q1 = "INSERT INTO tb_audit_all(id,p_table,p_rec_id,p_username,p_date_update,p_action_type,p_edit_fields,p_ip,p_computer_name,p_mac_address) " .
                     "VALUES(GetID(), '-', 0, NVL(UPPER(:login),'NULL'), sysdate,'DELETE-GROUP',:logtxt, :ip, :comp_name, :mac_addr)";
                $values1 = array(':login' => $login_user, ':logtxt' => '' . $d1, ':ip'=>$user_ip, ':comp_name'=>$user_comp_name, ':mac_addr'=>$user_mac_addr);
                $this->aml_oracle->execute($q1,__LINE__, $values1);
/******************************************************************************************************************/
			$q = "delete from tb_role_groups where id='".$id."' and p_type='0'";
			$d = "Удаление группы доступа ".$group_result[0];
			$stid = $this->aml_oracle->execute("insert into TB_ADMIN_AUDIT (id, p_system_description, p_user_description, p_status, p_system, p_user_create, p_date_create, p_sql)
				values (GetID(), '".$d."','-','0','GROUPS','".$this->aml_auth->get_username()."',sysdate,:sql) ", __LINE__ , array(':sql'=>$q), false);
//			$stid = $this->aml_oracle->execute("delete from tb_role_groups where id='".$id."' and p_type='0'", __LINE__, null, false);
			if(!$stid){
				$err = $this->aml_oracle->get_last_error();
				die($err['message']);
			}
			$this->aml_oracle->commit();
			echo "success";
		} else if ($action=='load_roles'){
			header("Content-type: text/plain;charset=utf-8");
			$stid = $this->aml_oracle->execute("select id, p_rolename from tb_roles where id not in (select id_role from tb_group_roles where id_group='".$id."')", __LINE__, null, false);
			if(!$stid){
				$err = $this->aml_oracle->get_last_error();
				die("{'role_ids':'error','error':'".$err['message']."'}");
			}
			oci_fetch_all($stid, $roles);
			echo "{'role_ids' : ['".implode("','",$roles['ID'])."'], 'role_names' : ['".implode("','",$roles['P_ROLENAME'])."']}";
		} else if ($action=='load_users'){
			header("Content-type: text/plain;charset=utf-8");
			$stid = $this->aml_oracle->execute("select id, p_username from tb_users where id not in (select id_user from tb_user_groups where id_group='".$id."')", __LINE__, null, false);
			if(!$stid){
				$err = $this->aml_oracle->get_last_error();
				die("{'user_ids':'error','error':'".$err['message']."'}");
			}
			oci_fetch_all($stid, $users);
			echo "{'user_ids' : ['".implode("','",$users['ID'])."'], 'user_names' : ['".implode("','",$users['P_USERNAME'])."']}";
		} else if ($action == 'add_roles'){
			$roles = $this->input->post("role");
			if($id < 1){
				header("Location: ".site_url("page/managerolegroups"));
				return;
			}
			if(is_array($roles) and count($roles)){
				$stid = $this->aml_oracle->execute("select p_name from tb_role_groups where id = '".$id."'", __LINE__, null, false);
				$group_result = oci_fetch_row($stid);
				$stid = $this->aml_oracle->execute("select id, p_rolename from tb_roles where id in (".implode(',',$roles).")", __LINE__, null, false);
				oci_fetch_all($stid, $roles_result);

				for($i=0;$i<count($roles_result['ID']);$i++){
					$q = "insert into tb_group_roles (id_group, id_role) values ('".$id."','".$roles_result['ID'][$i]."')";
					$d = "Добавление в группу \"".$group_result[0]."\" роли: ".$roles_result['P_ROLENAME'][$i];
					$stid = $this->aml_oracle->execute("insert into TB_ADMIN_AUDIT (id, p_system_description, p_user_description, p_status, p_system, p_user_create, p_date_create, p_sql)
						values (GetID(), :description ,'-','0','GROUP_ROLES','".$this->aml_auth->get_username()."',sysdate,:sql) ",
						__LINE__ , array(':sql'=>$q, ':description'=>$d), false);
				}
				$this->aml_oracle->commit();
			}
			header("Location: ".site_url("page/managerolegroups/view/".$id));
			return;
		} else if ($action == 'add_users'){
			$users = $this->input->post("user");
			if($id < 1){
				header("Location: ".site_url("page/managerolegroups"));
				return;
			}

			if(is_array($users) and count($users)){
				$q = $d = '';
				$stid = $this->aml_oracle->execute("select p_name from tb_role_groups where id = '".$id."'", __LINE__, null, false);
				$group_result = oci_fetch_row($stid);
				$stid = $this->aml_oracle->execute("select id, p_username from tb_users where id in (".implode(',',$users).")", __LINE__, null, false);
				oci_fetch_all($stid, $users_result);

				for($i=0;$i<count($users_result['ID']);$i++){
					$q = "insert into tb_user_groups (id_group, id_user) values ('".$id."','".$users_result['ID'][$i]."')";
					$d = "Добавление пользователя: ".$users_result['P_USERNAME'][$i]." в группу \"".$group_result[0]."\"";
					$stid = $this->aml_oracle->execute("insert into TB_ADMIN_AUDIT (id, p_system_description, p_user_description, p_status, p_system, p_user_create, p_date_create, p_sql)
						values (GetID(), :description ,'-','0','GROUP_ROLES','".$this->aml_auth->get_username()."',sysdate,:sql) ",
						__LINE__ , array(':sql'=>$q, ':description'=>$d), false);
				}
				$this->aml_oracle->commit();
			}
			header("Location: ".site_url("page/managerolegroups/view/".$id));
			return;
		} else if ($action == 'delete_role'){
			if($id < 1){
				header("Location: ".site_url("page/managerolegroups"));
				return;
			}
			$role_id = $this->input->post("id")*1;
			$stid = $this->aml_oracle->execute("select p_name from tb_role_groups where id = '".$id."'", __LINE__, null, false);
			$group_result = oci_fetch_row($stid);
			$stid = $this->aml_oracle->execute("select p_rolename from tb_roles where id = '".$role_id."'", __LINE__, null, false);
			$role_result = oci_fetch_row($stid);
			$q = "delete from tb_group_roles where id_group='".$id."' and id_role='".$role_id."'";
			$d = "Удаление роли \"".$role_result[0]."\" из группы \"".$group_result[0]."\"";
//			$stid = $this->aml_oracle->execute("delete from tb_group_roles where id_group='".$id."' and id_role='".$role_id."'", __LINE__, null, false);
			$stid = $this->aml_oracle->execute("insert into TB_ADMIN_AUDIT (id, p_system_description, p_user_description, p_status, p_system, p_user_create, p_date_create, p_sql)
				values (GetID(), :description,'-','0','GROUP_USERS','".$this->aml_auth->get_username()."',sysdate,:sql) ",
				__LINE__ , array(':sql'=>$q, ':description'=>$d), false);

			if(!$stid){
				$err = $this->aml_oracle->get_last_error();
				die($err['message']);
			} else {
				$this->aml_oracle->commit();
				die("success");
			}
		} else if ($action == 'delete_user'){
			if($id < 1){
				header("Location: ".site_url("page/managerolegroups"));
				return;
			}
			$user_id = $this->input->post("id")*1;
			$stid = $this->aml_oracle->execute("select p_name from tb_role_groups where id = '".$id."'", __LINE__, null, false);
			$group_result = oci_fetch_row($stid);
			$stid = $this->aml_oracle->execute("select p_username from tb_users where id = '".$user_id."'", __LINE__, null, false);
			$user_result = oci_fetch_row($stid);
			$q = "delete from tb_user_groups where id_group='".$id."' and id_user='".$user_id."'";
			$d = "Удаление пользователя \"".$user_result[0]."\" из группы \"".$group_result[0]."\"";
			$stid = $this->aml_oracle->execute("insert into TB_ADMIN_AUDIT (id, p_system_description, p_user_description, p_status, p_system, p_user_create, p_date_create, p_sql)
				values (GetID(), :description,'-','0','GROUP_USERS','".$this->aml_auth->get_username()."',sysdate,:sql) ",
				__LINE__ , array(':sql'=>$q, ':description'=>$d), false);
			$this->aml_oracle->commit();

//			$stid = $this->aml_oracle->execute("delete from tb_user_groups where id_group='".$id."' and id_user='".($this->input->post("id")*1)."'", __LINE__, null, false);
			if(!$stid){
				$err = $this->aml_oracle->get_last_error();
				$this->aml_oracle->rollback();
				die($err['message']);
			} else {
				$this->aml_oracle->commit();
				die("success");
			}
		}
	}

	function delete_source_operation($id=0){
        $this->aml_auth->check_auth();
        $access = $this->aml_security->check_privilege(11) || $this->aml_security->check_privilege(12);
        if (!$access) {
             $this->aml_html->error_page(array(('Отсутствуют права для данного действия.')));
        }
	    header("Content-type: text/html;charset=utf-8");
		$id *= 1;
		if(!$id){
			echo "alert('".('Не выбран объект')."');";
		} else {
			$stid_1 = $this->aml_oracle->execute("select p_suspoperationid, p_offidsum, p_offidsource from tb_susp_history where id = '".$id."'", __LINE__, null, false);
			$parent_id = oci_fetch_row($stid_1);
			// В Альфе закомментировать следующий блок if
			if($parent_id[1] == $parent_id[2]){
				echo "alert(".("Вы не можете удалить данную запись").");";
				return;
			}
			$stid = $this->aml_oracle->execute("delete from tb_susp_history where id = '".$id."'", __LINE__, null, false);
			if(!$stid or !$stid_1){
				$this->aml_oracle->rollback();
				$err = $this->aml_oracle->get_last_error();
				echo "div_alert('".('Ошибка при удалении')."<br/>".str_replace(array("\n","\r"),"",$err['message'])."');";
			} else {
				$stid = $this->aml_oracle->execute("BEGIN SET_LIST_HISTORY(".$parent_id[0]."); END;", __LINE__, null, false);
				if(!$stid){
					$this->aml_oracle->rollback();
					$err = $this->aml_oracle->get_last_error();
					echo "div_alert('".('Ошибка при удалении')."<br/>".str_replace(array("\n","\r"),"",$err['message'])."');";
				} else {
					$stid = $this->aml_oracle->execute("select P_OPERATIONSTATUS from TB_SUSPICIOUSOPERATIONS where id = '".$parent_id[0]."'", __LINE__, null, false);
					$status = oci_fetch_row($stid);
					if($status[0]==1){
					    $field = "P_CHECKED='0'";
					} else {
					    $field = "P_TOEXTRACTBOOL='0'";
					}
					$this->aml_oracle->execute("update tb_offlineoperations set ".$field." where id='".$parent_id[1]."'", __LINE__, null, false);
					$this->aml_oracle->commit();
				}
				echo "alert('".('Удаление выполнено')."');jQuery('.ul_operations_switcher li[histid=\"".$id."\"]').remove();";
			}
		}
	}

	function add_to_history(){
	    header("Content-type: text/html;charset=utf-8");
		$parent_id = $this->input->post("parent_id");
		$records = $this->input->post("records");
		$records_array = explode(',',$records);
		$q = "select p_offidsum from tb_susp_history where p_suspoperationid='".$parent_id."' and p_offidsum in (".$records.")";
		//die(var_dump($q));
		$stid = $this->aml_oracle->execute($q, __LINE__, null, false);
		if(!$stid){
			$err = $this->aml_oracle->get_last_error();
			$result_str = ("Ошибка при обработке операции")."<br/>".$err['message'];
		} else {
			$result_str = "";
			$nrows = oci_fetch_all($stid, $exists);
			$exists = array_unique(array_values($exists['P_OFFIDSUM']));

			$stid = $this->aml_oracle->execute("select p_suspcode, p_offidsource from tb_susp_history where p_suspoperationid='".$parent_id."'", __LINE__, null, false);
			$off_data = oci_fetch_row($stid);

			foreach($records_array as $record){
				if(in_array($record, $exists)){
					$result_str .= ("Операция")." №".$record." ".("уже есть в списке<br/><br/>");
				} else {
					$qwe = "SELECT p_operationdatetime FROM tb_offlineoperations WHERE id = ".$off_data[1];
					$st  = $this->aml_oracle->execute($qwe, __LINE__, null, false);
					$off_date = oci_fetch_row($st);
					
					$stid = $this->aml_oracle->execute("insert into tb_susp_history values (getid, '".$parent_id."', '".$off_data[0]."','".$off_data[1]."','".$record."', '".$off_date[0]."') ", __LINE__, null, false);
					if(!$stid){
						$err = $this->aml_oracle->get_last_error();
						$result_str .= ("Ошибка при добавлении операции").(" №").$record."<br/>".$err['message'].'<br/><br/>';
					} else {
						$stid = $this->aml_oracle->execute("BEGIN SET_LIST_HISTORY(".$parent_id."); END;", __LINE__, null, false);
						if(!$stid){
							$this->aml_oracle->rollback();
							$err = $this->aml_oracle->get_last_error();
							$result_str .= ("Ошибка при добавлении операции").(" №").$record."<br/>".$err['message'].'<br/><br/>';
						} else {
							$this->aml_oracle->commit();
						}
						$result_str .= ("Операция").(" №").$record." ".("успешно добавлена")."<br/>";
					}
				}
			}
		}
		$this->aml_oracle->commit();
		$result_str .= '<center>';
		$result_str .= '<input type="button" style="width:200px;margin-right:20px;" value="'.('Закрыть окно поиска').'" onclick="window.opener.location.reload();window.close();">';
		$result_str .= '<input type="button" style="width:200px;" value="'.('Продолжить').'" onclick="cancel_select_period();window.opener.location.reload();">';
		$result_str .= '</center>';
		echo "div_alert('".$result_str."');jQuery('.background_grey').bind('click',function(){window.opener.location.reload();});";

	}

	function jobs($action='', $id=0){
        $this->aml_auth->check_auth();
        $access = $this->aml_security->check_privilege(38);
        if (!$access) {
             $this->aml_html->error_page(array(('Отсутствуют права для данного действия.')));
        }
        switch($action){
        	case 'remove':
        		$jobs = explode(',',$this->input->post('records'));
        		if(count($jobs)){
        			foreach($jobs as $job){
        				$this->aml_oracle->execute("BEGIN DBMS_JOB.REMOVE(".$job."); END;",__LINE__, null, false);
        			}
        		}
        		$this->aml_oracle->execute("update tb_user_job set p_job_exists = '0' where job in (".implode(',',$jobs).")", __LINE__, null, false);
        		return;
        		break;
        	case 'switch_broken':
        		$stid = $this->aml_oracle->execute("select broken, next_date from vw_job_runing where job='".$id."'", __LINE__, null, false);
        		$result = oci_fetch_row($stid);
        		$this->aml_oracle->execute("BEGIN DBMS_JOB.BROKEN(".$id.", ".($result[0]=='N'?"TRUE":"FALSE").",'".$result[1]."'); COMMIT; END;", __LINE__, null, false);
        		return;
        		break;
        	case 'run':
				$bindings = array(':job'=>$id);
				$stid = $this->aml_oracle->execute("BEGIN DBMS_JOB.NEXT_DATE(:job, SYSDATE); COMMIT; END;", __LINE__, $bindings);
        		return;
        		break;
        	case 'run_copy':
        		$stid = $this->aml_oracle->execute("select what from vw_job_runing where job = '".$id."'", __LINE__, null, false);
        		$result = oci_fetch_row($stid);
        		if(!$result[0]){
        			return;
        		}
        		$job = 999999999;
				$bindings = array(':job'=>&$job, ':what'=>$result[0]);
				$stid = $this->aml_oracle->execute("BEGIN DBMS_JOB.SUBMIT(:job, :what, SYSDATE, NULL); COMMIT; END;", __LINE__, $bindings);
				return;
				break;
        	case 'stop':
        		$stid = $this->aml_oracle->execute("select next_date from vw_job_runing where job='".$id."'", __LINE__, null, false);
        		$result = oci_fetch_row($stid);
        		$bindings = array(':sid'=>$sid, ':serial'=>$serial);
	        	$this->aml_oracle->execute("BEGIN DBMS_JOB.BROKEN(".$id.", TRUE,'".$result[0]."'); COMMIT; END;", __LINE__, null);
	        	$stid = $this->aml_oracle->execute("SELECT sid, serial# FROM v\$session WHERE sid = (SELECT sid FROM dba_jobs_running WHERE job = '".$id."')", __LINE__, null);
	        	list($sid, $serial) = oci_fetch_row($stid);
	        	$this->aml_oracle->execute("alter system kill session '".$sid.", ".$serial."' IMMEDIATE", __LINE__, null);
        		return;
        		break;
        }

        $vars = array();
        $this->aml_context->set_general_vars($vars);
        $vars['grid']       = $this->aml_metainfo->get_js_table_properties('vw_job_runing', 0, 0);
        $vars['page_name']  = 'Задачи';
        $vars['content']    = $this->load->view('jobs.php', $vars, true);
        $this->load->view('main', $vars);
	}
	
	function report_post () {
		 
		$this->aml_auth->check_auth();
		$this->aml_security->check_privilege(37);
		$id = intval($this->input->post('prID'));
		 
		 //die(var_dump($id ));
		 header("Content-type: text/plain; charset=utf-8");
	// die(var_dump($id));
		return $id ;
	}
	
	
	function ipdl_job() {
	 
	 $variable = $_POST['prID'];
	 //$test = $this->report_post();
		 
		
        $this->aml_auth->check_auth();
        $can_admin_users = $this->aml_security->check_privilege(24); // ADMIN USERS
		//$prID = intval($this->input->post('prID'));
        if (!$can_admin_users) {
            $this->aml_html->error_page(array(('Отсутствуют права для данного действия.')));
        }
				$r['ID'] = '1';
				$ipdl_job_id = '1';
				$query = "select * from TB_IPDL_PARAMS";
				$stid = $this->aml_oracle->execute($query, __LINE__,array('CLIENT_TYPE_ID' => &$CLIENT_TYPE_ID,'COUNTRY_CODE' => &$COUNTRY_CODE,'EXCLUDE_COUNTRY' =>&$EXCLUDE_COUNTRY,'DATE_FROM' =>&$DATE_FROM,'TIME_FROM'=>&$TIME_FROM));
				$client = oci_fetch_all($stid,$results2);
				
				//die(var_dump($TIME_FROM));
                $stid = $this->aml_oracle->execute('SELECT * FROM TB_IPDL_PARAMS', __LINE__);
					while ($r = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
					$rows[] = $r;
                }
				 $time  = " ','DD.MM.YYYY HH24:MI:SS')";
				$todate  = "TO_DATE('";
				$extra_time = substr($DATE_FROM, 0,10);
		  //die(var_dump($extra_where));
		
				//$extra_time = $todate."".$extra_time ." ". $TIME_FROM ."". $time;
				$extra_time2 =  $extra_time ." ". $TIME_FROM  ;
                // $vars['content'] = $this->aml_html->button_link(array('link' => 'page/managebranches/add', 'link_text' => ('Добавить филиал'), 'attr' => array('class' => 'button-link','target' => '_blank')));
				 
				$cnt = 0;
				$stid = $this->aml_oracle->execute("select count(*) as CNT from IPDL_PROCESS",__LINE__,
				array('CNT' => &$cnt));
				oci_fetch($stid);
				oci_free_statement($stid);
				//die(var_dump($cnt));
				 if ($cnt == 0) {
					$output = '<a href="javascript:job_ipdl_button(\'' . $ipdl_job_id . '\',\'' . $CLIENT_TYPE_ID . '\',\'' . $COUNTRY_CODE . '\',\'' . $EXCLUDE_COUNTRY . '\',\'' . $extra_time2 . '\')" class="button-link float">'.('Запустить Проверку').'</a></br>
					<a target="_blank" href="' . site_url('page/edit/ipdl_job/' . $ipdl_job_id) . '">' . $this->aml_html->img('edit.png') . '</a>
					' ; 
					//$vars['content'] = $output;
                }
				else {
					$output = '<a href="javascript:alert (\'Дождитесь окончания проверки\')" class="button-link float">'.('Проверка запущена').'</a></br>' ; 
					//$vars['content'] = $output;
				}
					
					$vars['content'] = $output;
					$vars['content'] .= $report;
			
                $vars['content'] .= '<div style="margin-top:50px">' .
				'<div style="margin: 0px 0px 10px 15px">Парматеры запуска</div>'.
                        $this->_html_table(array(
							('ID'),
                            ('Тип Лица'),
                            ('Код Страны'),
                            ('Исключать'),
                            ('Время Запуска')
                            
                        ),
                        $rows) . '</div>';
		//Состояние проверки<				
         $stid2 = $this->aml_oracle->execute('SELECT * FROM IPDL_PROCESS', __LINE__);
					while ($r2 = oci_fetch_array($stid2, OCI_ASSOC + OCI_RETURN_NULLS)) {
					$rows2[] = $r2;
                }
		$vars['content'] .= '<div style="margin-top:50px">' .
				'<div style="margin: 0px 0px 10px 15px">Состояние проверки</div>'.
                        $this->_html_table(array(
							('№'),
                            ('Время Запуска Job'),
                            ('Проверка списка ИПДЛ изменненых после ') 
                            
                        ),
                        $rows2) . '</div>';
		// История запуска проверки
		 $stid3 = $this->aml_oracle->execute("SELECT * FROM IPDL_check_log", __LINE__);
					while ($r3 = oci_fetch_array($stid3, OCI_ASSOC + OCI_RETURN_NULLS)) {
					$processID = $r3['PROCESS_ID'];
					$reportid = 35;
					$r3['EDIT_LINK'] = '<a href="javascript:runReportIPDL(\'' . $reportid . '\', \''.$processID.'\')"  >' . $this->aml_html->img('edit.png').'</a>';
				   $rows3[] = $r3;
				  
                }
				
		
		$vars['content'] .= '<div style="margin-top:50px">' .
				'<div style="margin: 0px 0px 10px 15px">История запуска проверки</div>'.
                        $this->_html_table(array(
							('№'),
                            ('Дата начала события'),
                            ('Дата окончания события'),
                            ('Параметры'),
                            ('Имя пользователя'),
                            ('Сформировать отчет')
							
							
                        ),
                        $rows3) . '</div>'; 
						 
							 	//die(var_DUMP($vars['processid']));
							
        $this->aml_context->set_general_vars($vars);
        $this->load->view('main', $vars);
		//return $processID;
     }
	 
	 function ipdl_job_pr () {
		$this->aml_auth->check_auth();
		$this->aml_security->check_privilege(37);
		 
		
		$query = "select * from TB_IPDL_PARAMS";
				
				
		$stid = $this->aml_oracle->execute($query, __LINE__,array('CLIENT_TYPE_ID' => &$CLIENT_TYPE_ID,'COUNTRY_CODE' => &$COUNTRY_CODE,'EXCLUDE_COUNTRY' =>&$EXCLUDE_COUNTRY,'DATE_FROM' =>&$DATE_FROM,'TIME_FROM' =>&$TIME_FROM));
		$client = oci_fetch_all($stid,$results2);
				//die(var_dump($results2));
		$time  = " ','DD.MM.YYYY HH24:MI:SS')";
		$todate  = "TO_DATE('";
		$extra_time = substr($DATE_FROM, 0,10);
		$extra_time2 =  $extra_time ." ". $TIME_FROM  ;
		$result = 1;
			 
		$stid =  $this->aml_oracle->execute(
                "BEGIN do_check_client_ipdl(:in_start_date, :in_client_type, :in_country_code, :in_exclude_country, :in_full_scan );" .
                "END;",__LINE__, array(
                	':in_start_date'=>$extra_time2,
                	':in_client_type'=>$CLIENT_TYPE_ID,
                	':in_country_code'=>$COUNTRY_CODE,
                	':in_exclude_country'=>$EXCLUDE_COUNTRY,
                	':in_full_scan'=>0
                )
            );		
					 
		header("Content-type: text/plain; charset=utf-8");
		if ($stid ) {
			echo "alert('Проверка по ИПДЛ запущена');location.reload();";
		}
		else {
			echo "alert('Ошибка при запуске првоерки ИПДЛ')";
		}
		return;
	}
	
	
	
	function ipdl_report($what = null,$p1 = null, $p2 = null, $variable=null) {
		//$test = $this->report_post( );
		$this->aml_auth->check_auth();
		$this->aml_security->check_privilege(37);
		$processID = intval($this->input->post('prID'));
		 $processID= $_SERVER['REQUEST_URI'];
			 
			$str = strpos($processID,"5");
			$processID = substr($processID, 43, $str);
	
		 $variable = $_POST['prID'];
		 //die(var_dump($variable));
		 //header("Content-type: text/plain; charset=utf-8");
		//die(var_dump($test));
		//die(var_dump($prID));
        $vars = array();
        $this->aml_auth->check_auth();
        
        $can_use_reports = $this->aml_security->check_privilege(5); // REPORTS
        if (!$can_use_reports) {
            $this->aml_html->error_page(array(('Отсутствуют права для данного действия.')));
        }
		
        $vars['page_name'] = ('Управление отчетами');
        $vars['content']   = '';
		  
		//die(var_dump( $what ." ". 'ddd' ." ". $variable ) );
        switch($what) {
		case 'deletearchive':
			
                $id = floatval($p1);
                if ($id <= 0) {
                    $this->aml_html->error_page(array(sprintf(('Неверное значение параметра %s'), 'id')));
                }
                $id2 = intval($p2);
				
                $this->aml_oracle->execute('DELETE FROM TB_REPORT_HISTORY t WHERE t.ID = :id',__LINE__, array(':id' => $id));
                header('Location:' .site_url('page/ipdl_report/run/' . $id2 . '/' . $report_id ));
				   
                die();
                break;
           case 'run': 
		   	$processID= $_SERVER['REQUEST_URI'];
			//die(var_dump($processID));
			$str = strpos($processID,"5");
			$processID = substr($processID, 43, $str);
			
		//die(var_dump($processID));
		   //die(var_dump($_POST));
		   
		 // die(var_dump($vars['content'] ));
		// $postom = $this->report_post();
		//die(var_dump($postom) );
				// Обновленный Запуск отчетов 29,05,13
				
				
				$report_id = intval($p1);
				 
				$post = array();
				foreach ( $_POST as $key => $value ) // получаем значения формы
				{
					if ($key =='op')
						continue;
					$post[$key] = $this->input->post($key);
				}
				$noxsl = ($p2 == 'noxsl') ? true : false;
                if ($report_id <= 0) {
                    $this->aml_html->error_page(array(sprintf(('Неверное значение параметра %s'), 'id')));
                }
				//$q = "begin pkg_report.get_report_params(:id_report); end;";
				$q = "SELECT name, param_type FROM TB_REPORT_PARAMETERS WHERE report_id = :id_report"; //получение параметров для отчёта
				$bindings = array(':id_report'=>$report_id);
				$stid = $this->aml_oracle->execute($q, __LINE__, $bindings);
				$params = "<?xml version=\"1.0\"?>
				<report>";
				
				//if ()
				
				while($r = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS))
				{
				
					$params .="<parameter>
					<param_name>".$r['NAME']."</param_name>
					<param_value>".$processID."</param_value>
					</parameter>";
				}
				 
				$params .= "</report>";	
				// die(var_dump($params));
				
				
				  
				
				$q_param = "select * from TB_REPORT_PARAMETERS WHERE REPORT_ID = :r_id"; 
				$bind_param = array(':r_id' =>$report_id);
				$stid_param = $this->aml_oracle->execute($q_param, __LINE__, $bind_param); 

                $block = '';
				$vars['content'] = '<form name  = "form1" method = "post" action = "">';
                $vars['content'] .= '<fieldset  class="viewdata" style="background:transparent;width:800px;margin-top:10px;margin-left:10px"><legend>' . ('Параметры отчета') . '</legend>';
                //$vars['content'] .= form_open('page/ipdl_report/run/' . $report_id . ($noxsl ? '/noxsl' : ''),array('class' => 'check-required-field-form','style' => 'padding:10px'));
				 
                    
                    
                
			 
				
				$block .= "</table>";
                $vars['content'] .= $block;
                $vars['content'] .= '<input type="submit" class="submit" name="submit" value="' . ('Запустить') . '">';
                $vars['content'] .= form_close();
                $vars['content'] .= '</fieldset>';
                $vars['content'] .= '</form>';
				
				$vars['content'] .= '<fieldset class="viewdata" style="background:transparent;width:800px;margin-top:10px;margin-left:10px;padding:10px"><legend>' . ('Архив отчетов') . '</legend>';
				$submit = $_POST['submit'];
				//die(var_dump($processID));
				   
				if ( isset($submit) ) 
				    {
					//формирование xml отчёта	
					$q = "BEGIN :return_result := RUN_REPORT_PROC(:rep_id, :rep_params, :user_id); END;"; 
					$tb_rep_hist_id = 9999999999;
					$bindings = array(':return_result'=>&$tb_rep_hist_id,':rep_id'=>$report_id, ':rep_params'=>$params, ':user_id'=>'ADMIN');
					//die(var_dump($bindings));
					$stid = $this->aml_oracle->execute($q, __LINE__, $bindings);
				   }
				 
                $stid = $this->aml_oracle->execute('SELECT id, p_report_id, p_date, p_username, p_report_body, decode(p_ready,1, 1 , 0, 0,  1) p_ready  ' .
                                                   'FROM TB_REPORT_HISTORY t '.
                                                   'WHERE t.p_report_id = :report_id ' .
                                                   'ORDER BY t.p_date DESC', __LINE__, array(':report_id' => $report_id));
                $rows = array();
                while($r = oci_fetch_array($stid, OCI_ASSOC)) {
                    $rows[] = array(
						'TB_NAME' => 1,
                        'P_DATE'     => $r['P_DATE'],
                        'P_USERNAME' => $r['P_USERNAME'],
						 'P_READY'	=> $r['P_READY'],
                        'P_DL_LINK'  => array('#data' => '<a href="' . site_url('page/save_report_for_xsl/' . $r['ID'] . '/' . $report_id) . '">' . $this->aml_html->img('save.png') . '</a>', '#attributes' => array('style' => 'text-align:center','title' => ('Скачать сформированный отчет')))
                        //'P_DEL_LINK' => array('#data' => '<a href="' . site_url('page/ipdl_report/deletearchive/' . $r['ID']) . '/' . $report_id . '">' . $this->aml_html->img('trash.png') . '</a>', '#attributes' => array('style' => 'text-align:center','onclick' => 'return confirm(\'' . ('Удалить?') . ' \')'))
                    );
                }
				// Обновленный Запуск отчетов 29,05,13
				
                $vars['content'] .= $this->_html_table(array(('Дата'),('Пользователь'),('Скачать'), ('Готов.')), $rows);
                $vars['content'] .= '</fieldset>';
                break;
            default:
                $q = "SELECT ID,ORDER_NUMBER,REPORT_NAME FROM TB_REPORTS t ORDER BY t.ORDER_NUMBER";
                $rows = array();
                $stid = $this->aml_oracle->execute($q,__LINE__);
                while($r = oci_fetch_array($stid, OCI_ASSOC | OCI_RETURN_NULLS)) {
                    $rows[] = $r;
                }
                $vars['rows'] = $rows;
                $vars['content'] = $this->load->view('reports/index', $vars, true);
        }
        $this->aml_context->set_general_vars($vars);
        $this->load->view('main', $vars);
    }
	
	// Функция для прорисовки <select> по КАТО
	function drawkatodict() { 
       
		$parent_id = $this->input->post("parent_id");
		$select_name = $this->input->post("select_name");
		$q = "select city name_rus,id from  (select sys_connect_by_path(name_rus, ' - ') city, id
							from dict_kato k
							start with parent_id =  :parent_id
							connect by prior id = parent_id
							order siblings by name_rus)";
		$stid = $this->aml_oracle->execute($q, __LINE__, array(':parent_id' => $parent_id  ), false);
        oci_fetch_all($stid, $results);
		for($i = 0; $i < count($results['ID']); $i++) {
			$result .= '<option value="' . $results['ID'][$i] . '"> [ ' . $results['ID'][$i] . ' ] '  . ($results['NAME_RUS'][$i]) . '</option>';
		} 
		echo $result; 
	}
   // Функция для прорисовки <select> по КАТО
}
?>