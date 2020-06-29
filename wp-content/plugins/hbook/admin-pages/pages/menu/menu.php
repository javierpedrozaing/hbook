<?php
class HbAdminPageMenu extends HbAdminPage {

	public function __construct( $page_id, $hbdb, $utils, $options_utils ) {
		$this->data = array(
			'hb_text' => array()
		);
		parent::__construct( $page_id, $hbdb, $utils, $options_utils );
	}

	public function display() {
		$hbook_pages = $this->utils->get_hbook_pages();
		foreach ( $hbook_pages as $page ) :
		?>

		<a href="<?php echo( admin_url( 'admin.php?page=' . $page['id'] ) ); ?>" class="hb-menu-box">
			<span class="dashicons <?php echo( $page['icon'] ); ?>"></span>
			<p><?php echo( $page['name'] ); ?></p>
		</a>

		<?php
		endforeach;
	}

}