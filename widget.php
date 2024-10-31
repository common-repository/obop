<?php 
    class obop_widget extends WP_Widget {

        // constructor
        function obop_widget() {
            $widget_ops = array( 
                'class_name' => 'obop_widget',
                'description' => 'OBOP',
            );
            parent::__construct( 'obop_widget', 'OBOP', $widget_ops );
        }

        // widget form creation
        function form($instance) {	
            $title = ! empty( $instance['title'] ) ? $instance['title'] : __( 'New title', 'wp-admin-obop' );
            $provider = ! empty( $instance['provider'] ) ? $instance['provider'] : '';
            
            ?>
            <p>
                
            <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
            <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
            
            <label for="<?php echo $this->get_field_id( 'provider' ); ?>"><?php _e( 'Fournisseur:' ); ?></label> 
            <select class="widefat" id="<?php echo $this->get_field_id( 'provider' ); ?>" name="<?php echo $this->get_field_name( 'provider' ); ?>">
                <?php foreach(get_option('provider') as $option): ?>
                    <?php if($option != ""): ?>
                        <option value="<?php echo $option; ?>" <?php echo ($provider == $option) ? "selected" : ""; ?>><?php echo $option; ?></option>
                    <?php endif; ?>
                <?php endforeach; ?>
            </select>
            </p>
            <?php 
        }

        // widget update
        function update($new_instance, $old_instance) {
              $instance = $old_instance;
              // Fields
              $instance['title'] = strip_tags($new_instance['title']);
              $instance['provider'] = strip_tags($new_instance['provider']);
             return $instance;
        }
        
        
        
        // widget display
        function widget($args, $instance) {
            $provider = $instance["provider"];
            $scripts = get_option('script');
            $istest = get_option('istest');
            $testip = get_option('testip');
            $obopuid = get_option('obopuid');
            $script = "";
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
                    if($option == $provider && isset($scripts[$key]))
                        $script = $scripts[$key];

                if(!isset($_COOKIE["isObopAuth"]))
                    echo $script;

                
                if(!function_exists('obopScript'))
                {
                    function obopScript()
                    {
                        ?>
                        <script type="text/javascript">
                            (function (a, b) {
                                c = '<?php echo $obopuid; ?>';
                                d = b.createElement('script');
                                d.async = 1;
                                d.src = '//dev.obop.co/js/obop.js';
                                e = b.getElementsByTagName('script')[0];
                                e.parentNode.insertBefore(d, e)
                            })(window, document);
                        </script>
                        <?php
                    }
                }
                
                add_action('wp_footer', 'obopScript');
            }
        }
    }
?>