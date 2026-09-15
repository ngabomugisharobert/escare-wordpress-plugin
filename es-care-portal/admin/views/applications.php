<?php
/**
 * Applications list.
 *
 * @package ESC_Portal
 *
 * @var ESC_Portal_Applications_List_Table $table List table.
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="wrap esc-admin">
	<h1><?php esc_html_e( 'Applications', 'es-care-portal' ); ?></h1>
	<form method="get">
		<input type="hidden" name="page" value="esc-applications">
		<?php $table->search_box( __( 'Search applicants', 'es-care-portal' ), 'esc-applications' ); ?>
		<?php $table->display(); ?>
	</form>
</div>
