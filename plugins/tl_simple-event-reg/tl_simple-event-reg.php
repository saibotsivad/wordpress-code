<?php
/*
Plugin Name: Simple Event Registration
Plugin URI: http://tobiaslabs.com/
Description: Register for an event, using a post custom field and shortcode.
Version: 0.4
Author: Tobias Labs
Author URI: http://tobiaslabs.com/
*/

$TL_SimpleEventReg = new TL_SimpleEventReg;

class TL_SimpleEventReg
{
	var $registration = false;

	function __construct()
	{
		add_action( 'admin_init', array( $this, 'AdminInit' ) );
		add_action( 'admin_menu', array( $this, 'AdminMenu' ) );
		add_action( 'save_post', array( $this, 'SavePost' ) );
		add_action( 'init', array( $this, 'Registration' ) );
		add_filter( 'the_content', array( $this, 'RegistrationForm' ) );
	}
	
	function AdminInit()
	{
		// I thought it should be array('post','page') but that doesn't seem to work?
		add_meta_box(
			'TL_SimpleEventReg',
			'Event Registration',
			array( $this, 'MetaBox' ),
			'post',
			'side',
			'low'
		);
		add_meta_box(
			'TL_SimpleEventReg',
			'Event Registration',
			array( $this, 'MetaBox' ),
			'page',
			'side',
			'low'
		);
	}
	
	function MetaBox()
	{
		global $post;
		$event = '';
		$name = '';
		$date = '';
		$maxpeople = '';
		$data = get_post_meta( $post->ID, 'tl_simple-event-reg_event', true );
		if ( !empty( $data ) )
		{
			$event = 'checked="true"';
			$name = $data['eventname'];
			$date = $data['eventdate'];
			$maxpeople = $data['maxpeople'];
		}
		?>
		<p id="tl_simple-event-reg_addevent">Add event to post? <input name="tl_simple-event-reg_event" type="checkbox" <?php echo $event; ?> /> <small>(Uncheck to delete!)</small></p>
		<div id="tl_simple-event-reg_eventdetails">
			<?php wp_nonce_field( 'tl_simple-event-reg_nonce', 'tl_simple-event-reg_nonce' ); ?>
			<p>Name: <input name="tl_simple-event-reg_name" type="text" size="30" value="<?php echo $name; ?>" /></p>
			<p>Date: <input name="tl_simple-event-reg_date" type="text" size="16"value="<?php echo $date; ?>" /></p>
			<p>Maximum People: <input name="tl_simple-event-reg_maxpeople" type="text" size="3" value="<?php echo $maxpeople; ?>" /> <small>0 for no limit</small></p>
		</div>
		<?php
	}
	
	function SavePost()
	{
		global $post;
		// if checked, update data
		if ( isset( $_POST['tl_simple-event-reg_event'] ) && wp_verify_nonce( $_POST['tl_simple-event-reg_nonce'], 'tl_simple-event-reg_nonce' ) && current_user_can( 'edit_posts', $post->ID ) )
		{
			$data = array();
			$data['eventname'] = ( isset( $_POST['tl_simple-event-reg_name'] ) ? $_POST['tl_simple-event-reg_name'] : '' );
			$data['eventdate'] = ( isset( $_POST['tl_simple-event-reg_date'] ) ? $_POST['tl_simple-event-reg_date'] : '' );
			$data['maxpeople'] = ( isset( $_POST['tl_simple-event-reg_maxpeople'] ) ? $_POST['tl_simple-event-reg_maxpeople'] : '' );
			update_post_meta( $post->ID, 'tl_simple-event-reg_event', $data );
		}
		// if unchecked, remove the event and the list of people
		elseif ( is_object( $post ) && wp_verify_nonce( $_POST['tl_simple-event-reg_nonce'], 'tl_simple-event-reg_nonce' ) && current_user_can( 'edit_posts', $post->ID ) )
		{
			delete_post_meta( $post->ID, 'tl_simple-event-reg_event' );
			delete_post_meta( $post->ID, 'tl_simple-event-reg_attendees' );
		}
	}
	
