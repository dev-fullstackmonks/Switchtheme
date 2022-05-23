<?php
/**
 * Plugin Name: Switch theme
 * Plugin URI: https://twitter.com/webcreator06
 * Description: This is custom plugin
 * Version: 1.0
 * Author: Sikander Maan
 * Author URI: https://twitter.com/webcreator06
 */



/* Below function is for adding meta box in page */ 
function sik_add_custom_box() {
    $screens = ['page','wfacp_checkout','our-story','wffn_landing','wffn_ty'];
    foreach ( $screens as $screen ) {
        add_meta_box(
            'wporg_box_id',                 
            'Select Page Theme',      
            'sik_custom_box_html', 
            $screen                      
        );
    }
}
add_action( 'add_meta_boxes', 'sik_add_custom_box' );


/* Below function is for adding meta box HTML in page */
function sik_custom_box_html( $post ) {
    $value = get_post_meta( $post->ID, '_current_page_selected_theme', true );
    if(!empty($value)){
        echo '<b>Selected Theme: '.end(explode(',',$value)).'</b><br/><br/>';
    }else{
        echo '<b>Selected Theme: BuddyBoss Child</b><br/><br/>';
    }?>
     <select name="current_page_theme" id="current_page_theme" class="postbox">
        <option value="buddyboss-theme,buddyboss-theme-child,BuddyBoss Child">BuddyBoss Child</option>
        <option value="holiniq,holiniq,Holiniq">Holiniq</option>
        <option value="storefront,storefront,Storefront">Storefront</option>
    </select>
    <?php
}


/* Below function is for saving Meta box data in DB */
function sik_save_postdata( $post_id ) {
    if ( array_key_exists( 'current_page_theme', $_POST ) ) {
        update_post_meta(
            $post_id,
            '_current_page_selected_theme',
            $_POST['current_page_theme']
        );
    }
}
add_action( 'save_post', 'sik_save_postdata' );


/* Below function is for Switch theme */
add_action( 'plugins_loaded', 'switch_theme_custom' );
function switch_theme_custom() {
    if(isset($_SERVER['REQUEST_URI']) && !wp_doing_ajax() && !wp_doing_cron()){
        
        $foundString = $_SERVER['REQUEST_URI'];
        $searchs = array('/shop/', '/product/','/blog/page/','/shop/page/','/blog/');
        $isthemeUsed = 'no';
        foreach($searchs as $search){
            if(strpos($foundString, $search) !== false){
                update_option('template','holiniq');
                update_option('stylesheet','holiniq');
                update_option('current_theme','Holiniq');
                $isthemeUsed = 'yes';
            }
        }
        if($isthemeUsed == 'no'){
            $URLData = parse_url($_SERVER['REQUEST_URI']);
            $currentPageSlug = explode("/", trim($URLData['path'],'/'));
            $currentPageSlug = end($currentPageSlug);
            global $wpdb;
            $postTable = $wpdb->prefix.'posts';
            if(isset($_GET['post']) && isset($_GET['action'])){
                $results = $wpdb->get_results("SELECT ID FROM $postTable WHERE ID = ".$_GET['post']." AND (post_type = 'wffn_ty' || post_type = 'page' || post_type = 'our-story' || post_type = 'wfacp_checkout' || post_type = 'wffn_landing')");
            }else{
                if(empty($currentPageSlug)){
                    if(!is_user_logged_in()){ $pageId = 10682; }else{ $pageId = 853; }
                    $results = $wpdb->get_results("SELECT ID FROM $postTable WHERE ID = ".$pageId." AND (post_type = 'wffn_ty' || post_type = 'page' || post_type = 'our-story' || post_type = 'wfacp_checkout' || post_type = 'wffn_landing')");
                }else{
                    $results = $wpdb->get_results("SELECT ID, post_type FROM $postTable WHERE post_name LIKE '%".$currentPageSlug."%' AND (post_type = 'wffn_ty' || post_type = 'page' || post_type = 'our-story' || post_type = 'wfacp_checkout' || post_type = 'wffn_landing')");
                }
            }
            $defulteTheme = 'yes';
            if(!empty($results)){
                $themeName = get_post_meta($results[0]->ID, '_current_page_selected_theme', true );
                if(!empty($themeName)){
                    $defulteTheme = 'no';
                    $themeName = explode(',',$themeName);
                    update_option('template',$themeName[0]);
                    update_option('stylesheet',$themeName[1]);
                    update_option('current_theme',$themeName[2]);
                }
            }
            if($defulteTheme == 'yes'){
                update_option('template','storefront');
                update_option('stylesheet','storefront');
                update_option('current_theme','Storefront');
            }
        }
    }
}
?>
