<?php
/*
	Section: People Lud
	Author: bestrag
	Version: 1.2.0
	Author URI: http://bestrag.net
	Demo: http://bestrag.net/people-lud/demo
	Description: Custom Post Type Section for displaying People/Teams/Artists
	Class Name: PeopleLud
	Filter: component
*/

class PeopleLud extends PageLinesSection {
	var $lud_opts		= array();
	var $multiple_up	= 'People';
	var $multiple		= 'people';
	var $single_up		= 'Person';
	var $single		= 'person';
	var $prefix		= 'ppl';
	var $taxID		= 'person-sets';
	var $section_id		= 'people-lud';
	var $default_template	= 'default';
	var $temp_meta	= array();
	var $clone		= '';
	var $ico 		= '';
	var $ico_type 		= '';

	/* section_styles */
	function section_scripts() {
		wp_enqueue_script( 'jquery-masonry', array( 'jquery' ) );
		wp_enqueue_script( 'jquery-ludloop', $this->base_url.'/jquery.ludloop.js', array( 'jquery' ), true );
	}

	function setup_oset($clone){
		//master template
		if( $this->opt('opt_set_select') ) {
			$this->update_lud_settings($this->opt('opt_set_select'));
		}
		//set/update section_opts colors
		$this->update_lud_colors();
		//fontAwesome for DMS 2.0
		global $platform_build;
		$ver = intval(substr($platform_build, 0, 1));
		$this->ico = ($ver === 2) ? 'fa' : 'icon';
		$this->ico_type = ($ver === 2) ? '-square' : '-sign';
	}

	/* clone specific styles */
	function section_styles(){
		$colors=array(
			'templatebg'		=> array('.'.$this->prefix.'-container','#'.$this->opt('templatebg'), 'background-color'),
			'singlebg'		=> array('.'.$this->prefix.'-item-inner','#'.$this->opt('singlebg'), 'background-color'),
			'groupbg1'		=> array('#'.$this->prefix.'-group-1','#'.$this->opt('groupbg1'), 'background-color'),
			'groupbg2'		=> array('#'.$this->prefix.'-group-2','#'.$this->opt('groupbg2'), 'background-color'),
			'imgbg'			=> array('.'.$this->prefix.'-img','#'.$this->opt('imgbg'), 'background-color'),
			'title-color'		=> array('.'.$this->prefix.'-post_title','#'.$this->opt('title-color'), 'color'),
			'content-color'		=> array('.'.$this->prefix.'-post_content','#'.$this->opt('content-color'), 'color'),
			'position-color'		=> array('.'.$this->prefix.'-position','#'.$this->opt('position-color'), 'color'),
			'company-color'	=> array('.'.$this->prefix.'-company','#'.$this->opt('company-color'), 'color'),
			'custom1-color'		=> array('.'.$this->prefix.'-custom_text1','#'.$this->opt('custom1-color'), 'color'),
			'custom2-color'		=> array('.'.$this->prefix.'-custom_text2','#'.$this->opt('custom2-color'), 'color'),
			'icon-color'		=> array('.people-social a','#'.$this->opt('icon-color'), 'color'),
			'iconhover-color'	=> array('.people-social a:hover','#'.$this->opt('iconhover-color'), 'color'),
		);
		$css_code = '';
		foreach ($colors as $key => $value) {
			if($value[1] && $value[1] !== '#' && $value[1] !== 'px' ){
				$css_code .= sprintf('#%4$s%5$s %1$s{%2$s:%3$s;}', $value[0], $value[2], $value[1], $this->section_id, $this->meta['clone']);
			}
		}
		if ($css_code) {
			$lud_style = sprintf('<style type="text/css" id="%1$s-custom-%2$s">%3$s</style>', $this->prefix, $this->meta['clone'], $css_code);
			echo $lud_style;
		}
	}

