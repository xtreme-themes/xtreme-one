<?php

if (!function_exists ('add_action')) {
	header('Status: 403 Forbidden');
	header('HTTP/1.1 403 Forbidden');
	exit();
}

class xtreme_option_measures {
	function __construct() {
		$this->units['%'] = array('convert' => 'floatval', 'limits' => array(0,100) );
		$this->units['em'] = array('convert' => 'floatval', 'limits' => array(0,120) );
		$this->units['px'] = array('convert' => 'intval', 'limits' => array(0,1600) );
	}

	function get_safe_value_of($value, $unit, $limits, $enums) {
		$v = 0; $u = 'px';
		if (in_array($unit, array_keys($this->units))) $u = $unit;
		$v = call_user_func($this->units[$u]['convert'], $value);
		list($low, $high) = $this->units[$u]['limits'];
		if (is_array($limits)) {
			$low = call_user_func($this->units[$u]['convert'], $limits[$u][0]);
			$high = call_user_func($this->units[$u]['convert'], $limits[$u][1]);
		}
		$v = min(max($low, $v), $high);
		if (is_array($enums)) {
			if (!in_array($v, (array)$enums[$u])) {
				$v = call_user_func($this->units[$u]['convert'], $enums[$u][0]);
			}
		}
		return array($v, $u);
	}
}

class xtreme_option_base {

	/**
	 * @param $owner
	 * @param $owner_switch_index
	 * @param $name
	 * @param $desc
	 * @return xtreme_option_base
	 */
	function __construct(&$owner, $owner_switch_index, $name, $desc) {
		$this->owner		= $owner;
		$this->name 		= $name;
		$this->desc		= (empty($desc) ? __('*missing*', XF_TEXTDOMAIN) : $desc);
		$this->value		= false;
		$this->can_switched     = $owner_switch_index;
	}

	function full_name() { return $this->owner->name.'-'.$this->name; }

	function _tr_start() {
		if (is_array($this->can_switched)) {
			$class='';
			$off = false;
			foreach($this->can_switched as $number) {
				if ($this->owner->options[$number]->value == false) {
					$off = true;
					$class .= ' level-'.$this->owner->options[$number]->level;
				}
				$class .= ' x-level-'.$this->owner->options[$number]->level;
			}
			if ($off) $class .= ' x-hidden';
				return '<tr class="x-switchable r-'.$this->name.$class.'">';
		} else {
			if ($this->can_switched !== false) return '<tr class="x-switchable r-'.$this->name.($this->owner->options[$this->can_switched]->value == false ? ' level-1 x-hidden' : '').' x-level-1">';
		}
		return '<tr class="r-'.$this->name.'">';
	}

	function as_table_row() {
		return $this->_tr_start().'<td class="x_description">'.$this->desc.'</td><td class="x_auto">'.$this->to_html().'</td></tr>';
	}

	function to_html() {
		return __('*missing*', XF_TEXTDOMAIN);
	}

	function load(&$array) {
		if (isset($array[$this->name])) {
			$this->value = $array[$this->name]['value'];
		}
	}

	function save(&$array) {
		if (!isset($array[$this->name])) {
			$array[$this->name] = array();
		}
		$array[$this->name]['value'] = $this->value;
	}
}

class xtreme_option_numeric extends xtreme_option_base {
	function __construct(&$owner, $owner_switch_index, $name, $desc, $value, $unit, &$measures, $limits = false, $enums = false) {
		parent::__construct($owner, $owner_switch_index, $name, $desc);
		$this->measures = $measures;
		$this->limits	= $limits;
		$this->enums	= $enums;
		list($this->value, $this->unit) = $measures->get_safe_value_of($value, $unit, $limits, $enums);
	}

	function as_table_row() {
		return $this->_tr_start().'<td class="x_description">'.esc_attr($this->desc).'</td><td class="x_auto">'.$this->to_html($this->owner->name).'</td></tr>';
	}

	function to_html() {
		if ($this->enums) {
			$html = '<select name="'.$this->owner->name.'-'.$this->name.'-value" tabindex="1" id="'.$this->owner->name.'-'.$this->name.'-value">';
			foreach($this->enums[$this->unit] as $val) { $html .= '<option value="'.esc_attr($val).'"'.($val == $this->value ? ' selected="selected"' : '').'>'.esc_attr($val).$this->unit.'</option>'; }
				$html .= '</select><input type="hidden" name="'.$this->owner->name.'-'.$this->name.'-unit" value="'.$this->unit.'" />';
		}else{
			$html = '<input type="text" value="'.esc_attr($this->value).'" size="6" maxlength="6" name="'.$this->owner->name.'-'.$this->name.'-value" tabindex="1" id="'.$this->owner->name.'-'.$this->name.'-value"/>';
			$html .= '<select name="'.$this->owner->name.'-'.$this->name.'-unit" tabindex="1" id="'.$this->owner->name.'-'.$this->name.'-unit" >';
			$u = (is_array($this->limits) ? array_keys($this->limits) : array_keys($this->measures->units) );
			foreach($u as $val) { $html .= '<option value="'.esc_attr($val).'"'.($val == $this->unit ? ' selected="selected"' : '').'>'.esc_attr($val).'</option>'; }
			$html .= '</select>';
		}
		return $html;
	}

