<?php // Do not delete these lines
if (!empty($_SERVER['SCRIPT_FILENAME']) && 'comments.php' == basename($_SERVER['SCRIPT_FILENAME']))
die ('Please do not load this page directly. Thanks!');
if ( post_password_required() ) {
echo '<h2>Post protected, you need a password ;-)</h2>';
return;
}
?>

<?php if ($comments) : ?>
<h1 style="margin-top:25px;">Comments</h1>
<div id="comments">
<?php foreach ($comments as $comment) : ?>
<div id="commentbox">
	<?php if (the_author('', false) == get_comment_author()){ ?>
				<div id="avatar" style="background-image:url('<?php bloginfo('template_directory'); ?>/images/author_highlight.png'); background-repeat:repeat-x; background-color:white;"><?php echo get_avatar( $comment, 80 ); ?><br /><?php comment_author_link() ?><br /><?php comment_date('F jS, Y') ?></div>
				<?php   } else { ?>
				<div id="avatar"><?php echo get_avatar( $comment, 80 ); ?><br /><?php comment_author_link() ?><br /><?php comment_date('F jS, Y') ?></div>
				<?php  } ?>
				<div id="commentext"><?php comment_text() ?>	<?php if ($comment->comment_approved == '0') : ?>
					<em>Your comment is awaiting moderation.</em>
					<?php endif; ?>
		   			<?php edit_comment_link('edit','&nbsp;&nbsp;',''); ?>
				</div>
</div>
<?php endforeach; /* end for each comment */ ?>
</div>
 <?php else : // this is displayed if there are no comments so far ?>

	<?php if ('open' == $post->comment_status) : ?>
		<!-- If comments are open, but there are no comments. -->

	 <?php else : // comments are closed ?>
		<!-- If comments are closed. -->
			<h2>Comments are closed.</h2>
				<?php endif; ?>

<?php endif; ?>
<?php if ('open' == $post->comment_status) : ?>
<div id="respond">
<?php if ( get_option('comment_registration') && !$user_ID ) : ?>
<h2>You must be <a href="<?php echo get_option('siteurl'); ?>/wp-login.php?redirect_to=<?php echo urlencode(get_permalink()); ?>">logged in</a> to send a comment.</h2>
<?php else : ?>
<form action="<?php echo get_option('siteurl'); ?>/wp-comments-post.php" method="post" id="commentform">
<?php if ( $user_ID ) : ?>
<h2>Logged in as  <a href="<?php echo get_option('siteurl'); ?>/wp-admin/profile.php"><?php echo $user_identity; ?></a>. <a href="<?php echo wp_logout_url(get_permalink()); ?>" title="Log out of this account">Log out &raquo;</a></h2>
			<h1>Leave a comment</h1>
					<textarea name="comment" id="comment" tabindex="4"></textarea><br />					
<?php else : ?>
			<h1>Leave a comment</h1>
					<div id="campo"><div id="label"><label for="author"><img src="<? bloginfo('template_directory'); ?>/images/label_nome.png" alt="name" /></label></div><input type="text" class="input" name="author" id="author" size="30" value="<?php echo $comment_author; ?>"  tabindex="1" /></div>
					<div id="campo"><div id="label"><label for="email"><img src="<? bloginfo('template_directory'); ?>/images/label_email.png" alt="email" /></label></div><input type="text" class="input" name="email" id="email" size="30" value="<?php echo $comment_email; ?>"  tabindex="2" /></div>
					<div id="campo"><div id="label"><label for="url"><img src="<? bloginfo('template_directory'); ?>/images/label_sitoweb.png" alt="website" /></label></div><input type="text" class="input" name="url" id="url" size="30" value="<?php echo $comment_url; ?>"  tabindex="3" /></div>
					<textarea name="comment" id="comment" tabindex="4"></textarea><br />
				<?php endif; ?>					
					<input name="submit" type="submit" id="submit" tabindex="5" value="" />
					<input type="hidden" name="comment_post_ID" value="<?php echo $id; ?>" />
<?php comment_id_fields(); ?>
<?php do_action('comment_form', $post->ID); ?>

</form>

<?php endif; // If registration required and not logged in ?>
</div>
<?php endif; // if you delete this the sky will fall on your head ?>
