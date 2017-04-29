<?php

namespace EllisLab\ExpressionEngine\Model\Channel;

use EllisLab\ExpressionEngine\Service\Model\Model;

/**
 * ExpressionEngine (https://expressionengine.com)
 *
 * @link      https://expressionengine.com/
 * @copyright Copyright (c) 2003-2017, EllisLab, Inc. (https://ellislab.com)
 * @license   https://expressionengine.com/license
 */

/**
 * ExpressionEngine Channel Form Settings Model
 *
 * @package		ExpressionEngine
 * @subpackage	File
 * @category	Model
 * @author		EllisLab Dev Team
 * @link		https://ellislab.com
 */
class ChannelFormSettings extends Model {

	protected static $_primary_key = 'channel_form_settings_id';
	protected static $_table_name = 'channel_form_settings';

	protected static $_relationships = array(
		'Channel' => array(
			'type' => 'belongsTo'
		)
	);

	protected $channel_form_settings_id;
	protected $site_id;
	protected $channel_id;
	protected $default_status;
	protected $allow_guest_posts;
	protected $default_author;
}

// EOF