	function load(&$array) {
		parent::load($array);
		if( isset( $array[ $this->name ] ) && in_array($array[$this->name]['unit'], array_keys($this->measures->units)))
			$this->unit	= $array[$this->name]['unit'];
		list($this->value, $this->unit) = $this->measures->get_safe_value_of($this->value, $this->unit, $this->limits, $this->enums);
	}

	function save(&$array) {
		parent::save($array);
		$array[$this->name]['unit'] = $this->unit;
	}
}

class xtreme_option_textfield extends xtreme_option_base {
	function __construct(&$owner, $owner_switch_index, $name, $desc, $value, $noempty = false) {
		parent::__construct($owner, $owner_switch_index, $name, $desc);
		$this->noempty = $noempty;
		(!false === $this->noempty || trim($value) !== '') ? $this->value = $value : $this->value = $this->noempty;
	}

	function as_table_row() {
		return $this->_tr_start().'<td class="x_description">'.esc_attr($this->desc).'</td><td class="x_auto">'.$this->to_html($this->owner->name).'</td></tr>';
	}

	function to_html() {
		$html = '<input type="text" value="'.esc_attr($this->value).'" size="40" maxlength="90" name="'.$this->owner->name.'-'.$this->name.'-value" tabindex="1" id="'.$this->owner->name.'-'.$this->name.'-value" />';
		return $html;
	}

	function load(&$array) {
		parent::load($array);
		(!false === $this->noempty || trim($this->value) !== '') ? $this->value = $this->value : $this->value = $this->noempty;
	}
}

class xtreme_option_textarea extends xtreme_option_base {
	function __construct(&$owner, $owner_switch_index, $name, $desc, $value) {
		parent::__construct($owner, $owner_switch_index, $name, $desc);
		$this->value = $value;
	}

	function as_table_row() {
		return $this->_tr_start().'<td class="x_description">'.esc_attr($this->desc).'</td><td class="x_auto">'.$this->to_html($this->owner->name).'</td></tr>';
	}

	function to_html() {
		$html = '<textarea cols="30" rows="4" name="'.$this->owner->name.'-'.$this->name.'-value" tabindex="1" id="'.$this->owner->name.'-'.$this->name.'-value" >'.wp_filter_post_kses($this->value) .'</textarea>';
		return $html;
	}

}

class xtreme_option_description extends xtreme_option_base {
	function __construct(&$owner, $owner_switch_index, $name, $desc) {
		parent::__construct($owner, $owner_switch_index, $name, $desc);
		$this->value = (string)$desc;
	}

	function as_table_row() {
		return $this->_tr_start().'<td colspan="2">'.$this->to_html().'</td></tr>';
	}

	function to_html() {
		return $this->value;
	}

	function load(&$array) {}

	function save(&$array) {}
}

class xtreme_option_hidden extends xtreme_option_base {
	function __construct(&$owner, $owner_switch_index, $name, $desc, $value) {
		parent::__construct($owner, $owner_switch_index, $name, $desc);
		$this->value 	= $value;
	}
	function to_html() {
		return '<input type="hidden" value="'.esc_attr($this->value).'" name="'.$this->owner->name.'-'.$this->name.'-value" />';
	}
	function as_table_row() {
		return '<tr style="display:none;"><td colspan="2">'.$this->to_html().'</td></tr>';
	}
}

class xtreme_option_bool extends xtreme_option_base {
	function __construct(&$owner, $owner_switch_index, $name, $desc, $value) {
		parent::__construct($owner, $owner_switch_index, $name, $desc);
		$this->value 	= ($value === 'true' ? true : ($value === 'false' ? false : (bool)$value));
	}
	function to_html() {
		$html = '<input type="hidden" value="0" name="'.$this->owner->name.'-'.$this->name.'-value" />';
		return $html.'<input class="no-border" type="checkbox" value="1" tabindex="11" name="'.$this->owner->name.'-'.$this->name.'-value" id="'.$this->owner->name.'-'.$this->name.'-value"'.($this->value ? ' checked="checked"' : '').' />';
	}

	function load(&$array) {
		$value = isset($array[$this->name]['value']) ? $array[$this->name]['value'] : $this->value;
		$this->value = ($value == 'true' ? true : ($value == 'false' ? false : (bool)$value));
	}
}

class xtreme_option_switcher extends xtreme_option_bool {
	function __construct(&$owner, $owner_switch_index, $name, $desc, $value, $level = 1) {
		parent::__construct($owner, $owner_switch_index, $name, $desc, $value);
		$this->level = $level;
	}

