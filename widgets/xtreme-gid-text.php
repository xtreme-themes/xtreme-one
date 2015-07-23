<?php 

add_filter('xtreme-collect-widget-classes', create_function('$classes', '$classes[] = "Xtreme_Grid_Text_Widget"; return $classes;') );

class Xtreme_Grid_Text_Widget extends Xtreme_Widget_Base {

	function __construct() {
		$widget_ops = array('classname' => 'xtreme_grid_text', 'description' => __('Arbitrary text or HTML Grid Widget', XF_TEXTDOMAIN) );
		$control_ops = array('width' => 360, 'height' => 350);
		parent::__construct(__FILE__, 'xtreme-grid-text', __( 'Xtreme Grid Text', XF_TEXTDOMAIN ), $widget_ops, $control_ops );

		$this->classes = xtreme_get_grids();

		$this->subcolumns = array(
			"2-20-80",
			"2-25-75",
			"2-38-62",
			"2-40-60",
			"2-50-50",
			"2-60-40",
			"2-62-38",
			"2-75-25",
			"2-80-20",
			"3-20-40-40",
			"3-20-20-60",
			"3-20-60-20",
			"3-25-25-50",
			"3-25-50-25",
			"3-33-33-33",
			"3-40-20-40",
			"3-40-20-40",
			"3-40-40-20",
			"3-50-25-25",
			"3-60-20-20",
			"4-20-20-20-40",
			"4-20-20-40-20",
			"4-20-40-20-20",
			"4-25-25-25-25",
			"4-40-20-20-20",
			"5-20-20-20-20-20"
		);
	}

	function html_before( $current, $cols, $dist ) {

		$html = "";

		if ( $current % $cols == 0 ) {
			$html .= "<div class=\"ym-grid linearize-level-2 no-bottom\">\n";
		}

		$classes = array(
			"ym-g" . $dist[$current % $cols],
			($current % $cols == $cols -1) ? 'ym-gr' : 'ym-gl',
			"grid-" . ( ( $current%$cols ) + 1 )
		);

		$html .= '<div class="' . implode( " ", $classes ) . '">';

		$class_suffix = ($current % $cols == 0 ? 'left' : '');
		$class_suffix = ($current % $cols == $cols -1 ? 'right' : $class_suffix);
		$html .= '<div class="ym-gbox-' . $class_suffix . '">';

		return $html;
	}
	
	function html_after($current, $cols) {
		$html = "";
		if ( $current % $cols == $cols -1 ) {
			$html .= "</div>\n";
		}
		return $html."</div>\n</div>\n";
	}
	
	function html_options( $cols, $selected ) {
		
		foreach( $this->subcolumns as $subs ) {
				
			$vals = explode( '-', $subs );
			$num  = array_shift( $vals );
			if ( $cols == $num ) : ?>
				<option value="<?php echo $subs; ?>" <?php selected( $selected, $subs ); ?>><?php echo '[ '.implode('% | ', $vals).'% ]';?></option>
			<?php endif; 
		}
	}
	
