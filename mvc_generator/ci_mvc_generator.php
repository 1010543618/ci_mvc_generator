<?php
require_once('./helpers/json_helper.php');
require_once('./helpers/common_helper.php');
require_once('./libraries/CM_Generator.php');
if ($_GET) {
	switch ($_GET['action']) {
		case 'clean_mvc':
			// 没有确认将不清除
			if ($_GET['confirm'] === 'false') {
				die('没有确认无法清除');
			}
			$config = array(
				'folder' => array(
						'm' => $_GET['m_folder'],
						'v' => $_GET['v_folder'],
						'c' => $_GET['c_folder']
					)
				);
			if (has_null($config)) {
				return_json('有必填项没填啊！', false);
			}
			$generator = new CM_Generator($config);
			if ($generator->clean_generated_mvc() === true) {
				return_string("删除成功！");
			}
			break;
		case 'generate_from_config':
			// 使用设置的配置和配置文件的配置生成mvc文件
			$config = array(
				'generator_config' => $_POST['generator_config'],
				//mvc相对于当前目录的路径
				'folder'=>array(
					'm' => $_POST['m_folder'],
					//给v_folder，c_folder添加上子路径
					'v' => $_POST['v_folder'] . ($_POST['v_child_folder'] ? '/'.$_POST['v_child_folder'] : ''),
					'c' => $_POST['c_folder'] . ($_POST['c_child_folder'] ? '/'.$_POST['c_child_folder'] : ''),
					//给v_child_folder，c_child_folder添加"/"为了在ci的URL函数中使用
					'v_child' => $_POST['v_child_folder'] ? $_POST['v_child_folder'].'/' : '',
					'c_child' => $_POST['v_child_folder'] ? $_POST['c_child_folder'].'/' : ''
					)
				);
			$generator = new CM_Generator($config);
			$info = $generator->output_mvc_file();
			return_string($info['msg']);
		default:
			break;
	}
}
if ($_POST && isset($_POST['step'])) {
	switch ($_POST['step']) {
		case '1':
			// 通过配置找全部表
			$config = array(
				'host' => $_POST['host'],
				'user' => $_POST['user'],
				'password' => $_POST['pwd'],
				'db' => $_POST['db']
				);
			if (has_null($config)) {
				return_json('有必填项没填啊！', false);
			}
			$generator = new CM_Generator($config);
			return_json($generator->get_database_tebles(), true);
			break;
		case '2':
			// 通过选择的表生成配置文件
			$config = array(
				'host' => $_POST['host'],
				'user' => $_POST['user'],
				'password' => $_POST['pwd'],
				'db' => $_POST['db'],
				'tables' => isset($_POST['tables']) ? $_POST['tables'] : null
				);
			if ($config['tables'] == null) {
				return_json('请至少选择一张表', false);
			}
			if (has_null($config)) {
				return_json('有必填项没填啊！', false);
			}
			
			$generator = new CM_Generator($config);
			$tables_bean = $generator->create_tables_bean();
			if ($tables_bean === false) {
				return_json('创建配置文件失败（错误消息：写入文件失败）', false);
			}
			// 将tables_bean输出到config
			if (!file_put_contents('./config.json', $tables_bean))
				return_json('创建配置文件失败（错误消息：写入文件失败）', false);
			return_json($tables_bean, true);
			break;
		case '3_4':
			// 使用设置的配置和配置文件的配置生成mvc文件
			$config = array(
				'generator_config' => $_POST['generator_config'],
				//mvc相对于当前目录的路径
				'folder'=>array(
					'm' => $_POST['m_folder'],
					//给v_folder，c_folder添加上子路径
					'v' => $_POST['v_folder'] . ($_POST['v_child_folder'] ? '/'.$_POST['v_child_folder'] : ''),
					'c' => $_POST['c_folder'] . ($_POST['c_child_folder'] ? '/'.$_POST['c_child_folder'] : ''),
					//给v_child_folder，c_child_folder添加"/"为了在ci的URL函数中使用
					'v_child' => $_POST['v_child_folder'] ? $_POST['v_child_folder'].'/' : '',
					'c_child' => $_POST['v_child_folder'] ? $_POST['c_child_folder'].'/' : ''
					)
				);
					
			// 判断是否保存generator_config
			if (isset($_POST['is_save_config']) &&  $_POST['is_save_config'] == 'true') {
				file_put_contents('./config.json', $config['generator_config']);
			}
			$generator = new CM_Generator($config);
			$info = $generator->output_mvc_file();
			echo json_encode($info);
			die;
		default:
			break;
	}
}
