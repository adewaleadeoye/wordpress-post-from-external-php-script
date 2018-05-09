<?php
ini_set('error_reporting', E_ALL);
require_once 'wp-load.php';
require_once 'wp-admin/includes/taxonomy.php';
require_once 'wp-includes/link-template.php';
require_once 'wp-includes/plugin.php';
require_once 'wp-includes/post.php';
require_once('config.php');
//require_once('lib/twitteroauth.php');
include_once ('function.php');

$db_con = mysql_connect($db['host'], $db['user'], $db['password']);
 mysql_select_db($db['name'], $db_con);
 remove_filter('content_save_pre', 'wp_filter_post_kses');
 remove_filter('content_filtered_save_pre', 'wp_filter_post_kses');
 
$result1 = mysql_query("SELECT * FROM videos WHERE vid_processed='No' AND source='Dailymotion'", $db_con);
if(mysql_num_rows($result1)>=1)
{
    while($row=  mysql_fetch_array($result1)){
        extract($row);
        $content= '<div class="size"><iframe src="//'.$videourl.'?PARAMS" frameborder="0" class="size" allowfullscreen ></iframe></div>'
                . '<div><p>'.$descritpion. '</p></div>';
        
        $cat=  explode(',', $category);
        //$cat=array($category);
       $slug=to_permalink($title);
       $pid = wp_insert_post(array(
                                'post_title'    => $title,
                                'post_content'  => $content,
                                'post_name'      =>$slug,
                                'post_author'   => 1,
                                'post_type'     => 'post',
                                'post_status'   => 'publish',
                                'ping_status'    => 'open',
                                'comment_status' => 'open',
                                'post_category'  => $cat,
                                'tags_input'     => $tags
                            ));
          
          if ($pid){
              echo $pid;
              $filetype = wp_check_filetype( basename($imagename), null );
              $wp_upload_dir = wp_upload_dir();
              $attachment = array(
                'guid'           => $wp_upload_dir['url'] . '/' . basename($imagename), 
                'post_mime_type' => $filetype['type'],
                'post_title'     => preg_replace( '/\.[^.]+$/', '', basename($imagename) ),
                'post_content'   => '',
                'post_status'    => 'inherit'
               );
              $attach_id = wp_insert_attachment( $attachment, $imagename, $pid );

                // Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
                require_once('wp-admin/includes/image.php' );

                // Generate the metadata for the attachment, and update the database record.
                $attach_data = wp_generate_attachment_metadata( $attach_id, $imagename );
                wp_update_attachment_metadata( $attach_id, $attach_data );

                set_post_thumbnail( $pid, $attach_id );
             mysql_query("UPDATE videos SET vid_processed='Yes' WHERE vid_hash='$vid_hash'");
              echo $title.'<br/>';
          }

        }
        
    }

 add_filter('content_save_pre', 'wp_filter_post_kses');
    add_filter('content_filtered_save_pre', 'wp_filter_post_kses');
    mysql_close($db_con); 