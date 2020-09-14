jQuery( document ).ready( function( $ ) {

	$( '.hb-sync-errors-msg .notice-dismiss' ).click( function() {
		if ( confirm( hb_text.confirm_delete_sync_errors ) ) {
			hb_resa_ajax({
				data: {
					'action': 'hb_delete_sync_errors',
					'nonce': $( '#hb_nonce_update_db' ).val()
				},
				success: function( ajax_return ) {},
				error: function( jqXHR, textStatus, errorThrown ) {
					alert( textStatus + ' (' + errorThrown + ')' );
				}
			});
		}
	});

	$( '.hb-input-date' ).datepick( hb_datepicker_calendar_options );

	$( '.hb-input-date' ).datepick( 'option', {
		dateFormat: 'yyyy-mm-dd',
		onSelect: function() {
			$( this ).change();
		}
	});


	$( '#hb-block-accom-from-date' ).change( function () {
		var from_date = $( this ).datepick( 'getDate' )[0],
			to_date = $( '#hb-block-accom-to-date' ).datepick( 'getDate' )[0];
		if ( from_date && to_date && ( from_date.getTime() >= to_date.getTime() ) ) {
			$( '#hb-block-accom-to-date' ).datepick( 'setDate', null );
		}
		if ( from_date ) {
			var min_to_date = new Date( from_date.getTime() );
			min_to_date.setDate( min_to_date.getDate() + 1 );
			$( '#hb-block-accom-to-date' ).datepick( 'option', 'minDate', min_to_date );
		}
	});

	var customer_id_last_valid_selection = null;
	$( '.wrap' ).on( 'change', '.hb-customer-id-list', function( e ) {
		if ( $( this ).val() && $( this ).val().length > 1 ) {
			$( this ).val( customer_id_last_valid_selection );
		} else {
			customer_id_last_valid_selection = $( this ).val();
		}
	});

	hb_section_toggle( 'block-accom' );
	hb_section_toggle( 'admin-add-resa' );
	hb_section_toggle( 'archived-resa' );
	hb_section_toggle( 'export-resa' );

	$( window ).resize();
	setTimeout( function() {
		$( '#hb-admin-add-resa' ).css( 'display', 'none' );
	}, 2000 );

	$( '#hb-select-blocked-accom-type' ).change( function() {
		var blocked_accom_num_options = '';
		if ( accoms[ $( '#hb-select-blocked-accom-type' ).val() ] && accoms[ $( '#hb-select-blocked-accom-type' ).val() ].number > 1 ) {
			blocked_accom_num_options += '<option value="all">' + hb_text.all + '</option>';
			$( '#hb-select-blocked-accom-num' ).parent().show();
		} else {
			$( '#hb-select-blocked-accom-num' ).parent().hide();
		}
		if ( accoms[ $( '#hb-select-blocked-accom-type' ).val() ] ) {
			$.each( accoms[ $( '#hb-select-blocked-accom-type' ).val() ].num_name, function( accom_num_id, accom_num_name ) {
				blocked_accom_num_options += '<option value="' +  accom_num_id + '">' + accom_num_name + '</option>';
			});
		}
		$( '#hb-select-blocked-accom-num' ).html( blocked_accom_num_options );
	}).change();

	$( '.wrap' ).on( 'change', '.hb-filter-accom-list', function() {
		if ( $( '#hb-show-unavailable-accom' ).is( ':checked' ) && $( '#hb-show-people-unsuitable-accom' ).is( ':checked' ) ) {
			$( '.hb-accom-list .hb-accom' ).slideDown();
		} else if ( ! $( '#hb-show-unavailable-accom' ).is( ':checked' ) && ! $( '#hb-show-people-unsuitable-accom' ).is( ':checked' ) ) {
			$( '.hb-accom-list .hb-accom:not(.hb-accom-available)' ).slideUp();
			$( '.hb-accom-list .hb-accom:not(.hb-accom-suits-people)' ).slideUp();
		} else if ( $( '#hb-show-unavailable-accom' ).is( ':checked' ) ) {
			$( '.hb-accom-list .hb-accom.hb-accom-suits-people:not(.hb-accom-available)' ).slideDown();
			$( '.hb-accom-list .hb-accom:not(.hb-accom-suits-people)' ).slideUp();
		} else if ( $( '#hb-show-people-unsuitable-accom' ).is( ':checked' ) ) {
			$( '.hb-accom-list .hb-accom.hb-accom-available:not(.hb-accom-suits-people)' ).slideDown();
			$( '.hb-accom-list .hb-accom:not(.hb-accom-available)' ).slideUp();
		}
	});

	function hb_resa_ajax( ajax_param ) {
		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: ajax_param['data'],
			timeout: hb_ajax_settings.timeout,
			success: function( ajax_return ) {
				ajax_return = ajax_return.trim();
				ajax_param['success']( ajax_return );
			},
			error: ajax_param['error']
		});
	}

	function format_price( price ) {
		if ( hb_price_precision == 'no_decimals' ) {
			var formatted_price = Math.round( price );
		} else {
			var formatted_price = parseFloat( price ).toFixed( 2 );
		}
		return formatted_price;
	}

	function hb_calculate_admin_options_price( resa_id ) {
		var $options_wrapper = $( '.hb-options-editor-resa-' + resa_id );
		var accom_id = $options_wrapper.data( 'accom-id' );
		var options_price = 0;

		$options_wrapper.find( '.hb-option' ).each( function() {
			if ( $( this ).hasClass( 'hb-option-accom-' + accom_id ) ) {
				if ( $( this ).hasClass( 'hb-quantity-option' ) ) {
					if ( $( this ).find( 'input' ).val() < 0 ) {
		 				$( this ).find( 'input' ).val( 0 );
					}
					options_price += parseFloat( $( this ).find( 'input' ).data( 'price' ) * $( this ).find( 'input' ).val() );
				} else if ( $( this ).hasClass( 'hb-multiple-option' ) && $( this ).find( 'input:checked' ).length ) {
					options_price += parseFloat( $( this ).find( 'input:checked' ).data( 'price' ) );
				} else if ( $( this ).hasClass( 'hb-single-option' ) && $( this ).find( 'input' ).is(':checked' ) ) {
					options_price += parseFloat( $( this ).find( 'input' ).data( 'price' ) );
				}
			}
		});

		$options_wrapper.find( '.hb-options-price-raw' ).val( options_price );
		if ( options_price != 0 ) {
			options_price = format_price( options_price );
			if ( options_price < 0 ) {
				options_price = format_price( options_price * -1 );
				$options_wrapper.find( '.hb-options-total-price .hb-price-placeholder-minus' ).css( 'display', 'inline' );
			} else {
				$options_wrapper.find( '.hb-options-total-price .hb-price-placeholder-minus' ).css( 'display', 'none' );
			}
			$options_wrapper.find( '.hb-options-total-price .hb-price-placeholder' ).html( options_price );
			$options_wrapper.find( '.hb-options-total-price' ).show();
		} else {
			$options_wrapper.find( '.hb-options-total-price' ).hide();
		}
	}

	$( 'input[name="hb-admin-customer-type"]' ).change( function() {
		if ( $( 'input[name="hb-admin-customer-type"]:checked' ).val() == 'id' ) {
			$( '#hb-resa-customer-details' ).slideUp();
			$( '#hb-resa-customer-id' ).slideDown();
		} else {
			$( '#hb-resa-customer-details' ).slideDown();
			$( '#hb-resa-customer-id' ).slideUp();
		}
	});

	$( '.wrap' ).on( 'click', '.hb-option', function() {
		hb_calculate_admin_options_price( $( this ).parents( '.hb-options-editor' ).data( 'resa-id' ) );
	});

	$( '.wrap' ).on( 'click', '.hb-resa-more-info-toggle', function() {
		$( this ).parent().find( '.hb-resa-more-info-content' ).slideToggle( 100 );
		$( this ).toggleClass( 'hb-less-info-toggle-link' );
		return false;
	});

	$( '.wrap' ).on( 'click', 'a:not(.hb-resa-follow-link):not(.hb-resa-customer-resa-link):not(.hb-open-document):not(.hb-origin-url-link)', function( e ) {
		$( this ).blur();
		e.preventDefault();
	});

	var displayed_accoms = accoms;

	$( '#hb-resa-cal-accommodation' ).change( function() {
		var accom_selected = $( this ).val();
		if ( accom_selected == 'all' ) {
			displayed_accoms = accoms;
		} else {
			displayed_accoms = {};
			displayed_accoms[ accom_selected ] = accoms[ accom_selected ];
		}
		hb_resa_cal_tables( $( '#hb-resa-cal-table' ).data( 'first-day'), displayed_accoms );
		resaViewModel.redraw_calendar();
	});

	$( '#hb-resa-cal-wrapper' ).on( 'click', '.hb-go-to-previous-two-weeks, .hb-go-to-next-two-weeks', function() {
		hb_resa_cal_tables( $( this ).data( 'day' ), displayed_accoms );
		resaViewModel.redraw_calendar();
		return false;
	});

	$( 'body' ).on( 'click', '.hb-month.button', function() {
		$( this ).parents( '.hb-month-picker').slideUp();
		hb_resa_cal_tables( $( this ).data( 'day' ), displayed_accoms );
		resaViewModel.redraw_calendar();
		return false;
	});

	function Resa( id, status, price, previous_price, paid, accom_price, old_currency, check_in, check_out, adults, children, accom_id, accom_num, options_info, non_editable_info, admin_comment, accom_discount_amount, accom_discount_amount_type, global_discount_amount, global_discount_amount_type, customer_id, received_on, origin, origin_url, additional_info, lang, max_refundable, view_model ) {
		this.id = id;
		this.price = ko.observable( price );
		this.price_tmp = ko.observable();
		this.previous_price = ko.observable( previous_price );
		this.paid = ko.observable( paid );
		this.paid_tmp = ko.observable();
		this.accom_price = accom_price;
		this.accom_discount_amount = ko.observable( accom_discount_amount );
		if ( ! accom_discount_amount_type ) {
			accom_discount_amount_type = 'fixed';
		}
		this.accom_discount_amount_type = ko.observable( accom_discount_amount_type );
		this.global_discount_amount = ko.observable( global_discount_amount );
		if ( ! global_discount_amount_type ) {
			global_discount_amount_type = 'fixed';
		}
		this.global_discount_amount_type = ko.observable( global_discount_amount_type );
		this.accom_discount_amount_tmp = ko.observable();
		this.accom_discount_amount_type_tmp = ko.observable();
		this.global_discount_amount_tmp = ko.observable();
		this.global_discount_amount_type_tmp = ko.observable();
		this.charge_amount = ko.observable( max_charge( price, paid ) );
		this.refund_amount = ko.observable( format_price( max_refundable ) );
		this.max_refundable = ko.observable( max_refundable );
		this.old_currency = old_currency;
		this.check_in = ko.observable( check_in );
		this.check_out = ko.observable( check_out );
		this.check_in_tmp = ko.observable();
		this.check_out_tmp = ko.observable();
		this.adults = ko.observable( adults );
		this.children = ko.observable( children );
		this.adults_tmp = ko.observable();
		this.children_tmp = ko.observable();
		this.additional_info = ko.observable( additional_info );
		this.origin = origin;
		this.origin_url = origin_url;
		this.accom_id = ko.observable( accom_id );
		this.accom_num = ko.observable( accom_num );
		this.avai_accom_same_dates = ko.observableArray();
		this.customer_id = ko.observable( customer_id );
		this.select_customer_id = ko.observable();
		this.received_on = received_on;
		this.options_info = ko.observable( options_info );
		this.non_editable_info = non_editable_info;
		this.lang = ko.observable( lang );
		this.lang_tmp = ko.observable();
		this.admin_comment = ko.observable( admin_comment );
		this.admin_comment_tmp = ko.observable( '' );
		this.status = ko.observable( status );
		this.email_customer_template = ko.observable();
		this.email_customer_to_address = ko.observable();
		this.email_customer_subject = ko.observable();
		this.email_customer_message = ko.observable();
		this.email_customer_attachment_ids = ko.observable();
		this.email_customer_delete_attachments_after = ko.observable( false );
		this.action_processing = ko.observable( false );
		this.email_sent = ko.observable( false );
		this.deleting_anim = ko.observable( false );
		this.editing_resa_info = ko.observable( false );
		this.editing_options = ko.observable( false );
		this.options_editor = ko.observable( '' );
		this.editing_comment = ko.observable( false );
		this.editing_accom = ko.observable( false );
		this.fetching_accom = ko.observable( false );
		this.editing_accom_no_accom = ko.observable( false );
		this.creating_customer = ko.observable( false );
		this.selecting_customer = ko.observable( false );
		this.editing_customer = ko.observable( false );
		this.saving_accom = ko.observable( false );
		this.saving_resa_info = ko.observable( false );
		this.saving_options = ko.observable( false );
		this.saving_comment = ko.observable( false );
		this.saving_customer = ko.observable( false );
		this.saving_selected_customer = ko.observable( false );
		this.editing_price = ko.observable( false );
		this.saving_price = ko.observable( false );
		this.editing_paid = ko.observable( false );
		this.saving_paid = ko.observable( false );
		this.editing_discount = ko.observable( false );
		this.saving_discount = ko.observable( false );
		this.marking_paid = ko.observable( false );
		this.editing_charge = ko.observable( false );
		this.charging = ko.observable( false );
		this.editing_refund = ko.observable( false );
		this.refunding = ko.observable( false );
		this.editing_dates = ko.observable( false );
		this.saving_dates = ko.observable( false );
		this.preparing_email = ko.observable( false );
		this.opening_documents = ko.observable( false );
		this.anim_class = ko.observable( '' );

		var self = this;

		this.status_markup = ko.computed( function() {
			return '<div class="hb-resa-status hb-resa-' + self.status() + '" title="' + hb_text[ self.status() ] + '">' + hb_text[ self.status() ] + '</div>';
		});

		this.past = ko.computed( function() {
			var today = hb_date_to_str( new Date() );
			if ( self.check_out() < today ) {
				return true;
			} else {
				return false;
			}
		});

		this.price_with_security_bond = ko.computed( function() {
			if ( hb_paid_security_bond == 'yes' ) {
				return parseFloat( self.price() ) + parseFloat( hb_security_bond );
			} else {
				return self.price();
			}
		});

		this.price_markup = ko.computed( function() {
			var price = '';
			if ( self.old_currency ) {
				price = format_price( self.price() ) + ' ' + old_currency;
			} else {
				price = '<span title="' + hb_text.price + '">';
				price += hb_format_price( self.price() );
				price += '</span>';
				if ( ( hb_paid_security_bond == 'yes' ) && ( ! self.past() ) ) {
					price += '<br/>';
					price += '<span title="' + hb_text.price_with_bond + '" class="hb-amount-with-security-bond">';
					price += hb_format_price( self.price_with_security_bond() );
					price += '</span>';
				}
				if ( ( self.previous_price() > 0 ) && ( self.previous_price() != self.price() ) ) {
					price += '<br/>';
					price += '<span title="' + hb_text.previous_price + '" class="hb-previous-price">';
					price += hb_format_price( self.previous_price() );
					price += '</span>';
				}
			}
			return price;
		});

		this.discount_markup = ko.computed( function() {
			if ( self.accom_price == -1 ) {
				return '';
			}
			var discount = '';
			if ( self.accom_discount_amount_type() && self.accom_discount_amount() && self.accom_discount_amount() != 0 ) {
				discount += hb_text.accom_discount + ' ';
				if ( self.accom_discount_amount_type() == 'percent' ) {
					discount += self.accom_discount_amount() + '%';
				} else if ( self.accom_discount_amount_type() == 'fixed' ) {
					discount += hb_format_price( self.accom_discount_amount() );
				}
				discount += '<br/>';
			}
			if ( self.global_discount_amount_type() && self.global_discount_amount() && self.global_discount_amount() != 0 ) {
				discount += hb_text.global_discount + ' ';
				if ( self.global_discount_amount_type() == 'percent' ) {
					discount += self.global_discount_amount() + '%';
				} else if ( self.global_discount_amount_type() == 'fixed' ) {
					discount += hb_format_price( self.global_discount_amount() );
				}
			}
			return discount;
		});

		this.payment_status = ko.computed( function() {
			var payment_status = '';
			if ( self.paid() != -1 ) {
				if ( self.price() == 0 ) {
					payment_status = '<div class="hb-payment-status hb-resa-paid" title="' + hb_text['paid'] + '">' + hb_text['paid'] + '</div>';
				} else if ( self.paid() == 0 ) {
					payment_status = '<div class="hb-payment-status hb-resa-unpaid" title="' + hb_text['unpaid'] + '">' + hb_text['unpaid'] + '</div>';
				} else if ( hb_paid_security_bond == 'yes' ) {
					if ( parseFloat( self.paid() ) < parseFloat( self.price() ) ) {
						payment_status = '<div class="hb-payment-status hb-resa-not-fully-paid" title="' + hb_text['not_fully_paid'] + '">' + hb_text['not_fully_paid'] + '</div>';
					} else if (
						( parseFloat( self.paid() ) < parseFloat( self.price_with_security_bond() ) ) &&
						( hb_paid_security_bond == 'yes' ) &&
						( ! self.past() )
					) {
						payment_status = '<div class="hb-payment-status hb-resa-not-fully-paid" title="' + hb_text['bond_not_paid'] + '">' + hb_text['bond_not_paid'] + '</div>';
					} else {
						payment_status = '<div class="hb-payment-status hb-resa-paid" title="' + hb_text['paid'] + '">' + hb_text['paid'] + '</div>';
					}
				} else {
					if ( parseFloat( self.paid() ) >= parseFloat( self.price() ) ) {
						payment_status = '<div class="hb-payment-status hb-resa-paid" title="' + hb_text['paid'] + '">' + hb_text['paid'] + '</div>';
					} else {
						payment_status = '<div class="hb-payment-status hb-resa-not-fully-paid" title="' + hb_text['not_fully_paid'] + '">' + hb_text['not_fully_paid'] + '</div>';
					}
				}
			}
			return payment_status;
		});

		this.price_paid_details = ko.computed( function() {
			if ( self.paid() == 0 ) {
				return '';
			}
			if ( ( hb_paid_security_bond == 'no' ) && ( self.paid() == self.price() ) ) {
				return '';
			}
			if ( hb_paid_security_bond == 'yes' ) {
				if ( self.past() && ( self.paid() == self.price() ) ) {
					return '';
				}
				if ( ! self.past() && ( self.paid() == self.price_with_security_bond() ) ) {
					return '';
				}
			}
			var paid_details = '';
			paid_details += '<div>';
			paid_details += hb_text['paid_details'] + ' ' + hb_format_price( self.paid() );
			paid_details += '</div>';
			return paid_details;
		});

		this.price_details = ko.computed( function() {
			var price = '';
			price += self.price_paid_details();
			if ( self.past() ) {
				if ( parseFloat( self.paid() ) < parseFloat( self.price() ) ) {
					price += '<div>';
					price += hb_text['to_be_paid_details'];
					price += '<span class="hb-amount-due">';
					price += ' ' + hb_format_price( self.price() - self.paid() );
					price += '</span>';
					price += '</div>';
				}
				if ( parseFloat( self.paid() ) > parseFloat( self.price() ) ) {
					price += '<div>';
					price += hb_text['to_refund'];
					price += '<span class="hb-amount-due">';
					price += hb_format_price( self.paid() - self.price() );
					price += '</span>';
					price += '</div>';
				}
			} else {
				if (
					(
						( hb_paid_security_bond == 'no' ) &&
						( parseFloat( self.paid() ) < parseFloat( self.price() ) )
					) ||
					(
						( hb_paid_security_bond == 'yes' ) &&
						( parseFloat( self.paid() ) < parseFloat( self.price_with_security_bond() ) )
					)
				) {
					price += '<div>';
					price += hb_text['to_be_paid_details'] + ' ';
					if ( hb_paid_security_bond == 'no' ) {
						price += hb_format_price( self.price() - self.paid() );
					} else {
						price += hb_format_price( self.price_with_security_bond() - self.paid() );
					}
					price += '</div>';
				}
			}
			return price;
		})

		this.mark_paid_visible = ko.computed( function() {
			if ( self.marking_paid() ) {
				return false;
			}
			if (
				( hb_paid_security_bond == 'yes' ) &&
				( ! self.past() ) &&
				( parseFloat( self.paid() ) < parseFloat( self.price_with_security_bond() ) )
			) {
				return true;
			}
			if ( ( parseFloat( self.paid() ) < parseFloat( self.price() ) ) ) {
				return true;
			}
			return false;
		});

		this.charge_action_visible = ko.computed( function() {
			if ( hb_stripe_active != 'yes' ) {
				return false;
			}
			if ( ! self.customer_id() || self.customer_id() == '0' ) {
				return false;
			}
			customer = ko.utils.arrayFirst( view_model.customers_list(), function( customer ) {
				return self.customer_id() == customer.id;
			});
			if ( ! customer || ! customer.payment_id ) {
				return false;
			}
			if ( self.editing_charge() ) {
				return false;
			}
			return parseFloat( self.price() ) + parseFloat( hb_security_bond ) > parseFloat( self.paid() );
		});

		this.refund_action_visible = ko.computed( function() {
			if ( hb_stripe_active != 'yes' ) {
				return false;
			}
			if ( self.editing_refund() ) {
				return false;
			}
			return self.max_refundable() > 0;
		});

		this.nb_nights = ko.computed( function() {
			return hb_nb_days( self.check_in(), self.check_out() );
		});

		this.nb_nights_tmp = ko.computed( function() {
			if ( ! self.editing_dates() ||
				! self.check_in_tmp() ||
				! self.check_out_tmp() ||
				! hb_valid_date( self.check_in_tmp() ) ||
				! hb_valid_date( self.check_out_tmp() )
			) {
				return '';
			} else {
				return hb_nb_days( self.check_in_tmp(), self.check_out_tmp() );
			}
		});

		this.accom = ko.computed( function() {
			if ( self.accom_id() in accoms ) {
				var accom_txt = accoms[ self.accom_id() ].name;
				if ( self.accom_num() == 0 ) {
					accom_txt += ' <small>' + hb_text['not_allocated'] + '</small>';
				} else if ( accoms[ self.accom_id() ].num_name[ self.accom_num() ] ) {
					accom_txt += ' <small>(' + accoms[ self.accom_id() ].num_name[ self.accom_num() ] + ')</small>';
				}
				return accom_txt;
			} else {
				return '';
			}
		});

		this.accom_editor = ko.computed( function() {
			if ( ! self.editing_accom() ) {
				return '';
			}
			var accom_editor_html = '',
				avai_accom = self.avai_accom_same_dates();
			for ( var i = 0; i < avai_accom.length; i++ ) {
				for ( var j = 0; j < avai_accom[i].accom_num.length; j++ ) {
					var accom_id = avai_accom[i].accom_id,
						accom_num = avai_accom[i].accom_num[j],
						input_id = 'hb-accom-change-' + accom_id + '-' + accom_num;
					accom_editor_html += '<div class="hb-accom-editor-radio">';
					accom_editor_html += '<input type="radio" id="' + input_id + '" name="hb-accom-change"';
					accom_editor_html += ' data-accom-id="' + accom_id + '" data-accom-num="' + accom_num + '" />';
					accom_editor_html += '<label for="' + input_id + '">';
					accom_editor_html += accoms[ accom_id ].name + ' (' + accoms[ accom_id ].num_name[ accom_num ] + ')';
					accom_editor_html += '</label>';
					accom_editor_html += '</div>';
				}
			}
			return accom_editor_html;
		});

		this.resa_info_html = ko.computed( function() {
			var markup = '',
				additional_info = '',
				lang_info = '';

			markup += '<b>' + hb_text.info_adults + '</b> ' + self.adults() + '<br/>';
			if ( self.children() ) {
				markup += '<b>' + hb_text.info_children + '</b> ' + self.children() + '<br/>';
			}

			try {
				additional_info_data = JSON.parse( self.additional_info() );
			} catch ( e ) {
				additional_info_data = {};
			}

			$.each( additional_info_data, function( info_id, info_value ) {
				if ( info_value != '' ) {
					if ( hb_additional_info_fields[ info_id ] ) {
						additional_info += '<b>' + hb_additional_info_fields[ info_id ]['name'] + ':</b> ';
						if ( hb_additional_info_fields[ info_id ]['type'] == 'textarea' ) {
							additional_info += '<br/>';
						}
					}
					additional_info += info_value.replace( /(?:\r\n|\r|\n)/g, '<br/>' ) + '<br/>';
				}
			});

			if ( hb_multi_lang_site == 'yes' ) {
				lang_info = '<b>' + hb_text.resa_lang + '</b><br/>';
				if ( self.lang() in hb_langs ) {
					lang_info += hb_langs[ self.lang() ];
				} else {
					lang_info += self.lang();
				}
				lang_info += '<br/>';
			}

			if ( additional_info || lang_info ) {
				markup += '<a href="#" class="hb-resa-more-info-toggle">';
				markup += '<span class="hb-more-info-link">' + hb_text.more_info + '</span>';
				markup += '<span class="hb-less-info-link">' + hb_text.less_info + '</span>';
				markup += '</a>';
				markup += '<div class="hb-resa-more-info-content">' + additional_info + lang_info + '</div>';
			}
			return markup;
		});

		this.additional_info_editing_markup = ko.computed( function() {
			var additional_info_edit_markup = '';

			try {
				additional_info_data = JSON.parse( self.additional_info() );
			} catch ( e ) {
				additional_info_data = [];
			}

			if ( ! additional_info_data ) {
				additional_info_data = [];
			}

			$.each( hb_additional_info_fields, function( field_id, field_info ) {
				additional_info_edit_markup += field_info['name'] + '<br/>';
				if ( field_info['type'] == 'textarea' ) {
					additional_info_edit_markup += '<textarea ';
					additional_info_edit_markup += 'rows="2" ';
					additional_info_edit_markup += 'class="hb-textarea-edit-resa hb-input-additional-info-resa-' + self.id + '" ';
					additional_info_edit_markup += 'data-id="' + field_id + '" ';
					additional_info_edit_markup += '>';
					if ( additional_info_data[ field_id ] ) {
						additional_info_edit_markup += additional_info_data[ field_id ];
					}
					additional_info_edit_markup += '</textarea>';
				} else {
					additional_info_edit_markup += '<input ';
					additional_info_edit_markup += 'class="hb-input-edit-resa hb-input-additional-info-resa-' + self.id + '" ';
					additional_info_edit_markup += 'type="text" ';
					if ( additional_info_data[ field_id ] ) {
						additional_info_edit_markup += 'value="' +  additional_info_data[ field_id ] + '" ';
					}
					additional_info_edit_markup += 'data-id="' + field_id + '" ';
					additional_info_edit_markup += '/>';
				}
			});

			return additional_info_edit_markup;
		});

		this.options_editor_class = ko.computed( function() {
			return 'hb-options-editor hb-options-editor-resa-' + self.id;
		});

		this.admin_comment_html = ko.computed( function() {
			return self.admin_comment().replace( /(?:\r\n|\r|\n)/g, '<br/>' );
		});

		this.customer_info_markup = ko.computed( function() {
			var customer_info_markup = '',
				customer_more_info_markup = '',
				customer,
				customer_data_json,
				customer_data,
				nb_data = 0;

			if ( ! self.customer_id() || self.customer_id() == '0' ) {
				return '';
			}

			customer = ko.utils.arrayFirst( view_model.customers_list(), function( customer ) {
				return self.customer_id() == customer.id;
			});

			if ( ! customer ) {
				return '';
			}

			customer_data = customer.customer_data();
			if ( ! customer_data ) {
				return customer.info();
			}

			$.each( customer_data, function( info_id, info_value ) {
				if ( info_value != '' ) {
					nb_data++;
					var info_markup = '';
					if ( hb_customer_fields[ info_id ] ) {
						if ( ['first_name', 'last_name', 'email'].indexOf( info_id ) != -1 ) {
							info_markup += '<b>' + hb_text[ info_id ] + ':</b> ';
						} else {
							info_markup += '<b>' + hb_customer_fields[ info_id ]['name'] + ':</b> ';
						}
						if ( hb_customer_fields[ info_id ]['type'] == 'textarea' ) {
							info_markup += '<br/>';
						}
					}
					info_markup += info_value.replace( /(?:\r\n|\r|\n)/g, '<br/>' ) + '<br/>';
					if ( nb_data <= 2 ) {
						customer_info_markup += info_markup;
					} else {
						customer_more_info_markup += info_markup;
					}
				}
			});

			customer_info_markup = '<b>' + hb_text.customer_id + '</b> ' + self.customer_id() + '<br/>' + customer_info_markup;
			if ( customer_more_info_markup != '' ) {
				customer_info_markup += '<a href="#" class="hb-resa-more-info-toggle">';
				customer_info_markup += '<span class="hb-more-info-link">' + hb_text.more_info + '</span>';
				customer_info_markup += '<span class="hb-less-info-link">' + hb_text.less_info + '</span>';
				customer_info_markup += '</a>';
				customer_info_markup += '<div class="hb-resa-more-info-content">' + customer_more_info_markup + '</div>';
			}
			return customer_info_markup;
		});

		this.customer_info_editing_markup = ko.computed( function() {
			var customer_edit_markup = '',
				customer_data = [];

			if ( self.customer_id() != 0 ) {
				customer = ko.utils.arrayFirst( view_model.customers_list(), function( customer ) {
					return self.customer_id() == customer.id;
				});
				if ( customer ) {
					customer_data = customer.customer_data();
				}
			}

			$.each( hb_customer_fields, function( field_id, field_info ) {
				if ( ['first_name', 'last_name', 'email'].indexOf( field_id ) != -1 ) {
					customer_edit_markup += hb_text[ field_id ];
				} else {
					customer_edit_markup += field_info['name'];
				}
				customer_edit_markup += '<br/>';
				if ( field_info['type'] == 'textarea' ) {
					customer_edit_markup += '<textarea ';
					customer_edit_markup += 'rows="2" ';
					customer_edit_markup += 'class="hb-textarea-edit-resa hb-input-customer-resa-' + self.id + '" ';
					customer_edit_markup += 'data-id="' + field_id + '" ';
					customer_edit_markup += '>';
					if ( customer_data[ field_id ] ) {
						customer_edit_markup += customer_data[ field_id ];
					}
					customer_edit_markup += '</textarea>';
				} else {
					customer_edit_markup += '<input ';
					customer_edit_markup += 'class="hb-input-edit-resa hb-input-customer-resa-' + self.id + '" ';
					customer_edit_markup += 'type="text" ';
					if ( customer_data[ field_id ] ) {
						customer_edit_markup += 'value="' + customer_data[ field_id ] + '" ';
					}
					customer_edit_markup += 'data-id="' + field_id + '" ';
					customer_edit_markup += '/>';
				}
			});

			return customer_edit_markup;
		});

		this.customer_has_multiple_resa = ko.computed( function() {
			if ( hb_customers[ self.customer_id() ] ) {
				if ( hb_customers[ self.customer_id() ]['nb_resa'] > 1 ) {
					return true;
				} else {
					return false;
				}
			} else {
				return false;
			}
		});

		this.customer_nb_resa = ko.computed( function() {
			if ( hb_customers[ self.customer_id() ] ) {
				return hb_text.customer_resa.replace( '%s', hb_customers[ self.customer_id() ]['nb_resa'] );
			} else {
				return '';
			}
		});

		this.customer_multiple_resa_link = ko.computed( function() {
			if ( hb_customers[ self.customer_id() ] ) {
				return hb_resa_customer_page_url + self.customer_id();
			} else {
				return '';
			}
		});

		this.document_resa_link = ko.computed( function() {
			for ( var i = 0; i < hb_document_templates.length; i++ ) {
				if ( hb_document_templates[i]['lang'] == '' || hb_document_templates[i]['lang'] == self.lang() ) {
					return hb_resa_document_page_url + hb_document_templates[i]['id'] + '&resa_id=' + self.id;
				}
			}
		});

		this.document_resa_title = ko.computed( function() {
			for ( var i = 0; i < hb_document_templates.length; i++ ) {
				if ( hb_document_templates[i]['lang'] == '' || hb_document_templates[i]['lang'] == self.lang() ) {
					return hb_document_templates[i]['name'];
				}
			}
		});

		this.documents_resa_links = ko.computed( function() {
			var doc_links = '';
			for ( var i = 0; i < hb_document_templates.length; i++ ) {
				if ( hb_document_templates[i]['lang'] == '' || hb_document_templates[i]['lang'] == self.lang() ) {
					doc_links += '<a href="';
					doc_links += hb_resa_document_page_url + hb_document_templates[i]['id'] + '&resa_id=' + self.id;
					doc_links += '" class="hb-open-document" target="_blank">'
					doc_links += hb_document_templates[i]['name'];
					doc_links += '</a>';
				}
			}
			return doc_links;
		});

		this.documents_resa_nb = ko.computed( function() {
			var nb_doc = 0;
			for ( var i = 0; i < hb_document_templates.length; i++ ) {
				if ( hb_document_templates[i]['lang'] == '' || hb_document_templates[i]['lang'] == self.lang() ) {
					nb_doc++;
				}
			}
			return nb_doc;
		});

		this.email_customer_attachment_titles = ko.computed( function() {
			if ( self.email_customer_attachment_ids() ) {
				return hb_media_titles[ self.email_customer_attachment_ids() ] + '<br/>';
			} else {
				return '';
			}
		});

		this.status.subscribe( function() {
			resaViewModel.redraw_calendar();
		});

		this.accom_id.subscribe( function() {
			resaViewModel.redraw_calendar();
		});

		this.accom_num.subscribe( function() {
			resaViewModel.redraw_calendar();
		});

		this.check_in.subscribe( function() {
			resaViewModel.redraw_calendar();
		});

		this.check_out.subscribe( function() {
			resaViewModel.redraw_calendar();
		});

		function max_charge( price, paid ) {
			if ( hb_paid_security_bond == 'yes' ) {
				if ( parseFloat( paid ) < parseFloat ( price ) + parseFloat( hb_security_bond ) ) {
					return format_price( parseFloat ( price ) + parseFloat( hb_security_bond ) - paid );
				}
			} else {
				if ( parseFloat( paid ) < parseFloat( price ) ) {
					return format_price( price - paid );
				}
			}
		}

		this.price.subscribe( function( new_price ) {
			self.charge_amount( max_charge( new_price, self.paid() ) );
		});

		this.paid.subscribe( function( new_paid ) {
			self.charge_amount( max_charge( self.price(), new_paid ) );
		});

		this.email_templates_options = ko.computed( function() {
			var email_tmpls_options = [];
			email_tmpls_options.push({
				'id': '',
				'name': hb_text.email_templates_caption
			});
			$.each( hb_email_templates, function( email_tmpl_id, email_tmpl_values ) {
				var email_tmpl_accom = [];
				if ( email_tmpl_values['accom'] ) {
					email_tmpl_accom = email_tmpl_values['accom'].split( ',' );
				}
				if (
					( email_tmpl_values['lang'] == 'all' || email_tmpl_values['lang'] == self.lang() ) &&
					( email_tmpl_values['all_accom'] == 1 || email_tmpl_accom.indexOf( self.accom_id() ) >= 0 )
				) {
					var email_tmpls_option = {
						'id': email_tmpl_id,
						'name': email_tmpl_values['name']
					};
					email_tmpls_options.push( email_tmpls_option );
				}
			});
			return email_tmpls_options;
		});

		this.email_customer_template.subscribe( function( email_template_id ) {
			if ( ! email_template_id ) {
				self.email_customer_to_address( '' );
				self.email_customer_subject( '' );
				self.email_customer_message( '' );
			} else {
				self.email_customer_to_address( hb_email_templates[ email_template_id ]['to_address'] );
				self.email_customer_subject( hb_email_templates[ email_template_id ]['subject'] );
				self.email_customer_message( hb_email_templates[ email_template_id ]['message'] );
			}
		});
	}

	function BlockedAccom( from_date, to_date, accom_id, accom_num, accom_all_num, accom_all_ids, comment, linked_resa_id, is_prepa_time ) {
		this.from_date = ko.observable( from_date );
		this.to_date = ko.observable( to_date );
		this.accom_id = accom_id;
		this.accom_num = accom_num;
		this.accom_all_num = parseInt( accom_all_num );
		this.accom_all_ids = parseInt( accom_all_ids );
		this.comment = comment;
		this.linked_resa_id = linked_resa_id;
		this.is_prepa_time = is_prepa_time;
		this.deleting = ko.observable( false );
		this.anim_class = ko.observable( '' );

		if ( this.accom_all_ids ) {
			this.accom_name_num = hb_text.all;
		} else if ( this.accom_id in accoms ) {
			this.accom_name_num = accoms[ this.accom_id ].name;
			if ( ! this.accom_all_num && accoms[ this.accom_id ].num_name[ this.accom_num ] ) {
				this.accom_name_num += ' (' + accoms[ this.accom_id ].num_name[ this.accom_num ] + ')';
			}
		} else {
			this.accom_name_num = '';
		}

		this.from_date_display = from_date;
		this.to_date_display = to_date;
		if ( from_date == '2016-01-01' || from_date == '0000-00-00' ) {
			this.from_date_display = '';
		}
		if ( to_date == '2029-12-31' || to_date == '0000-00-00' ) {
			this.to_date_display = '';
		}

		this.from_date.subscribe( function() {
			resaViewModel.redraw_calendar();
		});

		this.to_date.subscribe( function() {
			resaViewModel.redraw_calendar();
		});
	}

	function Customer( id, info, payment_id ) {
		this.id = id;
		this.info = ko.observable( info );
		this.payment_id = payment_id;

		var self = this;

		this.customer_data = ko.computed( function() {
			try {
				customer_data = JSON.parse( self.info() );
			} catch ( e ) {
				return [];
			}
			return customer_data;
		});

		this.first_name = ko.computed( function() {
			if ( self.customer_data()['first_name'] ) {
				return self.customer_data()['first_name'];
			} else {
				return '';
			}
		});

		this.last_name = ko.computed( function() {
			if ( self.customer_data()['last_name'] ) {
				return self.customer_data()['last_name'];
			} else {
				return '';
			}
		});

		this.email = ko.computed( function() {
			if ( self.customer_data()['email'] ) {
				return self.customer_data()['email'];
			} else {
				return '';
			}
		});

		this.name_email = ko.computed( function() {
			var name_email_raw = self.first_name() + self.last_name() + self.email();
			return name_email_raw.toLowerCase();
		});
	}

	function ResaViewModel() {

		var self = this;

		this.resa = ko.observableArray();
		this.blocked_accom = ko.observableArray();
		this.customers_list = ko.observableArray();

		this.redraw_calendar = function() {
			hb_set_resa_cal( self.resa(), self.blocked_accom(), self.customers_list(), $( '#hb-resa-cal-table' ).data( 'first-day'), displayed_accoms );
		}

		this.resa.subscribe( function() {
			self.redraw_calendar();
		});

		this.blocked_accom.subscribe( function() {
			self.redraw_calendar();
		});

		this.customers_list.subscribe( function() {
			self.redraw_calendar();
		});

		function change_resa_status( new_status, resa ) {
			hb_resa_ajax({
				data: {
					'action': 'hb_change_resa_status',
					'resa_status': new_status,
					'resa_id': resa.id,
					'nonce': $( '#hb_nonce_update_db' ).val()
				},
				success: function( ajax_return ) {
					resa.action_processing( false );
					if ( ajax_return == 'resa updated' ) {
						if ( new_status == 'cancelled' ) {
							self.blocked_accom.remove( function( blocked_accom ) {
								return blocked_accom.linked_resa_id == resa.id;
							});
						}
						resa.status( new_status );
					} else {
						alert( ajax_return );
					}
				},
				error: function( jqXHR, textStatus, errorThrown ) {
					resa.action_processing( false );
					alert( textStatus + ' (' + errorThrown + ')' );
				}
			});
		}

		this.mark_read_resa = function( resa ) {
			resa.action_processing( true );
			change_resa_status( 'confirmed', resa );
		}

		this.confirm_resa = function( resa ) {
			resa.action_processing( true );
			hb_resa_ajax({
				data: {
					'action': 'hb_confirm_resa',
					'resa_id': resa.id,
					'nonce': $( '#hb_nonce_update_db' ).val()
				},
				success: function( ajax_return ) {
					resa.action_processing( false );
					try {
						var response = JSON.parse( ajax_return );
					} catch ( e ) {
						alert( hb_text['error'] + ' ' + ajax_return );
						return false;
					}
					if ( response['status'] == 'confirmed' ) {
						resa.status( 'confirmed' );
						resa.accom_num( response['accom_num'] );
						for ( var i = 0; i < response['automatic_blocked_accom'].length; i++ ) {
							var new_blocked_accom = new BlockedAccom(
								response['automatic_blocked_accom'][i]['from_date'],
								response['automatic_blocked_accom'][i]['to_date'],
								response['automatic_blocked_accom'][i]['accom_id'],
								response['automatic_blocked_accom'][i]['accom_num'],
								0,
								0,
								response['automatic_blocked_accom'][i]['comment'],
								response['automatic_blocked_accom'][i]['linked_resa_id'],
								response['automatic_blocked_accom'][i]['is_prepa_time']
							);
							self.blocked_accom.unshift( new_blocked_accom );
						}
					} else if ( response['status'] == 'no accom available' ) {
						alert( hb_text['no_accom_available_on_confirm'] );
					} else if ( response['status'] == 'accom num not available' ) {
						alert( hb_text['accom_num_not_available_on_confirm'] );
					} else if ( response['status'] == 'db_error' ) {
						alert( 'Database error.' );
					} else {
						alert( 'Unexpected error.' );
						console.log( ajax_return );
					}
				},
				error: function( jqXHR, textStatus, errorThrown ) {
					resa.action_processing( false );
					alert( textStatus + ' (' + errorThrown + ')' );
				}
			});
		}

		this.cancel_resa = function( resa ) {
			if ( confirm( hb_text.confirm_cancel_resa ) ) {
				resa.action_processing( true );
				change_resa_status( 'cancelled', resa );
			}
		}

		this.edit_resa_info = function( resa ) {
			resa.editing_resa_info( true );
			resa.adults_tmp( resa.adults() );
			resa.children_tmp( resa.children() );
			resa.lang_tmp( resa.lang() );
		}

		this.cancel_edit_resa_info = function( resa ) {
			resa.editing_resa_info( false );
		}

		this.save_resa_info = function( resa ) {
			var additional_info = {};
			resa.saving_resa_info( true );
			$( '.hb-input-additional-info-resa-' + resa.id ).each( function() {
				if ( $( this ).val() != '' ) {
					additional_info[ $( this ).data( 'id' ) ] = $( this ).val();
				}
			});
			additional_info = JSON.stringify( additional_info );
			hb_resa_ajax({
				data: {
					action: 'hb_update_resa_info',
					resa_id: resa.id,
					adults: resa.adults_tmp(),
					children: resa.children_tmp(),
					lang: resa.lang_tmp(),
					additional_info: additional_info,
					nonce: $( '#hb_nonce_update_db' ).val()
				},
				success: function( ajax_return ) {
					resa.saving_resa_info( false );
					resa.editing_resa_info( false );
					try {
						var response = JSON.parse( ajax_return );
					} catch ( e ) {
						alert( hb_text['error'] + ' ' + ajax_return );
						return false;
					}
					if ( response['status']  == 'resa_info_updated' ) {
						resa.adults( resa.adults_tmp() );
						resa.children( resa.children_tmp() );
						resa.lang( resa.lang_tmp() );
						resa.additional_info( additional_info );
						if ( response['new_price'] != -1 ) {
							resa.previous_price( format_price( resa.price() ) );
							resa.price( format_price( response['new_price'] ) );
						}
					} else if ( response['status'] == 'db_error' ) {
						alert( 'Database error.' );
					} else {
						alert( 'Unexpected error.' );
						console.log( ajax_return );
					}
				},
				error: function( jqXHR, textStatus, errorThrown ) {
					resa.saving_resa_info( false );
					alert( textStatus + ' (' + errorThrown + ')' );
				}
			});
		}

		this.edit_customer = function( resa ) {
			resa.editing_customer( true );
		}

		this.cancel_edit_customer = function( resa ) {
			resa.editing_customer( false );
		}

		$( '.wrap' ).on( 'change', '.hb-input-edit-resa', function() {
			var edit_resa_class = $( this ).attr( 'class' );
			edit_resa_class = edit_resa_class.replace( 'hb-input-edit-resa ', '' );
			var edit_resa_data_id = $( this ).data( 'id' );
			var selector = '.' + edit_resa_class + '[data-id="' + edit_resa_data_id + '"]';
			$( selector ).val( $( this ).val() );
		});

		this.save_customer = function( resa ) {
			resa.saving_customer( true );
			var customer_details = {},
				customer_email = '';
			$( '.hb-input-customer-resa-' + resa.id ).each( function() {
				if ( $( this ).val() != '' ) {
					customer_details[ $( this ).data( 'id' ) ] = $( this ).val();
				}
				if ( $( this ).data( 'id' ) == 'email' ) {
					customer_email = $( this ).val();
				}
			});
			customer_details = JSON.stringify( customer_details );
			hb_resa_ajax({
				data: {
					action: 'hb_update_customer',
					customer_id: resa.customer_id(),
					email: customer_email,
					info: customer_details,
					nonce: $( '#hb_nonce_update_db' ).val()
				},
				success: function( ajax_return ) {
					resa.saving_customer( false );
					resa.editing_customer( false );
					if ( ajax_return == 'customer updated' ) {
						customer = ko.utils.arrayFirst( self.customers_list(), function( customer ) {
							return resa.customer_id() == customer.id;
						});
						customer.info( customer_details );
						self.redraw_calendar();
					} else {
						alert( ajax_return );
					}
				},
				error: function( jqXHR, textStatus, errorThrown ) {
					resa.saving_customer( false );
					alert( textStatus + ' (' + errorThrown + ')' );
				}
			});
		}

		this.create_customer = function( resa ) {
			resa.creating_customer( true );
			hb_resa_ajax({
				data: {
					action: 'hb_resa_create_new_customer',
					resa_id: resa.id,
					nonce: $( '#hb_nonce_update_db' ).val()
				},
				success: function( ajax_return ) {
					resa.creating_customer( false );
					try {
						var response = JSON.parse( ajax_return );
					} catch ( e ) {
						alert( ajax_return );
						return;
					}
					if ( response['customer_id'] ) {
						self.customers_list.push( new Customer( response['customer_id'], '', '' ) );
						resa.customer_id( response['customer_id'] );
						resa.editing_customer( true );
					} else {
						alert( ajax_return );
					}
				},
				error: function( jqXHR, textStatus, errorThrown ) {
					resa.creating_customer( false );
					alert( textStatus + ' (' + errorThrown + ')' );
				}
			});
		}

		this.select_customer = function( resa ) {
			resa.selecting_customer( true );
		}

		this.save_selected_customer = function( resa ) {
			resa.saving_selected_customer( true );
			hb_resa_ajax({
				data: {
					action: 'hb_save_selected_customer',
					resa_id: resa.id,
					customer_id: resa.select_customer_id(),
					nonce: $( '#hb_nonce_update_db' ).val()
				},
				success: function( ajax_return ) {
					resa.saving_selected_customer( false );
					try {
						var response = JSON.parse( ajax_return );
					} catch ( e ) {
						alert( ajax_return );
						return;
					}
					resa.customer_id( response['customer_id'] );
					resa.selecting_customer( false );
					self.resa_customers_list_filter( '' );
				},
				error: function( jqXHR, textStatus, errorThrown ) {
					resa.creating_customer( false );
					alert( textStatus + ' (' + errorThrown + ')' );
				}
			});
		};

		this.cancel_select_customer = function( resa ) {
			resa.selecting_customer( false );
		};

		this.edit_accom = function( resa ) {
			resa.fetching_accom( true );
			hb_resa_ajax({
				data: {
					action: 'hb_edit_accom_get_avai',
					check_in: resa.check_in(),
					check_out: resa.check_out(),
					nonce: $( '#hb_nonce_update_db' ).val()
				},
				success: function( ajax_return ) {
					resa.fetching_accom( false );
					try {
						var avai_accom = JSON.parse( ajax_return );
					} catch ( e ) {
						alert( ajax_return );
						return;
					}
					if ( avai_accom.length ) {
						resa.avai_accom_same_dates( avai_accom );
						resa.editing_accom( true );
					} else {
						resa.editing_accom_no_accom( true );
						setTimeout( function() {
							resa.editing_accom_no_accom( false );
						}, 3000 );
					}
				},
				error: function( jqXHR, textStatus, errorThrown ) {
					resa.fetching_accom( false );
					resa.editing_accom( false );
					alert( textStatus + ' (' + errorThrown + ')' );
				}
			});
		}

		this.save_accom = function( resa ) {
			var $selected_input = $( 'input[name="hb-accom-change"]:checked' );
			if ( ! $selected_input.length ) {
				alert( hb_text.accom_not_selected );
			} else {
				var accom_id = $selected_input.data( 'accom-id' ),
					accom_num = $selected_input.data( 'accom-num' );
				resa.editing_accom( false );
				resa.saving_accom( true );
				hb_resa_ajax({
					data: {
						action: 'hb_update_resa_accom',
						check_in: resa.check_in(),
						check_out: resa.check_out(),
						resa_id: resa.id,
						accom_id: accom_id,
						accom_num: accom_num,
						nonce: $( '#hb_nonce_update_db' ).val()
					},
					success: function( ajax_return ) {
						resa.saving_accom( false );
						try {
							var response = JSON.parse( ajax_return );
						} catch ( e ) {
							alert( hb_text['error'] + ' ' + ajax_return );
							return false;
						}
						if ( response['status'] == 'accom_updated' ) {
							resa.accom_id( accom_id );
							resa.accom_num( accom_num );
							self.blocked_accom.remove( function( blocked_accom ) {
								return blocked_accom.linked_resa_id == resa.id;
							});
							for ( var i = 0; i < response['automatic_blocked_accom'].length; i++ ) {
								var new_blocked_accom = new BlockedAccom(
									response['automatic_blocked_accom'][i]['from_date'],
									response['automatic_blocked_accom'][i]['to_date'],
									response['automatic_blocked_accom'][i]['accom_id'],
									response['automatic_blocked_accom'][i]['accom_num'],
									0,
									0,
									response['automatic_blocked_accom'][i]['comment'],
									response['automatic_blocked_accom'][i]['linked_resa_id'],
									response['automatic_blocked_accom'][i]['is_prepa_time']
								);
								self.blocked_accom.unshift( new_blocked_accom );
							}
							if ( resa.accom_price != -1 ) {
								update_discounts_info( resa, response['discounts'] );
							}
							if ( response['new_price'] != -1 ) {
								resa.previous_price( format_price( resa.price() ) );
								resa.price( format_price( response['new_price'] ) );
							}
						} else if ( response['status'] == 'accom_no_longer_available' ) {
							alert( response['msg'] );
						} else if ( response['status'] == 'db_error' ) {
							alert( 'Database error.' );
						} else {
							alert( 'Unexpected error.' );
							console.log( ajax_return );
						}
					},
					error: function( jqXHR, textStatus, errorThrown ) {
						resa.saving_accom( false );
						alert( textStatus + ' (' + errorThrown + ')' );
					}
				});
			}
		}

		this.cancel_edit_accom = function( resa ) {
			resa.editing_accom( false );
		}

		this.edit_dates = function( resa ) {
			resa.check_in_tmp( resa.check_in() );
			resa.check_out_tmp( resa.check_out() );
			$( '.hb-input-edit-resa-dates' ).datepick( hb_datepicker_calendar_options );
			$( '.hb-input-edit-resa-dates' ).datepick( 'option', {
				dateFormat : 'yyyy-mm-dd',
				onSelect: function() {
					jQuery( this ).change();
				}
			});
			$( '.hb-input-edit-resa-check-in' ).change( function () {
				var check_in_date = $( this ).datepick( 'getDate' )[0],
					$check_out_date_input = jQuery( this ).parent().find( '.hb-input-edit-resa-check-out' ),
					check_out_date = $check_out_date_input.datepick( 'getDate' )[0];
				if ( check_in_date && check_out_date && ( check_in_date.getTime() >= check_out_date.getTime() ) ) {
					$check_out_date_input.datepick( 'setDate', null );
				}
				if ( check_in_date ) {
					var min_check_out = new Date( check_in_date.getTime() );
					min_check_out.setDate( min_check_out.getDate() + 1 );
					$check_out_date_input.datepick( 'option', 'minDate', min_check_out );
				}
			}).change();
			resa.editing_dates( true );
		}

		this.cancel_edit_dates = function( resa ) {
			resa.editing_dates( false );
		}

		this.save_dates = function( resa ) {
			if ( ! resa.nb_nights_tmp() ) {
				alert( hb_text.invalid_date );
				return;
			} else if ( resa.nb_nights_tmp() < 1 ) {
				alert( hb_text.check_out_before_check_in );
				return;
			} else if ( resa.check_in_tmp() == resa.check_in() && resa.check_out_tmp() == resa.check_out() ) {
				resa.editing_dates( false );
				return;
			}
			resa.saving_dates( true );
			hb_resa_ajax({
				data: {
					'action': 'hb_update_resa_dates',
					'resa_id': resa.id,
					'new_check_in': resa.check_in_tmp(),
					'new_check_out': resa.check_out_tmp(),
					'nonce': $( '#hb_nonce_update_db' ).val()
				},
				success: function( ajax_return ) {
					resa.saving_dates( false );
					try {
						var response = JSON.parse( ajax_return );
					} catch ( e ) {
						alert( hb_text['error'] + ' ' + ajax_return );
						return false;
					}
					if ( response['status']  == 'resa_dates_modified' ) {
						resa.editing_dates( false );
						resa.check_in( resa.check_in_tmp() );
						resa.check_out( resa.check_out_tmp() );
						self.blocked_accom.remove( function( blocked_accom ) {
							return blocked_accom.linked_resa_id == resa.id;
						});
						for ( var i = 0; i < response['automatic_blocked_accom'].length; i++ ) {
							var new_blocked_accom = new BlockedAccom(
								response['automatic_blocked_accom'][i]['from_date'],
								response['automatic_blocked_accom'][i]['to_date'],
								response['automatic_blocked_accom'][i]['accom_id'],
								response['automatic_blocked_accom'][i]['accom_num'],
								0,
								0,
								response['automatic_blocked_accom'][i]['comment'],
								response['automatic_blocked_accom'][i]['linked_resa_id'],
								response['automatic_blocked_accom'][i]['is_prepa_time']
							);
							self.blocked_accom.unshift( new_blocked_accom );
						}
						if ( resa.accom_price != -1 ) {
							update_discounts_info( resa, response['discounts'] );
						}
						if ( response['new_price'] != -1 ) {
							resa.previous_price( format_price( resa.price() ) );
							resa.price( format_price( response['new_price'] ) );
						}
					} else if ( response['status'] == 'resa_dates_not_modified' ) {
						alert( hb_text.resa_dates_not_modified );
					} else if ( response['status'] == 'db_error' ) {
						alert( 'Database error.' );
					} else {
						alert( 'Unexpected error.' );
						console.log( ajax_return );
					}
				},
				error: function( jqXHR, textStatus, errorThrown ) {
					alert( textStatus + ' (' + errorThrown + ')' );
				}
			});
		}

		function update_discounts_info( resa, discounts ) {
			if ( discounts.accom['amount_type'] ) {
				resa.accom_discount_amount( discounts.accom['amount'] );
				resa.accom_discount_amount_type( discounts.accom['amount_type'] );
			} else {
				resa.accom_discount_amount( '' );
				resa.accom_discount_amount_type( 'fixed' );
			}
			if ( discounts.global['amount_type'] ) {
				resa.global_discount_amount( discounts.global['amount'] );
				resa.global_discount_amount_type( discounts.global['amount_type'] );
			} else {
				resa.global_discount_amount( '' );
				resa.global_discount_amount_type( 'fixed' );
			}
		}

		this.edit_options = function( resa ) {
			resa.editing_options( true );
			resa.options_editor( '<p>' + hb_text.fetching_options_editor + '</p>' );
			hb_resa_ajax({
				data: {
					action: 'hb_edit_options_get_editor',
					resa_id: resa.id,
					nonce: $( '#hb_nonce_update_db' ).val()
				},
				success: function( ajax_return ) {
					resa.options_editor( ajax_return );
					hb_calculate_admin_options_price( resa.id );
				},
				error: function( jqXHR, textStatus, errorThrown ) {
					resa.fetching_accom( false );
					resa.editing_accom( false );
					alert( textStatus + ' (' + errorThrown + ')' );
				}
			});
		}

		this.cancel_edit_options = function( resa ) {
			resa.editing_options( false );
		}

		this.save_options = function( resa ) {
			resa.saving_options( true );
			var $options_form = $( '.hb-options-editor-resa-' + resa.id + ' form' );
			$options_form.append( '<input type="hidden" name="action" value="hb_update_resa_options" />' );
			$options_form.append( '<input type="hidden" name="resa_id" value="' + resa.id + '" />' );
			$options_form.append( '<input type="hidden" name="accom_id" value="' + resa.accom_id() + '" />' );
			$options_form.append( '<input type="hidden" name="nonce" value="' + $( '#hb_nonce_update_db' ).val() + '" />' );
			hb_resa_ajax({
				data: $options_form.serialize(),
				success: function( ajax_return ) {
					resa.editing_options( false );
					resa.saving_options( false );
					try {
						var response = JSON.parse( ajax_return );
					} catch ( e ) {
						alert( hb_text['error'] + ' ' + ajax_return );
						return false;
					}
					if ( response['status']  == 'resa_options_updated' ) {
						resa.options_info( response['options_info'] );
						if ( response['new_price'] != -1 ) {
							resa.previous_price( format_price( resa.price() ) );
							resa.price( format_price( response['new_price'] ) );
						}
					} else if ( response['status'] == 'db_error' ) {
						alert( 'Database error.' );
					} else {
						alert( 'Unexpected error.' );
						console.log( ajax_return );
					}
				},
				error: function( jqXHR, textStatus, errorThrown ) {
					alert( textStatus + ' (' + errorThrown + ')' );
				}
			});
		}

		this.edit_comment = function( resa ) {
			resa.editing_comment( true );
			resa.admin_comment_tmp( resa.admin_comment() );
		}

		this.cancel_edit_comment = function( resa ) {
			resa.editing_comment( false );
		}

		this.save_comment = function( resa ) {
			resa.saving_comment( true );
			hb_resa_ajax({
				data: {
					'action': 'hb_update_resa_comment',
					'resa_comment': resa.admin_comment_tmp(),
					'resa_id': resa.id,
					'nonce': $( '#hb_nonce_update_db' ).val()
				},
				success: function( ajax_return ) {
					resa.editing_comment( false );
					resa.saving_comment( false );
					if ( ajax_return == 'admin comment updated' ) {
						resa.admin_comment( resa.admin_comment_tmp() );
					} else {
						alert( ajax_return );
					}
				},
				error: function( jqXHR, textStatus, errorThrown ) {
					alert( textStatus + ' (' + errorThrown + ')' );
				}
			});
		}

		this.edit_charge = function( resa ) {
			resa.editing_charge( true );
		}

		this.cancel_edit_charge = function( resa ) {
			resa.editing_charge( false );
		}

		this.charge = function( resa ) {
			if ( resa.charge_amount() <= 0 ) {
				alert( hb_text.charge_amount_negative );
				return;
			}
			var charge_max = resa.price() - resa.paid() + parseFloat( hb_security_bond );
			charge_max = parseFloat( charge_max.toFixed( 2 ) );
			if ( parseFloat( resa.charge_amount() ) > charge_max ) {
				alert( hb_text.charge_amount_too_high.replace( '%amount', hb_decode_entities( hb_format_price( charge_max ) ) ) );
				return;
			}
			resa.charging( true );
			hb_resa_ajax({
				data: {
					'action': 'hb_resa_charging',
					'charge_amount': resa.charge_amount(),
					'resa_id': resa.id,
					'nonce': $( '#hb_nonce_update_db' ).val()
				},
				success: function( ajax_return ) {
					resa.editing_charge( false );
					resa.charging( false );
					if ( ajax_return == 'charge_done' ) {
						resa.paid( format_price( parseFloat( resa.paid() ) + parseFloat( resa.charge_amount() ) ) );
						resa.max_refundable( parseFloat( resa.max_refundable() ) + parseFloat( resa.charge_amount() ) );
						resa.refund_amount( format_price( resa.max_refundable() ) );
					} else {
						alert( ajax_return );
					}
				},
				error: function( jqXHR, textStatus, errorThrown ) {
					alert( textStatus + ' (' + errorThrown + ')' );
				}
			});
		};

		this.edit_refund = function( resa ) {
			resa.editing_refund( true );
		}

		this.cancel_edit_refund = function( resa ) {
			resa.editing_refund( false );
		}

		this.refund = function( resa ) {
			if ( resa.refund_amount() <= 0 ) {
				alert( hb_text.refund_amount_negative );
				return;
			}
			if ( parseFloat( resa.refund_amount() ) > parseFloat( resa.max_refundable() ) ) {
				alert( hb_text.refund_amount_too_high.replace( '%amount', hb_decode_entities( hb_format_price( resa.max_refundable() ) ) ) );
				return;
			}
			resa.refunding( true );
			hb_resa_ajax({
				data: {
					'action': 'hb_resa_refunding',
					'refund_amount': resa.refund_amount(),
					'resa_id': resa.id,
					'nonce': $( '#hb_nonce_update_db' ).val()
				},
				success: function( ajax_return ) {
					resa.editing_refund( false );
					resa.refunding( false );
					if ( ajax_return == 'refund_done' ) {
						resa.paid( format_price( parseFloat( resa.paid() ) - parseFloat( resa.refund_amount() ) ) );
						resa.max_refundable( resa.max_refundable() - resa.refund_amount() );
						resa.refund_amount( format_price( resa.max_refundable() ) );
					} else {
						alert( ajax_return );
					}
				},
				error: function( jqXHR, textStatus, errorThrown ) {
					alert( textStatus + ' (' + errorThrown + ')' );
				}
			});
		};

		this.edit_price = function( resa ) {
			resa.editing_price( true );
			resa.price_tmp( format_price( resa.price() ) );
		}

		this.cancel_edit_price = function( resa ) {
			resa.editing_price( false );
		}

		this.save_paid = function( resa ) {
			resa.saving_price( true );
			update_paid( resa, resa.paid_tmp() );
		}

		this.edit_paid = function( resa ) {
			resa.editing_paid( true );
			resa.paid_tmp( format_price( resa.paid() ) );
		}

		this.cancel_edit_paid = function( resa ) {
			resa.editing_paid( false );
		}

		this.save_price = function( resa ) {
			if ( parseFloat( resa.price_tmp() ) != parseFloat( resa.price() ) ) {
				resa.saving_price( true );
				hb_resa_ajax({
					data: {
						'action': 'hb_update_resa_price',
						'new_price': resa.price_tmp(),
						'resa_id': resa.id,
						'nonce': $( '#hb_nonce_update_db' ).val()
					},
					success: function( ajax_return ) {
						resa.editing_price( false );
						resa.saving_price( false );
						try {
							var response = JSON.parse( ajax_return );
						} catch ( e ) {
							alert( hb_text['error'] + ' ' + ajax_return );
							return false;
						}
						if ( response['status']  == 'price_updated' ) {
							resa.previous_price( format_price( resa.price() ) );
							resa.price( format_price( resa.price_tmp() ) );
							if ( response['discount_status'] == 'updated' ) {
								resa.global_discount_amount( response['discount_amount'] );
								resa.global_discount_amount_type( 'fixed' );
							}
						} else if ( response['status'] == 'db_error' ) {
							alert( 'Database error.' );
						} else {
							alert( 'Unexpected error.' );
							console.log( ajax_return );
						}
					},
					error: function( jqXHR, textStatus, errorThrown ) {
						alert( textStatus + ' (' + errorThrown + ')' );
					}
				});
			} else {
				resa.editing_price( false );
			}
		}

		this.mark_paid = function( resa ) {
			resa.marking_paid( true );
			if ( ( hb_paid_security_bond == 'yes' ) && ( ! resa.past() ) ) {
				update_paid( resa, resa.price_with_security_bond() );
			} else {
				update_paid( resa, resa.price() );
			}
		}

		function update_paid( resa, paid ) {
			hb_resa_ajax({
				data: {
					'action': 'hb_update_resa_paid',
					'resa_paid': paid,
					'resa_id': resa.id,
					'nonce': $( '#hb_nonce_update_db' ).val()
				},
				success: function( ajax_return ) {
					resa.editing_paid( false );
					resa.saving_paid( false );
					resa.marking_paid( false );
					if ( ajax_return == 'paid updated' ) {
						resa.paid( format_price( paid ) );
					} else {
						alert( ajax_return );
					}
				},
				error: function( jqXHR, textStatus, errorThrown ) {
					alert( textStatus + ' (' + errorThrown + ')' );
				}
			});
		}

		this.edit_discount = function( resa ) {
			if ( resa.accom_discount_amount() ) {
				if ( resa.accom_discount_amount_type() == 'fixed' ) {
					resa.accom_discount_amount_tmp( format_price( resa.accom_discount_amount() ) );
				} else {
					resa.accom_discount_amount_tmp( resa.accom_discount_amount() );
				}
			} else {
				resa.accom_discount_amount_tmp( '' );
			}
			resa.accom_discount_amount_type_tmp( resa.accom_discount_amount_type() );
			if ( resa.global_discount_amount() ) {
				if ( resa.global_discount_amount_type() == 'fixed' ) {
					resa.global_discount_amount_tmp( format_price( resa.global_discount_amount() ) );
				} else {
					resa.global_discount_amount_tmp( resa.global_discount_amount() );
				}
			} else {
				resa.global_discount_amount_tmp( '' );
			}
			resa.global_discount_amount_type_tmp( resa.global_discount_amount_type() );
			resa.editing_discount( true );
		}

		this.cancel_edit_discount = function( resa ) {
			resa.editing_discount( false );
		}

		this.save_discount = function( resa ) {
			resa.saving_discount( true );
			hb_resa_ajax({
				data: {
					'action': 'hb_update_resa_discount',
					'accom_discount_amount': resa.accom_discount_amount_tmp(),
					'accom_discount_amount_type': resa.accom_discount_amount_type_tmp(),
					'global_discount_amount': resa.global_discount_amount_tmp(),
					'global_discount_amount_type': resa.global_discount_amount_type_tmp(),
					'resa_id': resa.id,
					'nonce': $( '#hb_nonce_update_db' ).val()
				},
				success: function( ajax_return ) {
					resa.saving_discount( false );
					try {
						var response = JSON.parse( ajax_return );
					} catch ( e ) {
						alert( hb_text['error'] + ' ' + ajax_return );
						return false;
					}
					if ( response['status']  == 'discount updated' ) {
						resa.editing_discount( false );
						resa.previous_price( format_price( resa.price() ) );
						resa.price( format_price( response['new_price'] ) );
						resa.accom_discount_amount( resa.accom_discount_amount_tmp() );
						resa.accom_discount_amount_type( resa.accom_discount_amount_type_tmp() );
						resa.global_discount_amount( resa.global_discount_amount_tmp() );
						resa.global_discount_amount_type( resa.global_discount_amount_type_tmp() );
					} else if ( response['status'] == 'db_error' ) {
						alert( 'Database error.' );
					} else {
						alert( 'Unexpected error.' );
						console.log( ajax_return );
					}
				},
				error: function( jqXHR, textStatus, errorThrown ) {
					alert( textStatus + ' (' + errorThrown + ')' );
				}
			});
		}

		this.admin_add_resa = function() {
			for ( var i = 0; i < hb_new_admin_resa['automatic_blocked_accom'].length; i++ ) {
				var new_blocked_accom = new BlockedAccom(
					hb_new_admin_resa['automatic_blocked_accom'][i]['from_date'],
					hb_new_admin_resa['automatic_blocked_accom'][i]['to_date'],
					hb_new_admin_resa['automatic_blocked_accom'][i]['accom_id'],
					hb_new_admin_resa['automatic_blocked_accom'][i]['accom_num'],
					0,
					0,
					hb_new_admin_resa['automatic_blocked_accom'][i]['comment'],
					hb_new_admin_resa['automatic_blocked_accom'][i]['linked_resa_id'],
					hb_new_admin_resa['automatic_blocked_accom'][i]['is_prepa_time']
				);
				self.blocked_accom.unshift( new_blocked_accom );
			}
			var accom_id = $( '.hb-details-accom-id' ).val();
			var accom_num = $( 'input[name="hb-accom-' + accom_id + '-num"]:checked' ).val();
			var admin_comment = $( '#hb-admin-comment' ).val();
			var lang = $( '#hb-resa-admin-lang' ).val();
			$( '#hb-admin-add-resa' ).hide();
			$( '#hb-admin-add-resa-toggle .dashicons-arrow-down' ).css( 'display', 'inline-block' );
			$( '#hb-admin-add-resa-toggle .dashicons-arrow-up' ).hide();
			$( 'html, body' ).animate({ scrollTop: $( '#hb-add-resa-section' ).offset().top - 40 });
			$( '.hb-adults, .hb-children, .hb-accom' ).prop( 'selectedIndex', 0 );
			$( '#hb-add-resa-customer-id-list' ).prop( 'selectedIndex', -1 );
			$( '#hb-resa-admin-lang' ).val( hb_admin_lang );
			$( '.hb-admin-booking-details-form input[type="text"], .hb-admin-booking-details-form textarea' ).val( '' );
			$( '.hb-admin-booking-details-form input[type="checkbox"]' ).removeAttr( 'checked' );
			radios = $.uniqueSort( $( 'input[type="radio"]' ).map( function( i, e ) { return $( e ).attr( 'name' ) } ).get() );
			$( radios ).each( function( i, e ) { $( 'input[name="' + e + '"]:first' ).attr( 'checked','checked' ) });
			$( '#hb-resa-customer-id' ).show();
			self.resa_customers_list_filter( '' );
			self.resa_current_page_number( 1 );
			setTimeout( function() {
				customer = ko.utils.arrayFirst( self.customers_list(), function( customer ) {
					return hb_new_admin_resa.customer.id == customer.id;
				});
				if ( ! customer ) {
					self.customers_list.push(
						new Customer(
							hb_new_admin_resa.customer.id,
							hb_new_admin_resa.customer.info,
							''
						)
					);
				}
				knockout_resa = new Resa(
					hb_new_admin_resa.resa_id,
					hb_new_resa_status,
					hb_new_admin_resa.price,
					0,
					0,
					0,
					'',
					$( '.hb-details-check-in' ).val(),
					$( '.hb-details-check-out' ).val(),
					$( '.hb-details-adults' ).val(),
					$( '.hb-details-children' ).val(),
					accom_id,
					accom_num,
					hb_new_admin_resa.options_info,
					hb_new_admin_resa.non_editable_info,
					admin_comment,
					hb_new_admin_resa.accom_discount_amount,
					hb_new_admin_resa.accom_discount_amount_type,
					hb_new_admin_resa.global_discount_amount,
					hb_new_admin_resa.global_discount_amount_type,
					hb_new_admin_resa.customer.id,
					hb_new_admin_resa.received_on,
					'website',
					'',
					hb_new_admin_resa.additional_info,
					lang,
					0,
					self
				);
				knockout_resa.anim_class( 'hb-resa-added' );
				self.resa.unshift( knockout_resa );
				setTimeout( function() {
					knockout_resa.anim_class( '' );
				}, 300 );
			}, 1000 );
			return false;
		}

		this.email_resa = function( resa ) {
			resa.email_customer_template( '' );
			resa.email_customer_to_address( '' );
			resa.email_customer_subject( '' );
			resa.email_customer_message( '' );
			resa.email_customer_attachment_ids( '' );
			resa.email_customer_delete_attachments_after( false );
			resa.preparing_email( true );
		}

		this.remove_email_attachments = function( resa ) {
			if ( resa && confirm( hb_text.remove_all_attachments ) ) {
				resa.email_customer_attachment_ids( '' );
			}
		}

		this.delete_email_attachments_after_label = function( resa ) {
			if ( resa ) {
				resa.email_customer_delete_attachments_after( ! resa.email_customer_delete_attachments_after() );
			}
		}

		this.cancel_email_resa = function( resa ) {
			resa.preparing_email( false );
		}

		this.send_email_customer = function( resa ) {
			resa.preparing_email( false );
			resa.action_processing( true );
			hb_resa_ajax({
				data: {
					'action': 'hb_send_email_customer',
					'resa_id': resa.id,
					'email_template': resa.email_customer_template(),
					'email_to_address': resa.email_customer_to_address(),
					'email_subject': resa.email_customer_subject(),
					'email_message': resa.email_customer_message(),
					'email_attachments': resa.email_customer_attachment_ids(),
					'delete_attachments': resa.email_customer_delete_attachments_after(),
					'nonce': $( '#hb_nonce_update_db' ).val()
				},
				success: function( ajax_return ) {
					resa.action_processing( false );
					if ( ajax_return == 'email_sent' ) {
						resa.email_sent( true );
						setTimeout( function() {
							resa.email_sent( false );
						}, 4000 );
					} else {
						alert( ajax_return );
					}
				},
				error: function( jqXHR, textStatus, errorThrown ) {
					resa.action_processing( false );
					alert( textStatus + ' (' + errorThrown + ')' )
				}
			});
		}

		this.open_multiple_documents_resa = function( resa ) {
			if ( resa.opening_documents() ) {
				resa.opening_documents( false );
			} else {
				resa.opening_documents( true );
			}
		}

		this.delete_resa = function( resa ) {
			if ( confirm( hb_text.confirm_delete_resa ) ) {
				resa.action_processing( true );
				hb_resa_ajax({
					data: {
						'action': 'hb_delete_resa',
						'resa_id': resa.id,
						'nonce': $( '#hb_nonce_update_db' ).val()
					},
					success: function( ajax_return ) {
						if ( ajax_return == 'resa deleted' ) {
							self.blocked_accom.remove( function( blocked_accom ) {
								return blocked_accom.linked_resa_id == resa.id;
							});
							resa.anim_class( 'hb-resa-deleting' );
							setTimeout( function() {
								self.resa.remove( resa );
							}, 300 );
						} else {
							resa.action_processing( false );
							alert( ajax_return );
						}
					},
					error: function( jqXHR, textStatus, errorThrown ) {
						resa.action_processing( false );
						alert( textStatus + ' (' + errorThrown + ')' )
					}
				});
			}
		}

		this.resa_customers_list_filter = ko.observable( '' );

		this.resa_customers_list = ko.computed( function() {
			customers_id_name_list = [];
			for ( var i = 0; i < self.customers_list().length; i++ ) {
				var customer = {
					id: self.customers_list()[ i ].id,
					id_name: self.customers_list()[ i ].last_name() + ' ' + self.customers_list()[ i ].first_name() + ' (' + hb_text.customer_id_short + ' ' + self.customers_list()[ i ].id + ')'
				}
				customers_id_name_list.push( customer );
			}
			customers_id_name_list.sort( function( a, b ) {
				return a.id_name.localeCompare( b.id_name );
			});
			if ( ! self.resa_customers_list_filter() ) {
				return customers_id_name_list;
			} else {
				var filtered_customers = ko.utils.arrayFilter( customers_id_name_list, function( customer ) {
					if ( customer.id_name.toLowerCase().replace( /\s/g, '' ).indexOf( self.resa_customers_list_filter().toLowerCase().replace( /\s/g, '' ) ) >= 0 ) {
						return true;
					} else {
						return false;
					}
				});
				if ( filtered_customers.length == 1 ) {
					$( '.hb-customer-id-list' ).val( [ filtered_customers[0].id ] );
				}
				return filtered_customers;
			}

		});

		this.resa_filter = ko.observable( 'none' );
		this.resa_filter_customer = ko.observable( '' );
		this.resa_filter_status = ko.observable( 'confirmed' );
		this.resa_filter_accom_id = ko.observable( 'all' );
		this.resa_filter_accom_num = ko.observable( 'all' );
		this.resa_filter_accom_num_name = ko.computed( function() {
			if ( self.resa_filter_accom_id() == 'all' ) {
				return [];
			} else {
				var returned_num_name = [
						{ 'num': 'all', 'name': hb_text.all }
					];
				$.each( accoms[ self.resa_filter_accom_id() ].num_name, function( accom_num_id, accom_num_name ) {
					returned_num_name.push({ 'num': accom_num_id, 'name': accom_num_name });
				});
				return returned_num_name;
			}
		});
		this.resa_filter_check_in_from = ko.observable( hb_date_to_str( new Date() ) );
		this.resa_filter_check_in_to = ko.observable( hb_date_to_str( new Date() ) );
		this.resa_filter_check_out_from = ko.observable( hb_date_to_str( new Date() ) );
		this.resa_filter_check_out_to = ko.observable( hb_date_to_str( new Date() ) );
		this.resa_filter_check_in_out_from = ko.observable( hb_date_to_str( new Date() ) );
		this.resa_filter_check_in_out_to = ko.observable( hb_date_to_str( new Date() ) );
		this.resa_filter_active_resa_from = ko.observable( hb_date_to_str( new Date() ) );
		this.resa_filter_active_resa_to = ko.observable( hb_date_to_str( new Date() ) );

		$( '.hb-filter-clear-date' ).click( function() {
			$( this ).prev().val( '' ).change();
		});

		$( '.hb-filter-date-from' ).change( function () {
			var from = $( this ).val(),
				to = $( this ).parent().find( '.hb-filter-date-to' ).val();
			if ( from.length == 10 ) {
				$( this ).parent().find( '.hb-filter-date-to' ).datepick( 'option', 'minDate', from );
			} else {
				$( this ).parent().find( '.hb-filter-date-to' ).datepick( 'option', 'minDate', -9999 );
			}
			if ( to && from && ( from > to ) ) {
				$( this ).parent().find( '.hb-filter-date-to' ).val( '' ).change();
			}
		});

		$( '.hb-filter-date-to' ).change( function () {
			var from = $( this ).parent().find( '.hb-filter-date-from' ).val();
			if ( from.length == 10 ) {
				var to = $( this ).val();
				if ( to.length == 10 && ( from > to ) ) {
					$( this ).parent().find( '.hb-filter-date-from' ).val( '' ).change();
				}
			}
		});

		this.resa_filtered = ko.computed( function() {
			if ( self.resa_filter() == 'none' ) {
				return self.resa();
			} else if ( self.resa_filter() == 'customer' ) {
				var filter = self.resa_filter_customer().toLowerCase().replace( /\s/g, '' );
				if ( ! filter ) {
					return self.resa();
				} else {
					return ko.utils.arrayFilter( self.resa(), function( resa ) {
						var customer = ko.utils.arrayFirst( self.customers_list(), function( customer ) {
							return resa.customer_id() == customer.id;
						});
						if ( ! customer ) {
							return false;
						} else {
							if ( customer.name_email().toLowerCase().indexOf( filter ) >= 0 ) {
								return true;
							} else {
								return false;
							}
						}
					});
				}
			} else if ( self.resa_filter() == 'status' ) {
				return ko.utils.arrayFilter( self.resa(), function( resa ) {
					if ( resa.status() == self.resa_filter_status() ) {
						return true;
					} else {
						return false;
					}
				});
			} else if ( self.resa_filter() == 'accom' ) {
				return ko.utils.arrayFilter( self.resa(), function( resa ) {
					if ( self.resa_filter_accom_id() == 'all' ) {
						return true;
					} else if ( resa.accom_id() == self.resa_filter_accom_id() ) {
						if ( self.resa_filter_accom_num() == 'all' ) {
							return true;
						} else {
							if ( resa.accom_num() == self.resa_filter_accom_num() ) {
								return true;
							} else {
								return false;
							}
						}
					} else {
						return false;
					}
				});
			} else if (
				self.resa_filter() == 'check_in_date' ||
				self.resa_filter() == 'check_out_date' ||
				self.resa_filter() == 'check_in_out_date' ||
				self.resa_filter() == 'active_resa_date'
			) {
				return ko.utils.arrayFilter( self.resa(), function( resa ) {
					var from = self.resa_filter_check_in_from(),
						to = self.resa_filter_check_in_to();
					if ( self.resa_filter() == 'check_out_date' ) {
						from = self.resa_filter_check_out_from();
						to = self.resa_filter_check_out_to();
					} else if ( self.resa_filter() == 'check_in_out_date' ) {
						from = self.resa_filter_check_in_out_from();
						to = self.resa_filter_check_in_out_to();
					} else if ( self.resa_filter() == 'active_resa_date' ) {
						from = self.resa_filter_active_resa_from(),
						to = self.resa_filter_active_resa_to();
					}
					if ( from.trim() == '' ) {
						from = '0000-00-00';
					}
					if ( to.trim() == '' ) {
						to = '9999-99-99';
					}
					if (
						self.resa_filter() == 'check_in_date' && ( resa.check_in() >= from && resa.check_in() <= to ) ||
						self.resa_filter() == 'check_out_date' && ( resa.check_out() >= from && resa.check_out() <= to ) ||
						self.resa_filter() == 'check_in_out_date' && ( ( resa.check_in() >= from && resa.check_in() <= to ) || ( resa.check_out() >= from && resa.check_out() <= to ) ) ||
						self.resa_filter() == 'active_resa_date' && ( ( resa.check_in() <= to && resa.check_out() > from ) )
					) {
						return true;
					} else {
						return false;
					}
				});
			}
		});

		this.resa_sort = ko.observable( 'received_date' );

		this.resa_sort.subscribe( function( sorting ) {
			if ( sorting == 'check_in_date_asc' && self.resa_filter() == 'none' ) {
				self.resa_filter( 'check_in_date' );
				self.resa_filter_check_in_to( '' );
			}
		});

		this.resa_sorted = ko.computed( function() {
			if ( self.resa_sort() == 'check_in_date_asc' ) {
				return self.resa_filtered().slice().sort( function( a, b ) {
					if ( a.check_in() > b.check_in() ) {
						return 1;
					} else if ( a.check_in() < b.check_in() ) {
						return -1;
					} else {
						return 0;
					}
				});
			} else if ( self.resa_sort() == 'check_in_date_desc' ) {
				return self.resa_filtered().slice().sort( function( a, b ) {
					if ( a.check_in() < b.check_in() ) {
						return 1;
					} else if ( a.check_in() > b.check_in() ) {
						return -1;
					} else {
						return 0;
					}
				});
			} else if ( self.resa_sort() == 'received_date_asc' ) {
				return self.resa_filtered().slice().sort( function( a, b ) {
					if ( a.received_on > b.received_on ) {
						return 1;
					} else if ( a.received_on < b.received_on ) {
						return -1;
					} else {
						return 0;
					}
				});
			} else {
				return self.resa_filtered();
			}
		});

		function blur_buttons() {
			$( '.button' ).blur();
		}

		this.resa_per_page = 25;
		this.resa_current_page_number = ko.observable( 1 );

		this.resa_first_page = function() {
			self.resa_current_page_number( 1 );
			blur_buttons();
		}

		this.resa_last_page = function() {
			self.resa_current_page_number( self.resa_total_pages() );
			blur_buttons();
		}

		this.resa_next_page = function() {
			if ( self.resa_current_page_number() != self.resa_total_pages() ) {
				self.resa_current_page_number( self.resa_current_page_number() + 1 );
			}
			blur_buttons();
		}

		this.resa_previous_page = function() {
			if ( self.resa_current_page_number() != 1 ) {
				self.resa_current_page_number( self.resa_current_page_number() - 1 );
			}
			blur_buttons();
		}

		this.resa_total_pages = ko.computed(function() {
			var total = Math.floor( self.resa_sorted().length / self.resa_per_page );
			total += self.resa_sorted().length % self.resa_per_page > 0 ? 1 : 0;
			return total;
		});

		this.resa_paginated = ko.computed( function() {
			if ( self.resa_current_page_number() > self.resa_total_pages() ) {
				self.resa_current_page_number( 1 );
			}
			var first = self.resa_per_page * ( self.resa_current_page_number() - 1 );
			return self.resa_sorted().slice( first, first + self.resa_per_page );
		});

		this.selected_resa = ko.observable( 0 );

		$( '#hb-resa-cal-scroller' ).on( 'click', '.hbdlcd', function() {
			$( this ).blur();
			self.selected_resa( $( this ).data( 'resa-id' ) );
			return false;
		});

		this.hide_selected_resa = function() {
			self.selected_resa( 0 );
		}

		this.resa_detailed = ko.computed( function() {
			if ( self.selected_resa() == 0 ) {
				return [];
			}
			for ( var i = 0; i < self.resa().length; i++ ) {
				if ( self.resa()[ i ].id == self.selected_resa() ) {
					return self.resa()[ i ];
				}
			}
			self.selected_resa( 0 );
			return [];
		});

		this.add_blocked_accom = function() {
			$( '.hb-add-blocked-accom-submit input' ).blur();

			var from_date = $( '#hb-block-accom-from-date' ).val(),
				to_date = $( '#hb-block-accom-to-date' ).val(),
				accom_id = $( '#hb-select-blocked-accom-type' ).val(),
				accom_num = $( '#hb-select-blocked-accom-num' ).val(),
				accom_all_ids = 0,
				accom_all_num = 0,
				comment = $( '#hb-block-accom-comment' ).val();

			if ( ! from_date || from_date == '0000-00-00' ) {
				from_date = '2016-01-01';
			}
			if ( ! to_date || to_date == '0000-00-00' ) {
				to_date = '2029-12-31';
			}
			if ( from_date >= to_date ) {
				alert( hb_text.to_date_before_from_date );
				return;
			}
			if ( accom_id == 'all' ) {
				accom_all_ids = 1;
				accom_all_num = 1;
				accom_id = 0;
				accom_num = 0;
			}
			if ( accom_num == 'all' ) {
				accom_num = 0;
				accom_all_num = 1;
			}

			if ( accom_all_ids && ( from_date == '2016-01-01' ) && ( to_date == '2029-12-31' ) ) {
				if ( ! confirm( hb_text.block_all ) ) {
					return;
				}
			}

			$( '.hb-add-blocked-accom-submit .hb-ajaxing' ).css( 'display', 'inline-block' );

			hb_resa_ajax({
				data: {
					'action': 'hb_add_blocked_accom',
					'accom_id': accom_id,
					'accom_num': accom_num,
					'accom_all_ids': accom_all_ids,
					'accom_all_num': accom_all_num,
					'from_date': from_date,
					'to_date': to_date,
					'comment': comment,
					'nonce': $( '#hb_nonce_update_db' ).val()
				},
				success: function( ajax_return ) {
					$( '.hb-add-blocked-accom-submit .hb-ajaxing' ).css( 'display', 'none' );
					if ( ajax_return == 'blocked accom added' ) {
						var new_blocked_accom = new BlockedAccom(
							from_date,
							to_date,
							accom_id,
							accom_num,
							accom_all_num,
							accom_all_ids,
							comment,
							0,
							0
						);
						new_blocked_accom.anim_class( 'hb-blocked-accom-added' );
						self.blocked_accom.unshift( new_blocked_accom );
						setTimeout( function() {
							new_blocked_accom.anim_class( '' );
						}, 300 );
					} else {
						alert( ajax_return );
					}
				},
				error: function( jqXHR, textStatus, errorThrown ) {
					$( '.hb-add-blocked-accom-submit .hb-ajaxing' ).css( 'display', 'none' );
					blocked_accom.deleting( false );
					alert( textStatus + ' (' + errorThrown + ')' )
				}
			});
		}

		this.delete_blocked_accom = function( blocked_accom ) {
			if ( confirm( hb_text.confirm_delete_blocked_accom ) ) {
				blocked_accom.deleting( true );
				var accom_id = 0,
					accom_num = 0;
				if ( blocked_accom.accom_id ) {
					accom_id = blocked_accom.accom_id;
				}
				if ( blocked_accom.accom_num ) {
					accom_num = blocked_accom.accom_num;
				}
				hb_resa_ajax({
					data: {
						'action': 'hb_delete_blocked_accom',
						'date_from': blocked_accom.from_date,
						'date_to': blocked_accom.to_date,
						'accom_id': accom_id,
						'accom_num': accom_num,
						'accom_all_ids': blocked_accom.accom_all_ids,
						'accom_all_num': blocked_accom.accom_all_num,
						'nonce': $( '#hb_nonce_update_db' ).val()
					},
					success: function( ajax_return ) {
						if ( ajax_return == 'blocked accom deleted' ) {
							blocked_accom.anim_class( 'hb-blocked-accom-deleting' );
							setTimeout( function() {
								self.blocked_accom.remove( blocked_accom );
							}, 300 );
						} else {
							blocked_accom.deleting( false );
							alert( ajax_return );
						}
					},
					error: function( jqXHR, textStatus, errorThrown ) {
						blocked_accom.deleting( false );
						alert( textStatus + ' (' + errorThrown + ')' )
					}
				});
			}
		}


		var observable_customers = [];
		$.each( hb_customers, function( customer_id, customer_info ) {
			observable_customers.push(
				new Customer(
					customer_info.id,
					customer_info.info,
					customer_info.payment_id
				)
			);
		});

		this.customers_list( observable_customers );

		var prepared_resa = [],
			not_processing_status = [ 'new', 'pending', 'confirmed', 'cancelled' ];
		for ( var i = 0; i < resa.length; i++ ) {
			if ( not_processing_status.indexOf( resa[i].status ) < 0 ) {
				resa[i].status = 'processing';
			}
			prepared_resa.push(
				new Resa(
					resa[i].id,
					resa[i].status,
					resa[i].price,
					resa[i].previous_price,
					resa[i].paid,
					resa[i].accom_price,
					resa[i].old_currency,
					resa[i].check_in,
					resa[i].check_out,
					resa[i].adults,
					resa[i].children,
					resa[i].accom_id,
					resa[i].accom_num,
					resa[i].options_info,
					resa[i].non_editable_info,
					resa[i].admin_comment,
					resa[i].accom_discount_amount,
					resa[i].accom_discount_amount_type,
					resa[i].global_discount_amount,
					resa[i].global_discount_amount_type,
					resa[i].customer_id,
					resa[i].received_on,
					resa[i].origin,
					resa[i].origin_url,
					resa[i].additional_info,
					resa[i].lang,
					resa[i].max_refundable,
					this
				)
			);
		}
		this.resa( prepared_resa );

		var observable_blocked_accom = [];
		for ( var i = 0; i < hb_blocked_accom.length; i++ ) {
			observable_blocked_accom.push(
				new BlockedAccom(
					hb_blocked_accom[i].from_date,
					hb_blocked_accom[i].to_date,
					hb_blocked_accom[i].accom_id,
					hb_blocked_accom[i].accom_num,
					hb_blocked_accom[i].accom_all_num,
					hb_blocked_accom[i].accom_all_ids,
					hb_blocked_accom[i].comment,
					hb_blocked_accom[i].linked_resa_id
				)
			);
		}
		this.blocked_accom( observable_blocked_accom );
	}

	var resaViewModel = new ResaViewModel();
	ko.applyBindings( resaViewModel );

	var today = new Date();
	hb_resa_cal_tables( hb_date_to_str( today ), displayed_accoms );
	resaViewModel.redraw_calendar();

});

var hb_new_admin_resa;