	/* section_head */
	function section_head() {
		$this->lud_opts['template_name']	= ( $this->opt( 'template_name' ) ) ? $this->opt( 'template_name' ) : $this->default_template;
		//text style and weight
		$this->lud_opts['text_italic']	= ( $this->opt( 'text_italic' ) ) ? 'italic' : 'normal' ;
		$this->lud_opts['text_bold']	= ( $this->opt( 'text_bold' ) ) ? 'bold' : 'normal' ;
		$this->lud_opts['enable_animation']	= false;
		//single post vs lightbox
		$this->lud_opts['use_link']	= false;
		//layout
		$this->lud_opts['numslides']	= ( $this->opt( 'col_num' ) )  ? intval($this->opt( 'col_num' )) : 5;
		$this->lud_opts['slide_gutter']	= ( $this->opt( 'slide_gutter' ) ) ?  intval($this->opt( 'slide_gutter' ) ).'px' : '0' ;
		$this->lud_opts['equal_height']		= ( $this->opt( 'equal_height') ) ? false : true ;
		//not used in people lud
		$this->lud_opts['fluid']	= ( $this->opt( 'fluid') ) ? true : false ;
		//carousell single item min width - needed in ludloop.js
		$this->lud_opts['defFredWidth']	= 200;
		$this->lud_opts['fredWidth']		= 300;
		//all you need is json
		$lud_opts	= json_encode($this->lud_opts);
		?>
		<script type="text/javascript">
			/* <![CDATA[ */
			//lud objects
			var ludOpts 	= {};
			var ludSelectors	= {};
			jQuery(document).ready(function(){
				//selectors
				var cloneID 		= '<?php echo $this->meta['clone']; ?>';
				var sectionPrefix	= '<?php echo $this->prefix; ?>';
				var sectionClone	= jQuery('section#'+'<?php echo $this->section_id; ?>' + cloneID);
				ludSelectors[cloneID] = {
					'sectionPrefix'	: sectionPrefix,
					'sectionClone'	: sectionClone,
					'container'	: jQuery('.'+sectionPrefix+'-container', sectionClone),
					'wraper'	: jQuery('.'+sectionPrefix+'-wraper', sectionClone),
					'ludItem'	: jQuery('.'+sectionPrefix+'-item', sectionClone),
					'inner'		: jQuery('.'+sectionPrefix+'-item-inner', sectionClone)
				};
				//get options
				ludOpts[cloneID]	= <?php echo $lud_opts; ?>;
				//style and classes
				ItemStyle();
				responsiveClasses();
				//functions
				function ItemStyle(){
					ludSelectors[cloneID]['ludItem'].css({
						'padding-left'	: ludOpts[cloneID]['slide_gutter'],
						'padding-right'	: ludOpts[cloneID]['slide_gutter'],
						'font-style'	: ludOpts[cloneID]['text_italic'],
						'font-weight'	: ludOpts[cloneID]['text_bold']
					});
				}
				function responsiveClasses(){
					if(768 > ludSelectors[cloneID]['container'].width()){
						if(ludOpts[cloneID]['numslides'] > 3) ludOpts[cloneID]['numslides'] = 4;
					}
					if(600 > ludSelectors[cloneID]['container'].width()){
						if(ludOpts[cloneID]['numslides'] > 2) ludOpts[cloneID]['numslides'] = 3;
					}
					if(480 > ludSelectors[cloneID]['container'].width()){
						ludOpts[cloneID]['numslides'] = 1;
					}
					//set single item width
					var calcItemWidth = Math.floor((ludSelectors[cloneID]['container'].width()/ludOpts[cloneID]['numslides']) );
					ludSelectors[cloneID]['ludItem'].css({
						'width' :	calcItemWidth
					});
					ludOpts[cloneID]['itemWidth'] = calcItemWidth;
					if (384 > calcItemWidth) return ludSelectors[cloneID]['container'].addClass(ludOpts[cloneID]['template_name'] + '-c2');
					if (245 > calcItemWidth) return ludSelectors[cloneID]['container'].addClass(ludOpts[cloneID]['template_name'] + '-c3');
				}
			});
			jQuery(window).load(function(){
				cloneID 		= '<?php echo $this->meta['clone']; ?>';
				//engage
				ludSelectors[cloneID]['wraper'].ludLoop(ludSelectors[cloneID], ludOpts[cloneID]);
				//show
				ludSelectors[cloneID]['container'].animate({'height':'100%'},400);
				ludSelectors[cloneID]['wraper'].animate({'opacity':1},400);
			});
			/* ]]> */
		</script>
		<?php
		/* font */
		$font_selector = 'section#'.$this->section_id.$this->meta['clone'].' div.'.$this->prefix.'-container';
		if ( $this->opt( 'text_font' ) ) {
			echo load_custom_font( $this->opt( 'text_font' ), $font_selector );
		}
	}

