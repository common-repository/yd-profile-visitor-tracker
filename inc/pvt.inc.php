<?php
class pvtPlugin extends YD_Plugin {

	const DEBUG = false;	//bool true|false
	const DEBUG_LEVEL = 1;	//int 1|2
	private $field_names = array(
		'visitor' 		=> 'Visitor',
		'last_visit'	=> 'Last visit date',
		'visit_count'	=> 'Visit count',
		'visit_pv'		=> 'Last visit PV',
		'total_pv'		=> 'Total visitor PV',
		'opt_data'		=> 'Optional data',
		'xp_Name'		=> 'Name',
		'xp_First Name'	=> 'First name'
	);
	private $field_texts = array(
		'last_visit'	=> 'Last visit on:',
		'visit_count'	=> 'Total visit count:'
	);
	private $get_usermeta = array (
		'nickname',
		'first_name',
		'last_name',
		'description',
		'from',
		'occ',
		'last_posted',
		'interest',
		'jabber',
		'yim',
		'aim'
	);
	
	/** constructor **/
	function pvtPlugin ( $opts ) {
		$this->processTexts( &$opts );
		parent::YD_Plugin( $opts );
		//add_action( 'admin_init', array( &$this, 'init_css' ) );
		
		$this->form_blocks		= $opts['form_blocks']; // No backlinkware
		
		$options = get_option( $this->option_key );
		if( $options['autotrack'] ) {
			add_action( 'bp_before_member_header', array( &$this, 'track' ) );
		}
		if( $options['autopage'] ) {
			add_action( 'bp_member_options_nav', array( &$this, 'visitor_page_menu' ) );
		}
		add_action( 'pvt_show', array( &$this, 'display_visitors' ) );
	}
	
	function processTexts( $texts_array ) {
		if( !is_array( $texts_array ) ) return;
		foreach( $texts_array as $key => $value) {
			if( !is_string( $value ) ) continue;
			$value = str_replace( $replace_what, $replace_by, $value );
			$texts_array[$key] = $value;
		}
		return $texts_array;
	}
	
	function visitor_page_menu( $args ) {
		global $bp;
		$link = $bp->displayed_user->domain . 'visitors';
		//echo '<li id="pvt_visitors" class="pvt visitors"><a href="' . $link . '">' 
		//	. __( 'Visitors', $this->tdomain ) 
		//	. '</a></li>';
	}
	
	function do_shortcode( $atts = array() ) {
		$html = '';

		if( self::DEBUG ) $html .= 'SHORTCODE...<br/>';
		
		$atts = shortcode_atts( 
			array(
				'profile_id' 	=> 0,
				'filter'		=> '',		//field=cond
				'sort_by'		=> 0,		//field (0=date)
				'sort_order'	=> 'desc',	//asc|desc
				'style'			=> 'table',	//table|ul|none
				'limit'			=> 20,
				'offset'		=> 0,
				'date_format'	=> __( 'j F Y', $this->tdomain ),
				'show_tableh'	=> false,		//display table field name header
				'fields'		=> 'visitor,wp_display_name,last_visit,visit_count',
				'after_text'	=> '',
				'after_link'	=> '',
				'before_text'	=> '',
				'avatar_size'	=> 60,
				'friend_button'	=> true,
				'email_button'	=> true
			), 
			$atts
		);
		$html .= self::display_visitors( $atts );
		
		return $html;
	}
	
