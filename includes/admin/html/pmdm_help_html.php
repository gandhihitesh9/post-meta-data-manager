<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;


$content = esc_html__("Looking for expert assistance with your WordPress sites? You’ve come to the right place. Whether it’s a one-off customization project or a long-term partnership that can grow with you, we can help you get the job done right.", 'pmdm_wp');
/* Welcome to our expert WordPress assistance services! We understand that managing a WordPress site can be a challenging task, and we're here to help you make the most of your site.

Whether you need a one-time customization or a long-term partnership to help you grow your online presence, i am here to assist you every step of the way. I have extensive experience with WordPress and can help you with any project, big or small.

Our services include:

Customization: We can customize your WordPress site to meet your specific requirements, including customizing your theme, modifying your website's functionality, and adding new features to your site.

Support: We provide ongoing support for your WordPress site, including troubleshooting and maintenance, so that you can focus on your business.

Migration: If you're looking to migrate your site to WordPress, we can help you with the entire process, including transferring your content, setting up your site, and ensuring that everything runs smoothly.

No matter what your WordPress needs are, our team is dedicated to delivering exceptional service and results. Contact us today to learn more about how we can help you grow your online presence with WordPress. */
?>
<div class="wrap">
    <h1><?php esc_html_e('Post Metadata Manager Help', 'pmdm_wp'); ?></h1>
    <h3 class=""></h3>
    <footer class="tribe-events-admin-cta">
		

		<div class="tribe-events-admin-cta__content">
			<h2 class="tribe-events-admin-cta__content-title">
                <?php echo $content; ?>
			</h2>

			<div class="tribe-events-admin-cta__content-description">
				<a href="<?php echo PMDM_HELP_LINK; ?>">
					<?php esc_html_e( 'Contact us for help', 'pmdm_wp' ); ?>
				</a>
			</div>
		</div>
	</footer>
</div>