	//section template
	function section_template(){
		//params
		$template_name = ( $this->opt( 'template_name' ) ) ? $this->opt( 'template_name' ) : $this->default_template;
		$use_link	= false;
		$link_elems		= array('img');
		$a_open		= '';
		$a_close		= '';
		//$use_link	= false;
		$animation	= 'in-grid';
		//social
		$use_social		= ( $this->opt('use_social') ) ? false : true;
		$social_icon_size	=( $this->opt('social_icon_size') ) ? $this->opt('social_icon_size') : '2x';
		$social_icon_variant	= ( $this->opt('social_icon_variant') && $this->opt('social_icon_variant') === 'simbol' ) ? '' : '-square';
		$social_facebook_icon 	= $this->ico.'-facebook'.$social_icon_variant;
		$social_twitter_icon	= $this->ico.'-twitter'.$social_icon_variant;
		$social_google_icon	= $this->ico.'-google-plus'.$social_icon_variant;
		$social_linkedin_icon	= $this->ico.'-linkedin'.$social_icon_variant;
		$social_github_icon	= $this->ico.'-github'.$social_icon_variant;
		$social_instagram_icon	= $this->ico.'-instagram';
		//template json
		$data_path	= $this->base_dir.'/data/';
		$template_json	= (file_exists($data_path.$template_name.'.json')) ? file_get_contents($data_path.$template_name.'.json') : file_get_contents($data_path.'default.json') ;
		$template_json  = json_decode($template_json);
		//query params
		$slides_num	= ( $this->opt( 'slides_num' ) ) ? $this->opt( 'slides_num' ) : '-1';
		$orderby	= ( $this->opt( 'orderby' ) ) ? $this->opt( 'orderby' ) : 'name';
		$order		= ( $this->opt( 'order' ) ) ? $this->opt( 'order' ) : 'ASC';
		$params	= '';
		$params	= array( 'post_type' => $this->multiple, 'orderby' => $orderby, 'order' => $order, 'posts_per_page' => $slides_num );
		$taxonomy	= ( $this->opt( 'taxonomy' ) ) ? $this->opt( 'taxonomy' ) : null ;
		if ( $taxonomy ) {
			$query_tax = array(
				array(
					'taxonomy' => $this->taxID,
					'field'    => 'slug',
					'terms'    => array( $taxonomy )
				)
			);
			$params['tax_query'] = $query_tax;
		}
		//query
		$post_data	= array();
		$query		= null;
		$query		= new WP_Query( $params );
		$index 		= 0;
		//collect all posts
		$all_posts	= '';
		if($query->have_posts()){
			while($query->have_posts()){
				$query->the_post();
				//get post data for every post
				$temp_data = get_post_meta( get_the_ID() );
				//update
				$temp_data['post_title'][0]	= get_the_title( );
				$temp_data['post_content'][0]	= get_the_content( );
				$temp_data['post_url'][0]	= get_post_permalink();
				if($temp_data['img'][0]) {
					$temp_data['img'][0]	= (array_key_exists('demo', $temp_data) && $temp_data['demo'][0]) ?  '<img src="'. $temp_data['img'][0] . '">' :  wp_get_attachment_image($temp_data['img'][0], 'full');
				}
				//collects all posts data in one array
				$post_data[] = $temp_data;
				//create link to single post
				if(in_array($use_link, array('link', 'colorbox'))) {
					$link_index = $index + 1;
					$a_open = sprintf('<a href="%1$s" class="%2$s-link %2$s-link-%3$s" data-inner-id="%2$s-inner-%3$s">', $post_data[$index]['post_url'][0], $this->prefix, $link_index );
					$a_close = '</a>';
				}
				//render elements
				$group_index = 1;
				$all_elems = '';
				foreach ($template_json as $key => $value) {
					$key++;
					//template json - if array in array
					if(is_array($value)){
						$group_elems = '';
						//elements
						foreach ($value as $i => $val) {
							//annoying wp notice fix
							if(!array_key_exists($val, $post_data[$index])) $post_data[$index][$val][0] = '';
							//add link only to imgs and title
							if($a_close && in_array($val, $link_elems)){$a_start = $a_open; $a_end = $a_close;}else{$a_start = ''; $a_end = '';}
							$group_elem = sprintf('%4$s<div class="%1$s-%2$s">%3$s</div>%5$s',$this->prefix, $val, $post_data[$index][$val][0], $a_start, $a_end );
							$group_elems .= $group_elem;
						}
						//wrap elements
						$group = sprintf('<div id ="%1$s-group-%2$s" class="%1$s-group">%3$s</div>', $this->prefix, $group_index, $group_elems);
						$all_elems .= $group;
						$group_index++;
					}else{
						//and again
						if(!array_key_exists($value, $post_data[$index])) $post_data[$index][$value][0] = '';
						//add link only to imgs and title
						if($a_close && in_array($value, $link_elems)){$a_start = $a_open; $a_end = $a_close;}else{$a_start = ''; $a_end = '';}
						$elem = sprintf('%4$s<div class="%1$s-%2$s">%3$s</div>%5$s',$this->prefix, $value, $post_data[$index][$value][0], $a_start, $a_end );
						$all_elems .= $elem;
					}
				}
				//social
				if($use_social === false){
					$social = '';
				}else{
					$facebook	= (array_key_exists('facebook', $post_data[$index]) && $post_data[$index]['facebook'][0]) ? '<a href="https://www.facebook.com/'.$post_data[$index]['facebook'][0].'" class="ppl-single-link ppl-facebook-link" target="_blank"><i class="'.$this->ico.' '.$social_facebook_icon.' icon-'.$social_icon_size.'"></i></a>' : '';
					$twitter		= (array_key_exists('twitter', $post_data[$index]) && $post_data[$index]['twitter'][0]) ? '<a href="http://twitter.com/'.$post_data[$index]['twitter'][0].'" class="ppl-single-link ppl-twitter-link" target="_blank"><i class="'.$this->ico.' '.$social_twitter_icon.' icon-'.$social_icon_size.'"></i></a>' : '';
					$google	= (array_key_exists('google', $post_data[$index]) && $post_data[$index]['google'][0]) ? '<a href="https://plus.google.com/'.$post_data[$index]['google'][0].'" class="ppl-single-link ppl-google-link" target="_blank"><i class="'.$this->ico.' '.$social_google_icon.' icon-'.$social_icon_size.'"></i></a>' : '';
					$linkedin	= (array_key_exists('linkedin', $post_data[$index]) && $post_data[$index]['linkedin'][0]) ? '<a href="http://www.linkedin.com/profile/view?id='.$post_data[$index]['linkedin'][0].'" class="ppl-single-link ppl-linkedin-link" target="_blank"><i class="'.$this->ico.' '.$social_linkedin_icon.' icon-'.$social_icon_size.'"></i></a>' : '';
					$github		= (array_key_exists('github', $post_data[$index]) && $post_data[$index]['github'][0]) ? '<a href="https://github.com/'.$post_data[$index]['github'][0].'" class="ppl-single-link ppl-github-link" target="_blank"><i class="'.$this->ico.' '.$social_github_icon.' icon-'.$social_icon_size.'"></i></a>' : '';
					$instagram	= (array_key_exists('instagram', $post_data[$index]) && $post_data[$index]['instagram'][0]) ? '<a href="https://instagram.com/'.$post_data[$index]['instagram'][0].'" class="ppl-single-link ppl-instagram-link" target="_blank"><i class="'.$this->ico.' '.$social_instagram_icon.' icon-'.$social_icon_size.'"></i></a>' : '';
					$social		= sprintf('<div class="lud-social %1$s-social">%2$s%3$s%4$s%5$s%6$s%7$s</div>',$this->multiple, $facebook,$twitter,$google,$linkedin,$github, $instagram);
				}
				//wrap elements in <li>
				$index++;
				$all_posts .= sprintf('<li class="%1$s-item %1$s-item-%2$s"><div id="%1$s-inner-%2$s" class="%1$s-item-inner">%3$s</div>%4$s</li>', $this->prefix, $index, $all_elems, $social);
			}
		}
		wp_reset_postdata();
		//add controls
		$controls = '';
		//wrap it up
		$ludloop = sprintf('<div class="%1$s-container post-id-%2$s template-%3$s"><ul class="%1$s-wraper %4$s">%5$s</ul>%6$s</div>',
			$this->prefix,
			$this->multiple,
			$template_name,
			$animation,
			$all_posts,
			$controls
		);
		//print
		echo do_shortcode($ludloop );
	}

