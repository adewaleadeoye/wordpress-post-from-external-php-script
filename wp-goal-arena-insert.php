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

$db_con = mysqli_connect($db['host'], $db['user'], $db['password'],$db['name']);
 //mysql_select_db($db['name'], $db_con);
 remove_filter('content_save_pre', 'wp_filter_post_kses');
 remove_filter('content_filtered_save_pre', 'wp_filter_post_kses');
 
$result1 = mysqli_query($db_con,"SELECT * FROM videos WHERE vid_processed='No' AND source='GoalsArena'");
if(mysqli_num_rows($result1)>=1)
{
    while($row=  mysqli_fetch_array($result1)){
        extract($row);
        $content= '<div class="size"><iframe src="'.$videourl.'" frameborder="0" class="size" allowfullscreen ></iframe></div>'
                . '<div><p>'.$description. '</p></div>';
        
        $cat=  explode(',', $category);
        //$cat=array($category);
       //$slug=to_permalink($title);
       $pid = wp_insert_post(array(
                                'post_title'    => $title,
                                'post_content'  => $content,
                                //'post_name'      =>$slug,
                                'post_author'   => 1,
                                'post_type'     => 'post',
                                'post_status'   => 'publish',
                                'ping_status'    => 'open',
                                'comment_status' => 'open',
                                'post_category'  => $cat,
                                'tags_input'     => $tags
                            ));
          
          if ($pid){
              //echo 'PID:'.$pid;
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
              //echo ' Attach ID:'.$attach_id;
                // Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
                require_once('wp-admin/includes/image.php' );

                // Generate the metadata for the attachment, and update the database record.
                $imageloc="http://localhost/".$imagename;
                //$imageloc= $imagename;
                $attach_data = wp_generate_attachment_metadata( $attach_id, $imageloc );
//                echo ' Attach Data: ';
//                var_dump($attach_data);
                
                $updatestatus = wp_update_attachment_metadata( $attach_id, $attach_data );
//                echo ' Update Data: ';
//                var_dump($updatestatus);
                
               $setstatus=set_post_thumbnail( $pid, $attach_id );
//               echo ' Set Data: ';
//               var_dump($setstatus);
               
               mysqli_query($db_con,"UPDATE videos SET vid_processed='Yes' WHERE vid_hash='$vid_hash'");
               echo $title.'<br/>';
          }

        }
        
    }

 add_filter('content_save_pre', 'wp_filter_post_kses');
    add_filter('content_filtered_save_pre', 'wp_filter_post_kses');
    mysqli_close($db_con); 