<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * ExpressionEngine (https://expressionengine.com)
 *
 * @link      https://expressionengine.com/
 * @copyright Copyright (c) 2003-2017, EllisLab, Inc. (https://ellislab.com)
 * @license   https://expressionengine.com/license
 */

/**
 * ExpressionEngine File Module
 *
 * @package		ExpressionEngine
 * @subpackage	Modules
 * @category	Modules
 * @author		EllisLab Dev Team
 * @link		https://ellislab.com
 */

class File_upd {

	var $version		= '1.0.0';

	/**
	 * Module Installer
	 *
	 * @access	public
	 * @return	bool
	 */
	function install()
	{
		$data = array(
					'module_name' => 'File',
					'module_version' => $this->version,
					'has_cp_backend' => 'n'
					);

		ee()->db->insert('modules', $data);

		return TRUE;
	}

	/**
	 * Module Uninstaller
	 *
	 * @access	public
	 * @return	bool
	 */
	function uninstall()
	{
		ee()->db->select('module_id');
		ee()->db->from('modules');
		ee()->db->where('module_name', 'File');
		$query = ee()->db->get();

		ee()->db->delete('module_member_groups', array('module_id' => $query->row('module_id')));
		ee()->db->delete('modules', array('module_name' => 'File'));

		return TRUE;
	}

	/**
	 * Module Updater
	 *
	 * @access	public
	 * @return	bool
	 */
	function update()
	{
		return TRUE;
	}

}
// END CLASS

// EOF
