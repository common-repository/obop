<?php 
    class ObopSettings
    {
        /**
         * Holds the values to be used in the fields callbacks
         */
        private $options;

        /**
         * Start up
         */
        public function __construct()
        {
            add_action( 'init', array($this, 'loadTranslations'));
            add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
            add_action( 'admin_init', array( $this, 'page_init' ) );
            add_action( 'widgets_init', array( $this, 'register_widget' ) );
            
            function add_custom_content($content)
            {
                $positions = get_option('position');
                $scripts = get_option('script');
                $istest = get_option('istest');
                $testip = get_option('testip');
                $display = true;

                if($istest)
                {
                    $display = false;

                    foreach(explode(",", $testip) as $ip)
                       if($_SERVER['REMOTE_ADDR'] == trim($ip))
                           $display = true;
                }
                
                if($display)
                {
                    foreach(get_option('provider') as $key=>$option)
                        if($option != "")
                        {
                            if(isset($positions[$key]) && isset($scripts[$key]))
                            {
                                $position = $positions[$key];
                                $script = $scripts[$key];

                                if($position == 1)
                                {
                                    $content = $script.$content;
                                }
                                elseif($position == 2)
                                {
                                    $content = $content.$script;
                                }
                            } 
                        }
                }
                
                return $content;
            }

            add_filter( "the_content", "add_custom_content");
        }
        
        public function loadTranslations()
        {
            load_plugin_textdomain('wp-admin-obop', FALSE, dirname(plugin_basename(__FILE__)).'/languages/');
        }
        

        public function register_widget() {
            register_widget( 'obop_widget' );
        }
        
        /**
         * Add options page
         */
        public function add_plugin_page()
        {
            // This page will be under "Settings"
            add_options_page(
                'Settings Admin', 
                'OBOP', 
                'manage_options', 
                'obop', 
                array( $this, 'create_admin_page' )
            );
        }

        /**
         * Options page callback
         */
        public function create_admin_page()
        {
            // Set class property
            $this->options = get_option( 'my_option_name' );
            ?>
            <script type="text/javascript">
                function obopSwitch()
                {
                    if(document.getElementById("istest").checked)
                        document.getElementById("list-ip").style.display = "";
                    else
                        document.getElementById("list-ip").style.display = "none";
                }
            </script>
            <div class="wrap">
                <h2><?php echo __('OBOP Parameters', 'wp-admin-obop'); ?></h2>
                
                <form method="post" action="options.php">
                    
                    <table class="form-table">
                        <tr valign="top">
                            <th scope="row">OBOP UID</th>
                            <?php $obopuid = get_option('obopuid'); ?>
                            <td><input type="text" name="obopuid" value="<?php echo $obopuid; ?>" /><br><?php echo __('This ID is provided by OBOP, it\'s a unique code corresponding to your website', 'wp-admin-obop') ?></td>
                        </tr>
                        <tr>
                            <th scope="row"><?php echo __('Enabled only for test purpose', 'wp-admin-obop') ?></th>
                            <?php $istest = get_option('istest'); ?>
                            <td><input type="checkbox" id="istest" name="istest" onclick="obopSwitch()" value="1" <?php echo ($istest == 1) ? "checked" : ""; ?> /></td>
                        </tr>
                        <tr id="list-ip" style="<?php echo ($istest != 1) ? "display:none" : ""; ?>">
                            <th scope="row">IPs de test</th>
                            <?php $testip = get_option('testip'); ?>
                            <td><input type="text" name="testip" value="<?php echo $testip; ?>" /><br><?php echo __('List of IPs separated by comas for who the system is enabled', 'wp-admin-obop') ?></td>
                        </tr>
                    </table>
                    <?php for($idx = 1 ; $idx <= 10 ; $idx++): ?>
                        <h3><?php echo __('Provider', 'wp-admin-obop'); ?> <?php echo $idx; ?></h3>

                        <?php settings_fields( 'obop-settings-group' ); ?>
                        <?php do_settings_sections( 'obop-settings-group' ); ?>
                        <table class="form-table">
                            <tr valign="top">
                                <th scope="row"><?php __('Name', 'wp-admin-obop'); ?></th>
                                <?php $provider = get_option('provider'); ?>
                                <td><input type="text" name="provider[<?php echo $idx; ?>]" value="<?php echo $provider[$idx]; ?>" /></td>
                            </tr>

                            <tr valign="top">
                                <th scope="row">Script</th>
                                <?php $script = get_option('script'); ?>
                                <td><textarea name="script[<?php echo $idx; ?>]" rows="10" style="width:100%;"><?php echo $script[$idx]; ?></textarea></td>
                            </tr>

                            <tr valign="top">
                                <th scope="row">Position</th>
                                <?php $position = get_option('position'); ?>
                                <td>
                                    <select name="position[<?php echo $idx; ?>]">
                                        <option><?php echo __('None', 'wp-admin-obop'); ?></option>
                                        <option value="1" <?php echo ($position[$idx] == 1) ? "selected" : ""; ?>><?php echo __('Before every post', 'wp-admin-obop'); ?></option>
                                        <option value="2" <?php echo ($position[$idx] == 2) ? "selected" : ""; ?>><?php echo __('After every post', 'wp-admin-obop'); ?></option>
                                    </select>
                                </td>
                            </tr>
                        </table>
                    
                        <?php submit_button(); ?>
                    <?php endfor; ?>
                </form>
            </div>
            <?php
        }

        /**
         * Register and add settings
         */
        public function page_init()
        {        
            register_setting( 'obop-settings-group', 'obopuid' );     
            register_setting( 'obop-settings-group', 'istest' );     
            register_setting( 'obop-settings-group', 'testip' );     
            register_setting( 'obop-settings-group', 'provider' );     
            register_setting( 'obop-settings-group', 'script' );     
            register_setting( 'obop-settings-group', 'position' );     
        }
    }
?>