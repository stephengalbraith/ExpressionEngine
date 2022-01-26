/*!
 * This source file is part of the open source project
 * ExpressionEngine (https://expressionengine.com)
 *
 * @link      https://expressionengine.com/
 * @copyright Copyright (c) 2003-2021, Packet Tide, LLC (https://www.packettide.com)
 * @license   https://expressionengine.com/license Licensed under Apache License, Version 2.0
 */

(function($) {

"use strict";

var Conditional = window.Conditional = {

	// Event handlers stored here, direct access outside only from
	// Conditional.Publish class
	_eventHandlers: [],

	/**
	 * Binds an event to a fieldtype
	 *
	 * Available events are:
	 * 'display' - When a row is displayed
	 * 'remove' - When a row is deleted
	 *
	 * @param	{string}	fieldtypeName	Class name of fieldtype so the
	 *				correct cell object can be passed to the handler
	 * @param	{string}	action			Name of action
	 * @param	{func}		func			Callback function for event
	 */
	on: function(fieldtypeName, action, func) {
		if (this._eventHandlers[action] == undefined) {
			this._eventHandlers[action] = [];
		}

		// Each fieldtype gets one method per handler
		this._eventHandlers[action][fieldtypeName] = func;
	}
};

/**
 * Conditional Publish class
 *
 * @param	{string}	field		Selector of table to instantiate as a Conditional
 */
Conditional.Publish = function(field, settings) {
	this.root = $(field);
	this.mainParentContainer = this.root.parents('#fieldset-condition_fields');
	this.blankSet = $('.conditionset-temlates-row', this.mainParentContainer);
	this.activeElement = this.root.not(this.blankSet);
	this.setParent = $('#fieldset-condition_fields').find('.field-conditionset-wrapper');
	this.blankRow = $('.rule-blank-row', this.activeElement);
	this.rowContainer = this.activeElement.find('.rules');
	this.addButtonToolbar = $('[rel=add_row]', this.activeElement);
	this.deleteButtonsSelector = '.delete_rule [rel=remove_row]';
	this.deleteSetButtonsSelector = '.remove-set';
	this.rowSelector = '.rule';
	this.cellSelector = '.rule > div';

	this.init();

	this.eventHandlers = [];
}

Conditional.Publish.prototype = {

	init: function() {
		this._firstCloneSet();
		this._bindAddButton();
		this._bindDeleteButton();
		this._bindAddSetButton();
		this._bindDeleteSetButton();

		// Store the original row count so we can properly increment new
		// row placeholder IDs in _addRow()
		this.original_row_count = this._getRows().length;
		this.original_set_count = this._getSets().length;

		// Disable input elements in our blank template container so they
		// don't get submitted on form submission
		this.blankRow.find(':input').attr('disabled', 'disabled');
	},

	_getRows: function() {
		return this.rowContainer.children(this.rowSelector).not(this.blankRow);
	},

	_getSets: function() {
		return this.mainParentContainer.find(this.activeElement);
	},

	_addRuleRow: function(cloneElementParent) {
		// Clone our blank row

		var el = cloneElementParent.find('.rule-blank-row').clone();

		el.removeClass('rule-blank-row');
		el.removeClass('hidden');

		// Increment namespacing on inputs
		this.original_row_count++;

		el.html(
			el.html().replace(
				RegExp('new_rule_row_[0-9]{1,}', 'g'),
				'new_rule_row_' + this.original_row_count
			)
		);

		el.html(
			el.html().replace(
				RegExp('new_row_[0-9]{1,}', 'g'),
				'new_row_' + this.original_row_count
			)
		);

		// Add the new row ID to the field data
		$('> '+this.cellSelector, el).attr(
			'data-new-rule-row-id',
			'new_rule_row_' + this.original_row_count
		);

		// Enable remove button
		el.find('[rel=remove_row]').removeAttr('disabled');

		// Append the row to the end of the row container
		cloneElementParent.find('.rules').append(el);

		// Bind the new row's inputs to AJAX form validation
		if (EE.cp && EE.cp.formValidation !== undefined) {
			EE.cp.formValidation.bindInputs(el);
		}

		return el;
	},

	_bindAddButton: function() {
		var that = this;
		$('body').on('click', '.condition-btn', this.activeElement, function(event) {
			var cloneElementParent = $(this).parents('.field-conditionset');
			event.preventDefault();
			that._addRuleRow(cloneElementParent);
			Dropdown.renderFields();
		});
	},

	/**
	 * Binds click listener to Delete button in row column to delete the row
	 */
	_bindDeleteButton: function() {
		var that = this;

		$('body').on('click', that.deleteButtonsSelector, function(event) {
			event.preventDefault();

			var row = $(this).parents('.rule');

			// Remove the row
			row.remove();
		});
	},

	_addSetBlock: function() {
		// Clone our blank row

		var set = this.blankSet.clone();

		set.removeClass('conditionset-temlates-row');
		set.removeClass('hidden');

		// Increment namespacing on inputs
		this.original_set_count++;

		set.html(
			set.html().replace(
				RegExp('new_conditionset_block[0-9]{1,}', 'g'),
				'new_conditionset_block_' + this.original_set_count
			).replace(
				RegExp('new_set_[0-9]{1,}', 'g'),
				'new_set_' + this.original_set_count
			)
		);

		// Add the new row ID to the field data
		$(set).attr(
			'id',
			'new_conditionset_block_' + this.original_set_count
		);

		// Enable remove button
		set.find('[rel=remove_row]').removeAttr('disabled');

		// Append the row to the end of the row container
		this.setParent.append(set);

		// // Bind the new row's inputs to AJAX form validation
		if (EE.cp && EE.cp.formValidation !== undefined) {
			EE.cp.formValidation.bindInputs(set);
		}

		return set;
	},

	_bindAddSetButton: function() {
		var that = this;

		$('body').on('click', 'a.add-set', this.activeElement, function(event) {
			event.preventDefault();
			that._addSetBlock();

			if (that.original_set_count > 1) {
				$('.remove-set', this.activeElement).show();
			}

			Dropdown.renderFields();
		});
	},
	/**
	 * Binds click listener to Delete button in row column to delete the set
	 */
	_bindDeleteSetButton: function() {
		var that = this;

		$('body').on('click', that.deleteSetButtonsSelector, function(event) {
			event.preventDefault();

			var set = $(this).parents('.conditionset-item');

			// Remove the set
			set.remove();
			var set_count = that.mainParentContainer.find('.conditionset-item').not('.conditionset-temlates-row');


			if (set_count.length == 1) {
				$('.remove-set', this.activeElement).hide();
			}
		});
	},

	_firstCloneSet: function() {
		$('body').on('click', '[data-toggle-for="field_is_conditional"]', function(event) {
			var setCount = $('#fieldset-condition_fields').find('.conditionset-item');

			if (setCount.length == 1) {
				$('#fieldset-condition_fields .add-set').trigger('click');
				$('#fieldset-condition_fields .condition-btn').trigger('click');
			}
		})
	}
}

function initRules () {
	var el = $('.conditionset-item');
	return new Conditional.Publish(el);
}


$(document).ready(function() {
	initRules();

	function checkFieldType(fieldName) {
		var fieldType;

		$.each(EE.fields, function(i, val) {
			if (fieldName == val['field_label']) {
				fieldType = val['field_type'];
			}
		});
		return fieldType;
	}

	EE.cp.show_hide_rule_operator_field = function(element, input) {

		if ( ! $(element).size()) {
			return;
		}

		var fieldName = element.label;
		var parensRow = $(input).parents('.rule');
		var evaluationRules;
		var operator = {};

		parensRow.find('.condition-rule-value-wrap input').removeAttr('disabled');
		parensRow.find('.condition-rule-operator-wrap .condition-rule-operator').remove();
		parensRow.find('.condition-rule-value-wrap input').remove();

		$.each(EE.fields, function(i, val) {
			if (fieldName == val['field_label']) {
				evaluationRules = val['evaluationRules'];
			}
		});

		var fieldType = checkFieldType(fieldName);

		$.each(evaluationRules, function(item, value){
			operator[item] =  value['text'];
		});

		var selectedItem = Object.keys(operator)[0];

		var evaluation_rule_name = parensRow.find('.condition-rule-field-wrap .condition-rule-field').attr('data-input-value').replace('condition_field_id', 'evaluation_rule');
		var value_name = parensRow.find('.condition-rule-field-wrap .condition-rule-field').attr('data-input-value').replace('condition_field_id', 'value');

		var options = {
			name: evaluation_rule_name,
			items: operator,
			initialItems: operator,
			selected: selectedItem,
			disabled: false,
			tooMany: 20,
			limit: 100,
			groupToggle: null,
			emptyText: "Select a Field",
			conditionalRule: 'operator',
		};

		var dataDropdownReact = btoa(JSON.stringify(options));

		parensRow.find('.condition-rule-operator-wrap').append('<div data-input-value="'+evaluation_rule_name+'" class="condition-rule-operator" data-dropdown-react='+dataDropdownReact+'></div>');
		parensRow.find('.condition-rule-value-wrap').append('<input type="text" name="'+value_name+'">');

		Dropdown.renderFields();
		parensRow.find('.condition-rule-operator-wrap .empty-select').hide();
		parensRow.find('.condition-rule-operator-wrap .condition-rule-operator').show();

		EE.cp.show_hide_value_field(fieldType, selectedItem, parensRow);
	}

	EE.cp.check_operator_value = function(item, input) {
		var operatorVal = item.value;
		var parensRow = $(input).parents('.rule');
		var ruleLabel = parensRow.find('.condition-rule-field-wrap .select__dropdown-item--selected span:not(".short-name")').text();

		var rulefieldType = checkFieldType(ruleLabel);

		EE.cp.show_hide_value_field(rulefieldType, operatorVal, parensRow);
	} 

	EE.cp.show_hide_value_field = function(firstSelectVal, secondSelectVal, parentRow) {
		var enabled = true;
		var evaluationRules;

		$.each(EE.fields, function(i, val) {
			if (firstSelectVal == val['field_type']) {
				evaluationRules = val['evaluationRules'];
			}
		});

		$.each(evaluationRules, function(el, val) {
			if (secondSelectVal == el) {
				if (val['type'] == null) {
					parentRow.find('.condition-rule-value-wrap').children().hide();
				} else {
					parentRow.find('.condition-rule-value-wrap').children().show();
				}
			}
		})
	}

	$('body').on('mousemove', '.condition-rule-field-wrap .button-segment', function(e) {
		var X = e.offsetX;
		var Y = e.offsetY;
		var top = Y + 20 + 'px';
		var left = X + 20 + 'px';
		if ($(this).find('.tooltiptext').length) {
			$(this).find('.tooltiptext').css({
				display: 'block',
				top: top,
				left: left
			});
		}
	});

	$('body').on('mouseout', '.condition-rule-field-wrap .button-segment', function(e) {
		if ($(this).find('.tooltiptext').length) {
			$(this).find('.tooltiptext').css({display: "none"});
		}
	});
});

})(jQuery);
