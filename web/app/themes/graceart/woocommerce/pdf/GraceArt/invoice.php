<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>

<?php do_action( 'wpo_wcpdf_before_document', $this->get_type(), $this->order ); ?>

<table class="document-head container">
	<tr>
		<td class="logo">
			<?php if ( $this->has_header_logo() ) : ?>
				<?php do_action( 'wpo_wcpdf_before_shop_logo', $this->get_type(), $this->order ); ?>
				<?php $this->header_logo(); ?>
				<?php do_action( 'wpo_wcpdf_after_shop_logo', $this->get_type(), $this->order ); ?>
			<?php endif; ?>
		</td>
		<td class="document-title">
			<?php do_action( 'wpo_wcpdf_before_document_label', $this->get_type(), $this->order ); ?>
			<h1>
				<?php $this->title(); ?>
				<?php if ( $this->get_number( $this->get_type() ) ) : ?>
					<?php esc_html_e( 'č.', 'graceart' ); ?> <?php $this->number( $this->get_type() ); ?>
				<?php endif; ?>
			</h1>
			<?php do_action( 'wpo_wcpdf_after_document_label', $this->get_type(), $this->order ); ?>
		</td>
	</tr>
</table>

<table class="parties">
	<tr>
		<td class="party supplier">
			<p class="party-label"><?php esc_html_e( 'Dodávateľ:', 'graceart' ); ?></p>

			<?php do_action( 'wpo_wcpdf_before_shop_name', $this->get_type(), $this->order ); ?>
			<p class="party-name"><?php $this->shop_name(); ?></p>
			<?php do_action( 'wpo_wcpdf_after_shop_name', $this->get_type(), $this->order ); ?>

			<?php do_action( 'wpo_wcpdf_before_shop_address', $this->get_type(), $this->order ); ?>
			<?php foreach ( graceart_wcpdf_shop_address_lines( $this ) as $line ) : ?>
				<p class="party-line"><?php echo esc_html( $line ); ?></p>
			<?php endforeach; ?>
			<?php do_action( 'wpo_wcpdf_after_shop_address', $this->get_type(), $this->order ); ?>

			<?php
			$graceart_ico    = graceart_wcpdf_ico( $this );
			$graceart_dic    = graceart_wcpdf_dic( $this );
			$graceart_vat_id = graceart_wcpdf_vat_id( $this );
			?>
			<?php if ( $graceart_ico || $graceart_dic || $graceart_vat_id ) : ?>
				<div class="party-block">
					<?php if ( $graceart_ico ) : ?>
						<p class="party-line"><span class="label"><?php esc_html_e( 'IČO:', 'graceart' ); ?></span> <?php echo esc_html( $graceart_ico ); ?></p>
					<?php endif; ?>
					<?php if ( $graceart_dic ) : ?>
						<p class="party-line"><span class="label"><?php esc_html_e( 'DIČ:', 'graceart' ); ?></span> <?php echo esc_html( $graceart_dic ); ?></p>
					<?php endif; ?>
					<?php if ( $graceart_vat_id ) : ?>
						<p class="party-line"><span class="label"><?php esc_html_e( 'IČ DPH:', 'graceart' ); ?></span> <?php echo esc_html( $graceart_vat_id ); ?></p>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<div class="party-block">
				<?php if ( $this->get_shop_phone_number() ) : ?>
					<p class="party-line"><?php $this->shop_phone_number(); ?></p>
				<?php endif; ?>
				<?php if ( $this->get_shop_email_address() ) : ?>
					<p class="party-line"><?php $this->shop_email_address(); ?></p>
				<?php endif; ?>
				<p class="party-line"><?php echo esc_html( wp_parse_url( home_url(), PHP_URL_HOST ) ); ?></p>
			</div>
		</td>
		<td class="party customer">
			<p class="party-label"><?php esc_html_e( 'Odberateľ:', 'graceart' ); ?></p>

			<?php do_action( 'wpo_wcpdf_before_billing_address', $this->get_type(), $this->order ); ?>
			<p class="party-address"><?php $this->billing_address(); ?></p>
			<?php do_action( 'wpo_wcpdf_after_billing_address', $this->get_type(), $this->order ); ?>

			<?php if ( isset( $this->settings['display_email'] ) || isset( $this->settings['display_phone'] ) ) : ?>
				<div class="party-block">
					<?php if ( isset( $this->settings['display_phone'] ) && $this->get_billing_phone() ) : ?>
						<p class="party-line"><?php $this->billing_phone(); ?></p>
					<?php endif; ?>
					<?php if ( isset( $this->settings['display_email'] ) && $this->get_billing_email() ) : ?>
						<p class="party-line"><?php $this->billing_email(); ?></p>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</td>
	</tr>
