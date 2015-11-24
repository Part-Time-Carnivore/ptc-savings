<?php
/**
 * Plugin Name: PTC Savings
 * Description: Calculates and displays the savings for individuals, teams or the whole community on Part-Time Carnivrore
 * Version: 1.0
 * Author: Pete Davis
 */

//CONTENTS
	//PTC SAVINGS WP_ADMIN SETTINGS
		//user_update_do_this_twicedaily
		//user_update_setup_schedule
		//statsinfo_register_widget
		//ptc_savings_init
		//ptc_savings_settings_land_field
		//ptc_savings_settings_water_field
		//ptc_savings_settings_carbon_field
		//ptc_savings_settings_section
		//ptc_savings_plugin_menu

////Create table for site total stats
//function savingsActivate()
//{
//	/*db table create*/
//}
//register_activation_hook(__FILE__,"savingsActivate");
//

//UPDATE USERS' STATS
function user_update_do_this_twicedaily() {
	//LOOP THROUGH EACH USER
	$vegWeekGlobal = 0;
	$vegDaysGlobal = 0;
	$vegYearGlobal = 0;
	global $wpdb;
	$parttimecarnivores = get_users('orderby=ID');
	foreach ($parttimecarnivores as $user) {
		//GET THE USER'S PLEDGE
		$pledge = xprofile_get_field_data('My Pledge',$user->ID);
		if ($pledge == 'No') { 
			$pledge = 0;
		}
		$vegWeek = (7 - $pledge);
		
		//GET DATE REGISTERED AND CALCULATE THE NUMBER OF WEEKS
		$startDate = strtotime($user->user_registered);
		$weeksNew = floor((time() - $startDate)/ 604800);
		
		//GET THE OLD NUMBER OF WEEKS AND SET TO 0 IF NECESSARY
		$weeksOld = xprofile_get_field_data('Weeks',$user->ID);
	
		//IF DATA IS NOT UP-TO-DATE ALREADY
		if ($weeksNew > $weeksOld) {
			//GET THE OLD TOTAL NUMBER OF VEGGIE DAYS
			$vegDaysOld = xprofile_get_field_data('Total meat-free days',$user->ID);
			//CALCULATE USER'S TOTAL VEGGIE DAYS
//			if (empty($weeksNew )) {
//				$vegDaysNew = 0; 
//			} else {
				$vegDaysNew = $vegDaysOld + ($weeksNew - $weeksOld) * $vegWeek;
//			}
			//IF CURRENT WEEK IS MORE THAN A YEAR
			if ($weeksNew > 52) {
				//CALCULATE USER'S AVERAGE VEGGIE DAYS PER YEAR
				$vegDaysYearNew = 52 * $vegDaysNew / $weeksNew;
			} else {
				//YEAR ONE NOT COMPLETE, SO SET YEAR'S VEG DAYS EQUAL TO TOTAL VEG DAYS SO FAR
				$vegDaysYearNew = $vegDaysNew;
			}
			
			//UPDATE USER'S DATA
			xprofile_set_field_data('Weeks', $user->ID,  $weeksNew);
			xprofile_set_field_data('Yearly meat-free days', $user->ID,  $vegDaysYearNew);
			xprofile_set_field_data('Total meat-free days', $user->ID,  $vegDaysNew);
		}
		xprofile_set_field_data('Meat-free days per week', $user->ID,  $vegWeek);

		$vegWeekGlobal = $vegWeekGlobal + $vegWeek;
		$vegDaysCurrent = xprofile_get_field_data('Total meat-free days', $user->ID);
		$vegDaysGlobal = $vegDaysGlobal + $vegDaysCurrent;
		$vegYearCurrent = xprofile_get_field_data('Yearly meat-free days', $user->ID);
		$vegYearGlobal = $vegYearGlobal + $vegYearCurrent;
	}
	update_option( 'ptc_global_veg_week', $vegWeekGlobal );
	update_option( 'ptc_global_veg_total', $vegDaysGlobal );
	update_option( 'ptc_global_veg_year', $vegYearGlobal );
}
//SCHEDULE UPDATES
function user_update_setup_schedule() {
	if ( ! wp_next_scheduled( 'user_update_twicedaily_event' ) ) {
		wp_schedule_event( time(), 'twicedaily', 'user_update_twicedaily_event');
	}
}
add_action( 'wp', 'user_update_setup_schedule' );
add_action( 'user_update_twicedaily_event', 'user_update_do_this_twicedaily' );

//UPDATE GLOBAL STATS
function global_update_do_this_daily() {
	//LOOP THROUGH EACH USER
	global $wpdb;
	$vegWeekGlobal = 0;
	$vegDaysGlobal = 0;
	$vegYearGlobal = 0;
	$parttimecarnivores = get_users('orderby=ID');
	foreach ($parttimecarnivores as $user) {
		//GET THE USER'S PLEDGE
		$pledge = xprofile_get_field_data('My Pledge',$user->ID);
		if ($pledge == 'No') { 
			$pledge = 0;
		}
		$vegWeek = (7 - $pledge);
		xprofile_set_field_data('Meat-free days per week', $user->ID,  $vegWeek);

		$vegWeekGlobal = $vegWeekGlobal + $vegWeek;
		$vegDaysCurrent = xprofile_get_field_data('Total meat-free days', $user->ID);
		$vegDaysGlobal = $vegDaysGlobal + $vegDaysCurrent;
		$vegYearCurrent = xprofile_get_field_data('Yearly meat-free days', $user->ID);
		$vegYearGlobal = $vegYearGlobal + $vegYearCurrent;
	}
	update_option( 'ptc_global_veg_week', $vegWeekGlobal );
	update_option( 'ptc_global_veg_total', $vegDaysGlobal );
	update_option( 'ptc_global_veg_year', $vegYearGlobal );
	
}
//SCHEDULE UPDATES
function global_update_setup_schedule() {
	if ( ! wp_next_scheduled( 'global_update_daily_event' ) ) {
		wp_schedule_event( time(), 'daily', 'global_update_daily_event');
	}
}
add_action( 'wp', 'global_update_setup_schedule' );
add_action( 'global_update_daily_event', 'global_update_do_this_daily' );