	//section opts
	function section_opts() {
		$opts		= array();
		if($this->opt('opt_set_select')) {
			$master_info = '<div class="alert" style="font-weight:400;">Last loaded Master Template: <br><span style="font-weight:bold;">'.$this->opt('opt_set_select').'</span></div>';
		}else{
			$master_info = ($this->opt('opt_set_info')) ? '<div class="alert" style="font-weight:400;">Last loaded Master Template: <br><span style="font-weight:bold;">'.$this->opt('opt_set_info').'</span></div>' : '<div class="alert" style="font-weight:400;">No Master Template Loaded</div>';
		}
		$opts[] = array(
			'key'		=> 'master_template_settings',
			'type'		=>  'multi',
			'col'		=> 1,
			'title'		=> __( 'Master Template Settings', 'pagelines' ),
			'opts' => array(
				array(
					'key'	=>	'opt_set_select',
					'type'	=> 'select',
					'label'	=> __( 'Select Master Template', 'pagelines' ),
					'opts'	=> $this->opt_set_select(),
				),
				array(
					'key'	=>	'master_info',
					'type'	=> 'template',
					'label'	=> __( 'Select Master Template', 'pagelines' ),
					'template'	=> $master_info,
					'help'	=> __( "Master Template is innovative way to switch between section templates. When selected, master template will load chosen css template and set all required options for section's best design and functionality.", 'pagelines' ),
				),
				array(
					'key'	=>	'opt_set_info',
					'type'	=> 'text',
				)
			)
		);
		$opts[] = array(
			'key'		=> 'ccname_set',
			'type'		=>  'multi',
			'col'		=> 2,
			'title'		=> __( 'General settings', 'pagelines' ),
			'opts' => array(
				array(
					'key'	=>	'template_name',
					'type'		=> 'select',
					'label'	=> __( 'Choose Template', 'pagelines' ),
					'opts'		=> $this->get_template_selectvalues(),
					'compile'	=> true
				),
				array(
					'key'	=>	'taxonomy',
					'type'			=> 'select_taxonomy',
					'taxonomy_id'	=> $this->taxID,
					'label'	=> __( 'Select '.$this->single_up.' Set (default "all")', 'pagelines' ),
					'default'	=> false
				),
				array(
					'key'	=>	'order',
					'type'		=> 'select',
					'label'	=> __( 'Order of '.$this->multiple, 'pagelines' ),
					'opts'	=> array(
						'ASC'		=> array( 'name' => __( 'Ascending', 'pagelines' ) ),
						'DESC'		=> array( 'name' => __( 'Descending (default)', 'pagelines' ) ),
					)
				),
				array(
					'key'	=>	'orderby',
					'type'		=> 'select',
					'label'	=> __( 'Orderby', 'pagelines' ),
					'opts'	=> array(
						'title'		=> array( 'name' => __( 'Order by title.', 'pagelines' ) ),
						'name'		=> array( 'name' => __( 'Order by post name (post slug).', 'pagelines' ) ),
						'date'		=> array( 'name' => __( 'Order by date.', 'pagelines' ) ),
						'modified'	=> array( 'name' => __( 'Order by last modified date.', 'pagelines' ) ),
						'ID'		=> array( 'name' => __( 'Order by post id.', 'pagelines' ) ),
						'author'		=> array( 'name' => __( 'Order by author.', 'pagelines' ) ),
						'none'		=> array( 'name' => __( 'No order.', 'pagelines' ) ),
					)
				)
			)
		);
		$opts[] = array(
			'key'		=> 'layout_settings',
			'type'		=>  'multi',
			'col'		=> 3,
			'title'		=> __( 'Layout & Query Params', 'pagelines' ),
			'opts' => array(
				array(
					'key'	=>	'col_num',
					'type'	=> 'select',
					'label'	=> __( 'Number of columns', 'pagelines' ),
					'opts'	=> array(
						'1'		=> array( 'name' => __( '1', 'pagelines' ) ),
						'2'		=> array( 'name' => __( '2', 'pagelines' ) ),
						'3'		=> array( 'name' => __( '3', 'pagelines' ) ),
						'4'		=> array( 'name' => __( '4', 'pagelines' ) ),
						'5'		=> array( 'name' => __( '5', 'pagelines' ) ),
						'6'		=> array( 'name' => __( '6', 'pagelines' ) ),
					),
				),
				array(
					'key'	=>	'slides_num',
					'type'			=> 'text',
					'label'	=> __( 'Number of '.$this->multiple.' to use (default all)', 'pagelines' ),
					'default'	=> false
				),
				array(
					'key'	=>	'slide_gutter',
					'type'			=> 'text',
					'label'	=> __( 'Gutter between '.$this->multiple.' (default 0)', 'pagelines' ),
					'default'	=> false
				),
				array(
					'key'	=>	'equal_height',
					'type'			=> 'check',
					'label'	=> __( 'Enable variable items height', 'pagelines' ),
				)
			)
		);
		$opts[] = array(
			'key'		=> 'text_settings',
			'type'		=>  'multi',
			'col'		=> 3,
			'title'		=> __(  $this->single_up.' Content Options', 'pagelines' ),
			'opts' => array(
				array(
					'key'	=>	'text_italic',
					'type'			=> 'check',
					'label'	=> __( 'Italic text style of '.$this->single.' content', 'pagelines' ),
				),
				array(
					'key'	=>	'text_bold',
					'type'			=> 'check',
					'label'	=> __( 'Bold text style of '.$this->single.' content', 'pagelines' ),
				),
				array(
					'key'	=>	'text_font',
					'type' 			=> 'type',
					'label'	=> __( 'Choose '.$this->single_up.' text font', 'pagelines' ),
				)
			)
		);
		$opts[] = array(
			'key'		=> 'social_settings',
			'type'		=>  'multi',
			'col'		=> 2,
			'title'		=> __( 'Social Links Params', 'pagelines' ),
			'opts' => array(
				array(
					'key'	=>	'use_social',
					'type'			=> 'check',
					'label'	=> __( 'Hide social Icons', 'pagelines' ),
				),
				array(
					'key'	=>	'social_icon_variant',
					'type'		=> 'select',
					'label'	=> __( 'Social Icon Set', 'pagelines' ),
					'opts'	=> array(
						'sign'		=> array( 'name' => __( 'Square/Sign (Default)', 'pagelines' ) ),
						'simbol'		=> array( 'name' => __( 'Simbol', 'pagelines' ) ),
					)
				),
				array(
					'key'	=>	'social_icon_size',
					'type'		=> 'select',
					'label'	=> __( 'Social Icons Size', 'pagelines' ),
					'opts'	=> array(
						'1x'		=> array( 'name' => __( 'Normal (1em)', 'pagelines' ) ),
						'large'		=> array( 'name' => __( '1.33x', 'pagelines' ) ),
						'2x'		=> array( 'name' => __( '2x (default)', 'pagelines' ) ),
						'3x'		=> array( 'name' => __( '3x', 'pagelines' ) ),
						'4x'		=> array( 'name' => __( '4x', 'pagelines' ) ),

					)
				),


			)
		);
		$opts[] = array(
			'key'	=> 'bg_colors',
			'type' 	=> 	'multi',
			'col'	=> 1,
			'title' => __( 'Background Colors', 'pagelines' ),
			'opts'	=> array(
				array(
					'key'           => 'templatebg',
					'type'       => 'color',
					'label' => __( 'Container Background', 'pagelines' ),
					'default'	=> '',
				),
				array(
					'key'           => 'singlebg',
					'type'       => 'color',
					'label' => __( 'Single Item Background', 'pagelines' ),
					'default'	=> '',
				),
				array(
					'key'           => 'imgbg',
					'type'       => 'color',
					'label' => __( 'Image Background', 'pagelines' ),
					'default'	=> ''
				),
				array(
					'key'           => 'groupbg1',
					'type'       => 'color',
					'label' => __( 'Group 1 Background', 'pagelines' ),
					'default'	=> ''
				),
				array(
					'key'           => 'groupbg2',
					'type'       => 'color',
					'label' => __( 'Group 2 Background', 'pagelines' ),
					'default'	=> '',
					'ref'	=> __('Some meta fields are grouped (depends on template) .', 'pagelines')
				)
			)
		);
		$opts[] = array(
			'key'	=> 'txt-colors',
			'type' 	=> 	'multi',
			'col'	=> 2,
			'title' => __( 'Text Colors', 'pagelines' ),
			'opts'	=> array(
				array(
					'key'           => 'title-color',
					'type'          => 'color',
					'label'    => __( 'People Title Color', 'pagelines' ),
					 'default'	=> '',
				),
				array(
					'key'           => 'content-color',
					'type'          => 'color',
					'label'    => __( 'Content Color', 'pagelines' ),
					 'default'	=> '',
				),
				array(
					'key'           => 'position-color',
					'type'          => 'color',
					'label'    => __( 'Position Color', 'pagelines' ),
					 'default'	=> '',
				),
				array(
					'key'           => 'company-color',
					'type'          => 'color',
					'label'    => __( 'Company Color', 'pagelines' ),
					 'default'	=> '',
				),
				array(
					'key'           => 'custom1-color',
					'type'          => 'color',
					'label'    => __( 'Custom Text 1 Color', 'pagelines' ),
					 'default'	=> '',
				),
				array(
					'key'           => 'custom2-color',
					'type'          => 'color',
					'label'    => __( 'Custom Text 2 Color', 'pagelines' ),
					 'default'	=> '',
				),
				array(
					'key'           => 'icon-color',
					'type'          => 'color',
					'label'    => __( 'Social Icons Color', 'pagelines' ),
					 'default'	=> '',
				),
				array(
					'key'           => 'iconhover-color',
					'type'          => 'color',
					'label'    => __( 'Social Icons Hover Color', 'pagelines' ),
					 'default'	=> '',
				)
			)
		);
		return $opts;
	}

