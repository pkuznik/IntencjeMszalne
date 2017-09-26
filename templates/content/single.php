<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

	<?php

	$time  = strtotime(get_post_meta(get_the_ID(), "im_date", TRUE));
	$week  = IntencjeMszalne::getWeekName();
	$month = IntencjeMszalne::getRomanNumbers();
	?>
    <header class="entry-header">
        <h1>
			<?php echo($week[ date('w', $time) ]); ?>
            -
			<?php echo date('d', $time); ?>
			<?php echo $month[ intval(date('m', $time)) ]; ?>
			<?php echo date('Y').'r.' ?>
			<?php echo (!empty(get_post_meta(get_the_ID(), "im_patron", TRUE))) ? ' - '.get_post_meta(get_the_ID(), "im_patron", TRUE) : ''; ?>
        </h1>
		<?php
		$intencja = [];
		for ($i = 1; $i <= 5; $i++) {
			$intencja[] = get_post_meta(get_the_ID(), "im_intencja_".$i, TRUE);
		}

		$intencja = array_filter($intencja);
		?>
        <table cellspacing="0" cellpadding="0" style="border: none; padding: 0;">
            <tbody style="border: none;">
            <?php for ($i = 0; $i < count($intencja); $i++) { ?>
                <tr style="border: none; padding: 0;">
                    <td style="border: none; width:50px; padding: 0;"><?php echo ($i === 0) ? get_post_meta
                        (get_the_ID(), "im_hour", TRUE) : ''; ?></td>
	                <?php if (count($intencja) > 1) { ?>
                    <td style="border: none;width: 20px;padding: 0;text-align: center;"><?php echo $month[ $i+1 ];
                    ?></td>
	                <?php } ?>
                    <td style="border: none;padding: 0;">- <?php echo $intencja[ $i ]; ?></td>
                    <?php if (is_admin()) {?>
                    <td style="border: none;width: 100px;padding: 0;text-align: center;"><?php
	                    if ($i === 0) {
		                    edit_post_link(sprintf(/* translators: %s: Name of current post */
			                    __('Edit<span class="screen-reader-text"> "%s"</span>', 'twentysixteen'), get_the_title()), '<span class="edit-link">', '</span>');
	                    }?>
                    </td>
                    <?php } ?>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </header>
</article><!-- #post-## -->
