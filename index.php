<?php
/**
 * Plugin Name: Sensei Quiz Records with ACF
 * Description: A plugin to display quiz attempt records for users under Sensei LMS in the Dashboard
 * Version: 0.1
 * Author: Ethan Htoon
 * Text Domain: sensei-lms
 */


if(!defined('ABSPATH')){
    exit;
}

define ('QUIZR_PATH', plugin_dir_path(__FILE__));
define ('QUIZR_URL', plugin_dir_url(__FILE__));

require_once QUIZR_PATH . 'inc/init.php';
require_once QUIZR_PATH . 'inc/page.php';
require_once QUIZR_PATH . 'inc/func.php';