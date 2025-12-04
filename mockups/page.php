<?php get_header(); ?>
<?php include('includes/inc_header-interior.php'); ?>
    <div id="main" class="band">
        <div class="container">
            <div class="section_group">
                <div class="col main_col <?php if (get_field('map_code')) : ?>span_1_of_2<?php else: ?><?php if (get_field('hide_sidebar')) : ?>span_3_of_3<?php else: ?>span_3_of_4<?php endif; ?><?php endif; ?>"> <?php include('includes/inc_hr-top.php'); ?> <?php if ($post->post_content != "" || post_password_required()) { ?><?php if (have_posts()) : while (have_posts()) : the_post(); ?>
                        <div class="post_content" id="post-<?php the_ID(); ?>">
                            <div class="entry"> <?php the_content(); ?> </div>
                        </div> <?php endwhile; ?><?php else : ?><?php endif; ?><?php } else { ?><?php } ?> <?php if (!post_password_required()) { ?><?php include('includes/inc_main-sections.php'); ?><?php } else { ?><?php } ?> <?php /*?> <?php if(get_field('calendar_category')) : ?> <div class="calendar_category"> <div class=""> <h3 class="section_title"><?php $term = get_term( get_field( 'calendar_category')); echo $term->name; ?></h3> </div> <?php $recentposts = get_posts(array('numberposts' => -1, 'post_type' => 'calendar', 'orderby' => 'menu_order', 'order' => 'ASC', 'tax_query' => array( array( 'taxonomy' => 'calendar-cat', 'field' => 'term_id', 'terms' => get_post_meta($post->ID, 'calendar_category', true) )) )); ?> <div class=""> <div class=""> <?php foreach ($recentposts as $post) : setup_postdata($post); ?> <h5 class="post_title"><?php the_title(); ?></h5> <?php endforeach; ?> </div> </div> <?php wp_reset_query(); ?> </div> <?php else: ?><?php endif; ?> <?php */ ?> <?php if (get_field('calendar_category')) : ?>
                        <div class="calendar_sections"> <?php /*?><h2 class="section_title"><?php $term = get_term( get_field( 'calendar_category')); echo $term->name; ?></h2><?php */ ?> <?php $recentposts = get_posts(array('numberposts' => -1, 'post_type' => 'calendar', 'orderby' => 'menu_order', 'order' => 'ASC', 'tax_query' => array(array('taxonomy' => 'calendar-cat', 'field' => 'term_id', 'terms' => get_post_meta($post->ID, 'calendar_category', true))))); ?>
                            <div class="table table_select">
                                <div class="table_cell"><p class="select_title">Select an Academic Year:</p></div>
                                <div class="table_cell"><select
                                            id="cal_dropdown"> <?php foreach ($recentposts as $post) : setup_postdata($post); ?>
                                            <option value="option_<?php echo get_the_ID(); ?>"<?php if (get_field('featured')) { ?> selected<?php } else { ?><?php } ?>><?php the_title(); ?></option> <?php endforeach; ?>
                                    </select></div>
                            </div> <?php wp_reset_query(); ?> <?php $recentposts = get_posts(array('numberposts' => -1, 'post_type' => 'calendar', 'orderby' => 'menu_order', 'order' => 'ASC', 'tax_query' => array(array('taxonomy' => 'calendar-cat', 'field' => 'term_id', 'terms' => get_post_meta($post->ID, 'calendar_category', true))))); ?> <?php foreach ($recentposts as $post) : setup_postdata($post); ?>
                                <div id="option_<?php echo get_the_ID(); ?>"
                                     class="cal_sections"> <?php if (have_rows('sections')): ?>
                                        <div class="cal_sections_jump count_<?php echo $sections_count = count(get_field('sections')); ?>">
                                            <div class="table table_select">
                                                <div class="table_cell"><p class="select_title">Select a Semester:</p>
                                                </div>
                                                <div class="table_cell">
                                                    <div class=""> <?php while (have_rows('sections')): the_row(); ?><?php if (get_sub_field('title')) : ?>
                                                            <a class="ps2id btn"
                                                               href="#cal_section_<?php echo get_the_ID(); ?>_row_<?php echo get_row_index(); ?>">
                                                                <p><?php the_sub_field('title'); ?></p>
                                                            </a> <?php else: ?><?php endif; ?><?php endwhile; ?> </div>
                                                </div>
                                            </div>
                                        </div> <?php endif; ?>
                                    <div class="intro cal_post_title dark"><h6
                                                class="post_title"><?php the_title(); ?></h6>
                                    </div> <?php if (have_rows('sections')): ?>
                                        <div> <?php while (have_rows('sections')): the_row(); ?>
                                                <div id="cal_section_<?php echo get_the_ID(); ?>_row_<?php echo get_row_index(); ?>"
                                                     class="cal_section"> <?php if (get_sub_field('title')) : ?> <h3
                                                            class="cal_section_title"><?php the_sub_field('title'); ?></h3> <?php else: ?><?php endif; ?> <?php if (get_sub_field('row')): ?>
                                                        <div class="row"> <?php while (the_repeater_field('row')): ?>
                                                                <div class=""> <?php if (get_sub_field('intro')) : ?>
                                                                        <div class="entry"> <?php the_sub_field('intro'); ?> </div> <?php else: ?><?php endif; ?> <?php /*?> <?php $table = get_sub_field( 'table' ); if ( $table ) { echo '<div class="table_wrap"> <table class="table_content">'; if ( $table['header'] ) { echo '<thead>'; echo '<tr>'; foreach ( $table['header'] as $th ) { echo '<th><p>'; echo $th['c']; echo '</p></th>'; } echo '</tr>'; echo '</thead>'; } echo '<tbody>'; foreach ( $table['body'] as $tr ) { echo '<tr>'; foreach ( $tr as $td ) { echo '<td><p>'; echo $td['c']; echo '</p></td>'; } echo '</tr>'; } echo '</tbody>'; echo '</table></div>'; } ?> <?php */ ?> <?php if (get_sub_field('table_html')) : ?>
                                                                        <div class="table_wrap">
                                                                            <div class="table_html table_content"> <?php the_sub_field('table_html'); ?> </div>
                                                                        </div> <?php else: ?><?php endif; ?>
                                                                </div> <?php endwhile; ?> </div> <?php endif; ?>
                                                </div> <?php endwhile; ?> </div> <?php endif; ?>
                                </div> <?php endforeach; ?> <?php wp_reset_query(); ?>
                        </div> <?php if (get_field('calendar_category_conclusion')) : ?>
                            <div class="calendar_category_conclusion">
                                <div class="entry"> <?php the_field('calendar_category_conclusion'); ?> </div>
                            </div> <?php else: ?><?php endif; ?><?php else: ?><?php endif; ?>
                </div> <?php if (get_field('hide_sidebar')) : ?><?php else: ?>
                    <div class="col sidebar_col <?php if (get_field('map_code')) : ?>span_1_of_2<?php else: ?>span_1_of_4<?php endif; ?>">
                        <div class="sidebar sidebar_right"> <?php if (get_field('hide_sidebar_nav')) { ?><?php } else { ?><?php if ($post->post_parent) {
                                $ancestors = get_post_ancestors($post->ID);
                                $root = count($ancestors) - 1;
                                $parent = $ancestors[$root];
                                $children = wp_list_pages("title_li=&child_of=" . $parent . "&echo=0");
                            } else {
                                $parent = $post->ID;
                                $children = wp_list_pages("title_li=&child_of=" . $parent . "&echo=0");
                            }
                                $parent_title = get_the_title($parent);
                                $parent_link = get_permalink($parent);
                                if ($children) { ?>
                                    <div class="box sidebar_nav_box">
                                        <ul class="sidebar_nav">
                                            <li class="<?php if (is_page($parent)) {
                                                echo 'current_page_item';
                                            } ?> parent"><a title="<?php echo $parent_title; ?>"
                                                            href="<?php echo $parent_link; ?>"><?php echo $parent_title; ?></a>
                                            </li> <?php echo $children; ?> </ul>
                                    </div> <?php } ?><?php } ?> <?php $featured_team_members = get_field('featured_team_members');
                            if ($featured_team_members): ?>
                                <div class="featured_team_members">
                                    <div class="portal"> <?php foreach ($featured_team_members as $post): ?><?php setup_postdata($post); ?><?php include('includes/inc_team-member.php'); ?><?php endforeach; ?><?php wp_reset_postdata(); ?> </div>
                                </div> <?php endif; ?> <?php include('includes/inc_sidebar-content.php'); ?> <?php if (get_field('uga_site', 'option')) { ?><?php if (get_field('quick_links', 'option')): ?>
                                <div class="box quick_links_box dark">
                                    <ul class="quick_links"> <?php if (get_field('quick_links_title', 'option')) : ?>
                                            <li class="quick_links_title parent"> <?php the_field('quick_links_title', 'option'); ?> </li> <?php else: ?><?php endif; ?> <?php while (the_repeater_field('quick_links', 'option')): ?>
                                            <li><a class="more"
                                                   href="<?php if (get_sub_field('url', 'option')) : ?><?php the_sub_field('url', 'option'); ?><?php else: ?>#<?php endif; ?>"<?php if (get_sub_field('open_link_in_new_tab', 'option')) { ?> target="_blank"<?php } else { ?><?php } ?><?php if (get_sub_field('title', 'option')) : ?> title="<?php the_sub_field('title', 'option'); ?>"<?php else: ?><?php endif; ?>> <?php the_sub_field('title', 'option'); ?> </a>
                                            </li> <?php endwhile; ?> </ul>
                                </div> <?php endif; ?><?php } else { ?><?php } ?> <?php include('includes/inc_quick-links.php'); ?> <?php if (get_field('map_code')) : ?>
                                <div class="map"> <?php the_field('map_code'); ?> </div> <?php else: ?><?php endif; ?>
                        </div>
                    </div> <?php endif; ?> </div>
        </div>
    </div> <?php get_footer(); ?>