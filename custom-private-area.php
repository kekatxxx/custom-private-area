<?php

/*
Plugin Name: Custom Private Area
Plugin URI:  http://www.francescobarbieri.info
Description: Crea una area riservata con accesso e login
Version:     1.5
Author:      Francesco Barbieri
Author URI:  http://www.francescobarbieri.info
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

wp_enqueue_style('cpa-style-form',  plugins_url('css/cpa-style.css', __FILE__));
wp_enqueue_script( 'jvalidate', plugins_url('js/jquery.validate.min.js', __FILE__), array(),false, true);
wp_enqueue_script( 'repeat-password-script', plugins_url('js/repeat-password.js', __FILE__), array(),false, true);
include_once 'inc/CPA_Registration_Form.php';

/*
 * Register a new user role for the private area
 * register_activation_hook = metodo chiamato alla registrazione del plugin
 */
function add_roles_on_plugin_activation() {
    add_role( 'private_area_role', 'Utente area privata', array( 'read' => true, 'level_0' => true ) );
}
register_activation_hook( __FILE__, 'add_roles_on_plugin_activation' );

/*
 * rimuovuo la barra admin per i non amministratori
 */
add_action('init', 'remove_admin_bar');
function remove_admin_bar() {
    if (!current_user_can('administrator') && !is_admin()) {
        show_admin_bar(false);
    }
}

/*
 * --------- ADMIN SECTION -------------
 */
function cpa_settings_api_init() {

    // Add the section to reading settings so we can add our fields to it
    add_settings_section(
        'cpa_setting_section',
        'Custom Private Area Settings',
        'cpa_setting_section_callback_function',
        'reading'
    );

    // Add the field with the names and function to use for our new settings, put it in our new section
    add_settings_field(
        'cpa_private_page_id',
        'Private Area Page ID',
        'cpa_setting_callback_function',
        'reading',
        'cpa_setting_section'
    );

    // Register our setting in the "reading" settings section
    register_setting( 'reading', 'cpa_private_page_id' );

}
add_action( 'admin_init', 'cpa_settings_api_init');

/*
* Settings section callback function
*/
function cpa_setting_section_callback_function() {
    
    echo '<p>Select the private area page ID (int)</p>';
}

/*
* Callback function for our example setting
*/
function cpa_setting_callback_function() {
    $cpa_private_page_id = esc_attr( get_option( 'cpa_private_page_id' ) );
    
    $args = array(
	'sort_order' => 'asc',
	'sort_column' => 'post_title',
	'hierarchical' => 1,
	'exclude' => '',
	'include' => '',
	'meta_key' => '',
	'meta_value' => '',
	'authors' => '',
	'child_of' => 0,
	'parent' => -1,
	'exclude_tree' => '',
	'number' => '',
	'offset' => 0,
	'post_type' => 'page',
	'post_status' => 'publish'
    ); 
    $pages = get_pages($args); 
    echo "<select name='cpa_private_page_id'>";
    foreach ($pages as $page){
        //print_r($page);
        $option_html = "<option value='$page->ID'";
        if ($page->ID === intval($cpa_private_page_id)){
            $option_html .= " selected='selected'";
        }
        $option_html .= " >$page->post_title</option>";
        echo $option_html;
    }
    echo "</select>";
}

/*---------- END ADMIN SECTION ------------

/*
 *Check if the current page is the Private Area Page
 */
function cpa_page_load() {
    
    if(is_page(esc_attr(get_option('cpa_private_page_id' )))){
        add_filter( 'the_content', 'filter_content_private_page' );
    }
    
    /* per la traduzione (WPML) */
    if ( function_exists('icl_object_id')){
        $translated_page_id = icl_object_id( esc_attr(get_option('cpa_private_page_id' )), 'page', false );
        if(is_page($translated_page_id)){
            add_filter( 'the_content', 'filter_content_private_page' );
        }
    }
    
}
add_action( 'wp', 'cpa_page_load' );
    
function filter_content_private_page($content) {
  
    // otherwise returns the database content
    if (is_user_logged_in()){
        return html_area_menu().$content;
    }
    //echo html_login_page();
    return html_login_page(); 
}

add_shortcode('cpa_login_form', 'login_form_shortcode');
function login_form_shortcode() {

        if ( is_user_logged_in() )
                return '';
        
        return wp_login_form( array( 'echo' => false ) );
}

add_action( 'login_form_middle', 'add_lost_password_link' );
function add_lost_password_link() {
    return '<a href="'.wp_lostpassword_url().'" class="lostpswlink" title="Lost Password">Lost Password?</a>';
}

function html_login_page(){
?>

    
    <div class='container privatearea'>
        <div class='row'>
            <div class='full-width-text spb_content_element col-sm-12 spb_text_column'>
                <p>
                
                    <?php
                    _e( "Per accedere a questa area bisogna essere loggati - se non possiedi un account compila il form a destra ed effettua l'accesso.", 
                            "Custom Private Area" ); ?>
                </p>
            </div>
            <div class='col-sm-6'>
                <?php echo do_shortcode('[cpa_login_form]');?>

            </div>
            <div class='col-sm-6'>
                <?php echo do_shortcode('[cpa_registration_form]');?>
            </div>
        </div>
    </div>

<?php
}

function html_area_menu(){

    $current_user = wp_get_current_user();
    $id_private_area_page = esc_attr(get_option('cpa_private_page_id'));
    $id_private_area_page_en = icl_object_id($id_private_area_page, 'page', false, 'en');
    
    $id_page_redirect = false;
    if (ICL_LANGUAGE_CODE=='en'){
        $id_page_redirect = $id_private_area_page_en;
    }else{
        $id_page_redirect = $id_private_area_page;
    }
    
    ?>

    <div class='container privatearea'>
        <div class='row'>
            <div class='full-width-text spb_content_element col-sm-12 spb_text_column privatearea_topbar'>
                <ul class='menu-private-area'>
                    <li>
                        <a href="<?php echo wp_logout_url( get_permalink($id_page_redirect) ); ?>">
                            <?php _e( "Logout", "Custom Private Area" ); ?>
                        </a>
                    </li>
                </ul>
                <span class="hello-user"><?php _e( "Ciao", "Custom Private Area" ); ?>, <?php echo $current_user->user_firstname." ".$current_user->user_lastname?></span>
            </div>
        </div>
    </div>

<?php
}