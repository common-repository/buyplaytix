<?php
namespace BuyPlayTix\Wordpress\Widget;
use \BuyPlayTix\Wordpress\Plugin as Plugin;
class CalendarWidget extends \WP_Widget {

  /**
   * Register widget with WordPress.
   */
  function __construct() {
    parent::__construct(
        'bpt_calendar_widget', // Base ID
        __('Calendar', 'buyplaytix'), // Name
        array( 'description' => __( 'BuyPlayTix calendar', 'buyplaytix' ), ) // Args
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
    $title = apply_filters( 'widget_title', $instance['title'] );

    echo $args['before_widget'];
    echo Plugin::calendar_shorttag();
    echo $args['after_widget'];
  }

 	public function form( $instance ) {
 	  // TODO: this should support different sizes, can it somehow hook into the page to display it for the production?
 	  // outputs the options form on admin
 	}

 	public function update( $new_instance, $old_instance ) {
 	  return array();
 	}
}
