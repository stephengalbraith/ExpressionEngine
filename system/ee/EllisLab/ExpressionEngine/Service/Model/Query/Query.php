<?php

namespace EllisLab\ExpressionEngine\Service\Model\Query;

use EllisLab\ExpressionEngine\Service\Model\DataStore;

/**
 * ExpressionEngine (https://expressionengine.com)
 *
 * @link      https://expressionengine.com/
 * @copyright Copyright (c) 2003-2017, EllisLab, Inc. (https://ellislab.com)
 * @license   https://expressionengine.com/license
 */

/**
 * ExpressionEngine Query
 *
 * @package		ExpressionEngine
 * @subpackage	Model
 * @category	Service
 * @author		EllisLab Dev Team
 * @link		https://ellislab.com
 */
abstract class Query {

	protected $store = NULL;
	protected $builder = NULL;

	public function __construct(DataStore $store, Builder $builder)
	{
		$this->store = $store;
		$this->builder = $builder;
	}

	abstract public function run();
}