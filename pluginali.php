<?php
/*
Plugin Name: Plugin Dev Test
Description: Create CPT with 2 CTs, get data from JSON. This plugin take few minutes to activate because of populating posts.
Author: Ali Jaun
*/

class vsetup {
     function __construct() {
          register_activation_hook( __FILE__ , array( $this , 'activate' ));
          add_action( 'init', array( $this, 'create_product_post_type' ));
          add_action( 'init', array( $this, 'create_taxonomies' ));
          add_action( 'add_meta_boxes', array( $this, 'add_product_custom_fields' ));
          add_action( 'save_post_product', array( $this, 'save_product_custom_fields' ));
          add_shortcode( 'product_archive', array( $this, 'product_display' ) );
     } 

     function activate() {
          $this->create_product_post_type();
          $this->create_taxonomies();
          $this->import_data();
          
	}
          
    function create_product_post_type() {
	    $labels = array(
	        'name' => 'Products',
	        'singular_name' => 'Product',
	    );
	    $args = array(
	        'labels' => $labels,
	        'public' => true,
	        'has_archive' => true,
	        'menu_icon' => 'dashicons-cart',
	        'supports' => array( 'title', 'editor', 'thumbnail' ),
	    );
	    register_post_type( 'product', $args );
	}

   function create_taxonomies() {

    // Brand Taxonomy
    $labels = array(
        'name'              => 'Brands',
        'singular_name'     => 'Brand',
        'search_items'      => 'Search Brands',
        'all_items'         => 'All Brands',
        'parent_item'       => 'Parent Brand',
        'parent_item_colon' => 'Parent Brand:',
        'edit_item'         => 'Edit Brand',
        'update_item'       => 'Update Brand',
        'add_new_item'      => 'Add New Brand',
        'new_item_name'     => 'New Brand Name',
        'menu_name'         => 'Brands',
    );
    $args = array(
        'labels'            => $labels,
        'hierarchical'      => true,
        'public'            => true,
        'show_ui'           => true,
        'show_admin_column' => true,
        'show_in_nav_menus' => true,
        'show_tagcloud'     => true,
    );
    register_taxonomy( 'brand', 'product', $args );

    // Category Taxonomy
    $labels = array(
        'name'              => 'Categories',
        'singular_name'     => 'Category',
        'search_items'      => 'Search Categories',
        'all_items'         => 'All Categories',
        'parent_item'       => 'Parent Category',
        'parent_item_colon' => 'Parent Category:',
        'edit_item'         => 'Edit Category',
        'update_item'       => 'Update Category',
        'add_new_item'      => 'Add New Category',
        'new_item_name'     => 'New Category Name',
        'menu_name'         => 'Categories',
    );
    $args = array(
        'labels'            => $labels,
        'hierarchical'      => true,
        'public'            => true,
        'show_ui'           => true,
        'show_admin_column' => true,
        'show_in_nav_menus' => true,
        'show_tagcloud'     => true,
    );
    register_taxonomy( 'category', 'product', $args );
	}


