registerBlockType( 'hbook/accom-list', {
	title: hb_blocks_text.accom_list_title,
	icon: 'list-view',
	category: 'hbook-blocks',
	supports: {
		className: false,
		customClassName: false,
		html: false
	},

	edit: function edit( props ) {
		var setAttributes = props.setAttributes;
		var attributes = props.attributes;
		var show_thumb = props.attributes.show_thumb;
		var link_thumb = props.attributes.link_thumb;
		var thumb_width = props.attributes.thumb_width;
		var thumb_height = props.attributes.thumb_height;

		function on_show_thumb_change() {
			setAttributes({ show_thumb: ! show_thumb });
		}

		function on_link_thumb_change() {
			setAttributes({ link_thumb: ! link_thumb });
		}

		function on_thumb_width_change( changes ) {
			setAttributes({ thumb_width: changes });
		}

		function on_thumb_height_change( changes ) {
			setAttributes({ thumb_height: changes });
		}

		return [
			el(
				InspectorControls,
				null,
				el(
					PanelBody,
					{ title: hb_blocks_text.accom_list_settings },
					el(
						ToggleControl, {
							label: hb_blocks_text.show_thumb,
							checked: show_thumb,
							onChange: on_show_thumb_change
						}
					),
					show_thumb &&
					el(
						ToggleControl, {
							label: hb_blocks_text.link_thumb_to_accom,
							checked: link_thumb,
							onChange: on_link_thumb_change
						}
					),
					show_thumb &&
					el(
						TextControl, {
							label: hb_blocks_text.thumb_width,
							value: thumb_width,
							onChange: on_thumb_width_change,
							type: 'number',
							step: '10'
						}
					),
					show_thumb &&
					el(
						TextControl, {
							label: hb_blocks_text.thumb_height,
							value: thumb_height,
							onChange: on_thumb_height_change,
							type: 'number',
							step: '10'
						}
					)
				)
			),
			el(
				'div',
				{ style: { background: '#fff', border: '1px solid', padding: '10px 15px' } },
				hb_blocks_text.accom_list_block
			)
		];
	},
	save: function save() {
		return null;
	}
});