	//master template selector for opts
	function opt_set_select(){
		$dir 	= $this->base_dir.'/master-template/';
		$files = glob($dir.'*.json');
		$array 	= array();
		foreach ($files as $filename) {
			$file 		= basename($dir.$filename, ".json");
			$array[$file] 	= array( 'name' => $file );
		}
		return $array;
	}

	//template list for section_opts()
	function get_template_selectvalues(){
		$dir 	= $this->base_dir.'/templates/';
		$files = glob($dir.'*.less');
		$array 	= array();
		foreach ($files as $filename) {
			$file 		= basename($dir.$filename, ".less");
			$array[$file] 	= array( 'name' => $file );
		}
		return $array;
	}

	//section persistent
	function section_persistent(){
		//set post
		$this->post_type_setup();
		if(!class_exists('RW_Meta_Box')) {
			add_action( 'admin_notices',array(&$this, 'people_lud_notice') );
		} else  //meta setup
			add_action( 'admin_init',array(&$this, 'post_meta_setup') );
	}

	//notice for metabox
	function people_lud_notice(){
		echo '<div class="updated">
			<p>For the <strong>People Lud</strong> you need to install the <strong>Meta Box</strong> plugin by <a href="http://www.deluxeblogtips.com/" >Rilwis</a>. It is well tested, <strong>free</strong>, open source solution that will be seamlessly integrated once you install it. <strong>It does not require your attention.</strong>
			You can get it from <a href="http://wordpress.org/plugins/meta-box" target="_blank"><strong>here</strong></a>.</p>
		</div>';
	}

