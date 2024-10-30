<?php
namespace BuyPlayTix\Wordpress\Widget;
use \BuyPlayTix\Wordpress\Plugin as Plugin;
class ProductionWidget extends \WP_Widget {

  /**
   * Register widget with WordPress.
   */
  function __construct() {
    parent::__construct(
        'bpt_production_widget', // Base ID
        __('Production Information', 'buyplaytix'), // Name
        array( 'description' => __( "Production information. Note: you must assign a production to the page or post for this widget to appear.", 'buyplaytix' ), ) // Args
    );
  }

  /**
   * Front-end display of widget.
   *
   * @see WP_Widget::widget()
   *
   * @param array $args     Widget arguments.
   * @param array $instance Saved values from database.
   */
  public function widget( $args, $instance ) {

    // TODO: ADD A CACHE

    $post = $GLOBALS["post"];
    if($GLOBALS["post_type"] == "page") {
      $post = $GLOBALS["page"];
    }
    if($post == NULL) {
      return;
    }
    $url_name = get_post_meta($post->ID, 'bpt_production', true );
    if(empty($url_name)) {
      return;
    }
    echo $args['before_widget'];
    switch($instance["type"]) {
      case "location":
        echo Plugin::location_shorttag(array("url" => $url_name));
        break;
      case "additional_info":
        echo Plugin::additional_info_shorttag(array("url" => $url_name));
        break;
      case "when":
        echo Plugin::when_shorttag(array("url" => $url_name));
        break;
      case "minical":
        echo Plugin::minical_shorttag(array("url" => $url_name));
        break;
      case "logo":
        echo Plugin::logo_shorttag(array("url" => $url_name));
        break;
      case "tickets":
        echo Plugin::tickets_shorttag(array("url" => $url_name));
        break;
      case "people":
        echo Plugin::people_shorttag(array("url" => $url_name));
        break;
      default:
        echo "Unsupported type";
    }
    echo $args['after_widget'];
  }

 	public function form( $instance ) {
 	  $type = NULL;
 	  if ( isset( $instance['type'] ) ) {
 	    $type = $instance[ 'type' ];
 	  }
 	  ?>
<p>
	<label for="<?php echo $this->get_field_id('type'); ?>"><?php _e('Type:'); ?>
	</label> <select class="widefat"
		id="<?php echo $this->get_field_id( 'type' ); ?>"
		name="<?php echo $this->get_field_name( 'type' ); ?>">
		<option value="tickets"
		<?php echo ($type == "tickets" ? ' selected="selected" ' : '') ?>>
			<?php _e('Tickets')?>
		</option>
		<option value="minical"
		<?php echo ($type == "minical" ? ' selected="selected" ' : '') ?>>
			<?php _e('Mini Calendar')?>
		</option>
		<option value="location"
		<?php echo ($type == "location" ? ' selected="selected" ' : '') ?>>
			<?php _e('Location')?>
		</option>
		<option value="when"
		<?php echo ($type == "when" ? ' selected="selected" ' : '') ?>>
			<?php _e('When')?>
		</option>
		<option value="additional_info"
		<?php echo ($type == "additional_info" ? ' selected="selected" ' : '') ?>>
			<?php _e('Additional Info')?>
		</option>
		<option value="logo"
		<?php echo ($type == "logo" ? ' selected="selected" ' : '') ?>>
			<?php _e('Logo')?>
		</option>
		<option value="people"
		<?php echo ($type == "people" ? ' selected="selected" ' : '') ?>>
			<?php _e('People')?>
		</option>
		<!-- managed on wordpress, links and galleries? -->





	</select>
</p>
<?php
 	}

 	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['type'] = ( ! empty( $new_instance['type'] ) ) ? strip_tags( $new_instance['type'] ) : '';

		return $instance;
	}
}
