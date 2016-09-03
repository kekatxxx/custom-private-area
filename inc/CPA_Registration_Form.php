<?php

class CPA_Registration_Form
{

    private $username;
    private $email;
    private $password;
    private $website;
    private $first_name;
    private $last_name;
    private $nickname;
    private $bio;
    private $regok;

    function __construct()
    {
        
        add_shortcode('cpa_registration_form', array($this, 'registration_form_shortcode'));
        add_action('wp_enqueue_scripts', array($this, 'flat_ui_kit'));
        
    }


    public function registration_form()
    {

        ?>

        <form method="post" id="registration-form" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>">
            <div class="registration-form">
                
                <h2 class="entry-title"><?php _e( "Registrati", "Custom Private Area" ); ?></h2>
                
                <div class="form-group">
                    <input name="reg_name" type="text" class="form-control login-field"
                           value="<?php echo(isset($_POST['reg_name']) ? $_POST['reg_name'] : null); ?>"
                           placeholder="Username" id="reg-name" required/>
                </div>

                <div class="form-group">
                    <input name="reg_email" type="email" class="form-control login-field"
                           value="<?php echo(isset($_POST['reg_email']) ? $_POST['reg_email'] : null); ?>"
                           placeholder="Email" id="reg-email" required/>
                </div>

                <div class="form-group">
                    <input name="reg_password" type="password" class="form-control login-field"
                           value="<?php echo(isset($_POST['reg_password']) ? $_POST['reg_password'] : null); ?>"
                           placeholder="Password" id="reg-pass" required/>
                </div>
                
                <div class="form-group">
                    <input name="reg_password2" type="password" class="form-control login-field"
                           value="<?php echo(isset($_POST['reg_password2']) ? $_POST['reg_password2'] : null); ?>"
                           placeholder="Repeat password" id="reg-pass2" required/>
                </div>

                <!--div class="form-group">
                    <input name="reg_website" type="text" class="form-control login-field"
                           value="<?php echo(isset($_POST['reg_website']) ? $_POST['reg_website'] : null); ?>"
                           placeholder="Website" id="reg-website"/>
                </div-->

                <div class="form-group">
                    <input name="reg_fname" type="text" class="form-control login-field"
                           value="<?php echo(isset($_POST['reg_fname']) ? $_POST['reg_fname'] : null); ?>"
                           placeholder="First Name" id="reg-fname"/>
                </div>

                <div class="form-group">
                    <input name="reg_lname" type="text" class="form-control login-field"
                           value="<?php echo(isset($_POST['reg_lname']) ? $_POST['reg_lname'] : null); ?>"
                           placeholder="Last Name" id="reg-lname"/>
                </div>

                <!--div class="form-group">
                    <input name="reg_nickname" type="text" class="form-control login-field"
                           value="<?php echo(isset($_POST['reg_nickname']) ? $_POST['reg_nickname'] : null); ?>"
                           placeholder="Nickname" id="reg-nickname"/>
                </div-->

                <div class="form-group">
                    <input name="reg_bio" type="text" class="form-control login-field"
                           value="<?php echo(isset($_POST['reg_bio']) ? $_POST['reg_bio'] : null); ?>"
                           placeholder="Info" id="reg-bio"/>
                </div>
                <div class="clearfix"></div>

                <input class="btn btn-primary btn-lg btn-block" type="submit" name="reg_submit" value="Registrati"/>
                <div class="clearfix"></div>
                
        </form>
        </div>
    <?php
    }

    function validation()
    {

        if (empty($this->username) || empty($this->password) || empty($this->email)) {
            return new WP_Error('field', 'Required form field is missing');
        }

        if (strlen($this->username) < 4) {
            return new WP_Error('username_length', 'Username too short. At least 4 characters is required');
        }

        if (strlen($this->password) < 5) {
            return new WP_Error('password', 'Password length must be greater than 5');
        }

        if (!is_email($this->email)) {
            return new WP_Error('email_invalid', 'Email is not valid');
        }

        if (email_exists($this->email)) {
            return new WP_Error('email', 'Email Already in use');
        }

        if (!empty($website)) {
            if (!filter_var($this->website, FILTER_VALIDATE_URL)) {
                return new WP_Error('website', 'Website is not a valid URL');
            }
        }

        $details = array('Username' => $this->username,
            'First Name' => $this->first_name,
            'Last Name' => $this->last_name,
            //'Nickname' => $this->nickname
        );

        foreach ($details as $field => $detail) {
            if (!validate_username($detail)) {
                return new WP_Error('name_invalid', 'Sorry, the "' . $field . '" you entered is not valid');
            }
        }

    }

    function registration()
    {

        $userdata = array(
            'user_login' => esc_attr($this->username),
            'user_email' => esc_attr($this->email),
            'user_pass' => esc_attr($this->password),
            'user_url'  => esc_attr($this->website),
            'first_name' => esc_attr($this->first_name),
            'last_name' => esc_attr($this->last_name),
            'nickname' => esc_attr($this->nickname),
            'description' => esc_attr($this->bio),
            'role' => 'private_area_role'
        );

        if (is_wp_error($this->validation())) {
            
            echo '<div style="margin-bottom: 6px" class="btn btn-block btn-lg btn-danger btn-norad">';
            echo $this->validation()->get_error_message();
            echo '</div>';
            
        } else {
            $register_user = wp_insert_user($userdata);
            if (!is_wp_error($register_user)) {
                
                wp_new_user_notification( $register_user, null, 'both' );

                echo '<div style="margin-bottom: 6px" class="btn btn-block btn-lg btn-success btn-norad">';
                echo 'Registration complete. Please log in.';
                echo '</div>';
                $this->regok = true;
                
            } else {
                
                echo '<div style="margin-bottom: 6px" class="btn btn-block btn-lg btn-danger btn-norad">';
                echo $register_user->get_error_message();
                echo '</div>';
                
            }
        }

    }

    function flat_ui_kit()
    {
        wp_enqueue_style('bootstrap-css', plugins_url('bootstrap/css/bootstrap.css', __FILE__));
        wp_enqueue_style('flat-ui-kit', plugins_url('css/flat-ui.css', __FILE__));

    }

    function registration_form_shortcode()
    {

        ob_start();

        if ($_POST['reg_submit']) {
            $this->username = $_POST['reg_name'];
            $this->email = $_POST['reg_email'];
            $this->password = $_POST['reg_password'];
            $this->website = $_POST['reg_website'];
            $this->first_name = $_POST['reg_fname'];
            $this->last_name = $_POST['reg_lname'];
            $this->nickname = $_POST['reg_nickname'];
            $this->bio = $_POST['reg_bio'];

            $this->validation();
            $this->registration();
        }

        if ( $this->regok != true) $this->registration_form();
        
        return ob_get_clean();
    }
    

}
new CPA_Registration_Form;