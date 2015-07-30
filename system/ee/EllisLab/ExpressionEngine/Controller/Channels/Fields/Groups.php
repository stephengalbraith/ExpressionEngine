<?php

namespace EllisLab\ExpressionEngine\Controller\Channels\Fields;

use EllisLab\ExpressionEngine\Library\CP\Table;
use EllisLab\ExpressionEngine\Controller\Channels\AbstractChannels as AbstractChannelsController;
use EllisLab\ExpressionEngine\Module\Channel\Model\ChannelFieldGroup;

/**
 * ExpressionEngine - by EllisLab
 *
 * @package		ExpressionEngine
 * @author		EllisLab Dev Team
 * @copyright	Copyright (c) 2003 - 2015, EllisLab, Inc.
 * @license		https://ellislab.com/expressionengine/user-guide/license.html
 * @link		http://ellislab.com
 * @since		Version 3.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * ExpressionEngine CP Channel\Fields\Groups Class
 *
 * @package		ExpressionEngine
 * @subpackage	Control Panel
 * @category	Control Panel
 * @author		EllisLab Dev Team
 * @link		http://ellislab.com
 */
class Groups extends AbstractChannelsController {

	public function __construct()
	{
		parent::__construct();

		if ( ! ee()->cp->allowed_group(
			'can_access_admin',
			'can_admin_channels',
			'can_access_content_prefs'
		))
		{
			show_error(lang('unauthorized_access'));
		}

		ee()->lang->loadfile('admin');
		ee()->lang->loadfile('admin_content');
	}

	public function groups()
	{
		if (ee()->input->post('bulk_action') == 'remove')
		{
			$this->remove(ee()->input->post('selection'));
			ee()->functions->redirect(ee('CP/URL', 'channels/fields/groups/groups'));
		}

		$groups = ee('Model')->get('ChannelFieldGroup')
			->filter('site_id', ee()->config->item('site_id'));

		$vars = array(
			'create_url' => ee('CP/URL', 'channels/fields/groups/create')
		);

		$table = $this->buildTableFromChannelGroupsQuery($groups);

		$vars['table'] = $table->viewData(ee('CP/URL', 'channels/fields/groups'));

		$vars['pagination'] = ee('CP/Pagination', $vars['table']['total_rows'])
			->perPage($vars['table']['limit'])
			->currentPage($vars['table']['page'])
			->render($vars['table']['base_url']);

		ee()->javascript->set_global('lang.remove_confirm', lang('group') . ': <b>### ' . lang('groups') . '</b>');
		ee()->cp->add_js_script(array(
			'file' => array(
				'cp/v3/confirm_remove',
			),
		));

		ee()->view->cp_page_title = lang('field_groups');
		ee()->view->cp_page_title_desc = lang('field_groups_desc');

		ee()->cp->render('channels/fields/groups/index', $vars);
	}

	public function create()
	{
		ee()->view->cp_breadcrumbs = array(
			ee('CP/URL', 'channels/fields/groups')->compile() => lang('field_groups'),
		);

		$vars = array(
			'ajax_validate' => TRUE,
			'base_url' => ee('CP/URL', 'channels/fields/groups/create'),
			'sections' => $this->form(),
			'save_btn_text' => 'btn_create_field_group',
			'save_btn_text_working' => 'btn_saving'
		);

		if ( ! empty($_POST))
		{
			$field_group = $this->setWithPost(ee('Model')->make('ChannelFieldGroup'));
			$result = $field_group->validate();

			if ($response = $this->ajaxValidation($result))
			{
			    return $response;
			}

			if ($result->isValid())
			{
				$field_group->save();

				ee('Alert')->makeInline('shared-form')
					->asSuccess()
					->withTitle(lang('create_field_group_success'))
					->addToBody(sprintf(lang('create_field_group_success_desc'), $field_group->group_name))
					->defer();

				ee()->session->set_flashdata('group_id', $field_group->group_id);

				ee()->functions->redirect(ee('CP/URL', 'channels/fields/groups'));
			}
			else
			{
				ee('Alert')->makeInline('shared-form')
					->asIssue()
					->withTitle(lang('create_field_group_error'))
					->addToBody(lang('create_field_group_error_desc'))
					->now();
			}
		}

		ee()->view->cp_page_title = lang('create_field_group');

		ee()->cp->render('settings/form', $vars);
	}

