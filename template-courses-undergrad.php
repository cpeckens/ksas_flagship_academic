<?php
/*
Template Name: ISIS Courses
*/
?>	
<?php get_header(); ?>
 
<?php // Load Zebra Curl
	require_once TEMPLATEPATH . "/assets/functions/Zebra_cURL.php";
	//Set query sting variables
		$theme_option = flagship_sub_get_global_options(); 
		$department_unclean = $theme_option['flagship_sub_isis_name'];
		$department = str_replace(' ', '%20', $department_unclean);
		$fall = 'fall%202014';
		$spring = 'spring%202014';
		$key = 'DZkN4QOJGaDKVg6Du1911u45d4TJNp6I';
		
	//Create first Zebra Curl class
		$course_curl = new Zebra_cURL();
		$course_curl->option(array(
		    CURLOPT_TIMEOUT         =>  60,
		    CURLOPT_CONNECTTIMEOUT  =>  60,
		));
		$cache_dir = TEMPLATEPATH . "/assets/functions/cache/";
		$course_curl->cache($cache_dir, 2592000);
 
	//Create API Url calls
		$courses_spring_url = 'https://isis.jhu.edu/api/classes?key=' . $key . '&School=Krieger%20School%20of%20Arts%20and%20Sciences&Term=' . $spring . '&Department=AS%20' . $department;
		$courses_fall_url = 'https://isis.jhu.edu/api/classes?key=' . $key . '&School=Krieger%20School%20of%20Arts%20and%20Sciences&Term=' . $fall . '&Department=AS%20' . $department;
		$courses_call = array($courses_spring_url, $courses_fall_url);
	
	//Course display callback function
		function display_courses($result) {
		    $result->body = json_decode(html_entity_decode($result->body));
			$title = $result->body[0]->{'Title'};
			$term = $result->body[0]->{'Term'};
			$course_number = $result->body[0]->{'OfferingName'};
			$clean_course_number = preg_replace('/[^A-Za-z0-9\-]/', '', $course_number);
			$credits = $result->body[0]->{'Credits'};
			$instructor = $result->body[0]->{'InstructorsFullName'};
			$description = $result->body[0]->{'SectionDetails'}[0]->{'Description'};
		    // show everything
		    echo '<li class="' . $term . '" id="' . $clean_course_number . '"><div class="title"><h5><span class="course-number">' . $course_number . '</span> - ' . $title . '</h5></div>';
		    echo '<div class="content"><p>' . $description . '</p>';
		    echo '<p><b>Credits: </b>' . $credits . '<br><b>Instructor: </b>' . $instructor . '<br><b>Term: </b>' . $term . '</p>'; 
		    echo '</div></li>';
		 
		}
	//ISIS Call callback function	
		function parse_courses($result) {
			$key = 'DZkN4QOJGaDKVg6Du1911u45d4TJNp6I';
			$result->body = json_decode(html_entity_decode($result->body));
			$course_data = array();
				foreach($result->body as $course) {
					$section = $course->{'SectionName'};
					$level = $course->{'Level'};
					$parent = the_parent_title();
					
					if($section === '01' && strpos($level, $parent) !== false) {
						$number = $course->{'OfferingName'};
						$clean_number = preg_replace('/[^A-Za-z0-9\-]/', '', $number);
						$dirty_term = $course->{'Term'};
						$clean_term = str_replace(' ', '%20', $dirty_term);
						$details_url = 'https://isis.jhu.edu/api/classes/' . $clean_number . '01/' . $clean_term . '?key=' . $key;
						$course_data[] = $details_url;					
					}
				}
			$curl = new Zebra_cURL();
			$curl->option(array(
			    CURLOPT_TIMEOUT         =>  60,
			    CURLOPT_CONNECTTIMEOUT  =>  60,
			));
			$curl->cache($cache_dir, 2592000);
			$curl->get($course_data, 'display_courses');
			
		}
?>	
 
<div class="row sidebar_bg radius10" id="page">
	<div class="eight columns wrapper radius-left offset-topgutter">	
		<?php locate_template('parts-nav-breadcrumbs.php', true, false); ?>	
		<section class="content">
			<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
				<h2><?php the_title();?></h2>
				<?php the_content(); ?>
				
			<?php endwhile; endif;  ?>
					<div id="fields_search">
			<form action="#">
				<fieldset class="radius10">
							<div class="row filter option-set" data-filter-group="term">
									<div class="button radio"><a href="#" data-filter="" class="selected">View All</a></div>
									<div class="button radio"><a href="#" data-filter=".Spring">Spring 2014 Courses</a></div>
									<div class="button radio"><a href="#" data-filter=".Fall">Fall 2014 Courses</a></div>
									<h5 class="inline"><a href="#" class="acc_expandall">[Expand All]</a></h5>
							</div>
					<div class="row">		
						<input type="submit" class="icon-search" placeholder="Search by course number, title, and keyword" value="&#xe004;" />
						<input type="text" name="search" id="id_search"  /> 
					</div>
				</fieldset>
			</form>	
		</div>

			<ul class="expander accordion courses">
			<?php $course_curl->get($courses_call, 'parse_courses'); ?>
			</ul>
			
		</section>
	</div>	<!-- End main content (left) section -->
<?php locate_template('parts-sidebar.php', true, false); ?>
</div> <!-- End #landing -->
<?php get_footer(); ?>