<?php

get_header();

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
				$date = (isset($_GET['date'])) ? $_GET['date'] : date('Y-m-d');
				
				if (!preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $date)) {
					$date = date('Y-m-d');
				}		

				$timeToday = strtotime($date);

				$currentYear = date('Y');

				$max = strtotime("+7 day", $timeToday);
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
        <style type="text/css">
        	.linkDate{
        		width: 120px;
			    padding: 2px;
			    float:left;
			    text-align: center;
			    border: 1px solid #fff;
        	}
        	.linkDate:hover{
			    border: 1px solid #000;
        	}
        </style>
        <div class="wrap">
         	<div id="listYear" style="display: block;width:100%;"></div>
	        <div id="listDate" style="display: block;width:100%;"></div>
        </div><!-- .wrap -->
        <div class="wrap">
			<?php
			$week  = IntencjeMszalne::getWeekName();
			$month = IntencjeMszalne::getRomanNumbers();
			
			$y = [];

			$legend = [];

			$lastTime = NULL;
			if (have_posts()) :
			while (have_posts()) :
			the_post();
			
			
			$time = strtotime(get_post_meta(get_the_ID(), "im_date", TRUE));
			$w = intval(date('w', $time));
			if (in_array($w, [0])) {
				$y = intval(date('Y'));
				$m = intval(date('m'));

				$tmp = date('Y-m-d', $time);
				if (!in_array($tmp, $legend)) {
					$legend[] = $tmp;
				}

				$tmp2 = date('Y',$time);
				if (!in_array($tmp2, $y)) {
					$y[] = $tmp2;
				}
			}
			
			if ($time != $lastTime) :
            if ($timeToday > $time || $time > $max) {
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
		
		<script type="text/javascript">
			var a = <?php echo json_encode($legend);?>;
			var elm = document.getElementById('listDate');
			for (var i = 0; i < a.length; i++) {
			    var e = document.createElement('div');
				e.innerHTML = '<div class="linkDate"><a href="?date=' + a[i] + '">' + a[i] + '</a></div>';

				elm.appendChild(e.firstChild);
			}
			var a = <?php echo json_encode($y);?>;
			var elm = document.getElementById('listYear');
			for (var i = 0; i < a.length; i++) {
			    var e = document.createElement('div');
				e.innerHTML = '<div class="linkDate"><a>' + a[i] + '</a></div>';

				elm.appendChild(e.firstChild);
			}
		</script>
		<?php
		$wp_query = NULL;
		$wp_query = $original_query;
		wp_reset_postdata();
	?>
    </div><!-- .panel-content -->
</article><!-- #post-## -->
<?php

get_footer(); 