	function display_visitors( $atts = array() ) {
		$html = '';
		$defaults = array(
			'profile_id' 	=> 0,
			'filter'		=> '',		//field=cond
			'sort_by'		=> 0,		//field (0=date)
			'sort_order'	=> 'desc',	//asc|desc
			'style'			=> 'table',	//table|ul|none
			'limit'			=> 20,
			'offset'		=> 0,
			'date_format'	=> __( 'j F Y', $this->tdomain ),
			'show_tableh'	=> false,		//display table field name header
			'fields'		=> 'visitor,wp_display_name,last_visit,visit_count',
			'after_text'	=> '',
			'after_link'	=> '',
			'before_text'	=> '',
			'avatar_size'	=> 60,
			'friend_button'	=> true,
			'email_button'	=> true
		);
		$r = wp_parse_args( $atts, $defaults );
		extract( $r, EXTR_SKIP );
		if( self::DEBUG ) {
			$html .= 'DISPLAY FUNCTION...<br/>';
			var_dump( $atts );
		}
		
		// Profile ID
		if( !$profile_id ) 
			$profile_id = self::current_profile();
		if( self::DEBUG ) echo 'profile_id: ' . $profile_id . '<br/>';
	
		// Visited data
		$visited_data = get_user_meta( $profile_id, 'visitors', true );
		if( !is_array( $visited_data ) ) {
			if( self::DEBUG ) echo 'not array<br/>';
			$visited_data = array();
		}
		$count_data = count( $visited_data );
		if( self::DEBUG ) echo 'count: ' . $count_data . '<br/>';
		if( self::DEBUG && self::DEBUG_LEVEL >= 2 ) { echo "<pre>META\n"; var_dump( $visited_data ); echo "</pre>"; }
		$visited_data = self::process_keys( &$visited_data );
		
		//filtering
		if( $filter ) {
			list( $filterkey, $filtercond ) = preg_split( '/=/', $filter );
		}
		if( self::DEBUG ) echo 'filter: ' . $filter . ' - ' . $filterkey . ' = ' . $filtercond . '<br/>';
		
		//array sort 
		if( self::DEBUG ) echo 'sort by: ' . $sort_by . ' - order: ' . $sort_order . '<br/>';
		$visited_data = self::flexible_sort( &$visited_data, $sort_by, $sort_order );
		if( self::DEBUG && self::DEBUG_LEVEL >= 2 ) { echo "<pre>SORTED\n"; var_dump( $visited_data ); echo "</pre>"; }
		
		//rendering style selection: table|ul|none
		if( $style == 'table' ) {
			$outerw = 'table';
			$itemw = 'tr';
			$fieldw = 'td';
			$titlew = 'th';
		}
		if( $style == 'ul' ) {
			$outerw = 'ul';
			$itemw = 'li';
			$fieldw = 'span';
			$titlew = 'span';
		}
		if( self::DEBUG ) echo "style: " . $style . "<br/>";
		
		$show_fields = preg_split( '/,/', $fields );
		
		if( $style != 'none' ) $html .= '<div class="pvt_wrap">';
		$result = array();
		if( $count_data > 0 ) {
			$html .= __( $before_text, $this->tdomain );
			$html .= "<{$outerw} class=\"pvt\">";
			if( $show_tableh ) {
				$html .= " <{$itemw} class=\"pvt header\">";
				
				foreach( $show_fields as $field ) {
				
					if( isset( $this->field_names[$field] ) ) {
						$field_name = __( $this->field_names[$field], $this->tdomain );
					} else {
						$field_name = __( $field, $this->tdomain );
					}
					$html 	.= "  <{$titlew} class=\"pvt title $field_name\">" 
							. $field_name 
							. "</{$titlew}>";
							
				}
				$html .= " </{$itemw}>";
			}
			$totalcount = 0;
			$count = 0;
			foreach( $visited_data as $record_key => $data ) {
				//echo "count: $count - limit: $limit<br/>";
				if( !isset( $_GET['more'] ) && ( $count >= $limit ) ) break;
				
				if( $filter && $data[ $filterkey ] != $filtercond ) continue;
				$totalcount ++;
				if( $offset && $totalcount <= $offset ) continue;		
				$count ++;
				
				$result[] = $data;
				$html .= "<{$itemw} class=\"pvt data\">";
				
				foreach( $show_fields as $field ) {
					if( $field == 'visitor' ) {
						if( function_exists( 'bp_core_get_core_userdata' ) ) {
							//BP-based visitor profile
							$visitor_data = bp_core_get_core_userdata( $data['visitor_id'] );
							//var_dump( $visitor_data );
							$html .= " <{$fieldw} class=\"pvt field user\">" 
									. '<a href="' . bp_core_get_user_domain( $data['visitor_id'] ) . '">'
									. bp_core_fetch_avatar( 
										array( 
											'item_id'	=> $data['visitor_id'],
											'width'		=> $avatar_size,
											'height'	=> $avatar_size
										)
									) 
									. "</a>"
									//. '<span class="visitor_name">'
									//. '<a href="' . bp_core_get_user_domain( $data['visitor_id'] ) . '">'
									//. $visitor_data->user_login
									//. '</a>'
									//. '</span>'
									//. 'visitor id: ' . $data['visitor_id']
									//. 'record_key: ' . $record_key
									. "</{$fieldw}>";
						} else {
							//WP-based visitor profile + bbPress profile link if exists
							$visitor_data = get_userdata( $data['visitor_id'] );
							if( function_exists( 'get_user_profile_link' ) ) {
								// bbPress profile link
								$link = get_user_profile_link( $data['visitor_id'] );
							} else {
								$link = $visitor_data->user_url;
							}				
							$html .= " <{$fieldw} class=\"pvt field user\">" 
								. '<a href="' . $link . '">'
								. get_avatar( $visitor_data->user_email, $avatar_size )
								. '</a>'
								. "</{$fieldw}>";
						}
					} else {
						if( isset( $this->field_texts[$field] ) ) {
							$field_text = __( $this->field_texts[$field], $this->tdomain );
						} else {
							$field_text = '';
						}
						if( $field == 'last_visit' ) {
							$field_value = date( $date_format, $data['last_visit'] );
						} elseif( $field == 'wp_display_name' ) {
							if( function_exists( 'get_user_profile_link' ) ) {
								// bbPress profile link
								$link = get_user_profile_link( $data['visitor_id'] );
							} else {
								$link = $visitor_data->user_url;
							}
							$field_value = '<a href="' . $link . '">' . $data['wp_display_name'] . '</a>';
						} else {
							$field_value = $data[$field];
						}
						$html .= " <{$fieldw} class=\"pvt field $field\">" 
						. $field_text . " " 
						. $field_value 
						. "</{$fieldw}>";
					}
				}
				
				if( $style == 'ul' ) $html .= '<div class="clearleft">';
				
				/** Add Friend **/
				if( function_exists( 'friends_check_friendship_status' ) && $friend_button ) {
					$html .= "<{$fieldw} class=\"pvt friend_button\">";
					global $bp;
					$friend_status = friends_check_friendship_status( $bp->loggedin_user->id, $data['visitor_id'] );			
					if ( $bp->loggedin_user->id != $data['visitor_id'] && $friend_status != 'is_friend' ) {
						$html .= bp_get_add_friend_button( $data['visitor_id'] );
					} else {
						$html .= '<!-- already friend -->';
					}
					$html .= "</{$fieldw}>";
				}
				/** **/
				
				/** Send private email **/
				if( function_exists( 'bp_get_send_message_button' ) && $email_button ) {
					if ( $bp->loggedin_user->id != $data['visitor_id'] ) {
						$html .= "<{$fieldw} class=\"pvt email_button\">";
						global $bp;
						$visitor_data = bp_core_get_core_userdata( $data['visitor_id'] );
						$link = apply_filters( 
							'bp_get_send_private_message_link', 
							wp_nonce_url( 
								$bp->loggedin_user->domain 
								. $bp->messages->slug 
								. '/compose/?r=' 
								. bp_core_get_username( 
									$data['visitor_id'], 
									$visitor_data->user_nicename, 
									$visitor_data->user_login
								)
							)
						);
						$html .= apply_filters( 
							'bp_get_send_message_button',
							bp_get_button( 
								array(
									'id'                => 'private_message',
									'component'         => 'messages',
									'must_be_logged_in' => true,
									'block_self'        => true,
									'wrapper_id'        => 'send-private-message-' . $count,
									'link_href'         => $link,
									'link_class'        => 'send-message',
									'link_title'        => __( 'Send a private message to this user.', 'buddypress' ),
									'link_text'         => __( 'Send Private Message', 'buddypress' )
								)
							)
						);
						$html .= "</{$fieldw}>";
					}
				}
				/** **/
				
				if( $style == 'ul' ) $html .= '</div>';
				
				$html .= "</{$itemw}>";
			}
			$html .= "<br class=\"brclear\" /></{$outerw}>";
			if( $after_text ) {
				if( $after_link )
					$html .= '<a href="' . $after_link . '" class="pvt after">';
				$html .= __( $after_text, $this->tdomain );
				if( $after_link )
					$html .= '</a>';
			}
		}
		if( $style != 'none' ) $html .= '</div><!-- /pvt_wrap -->';
		
		if( $display === false ) {
			return $result;
		} else {
			return $html;
		}
	}
	
