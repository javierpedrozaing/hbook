<?php
class HbAdminPageReservationsSyncErrors {

	private $hbdb;

	public function __construct( $hbdb ) {
		$this->hbdb = $hbdb;
	}

	public function display() {
		$sync_errors = $this->hbdb->get_sync_errors();
		if ( $sync_errors ) {
			?>
			<div class="hb-sync-errors-msg notice notice-warning is-dismissible">
				<ul>
				<?php
				foreach ( $sync_errors as $sync_error ) {
					$accom_id = $sync_error['accom_id'];
					$accom_num_name = $this->hbdb->get_accom_num_name_by_accom_num( $accom_id, $sync_error['accom_num'] );
					if ( $sync_error['error_type'] == 'invalid_url' ) { ?>
						<p>
							<?php
							printf(
								esc_html__( '%1$s : There was a connection error to synchronize the calendar named %2$s and with the url %3$s for the accommodation %4$s (%5$s).', 'hbook-admin' ),
								'<b>' . $sync_error['created_on'] . '</b>',
								'<b>' . $sync_error['calendar_name'] . '</b>',
								'<b>' . $sync_error['synchro_url'] . '</b>',
								'<b>' . get_the_title( $accom_id ) . '</b>',
								$accom_num_name
							);
							?>
						</p>
					<?php
					} else if ( $sync_error['error_type'] == 'event not imported' ) { ?>
						<p>
							<?php
							printf(
								esc_html__( '%1$s : A reservation of the calendar named %2$s could not be imported as the accommodation %3$s (%4$s) is not available for a check-in on %5$s and a check-out on %6$s', 'hbook-admin' ),
								'<b>' . $sync_error['created_on'] . '</b>',
								'<b>' . $sync_error['calendar_name'] . '</b>',
								'<b>' . get_the_title( $accom_id ) . '</b>',
								$accom_num_name,
								'<b>' . $sync_error['check_in'] . '</b>',
								'<b>' . $sync_error['check_out'] . '</b>'
							);
							?>
						</p>
					<?php
					} else if ( $sync_error['error_type'] == 'resa_modified' ) { ?>
						<p>
							<?php
							printf(
								esc_html__( '%1$s : A reservation of the calendar named %2$s has been modified. Changes have not been reflected in the Reservation list below. This reservation is for the accommodation %3$s (%4$s) and appears in the Reservation list with a check-in on %5$s and a check-out on %6$s.', 'hbook-admin' ),
								'<b>' . $sync_error['created_on'] . '</b>',
								'<b>' . $sync_error['calendar_name'] . '</b>',
								'<b>' . get_the_title( $accom_id ) . '</b>',
								$accom_num_name,
								'<b>' . $sync_error['check_in'] . '</b>',
								'<b>' . $sync_error['check_out'] . '</b>'
							);
							?>
						</p>
					<?php
					}
				}
				?>
				</ul>
			</div>
			<?php
		}
	}

}