<?php
/*
  Plugin Name: Project Team Plugin
  Description: Affiche la liste des participants à un projet de recherche
 */
/* Start Adding Functions Below this Line */

// Creating the widget 
class projectTeam_widget extends WP_Widget {

    function __construct() {
        parent::__construct(
// Base ID of your widget
                'projectTeam_widget',
// Widget name will appear in UI
                __('ProjectTeam Widget'),
// Widget description
                array('description' => __('Permet d\'ajouter la liste des participants à un projet de recherche'),)
        );
    }

// Creating widget front-end
// This is where the action happens
    public function widget($args, $instance) {

        $staffString = apply_filters('widget_title', $instance['staffString']);
        $phdPeopleString = apply_filters('widget_title', $instance['phdPeopleString']);
        $postDocPeopleString = apply_filters('widget_title', $instance['postDocPeopleString']);
// before and after widget arguments are defined by themes
        echo $args['before_widget'];
        echo $args['before_title'] . 'Project Team' . $args['after_title'];

// This is where you run the code and display the output

///Display Staff people
       
        if ($staffString != ''){
        $staffArray = explode(",", $staffString);
        $firstStaff = true;
        foreach ($staffArray as $staffPerson) {
            if ($firstStaff) {
                echo '<span class="widget-subtitle">STAFF</span>';
                echo $staffPerson . '<br/>';
                $firstStaff = false;
            } else {
                echo $staffPerson . '<br/>';
            }
        }
        }
        
///Display PhD people
        if ($phdPeopleString != ''){
        $firstPhd = true;
        $phdPeopleArray = explode(",", $phdPeopleString);
        foreach ($phdPeopleArray as $phdPerson) {
            if ($firstPhd) {
                echo '<span class="widget-subtitle">PhD THESIS</span>';
                echo $phdPerson . '<br/>';
                $firstPhd = false;
            } else {
                echo $phdPerson . '<br/>';
            }
        }
        }
///Display Post Doc people

        if ($postDocPeopleString != ''){
        $firstPostDoc = true;
        $postDocPeopleArray = explode(",", $postDocPeopleString);
        foreach ($postDocPeopleArray as $postDocPerson) {
            if ($firstPostDoc) {
                echo '<span class="widget-subtitle">Post DOCTORANTS</span>';
                echo $postDocPerson . '<br/>';
                $firstPostDoc = false;
            } else {
                echo $postDocPerson . '<br/>';
            }
        }
        }
        
///End of the widget        
        echo $args['after_widget'];
    }

// Widget Backend 
    public function form($instance) {
        if (isset($instance['staffString'])) {
            $staffString = $instance['staffString'];
        } else {
            $staffString = ('');
        }

        if (isset($instance['phdPeopleString'])) {
            $phdPeopleString = $instance['phdPeopleString'];
        } else {
            $phdPeopleString = ('');
        }

        if (isset($instance['postDocPeopleString'])) {
            $postDocPeopleString = $instance['postDocPeopleString'];
        } 
        else {
            $postDocPeopleString = ('');
        }
// Widget admin form
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('staffString'); ?>"><?php _e('Staff (personnes séparées par des virgules)'); ?></label> 
            <input class="widefat" id="<?php echo $this->get_field_id('staffString'); ?>" name="<?php echo $this->get_field_name('staffString'); ?>" type="text" value="<?php echo esc_attr($staffString); ?>" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('phdPeopleString'); ?>"><?php _e('Doctorants (personnes séparées par des virgules)'); ?></label> 
            <input class="widefat" id="<?php echo $this->get_field_id('phdPeopleString'); ?>" name="<?php echo $this->get_field_name('phdPeopleString'); ?>" type="text" value="<?php echo esc_attr($phdPeopleString); ?>" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('postDocPeopleString'); ?>"><?php _e('Post-Docs (personnes séparées par des virgules)'); ?></label> 
            <input class="widefat" id="<?php echo $this->get_field_id('postDocPeopleString'); ?>" name="<?php echo $this->get_field_name('postDocPeopleString'); ?>" type="text" value="<?php echo esc_attr($postDocPeopleString); ?>" />
        </p>
        <?php
    }

// Updating widget replacing old instances with new
    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['staffString'] = (!empty($new_instance['staffString']) ) ? strip_tags($new_instance['staffString']) : '';
        $instance['phdPeopleString'] = (!empty($new_instance['phdPeopleString']) ) ? strip_tags($new_instance['phdPeopleString']) : '';
        $instance['postDocPeopleString'] = (!empty($new_instance['postDocPeopleString']) ) ? strip_tags($new_instance['postDocPeopleString']) : '';
        return $instance;
    }

}

// Class projectTeam_widget ends here
// Register and load the widget
function projectTeam_load_widget() {
    register_widget('projectTeam_widget');
}

add_action('widgets_init', 'projectTeam_load_widget');
/* Stop Adding Functions Below this Line */
?>