</table>

<table class="order-meta">
	<tr>
		<td class="payment-data">
			<?php if ( $this->get_payment_method() ) : ?>
				<p><?php esc_html_e( 'Forma úhrady:', 'graceart' ); ?> <?php $this->payment_method(); ?></p>
			<?php endif; ?>
			<?php if ( graceart_wcpdf_variable_symbol( $this ) ) : ?>
				<p><?php esc_html_e( 'Variabilný symbol:', 'graceart' ); ?> <?php echo esc_html( graceart_wcpdf_variable_symbol( $this ) ); ?></p>
			<?php endif; ?>

			<div class="dates">
				<?php if ( $this->get_date( $this->get_type() ) ) : ?>
					<p><span class="label"><?php esc_html_e( 'Dátum vystavenia:', 'graceart' ); ?></span> <?php $this->date( $this->get_type() ); ?></p>
				<?php endif; ?>
				<?php if ( graceart_wcpdf_tax_point_date( $this ) ) : ?>
					<p><span class="label"><?php esc_html_e( 'Dátum vzniku daňovej povinnosti:', 'graceart' ); ?></span> <?php echo esc_html( graceart_wcpdf_tax_point_date( $this ) ); ?></p>
				<?php endif; ?>
				<?php if ( $this->show_due_date() ) : ?>
					<p><span class="label"><?php esc_html_e( 'Dátum splatnosti:', 'graceart' ); ?></span> <?php $this->due_date(); ?></p>
				<?php endif; ?>
			</div>

			<!-- Hooked rows (third party add-ons) expect a table context, so keep them isolated. -->
			<table class="order-data-extra">
				<?php do_action( 'wpo_wcpdf_before_order_data', $this->get_type(), $this->order ); ?>
				<?php do_action( 'wpo_wcpdf_after_order_data', $this->get_type(), $this->order ); ?>
			</table>
		</td>
		<td class="delivery-data">
			<?php if ( $this->show_shipping_address() ) : ?>
				<p class="delivery-label"><?php esc_html_e( 'Adresa doručenia:', 'graceart' ); ?></p>
				<?php do_action( 'wpo_wcpdf_before_shipping_address', $this->get_type(), $this->order ); ?>
				<?php if ( isset( $this->settings['display_phone'] ) && $this->get_shipping_phone( true ) ) : ?>
					<p class="delivery-address"><?php $this->shipping_phone( true ); ?></p>
				<?php endif; ?>
				<p class="delivery-address"><?php $this->shipping_address(); ?></p>
				<?php do_action( 'wpo_wcpdf_after_shipping_address', $this->get_type(), $this->order ); ?>
			<?php endif; ?>
		</td>
	</tr>
</table>

<?php do_action( 'wpo_wcpdf_before_order_details', $this->get_type(), $this->order ); ?>

