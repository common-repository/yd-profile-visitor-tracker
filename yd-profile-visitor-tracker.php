<?php
/**
 * @package YD_Profile-visitor-tracker
 * @author Yann Dubois
 * @version 0.1.9
 */

/*
 Plugin Name: YD Profile Visitor Tracker
 Plugin URI: http://www.yann.com/en/wp-plugins/yd-profile-visitor-tracker
 Description: A social oriented plugin to track who has been visiting your user profile in a BuddyPress or community-oriented WordPress / bbPress environment. | Funded by <a href="http://www.selliance.com">Selliance</a>
 Version: 0.1.9
 Author: Yann Dubois
 Author URI: http://www.yann.com/
 License: GPL2
 */

/**
 * @copyright 2010  Yann Dubois  ( email : yann _at_ abc.fr )
 *
 *  Original development of this plugin was kindly funded by http://www.selliance.com
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

/**
 Revision 0.1.9:
 - Some more fixes and improvements for bbPress / WP-alone setups [2011/01/01]
 - Now gets and can display all regular WP user fields (use wp_ prefix fo keys)
 - Now gets and can display all main bbPress and WP usermeta fields (use um_ prefix)
 - Automatic add link to bbPress profiles or WP user link
 - BR clear to resolve some CSS/display issues
 Revision 0.1.8:
 - Framework update to VERSION 20110328-01 [2011/03/28]
 - Very small debug statement bugfix
 - Bugfix: profile_id can now be passed as parameter to tracking template function
 - Standalone bbPress user profile integration checked and effective (without BuddyPress)
 - Added "before_text" parameter
 - Updated doc 
 Revision 0.1.7:
 - Bugfix: removed visitors tab [2011/03/28]
 Revision 0.1.6:
 - Official first release [2011/03/28]
 Revision 0.1.5:
 - Beta RC2 [2011/03/25]
 Revision 0.1.4:
 - Beta RC1 [2011/03/24]
 Revision 0.1.3:
 - Original alpha release 3 [2011/03/24]
 Revision 0.1.2:
 - Original alpha release 2
 Revision 0.1.1:
 - Original alpha release 1
 Revision 0.1.0:
 - Original alpha release 0
 */

/** Misc. Texts **/

global $texts; 
$texts = array (

	'option_page_title' => 'YD Profile Visitor Tracker Configuration'

);

/** Class includes **/

include_once( 'inc/yd-widget-framework.inc.php' );	// standard framework VERSION 20110323-01 or better
include_once( 'inc/pvt.inc.php' );					// custom classes

/**
 * 
 * Just fill up necessary settings in the configuration array
 * to create a new custom plugin instance...
 * 
 */
global $pvt_o;
$pvt_o = new pvtPlugin( 
	array(
		'name' 				=> 'YD Profile Visitor Tracker',
		'version'			=> '0.1.9',
		'has_option_page'	=> true,
		'option_page_title' => $texts['option_page_title'],
		'op_donate_block'	=> false,
		'op_credit_block'	=> true,
		'op_support_block'	=> true,
		'has_toplevel_menu'	=> false,
		'has_shortcode'		=> true,
		'shortcode'			=> 'yd_visitor_profiles',
		'has_widget'		=> false,
		'widget_class'		=> '',
		'has_cron'			=> false,
		'crontab'			=> array(
			//'daily'			=> array( 'YD_MiscWidget', 'daily_update' ),
			//'hourly'		=> array( 'YD_MiscWidget', 'hourly_update' )
		),
		'has_stylesheet'	=> false,
		'stylesheet_file'	=> 'css/yd.css',
		'has_translation'	=> true,
		'translation_domain'=> 'ydpvt', // must be copied in the widget class!!!
		'translations'		=> array(
			array( 'English', 'Yann Dubois', 'http://www.yann.com/' ),
			array( 'French', 'Yann Dubois', 'http://www.yann.com/' )
		),		
		'initial_funding'	=> array( 'Selliance', 'http://www.selliance.com' ),
		'additional_funding'=> array(),
		'form_blocks'		=> array(
			'Main options' => array( 
				'autotrack'	=> 'bool',
				'autoattr'	=> 'text',
				//'autopage'	=> 'bool'
			)
		),
		'option_field_labels'=>array(
				'autotrack'	=> 'Auto track visitors on all profile pages',
				'autoattr'	=> 'Default tracking attributes',
				//'autopage'	=> 'Add visitors tracking page to member menu'
		),
		'option_defaults'	=> array(
				'autotrack'	=> true,
				'autoattr'	=> '',
				//'autopage'	=> true
		),
		'form_add_actions'	=> array(
				//'Manually run hourly process'	=> array( 'YD_MiscWidget', 'hourly_update' ),
				//'Check latest'				=> array( 'YD_MiscWidget', 'check_update' )
		),
		'has_cache'			=> false,
		'option_page_text'	=> 'Welcome to the YD Profile Visitor Tracker settings page. If you do not use autotracking, here is the template tag: &lt;?php if( is_callable( array( \'pvtPlugin\', \'track\' ) ) ) pvtPlugin::track( [array( \'optcookie\'=>\'whatever\' )] ); ?&gt;. <a href=\'http://wordpress.org/extend/plugins/yd-profile-visitor-tracker/installation/\'>See installation instructions for setup and usage documentation</a>.',
		'backlinkware_text' => '',
		'plugin_file'		=> __FILE__,
		'has_activation_notice'	=> false,
		'activation_notice' => '',
		'form_method'		=> 'post'
 	)
);

/**
 * 
 * You must specify a unique class name
 * to avoid collision with other plugins...
 * 
 */
class YD_VisitorProfileWidget extends YD_Widget {
    
	function do_things( $op ) {
		// do things
		$option_key = 'yd-plugin';
		$options = get_option( $option_key );
		
		$op->error_msg .= 'Great.';
		$op->update_msg .= 'Cool.';
		
		update_option( 'YD_P_last_action', time() );
	}
	
	function hourly_update( $op ) {
		if( !$op || !is_object( $op ) ) {
			$op = new YD_OptionPage(); //dummy object
		}
		self::do_things( &$op );
		update_option( 'YD_P_hourly', time() );
	}
	
	function daily_update( $op ) {
		if( !$op || !is_object( $op ) ) {
			$op = new YD_OptionPage(); //dummy object
		}
		self::do_things( &$op );
		update_option( 'YD_P_daily', time() );
	}
	
	function check_update( $op ) {
		$op->update_msg .= '<p>';
		if( $last = get_option( 'YD_P_daily' ) ) {
			$op->update_msg .= 'Last daily action was on: ' 
				. date( DATE_RSS, $last ) . '<br/>';
		} else { 
			$op->update_msg .= 'No daily action yet.<br/>';
		}
		if( $last = get_option( 'YD_P_hourly' ) ) {
			$op->update_msg .= 'Last hourly action was on: ' 
				. date( DATE_RSS, $last ) . '<br/>';
		} else { 
			$op->update_msg .= 'No hourly action yet.<br/>';
		}
		if( $last = get_option( 'YD_P_last_action' ) ) {
			$op->update_msg .= 'Last completed action was on: ' 
				. date( DATE_RSS, $last ) . '<br/>';
		} else { 
			$op->update_msg .= 'No recorded action yet.<br/>';
		}
		$op->update_msg .= '</p>';
	}
}
?>