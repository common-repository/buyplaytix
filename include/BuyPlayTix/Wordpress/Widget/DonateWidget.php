<?php
namespace BuyPlayTix\Wordpress\Widget;
use \BuyPlayTix\Wordpress\Plugin as Plugin;
class DonateWidget extends \WP_Widget {

  /**
   * Register widget with WordPress.
   */
  function __construct() {
    parent::__construct(
        'bpt_donate_widget', // Base ID
        __('Donate Widget', 'buyplaytix'), // Name
        array( 'description' => __( 'A BuyPlayTix donation form', 'buyplaytix' ), ) // Args
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
    echo Plugin::donate_shorttag([]);
    echo $args['after_widget'];
  }

 	public function form( $instance ) {
 	  // outputs the options form on admin
 	}

 	public function update( $new_instance, $old_instance ) {
 	  return array();
 	}
}
