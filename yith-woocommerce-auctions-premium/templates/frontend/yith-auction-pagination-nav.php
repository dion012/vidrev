<?php
/**
 * Auction navigation template
 *
 * @package YITH\Auctions\Templates\Frontend
 */

?>
<nav class="woocommerce-pagination">
	<?php
	echo wp_kses_post(
		paginate_links(
			apply_filters(
				'woocommerce_pagination_args',
				array(
					'base'      => esc_url_raw( str_replace( 999999999, '%#%', remove_query_arg( 'add-to-cart', get_pagenum_link( 999999999, false ) ) ) ),
					'format'    => '',
					'add_args'  => false,
					'current'   => max( 1, get_query_var( 'paged' ) ),
					'total'     => $max_num_pages,
					'prev_text' => '&larr;',
					'next_text' => '&rarr;',
					'type'      => 'list',
					'end_size'  => 3,
					'mid_size'  => 3,
				)
			)
		)
	);
	?>
</nav>
<?php
