<?php
/*
Plugin Name: Theme File Maker
Plugin URI: http://www.Bainternet.info/wordpress/plugins
Description: Lets you to create your own template pages (blank, or with custom loop) without leaving the WordPress Admin and no file uploading is needed.
Version: 1.0.0
Author: BaInternet ,Ohad raz
Author URI: http://en.Bainternet.info/
*/
/*  Copyright 2010 BaInternet  (email : admin@bainternet.info)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if ( is_admin() ){

function insert_attribute_to_tag($tag,$attribute){
	$attribute .= '>';
	$attribute = ' '.$attribute;
	$temp = str_replace('>',$attribute,$tag);
	return($temp);
}
/* Call the html code */
add_action('admin_menu', 'admin_menu1');
function admin_menu1()
	{
	$plugin_page=add_submenu_page('themes.php', 'New Template File', 'New Template File', 'edit_themes', __FILE__, 'trender');
	add_action( 'admin_head-'. $plugin_page, 'myplugin_admin_header' );
	}
}	 
	
	
function myplugin_admin_header(){
	$plugindir = get_option('home').'/wp-content/plugins/'.dirname(plugin_basename(__FILE__));
	echo "<link rel='stylesheet' href='$plugindir/style.css' type='text/css' />\n";

}

  

	
	
 function trender()
	{
		echo '<div class="wrap">';
		echo '<h2>Create New Template File</h2>';
		echo '<ul style="list-style: square inside none; width: 300px; font-weight: bolder; padding: 20px; border: 2px solid; background-color: #FFFFE0; border-color: #E6DB55; position: fixed;  right: 120px; top: 150px;">
					<li> Any feedback or suggestions are welcome</li>
					<li> <a href="http://wordpress.org/tags/theme-file-maker?forum_id=10">Support forum</a> for help and bug submittion</li>
					<li> Also check out <a href="http://en.bainternet.info/category/plugins">my other plugins</a></li>
					<li> And if you like my work <a  style="color: #FC000D;" href="http://en.bainternet.info/donations">make a donation</a></li>
				</ul>';
		if(current_user_can('edit_plugins'))
		{
			global $display_name,$user_url;
			get_currentuserinfo();
			$defaults = array(
				'template_name' => 'New Template',
				'Template_uri' => get_bloginfo('url'),
				'description' => 'A Brand New Template File',
				'version' => '1.0',
				'author' => '' != $display_name ? $display_name : get_bloginfo('name'),
				'author_uri' => '' != $user_url ? $user_url : get_bloginfo('url'),
				'' => '');
			$r = wp_parse_args($_POST, $defaults);
			$r['template_name'] = strtolower( str_replace(' ', '-', $r['template_name']) );
			
			if(isset($_POST['template_name']))
			{
				if(!wp_verify_nonce($_POST['_wpnonce'], 't-maker'))
					wp_die(__('Error'));
				echo 'Creating template';
				
				// create dir
				extract($r, EXTR_SKIP);
				
				$file_name = TEMPLATEPATH . '/' . $template_name . '.php';
				
				
				if(file_exists( $file_name))
					wp_die("File already exists");

								
				$handle = fopen($file_name, 'w') or wp_die('Cannot open file for editing');
				$prefile[] ="";
				$inloop[] = "";
				$loopcount = 0;
				if (isset($_POST['include_header'])){
				$prefile[0] = '<?php get_header(); ?> ';
				}
				if (isset($_POST['include_loop'])){
				//query_post options
					$query_p = 0;
						if (isset($_POST['category_numbers'])){
							$q_cat_string= "'cat' => '".$_POST['category_numbers']."'";
							$query_p = $query_p + 1;
						}
						if (isset($_POST['q_post_type'])){
							$qstring .= "'post_type' => array(".$_POST['q_post_type'].")";
							$query_p = $query_p + 1;
						}
						if ($query_p >0){
							$addCOMMA = false;
							$Q_P = '$Q = array(';
							if (isset($q_cat_string)){
								$Q_P .=$q_cat_string;
								$argsCount = true;
							}
							if (isset($query_p)){
								if ($argsCount){ $Q_P .= ",\n" .$query_p;}
								else{ 
									$Q_P .= $query_p;
									$argsCount = true;
								}
							}
							$Q_P = ");";
							$prefile[1] ="<php ". $Q_P ."\n ".'query_posts($Q); ?>';
						}
					
				//end query_post options
				//inside loop options if (isset($_POST[''])){}
					
					global $title_Tag_is,$time_tag_is,$author_Tag_is;
					
					// show title
					if (isset($_POST['show_title'])){
						$title_link = 'nop';
						//if title is link
						if (isset($_POST['is_title_link'])){
							//title class
							if (isset($_POST['title_link_class'])){
								$Title_link_class = $_POST['title_link_class'];
							}
							else{
								$Title_link_class = 'post-title-link';
							}
							//add title attribute to title link
							if (isset($_POST['title_title'])){ 
								$title_link = '<a href="<?php the_permalink() ?>" class="'.$Title_link_class.'" title="<?php the_title() ?>"><?php the_title() ?></a>';
							}
							else{
								$title_link = '<a href="<?php the_permalink() ?>" class="'.$Title_link_class.'"><?php the_title() ?></a>';
							}
						}
						//tag for title
						if (isset($_POST['before_title'])){
							$preTitle= $_POST['before_title'];
						} 
						else{
							$preTitle='<h2>' ; 
						}
						//closing tag for title
						if (isset($_POST['after_title'])){
							$postTitle = $_POST['after_title'];
						}
						else{
							$postTitle ='</h2>';
						}
						//class for title tag
						if (isset($_POST['title_tag_class'])){
							$Title_Tag_class= $_POST['title_tag_class'];
						}
						else{
							$Title_Tag_class= 'post-title';
						}
						//check if tag is html format and if is link
						$pos = strpos($preTitle,'>');
						if($pos === false) {
							if ($title_link == 'nop'){
								$title_Tag_is = insert_attribute_to_tag('<h2>',"class=\"".$Title_Tag_class."\"") . '<?php the_title() ?></h2>';
							} 
							else{
								$title_Tag_is = insert_attribute_to_tag('<h2>',"class=\"".$Title_Tag_class."\"").$title_link.'</h2>';
							}
						}
						else {
							if ($title_link == 'nop'){
								$title_Tag_is = insert_attribute_to_tag($preTitle,"class=\"".$Title_Tag_class."\"").'<?php the_title() ?>'.$postTitle;
							} 
							else{
								$title_Tag_is = insert_attribute_to_tag($preTitle,"class=\"".$Title_Tag_class."\"").$title_link.$postTitle;
							}
						}
					}
					//end show title options
					
					//show time
					if (isset($_POST['show_time'])){
						// time display type
						switch ($_POST['time_type']) {
							case 't1':
								$time_print = '<?php the_time(\'g:i a\'); ?>';
								break;
							case 't2':
								$time_print = '<?php the_time(\'G:i\'); ?>';
								break;
							case 't3':
								$time_print = '<?php the_time(\'F j, Y\'); ?>';
								break;
							case 't4':
								$time_print = '<?php the_time(\'F j, Y\'); ?> at <?php the_time(\'g:i a\'); ?>';
								break;
						}
						// text before time 
						if (isset($_POST['text_before_time'])){
							$pretime = $_POST['text_before_time'];
						}
						else{
							$pretime = 'Posted: ';
						}
						// text after time 
						if (isset($_POST['text_after_time'])){
							$posttime = $_POST['text_after_time'];
						}
						else{
							$posttime = '';
						}
						// time class name
						if (isset($_POST['timeclass'])){
							$timeclass = $_POST['timeclass'];
						}
						else{
							$timeclass = 'timeclass';
						}
						$time_tag_is = '<div id="post-time-container" class="'. $timeclass.'">'.$pretime.$time_print.$posttime.'<div>'."\n";
					}
					//end show time
					
					//show author
					if (isset($_POST['show_author'])){
						$author_link = 'nop';
						// link other to profile
						if (isset($_POST['is_author_link'])){
							//author link class
							if (isset($_POST['author_link_class'])){
							$author_link_class = $_POST['author_link_class'];
							}
							else{
							$author_link_class = 'post-author-link';
							}
							//add title attribue to link
							if (isset($_POST['author_title'])){
								$author_link = '<a href="<?php get_the_author_link(); ?>" class="'.$author_link_class.'" title="<?php the_author() ?>"><?php the_author() ?></a>';
							}
							else{
								$author_link = '<a href="<?php get_the_author_link(); ?>" class="'.$author_link_class.'"><?php the_author() ?></a>';
							}
						}
						// text before author
						if (isset($_POST['before_author'])){
							$preauthor = $_POST['before_author'];
						}
						else{
							$preauthor = '<small>';
						}
						// text after author
						if (isset($_POST['after_author'])){
							$postauthor = $_POST['after_author'];
						}
						else{
							$postauthor = '</small>';
						}
						// class fot author tag
						if (isset($_POST['author_tag_class'])){
							$author_class = $_POST['author_tag_class'];
						}
						else{
							$author_class = 'post-author';
						}
						//check if tag is html format and if is link
						$pos1 = strpos($preauthor,'>');
						if($pos1 === false) {
							if ($author_link == 'nop'){
								$author_Tag_is = insert_attribute_to_tag('<small>',$author_class) . '<?php the_author() ?></small>';
							} 
							else{
								$author_Tag_is = insert_attribute_to_tag('<small>',$author_class).$author_link.'</small>';
							}
						}
						else {
							if ($author_link == 'nop'){
								$author_Tag_is = insert_attribute_to_tag($preauthor,"class=\"".$author_class."\"").'<?php the_author() ?>'.$postauthor;
							} 
							else{
								$author_Tag_is = insert_attribute_to_tag($preauthor,"class=\"".$author_class."\"").$author_link.$postauthor;
							}
						}
					}
					//end show author
					
					//show_thumbnail
					if (isset($_POST['show_thumbnail'])){
						$thumb_tag_is = "<?php // check if the post has a Post Thumbnail assigned to it. ?>\n <?php if ( has_post_thumbnail() ) { ?>\n ";
						if (isset($_POST['thumbnail_alt'])){
							$thumb_alt = $_POST['thumbnail_alt'];
						}else{
							$thumb_alt = 'trim(strip_tags( $attachment->post_excerpt ))';
						}
						if (isset($_POST['is_thumbnail_link'])){
							$thumb_tag_is .='<a href="<?php the_permalink() ?>" class="'. $_POST['thumbnail_link_class'] .'"';
							if (isset($_POST['thumbnail_title'])){$thumb_tag_is .=' title="<?php the_title(); ?>" >';}else {$thumb_tag_is .=">\n";}
						}
						$t_size = "array(".$_POST['thumbnailx'].",".$_POST['thumbnaily'].")";
						$default_attr = "array('class'	=> ".$_POST['thumbnail_tag_class'].",'alt'	=> ".$thumb_alt.")";
						$thumb_tag_is .= "<?php the_post_thumbnail(".$t_size.",". $default_attr."); ?>\n";
						
						if (isset($_POST['is_thumbnail_link'])){$thumb_tag_is .= "</a>\n";}
						$thumb_tag_is .= "<?php } ?>";
					}
					 
					//end show_thumbnail
					
					//show_content
					if (isset($_POST['show_content'])){
						$content_tag_is ="";
						if ($_POST[content] == 'co'){$content_tag_is ="<?php the_content(); ?>\n";}else{$content_tag_is ="<?php the_excerpt(); ?>\n";}
						if (isset($_POST['content_class'])){$con_class = $_POST['content_class'];}else{$con_class = 'post';}
					}
					//end show_content
					
					//show_categories
					if (isset($_POST['show_categories'])){
						$cat_tag_is ="<p>".$_POST['before_categories']." <?php the_category('".$_POST['separator']." '); ?></p>\n";
						if (isset($_POST['categories_tag_class'])){$cat_class= $_POST['categories_tag_class'];}else{$cat_class= "post-categories";}
					}
					//end show_categories
					
					//show_tags
					if (isset($_POST['show_tags'])){
						$tags_tag_is ="<p><?php the_tags( '".$_POST['before_tags']."','".$_POST['Tagsseparator']."','".$_POST['after_Tags']."'); ?><p>\n";
						if (isset($_POST['Tags_tag_class'])){$tags_class= $_POST['Tags_tag_class'];}else{$tags_class= "post-Tags";}
					}
					//end show_tags
					
					//show comments
					if (isset($_POST['show_comments'])){
						$comments_tag_is = "<p><?php comments_popup_link('".$_POST['no_comments']."','".$_POST['one_Comment']."','".$_POST['more_comments']."', 'comments-link', '".$_POST['comments_off']."'); ?></p>\n";
						if (isset($_POST['comments_tag_class'])){$com_class= $_POST['comments_tag_class'];}else{$com_class = "post-comments";}
					}
					

					//end show comments
					
					//inside loop code collection
					if (isset($title_Tag_is)){
						$inloop[] = '<!--Post Title-->'."\n".'<div id="post-title-container">'."\n".$title_Tag_is."\n".'</div>'."\n".'<!--End Post Title-->'."\n";
					}
					if (isset($time_tag_is)){
						$inloop[] = '<!--Post Time-->'."\n".$time_tag_is."\n".'<!--End Post Time-->'."\n";
					}
					if (isset($author_Tag_is)){
						$inloop[] = '<!--Post author-->'."\n".'<div id="post-author">'."\n".$author_Tag_is."\n".'</div>'."\n".'<!--End Post author-->'."\n";
					}
					if (isset($thumb_tag_is)){
						$inloop[] = '<!--Post thumb-->'."\n".'<div id="post-thumbnail-container">'."\n".$thumb_tag_is."\n".'</div>'."\n".'<!--End Post thumb-->'."\n";
					}
					if (isset($content_tag_is)){
						$inloop[] = '<!--Post Content-->'."\n".'<div id="'.$con_class.'">'."\n".$content_tag_is."\n".'</div>'."\n".'<!--End content -->'."\n";
					}
					if (isset($cat_tag_is)){
						$inloop[] = '<!--Post categories-->'."\n".'<div id="'.$cat_class.'">'."\n".$cat_tag_is."\n".'</div>'."\n".'<!--End categories -->'."\n";
					}
					if (isset($tags_tag_is)){
						$inloop[] = '<!--Post tags-->'."\n".'<div id="'.$tags_class.'">'."\n".$tags_tag_is."\n".'</div>'."\n".'<!--End tags -->'."\n";
					}
					if (isset($comments_tag_is)){
						$inloop[] = '<!--Post comments-->'."\n".'<div id="'.$com_class.'">'."\n".$comments_tag_is."\n".'</div>'."\n".'<!--End comments -->'."\n";
					}
					//End inside loop code collection
					$inside_loop_colection = '<?php // inside the loop ?>'."\n";
					foreach ($inloop as $in_loop_option) {
						$inside_loop_colection .= $in_loop_option ."\n";
					}
					//end inside loop options
					
					$prefile[2] = '<?php if(have_posts()) : ?>
					<?php while(have_posts()) : the_post(); ?>
					insideloop
					<?php endwhile; ?>
					<?php else : ?> 
					<?php endif; ?>';
					$prefile[2] = str_replace('insideloop',$inside_loop_colection,$prefile[2]);
				}else{
					//no loop
					$prefile[2] = '<?php // your loop here ?>';
				}
				if (isset($_POST['include_footer'])){
				$prefile[3] = '<?php get_footer(); ?>';
				}
				
				$file_contents = <<<OUT
<?php
/*
Template Name: $template_name
*/
?>

$prefile[0] 

$prefile[1]
$prefile[2]

$prefile[3]


OUT;
				fwrite($handle, $file_contents);
				fclose($handle);
				echo '<p> template page successfully created. Start editing the template page at <a href="' . admin_url('theme-editor.php') .'">here</a>';
			}
			else
			{
				echo '<div class="form-wrap">';
				?>
				<script type="text/javascript">
				function loop(el,bo){
					var vis = (bo.checked) ? "block" : "none";
					document.getElementById(el).style.display = vis;
				}
				function showtab(tabId) {
					var tabs = new Array('tab-1','tab-2','tab-3','tab-4','tab-5','tab-6','tab-7','tab-8');
					var lin = new Array('link-1', 'link-2', 'link-3', 'link-4', 'link-5', 'link-6', 'link-7', 'link-8');
					tabId = tabId - 1;
					link = tabId;
					for (i = 0; i < tabs.length; i++) {
					   if (i == tabId) {
							window.document.getElementById(tabs[i]).setAttribute("class", "shown");
						}
						else {
							window.document.getElementById(tabs[i]).setAttribute("class", "notshown");
						}
					}
					for (j = 0; j < lin.length; j++) {
						if (j == link) {
							window.document.getElementById(lin[j]).setAttribute("class", "active"); 
						}
						else { 
							window.document.getElementById(lin[j]).setAttribute("class", "notactive");
						}
					}
				}
				</script>
				<?php 
				echo '<form action="" method="post">';
				wp_nonce_field('t-maker');
				echo '<table class="form-table" dir="ltr">';

				echo '<tr valign="top"><th scope="row">';
				echo '<label for="template_name">' . __('Template Name') . '</label></th><td>';
				echo '<input type="text"   id="template_name" name="template_name" value=""/>';
				echo '</td></tr>';
				
				echo '<tr valign="top"><th scope="row">';
				echo '<label for="include_header">' . __('Include get_header?') . '</label></th><td>';
				echo '<input type="checkbox"   id="include_header" name="include_header" value="1"/>';
				echo '</td></tr>';
				
				echo '<tr valign="top"><th scope="row">';
				echo '<label for="include_loop">' . __('Include Loop?') . '</label></th><td>';
				echo '<input type="checkbox" onclick="loop(\'loop_options\',this)"  id="include_loop" name="include_loop" value="1"/>';
				echo '</td></tr>';
				echo '<tr><th></th><td><div id="loop_options" style="display: none;"><table border="0">';
				echo '<tr valign="top"><td colspan="2" align="left"><b>query_posts options</b></td></tr><tr>';			
				echo '<th scope="row"><label for="in_category">' . __('Use In_category?') . '</label></th><td>';
				echo '<input type="checkbox" onclick="loop(\'incat\',this)" id="in_category" name="in_category"  value="1"/><br> ' . __('if checked only posts from categories listed ') . '<div id="incat" style="display: none;"><br> '.__('enter category id comma sepred. ex: 2,25,33 or single: 5').'<br> <input type="text"  id="category_numbers" name="category_numbers" value=""/></div>';
				echo '</td></tr>';
				echo '<th scope="row"><label for="q_post_type">' . __('Post Type') . '</label></th><td>';
				echo '<input type="checkbox" onclick="loop(\'inpost_type\',this)" id="post_type" name="post_type"  value="1"/><br> ' . __('if checked only posts from categories listed ') . '<div id="inpost_type" style="display: none;"><br> '.__('enter post type. ex: \'post\',\'page\' ').'<br> <input type="text"  id="q_post_type" name="q_post_type" value=""/></div>';
				echo '</td></tr>';
				
				echo '<tr valign="top"><td colspan="2" align="left"><b>Inside the Loop options</b></td></tr>';			
				?>
				<tr><td colspan="2">
				<div id="tabswrapper">
	
				<div id="tabsleft">
					<div class="tab">
						<h3 title="first"><a onclick="showtab(1)" class="active" id="link-1">Show title?</a></h3>
					</div>
					<div class="tab">
						<h3 title="second"><a onclick="showtab(2)" id="link-2" class="notactive">Show The Time?</a></h3>
					</div>
					<div class="tab">
						<h3 title="4"><a onclick="showtab(3)" id="link-3" class="notactive">Show author?</a></h3>
					</div>
					<div class="tab">
						<h3 title="t5hird"><a onclick="showtab(4)" id="link-4" class="notactive">Show thumbnail?</a></h3>
					</div>
					<div class="tab">
						<h3 title="th6ird"><a onclick="showtab(5)" id="link-5" class="notactive">Show Content?</a></h3>
					</div>
					<div class="tab">
						<h3 title="thi7rd"><a onclick="showtab(6)" id="link-6" class="notactive">Show categories?</a></h3>
					</div>
					<div class="tab">
						<h3 title="thir8d"><a onclick="showtab(7)" id="link-7" class="notactive">Show tags?</a></h3>
					</div>
					<div class="tab">
						<h3 title="third3"><a onclick="showtab(8)" id="link-8" class="notactive">Show Comments?</a></h3>
					</div>
					<br />
				</div>
			
			<div id="tabsright">
				<div id="tabscontent">
					<div id="tab-1" class="shown">
					 <h3>Show title?</h3>
					<p><?php echo '<input type="checkbox" onclick="loop(\'title\',this)" id="show_title" name="show_title"  value="1"/><br> ' . __('if checked div container will be created ') . '<div id="title" style="display: none;"><br>'.__('<b>Title Options</b><br>enter tag before title (ex: &lt;h2&gt;)').'<br>  <input type="text"  id="before_title" name="before_title" value="&lt;h2&gt;"/><br>'. __('enter closing tag for after title (ex: &lt;/h2&gt;)').'<br>  <input type="text"  id="after_title" name="after_title" value="&lt;/h2&gt;" /><br>'. __('class for title tag (defualt: post-title)').'<br>  <input type="text"  id="title_tag_class" name="title_tag_class" value="post-title" /><br>'.__('Link Title to Post?').'<input type="checkbox" onclick="loop(\'titleli\',this)" id="is_title_link" name="is_title_link" value="1"/><div id="titleli" style="display: none;"><br>' .__('<b>Link Options</b><br> Add title attribute?').' <input type="checkbox"  id="title_title" name="title_title" value="1"/><br>'. __('class for title link (defualt: post-title-link)').'<br><input type="text"  id="title_link_class" name="title_link_class" value="post-title-link" /><br></div></div>'; ?></p>
					</div>
				
					<div id="tab-2" class="notshown">
					 <h3>Show The Time?</h3>
					<p><?php echo '<input type="checkbox" onclick="loop(\'showtime\',this)" id="show_time" name="show_time"  value="1"/>
							<br> ' . __('if checked div container will be created ') . '<div id="showtime" style="display: none;">
							<br>'.__('<b>Time Options</b><br>you must select time display type :').'<br>
							<select name="time_type" id="time_type" style="width: 85%;">
								<option value="t1" selected="selected">Time as AM/PM</option>
								<option value="t2">24H format</option>
								<option value="t3">Date as Month Day, Year</option>
								<option value="t4">Date and Time</option>
							</select><div>Time as AM/PM <b>ex: 10:36 pm</b><br>24H format <b>ex: 17:52</b><br>Date as Month Day, Year <b>ex: December 2, 2004</b><br>Date and Time <b>ex: July 17, 2007 at 7:19 am</b> </div>
							<br>'.__(' text Before Time (ex: Posted : )').'
							<br><input type="text"  id="text_before_time" name="text_before_time" value="Posted: "/>
							<br>'.__(' text After Time (Defualt: blank )').'
							<br><input type="text"  id="text_after_time" name="text_after_time" value=""/>
							<br>'.__(' Time Container Class Name: (Defualt: timeclass)').'
							<br><input type="text"  id="timeclass" name="timeclass" value="timeclass"/></div>'; ?></p>
					</div>
				
					<div id="tab-3" class="notshown">
					 <h3>Show author?</h3>
					<p><?php echo '<input type="checkbox" onclick="loop(\'author\',this)" id="show_author" name="show_author"  value="1"/>
					<br> ' . __('if checked div container will be created ') . '<div id="author" style="display: none;">
					<br>'.__('<b>Author Options</b><br>enter tag before author (ex: &lt;small&gt;)').'
					<br>  <input type="text"  id="before_author" name="before_author" value="&lt;small&gt;"/>
					<br>'. __('enter closing tag for after author (ex: &lt;/small&gt;)').'
					<br>  <input type="text"  id="after_author" name="after_author" value="&lt;/small&gt;" />
					<br>'. __('class for author tag (defualt: post-author)').'
					<br>  <input type="text"  id="author_tag_class" name="author_tag_class" value="post-author" />
					<br>'.__('Link author to Profile?').'<input type="checkbox" onclick="loop(\'authorli\',this)" id="is_author_link" name="is_author_link" value="1"/>
					<div id="authorli" style="display: none;">
					<br>' .__('<b>Link Options</b><br> Add title attribute?').' <input type="checkbox"  id="author_title" name="author_title" value="1"/>
					<br>'. __('class for author link (defualt: post-author-link)').'
					<br><input type="text"  id="author_link_class" name="author_link_class" value="post-author-link" />
					<br></div></div>'; ?></p>
					</div>
				
					<div  id="tab-4" class="notshown">
					 <h3>Show thumbnail?</h3>
					<p><?php echo '<input type="checkbox" onclick="loop(\'thumbnail\',this)" id="show_thumbnail" name="show_thumbnail"  value="1"/><br> ' . __('if checked div container will be created ') . '<div id="thumbnail" style="display: none;"><br>'.__('<b>thumbnail Options</b><br>enter size of thumbnail ').'<br>  <input type="text" style="width: 35px;" id="thumbnailx" name="thumbnailx" value="200"/> X <input type="text" style="width: 35px;" id="thumbnaily" name="thumbnaily" value="200"/><br>'. __('enter ALT for thumbnail (if blank will show post title)').'<br>  <input type="text"  id="thumbnail_alt" name="thumbnail_alt" value="" /><br>'. __('class for thumbnail (defualt: post-image)').'<br>  <input type="text"  id="thumbnail_tag_class" name="thumbnail_tag_class" value="post-image" /><br>'.__('Link thumbnail to Post?').'<input type="checkbox" onclick="loop(\'thumbnailli\',this)" id="is_thumbnail_link" name="is_thumbnail_link" value="1"/><div id="thumbnailli" style="display: none;"><br>' .__('<b>Link Options</b><br> Add title attribute?').' <input type="checkbox"  id="thumbnail_title" name="thumbnail_title" value="1"/><br>'. __('class for thumbnail link (defualt: post-thumbnail-link)').'<br><input type="text"  id="thumbnail_link_class" name="thumbnail_link_class" value="post-thumbnail-link" /><br></div></div>'; ?></p>
				</div>
				
					<div  id="tab-5" class="notshown">
					 <h3>Show Content?</h3>
					<p><?php echo '<input type="checkbox" onclick="loop(\'contype\',this)" id="show_content" name="show_content"  value="1"/><br> ' . __('if checked div container will be created ') . '<div id="contype" style="display: none;"><br>'.__('<b>Content Options</b><br>you must select type full or experts:').'<br><select name="content" id="content" style="width: 35%;"><option value="co" selected="selected">Full Content</option><option value="ex">The Experts</option></select><br>'.__(' and you can define a class for container (default = post) .').'<br>  class= <input type="text"  id="content_class" name="content_class" value="post"/></div>'; ?></p>
					</div>
				
					<div  id="tab-6" class="notshown">
					 <h3>Show categories?</h3>
					<p><?php echo '<input type="checkbox" onclick="loop(\'categories\',this)" id="show_categories" name="show_categories"  value="1"/><br> ' . __('if checked div container will be created ') . '<div id="categories" style="display: none;"><br>'.__('<b>categories Options</b><br>enter text before categories (ex: Posted In:)').'<br>  <input type="text"  id="before_categories" name="before_categories" value="Posted in:"/><br>'. __('enter separator (ex: ,)').'<br>  <input type="text"  id="separator" name="separator" value="," /><br>'. __('class for categories Container (defualt: post-categories)').'<br>  <input type="text"  id="categories_tag_class" name="categories_tag_class" value="post-categories" /><br></div>'; ?></p>
					</div>
				
					<div  id="tab-7" class="notshown">
					<h3>Show tags?</h3>
					<p><?php echo '<input type="checkbox" onclick="loop(\'tags\',this)" id="show_tags" name="show_tags"  value="1"/><br> ' . __('if checked div container will be created ') . '<div id="tags" style="display: none;"><br>'.__('<b>tags Options</b><br>enter text before tags (ex: Taged: or Taged: &lt;ul&gt;&lt;li&gt; for Unordered list)').'<br>  <input type="text"  id="before_tags" name="before_tags" value="Taged: "/><br>'. __('enter Tags separator (ex: , or &lt;/li&gt;&lt;li&gt; for Unordered list)').'<br>  <input type="text"  id="Tagsseparator" name="Tagsseparator" value="," /><br>'. __('After Tags  (ex: leave blank or &lt;/li&gt;&lt;/ul&gt; for un ordered list)').'<br>  <input type="text"  id="after_Tags" name="after_Tags" value="" /><br>'. __('class for Tags Container (defualt: post-Tags)').'<br>  <input type="text"  id="Tags_tag_class" name="Tags_tag_class" value="post-Tags" /><br></div>'; ?></p>
					</div>
				
					<div  id="tab-8" class="notshown">
					<h3>Show Comments?</h3>
					<p><?php echo '<input type="checkbox" onclick="loop(\'comments\',this)" id="show_comments" name="show_comments"  value="1"/>
						<br> ' . __('if checked div container will be created ') . '<div id="comments" style="display: none;">
						<br>'.__('<b>comments Options</b><br>enter text for no comments_popup_link (ex: No comments yet)').'<br>  
						<input type="text"  id="no_comments" name="no_comments" value="No comments yet"/>
						<br>'. __('enter Text to display when there is one comment (ex: 1 Comment)').'<br>  
						<input type="text"  id="one_Comment" name="one_Comment" value="One comment so far" />
						<br>'. __('Enter Text to display when there are more than one comments. 
						<br>\'%\' is replaced by the number of comments <br> ex: % Comments').'<br>  
						<input type="text"  id="more_comments" name="more_comments" value="% comments so far (is that a lot?)" />
						<br>'. __('class for comments Container (defualt: post-comments)').'<br>  
						<input type="text"  id="comments_tag_class" name="comments_tag_class" value="post-comments" />
						<br>'. __('enter Text to display when comments are disabled<br> (ex: Comments Off)').'<br>  
						<input type="text"  id="comments_off" name="comments_off" value="Comments are off for this post" /><br>
						</div>'; ?>
					</p>
					</div>
					</div>
				</div>
			</div>

	</td></tr>
				<?php
				
				echo '</table></div><td></tr>';
				echo '<tr valign="top"><th scope="row">';
				echo '<label for="include_footer">' . __('Include get_footer?') . '</label></th><td>';
				echo '<input type="checkbox"   id="include_footer" name="include_footer" value="1"/>';
				echo '</td></tr>';
				
				echo '<tr><td colspan=2><input type="submit" value="Create Theme File" name="submit" class="button"/></td></tr>';
				
				echo '</table>';
				echo '</form>';
				echo '</div>';
			}
		}
		else wp_die(__('Sorry, you are not allowed to create new theme pages'));
		echo '</div>';
	}
	
?>