	function AdminMenu()
	{
		add_submenu_page( 'edit.php', 'Manage Events', 'Manage Events', 'edit_posts', 'tl_simple-event-reg_manage', array( $this, 'EventManager' ) );
	}
	
	function EventManager()
	{
		if ( current_user_can( 'edit_posts' ) )
		{
			if ( is_numeric( @$_GET['tl_simple-event-reg_attendees'] ) )
			{
				if ( is_numeric( @$_GET['tl_simple-event-reg_deleteuser'] ) )
				{
					$attendees = get_post_meta( (int)$_GET['tl_simple-event-reg_attendees'], 'tl_simple-event-reg_attendees' );
					$attendees = $attendees[0];
					unset( $attendees[ (int)$_GET['tl_simple-event-reg_deleteuser'] ] );
					update_post_meta( (int)$_GET['tl_simple-event-reg_attendees'], 'tl_simple-event-reg_attendees', $attendees );
					
				}
				$attendees = get_post_meta( (int)$_GET['tl_simple-event-reg_attendees'], 'tl_simple-event-reg_attendees' );
				if ( is_array( $attendees ) ) $attendees = $attendees[0];
				?>
				<div class="wrap">
					<div id="icon-upload" class="icon32"></div>
					<h2>Registered Attendees</h2>
					<div id="ajax-response"></div>
					<table class="widefat">
						<thead>
							<tr>
								<th id="">Last Name</th>
								<th id="">First Name</th>
								<th id="">Email</th>
								<th id="">Phone Number</th>
							</tr>
						</thead>
						<tbody>
						<?php
						foreach ( $attendees as $key => $person )
						{
							?>
							<tr>
								<td id=""><?php echo $person['name_last']; ?>
									<div class="row-actions">
										<span><a href="edit.php?<?php echo $_SERVER['QUERY_STRING']; ?>&tl_simple-event-reg_deleteuser=<?php echo $key; ?>">Delete Attendee</a></span>
									</div>
								</td>
								<td id=""><?php echo $person['name_first']; ?></td>
								<td id=""><?php echo $person['email']; ?></td>
								<td id=""><?php echo $person['phone']; ?></td>
							</tr>
							<?php
						}
						?>
						</tbody>
					</table>
				</div>
				<?php
			}
			else
			{
				// grab the list of posts that have events attached
				$args = array(
					'meta_key' => 'tl_simple-event-reg_event',
					'post_type' => array( 'post', 'page' )
				);
				$query = new WP_Query( $args );
				// html wrapper
				?>
				<div class="wrap">
					<div id="icon-upload" class="icon32"></div>
					<h2>Manage Events</h2>
					<div id="ajax-response"></div>
					<form id="tl_simple-event-reg_event-list" action="edit.php?page=tl_simple-event-reg_manage" method="post">
						<?php wp_nonce_field('tl_simple-event-reg_event-list','tl_simple-event-reg_event-list'); ?>
						<table class="widefat">
							<thead>
								<tr>
									<th id="">Post Name</th>
									<th id="">Event Name</th>
									<th id="">Event Date</th>
									<th id="">Attending / Maximum People</th>
								</tr>
							</thead>
							<tbody>
				<?php
				if ( $query->have_posts() )
				{
					while ( $query->have_posts() )
					{
						$query->the_post();
						$data = get_post_meta( get_the_ID(), 'tl_simple-event-reg_event', true );
						$name = $data['eventname'];
						$date = $data['eventdate'];
						$maxpeople = $data['maxpeople'];
						$attending = get_post_meta( get_the_ID(), 'tl_simple-event-reg_attendees', true );
						$attending = ( $attending == '' ? 0 : count( $attending ) );
						?>
						<tr>
							<td id=""><?php the_title(); ?>
								<div class="row-actions">
									<a href="post.php?post=<?php the_ID(); ?>&action=edit" title="Edit post">Edit Event</a>
								</div>
							</td>
							<td id=""><?php echo $name; ?></td>
							<td id=""><?php echo $date; ?></td>
							<td><?php echo $attending; ?> / <?php echo $maxpeople; ?>
								<?php if( $attending == 0 ) : ?>
								<div class="row-actions">
									<span style="color:#999999;">View Attendees</span>
								</div>
								<?php else : ?>
								<div class="row-actions">
									<a href="edit.php?page=tl_simple-event-reg_manage&tl_simple-event-reg_attendees=<?php the_ID(); ?>" title="View Attendees">View Attendees</a>
								</div>
								<?php endif; ?>
							</td>
						</tr>
						<?php
					}
				}
				else
				{
					echo 'No events found.';
				}
				?>
							</tbody>
						</table>
					</form>
				</div>
				<?php
				wp_reset_postdata();
			}
		}
	}
	