	function widget( $args, $instance ) {
		
		extract($args);
		
		$title       = isset( $instance['title'] ) ? strip_tags( $instance['title'] ) : '';
		$columns     = isset( $instance['columns'] ) ? absint( $instance['columns'] ) : 2;
		$rows        = isset( $instance['rows'] ) ? absint( $instance['rows'] ) : 1;
		$count       = $rows * $columns;
		$filter      = ( isset( $instance['filter'] ) && is_array( $instance['filter'] ) ) ? $instance['filter'] : array();
		$description = ( isset( $instance['description'] ) && is_array( $instance['description'] ) ) ? $instance['description'] : array();
		$description = array_pad($description, $count, '');
		$text        = ( isset( $instance['text'] ) && is_array( $instance['text'] ) ) ? $instance['text'] : array();
		$text        = array_pad($text, $count, '');
		
		$key = "distribution$columns";
		$dist = ( isset( $instance[$key] ) && in_array( $instance[$key], $this->subcolumns) ) ? $instance[$key] : false;
		if ( $dist === false ) {
			switch($column) {
				case 2:
					$dist = '2-50-50';
					break;
				case 3:
					$dist = '3-33-33-33';
					break;
				case 4:
					$dist = '4-25-25-25-25';
					break;
				case 5:
					$dist = '5-20-20-20-20-20';
					break;
			}
		}
		$vals = explode('-', $dist);
		array_shift($vals);

		
		echo $before_widget;
		if ( $title ) echo $before_title . $title . $after_title;

		for ( $i=0; $i < $count; $i ++ ) {
			
			echo $this->html_before($i, $columns, $vals);
			echo '<div class="entry-content">';
			
			if ( empty( $text[$i] ) )
				echo "&nbsp;";
			else
				echo ( in_array( $i, $filter ) ?  wpautop( $text[$i] ) : $text[$i] );
			
			echo '</div>';
			echo $this->html_after( $i, $columns );
		}
		
		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		
		$instance            = $old_instance;
		$instance['title']   = strip_tags($new_instance['title'] );
		$instance['columns'] = (int) $new_instance['columns'];
		$instance['rows']    = (int) $new_instance['rows'];
		$count               = $rows * $columns;
		$text                = ( isset($new_instance['text'] ) && is_array($new_instance['text'] ) ) ? $new_instance['text'] : array();
		$text                = array_pad($text, $count, '');
		
		if ( current_user_can('unfiltered_html') ) {
			$instance['text'] =  $text;
		} else {
			$text = array_map('addslashes', $text);
			$text = array_map('wp_filter_post_kses', $text);
			$text = array_map('stripslashes', $text);
		}
		
		$instance['description']   = ( isset($new_instance['description'] ) && is_array($new_instance['description'] ) ) ? $new_instance['description'] : array();
		$instance['description']   = array_pad( $instance['description'], $count, '');
		$instance['filter']        = ( isset($new_instance['filter'] ) && is_array($new_instance['filter'] ) ) ? $new_instance['filter'] : array();
		$instance['distribution2'] = ( isset($new_instance['distribution2'] ) && in_array($new_instance['distribution2'], $this->subcolumns) && stripos($new_instance['distribution2'], "2-") === 0) ? $new_instance['distribution2'] : '2-50-50';
		$instance['distribution3'] = ( isset($new_instance['distribution3'] ) && in_array($new_instance['distribution3'], $this->subcolumns) && stripos($new_instance['distribution3'], "3-") === 0) ? $new_instance['distribution3'] : '3-33-33-33';
		$instance['distribution4'] = ( isset($new_instance['distribution4'] ) && in_array($new_instance['distribution4'], $this->subcolumns) && stripos($new_instance['distribution4'], "4-") === 0) ? $new_instance['distribution4'] : '4-25-25-25-25';
		$instance['distribution5'] = ( isset($new_instance['distribution5'] ) && in_array($new_instance['distribution5'], $this->subcolumns) && stripos($new_instance['distribution5'], "5-") === 0) ? $new_instance['distribution5'] : '5-20-20-20-20-20';
		
		return $instance;
	}

