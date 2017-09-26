<?php
/**
 * Created by PhpStorm.
 * @package          wp_parafia
 * @createdate       2017-04-24 07:33
 * @lastmodification 2017-04-24 07:33
 * @version          0.0.1
 * @author           Piotr Kuźnik <piotr.damian.kuznik@gmail.com>
 */

get_header();

while (have_posts()) : the_post();
	$contentW = 12;
	//	if (get_field('sidebar_aktywny')) :
	//		echo '<div class="layout with-left-sidebar js-layout">
	//				<div class="row">';
	//		get_template_part('content', 'sidebar');
	//		$contentW = 9;
	//
	//	endif;
	?>

    <article id="post-<?php the_ID(); ?>" <?php post_class('twentyseventeen-panel '); ?> >
        <div class="panel-content">
            <div class="wrap" style="padding-bottom:0px;">
                <header class="entry-header" style="padding-bottom:0px;">
					<?php the_title('<h2 class="entry-title">', '</h2>'); ?>
					
					<?php twentyseventeen_edit_link(get_the_ID()); ?>

                </header><!-- .entry-header -->
					<?php
					/* translators: %s: Name of current post */
					the_content();
					
					$timeToday = strtotime(date('Y-m-d'));
					$original_query = $wp_query;
					$wp_query       = NULL;
					$args           = [
						'post_type'      => IntencjeMszalne::POST_TYPE,
						'orderby'        => 'title',
                        'order'          => 'ASC',
                        'posts_per_page' => -1
					];
					$wp_query       = new WP_Query($args);
					?>

            
            </div><!-- .wrap -->
            <div class="wrap">
				<?php
				$week  = IntencjeMszalne::getWeekName();
				$month = IntencjeMszalne::getRomanNumbers();
				
				$lastTime = NULL;
				if (have_posts()) :
				while (have_posts()) :
				the_post();
				
				
				$time = strtotime(get_post_meta(get_the_ID(), "im_date", TRUE));
				
				
				if ($time != $lastTime) :
                if ($timeToday > $time) {
				    continue;
                }
				if (!is_null($lastTime)) {
					echo '</header>';
				}
				?>
                <header class="entry-header" style="width: 100%; display: block;margin-bottom: 20px;">
                    <h3>
						<?php echo($week[ date('w', $time) ]); ?>
                        -
						<?php echo date('d', $time); ?>
						<?php echo $month[ intval(date('m', $time)) ]; ?>
						<?php echo date('Y').'r.' ?>
						<?php echo (!empty(get_post_meta(get_the_ID(), "im_patron", TRUE))) ? ' - '.get_post_meta(get_the_ID(), "im_patron", TRUE) : ''; ?>
                    </h3>
					<?php
					endif;
					$lastTime = $time;
					$intencja = [];
					for ($i = 1; $i <= 5; $i++) {
						$intencja[] = get_post_meta(get_the_ID(), "im_intencja_".$i, TRUE);
					}
					
					$intencja = array_filter($intencja);
					?>
                    <table cellspacing="0" cellpadding="0" style="border: none; padding: 0;margin: 0;">
                        <tbody style="border: none;">
						<?php for ($i = 0; $i < count($intencja); $i++) { ?>
                            <tr style="border: none; padding: 0;">
                                <td style="vertical-align: top;border: none; width:50px; padding: 0;"><?php echo ($i === 0) ? get_post_meta(get_the_ID(), "im_hour", TRUE) : ''; ?></td>
								<?php if (count($intencja) > 1) { ?>
                                    <td style="vertical-align: top;border: none;width: 20px;padding: 0;text-align: center;"><?php echo $month[ $i + 1 ];
										?></td>
								<?php } ?>
                                <td style="border: none;padding: 0;">- <?php echo $intencja[ $i ]; ?></td>
								
                                <td style="border: none;width: 100px;padding: 0;text-align: center;"><?php
									if ($i === 0) {
										twentyseventeen_edit_link(get_the_ID());
									} ?>
                                </td>
								
                            </tr>
						<?php } ?>
                        </tbody>
                    </table>
					
					
					<?php
					endwhile;
					endif;
					?>
            </div>
            <div class="entry-content">
				<?php
				wp_link_pages([
					'before'      => '<div class="page-links">'.__('Pages:', 'twentyseventeen'),
					'after'       => '</div>',
					'link_before' => '<span class="page-number">',
					'link_after'  => '</span>',
				]);
				?>
            </div><!-- .entry-content -->
			
			<?php
			$wp_query = NULL;
			$wp_query = $original_query;
			wp_reset_postdata();
			?>
        </div><!-- .panel-content -->
    </article><!-- #post-## -->
	<?php
endwhile;
?>


<?php get_footer(); ?>