	function RegistrationForm( $postcontent )
	{
		global $post;
		$data = get_post_meta( $post->ID, 'tl_simple-event-reg_event', false );
		if( !empty( $data ) )
		{
			$htmlform = '<div id="tl_simple-event-reg">';
			if ( is_string( $this->registration ) )
			{
				$htmlform .= '<p id="tl_simple-event-reg_regsuccess">You have registered successfully, '.$this->registration.'. Thank you!</p>';
			}
			else
			{
				$attendees = get_post_meta( $post->ID, 'tl_simple-event-reg_attendees', false );
				$spotsavailable = (int)$data[0]['maxpeople'] - count( $attendees[0] );
				if ( $spotsavailable > 0 )
				{
					$htmlform .= "<script type='text/javascript'>function tlSimpleEventReg(){document.getElementById('tl_simple-event-reg_replaceifjs').innerHTML='";
					$htmlform .= '<form id="tl_simple-event-reg_registerform" method="post" action="">';
					$htmlform .= '<input name="tl_simple-event-reg_postid" value="'.$post->ID.'" type="hidden" />';
					$htmlform .= '<input name="tl_simple-event-reg_register" value="'.wp_create_nonce( 'tl_simple-event-reg_register' ).'" type="hidden" />';
					$htmlform .= 'Last Name: <input name="tl_simple-event-reg_lastname" type="text" /><br />';
					$htmlform .= 'First Name: <input name="tl_simple-event-reg_firstname" type="text" /><br />';
					$htmlform .= 'Email: <input name="tl_simple-event-reg_email" type="text" /><br />';
					$htmlform .= 'Phone: <input name="tl_simple-event-reg_phone" type="text" /><br />';
					$htmlform .= ' <input name="tl_simple-event-reg_submit" type="submit" value="Register" />';
					$htmlform .= '</form>';
					$htmlform .= "';}window.onload=tlSimpleEventReg;</script>";
					$htmlform .= '<div id="tl_simple-event-reg_replaceifjs">You need JavaScript enabled to register, please enable it and refresh the page.</div>';
				}
				else
				{
					$htmlform .= '<p id="tl_simple-event-reg_apologiesfull">Our apologies, there are no open spots left available for this dance!</p>';
				}
			}
			$htmlform .= '</div>';
			$postcontent .= $htmlform;
			return $postcontent;
			}
	}
	
	function Registration()
	{
		if ( isset( $_POST['tl_simple-event-reg_submit'] ) && isset( $_POST['tl_simple-event-reg_postid'] ) && is_numeric( @$_POST['tl_simple-event-reg_postid'] ) && wp_verify_nonce( $_POST['tl_simple-event-reg_register'], 'tl_simple-event-reg_register' ) )
		{
			$attendees = get_post_meta( (int)$_POST['tl_simple-event-reg_postid'], 'tl_simple-event-reg_attendees', false );
			if ( is_array( $attendees ) ) $attendees = $attendees[0];
			$attendee = array();
			$attendee['name_last'] = $_POST['tl_simple-event-reg_lastname'];
			$attendee['name_first'] = $_POST['tl_simple-event-reg_firstname'];
			$attendee['email'] = $_POST['tl_simple-event-reg_email'];
			$attendee['phone'] = $_POST['tl_simple-event-reg_phone'];
			$attendees[] = $attendee;
			$result =  update_post_meta( (int)$_POST['tl_simple-event-reg_postid'], 'tl_simple-event-reg_attendees', $attendees );
			if ( $result === true )
			{
				$this->registration = $attendee['name_first'];
			}
		}
	}

}

?>