	function add_product_custom_fields() {
	    add_meta_box(
	        'product_custom_fields',
	        'Product Custom Fields',
	        array( $this, 'product_custom_fields_callback'),
	        'product',
	        'normal',
	        'default'
	    );
	}

	
function product_custom_fields_callback( $post ) {
    $api_id = get_post_meta( $post->ID, '_api_id', true );
    $price = get_post_meta( $post->ID, '_price', true );
    $discount_percentage = get_post_meta( $post->ID, '_discount_percentage', true );
    $rating = get_post_meta( $post->ID, '_rating', true );
    $stock = get_post_meta( $post->ID, '_stock', true );
    
    // API ID Field
    echo '<p><label for="api_id">API ID:</label><br />';
    echo '<input type="text" id="api_id" name="api_id" value="' . esc_attr( $api_id ) . '"></p>';
    
    // Price Field
    echo '<p><label for="price">Price:</label><br />';
    echo '<input type="text" id="price" name="price" value="' . esc_attr( $price ) . '"></p>';
    
    // Discount Percentage Field
    echo '<p><label for="discount_percentage">Discount Percentage:</label><br />';
    echo '<input type="text" id="discount_percentage" name="discount_percentage" value="' . esc_attr( $discount_percentage ) . '"></p>';
    
    // Rating Field
    echo '<p><label for="rating">Rating:</label><br />';
    echo '<input type="text" id="rating" name="rating" value="' . esc_attr( $rating ) . '"></p>';
    
    // Stock Field
    echo '<p><label for="stock">Stock:</label><br />';
    echo '<input type="text" id="stock" name="stock" value="' . esc_attr( $stock ) . '"></p>';
}

function save_product_custom_fields( $post_id ) {
    if ( isset( $_POST['api_id'] ) ) {
        update_post_meta( $post_id, '_api_id', sanitize_text_field( $_POST['api_id'] ) );
    }
    
    if ( isset( $_POST['price'] ) ) {
        update_post_meta( $post_id, '_price', sanitize_text_field( $_POST['price'] ) );
    }
    
    if ( isset( $_POST['discount_percentage'] ) ) {
        update_post_meta( $post_id, '_discount_percentage', sanitize_text_field( $_POST['discount_percentage'] ) );
    }
    
    if ( isset( $_POST['rating'] ) ) {
        update_post_meta( $post_id, '_rating', sanitize_text_field( $_POST['rating'] ) );
    }
    
    if ( isset( $_POST['stock'] ) ) {
        update_post_meta( $post_id, '_stock', sanitize_text_field( $_POST['stock'] ) );
    }
}


function import_data() {
	$request = wp_remote_get( 'https://dummyjson.com/products' );

	if( is_wp_error( $request ) ) {
		return false;
	}

	$body = wp_remote_retrieve_body( $request );

	$data = json_decode( $body );
	$products = $data->products;

	if( ! empty( $data ) ) {

		foreach( $products as $product ) {
			$brand_id = term_exists( $product->brand, 'brand', 0 );
			if ( !$brand_id ) {
			    $brand_id = wp_insert_term( $product->brand, 'brand' , 0 );
			}

			$category_id = term_exists( $product->category, 'category', 0 );
			if ( !$category_id ) {
			    $category_id = wp_insert_term( $product->category, 'category', 0 );
			}


			$my_post = array(
				'post_type' => 'product',
				'post_title'    => $product->title,
				'post_content' =>  $product->description,
				'post_status'  => 'publish',
				'tax_input'    => array(
					'brand'    => $brand_id['term_taxonomy_id'],
					'category' => $category_id['term_taxonomy_id'],
				),

				'meta_input'   => array(
					'_api_id' => $product->id,
					'_price' => $product->price,
					'_discount_percentage' => $product->discountPercentage,
					'_rating' => $product->rating,
					'_stock' => $product->stock,
				),
			);


			$post_id = wp_insert_post( $my_post );
			$name = basename($product->thumbnail); 

		
			$image_url        = $product->thumbnail; 
			$image_name       = $name;
			$upload_dir       = wp_upload_dir(); 
			$image_data       = file_get_contents($image_url); 
			$unique_file_name = wp_unique_filename( $upload_dir['path'], $image_name );
			$filename         = basename( $unique_file_name ); 

			if( wp_mkdir_p( $upload_dir['path'] ) ) {
			    $file = $upload_dir['path'] . '/' . $filename;
			} else {
			    $file = $upload_dir['basedir'] . '/' . $filename;
			}

			file_put_contents( $file, $image_data );

			$wp_filetype = wp_check_filetype( $filename, null );

			$attachment = array(
			    'post_mime_type' => $wp_filetype['type'],
			    'post_title'     => sanitize_file_name( $filename ),
			    'post_content'   => '',
			    'post_status'    => 'inherit'
			);

			$attach_id = wp_insert_attachment( $attachment, $file, $post_id );

			require_once(ABSPATH . 'wp-admin/includes/image.php');

			$attach_data = wp_generate_attachment_metadata( $attach_id, $file );

			wp_update_attachment_metadata( $attach_id, $attach_data );

			set_post_thumbnail( $post_id, $attach_id );
		 	}
		}
	}


	function product_display( $atts, $content ) {
	   // extract( shortcode_atts( array(     
	   //    'foo'       => '',
	   //    'icon'      => '',
	   //    'counter'   => '0',
	   //    'plus_sign' => 'yes',
	   //    'delay_time'=> '',
	   //  ), $atts ) );
	  ob_start();
	  ?>

	  <table class="table table-striped">
	  <thead>
	    <tr>
	      <th scope="col">#</th>
	      <th scope="col">First</th>
	      <th scope="col">Last</th>
	      <th scope="col">Handle</th>
	    </tr>
	  </thead>
	  <tbody>
	  	<?php
          $args = array( 'posts_per_page'         => -1,
          				 'post_type'              => 'product' );
            $loop = new WP_Query( $args );
            while ( $loop->have_posts() ) : $loop->the_post(); ?>
	    <tr>
	      <th scope="row">1</th>
	      <td><?php the_title(); ?></td>
	      <td>Otto</td>
	      <td>@mdo</td>
	    </tr>
		<?php endwhile; ?>
	    
	  </tbody>
	</table>
	  
	 <?php 
	  $cont = ob_get_contents();
	  ob_end_clean();
	  return $cont;
	} 
}


new vsetup();