	function process_keys( &$array ) {
		if( !is_array( $array ) )
			return $array;
		
		foreach( $array as $key => &$data) {
			$data['visit_key']		= $key;
			list( 
			$data['visitor_id'], 
			$data['opt_data'] 
			) = preg_split( '/_/', $key );
			$data['last_visit']	= $data[0];
			$data['visit_count']	= $data[2];
			$data['visit_pv']		= $data[3];
			$data['total_pv']		= $data[4];
			
			// Fetch BuddyPress extended profile (xp) data
			if ( function_exists( 'bp_has_profile' ) && bp_has_profile( 'user_id=' . $data['visitor_id'] ) ) {
				while ( bp_profile_groups() ) {
					bp_the_profile_group();
					if ( bp_profile_group_has_fields() ) {
						while ( bp_profile_fields() ) {
							bp_the_profile_field();
							$name = bp_get_the_profile_field_name();
							$value = bp_get_the_profile_field_value();
							$data['xp_' . $name] = strip_tags( $value );
						}
					}
				}
			}
			
			// Fetch WordPress usermeta (um) data
			if( function_exists( 'get_user_meta' ) ) {
				foreach( $this->get_usermeta as $key ) {
					$data['um_' . $key] = get_user_meta( $data['visitor_id'], $key, true );
				}
			}
			
			// Fetch other (regular, default) WordPress (wp) user data
			if( function_exists( 'get_userdata' ) ) {
				$visitor_data = get_userdata( $data['visitor_id'] );
				foreach( $visitor_data as $key => $value ) {
					$data['wp_' . $key] = strip_tags( $value );
				}
			}

		}
			
		return $array;
	}
	
