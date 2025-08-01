<?php
global $outsellers_gf_form_id;
$outsellers_gf_form_id = 1;



/**
 * 
 * Checkboxes
 */

add_filter( 'gform_field_content', 'custom_checkbox_html', 10, 5 );
function custom_checkbox_html( $content, $field, $value, $lead_id, $form_id ) {
	global $outsellers_gf_form_id;

	if ( is_admin() || $form_id != $outsellers_gf_form_id || $field->type !== 'checkbox' ) {
		return $content;
	}

	$has_three_col = strpos( $field->cssClass, 'otslr-three-col-checkbox' ) !== false;
	$wrapper_classes = 'otslr-checkboxes' . ( $has_three_col ? ' three-col' : 'otslr-checkboxes' );

	$output = '<div class="' . esc_attr( $wrapper_classes ) . '">';

	foreach ( $field->choices as $i => $choice ) {
		$index        = $i + 1;
		$g_choice     = "gchoice_{$form_id}_{$field->id}_{$index}";
		$choice_id    = "choice_{$form_id}_{$field->id}_{$index}";
		$input_id     = $choice_id;
		$label_id     = "label_{$form_id}_{$field->id}_{$index}";
		$input_name   = "input_{$field->id}.{$index}";
		$input_value  = esc_attr( $choice['value'] );
		$input_label  = esc_html( $choice['text'] );
		$is_checked   = is_array( $value ) && in_array( $choice['value'], $value ) ? 'checked' : '';

		$output .= "
			<div class='otslr-checkbox-wrapper checkbox-wrapper'>
				<div class='gchoice {$g_choice}' style='align-items: center; margin-bottom: 20px !important;'>
					<input type='checkbox' class='gfield-choice-input' name='{$input_name}' id='{$input_id}' value='{$input_value}' {$is_checked}>
					<label id='{$label_id}' for='{$input_id}' class='custom-checkbox'>{$input_label}</label>
				</div>
			</div>
		";
	}

	$output .= '</div>';

	return $output;
}



/**
 * Customize Gravity Forms radio output.
 */
add_filter('gform_field_content', 'custom_radio_html', 10, 5);
function custom_radio_html($content, $field, $value, $lead_id, $form_id) {
    global $outsellers_gf_form_id;

    if (is_admin()) {
        return $content;
    }

    if ($form_id == $outsellers_gf_form_id && $field->type == 'radio') {
        $custom_html = '<div class="otslr-radios">';

        foreach ($field->choices as $i => $choice) {
            $index = $i + 1;

            $g_choice    = "gchoice_{$form_id}_{$field->id}_{$index}";
            $choice_id   = "choice_{$form_id}_{$field->id}_{$index}";
            $label_id    = "label_{$form_id}_{$field->id}_{$index}";
            $input_name  = "input_{$field->id}";
            $input_value = esc_attr($choice['value']);
            $input_label = esc_html($choice['text']);

            $checked = $value == $choice['value'] ? 'checked' : '';

            $custom_html .= "
                <div class='otslr-radio-wrapper radio-wrapper'>
                    <div class='gchoice {$g_choice}' style='align-items: center;margin-bottom: 20px !important;'>
                        <input type='radio' class='gfield-choice-input' name='{$input_name}' id='{$choice_id}' value='{$input_value}' {$checked}>
                        <label id='{$label_id}' for='{$choice_id}' class='custom-radio'>{$input_label}</label>
                    </div>
                </div>
            ";
        }

        $custom_html .= '</div>';
        return $custom_html;
    }

    return $content;
}

/**
 * 
 * SELECT
 */

