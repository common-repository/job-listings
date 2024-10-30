<?php
/**
 * Navigation candidate
 *
 * This template can be overridden by copying it to yourtheme/job-listings/member/navigation-candidate.php.
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

?>

<nav class="jlt-member-navigation jlt-employer-navigation">
    <ul>
        <?php foreach ( jlt_get_candidate_menu() as $endpoint ) : ?>
            <li>
                <a href="<?php echo esc_url( jlt_get_member_endpoint_url( $endpoint[ 'url' ] ) ); ?>"><?php echo esc_html( $endpoint[ 'text' ] ); ?></a>
            </li>
        <?php endforeach; ?>
    </ul>
</nav>
