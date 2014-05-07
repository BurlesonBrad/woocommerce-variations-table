<?php
/*
Plugin Name: Woocommerce Variations Table
Plugin URI: https://github.com/DonningerConsultancy/woocommerce-variations-table
Description: Display a table in stead of a drop down box for product variations on the product detail page. In this case, checkout is not used but a link from a custom product.
Version: 0.1
Author: Donninger Cosultancy
Author Email: niels@donninger.nl
License:

  Copyright 2011 Donninger Cosultancy (niels@donninger.nl)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as 
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
  
*/

class WoocommerceVariationsTable {

	/*--------------------------------------------*
	 * Constants
	 *--------------------------------------------*/
	const name = 'Woocommerce Variations Table';
	const slug = 'woocommerce_variations_table';
	
	/**
	 * Constructor
	 */
	function __construct() {
		//Hook up to the init action
		add_action( 'init', array( &$this, 'init_woocommerce_variations_table' ) );
		add_action( 'init', array( &$this, 'create_post_type' ) );
	}
  
	/**
	 * Runs when the plugin is activated
	 */  
	function install_woocommerce_variations_table() {
		// do not generate any output here
	}
  
	/**
	 * Runs when the plugin is initialized
	 */
	function init_woocommerce_variations_table() {
		/**
		* register custom post type
		*/
//		add_action( 'init', array( &$this, 'add_custom_post_type' ) );

		add_post_type_support( 'winkel', 'genesis-layouts' );

		if ( is_admin() ) {
			//this will run when in the WordPress admin
		} else {
			//this will run when on the frontend
			add_action( 'wp', array( &$this, 'remove_buy_buttons' ) );
			add_filter( 'woocommerce_product_tabs', array(&$this, 'woo_remove_product_tabs' ) );		
		}

		/*
		 * TODO: Define custom functionality for your plugin here
		 *
		 * For more information: 
		 * http://codex.wordpress.org/Plugin_API#Hooks.2C_Actions_and_Filters
		 */
	}
	
	function remove_buy_buttons(){
		
		// die early if we aren't on a product
		if ( ! is_product() ) return;
		
		$product = get_product();
		
		// removing the purchase buttons
		//remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
		//remove_action( 'woocommerce_simple_add_to_cart', 'woocommerce_simple_add_to_cart', 30 );
		//remove_action( 'woocommerce_grouped_add_to_cart', 'woocommerce_grouped_add_to_cart', 30 );
		//remove_action( 'woocommerce_variable_add_to_cart', 'woocommerce_variable_add_to_cart', 30 );
		//remove_action( 'woocommerce_external_add_to_cart', 'woocommerce_external_add_to_cart', 30 );		
	}
	
	function woo_remove_product_tabs( $tabs ) {
	 
	    unset( $tabs['description'] );      	// Remove the description tab
	    //unset( $tabs['reviews'] ); 			// Remove the reviews tab
	    unset( $tabs['additional_information'] );  	// Remove the additional information tab
	 
	    return $tabs;
	 
	}
	
	
	function create_post_type() {
		register_post_type( 'online_shops',
			array(
				'labels' => array(
					'name' => __( 'Online Shops' ),
					'singular_name' => __( 'Online Shop' )
				),
			'public' => true,
			'has_archive' => true,
			)
		);
	}

} // end class
new WoocommerceVariationsTable();

function woocommerce_variable_add_to_cart() {
	global $product, $post;
	$variations = $product->get_available_variations(); 
	?>
	<table><thead><tr><td>Winkel</td><td>Prijs</td></tr></thead><tbody>
	<?php

	foreach ($variations as $key => $value) {
	?>
		<input type="hidden" name="variation_id" value="<?php echo $value['variation_id']?>" />
		<input type="hidden" name="product_id" value="<?php echo esc_attr( $post->ID ); ?>" />
		<?php
		$shop_url = "";
	    $shop_url = get_post_meta( $value['variation_id'], '_shop_url', true);
		
		if(!empty($value['attributes'])){
			$shop_name = "";
			foreach ($value['attributes'] as $attr_key => $attr_value) {
				if($attr_key=="attribute_pa_winkel") {
					$shop_name = $attr_value;
				}
			?>
			<input type="hidden" name="<?php echo $attr_key?>" value="<?php echo $attr_value?>">
			<?php
			}
		}
		?>
		<tr>
			<td>
				<b><?php echo $shop_name ?></b>
			</td>
			<td>
				<?php echo $value['price_html'];?>
			</td>
			<td><?php if($shop_url != "" ) { ?>
				<a target="_blank" href="<?php echo $shop_url ?>"><span class="button">Ga naar <?php echo $shop_name ?></span></a>			
				
			<?php } ?></td>
		</tr>
		<?php
	}
	?>
	</tbody></table>
	<?php	
}

/**
* add variation custom field for Shop URL
*/
//Display Fields
add_action( 'woocommerce_product_after_variable_attributes', 'variable_fields', 10, 2 );
//JS to add fields for new variations
add_action( 'woocommerce_product_after_variable_attributes_js', 'variable_fields_js' );
//Save variation fields
add_action( 'woocommerce_process_product_meta_variable', 'variable_fields_process', 10, 1 );
 
function variable_fields( $loop, $variation_data ) {
?>	
	<tr>
		<td>
			<div>
					<label><?php _e( 'Winkel URL', 'woocommerce' ); ?></label>
					<input type="text" size="5" name="shop_url[<?php echo $loop; ?>]" value="<?php echo $variation_data['_shop_url'][0]; ?>"/>
			</div>
		</td>
	</tr>
<?php
}
 
function variable_fields_js() {
?>
<tr>\
		<td>\
			<div>\
					<label><?php _e( 'Winkel URL', 'woocommerce' ); ?></label>\
					<input type="text" size="5" name="shop_url[' + loop + ']" />\
			</div>\
		</td>\
	</tr>\
<?php
}
 
function variable_fields_process( $post_id ) {
	if (isset( $_POST['variable_sku'] ) ) :
		$variable_sku = $_POST['variable_sku'];
		$variable_post_id = $_POST['variable_post_id'];
		$variable_custom_field = $_POST['shop_url'];
		for ( $i = 0; $i < sizeof( $variable_sku ); $i++ ) :
			$variation_id = (int) $variable_post_id[$i];
			if ( isset( $variable_custom_field[$i] ) ) {
				update_post_meta( $variation_id, '_shop_url', stripslashes( $variable_custom_field[$i] ) );
			}
		endfor;
	endif;
}

/**
* Hide product SKU fields
*/
add_filter('option_woocommerce_enable_sku', function ($value) 
{ 
if (!is_admin()) 
{ 
return 'no'; 
}

return $value; 
});
 	