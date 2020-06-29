<?php
class HbAdminPageEmails extends HbAdminPage {

	private $accom;

	public function __construct( $page_id, $hbdb, $utils, $options_utils ) {
		$langs = $utils->get_langs();
		$hb_email_langs = array();
		foreach ( $langs as $locale => $lang_name ) {
			$email_lang = array(
				'lang_value' => $locale,
				'lang_name' => $lang_name
			);
			$hb_email_langs[] = $email_lang;
		}
		$hb_email_langs[] = array(
			'lang_value' => 'all',
			'lang_name' => esc_html__( 'All', 'hbook-admin' )
		);
		$email_tmpls = $hbdb->get_all_email_templates();
		$media_titles_list = array();
		foreach ( $email_tmpls as $email_tmpl ) {
			if ( $email_tmpl['media_attachments'] ) {
				$media_attachments = explode( ',', $email_tmpl['media_attachments'] );
				$media_titles = array();
				foreach ( $media_attachments as $media_id ) {
					$media_title = get_the_title( $media_id );
					if ( $media_title ) {
						$media_titles[] = $media_title;
					}
				}
				$media_titles_list[ $email_tmpl['media_attachments'] ] = implode( ', ', $media_titles );
			}
		}
		$this->email_actions = apply_filters(
			'hb_email_actions',
			array(
				array(
					'action_value' => 'new_resa',
					'action_text' => esc_html__( 'New reservation (from customers)', 'hbook-admin' ),
				),
				array(
					'action_value' => 'new_resa_admin',
					'action_text' => esc_html__( 'New reservation (from admin)', 'hbook-admin' ),
				),
				array(
					'action_value' => 'confirmation_resa',
					'action_text' => esc_html__( 'Reservation confirmation', 'hbook-admin' ),
				),
				array(
					'action_value' => 'cancellation_resa',
					'action_text' => esc_html__( 'Reservation cancellation', 'hbook-admin' ),
				),
			)
		);
		$this->accom = $hbdb->get_all_accom();
		$this->data = array(
			'hb_text' => array(
				'new_email_tmpl' => esc_html__( 'New email template', 'hbook-admin' ),
				'invalid_email_address' => esc_html__( 'This e-mail address does not seem valid.', 'hbook-admin' ),
				'invalid_multiple_to_address' => esc_html__( 'Please seperate multiple e-mail addresses with commas.', 'hbook-admin' ),
				'invalid_complete_address' => esc_html__( 'Please use a complete e-mail address eg. Your Name <email@domain.com>', 'hbook-admin' ),
				'select_attachments' => esc_html__( 'Select attachments', 'hbook-admin' ),
				'remove_all_attachments' => esc_html__( 'Remove all attachments?', 'hbook-admin' ),
				'email_not_automatically_sent' => esc_html__( 'Not automatically sent', 'hbook-admin' ),
				'message' => esc_html__( 'Message:', 'hbook-admin' ),
				'format' => esc_html__( 'Format:', 'hbook-admin' ),
				'attachments' => esc_html__( 'Attachments:', 'hbook-admin' ),
			),
			'email_tmpls' => $email_tmpls,
			'hb_media_titles' => $media_titles_list,
			'hb_email_langs' => $hb_email_langs,
			'hb_email_actions' => $this->email_actions,
			'accom_list' => $this->accom,
		);
		parent::__construct( $page_id, $hbdb, $utils, $options_utils );
	}