add_filter( 'gform_field_content', 'custom_select_html', 15, 5 );
function custom_select_html( $content, $field, $value, $lead_id, $form_id ) {
	global $outsellers_gf_form_id;
	if ( is_admin() ) {
		return $content;
	}
	
	if ( $form_id == $outsellers_gf_form_id  && $field->type == 'select' ) {
		$field_id     = $field->id;
		$field_label  = esc_html( $field->label );
		$input_name   = "input_{$field_id}";
		$input_id     = "input_{$form_id}_{$field_id}";
		$is_required  = $field->isRequired ? 'aria-required="true"' : '';
		$required_span = $field->isRequired ? '<span class="gfield_required"><span class="gfield_required gfield_required_asterisk">*</span></span>' : '';

// 		$custom_html  = "<label class='gfield_label gform-field-label' for='{$input_id}'>{$field_label}{$required_span}</label>";
		$custom_html .= "<div class='otslr-select-wrapper select-wrapper'>";
		$custom_html .= "<div class='ginput_container ginput_container_select'>";
		$custom_html .= "<select name='{$input_name}' id='{$input_id}' class='custom-select-field' {$is_required}>";

		foreach ( $field->choices as $choice ) {
			$selected = selected( $value, $choice['value'], false );
			$custom_html .= "<option value='" . esc_attr( $choice['value'] ) . "' {$selected}>" . esc_html( $choice['text'] ) . "</option>";
		}

		$custom_html .= "</select></div></div>";

		return $custom_html;
	}

	return $content;
}

/**
 * 
 * 		'text'     => 'otslr-text-wrapper',
 *		 'date'     => 'otslr-date-wrapper',
 *		 'textarea' => 'otslr-paragraph-wrapper',
 *		 'name'     => 'otslr-name-wrapper',
 */
add_filter( 'gform_field_content', 'custom_input_field_wrapper', 10, 5 );
function custom_input_field_wrapper( $content, $field, $value, $lead_id, $form_id ) {
	global $outsellers_gf_form_id;

	if ( is_admin() ) {
		return $content;
	}

	if ( $form_id != $outsellers_gf_form_id ) {
		return $content;
	}

	$field_type  = $field->type;
	$input_id    = "input_{$form_id}_{$field->id}";
	$input_name  = "input_{$field->id}";
	$input_value = esc_attr( $value );

	$wrapper_classes = [
		'text'     => 'otslr-text-wrapper',
		'date'     => 'otslr-date-wrapper',
		'textarea' => 'otslr-paragraph-wrapper',
		'name'     => 'otslr-name-wrapper',
		'email'    => 'otslr-email-wrapper',
		'phone'    => 'otslr-phone-wrapper',
		'website'  => 'otslr-website-wrapper',
	];

	if ( array_key_exists( $field_type, $wrapper_classes ) ) {
		$wrapper_class = $wrapper_classes[ $field_type ];

		if ( $field_type === 'date' ) {
			$input_html = "<input type='date' name='{$input_name}' id='{$input_id}' value='{$input_value}' class='gform-date-picker gfield-choice-input custom-date-field'>";
			return "<div class='{$wrapper_class}'><div>{$input_html}</div></div>";
		}

		if ( $field_type === 'textarea' ) {
			$input_html = "<textarea name='{$input_name}' id='{$input_id}' class='gform-textarea gfield-choice-input custom-textarea'>{$input_value}</textarea>";
			return "<div class='{$wrapper_class}'><div>{$input_html}</div></div>";
		}

		if ( $field_type === 'name' ) {
			preg_match('/(<legend.*<\/legend>)/isU', $content, $legend_match);
			$legend = $legend_match[1] ?? '';
			$fields = str_replace($legend, '', $content);

			return "<div class='{$wrapper_class}'>
						<div>
							<div class='otslr-legend'>{$legend}</div>
							<div class='otslr-name-fields'>{$fields}</div>
						</div>
					</div>";
		}

		if ( $field_type === 'email' ) {
			$field_label = esc_html( $field->label );
			$input_html = "<input type='email' name='{$input_name}' id='{$input_id}' value='{$input_value}' class='gform-text-input gfield-choice-input custom-text-field'>";
			return "<div class='{$wrapper_class}'><div><div class='otslr-label'><span>{$field_label}</span></div><div>{$input_html}</div></div></div>";
		}

		if ( $field_type === 'website' ) {
			$field_label = esc_html( $field->label );
			$input_html = "<input type='url' name='{$input_name}' id='{$input_id}' value='{$input_value}' class='large' placeholder='https://' aria-invalid='false'>";
			return "<div class='{$wrapper_class}'><div><div class='otslr-label'><span>{$field_label}</span></div><div class='ginput_container ginput_container_website'>{$input_html}</div></div>";
		}

		$type = ($field_type === 'phone') ? 'tel' : 'text';
		$input_html = "<input type='{$type}' name='{$input_name}' id='{$input_id}' value='{$input_value}' class='gform-text-input gfield-choice-input custom-text-field'>";
		return "<div class='{$wrapper_class}'><div>{$input_html}</div></div>";
	}

	return $content;
}