	function flexible_sort( &$array, $sort_field=0, $sort_order='asc' ) {
		if( !is_array( $array ) ) 
			return $array;
			
		/** **/
		$this->sort_order = $sort_order;
		$this->sort_field = $sort_field;
		uasort( 
			&$array, 
			array( &$this, 'sort_callback' )
		);
		/** **/
		
		return $array;
	}
	
	function sort_callback( $a, $b ) {
		if( $this->sort_order ) {
			$sort_order = $this->sort_order;
		} else {
			$sort_order = 'asc';
		}
		if( $this->sort_field ) {
			$sort_field = $this->sort_field;
		} else {
			$sort_field = 0;
		}
		if( $this->sort_order == 'desc' ) {
			return $a[$this->sort_field] < $b[$this->sort_field] ? 1 : -1;
		} else {
			return $a[$this->sort_field] > $b[$this->sort_field] ? 1 : -1;
		}
	}
	
	/**
	 * 
	 * SERIALIZED ARRAY STRUCTURE:
	 * 
	 * array(
	 * 	$visitor_id => array (
	 * 		$last_visit = timestamp(),
	 *  	$key, (from session cookie)
	 *  	$visit_count,
	 *  	$visit_pv_count,
	 *  	$total_pv_count
	 *  )
	 * )
	 * 
	 */
	function track( $atts = array() ) {
		if( isset( $this ) ) {
			$options = get_option( $this->option_key );
			$defaults = $options['autoattr'];
		}
		if( is_array( $atts ) && !empty( $atts ) ) {
			$r = wp_parse_args( $atts, $defaults );
		} else {
			$r = wp_parse_args( $defaults );
		}
		extract( $r );
		
		//if( is_array( $atts ) ) extract( $atts );
		
		if( self::DEBUG ) echo 'TRACKING...<br/>';
		if( is_user_logged_in() ) {
			
			global $current_user; 								//WP
			global $bp;											//BP
			if( !$current_user->ID ) get_currentuserinfo(); 	//WP
			
			$visitor_id = $current_user->ID;					//WP
			if( function_exists( 'bp_loggedin_user_id' ) ) 
				$visitor_id = bp_loggedin_user_id(); 			//BP
			if( function_exists( 'bb_get_current_user_info' ) ) 
				$visitor_id = bb_get_current_user_info( 'id' );	//BB (untested yet!)
			if( self::DEBUG ) echo 'visitor_id: ' . $visitor_id . '<br/>';
				
			if( !$profile_id ) $profile_id = self::current_profile();
			
			$visited_data = get_user_meta( $profile_id, 'visitors', true );
			if( !is_array( $visited_data ) ) {
				if( self::DEBUG ) echo 'not array<br/>';
				$visited_data = array();
			}
			$count_data = count( $visited_data );
			if( self::DEBUG ) echo 'count: ' . $count_data . '<br/>';
			
			$cookie = $_COOKIE[ LOGGED_IN_COOKIE ];
			$data = preg_split( '/\|/', $cookie );
			list( $login, $key, $hash ) = $data;
			if( self::DEBUG ) echo 'cookie: ' . $cookie . '<br/>';
			if( self::DEBUG ) echo 'key: ' . $key . '<br/>';
			
			$record_key = $visitor_id;
			if( $optcookie ) {
				$opt_data = $_COOKIE[ $optcookie ];
				$record_key .= '_' . $opt_data;
			}	

			$last_tracked = $visited_data[ $record_key ];
			list( 
				$last_timestamp,
				$last_key,
				$visit_count,
				$visit_pv_count,
				$total_pv_count
			) = $last_tracked;
			
			if( $last_key == $key ) {
				// same session
				if( self::DEBUG ) echo 'same session<br/>';
			} else {
				//different session
				if( self::DEBUG ) echo 'different session<br/>';
				if( $count_data >= 200 ) {
					//array_shift( &$visited_data );
					//This will drop the oldest line (hopefully)
					//ref: http://www.php.net/manual/fr/function.array-shift.php#30308
					reset( $visited_data ); 
					list( $oldKey, $oldElement ) = each( $visited_data ); 
					unset( $visited_data[$oldKey] ); 
				}

				$visit_count ++;
				$visit_pv_count = 0;
			}
			$visit_pv_count ++;
			$total_pv_count ++;

			$tracker = array(
				time(),
				$key,
				$visit_count,
				$visit_pv_count,
				$total_pv_count
			);
			$visited_data[ $record_key ] = $tracker;
			
			if( self::DEBUG ) 
				{	
					echo 'record key: ' . $record_key . '<br/>'; 
					echo '<pre>'; var_dump( $visited_data ); echo '</pre>'; 
				}
			
			update_user_meta( $profile_id, 'visitors', $visited_data );
		}
	}
	
	function current_profile() {
		global $wp_query;									//WP
		global $bp;											//BP
		$curauth = $wp_query->get_queried_object();			//WP
		
		$profile_id =  $curauth->ID;						//WP
		if( function_exists( 'bp_loggedin_user_id' ) ) 
			$profile_id = $bp->displayed_user->id;			//BP
		if( self::DEBUG ) echo 'profile_id: ' . $profile_id . '<br/>';
		return $profile_id;
	}
}
?>