//UPDATE GROUPS' STATS
//function group_update_do_this_hourly() {
//}
function ptc_group_update() {
	global $wpdb;
//	if ( bp_has_groups('per_page=99999') ) {
//		while ( bp_groups() ) : bp_the_group();
			$groupID = bp_get_group_id();
			$args = array(
				 'group_id' => $groupID,
				 'per_page' => 99999999,
				 'exclude_admins_mods' => 0
			);
			$vegWeekGroup = 0;
			$vegDaysGroup = 0;
			$vegYearGroup = 0;
			if ( bp_group_has_members($args)) {
				while ( bp_group_members() ) : bp_group_the_member();
					$currentMember = bp_get_group_member_id();
					$vegWeekMember = xprofile_get_field_data('Meat-free days per week', $currentMember);
					$vegWeekGroup = $vegWeekGroup + $vegWeekMember;
					$vegDaysMember = xprofile_get_field_data('Total meat-free days', $currentMember);
					$vegDaysGroup = $vegDaysGroup + $vegDaysMember;
					$vegYearMember = xprofile_get_field_data('Yearly meat-free days', $currentMember);
					$vegYearGroup = $vegYearGroup + $vegYearMember;
				endwhile;
			}
			groups_update_groupmeta( $groupID, 'ptc_group_veg_week', $vegWeekGroup );
			groups_update_groupmeta( $groupID, 'ptc_group_veg_total', $vegDaysGroup );
			groups_update_groupmeta( $groupID, 'ptc_group_veg_year', $vegYearGroup );
//		endwhile; 
//	}
}
add_action( 'bp_before_group_home_content', 'ptc_group_update' );

//PTC SAVINGS WP_ADMIN SETTINGS
function ptc_savings_init()
{
	register_setting('general','ptc_land_savings_setting');
	register_setting('general','ptc_water_savings_setting');
	register_setting('general','ptc_carbon_savings_setting');
}
add_action('admin_init','ptc_savings_init');

function ptc_savings_settings_land_field()
{
	?>
	<input type="text" name="ptc_land_savings_setting" id="ptc_land_savings_setting"
			value="<?php echo get_option('ptc_land_savings_setting'); ?>" />
	<?php 
}
function ptc_savings_settings_water_field()
{
	?>
	<input type="text" name="ptc_water_savings_setting" id="ptc_water_savings_setting"
			value="<?php echo get_option('ptc_water_savings_setting'); ?>" />
	<?php 
}
function ptc_savings_settings_carbon_field()
{
	?>
	<input type="text" name="ptc_carbon_savings_setting" id="ptc_carbon_savings_setting"
			value="<?php echo get_option('ptc_carbon_savings_setting'); ?>" />
	<?php 
}
function ptc_global_veg_week_field()
{
	?>
	<input type="text" name="ptc_global_veg_week" id="ptc_global_veg_week"
			value="<?php echo get_option('ptc_global_veg_week'); ?>" disabled />
	<?php 
}
function ptc_global_veg_total_field()
{
	?>
	<input type="text" name="ptc_global_veg_total" id="ptc_global_veg_total"
			value="<?php echo get_option('ptc_global_veg_total'); ?>" disabled />
	<?php 
}
function ptc_global_veg_year_field()
{
	?>
	<input type="text" name="ptc_global_veg_year" id="ptc_global_veg_year"
			value="<?php echo get_option('ptc_global_veg_year'); ?>" disabled />
	<?php 
}

function ptc_savings_settings_section()
{
	?>
	<p>Enter savings per meat-free day per person (whole numbers only):</p>
	<?php 
}

function ptc_savings_plugin_menu()
{ 	
	add_settings_section('ptc_savings_section','PTC Savings Settings','ptc_savings_settings_section','general');
	add_settings_field('ptc_land_savings_setting', 'Land (m<sup>2</sup>)','ptc_savings_settings_land_field','general','ptc_savings_section');
	add_settings_field('ptc_water_savings_setting', 'Water (litres)','ptc_savings_settings_water_field','general','ptc_savings_section');
	add_settings_field('ptc_carbon_savings_setting', 'CO<sub>2</sub> (kg)','ptc_savings_settings_carbon_field','general','ptc_savings_section');
	add_settings_field('ptc_global_veg_week', 'Worldwide meat-free days per week','ptc_global_veg_week_field','general','ptc_savings_section');
	add_settings_field('ptc_global_veg_total', 'Worldwide total meat-free days','ptc_global_veg_total_field','general','ptc_savings_section');
	add_settings_field('ptc_global_veg_year', 'Worldwide total meat-free days capped at 1 year per person','ptc_global_veg_year_field','general','ptc_savings_section');
}
add_action('admin_menu', 'ptc_savings_plugin_menu');

//THIS IS HOW TO GET THE SETTINGS DATA
//	$land = get_option('ptc_land_savings_setting');
//	$water = get_option('ptc_water_savings_setting');
//	$carbon = get_option('ptc_carbon_savings_setting');

