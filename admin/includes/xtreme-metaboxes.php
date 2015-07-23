<?php

class xc_base {
	function __construct(&$owner, $name, $desc) {
		$this->owner = $owner;
		$this->name  = $name;
		$this->desc  =  $desc;
		$this->options = array();
	}

	function full_name() { return $this->name; }

	function load(&$array) {
		$count = count($this->options);
		for ($i=0; $i < $count; $i++) {
			$this->options[$i]->load($array[$this->name]);
		}
    }

	function save(&$array) {
		$count = count($this->options);
		for ($i=0; $i < $count; $i++) {
		$this->options[$i]->save($array[$this->name]);
		}
	}

	function get_measures() {
		return $owner->get_measures();
	}

	function as_table() {
		$html = '<table class="form-table x-template"><tbody>';
		foreach($this->options as $row) {
			$html .= $row->as_table_row();
		}
		return 	$html.'</tbody></table>';
	}
}

function xtreme_is_metabox_class($class) {
	if(class_exists($class)) {
		if (get_parent_class($class) == 'xc_base')
			return true;
		else
			return xtreme_is_metabox_class(get_parent_class($class));
	}
	return false;
}

function xtreme_build_metaboxes($classes, &$owner) {
	$boxes = array();
	if (is_array($classes)) {
		foreach($classes as $class) {
			if (xtreme_is_metabox_class($class)) {
				$box = new $class($owner);
				$boxes[$box->name] = $box;
			}
		}
	}
	return $boxes;
}

function xtreme_repair_metaboxes_sort_order($classes, $pagehook) {
	$user = get_current_user_id();
	$meta = get_user_meta($user, 'meta-box-order_'.$pagehook);
	if (is_array($meta) && isset($meta[0])) {
		$merged = explode(',', trim($meta[0]['side'].','.$meta[0]['normal'], ','));
		$ok = true;
		foreach($classes as $class) {
			$ok = $ok && in_array(XF_METABOX_SLUG.$class, $merged);
		}
		if ($ok === false) {
			delete_user_meta($user, 'meta-box-order_'.$pagehook);
		}
	}
}