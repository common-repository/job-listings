<?php
/**
 * Display Company on Single Job
 *
 * This template can be overridden by copying it to yourtheme/job-listings/job/job-company.php.
 *
 * HOWEVER, on occasion NooTheme will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @author      NooTheme
 * @version     0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $job;

$company_id = jlt_get_job_company( $job->id );
if (empty($company_id)) return;
$company = jlt_get_company($company_id);

?>
<div class="jlt-single-company-header">
	<div class="jlt-col-30">
		<a href="<?php the_permalink( $company_id ); ?>"
		   title="<?php echo get_the_title( $company_id ); ?>">
			<div class="company-logo">
				<?php echo $company->logo(); ?>
			</div>
		</a>
	</div>
	<div class="jlt-col-70">
		<div class="company-meta">
			<a href="<?php the_permalink( $company_id ); ?>"
			   title="<?php echo get_the_title( $company_id ); ?>">
				<h3><?php echo get_the_title( $company_id ); ?></h3>
			</a>
			<ul class="jlt-social">
				<?php foreach ( $company->social() as $social ): ?>
					<li>
						<a href="<?php echo esc_url( $social[ 'link' ] ) ?>" target="_blank">
							<i class="jlt-icon <?php echo esc_attr( $social[ 'icon' ] ); ?>"></i>
							<span class="jlt-social-label"><?php echo esc_attr($social[ 'label' ]); ?></span>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
			<a class="jlt-btn jlt-btn-view-company" href="<?php the_permalink( $company_id ); ?>"
			   title="<?php echo get_the_title( $company_id ); ?>">
				<?php _e( 'View Company', 'job-listings' ); ?>
			</a>
		</div>
	</div>
</div>
<?php wp_reset_postdata(); ?>
