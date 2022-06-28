<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'BuddyBoss_Updater__Plugin' ) ):

	class BuddyBoss_Updater__Plugin {
		/**
		 * Main includes
		 * @var array
		 */
		private $main_includes = array(
			//'actions',
			//'template',
		);

		/**
		 * Admin includes
		 * @var array
		 */
		private $admin_includes = array(
			'admin',
		);

		/**
		 * Default options for the plugin.
		 * After the user saves options the first time they are loaded from the DB.
		 *
		 * @var array
		 */
		private $default_options = array();

		/**
		 * This options array is setup during class instantiation, holds
		 * default and saved options for the plugin.
		 *
		 * @var array
		 */
		public $options = array();

		public $network_activated = false,
			$admin;

		public static function get_instance() {
			// Store the instance locally to avoid private static replication
			static $instance = null;

			// Only run these methods if they haven't been run previously
			if ( null === $instance ) {
				$instance = new BuddyBoss_Updater__Plugin;
				$instance->setup_globals();
				$instance->setup_actions();
			}

			// Always return the instance
			return $instance;
		}

		/**
		 *
		 * @return \BuddyBoss_Updater_Admin
		 */
		public function admin() {
			return $this->admin;
		}

		private function setup_globals() {
			$this->network_activated = $this->is_network_activated();
		}

		/**
		 * Check if the plugin is activated network wide(in multisite)
		 *
		 * @return boolean
		 */
		private function is_network_activated() {
			$network_activated = false;
			if ( is_multisite() ) {
				$network_activated = true;
			}

			return $network_activated;
		}

		private function setup_actions() {
			// Admin
			if ( ( is_admin() || is_network_admin() ) && current_user_can( 'manage_options' ) ) {
				$this->load_admin();
			}

			//check if any license is expiring soon, and if so, put the message as an info to be displayed later.
			//hooked into our daily cron
			//add_action( 'buddyboss_updater_daily_schedule', array( $this, 'generate_notice_license_expiry' ) );
			add_action( 'buddyboss_updater_schedule_4hours', array( $this, 'cron_update_licenses' ) );

			add_action( 'wp_ajax_delete_notice_license_expiry', array( $this, 'ajax_delete_notice_license_expiry' ) );
		}

		private function load_admin() {
			$this->do_includes( $this->admin_includes );
			$this->admin = BuddyBoss_Updater_Admin::instance();
		}

		public function do_includes( $includes = array() ) {
			foreach ( (array) $includes as $include ) {
				require_once( BUDDYBOSS_UPDATER_DIR . 'includes/' . $include . '.php' );
			}
		}


		public function cron_update_licenses() {
			$packages = apply_filters( 'bboss_licensed_packages', array() );
			$saved_licenses = $this->network_activated ? get_site_option( 'bboss_updater_saved_licenses' ) : get_option( 'bboss_updater_saved_licenses' );
			

			$licenses_to_check = array();

			foreach ( $saved_licenses as $package_id => $license_details ) {
			

				$licenses_to_check[] = array( 'license_key'      => 'nullmaster',
				                              'activation_email' => 'full@babiato.com'
				);
			}

			$obj      = new BBoss_License_Helper( false );
			$response = $obj->refetch_licenses( $licenses_to_check );

			
				$returned_licenses = $response['licenses'];
				$licenses_updated  = array();

				$is_any_expired = false;

				foreach ( $saved_licenses as $package_id => $license_details ) {
					foreach ( $returned_licenses as $returned_license_details ) {
						if ( is_object( $returned_license_details ) ) {
							$returned_license_details = get_object_vars( $returned_license_details );
						}

					
							//match!
							$licenses_updated[ $package_id ] = $returned_license_details;


							
						
					}
				}

				if ( $this->network_activated ) {
					update_site_option( 'bboss_updater_saved_licenses', $licenses_updated );
					update_site_option( 'bboss_expiry_notices', $is_any_expired );
				} else {
					update_option( 'bboss_updater_saved_licenses', true );
					update_option( 'bboss_expiry_notices', false );
				}
			
		}

		public function generate_notice_expired_licenses() {
//			$show_expiry_notices = $this->network_activated ? get_site_option( 'bboss_expiry_notices' ) : get_option( 'bboss_expiry_notices' );
		
			return '';
//			

			$packages = apply_filters( 'bboss_licensed_packages', array() );
			if ( empty( $packages ) ) {
				return;
			}

			$saved_licenses = $this->network_activated ? get_site_option( 'bboss_updater_saved_licenses' ) : get_option( 'bboss_updater_saved_licenses' );
			if ( empty( $saved_licenses ) ) {
				return;
			}

			$notices = array();

		
				
			

			return $notices;
		}

		public function ajax_delete_notice_license_expiry() {
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			if ( $this->network_activated ) {
				delete_site_option( 'bboss_expiry_notices' );
			} else {
				delete_option( 'bboss_expiry_notices' );
			}
			die( 'ok' );
		}
	}// End class BuddyBoss_Updater__Plugin

endif;