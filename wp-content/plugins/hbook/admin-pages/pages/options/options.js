function Option( brand_new, id, name, amount, amount_children, apply_to_type, choice_type, choices, accom, all_accom, quantity_max_option, quantity_max, quantity_max_child, temporada, price_season_1, price_season_2, price_season_3) {
	OptionsAndFees.call( this, brand_new, 'option', id, name, amount, amount_children, apply_to_type, accom, all_accom, temporada, price_season_1, price_season_2, price_season_3);
	this.choice_type = ko.observable( choice_type );
	this.choices = ko.observableArray( choices );
	this.quantity_max_option = ko.observable( quantity_max_option );
	this.quantity_max = ko.observable( quantity_max );
	this.quantity_max_child = ko.observable( quantity_max_child );	
	this.showPriceSeason1 = ko.observable(false);
	this.showPriceSeason2 = ko.observable(false);
	this.showPriceSeason3 = ko.observable(false);	

	this.price_season_1 = ko.observable(price_season_1);
	this.price_season_2 = ko.observable(price_season_2);
	this.price_season_3 = ko.observable(price_season_3);
	var self = this;


	this.choice_type_text = ko.computed( function() {
		if ( self.apply_to_type() == 'quantity' || self.apply_to_type() == 'quantity-per-day' ) {
			return '-';
		} else if ( self.choice_type() == 'multiple' ) {
			return hb_text.multiple_choice_yes;
		} else {
			return hb_text.multiple_choice_no;
		}
	});

	this.apply_to_type.subscribe( function( new_value ) {				
		if ( new_value == 'quantity' || new_value == 'quantity-per-day' ) {
			this.choice_type( 'single' );
		}
		for ( var i = 0; i < self.choices().length; i++ ) {
			self.choices()[ i ].apply_to_type( new_value );
		}
	}, this);


	this.temporada.subscribe( function( new_value ) {				
		switch (new_value) {
			case 'temporada_1':
				this.showPriceSeason1(true);
				this.showPriceSeason2(false);
				this.showPriceSeason3(false);
				break;
			case 'temporada_2':
				this.showPriceSeason1(false);
				this.showPriceSeason3(false);
				this.showPriceSeason2(true);
				break;
			case 'temporada_3':
				this.showPriceSeason1(false);
				this.showPriceSeason2(false);
				this.showPriceSeason3(true);
				break;
		
			default:
				break;
		}
		
	}, this);

	
	this.revert = function( option ) {
		if ( option ) {
			self.name( option.name );
			self.amount( option.amount );
			self.amount_children( option.amount_children );
			self.apply_to_type( option.apply_to_type );
			self.temporada( option.temporada );
			self.choice_type( option.choice_type );
			self.accom( option.accom );
			self.all_accom( option.all_accom );
			self.quantity_max_option( option.quantity_max_option );
			self.quantity_max( option.quantity_max );
			self.quantity_max_child( option.quantity_max_child );
			self.price_season_1(option.price_season_1);
			self.price_season_2(option.price_season_2);
			self.price_season_3(option.price_season_3);
		}
	}

}

function OptionChoice( brand_new, id, option_id, name, amount, amount_children, apply_to_type, temporada, price_season_1, price_season_2, price_season_3 ) {
	OptionsAndFees.call( this, brand_new, 'option_choice', id, name, amount, amount_children, apply_to_type, temporada, price_season_1, price_season_2, price_season_3 );
	this.option_id = option_id;

	var self = this;

	this.revert = function( option_choice ) {
		if ( option_choice ) {
			self.name( option_choice.name );
			self.amount( option_choice.amount );
			self.amount_children( option_choice.amount_children );
		}
	}

}

function OptionsViewModel() {

	var self = this;

	observable_options = [];
	for ( var i = 0; i < options.length; i++ ) {
		var observable_option_choices = [];
		for ( var j = 0; j < options[i].choices.length; j++ ) {
			observable_option_choices.push(
				new OptionChoice(
					false,
					options[i].choices[j].id,
					options[i].id,
					options[i].choices[j].name,
					options[i].choices[j].amount,
					options[i].choices[j].amount_children,
					options[i].apply_to_type,
					options[i].temporada,
					options[i].price_season1,
					options[i].price_season2,
					options[i].price_season3
				)
			);
		}
		observable_options.push(
			new Option(
				false,
				options[i].id,
				options[i].name,
				options[i].amount,
				options[i].amount_children,
				options[i].apply_to_type,
				options[i].choice_type,
				observable_option_choices,
				options[i].accom,
				options[i].all_accom,
				options[i].quantity_max_option,
				options[i].quantity_max,
				options[i].quantity_max_child,
				options[i].temporada,
				options[i].price_season1,
				options[i].price_season2,
				options[i].price_season3
			)
		);
	}

	ko.utils.extend( this, new HbSettings() );

	this.options = ko.observableArray( observable_options );

	this.create_option = function() {
		var new_option = new Option( true, 0, hb_text.new_option, '0', '0', 'per-person', 'single', [], '', true, 'no', 0, 0, 'temporada_1', '', '', '' );
		self.create_setting( new_option, function( new_option ) {
			self.options.push( new_option );
		});
	}

	this.create_option_choice = function( option ) {
		var new_option_choice = new OptionChoice( true, 0, option.id, hb_text.new_option_choice, '0', '0', 'per-person' );
		self.create_child_setting( option, new_option_choice, function( new_option_choice ) {
			option.choices.push( new_option_choice );
		});
	}

	this.remove = function( setting, event, option ) {
		if ( setting.type == 'option' ) {
			callback_function = function() {
				self.options.remove( setting );
			}
		} else { // option_choice
			callback_function = function() {
				option.choices.remove( setting );
			}
		}
		self.delete_setting( setting, callback_function );
	}

}

ko.applyBindings( new OptionsViewModel() );