	public function edit($id)
	{
		$field_group = ee('Model')->get('ChannelFieldGroup', $id)->first();

		if ( ! $field_group)
		{
			show_404();
		}

		ee()->view->cp_breadcrumbs = array(
			ee('CP/URL', 'channels/fields/groups')->compile() => lang('field_groups'),
		);

		$vars = array(
			'ajax_validate' => TRUE,
			'base_url' => ee('CP/URL', 'channels/fields/groups/edit/' . $id),
			'sections' => $this->form($field_group),
			'save_btn_text' => 'btn_edit_field_group',
			'save_btn_text_working' => 'btn_saving'
		);

		if ( ! empty($_POST))
		{
			$field_group = $this->setWithPost($field_group);
			$result = $field_group->validate();

			if ($response = $this->ajaxValidation($result))
			{
			    return $response;
			}

			if ($result->isValid())
			{
				$field_group->save();

				ee('Alert')->makeInline('shared-form')
					->asSuccess()
					->withTitle(lang('edit_field_group_success'))
					->addToBody(sprintf(lang('edit_field_group_success_desc'), $field_group->group_name))
					->defer();

				ee()->functions->redirect(ee('CP/URL', 'channels/fields/groups/edit/' . $id));
			}
			else
			{
				ee('Alert')->makeInline('shared-form')
					->asIssue()
					->withTitle(lang('edit_field_group_error'))
					->addToBody(lang('edit_field_group_error_desc'))
					->now();
			}
		}

		ee()->view->cp_page_title = lang('edit_field_group');

		ee()->cp->render('settings/form', $vars);
	}

	private function setWithPost(ChannelFieldGroup $field_group)
	{
		$selected_field_ids = ee()->input->post('custom_fields');

		if ( ! empty($selected_field_ids))
		{
			$custom_fields = ee('Model')->get('ChannelField', $selected_field_ids)
				->filter('site_id', ee()->config->item('site_id'))
				->all();

			$field_group->ChannelFields = $custom_fields;
		}
		else
		{
			$field_group->ChannelFields = NULL;
		}

		$field_group->group_name = ee()->input->post('group_name');
		return $field_group;
	}

	private function form(ChannelFieldGroup $field_group = NULL)
	{
		if ( ! $field_group)
		{
			$field_group = ee('Model')->make('ChannelFieldGroup');
		}

		$custom_fields_options = array();
		$disabled_custom_fields_options = array();

		$fields = ee('Model')->get('ChannelField')
			->filter('site_id', ee()->config->item('site_id'))
			->all();

		foreach ($fields as $field)
		{
			$display = $field->field_label;

			$assigned_to = $field->ChannelFieldGroup;

			if ($assigned_to
				&& $assigned_to->group_id != $field_group->group_id)
			{
				$disabled_custom_fields_options[] = $field->field_id;

				$display =  '<s>' . $display . '</s>';
				$display .= ' <i>&mdash; ' . lang('assigned_to');
				$display .= ' <a href="' . ee('CP/URL', 'channels/fields/groups/edit/' . $assigned_to->group_id) . '">' . $assigned_to->group_name . '</a></i>';
			}

			$custom_fields_options[$field->field_id] = $display;
		}

		$custom_fields_value = array();

		$selected_fields = $field_group->ChannelFields;
		$custom_fields_value = ($selected_fields) ? $selected_fields->pluck('field_id') : array();

		// Alert to show only for new channels
		$alert = ee('Alert')->makeInline('permissions-warn')
			->asWarning()
			->addToBody(lang('create_field_group_warning'))
			->addToBody(sprintf(lang('create_field_group_warning2'), ee('CP/URL', 'channels/fields/create')))
			->cannotClose()
			->render();

		$sections = array(
			array(
				$alert,
				array(
					'title' => 'name',
					'desc' => '',
					'fields' => array(
						'group_name' => array(
							'type' => 'text',
							'value' => $field_group->group_name,
							'required' => TRUE
						)
					)
				),
				array(
					'title' => 'custom_fields',
					'desc' => 'custom_fields_desc',
					'fields' => array(
						'custom_fields' => array(
							'type' => 'checkbox',
							'choices' => $custom_fields_options,
							'disabled_choices' => $disabled_custom_fields_options,
							'value' => $custom_fields_value,
							'no_results' => array(
								'text' => 'custom_fields_not_found',
								'link_text' => 'create_new_field',
								'link_href' => ee('CP/URL', 'channels/fields/create')->compile()
							)
						)
					)
				)
			)
		);

		return $sections;
	}

	/**
	  *	 Check Field Group Name
	  */
	public function _field_group_name_checks($str, $group_id)
	{
		if ( ! preg_match("#^[a-zA-Z0-9_\-/\s]+$#i", $str))
		{
			ee()->lang->loadfile('admin');
			ee()->form_validation->set_message('_field_group_name_checks', lang('illegal_characters'));
			return FALSE;
		}

		$group = ee('Model')->get('ChannelFieldGroup')
			->filter('site_id', ee()->config->item('site_id'))
			->filter('group_name', $str);

		if ($group_id)
		{
			$group->filter('group_id', '!=', $group_id);
		}

		if ($group->count())
		{
			ee()->form_validation->set_message('_field_group_name_checks', lang('taken_field_group_name'));
			return FALSE;
		}

		return TRUE;
	}

	private function remove($group_ids)
	{
		if ( ! is_array($group_ids))
		{
			$group_ids = array($group_ids);
		}

		$field_groups = ee('Model')->get('ChannelFieldGroup', $group_ids)
			->filter('site_id', ee()->config->item('site_id'))
			->all();

		$group_names = $field_groups->pluck('group_name');

		$field_groups->delete();
		ee('Alert')->makeInline('field-groups')
			->asSuccess()
			->withTitle(lang('success'))
			->addToBody(lang('field_groups_removed_desc'))
			->addToBody($group_names)
			->defer();
	}

}