	//post meta - uses MetaBox plugin
	function post_meta_setup(){
		$type_meta_array = array(
			'settings' => array(
				'type'         =>  'multi_option',
				'title'        => __( 'Single '.$this->single_up.' Options', 'pagelines' ),
				'shortexp'     => __( 'Parameters', 'pagelines' ),
				'exp'          => __( '<strong>Single '.$this->single_up.' Options</strong><br>Add '.$this->single_up.' Metadata that will be used on the page.<br><strong>HEADS UP:<strong> Each template uses different set of metadata. Check out <a href="http://bestrag.net/'.$this->multiple.'-lud" target="_blank">demo page</a> for more information.', 'pagelines' ),
				'selectvalues' => array(
					'position' => array(
						'type'       => 'text',
						'inputlabel' => __( $this->single_up.'\'s position in the company', 'pagelines' )
					),
					'company' => array(
						'type'       => 'text',
						'inputlabel' => __( 'Company Name', 'pagelines' )
					),
					'img'  => array(
						'inputlabel' => __( 'Associate an image with this '.$this->single, 'pagelines' ),
						'type'       => 'thickbox_image'
					),
					'custom_text1' => array(
						'type'       => 'text',
						'inputlabel' => __( 'Custom Text/HTML/Shortcode 1', 'pagelines' )
					),
					'custom_text2' => array(
						'type'       => 'text',
						'inputlabel' => __( 'Custom Text/HTML/Shortcode 2', 'pagelines' )
					),
					'facebook' => array(
						'type'       => 'text',
						'inputlabel' => __( 'Facebook ID', 'pagelines' )
					),
					'twitter' => array(
						'type'       => 'text',
						'inputlabel' => __( 'Twitter ID', 'pagelines' )
					),
					'google' => array(
						'type'       => 'text',
						'inputlabel' => __( 'Google+ ID', 'pagelines' )
					),
					'linkedin' => array(
						'type'       => 'text',
						'inputlabel' => __( 'LinkedIn ID', 'pagelines' )
					),
					'github' => array(
						'type'       => 'text',
						'inputlabel' => __( 'Github ID', 'pagelines' )
					),
					'instagram' => array(
						'type'       => 'text',
						'inputlabel' => __( 'Instagram ID', 'pagelines' )
					)
				)
			)
		 );
		$fields = $type_meta_array['settings']['selectvalues'];
		$figo = array(); $findex = 0;

		foreach ($fields as $key => $value) {
			$figo[$findex] = array(
				'name'  => $value['inputlabel'],
				'id'    => $key,
				'type'  => $value['type'],
				'std'   => '',
				'class' => 'custom-class',
				'clone' => false
			);
			$findex++;
		}
		$metabox = array(
			'id'       => 'personal',
			'title'    => 'Personal Information',
			'pages'    => array( $this->multiple ),
			'context'  => 'normal',
			'priority' => 'high',
			'fields' => $figo
		);
		 new RW_Meta_Box($metabox);
	}

