<?php
/**
 * ExpressionEngine (https://expressionengine.com)
 *
 * @link      https://expressionengine.com/
 * @copyright Copyright (c) 2003-2017, EllisLab, Inc. (https://ellislab.com)
 * @license   https://expressionengine.com/license
 */

namespace EllisLab\ExpressionEngine\Service\Validation\Rule;

use EllisLab\ExpressionEngine\Service\Validation\ValidationRule;

/**
 * Alphabetical, Dashes, Periods, and Emoji Validation Rule
 */
class AlphaDashPeriodEmoji extends ValidationRule {

	public function validate($key, $value)
	{
		$emojiless = $this->stripEmojis($value);

		// If the only value we were given were emoji(s) then it's valid
		if (strlen($value) > 0 && strlen($emojiless) < 1)
		{
			return TRUE;
		}

		return (bool) preg_match("/^([-a-z0-9_.-])+$/i", $emojiless);
	}

	protected function stripEmojis($value)
	{
		$regex = '/(?:'.EMOJI_REGEX.')/u';

		$value = preg_replace($regex, '', $value);

		return $value;
	}

	public function getLanguageKey()
	{
		return 'alpha_dash_period';
	}
}

// EOF