<table class="order-details">
	<thead>
		<tr>
			<th class="product"><?php esc_html_e( 'Tovar', 'graceart' ); ?></th>
			<th class="single-price"><?php esc_html_e( 'Jednotková cena', 'graceart' ); ?></th>
			<th class="quantity"><?php esc_html_e( 'Množstvo', 'graceart' ); ?></th>
			<th class="line-total"><?php esc_html_e( 'Suma', 'graceart' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ( $this->get_order_items() as $item_id => $item ) : ?>
			<tr class="<?php echo esc_attr( $item['row_class'] ); ?>">
				<td class="product">
					<p class="item-name"><?php echo esc_html( $item['name'] ); ?></p>
					<?php do_action( 'wpo_wcpdf_before_item_meta', $this->get_type(), $item, $this->order ); ?>
					<div class="item-meta">
						<?php if ( ! empty( $item['sku'] ) ) : ?>
							<p class="sku"><span class="label"><?php $this->sku_title(); ?></span> <?php echo esc_html( $item['sku'] ); ?></p>
						<?php endif; ?>
						<!-- ul.wc-item-meta -->
						<?php if ( ! empty( $item['meta'] ) ) : ?>
							<?php echo wp_kses_post( $item['meta'] ); ?>
						<?php endif; ?>
						<!-- / ul.wc-item-meta -->
					</div>
					<?php do_action( 'wpo_wcpdf_after_item_meta', $this->get_type(), $item, $this->order ); ?>
				</td>
				<td class="single-price"><?php echo wp_kses_post( $item['single_price'] ); ?></td>
				<td class="quantity"><?php echo esc_html( $item['quantity'] ); ?>&nbsp;<?php esc_html_e( 'ks', 'graceart' ); ?></td>
				<td class="line-total"><?php echo wp_kses_post( $item['order_price'] ); ?></td>
			</tr>
		<?php endforeach; ?>

		<?php foreach ( $this->get_order_fees() as $fee ) : ?>
			<tr class="fee">
				<td class="product"><?php echo esc_html( $fee['label'] ); ?></td>
				<td class="extra-line" colspan="3"><?php echo wp_kses_post( $fee['value'] ); ?></td>
			</tr>
		<?php endforeach; ?>

		<?php if ( graceart_wcpdf_has_shipping_line( $this ) ) : ?>
			<?php $shipping = $this->get_order_shipping( 'incl' ); ?>
			<tr class="shipping">
				<td class="product"><?php esc_html_e( 'Poštovné a balné', 'graceart' ); ?></td>
				<td class="extra-line" colspan="3"><?php echo wp_kses_post( $shipping['value'] ); ?></td>
			</tr>
		<?php endif; ?>

		<?php if ( $discount = $this->get_order_discount( 'total', 'incl' ) ) : ?>
			<tr class="discount">
				<td class="product"><?php echo esc_html( $discount['label'] ); ?></td>
				<td class="extra-line" colspan="3">-<?php echo wp_kses_post( $discount['value'] ); ?></td>
			</tr>
		<?php endif; ?>

		<?php if ( graceart_wcpdf_is_vat_registered( $this ) ) : ?>
			<?php foreach ( (array) $this->get_order_taxes() as $tax ) : ?>
				<tr class="tax">
					<td class="product"><?php echo esc_html( $tax['label'] ); ?><?php if ( ! empty( $tax['rate'] ) ) : ?> (<?php echo esc_html( $tax['rate'] ); ?>)<?php endif; ?></td>
					<td class="extra-line" colspan="3"><?php echo wp_kses_post( $tax['value'] ); ?></td>
				</tr>
			<?php endforeach; ?>
		<?php endif; ?>

		<tr class="grand-total">
			<td class="grand-total-cell" colspan="4">
				<?php esc_html_e( 'Celková faktúrovaná suma:', 'graceart' ); ?>
				<span class="grand-total-amount"><?php $this->order_grand_total( 'incl' ); ?></span>
			</td>
		</tr>
	</tbody>
</table>

<?php do_action( 'wpo_wcpdf_after_order_details', $this->get_type(), $this->order ); ?>

<div class="below-details">
	<?php if ( ! graceart_wcpdf_is_vat_registered( $this ) ) : ?>
		<p class="vat-notice"><?php esc_html_e( 'Dodávateľ nie je platcom DPH.', 'graceart' ); ?></p>
	<?php endif; ?>

	<?php do_action( 'wpo_wcpdf_before_document_notes', $this->get_type(), $this->order ); ?>
	<?php if ( $this->get_document_notes() ) : ?>
		<div class="document-notes">
			<h3><?php $this->notes_title(); ?></h3>
			<?php $this->document_notes(); ?>
		</div>
	<?php endif; ?>
	<?php do_action( 'wpo_wcpdf_after_document_notes', $this->get_type(), $this->order ); ?>

	<?php do_action( 'wpo_wcpdf_before_customer_notes', $this->get_type(), $this->order ); ?>
	<?php if ( $this->get_shipping_notes() ) : ?>
		<div class="customer-notes">
			<h3><?php $this->customer_notes_title(); ?></h3>
			<?php $this->shipping_notes(); ?>
		</div>
	<?php endif; ?>
	<?php do_action( 'wpo_wcpdf_after_customer_notes', $this->get_type(), $this->order ); ?>
</div>

<div class="bottom-spacer"></div>

<htmlpagefooter name="docFooter"><!-- required for mPDF engine -->
	<div id="footer">
		<!-- hook available: wpo_wcpdf_before_footer -->
		<?php if ( $this->get_footer() ) : ?>
			<?php $this->footer(); ?>
		<?php else : ?>
			<?php
			printf(
				/* translators: 1: shop name, 2: shop website */
				esc_html__( 'Vystavil: %1$s, %2$s', 'graceart' ),
				esc_html( $this->get_shop_name() ),
				esc_html( wp_parse_url( home_url(), PHP_URL_HOST ) )
			);
			?>
		<?php endif; ?>
		<!-- hook available: wpo_wcpdf_after_footer -->
	</div>
</htmlpagefooter><!-- required for mPDF engine -->

<?php do_action( 'wpo_wcpdf_after_document', $this->get_type(), $this->order ); ?>