	function to_html() {
		$html = '<input type="hidden" value="0" name="'.$this->owner->name.'-'.$this->name.'-value" />';
		return $html.'<input type="checkbox" value="1" tabindex="11" name="'.$this->owner->name.'-'.$this->name.'-value" id="'.$this->owner->name.'-'.$this->name.'-value"'.($this->value ? ' checked="checked"' : '').' class="x-switcher no-border level-'.$this->level.'"  />';
	}
}

class xtreme_option_select extends xtreme_option_base {
	function __construct(&$owner, $owner_switch_index, $name, $desc, $value, $items) {
		parent::__construct($owner, $owner_switch_index, $name, $desc);
		$this->value 	= (string)$value;
		$this->items 	= (is_array($items) ? $items : array($this->value));
	}

	function to_html() {
		$html = '<select name="'.$this->owner->name.'-'.$this->name.'-value" id="'.$this->owner->name.'-'.$this->name.'-value" tabindex="1">';
		foreach($this->items as $file) {
			$html .= '<option value="'.esc_attr($file).'"'.($this->value == $file ? ' selected="selected"' : '').'>'.esc_attr($file).'</option>';
		}
		return $html.'</select>';
	}
}

class xtreme_option_select_pair extends xtreme_option_base {
	function __construct(&$owner, $owner_switch_index, $name, $desc, $value, $items) {
		parent::__construct($owner, $owner_switch_index, $name, $desc);
		$this->value = (string)$value;
		$this->items = (is_array($items) ? $items : array($this->value));
	}
	function to_html() {
		$html = '<select name="'.$this->owner->name.'-'.$this->name.'-value" id="'.$this->owner->name.'-'.$this->name.'-value" tabindex="1">';
		foreach($this->items as $k => $v) {
			$html .= '<option value="'.esc_attr($k).'"'.($this->value == $k ? ' selected="selected"' : '').'>'.esc_attr($v).'</option>';
		}
		return $html.'</select>';
	}
}

class xtreme_option_select_optgroup extends xtreme_option_base {
	function __construct(&$owner, $owner_switch_index, $name, $desc, $value, $items, $label) {
		parent::__construct($owner, $owner_switch_index, $name, $desc);
		$this->value = (string) $value;
                $this->items = (is_array($items) ? $items : array($this->value));
                $this->label = (string) $label;
	}

	function to_html() {
		$html = '<select name="'.$this->owner->name.'-'.$this->name.'-value" id="'.$this->owner->name.'-'.$this->name.'-value" tabindex="1" >';
		foreach($this->items as $group) {
			$gruppe[] = $group['group'];
		}
		$optgroup = array_unique($gruppe);
		foreach($optgroup as $v) {
			$html .= '<optgroup label="'. esc_attr($v). ' ' .esc_attr($this->label) . '" rel="' . esc_attr($v) . '">';
			foreach($this->items as $k) {
				if($k['group'] == $v) {
					$html .= '<option value="'.esc_attr($k['value']).'"'.($this->value == $k['value'] ? ' selected="selected"' : '').'>'.esc_attr($k['label']).'</option>';
				}
			}
			$html .= '</optgroup>';
		}
		return $html.'</select>';
	}
}

class xtreme_option_locate_files extends xtreme_option_base {
	function __construct(&$owner, $owner_switch_index, $name, $desc, $value, $dir, $type, $editable = false) {
		parent::__construct($owner, $owner_switch_index, $name, $desc);
		$this->value 	= (string)$value;
		$this->dir 	= array( STYLESHEETPATH . $dir , TEMPLATEPATH . $dir );
		$this->type 	= $type;
		$this->editable	= $editable;
		$this->files 	= array();

		foreach ($this->dir as $folder) {
			if ( is_dir($folder) && is_readable($folder)) {
				$dh  = opendir($folder);
				while (false !== ($filename = readdir($dh))) {
					if (preg_match("/(.*)\.$type$/", $filename, $hits))
						$this->files[] = $hits[1];
				}
				closedir($dh);
			}
		}

		if (isset($this->files) && !empty($this->files)) {
			$this->files = array_unique($this->files);
			if (!in_array($this->value, $this->files)) {
				$this->value = $this->files[0];
			}
		}
	}

	//hidden field mit angabe ob childtheme oder framework
	function to_html() {
		$html = '<select name="'.$this->owner->name.'-'.$this->name.'-value" id="'.$this->owner->name.'-'.$this->name.'-value">';
		foreach($this->files as $file) {
			$selected = ($this->value == $file ? ' selected="selected"' : '');
			$html .= '<option value="'.esc_attr($file).'" '.$selected.'>'.esc_attr($file).'</option>';
		}
		$html .= '</select>';
		return $html;
	}
}
