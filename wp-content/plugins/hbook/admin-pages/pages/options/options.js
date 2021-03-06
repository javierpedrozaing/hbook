function Option( brand_new, id, name, amount, amount_children, apply_to_type, choice_type, choices, accom, all_accom, quantity_max_option, quantity_max, quantity_max_child,  price_season, temporada) {
	OptionsAndFees.call( this, brand_new, 'option', id, name, amount, amount_children, apply_to_type, accom, all_accom, price_season, temporada);
	this.choice_type = ko.observable( choice_type );
	this.choices = ko.observableArray( choices );
	this.quantity_max_option = ko.observable( quantity_max_option );
	this.quantity_max = ko.observable( quantity_max );
	this.quantity_max_child = ko.observable( quantity_max_child );	
	this.showPriceSeason1 = ko.observable(true);
	// this.showPriceSeason2 = ko.observable(false);
	// this.showPriceSeason3 = ko.observable(false);	
	this.temporada = ko.observable(temporada);	
	this.price_season = ko.observable(price_season);	


	var self = this;

	this.precio_temporada =  ko.computed( function() {
		return self.temporada() + " (" + self.price_season() + " €)";
	});

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

	

	// this.temporada.subscribe( function( new_value ) {	
	// 	this.choice_type( 'single' );			
	// 	switch (new_value) {
	// 		case 'temporada_1':
	// 			this.showPriceSeason1(true);
	// 			this.showPriceSeason2(false);
	// 			this.showPriceSeason3(false);
	// 			break;
	// 		case 'temporada_2':
	// 			this.showPriceSeason1(false);
	// 			this.showPriceSeason3(false);
	// 			this.showPriceSeason2(true);
	// 			break;
	// 		case 'temporada_3':
	// 			this.showPriceSeason1(false);
	// 			this.showPriceSeason2(false);
	// 			this.showPriceSeason3(true);
	// 			break;
		
	// 		default:
	// 			break;
	// 	}
	// 	for ( var i = 0; i < self.choices().length; i++ ) {
	// 		self.choices()[ i ].temporada( new_value );
	// 	}
	
	// }, this);

	
	this.revert = function( option ) {		
		if ( option ) {
			self.name( option.name );
			self.amount( option.amount );
			self.amount_children( option.amount_children );
			self.apply_to_type( option.apply_to_type );			
			self.choice_type( option.choice_type );
			self.accom( option.accom );
			self.all_accom( option.all_accom );
			self.quantity_max_option( option.quantity_max_option );
			self.quantity_max( option.quantity_max );
			self.quantity_max_child( option.quantity_max_child );
			self.price_season(option.price_season);			
			self.temporada( option.temporada );
		}
	}

}

function OptionChoice( brand_new, id, option_id, name, amount, amount_children, apply_to_type, price_season, temporada ) {
	OptionsAndFees.call( this, brand_new, 'option_choice', id, name, amount, amount_children, apply_to_type, price_season, temporada );
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
					options[i].price_season,					
					options[i].temporada,
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
				options[i].price_season,					
				options[i].temporada,
			)

		);
	}

	ko.utils.extend( this, new HbSettings() );

	this.options = ko.observableArray( observable_options );
	this.create_option = function() {
		var new_option = new Option( true, 0, hb_text.new_option, '0', '0', 'per-person', 'single', [], '', true, 'no', 0, 0, '' ,  '' );
		self.create_setting( new_option, function( new_option ) {
			self.options.push( new_option );
		});
	}

	this.create_option_choice = function( option ) {
		var new_option_choice = new OptionChoice( true, 0, option.id, hb_text.new_option_choice, '0', '0', 'per-person',  '', ''  );
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