	public function display() {
	?>

	<div class="wrap">

		<h2>
			<?php esc_html_e( 'Email templates', 'hbook-admin' ); ?>
			<a href="#" class="add-new-h2" data-bind="click: create_email_tmpl"><?php esc_html_e( 'Add new email template', 'hbook-admin' ); ?></a>
			<span class="hb-add-new spinner"></span>
		</h2>

		<?php $this->display_right_menu(); ?>

		<br/>

		<!-- ko if: email_tmpls().length == 0 -->
		<?php esc_html_e( 'No email templates set yet.', 'hbook-admin' ); ?>
		<!-- /ko -->

		<!-- ko if: email_tmpls().length > 0 -->
		<?php
		$table_class = 'hb-table hb-email-tmpls-table';
		if ( $this->utils->is_site_multi_lang() ) {
			$table_class .= ' hb-email-multiple-lang';
		}
		?>

		<div class="<?php echo( esc_attr( $table_class ) ); ?>">

			<div class="hb-table-head hb-clearfix">
				<div class="hb-table-head-data"><?php esc_html_e( 'Name', 'hbook-admin' ); ?></div>
				<div class="hb-table-head-data hb-data-addresses"><?php esc_html_e( 'Addresses', 'hbook-admin' ); ?></div>
				<div class="hb-table-head-data"><?php esc_html_e( 'Subject', 'hbook-admin' ); ?></div>
				<div class="hb-table-head-data hb-data-message"><?php esc_html_e( 'Message', 'hbook-admin' ); ?></div>
				<div class="hb-table-head-data hb-data-email-action"><?php esc_html_e( 'Send on', 'hbook-admin' ); ?></div>
				<div class="hb-table-head-data"><?php esc_html_e( 'For accom.', 'hbook-admin' ); ?></div>
				<div class="hb-table-head-data hb-data-email-lang"><?php esc_html_e( 'For language', 'hbook-admin' ); ?></div>
				<div class="hb-table-head-data hb-table-head-data-action"><?php esc_html_e( 'Actions', 'hbook-admin' ); ?></div>
			</div>
			<div data-bind="template: { name: template_to_use, foreach: email_tmpls, beforeRemove: hide_setting }"></div>

			<script id="text_tmpl" type="text/html">
				<div class="hb-table-row hb-clearfix">
					<div class="hb-table-data" data-bind="text: name"></div>
					<div class="hb-table-data hb-data-addresses">
						<span class="hb-data-addresses-type-name"><?php esc_html_e( 'To:', 'hbook-admin' ); ?></span> <span data-bind="html: to_address_html"></span><br/>
						<span class="hb-data-addresses-type-name"><?php esc_html_e( 'Reply-to:', 'hbook-admin' ); ?></span> <span data-bind="html: reply_to_address_html"></span><br/>
						<span class="hb-data-addresses-type-name"><?php esc_html_e( 'From:', 'hbook-admin' ); ?></span> <span data-bind="html: from_address_html"></span>
					</div>
					<div class="hb-table-data" data-bind="text: subject"></div>
					<div class="hb-table-data hb-data-message" data-bind="html: message_html"></div>
					<div class="hb-table-data hb-data-email-action" data-bind="html: actions_text"></div>
					<div class="hb-table-data" data-bind="text: accom_list"></div>
					<div class="hb-table-data hb-data-email-lang" data-bind="text: lang_text"></div>
					<div class="hb-table-data hb-table-data-action"><?php $this->display_admin_action(); ?></div>
				</div>
			</script>

			<script id="edit_tmpl" type="text/html">
				<div class="hb-table-row hb-clearfix">
					<div class="hb-table-data"><input data-bind="value: name" type="text" /></div>
					<div class="hb-table-data hb-data-addresses">
						<label for="to-address"><?php esc_html_e( 'To:', 'hbook-admin' ); ?>&nbsp;&nbsp;</label>
						<input id="to-address" data-bind="value: to_address" type="text" /><br/>
						<label for="reply-to-address"><?php esc_html_e( 'Reply-to:', 'hbook-admin' ); ?>&nbsp;&nbsp;</label>
						<input id="reply-to-address" data-bind="value: reply_to_address" type="text" /><br/>
						<label for="from-address"><?php esc_html_e( 'From:', 'hbook-admin' ); ?>&nbsp;&nbsp;</label>
						<input id="from-address"data-bind="value: from_address" type="text" />
					</div>
					<div class="hb-table-data"><input data-bind="value: subject" type="text" /></div>
					<div class="hb-table-data hb-data-message">
						<label for="email-message"><?php esc_html_e( 'Message:', 'hbook-admin' ); ?></label>
						<textarea id="email-message" class="hb-template-email-message" data-bind="value: message" /></textarea>
						<p>
							<?php esc_html_e( 'Format:' ); ?><br/>
							<input data-bind="checked: format" name="format" id="format_text" type="radio" value="TEXT" />
							<label for="format_text"><?php esc_html_e( 'TEXT', 'hbook-admin' ); ?></label>
							&nbsp;&nbsp;
							<input data-bind="checked: format" name="format" id="format_html" type="radio" value="HTML" />
							<label for="format_html"><?php esc_html_e( 'HTML', 'hbook-admin' ); ?></label>
						</p>
						<p class="hb-add-attachment">
							<?php esc_html_e( 'Attachments:' ); ?><br/>
							<span data-bind="text: media_attachments_list"></span>
							<a href="#" class="hb-add-attachment-link"><?php esc_html_e( 'Select', 'hbook-admin' ); ?></a>
							<a href="#" data-bind="visible: media_attachments() != '', click: remove_media_attachment" class="hb-remove-attachment-link"><?php esc_html_e( 'Remove all', 'hbook-admin' ); ?></a>
							<input data-bind="value: media_attachments" type="hidden" />
						</p>
					</div>
					<div class="hb-table-data hb-data-email-action">
						<?php
						foreach ( $this->email_actions as $action ) {
							$radio_action_id = 'send-on-' . $action['action_value'];
							?>
						<input
							data-bind="checked: action"
							name="action"
							id="<?php echo( esc_attr( $radio_action_id ) ); ?>"
							type="checkbox"
							value="<?php echo( esc_attr( $action['action_value'] ) ); ?>"
						/>
						<label for="<?php echo( esc_attr( $radio_action_id ) ); ?>">
						<?php echo( esc_html( $action['action_text'] ) ); ?>
						</label><br/>
						<?php } ?>
					</div>
					<div class="hb-table-data">
						<?php $this->display_checkbox_list( $this->accom, 'accom' ); ?>
					</div>
					<div class="hb-table-data hb-data-email-lang">
						<select data-bind="options: hb_email_langs, optionsValue: 'lang_value', optionsText: 'lang_name', value: lang">
						</select>
					</div>
					<div class="hb-table-data hb-table-data-action"><?php $this->display_admin_on_edit_action(); ?></div>
				</div>
			</script>

		</div>

		<!-- ko if: email_tmpls().length > 5 -->
		<br/>
		<a href="#" class="add-new-h2 add-new-below" data-bind="click: create_email_tmpl"><?php esc_html_e( 'Add new email template', 'hbook-admin' ); ?></a>
		<span class="hb-add-new spinner"></span>
		<!-- /ko -->

		<h4><?php esc_html_e( '"To address", "Reply-To address", "From address", "Subject" and "Message" fields:', 'hbook-admin' ); ?></h4>
		<p>
			<?php esc_html_e( 'You can use the following variables:', 'hbook-admin' ); ?><br/>
			<?php echo( $this->utils->get_ical_email_document_available_vars() ); ?>
		</p>

		<h4><?php esc_html_e( '"To address" field:', 'hbook-admin' ); ?></h4>
		<p>
			<?php
			printf(
				esc_html__( 'If the field is blank the email address of the WordPress administrator (%s) will be used.', 'hbook-admin' ),
				'<b>' . get_option( 'admin_email' )  . '</b>'
			);
			?>
		</p>
		<p>
			<?php
			printf(
				esc_html__( 'Separate multiple e-mail addresses with commas, for example: %s', 'hbook-admin' ),
				'<b>' . esc_html__( 'email-1@domain.com,email-2@domain.com', 'hbook-admin' )  . '</b>'
			);
			?>
			<b><?php ; ?></b>
		</p>

		<h4><?php esc_html_e( '"Reply-To address", "From address" fields:', 'hbook-admin' ); ?></h4>
		<p>
			<?php
			printf(
				esc_html__( 'Insert a complete e-mail address (a name followed by an email address wrapped between %s and %s, for example: %s', 'hbook-admin' ),
				'<b style="font-weight:900; font-size: 15px">&lt;</b>',
				'<b style="font-weight:900; font-size: 15px">&gt;</b>',
				'<b>' . esc_html__( 'Your Name <your.email@domain.com>', 'hbook-admin' )  . '</b>'
			);
			?>
		</p>

		<h4><?php esc_html_e( '"From address" fields:', 'hbook-admin' ); ?></h4>
		<?php
		$sitename = strtolower( $_SERVER['SERVER_NAME'] );
		if ( substr( $sitename, 0, 4 ) == 'www.' ) {
			$sitename = substr( $sitename, 4 );
		}
		$from_address = 'no-reply@' . $sitename;
		?>
		<p>
			<?php
			printf(
				esc_html__( 'If the field is blank %s will be used.', 'hbook-admin' ),
				'<b>' . get_option( 'blogname' ) . ' &lt;' . $from_address . '&gt;</b>'
			);
			?>
		</p>

		<!-- /ko -->

	</div>

	<?php
	}

}