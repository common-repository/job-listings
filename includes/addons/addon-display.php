<?php
wp_enqueue_script( 'addons' );
wp_enqueue_style( 'addons' );
?>
<div class="wrap plugin-install-tab-featured">
	<?php
	$reload_addons = add_query_arg( 'force_load', true );
	?>
	<h1><?php echo esc_html__( 'Add-ons', 'job-listings' ); ?>
		<a href="<?php echo esc_url( $reload_addons ); ?>"
		   class="jlt-dash-button jlt-dash-icon-small"><?php _e( 'Update list', 'job-listings' ); ?></a></h1>
	<form id="plugin-filter" method="post">
		<div class="wp-list-table widefat plugin-install">
			<h2 class="screen-reader-text"><?php _e( 'Plugins list', 'job-listings' ); ?></h2>
			<div id="JLT_Addon" class="view_wrapper">
				<div class='wrap'>
					<div class="jlt-dashboard jlt-dash-addons">
						<?php
						$addons = JLT_Addons::get_addons();
						$addons = apply_filters( 'jlt_addons_filter', (array) $addons );

						if ( ! empty( $addons ) ):
							?>
							<?php
							$plugins = get_plugins();
							foreach ( $addons as $addon ) {
								if ( version_compare( JLT_VERSION, $addon->version_from, '<' ) || version_compare( JLT_VERSION, $addon->version_to, '>' ) ) {
									continue;
								}
								if ( empty( $addon->title ) ) {
									continue;
								}

								$jlt_dash_background_style = ! empty( $addon->background ) ? 'style="background-image: url(' . $addon->background . ');"' : "";
								?>
								<div
									class="jlt-dash-widget <?php echo $addon->slug; ?>" <?php echo $jlt_dash_background_style; ?>>
									<div class="jlt-dash-title-wrap">
										<div class="jlt-dash-title"><?php echo $addon->title; ?></div>
										<?php
										//Plugin Status
										$jlt_addon_not_activated = $jlt_addon_activated = $jlt_addon_not_installed = 'style="display:none"';
										$jlt_addon_version       = "";
										if ( array_key_exists( $addon->slug . '/' . $addon->slug . '.php', $plugins ) ) {
											if ( is_plugin_inactive( $addon->slug . '/' . $addon->slug . '.php' ) ) {
												$jlt_addon_not_activated = 'style="display:block"';
											} else {
												$jlt_addon_activated = 'style="display:block"';
											}
											$jlt_addon_version = $plugins[ $addon->slug . '/' . $addon->slug . '.php' ][ 'Version' ];
										} else {
											$jlt_addon_not_installed = 'style="display:block"';
										}

										//Check for registered
										$jlt_addon_validated = get_option( 'jlt-addon-valid', 'true' );
										$jlt_addon_validated = $jlt_addon_validated == 'true' ? true : false;

										if ( $jlt_addon_validated ) {
											?>
											<div
												class="jlt-dash-title-button jlt-status-orange" <?php echo $jlt_addon_not_activated; ?>
												data-plugin="<?php echo $addon->slug . '/' . $addon->slug . '.php'; ?>"
												data-alternative="<i class='icon-no-problem-found'></i>Activate">
												<i class="icon-update-refresh"></i><?php echo esc_html__( "Not Active", 'job-listings' ); ?>
											</div>
											<div
												class="jlt-dash-button-rosybrown jlt-dash-deactivate-addon jlt-dash-title-button" <?php echo $jlt_addon_activated; ?>
												data-plugin="<?php echo $addon->slug . '/' . $addon->slug . '.php'; ?>"
												data-alternative="<i class='icon-update-refresh'></i>Deactivate">
												<i class="icon-update-refresh"></i><?php echo esc_html__( "Deactivate", 'job-listings' ); ?>
											</div>
											<div
												class=" jlt-dash-title-button jlt-status-green" <?php echo $jlt_addon_activated; ?>
												data-plugin="<?php echo $addon->slug . '/' . $addon->slug . '.php'; ?>"
												data-alternative="<i class='icon-update-refresh'></i>Deactivate">
												<i class="icon-no-problem-found"></i><?php echo esc_html__( "Active", 'job-listings' ); ?>
											</div>
											<div
												class=" jlt-dash-title-button jlt-status-red" <?php echo $jlt_addon_not_installed; ?>
												data-alternative="<i class='icon-update-refresh'></i>Install"
												data-plugin="<?php echo $addon->slug; ?>">
												<i class="icon-not-registered"></i><?php echo esc_html__( "Not Installed", 'job-listings' ); ?>
											</div>
										<?php } else {
											$jlt_addon_version = "";
											$result           = deactivate_plugins( $addon->slug . '/' . $addon->slug . '.php' );
											?>
											<div class="jlt-dash-title-button jlt-status-red" style="display:block">
												<i class="icon-not-registered"></i><?php echo esc_html__( "Add-on locked", 'job-listings' ); ?>
											</div>
										<?php }
										?>
									</div>
									<div class="jlt-dash-widget-inner jlt-dash-widget-registered">

										<div class="jlt-dash-content">
											<div class="jlt-dash-strong-content"><?php echo $addon->line_1; ?></div>
											<div><?php echo $addon->line_2; ?></div>
										</div>
										<div class="jlt-dash-content-space"></div>
										<?php if ( ! empty( $jlt_addon_version ) ) { ?>
											<div class="jlt-dash-version-info">
												<div class="jlt-dash-strong-content ">
													<?php echo esc_html__( 'Installed Version', 'job-listings' ); ?>
												</div>
												<?php echo $jlt_addon_version; ?>
											</div>
										<?php } ?>
										<div class="jlt-dash-version-info">
											<div class="jlt-dash-strong-content jlt-dash-version-info">
												<?php echo esc_html__( 'Available Version', 'job-listings' ); ?>
											</div>
											<?php echo $addon->available; ?>
										</div>
										<?php if ( ! empty( $jlt_addon_version ) ) { ?>
											<div class="jlt-dash-content-space"></div>
											<div class="jlt-dash-content-space"></div>
										<?php } ?>
										<div class="jlt-dash-bottom-wrapper">
											<?php if ( ! empty( $jlt_addon_version ) ) { ?>
												<?php
												if ( version_compare( $jlt_addon_version, $addon->available ) >= 0 ) { ?>
													<span
														class="jlt-dash-button-gray"><?php echo esc_html__( 'Up to date', 'job-listings' ); ?></span>
													<?php
												} else { ?>
													<a href="update-core.php?check_update=true"
													   class="jlt-dash-button"><?php echo esc_html__( 'Update Now', 'job-listings' ); ?></a>
													<?php
												}
												?>
											<?php } else {
												if ( $jlt_addon_validated ) {
													?>
													<span data-plugin="<?php echo $addon->slug; ?>" data-is-buy="<?php echo $addon->is_buy; ?>" data-url="<?php echo $addon->url_button; ?>" class="jlt-addon-not-installed jlt-dash-button"><?php echo esc_html__( 'Install this Add-on', 'realty-portal' ); ?></span>
													<?php
												} else { ?>
													<a href="<?php echo admin_url( 'admin.php?page=job-listings-addons' ); ?>"
													   class="jlt-dash-button"><?php echo esc_html__( 'Register Job Listings', 'job-listings' ); ?></a>
													<?php
												}
											} ?>

											<?php if ( ! empty( $addon->button ) && ! empty( $addon->url_button ) && $jlt_addon_validated && ! empty( $jlt_addon_version ) ) {
												if ( $jlt_addon_activated == 'style="display:block"' ) {
													?>
													<a target="_blank" href="<?php echo $addon->url_button; ?>"
													   class="jlt-dash-button"><?php echo $addon->button; ?></a>
												<?php } else { ?>
													<span
														data-plugin="<?php echo $addon->slug . '/' . $addon->slug . '.php'; ?>"
														class="jlt-addon-not-activated jlt-dash-button jlt-dash-action-button"
														id="jlt-dash-addons-trigger_<?php echo $addon->slug; ?>"><?php echo esc_html__( 'Activate Plugin', 'job-listings' ); ?></span>
												<?php }
											} ?>
										</div>
									</div>
								</div>
								<?php
							}
							?>

						<?php else: ?>
							<p><?php _e( 'Currently there are no addons. We will update soon.', 'job-listings' ); ?></p>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
	</form>
</div>