/**
 * Half Width Wrapper
 * 
 */
add_filter( 'gform_field_container', 'custom_wrap_half_width_fields', 17, 6 );
function custom_wrap_half_width_fields( $field_container, $field, $form, $css_class, $style, $field_content ) {
	global $outsellers_gf_form_id;
	if ( is_admin() ) {
		return $field_container;
	}

	static $half_field_index = 0;
	static $current_form_id = null;

	if ( $current_form_id !== $form['id'] ) {
		$half_field_index = 0;
		$current_form_id = $form['id'];
	}

	if ( strpos( $css_class, 'gfield--width-half' ) !== false ) {
		$open_row  = ( $half_field_index % 2 === 0 ) ? "<div class='otslr-gf-row'><div>" : '';
		$close_row = ( $half_field_index % 2 === 1 ) ? '</div></div>' : '';

		$half_field_index++;

		return $open_row .
			"<div class='{$css_class}' id='field_{$form['id']}_{$field->id}' {$style}>{$field_content}</div>" .
			$close_row;
	}

	return "<div class='{$css_class}' id='field_{$form['id']}_{$field->id}' {$style}>{$field_content}</div>";
}

/**
 * 
 * Address
 * 
 */
add_filter( 'gform_field_content', 'custom_address_field_wrapper', 18, 5 );
function custom_address_field_wrapper( $content, $field, $value, $lead_id, $form_id ) {
	global $outsellers_gf_form_id;
	if ( is_admin() ) {
		return $content;
	}

	if ( $form_id != $outsellers_gf_form_id ) {
		return $content;
	}
	
	if ( $field->type === 'address' ) {
		return "<div class='otslr-address-wrapper'><div>{$content}</div></div>";
	}

	return $content;
}

/**
 * 
 * Calculation
 */
add_filter( 'gform_field_content', 'custom_calculated_product_wrapper', 19, 5 );
function custom_calculated_product_wrapper( $content, $field, $value, $lead_id, $form_id ) {
	global $outsellers_gf_form_id;
	if ( is_admin() ) {
		return $content;
	}

	if ( $form_id != $outsellers_gf_form_id ) {
		return $content;
	}
	


	$field_type = $field->type;

	if ( $field_type === 'product' && $field->inputType === 'calculation' ) {
		// Remove the quantity input
		$content = preg_replace(
			'/<input[^>]+class="[^"]*ginput_quantity[^"]*"[^>]*>/i',
			'',
			$content
		);

		return "<div class='otslr-product-calculation'>{$content}</div>";
	}

	return $content;
}

/**
 * Submit
 */
add_filter( 'gform_field_content', 'custom_submit_field_wrapper', 20, 5 );
function custom_submit_field_wrapper( $content, $field, $value, $lead_id, $form_id ) {
	global $outsellers_gf_form_id;
	if ( is_admin() ) {
		return $content;
	}

	if ( $form_id != $outsellers_gf_form_id ) {
		return $content;
	}

	$field_type  = $field->type;
	$input_id    = "input_{$form_id}_{$field->id}";
	$input_name  = "input_{$field->id}";
	$input_value = esc_attr( $value );

	$wrapper_classes = [
		'submit'     => 'otslr-submit-wrapper',
	];

	if ( array_key_exists( $field_type, $wrapper_classes ) ) {
		$wrapper_class = $wrapper_classes[ $field_type ];

		if ( $field_type === 'submit' ) {
			$input_html = "<input type='submit' name='{$input_name}' id='{$input_id}' value='{$input_value}' class='gform-text-input gfield-choice-input custom-text-field'>";
		return "<div class='{$wrapper_class}'><div>{$input_html}</div></div>";;
		}

		
	}

	return $content;
}


