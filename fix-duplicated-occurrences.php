<?php
/**
 * Plugin Name: Fix Duplicated Occurrences
 * Description: Somehow Recurring Events made a ton of extra occurences. This plugin will delete the extras. Deactivate after use.
 * Version: 0.1.0
 * Text Domain: fix-duplicated-occurrences
 * Author: Eric Defore
 * Author URI: https://realbigmarketing.com/
 * Contributors: d4mation
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'fix_duplicated_occurrences' ) ) {

	/**
	 * Main fix_duplicated_occurrences class
	 *
	 * @since	  {{VERSION}}
	 */
	final class fix_duplicated_occurrences {
		
		/**
		 * @var			array $plugin_data Holds Plugin Header Info
		 * @since		{{VERSION}}
		 */
		public $plugin_data;
		
		/**
		 * @var			array $admin_errors Stores all our Admin Errors to fire at once
		 * @since		{{VERSION}}
		 */
		private $admin_errors;

		/**
		 * Get active instance
		 *
		 * @access	  public
		 * @since	  {{VERSION}}
		 * @return	  object self::$instance The one true fix_duplicated_occurrences
		 */
		public static function instance() {
			
			static $instance = null;
			
			if ( null === $instance ) {
				$instance = new static();
			}
			
			return $instance;

		}
		
		protected function __construct() {
			
			$this->setup_constants();
			$this->load_textdomain();
			
			if ( version_compare( get_bloginfo( 'version' ), '4.4' ) < 0 ) {
				
				$this->admin_errors[] = sprintf( _x( '%s requires v%s of %sWordPress%s or higher to be installed!', 'First string is the plugin name, followed by the required WordPress version and then the anchor tag for a link to the Update screen.', 'fix-duplicated-occurrences' ), '<strong>' . $this->plugin_data['Name'] . '</strong>', '4.4', '<a href="' . admin_url( 'update-core.php' ) . '"><strong>', '</strong></a>' );
				
				if ( ! has_action( 'admin_notices', array( $this, 'admin_errors' ) ) ) {
					add_action( 'admin_notices', array( $this, 'admin_errors' ) );
				}
				
				return false;
				
			}
			
			$this->require_necessities();
			
			// Register our CSS/JS for the whole plugin
			add_action( 'init', array( $this, 'register_scripts' ) );

			add_action( 'admin_init', array( $this, 'fix_occurrences' ) );
			
		}

		/**
		 * Setup plugin constants
		 *
		 * @access	  private
		 * @since	  {{VERSION}}
		 * @return	  void
		 */
		private function setup_constants() {
			
			// WP Loads things so weird. I really want this function.
			if ( ! function_exists( 'get_plugin_data' ) ) {
				require_once ABSPATH . '/wp-admin/includes/plugin.php';
			}
			
			// Only call this once, accessible always
			$this->plugin_data = get_plugin_data( __FILE__ );

			if ( ! defined( 'fix_duplicated_occurrences_VER' ) ) {
				// Plugin version
				define( 'fix_duplicated_occurrences_VER', $this->plugin_data['Version'] );
			}

			if ( ! defined( 'fix_duplicated_occurrences_DIR' ) ) {
				// Plugin path
				define( 'fix_duplicated_occurrences_DIR', plugin_dir_path( __FILE__ ) );
			}

			if ( ! defined( 'fix_duplicated_occurrences_URL' ) ) {
				// Plugin URL
				define( 'fix_duplicated_occurrences_URL', plugin_dir_url( __FILE__ ) );
			}
			
			if ( ! defined( 'fix_duplicated_occurrences_FILE' ) ) {
				// Plugin File
				define( 'fix_duplicated_occurrences_FILE', __FILE__ );
			}

		}

		/**
		 * Internationalization
		 *
		 * @access	  private 
		 * @since	  {{VERSION}}
		 * @return	  void
		 */
		private function load_textdomain() {

			// Set filter for language directory
			$lang_dir = fix_duplicated_occurrences_DIR . '/languages/';
			$lang_dir = apply_filters( 'fix_duplicated_occurrences_languages_directory', $lang_dir );

			// Traditional WordPress plugin locale filter
			$locale = apply_filters( 'plugin_locale', get_locale(), 'fix-duplicated-occurrences' );
			$mofile = sprintf( '%1$s-%2$s.mo', 'fix-duplicated-occurrences', $locale );

			// Setup paths to current locale file
			$mofile_local   = $lang_dir . $mofile;
			$mofile_global  = WP_LANG_DIR . '/fix-duplicated-occurrences/' . $mofile;

			if ( file_exists( $mofile_global ) ) {
				// Look in global /wp-content/languages/fix-duplicated-occurrences/ folder
				// This way translations can be overridden via the Theme/Child Theme
				load_textdomain( 'fix-duplicated-occurrences', $mofile_global );
			}
			else if ( file_exists( $mofile_local ) ) {
				// Look in local /wp-content/plugins/fix-duplicated-occurrences/languages/ folder
				load_textdomain( 'fix-duplicated-occurrences', $mofile_local );
			}
			else {
				// Load the default language files
				load_plugin_textdomain( 'fix-duplicated-occurrences', false, $lang_dir );
			}

		}
		
		/**
		 * Include different aspects of the Plugin
		 * 
		 * @access	  private
		 * @since	  {{VERSION}}
		 * @return	  void
		 */
		private function require_necessities() {
			
		}
		
		/**
		 * Show admin errors.
		 * 
		 * @access	  public
		 * @since	  {{VERSION}}
		 * @return	  HTML
		 */
		public function admin_errors() {
			?>
			<div class="error">
				<?php foreach ( $this->admin_errors as $notice ) : ?>
					<p>
						<?php echo $notice; ?>
					</p>
				<?php endforeach; ?>
			</div>
			<?php
		}
		
		/**
		 * Register our CSS/JS to use later
		 * 
		 * @access	  public
		 * @since	  {{VERSION}}
		 * @return	  void
		 */
		public function register_scripts() {
			
			wp_register_style(
				'fix-duplicated-occurrences',
				fix_duplicated_occurrences_URL . 'dist/assets/css/app.css',
				null,
				defined( 'WP_DEBUG' ) && WP_DEBUG ? time() : fix_duplicated_occurrences_VER
			);
			
			wp_register_script(
				'fix-duplicated-occurrences',
				fix_duplicated_occurrences_URL . 'dist/assets/js/app.js',
				array( 'jquery' ),
				defined( 'WP_DEBUG' ) && WP_DEBUG ? time() : fix_duplicated_occurrences_VER,
				true
			);
			
			wp_localize_script( 
				'fix-duplicated-occurrences',
				'fixduplicatedoccurrences',
				apply_filters( 'fix_duplicated_occurrences_localize_script', array() )
			);
			
			wp_register_style(
				'fix-duplicated-occurrences-admin',
				fix_duplicated_occurrences_URL . 'dist/assets/css/admin.css',
				null,
				defined( 'WP_DEBUG' ) && WP_DEBUG ? time() : fix_duplicated_occurrences_VER
			);
			
			wp_register_script(
				'fix-duplicated-occurrences-admin',
				fix_duplicated_occurrences_URL . 'dist/assets/js/admin.js',
				array( 'jquery' ),
				defined( 'WP_DEBUG' ) && WP_DEBUG ? time() : fix_duplicated_occurrences_VER,
				true
			);
			
			wp_localize_script( 
				'fix-duplicated-occurrences-admin',
				'fixduplicatedoccurrences',
				apply_filters( 'fix_duplicated_occurrences_localize_admin_script', array() )
			);
			
		}

		public function fix_occurrences() {

			$shows_to_fix = array(
				'Divine Intimacy Radio',
				'Jackson Today',	
			);

			foreach ( $shows_to_fix as $title ) {

				$found_occurrences = array();

				$radio_show_query = new WP_Query( array(
					's' => $title,
					'sentence' => true,
					'post_type' => 'tribe_events',
					'eventDisplay' => 'custom',
					'posts_per_page' => -1,
					'order' => 'ASC',
					'fields' => 'ids',
					'post_status' => 'publish',
					'tax_query' => array(
						'relationship' => 'AND',
						array(
							'taxonomy' => 'tribe_events_cat',
							'field' => 'slug',
							'terms' => array( 'radio-show' ),
							'operator' => 'IN'
						),
						array(
							'key' => '_EventStartDate',
							'value' => current_time( 'Y-m-d H:i:s' ),
							'type' => 'DATETIME',
							'compare' => '<=',
						),
					),
				) );

				if ( ! $radio_show_query->have_posts() ) continue;

				foreach ( $radio_show_query->posts as $post_id ) {

					$start_datetime = strtotime( get_post_meta( $post_id, '_EventStartDate', true ) );

					$start_date = date( 'Y-m-d', $start_datetime );
					$start_time = date( 'H:i:s', $start_datetime );

					if ( ! isset( $found_occurrences[ $start_date ] ) ) {
						$found_occurrences[ $start_date ] = array();
					}

					if ( ! isset( $found_occurrences[ $start_date ][ $start_time ] ) ) {
						$found_occurrences[ $start_date ][ $start_time ] = array();
					}

					$found_occurrences[ $start_date ][ $start_time ][] = $post_id;

				}

				$posts_to_delete = array();

				foreach ( $found_occurrences as $day => $timeslots ) {

					foreach ( $timeslots as $timeslot => $post_ids ) {

						// This is an intermediary array to ensure we do not accidentally delete all occurrences if they were never broken from the Series
						$delete = array();

						// In some weird cases, multiple of the same Event were broken from the series at the same DateTime
						$broken_from_series = array();

						if ( count( $post_ids ) > 1 ) {

							foreach ( $post_ids as $post_id ) {

								// If this one was not broken from the series, it should be deleted
								if ( wp_get_post_parent_id( $post_id ) ) {
									$delete[] = $post_id;
								}
								else {
									$broken_from_series[] = $post_id;
								}

							}

							// If we were about to remove every occurrence for the timeslot, preserve one of them
							if ( count( $delete ) == count( $post_ids ) ) {
								unset( $delete[0] );
							}

							if ( count( $broken_from_series ) > 1 ) {

								// Keep first/original
								unset( $broken_from_series[0] );

								$delete = array_merge( $delete, $broken_from_series );

							}

						}

						$posts_to_delete = array_merge( $posts_to_delete, $delete );

					}

				}

				foreach ( $posts_to_delete as $post_id ) {

					wp_delete_post( $post_id, true );

				}

			}

		}
		
	}
	
} // End Class Exists Check

/**
 * The main function responsible for returning the one true fix_duplicated_occurrences
 * instance to functions everywhere
 *
 * @since	  {{VERSION}}
 * @return	  \fix_duplicated_occurrences The one true fix_duplicated_occurrences
 */
add_action( 'plugins_loaded', 'fix_duplicated_occurrences_load' );
function fix_duplicated_occurrences_load() {

	require_once __DIR__ . '/core/fix-duplicated-occurrences-functions.php';
	FIXDUPLICATEDOCCURRENCES();

}