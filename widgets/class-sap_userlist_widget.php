<?php
/**
 * Feature Name:	SAP User List Widget
 * Version:			0.1
 * Author:			Frank Bültge
 * Author URI:		http://bueltge.de
 * Licence:			GPLv3 
 * 
 * Changelog
 *
 * 0.1
 * - Initial Commit
 */

if ( ! class_exists( 'Sap_Userlist_Widget' ) ) {
	
	add_filter( 'widgets_init', array( 'Sap_Userlist_Widget', 'register' ) );
	
	class Sap_Userlist_Widget extends WP_Widget {
	
		/**
		 * The plugins textdomain
		 *
		 * @since	0.1
		 * @access	public
		 * @static
		 * @var		string
		 */
		public static $textdomain = '';
		
		/**
		 * constructor
		 *
		 * @since	0.1
		 * @access	public
		 * @uses	__
		 * @return	void
		 */
		public function __construct() {
			
			self::$textdomain = 'saprfc';
			
			parent::__construct(
				'saprfc-userlist',
				'SAP Userlist Widget',
				array(
					'description' => __( 'Get list of SAPoffice users', self::$textdomain )
				)
			);
		}
		
		/**
		 * displays the widget in frontend
		 *
		 * @since	0.1
		 * @access	public
		 * @param	array $args the widget arguments
		 * @param	array $instance current instance
		 * @uses	
		 * @return	void
		 */
		public function widget( $args, $instance ) {
			
			extract( $args, EXTR_SKIP );
			
			echo $before_widget;
			
			$title = '';
			if ( empty($instance[ 'title' ]) )
				$title = $args['widget_name'];
			else
				$title = $instance[ 'title' ];
			
			$title = apply_filters( 'saprfc_userlist_widget_title', $title );
			
			if ( '' != $title )
				echo $before_title . $title . $after_title;
			
			global $sap;
			// Usee SE37 oder BAPI explorer to read more about the functions and his params
			// Call-Function
			$result = $sap->callFunction( "SO_USER_LIST_READ",
				array(
					array( "IMPORT", "USER_GENERIC_NAME", "*" ),
					array( "TABLE", "USER_DISPLAY_TAB", array() )
				)
			);
			
			// Call successfull?
			if ( $sap->getStatus() == SAPRFC_OK ) {
				// Yes, print out the Userlist
				echo '<table>';
				echo '<tr><th>SAP-Name</th><th>User-Nummer</th></tr>';
					
				foreach ( $result['USER_DISPLAY_TAB'] as $user ) {
					echo "<tr><td>" . $user["SAPNAM"] . "</td><td>" . $user["USRNO"] . "</td></tr>";
				}
				
				echo '</table>';
			} else { 
				// No, print long Version of last Error
				$sap->printStatus();
				// or print your own error-message with the strings received from
				// $sap->getStatusText() or $sap->getStatusTextLong()
			}
			
			echo $after_widget;
		}
		
		/**
		 * process the options-updateing
		 *
		 * @since	0.1
		 * @access	public
		 * @param	array $new_instance
		 * @param	array $old_instance
		 * @return	array
		 */
		public function update( $new_instance, $old_instance ) {

			$instance = $old_instance;
			$instance[ 'title' ] = strip_tags( $new_instance[ 'title' ] );
			return $instance;
		}

		/**
		 * the backend options form
		 *
		 * @since	0.1
		 * @access	public
		 * @param	array $instance
		 * @uses	_e, esc_attr
		 * @return	string
		 */
		public function form( $instance ) {

			$title = '';

			if ( isset( $instance[ 'title' ] ) )
				$title = esc_attr( $instance[ 'title' ] );

			?>
			<p>
				<label for="<?php $this->get_field_id( 'title' );?>">
					<?php _e( 'Title:', self::$textdomain );?>
				</label><br />
				<input type="text" id="<?php echo $this->get_field_id( 'title' );?>" name="<?php echo $this->get_field_name( 'title' );?>" value="<?php echo $title; ?>" />
			</p>
			<?php
			return TRUE;
		}

		/**
		 * register
		 *
		 * @since	0.1
		 * @access	public
		 * @static
		 * @uses	register_widget
		 * @return	void
		 */
		public static function register() {
			register_widget( __CLASS__ );
		}
	}
}