	function form( $instance ) {
		
		$title         = isset( $instance['title'] ) ? strip_tags( $instance['title'] ) : '';
		$columns       = isset( $instance['columns'] ) ? absint( $instance['columns'] ) : 2;
		$rows          = isset( $instance['rows'] ) ? absint( $instance['rows'] ) : 1;
		$count         = $rows * $columns;
		$filter        = ( isset( $instance['filter'] ) && is_array( $instance['filter'] ) ) ? $instance['filter'] : array();
		$description   = ( isset( $instance['description'] ) && is_array( $instance['description'] ) ) ? $instance['description'] : array();
		$description   = array_pad($description, $count, '');
		$text          = ( isset( $instance['text'] ) && is_array( $instance['text'] ) ) ? $instance['text'] : array();
		$text          = array_pad($text, $count, '');
		$text          = array_map("esc_textarea", $text);
		$distribution2 = ( isset( $instance['distribution2'] ) && in_array( $instance['distribution2'], $this->subcolumns) && stripos( $instance['distribution2'], "2-") === 0) ? $instance['distribution2'] : '2-50-50';
		$distribution3 = ( isset( $instance['distribution3'] ) && in_array( $instance['distribution3'], $this->subcolumns) && stripos( $instance['distribution3'], "3-") === 0) ? $instance['distribution3'] : '3-33-33-33';
		$distribution4 = ( isset( $instance['distribution4'] ) && in_array( $instance['distribution4'], $this->subcolumns) && stripos( $instance['distribution4'], "4-") === 0) ? $instance['distribution4'] : '4-25-25-25-25';
		$distribution5 = ( isset( $instance['distribution5'] ) && in_array( $instance['distribution5'], $this->subcolumns) && stripos( $instance['distribution5'], "5-") === 0) ? $instance['distribution5'] : '5-20-20-20-20-20';
?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', XF_TEXTDOMAIN); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></p>
		<p>
			
			<label for="<?php echo $this->get_field_id( 'rows' ) ?>"><?php _e('Rows:', XF_TEXTDOMAIN ) ?></label>
			<select id="<?php echo $this->get_field_id( 'rows' ) ?>" class="x-textrows" name="<?php echo $this->get_field_name( 'rows' ) ?>">
			<?php $arr = array( 1,2,3,4,5,6 );
			foreach ( $arr as $v ) :
			?>
				<option value="<?php echo $v ?>" <?php selected( $v, $rows ) ?>><?php echo $v ?></option>
			<?php endforeach;
			unset( $arr );?>
			</select>
			
			<label for="<?php echo $this->get_field_id( 'columns' ) ?>"><?php _e( 'Columns:', XF_TEXTDOMAIN ); ?></label>
			<select id="<?php echo $this->get_field_id( 'columns' ) ?>" class="x-textcolumns" name="<?php echo $this->get_field_name( 'columns' ) ?>" >
			<?php $arr = array( 2,3,4,5 );
			foreach ( $arr as $v ) :
			?>
				<option value="<?php echo $v ?>" <?php selected( $v, $columns ) ?>><?php echo $v ?></option>
			<?php endforeach;
			unset( $arr );?>
			</select>
		</p><p>
			<label><?php _e( 'Distribution:', XF_TEXTDOMAIN ); ?></label>
			<select id="<?php echo $this->get_field_id( 'distribution2' ) ?>" class="x-distribution x-distribution-2 <?php if($columns != 2) echo 'hidden';?>" name="<?php echo $this->get_field_name( 'distribution2' ) ?>" >
				<?php $this->html_options(2, $distribution2); ?>
			</select>
			<select id="<?php echo $this->get_field_id( 'distribution3' ) ?>" class="x-distribution x-distribution-3 <?php if($columns != 3) echo 'hidden';?>" name="<?php echo $this->get_field_name( 'distribution3' ) ?>" >
				<?php $this->html_options(3, $distribution3); ?>
			</select>
			<select id="<?php echo $this->get_field_id( 'distribution4' ) ?>" class="x-distribution x-distribution-4 <?php if($columns != 4) echo 'hidden';?>" name="<?php echo $this->get_field_name( 'distribution4' ) ?>" >
				<?php $this->html_options(4, $distribution4); ?>
			</select>
			<select id="<?php echo $this->get_field_id( 'distribution5' ) ?>" class="x-distribution x-distribution-5 <?php if($columns != 5) echo 'hidden';?>" name="<?php echo $this->get_field_name( 'distribution5' ) ?>" >			
				<?php $this->html_options(5, $distribution5); ?>
			</select>
			
		</p>
		<div class="x-textarea-grid-wrapper">
			<ul class="x-textarea-grid columns-<?php echo $columns; ?>">
				<?php for($i=0; $i<$count; $i++) : ?>
				<li class="ui-state-default<?php if ( ! empty($text[$i] ) ) echo ' none-empty' ?>" title="<?php echo $description[$i]; ?>">
					<small><a href="#" class="edit ui-icon ui-icon-pencil">&nbsp;</a></small>
					<textarea class="widefat hidden" rows="16" cols="20" id="<?php echo $this->get_field_id('text')."-$i"; ?>" name="<?php echo $this->get_field_name('text'); ?>[]"><?php echo $text[$i]; ?></textarea>
					<p class="hidden">
						<label for="<?php echo $this->get_field_id( 'description' ) ?>"><?php _e('Description:', XF_TEXTDOMAIN ) ?></label>
						<label class="alignright" for="<?php echo $this->get_field_id('filter'); ?>">&nbsp;<?php _e('Automatically add paragraphs', XF_TEXTDOMAIN); ?></label>
						<input class="alignright" id="<?php echo $this->get_field_id('filter')."-$i"; ?>" name="<?php echo $this->get_field_name('filter'); ?>[]" value="<?php echo $i;?>" type="checkbox" <?php checked( in_array($i, $filter) ); ?> />
						<input class="description" id="<?php echo $this->get_field_id('description')."-$i"; ?>" name="<?php echo $this->get_field_name('description'); ?>[]" type="text" class="widefat" value="<?php echo $description[$i]; ?>" /><br />
					</p>
				</li>
				<?php endfor; ?>
			</ul>
		</div>
<?php
	}
}