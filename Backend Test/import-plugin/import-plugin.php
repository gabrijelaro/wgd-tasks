<?php
/**
 * Plugin Name: Import Plugin
 * Description: Wholegrain Starter plugin for developer test
 * Version: 0.1
 * Author: Wholegrain Digital
*/


function create_posttype() {

    register_post_type( 'wgd-locations',
        array(
            'labels' => array(
                'name' => __( 'WGD Locations' ),
                'singular_name' => __( 'WGD Location' ),
                'add_new' => __('Add New Location'),
                'edit_item'=> __('Edit Location'),
                'search' => __('Search Locations'),
            ),
            'menu_position'=>5,
            'public' => true,
            'exclude_from_search'=>true,
            'has_archive' => true,
            'rewrite' => array('slug' => 'wgd-locations'),
            'supports' => array('title','editor','thumbnail'),
            'show_in_rest' => true,

        )
    );

    register_taxonomy(
        'wgd-locations-category',
        'wgd-locations',
        array(
            'label' => __( 'Locations categories' ),
            'hierarchical' => true,
            'show_in_rest' => true,
            'public' => true,
            'show_admin_column' => true,

        )
    );
    register_taxonomy_for_object_type('wgd-locations-category', 'wgd-locations');

}
add_action( 'init', 'create_posttype' );

function wpdocs_register_my_custom_menu_page() {
    add_menu_page(
        __( 'Import-Plugin Settings', 'import-plugin' ),
        __( 'Import-Plugin Settings', 'import-plugin' ),
        'manage_options',
        'import-plugin-settings-page',
        'import_plugin_content',
        '',
        null
    );
}
add_action( 'admin_menu', 'wpdocs_register_my_custom_menu_page' );


function import_plugin_content(){
    ?>
    <h3>Import CSV from url:</h3>

    <form action="" method="post" enctype="multipart/form-data">
        https://gitlab.com/wholegrain/webdeveloper-test/-/raw/master/backend-test/locations.csv<br><br>
        <input type="submit" value="import" name="import">
    </form>

    <?php

    // Handle the CSV File
    if (isset($_POST['import'])) {

        $data = file_get_contents("https://gitlab.com/wholegrain/webdeveloper-test/-/raw/master/backend-test/locations.csv");

        $rows = explode("\n",$data);

        $i=0;
        foreach($rows as $row) {
            $location = str_getcsv($row);

            if ($i > 0) { 

                $content = "<p><strong>Description: </strong>".$location[3]."</p>";

                $post_id = wp_insert_post([
                    'post_title'    => $location[1],
                    'post_content'  => $content,
                    'post_type' => 'wgd-locations',
                    'post_status'   => 'publish',
                    'post_author'   => 1,
                ]);
                // In folder are exported Custom Fields from database wp_post table
                update_field( "field_605f8d742b436", $location[0], $post_id );
                update_field( "field_605f8d7b2b437", $location[4], $post_id );


                if (!term_exists( $location[2], 'wgd-locations-category') ) {
                    wp_insert_term( $location[2], 'wgd-locations-category' );
                }

                $category = get_term_by('name', $location[2], 'wgd-locations-category');

                // Assign category to Post
                $category_id = $category->term_taxonomy_id;
                $taxonomy = 'wgd-locations-category';
                wp_set_object_terms( $post_id, intval( $category_id ), $taxonomy );
            }
            
            $i++;
        }

        ?>
        <p>
            Import Done ! Go to
            <a href="/wp-admin/edit.php?post_type=wgd-locations">Locations</a>
            to see the entries
        </p>
        <?php
    } 
}



