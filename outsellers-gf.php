<?php
/**
 * Plugin Name:     Outsellers GF
 * Plugin URI:      https://philiparudy.com
 * Description:     A simple plugin to extend gravity forms
 * Author:          Philip Rudy
 * Author URI:      https://philiprudy.com
 * Text Domain:     outsellers
 * Domain Path:     /languages
 * Version:         1.0.0
 */

if (!defined('ABSPATH')) exit;

define('OUTSELLERS_GF_PLUGIN', __FILE__);
define('OUTSELLERS_GF_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('OUTSELLERS_GF_PLUGIN_URL', plugin_dir_url(__FILE__));
define('OUTSELLERS_GF_ASSETS_VERSION', '1.0.1');

include_once 'filters.php';

class OtslrGf {
	public function boot() {
		add_action('wp_enqueue_scripts', [$this, 'maybe_enqueue_styles']);
		add_filter('mepr-signup-styles', [$this, 'enqueue_mepr_styles']);
	}

	public function maybe_enqueue_styles() {
		wp_enqueue_style('otslr-gf', OUTSELLERS_GF_PLUGIN_URL . 'resources/assets/css/otslr-gf.css', [], time());
		//wp_enqueue_style('otslr-gf', OUTSELLERS_GF_PLUGIN_URL . 'resources/otslr-gf.css', [], time());
		wp_enqueue_style('otslr-gf', OUTSELLERS_GF_PLUGIN_URL . 'resources/aa.css', [], time());
		
	}

	public function enqueue_mepr_styles($prereqs) {
		if (!is_array($prereqs)) {
			$prereqs = [];
		}

		wp_register_style(
			'outsellers-checkout',
			OUTSELLERS_GF_PLUGIN_URL . 'resources/assets/css/outsellers-checkout.css',
			[],
			OUTSELLERS_GF_ASSETS_VERSION
		);

		$prereqs[] = 'outsellers-checkout';

		return $prereqs;
	}
}

// add_action('wp_enqueue_scripts', function () {
//     if (function_exists('gravity_form_enqueue_scripts')) {
//         gravity_form_enqueue_scripts(2, true); // Replace 2 with your actual form ID
//     }

//     // Manually enqueue core Gravity Forms scripts
//     wp_enqueue_script('gform_gravityforms', GFCommon::get_base_url() . '/js/gravityforms.min.js', ['jquery'], null, true);
//     wp_enqueue_script('gform_json', GFCommon::get_base_url() . '/js/jquery.json.min.js', ['jquery'], null, true);
//     wp_enqueue_script('gform_conditional_logic', GFCommon::get_base_url() . '/js/conditional_logic.min.js', ['jquery'], null, true);
//     wp_enqueue_script('gform_form', GFCommon::get_base_url() . '/js/form.min.js', ['jquery'], null, true);
// });



$otslrGf = new OtslrGf();
add_action('init', [$otslrGf, 'boot']);

add_filter('mepr_view_paths', function($paths) {
	$custom_path = trailingslashit(plugin_dir_path(__FILE__)) . 'templates/memberpress';
	array_unshift($paths, $custom_path);
	return $paths;
});


add_action('init', 'set_otslr_global_price');
function set_otslr_global_price() {
	$form_id = 4;
	$form = GFAPI::get_form($form_id);

	$field_workers = GFAPI::get_field($form, 11);
	$field_hours   = GFAPI::get_field($form, 12);

	$total_workers = (int) ($field_workers->choices[0]['value'] ?? 0);
	$job_length    = (int) ($field_hours->choices[0]['value'] ?? 0);

	if (!$total_workers || !$job_length) {
		return;
	}

	$product_id = 1728;

	if (have_rows('prices', $product_id)) {
		while (have_rows('prices', $product_id)) {
			the_row();

			$acf_job_length    = (int) get_sub_field('job_length_hr');
			$acf_hourly_rate   = (float) str_replace('$', '', get_sub_field('worker_hourly'));
			$acf_downpayment   = (float) str_replace('$', '', get_sub_field('downpayment_per_worker'));

			if ($acf_job_length === $job_length) {
				global $book_price, $amount_due_now, $amount_due_later;

				$book_price = $acf_hourly_rate * $acf_job_length * $total_workers;
				$amount_due_now = $acf_downpayment * $total_workers;
				$amount_due_later = $book_price - $amount_due_now;

				return;
			}
		}
	}
}


// add_action('wp_enqueue_scripts', 'outsellers_scripts');
// function outsellers_scripts() {
// 	$form_id = 4;
// 	$form = GFAPI::get_form($form_id);

// 	$field_workers = GFAPI::get_field($form, 11);
// 	$field_hours   = GFAPI::get_field($form, 12);

// 	$worker_choices = $field_workers->choices ?? [];
// 	$hours_choices  = $field_hours->choices ?? [];

// 	global $bookChoices;
// 	$bookChoices = [
// 		'worker' => $worker_choices,
// 		'hours'  => $hours_choices,
// 	];

// 	$worker_choices_first_value = (int) ($worker_choices[0]['value'] ?? 0); // Number of workers
// 	$hours_choices_first_value  = (int) ($hours_choices[0]['value'] ?? 0); // Job length in hours

// 	$product_post_id = 1728;
// 	$price = '';
// 	$amount_due_now = '';
// 	$amount_due_later = '';

// 	if (have_rows('prices', $product_post_id)) {
// 		while (have_rows('prices', $product_post_id)) {
// 			the_row();

// 			$acf_job_length  = (int) get_sub_field('job_length_hr');
// 			$acf_hourly_rate = (float) str_replace('$', '', get_sub_field('worker_hourly'));
// 			$acf_downpayment = (float) str_replace('$', '', get_sub_field('downpayment_per_worker'));

// 			if ($acf_job_length === $hours_choices_first_value) {
// 				$total_workers = $worker_choices_first_value;

// 				$price = $acf_hourly_rate * $acf_job_length * $total_workers;
// 				$amount_due_now = $acf_downpayment * $total_workers;
// 				$amount_due_later = $price - $amount_due_now;

// 				break;
// 			}
// 		}
// 	}

// 	// wp_register_script(
// 	// 	'outsellers-mepr-forms',
// 	// 	OUTSELLERS_GF_PLUGIN_URL . 'resources/assets/js/outsellers-mepr-forms.js',
// 	// 	[],
// 	// 	null,
// 	// 	true
// 	// );

// 	// wp_enqueue_script('outsellers-mepr-forms');
// 	// wp_localize_script('outsellers-mepr-forms', 'outsellersForms', [
// 	// 	'siteUrl'             => site_url(),
// 	// 	'restUrl'             => trailingslashit(site_url() . '/' . rest_get_url_prefix()),
// 	// 	'coupon_nonce'        => wp_create_nonce('mepr_coupons'),
// 	// 	'iconsPath'           => OUTSELLERS_GF_PLUGIN_URL . 'resources/assets/images/icons/',
// 	// 	'ajaxUrl'             => admin_url('admin-ajax.php'),
// 	// 	'post_id'             => $product_post_id,
// 	// 	'mepr_product_id'     => $product_post_id,
// 	// 	'restNonce'           => wp_create_nonce('wp_rest'),
// 	// 	'forgot_password_url' => site_url('/login/?action=forgot_password'),
// 	// 	'current_user_id'     => get_current_user_id(),
// 	// 	'current_user_email'  => wp_get_current_user()->user_email,
// 	// 	'current_user_login'  => wp_get_current_user()->user_login,
// 	// 	'price'               => $price,
// 	// 	'amount_due_now'      => $amount_due_now,
// 	// 	'amount_due_later'    => $amount_due_later,
// 	// 	'worker_choice_value' => $worker_choices_first_value,
// 	// 	'hour_choice_value'   => $hours_choices_first_value,
// 	// ]);
// }



add_action('rest_api_init', function () {
	register_rest_route('otslr/v1', '/get-pricing/', [
		'methods'  => 'GET',
		'callback' => 'otslr_get_dynamic_pricing',
		'permission_callback' => '__return_true',
		'args' => [
			'workers' => [
				'required' => true,
				'type'     => 'string'
			],
			'hours' => [
				'required' => true,
				'type'     => 'string'
			],
		],
	]);
});


function otslr_get_dynamic_pricing(WP_REST_Request $request) {
	$workers = (int) sanitize_text_field($request->get_param('workers'));
	$hours   = (int) sanitize_text_field($request->get_param('hours'));

	if (!$workers || !$hours) {
		return new WP_Error('invalid_params', 'Workers and hours are required and must be numeric.', ['status' => 400]);
	}

	$product_id = 1728;
	$price_rows = get_field('prices', $product_id);

	if (!empty($price_rows) && is_array($price_rows)) {
		foreach ($price_rows as $row) {
			$acf_job_length  = (int) $row['job_length_hr'];
			$acf_hourly_rate = (float) str_replace('$', '', $row['worker_hourly']);
			$acf_downpayment = (float) str_replace('$', '', $row['downpayment_per_worker']);

			if ($acf_job_length === $hours) {
				$total_price  = $acf_hourly_rate * $hours * $workers;
				$due_now      = $acf_downpayment * $workers;
				$due_later    = $total_price - $due_now;
				$redirect_url = get_permalink($product_id);

				return [
					'due_now'      => number_format($due_now, 2),
					'due_later'    => number_format($due_later, 2),
					'total_price'  => number_format($total_price, 2),
					'redirect_url' => $redirect_url,
				];
			}
		}
	}

	return new WP_Error('no_match', 'No matching pricing tier found.', ['status' => 404]);
}