	//post type
	function post_type_setup() {
		$public_pt = false;
		//$public_pt = (pl_setting('disable_public_pt')) ? false : true;
		$args = array(
			'label'			=> __( $this->multiple_up, 'pagelines' ),
			'singular_label'		=> __( $this->single_up, 'pagelines' ),
			'description'		=> __( 'For creating '.$this->multiple.' items.', 'taxonomies' ),
			'taxonomies'		=> array( $this->taxID ),
			'menu_icon'		=> 'dashicons-groups',
			'public'			=> $public_pt,
			'show_ui'		=> true,
			'hierarchical'		=> true,
			'featured_image'	=> true,
			'has_archive'		=> true,
			'show_in_menu'		=> true,
			'show_in_nav_menus'	=> true,
			'show_in_admin_bar'	=> true,
			'menu_position'		=> 20,
			'can_export'		=> true,
		);
		$taxonomies = array(
			$this->taxID => array(
				'label'		=> __( $this->single_up.' Sets', 'pagelines' ),
				'singular_label'	=> __( $this->single_up.' Set', 'pagelines' ),
			)
		);
		$columns = array(
			'cb'		=> "<input type=\"checkbox\" />",
			'title'		=> 'Title',
			'description'	=> 'Description',
			'media'		=> 'Media',
			'company'=> 'Company',
			'position'	=> 'Position',
			$this->taxID	=> $this->single_up.' Sets',
		);
		$this->post_type = new PageLinesPostType( $this->multiple, $args, $taxonomies, $columns, array( &$this, 'column_display' ) );
		 // Defaults
		$this->post_type->set_default_posts( 'bestrag_default_posts', $this );
	}

	//default autogenerated posts
	function bestrag_default_posts($post_type){
		$def_posts  = array(
			array(
				'title'		=>   'Isabella Turner',
				'content'	=>   'I am a randomly generated user. Since we are using Lorem ipsum dolor sit amet, con se ctetur do lore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea. Give us a try!',
				'position'	=>   'Random User',
				'company'	=>   'Bestrag',
				'custom_text1'	=>   '[pl_button type="primary" size="default" link="mailto:#"]Email Me[/pl_button]',
				'custom_text2'	=>   '',
				'img'		=>   $this->base_url.'/images/isabel.jpg',
				'demo'		=> true,
				'facebook'	=> '#',
				'twitter'		=> '#',
				'google'	=> '#',
				'linkedin'	=> '#',
				'github'		=> '#',
				'instagram'	=> '#',
			),
			array(
				'title'		=>   'Paul Sanders',
				'content'	=>   'Since we are using Lorem ipsum dolor sit amet, I am a randomly generated user, con se ctetur adip is cing elit, sed do eiusmod tempor in cididunt ut labore et do lore magna aliqua. Ut enim ad minim veniam. Give us a try!',
				'position'	=>   'Random Man',
				'company'	=>   'RandomR',
				'custom_text1'	=>   '[pl_button type="primary" size="default" link="mailto:#"]Email Me[/pl_button]',
				'custom_text2'	=>   '',
				'img'		=>   $this->base_url.'/images/paul.jpg',
				'demo'		=> true,
				'facebook'	=> '#',
				'twitter'		=> '#',
				'google'	=> '#',
				'linkedin'	=> '#',
				'github'		=> '#',
				'instagram'	=> '#',
			),
			array(
				'title'		=>   'Evelyn Baker',
				'content'	=>   ' I am a randomly generated user. Since we are using Lorem ipsum dolor sit amet, con se ctetur adip is cing elit, sed do eiusmod tempor in cididunt ut labore et do lore magna aliqua. Ut enim ad minim veniam. Give us a try! Since we are using Lorem ipsum. Give us a try!',
				'position'	=>   'Random Position',
				'company'	=>   'RandomCo',
				'custom_text1'	=>   '[pl_button type="primary" size="default" link="mailto:#"]Email Me[/pl_button]',
				'custom_text2'	=>   '',
				'img'		=>   $this->base_url.'/images/evelin.jpg',
				'demo'		=> true,
				'facebook'	=> '#',
				'twitter'		=> '#',
				'google'	=> '#',
				'linkedin'	=> '#',
				'github'		=> '#',
				'instagram'	=> '#',
			),
			array(
				'title'		=>   'Kenzi Allen',
				'content'	=>   ' I am a randomly generated user. Since we are using Lorem ipsum dolor sit amet, con se ctetur adip is cing elit, sed do eiusmod tempor in cididunt ut labore et do lore magna aliqua. Ut enim ad minim veniam. Give us a try! Since we are using Lorem ipsum. Give us a try!',
				'position'	=>   'Inspire Others',
				'company'	=>   'iInspireCo',
				'custom_text1'	=>   '[pl_button type="primary" size="default" link="mailto:#"]Email Me[/pl_button]',
				'custom_text2'	=>   '',
				'img'		=>   $this->base_url.'/images/kenzi.jpg',
				'demo'		=> true,
				'facebook'	=> '#',
				'twitter'		=> '#',
				'google'	=> '#',
				'linkedin'	=> '#',
				'github'		=> '#',
				'instagram'	=> '#',
			),
			array(
				'title'		=>   'Kim Jones',
				'content'	=>   ' I am a randomly generated user. Since we are using Lorem ipsum dolor sit amet, con se ctetur adip is cing elit, sed do eiusmod tempor in cididunt ut labore et do lore magna aliqua. Ut enim ad minim veniam. Give us a try! Since we are using Lorem ipsum. Give us a try!',
				'position'	=>   'Creative Position',
				'company'	=>   'RandomCo',
				'custom_text1'	=>   '[pl_button type="primary" size="default" link="mailto:#"]Email Me[/pl_button]',
				'custom_text2'	=>   '',
				'img'		=>   $this->base_url.'/images/kim.jpg',
				'demo'		=> true,
				'facebook'	=> '#',
				'twitter'		=> '#',
				'google'	=> '#',
				'linkedin'	=> '#',
				'github'		=> '#',
				'instagram'	=> '#',
			),
			array(
				'title'		=>   'Nathan Rodriquez',
				'content'	=>   ' I am Nathan Rodriquez a randomly generated user. Since we are using Lorem ipsum dolor sit amet, con se ctetur adip is cing elit, sed do eiusmod tempor in cididunt ut labore et do lore magna aliqua. Ut enim ad minim veniam. Give us a try! Since we are using Lorem ipsum. Give us a try!',
				'position'	=>   'iImagineCo',
				'company'	=>   'ImagineCo',
				'custom_text1'	=>   '[pl_button type="primary" size="default" link="mailto:#"]Email Me[/pl_button]',
				'custom_text2'	=>   '',
				'img'		=>   $this->base_url.'/images/nathan.jpg',
				'demo'		=> true,
				'facebook'	=> '#',
				'twitter'		=> '#',
				'google'	=> '#',
				'linkedin'	=> '#',
				'github'		=> '#',
				'instagram'	=> '#',
			)

		);
		foreach( $def_posts as $p ){
			$defaults			= array();
			$defaults['post_title']		= $p['title'];
			$defaults['post_content']	= $p['content'];
			$defaults['post_type']		= $post_type;
			$defaults['post_status']		= 'publish';
			$id				= wp_insert_post( $defaults );
			update_post_meta( $id, 'name', $p['name'] );
			update_post_meta( $id, 'name_url', $p['name_url'] );
			update_post_meta( $id, 'position', $p['position'] );
			update_post_meta( $id, 'company', $p['company'] );
			update_post_meta( $id, 'company_url', $p['company_url'] );
			update_post_meta( $id, 'custom_text1', $p['custom_text1'] );
			update_post_meta( $id, 'custom_text2', $p['custom_text2'] );
			update_post_meta( $id, 'img', $p['img'] );
			update_post_meta( $id, 'demo', $p['demo'] );
			update_post_meta( $id, 'facebook', $p['facebook'] );
			update_post_meta( $id, 'twitter', $p['twitter'] );
			update_post_meta( $id, 'google', $p['google'] );
			update_post_meta( $id, 'linkedin', $p['linkedin'] );
			update_post_meta( $id, 'github', $p['github'] );
			update_post_meta( $id, 'instagram', $p['instagram'] );
			wp_set_object_terms( $id, 'default-'.$this->multiple, $this->taxID );
		}
	}

