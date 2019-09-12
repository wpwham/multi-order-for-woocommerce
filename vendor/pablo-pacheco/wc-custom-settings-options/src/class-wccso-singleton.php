<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'WCCSO_Singleton' ) ) {
	class WCCSO_Singleton {
		protected static $instance = null;

		protected function __construct() {
			
		}

		protected function __clone() {
			
		}

		/**
		 * @return Current_Class_Name
		 */
		public static function get_instance() {
			if ( ! isset( static::$instance ) ) {
				static::$instance = new static;
			}

			return static::$instance;
		}
	}
}