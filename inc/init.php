<?php
function quizr_styles($hook) {

    if ($hook !== 'sensei-lms_page_quizr-dash') {
        return;
    }   
    wp_enqueue_style('quizr-admin-style', QUIZR_URL . 'assets/styles.css');
    wp_enqueue_script('quizr-admin-script', QUIZR_URL . 'assets/scripts.js');
}
add_action('admin_enqueue_scripts', 'quizr_styles');


function add_quizr_menu(){
    add_submenu_page(
        'sensei',
        'Quiz Attempt Records',
        'Quiz Records',
        'manage_options',
        'quizr-dash',
        'display_quizr_dash'
    );
}
add_action('admin_menu','add_quizr_menu');

function handle_csv_export(){
    if(isset($_POST['export_csv'])){
        export_csv();
    }
}
add_action('init','handle_csv_export');