	//post type admin side columns
	function column_display( $column ) {
		global $post;
		switch ( $column ) {
		case 'description':
			echo the_excerpt();
			break;
		case 'media':
			$is_demo_post = get_post_meta( $post->ID, 'demo', true );
			$img = get_post_meta( $post->ID, 'img', true );
			// check if the custom field has a value
			if( ! empty( $is_demo_post ) ) {
				if ( $img ) echo '<img src="'.$img.'" style="max-width: 80px; margin: 10px; border: 1px solid #ccc; padding: 5px; background: #fff" />';
			}
			else {
				if ( $img ) echo wp_get_attachment_image($img, array(80, 80));
			}
			break;
		case 'company':
			if ( get_post_meta( $post->ID, 'company', true ) )
				echo get_post_meta( $post->ID, 'company', true );
			break;
		case 'position':
			if ( get_post_meta( $post->ID, 'position', true ) )
				echo get_post_meta( $post->ID, 'position', true );
			break;
		case $this->taxID:
			echo get_the_term_list( $post->ID, $this->taxID, '', ', ', '' );
			break;
		}
	}

	function update_lud_settings($template){
		$this->opt_update('opt_set_select', null, 'local');
		$default = array(
			'template_name' => 0,
			'taxonomy' => 0,
			'order' => 0,
			'orderby' => 0,
			'col_num' => 0,
			'slides_num' => 0,
			'slide_gutter' => 0,
			'equal_height' => 0,
			'text_italic' => 0,
			'text_bold' => 0,
			'text_font' => 0,
			'use_social' => 0,
			'social_icon_variant' => 0,
			'social_icon_size' => 0,
			'fluid' => 0,
			'opt_set_info' => $template
		);
		$clone_id = $this->meta['clone'];
		$data_path = $this->base_dir.'/master-template/';
		$opts_json	= (file_exists($data_path.$template.'.json')) ? file_get_contents($data_path.$template.'.json')  : array() ;
		$opts_json = json_decode($opts_json, true);
		$opts = wp_parse_args( $opts_json, $default );
		foreach ($opts as $key => $value) {
			$this->opt_update($key, $value, 'local');
			$this->meta['set'][$key] = $value;
		}
	}

	//update section specific colors - moved from global to local in ver. 1.2
	function update_lud_colors(){
		$global_colors = array('templatebg' => '', 'singlebg' => '', 'groupbg' => '', 'imgbg' => '', 'title-color' => pl_setting('text_primary'), 'content-color' => pl_setting('text_primary'), 'position-color' => pl_setting('text_primary'), 'company-color' => pl_setting('text_primary'), 'custom1-color' => pl_setting('text_primary'), 'custom2-color' => pl_setting('text_primary'), 'icon-color' => pl_setting('linkcolor'), 'iconhover-color' => '#000');
		foreach ($global_colors as $key => $value) {
			$global_color = pl_setting($this->prefix.'-'.$key);
			if($global_color && $global_color !== $value){
				$group_num = ($key === 'groupbg') ? '1' :  '';
				$this->opt_update($key.$group_num, $global_color, 'local');
				$this->meta['set'][$key] = $global_color;
				pl_setting_update($this->prefix.'-'.$key);
			}
		}
	}

}//EOC