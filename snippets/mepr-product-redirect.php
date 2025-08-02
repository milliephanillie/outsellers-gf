<?php
/**
 * Gravity Forms → MemberPress Product Redirect
 *
 * This snippet hooks into the Gravity Forms confirmation action for a specific form (ID = 1)
 * and dynamically redirects users to a MemberPress product page after form submission.
 * 
 * It reads a hidden field containing the MemberPress product ID (field ID = 11), verifies that the product exists,
 * and constructs a redirect URL to the product page with the Gravity Forms entry ID appended as a query parameter.
 * 
 * This setup allows developers to customize the MemberPress product page based on submitted entry data
 * — such as pre-filling fields, modifying copy, or displaying user-specific information.
 * 
 * If no matching product is found or data is invalid, it gracefully falls back to a default URL.
 */
$gf_form_id = 1;

add_filter('gform_confirmation_' . $gf_form_id, 'redirect_to_mepr_product', 10, 4);
function redirect_to_mepr_product($confirmation, $form, $entry, $ajax) {
    $fall_back_redirect = home_url('/fallback');
	$entry_id   = rgar($entry, 'id'); // Get the Gravity Forms entry ID if we want to pass the data to the Memberpress product page

    // Get the MemberPress product ID from a hidden form field.
	// This allows us to optionally change the MemberPress signup page’s design or content — such as customizing text or pre-filling fields — using entry data like the start date.
	$product_id = (int) rgar($entry, '11'); 

	// Ensure valid entry and product_id
	if ($entry_id && is_numeric($product_id) && $product_id > 0) {

		// Optionally verify the product exists
		$args = [
			'post_type'      => 'memberpressproduct',
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'p'              => $product_id,
		];

		$query = new WP_Query($args);

		if ($query->have_posts()) {
			$product      = $query->posts[0];
			$redirect_url = get_permalink($product->ID) . '?entry=' . $entry_id; // can use this ID to pull entry specific data and modify template

			error_log("Redirecting to: $redirect_url");
			return ['redirect' => $redirect_url];
		} else {
			error_log("No matching product found for ID: $product_id");
			return ['redirect' => $fall_back_redirect];
		}
	}

	// Default fallback (no redirect)
	return $confirmation;
}
