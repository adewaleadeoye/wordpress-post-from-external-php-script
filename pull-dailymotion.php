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
 
$url="https://api.dailymotion.com/user/EuropeanLeagueLiveGoal/videos?fields=description,id,tags,thumbnail_480_url,title,&limit=10";
$data=json_decode(file_get_contents($url));
//var_dump($data);
 $all_cat = wp_create_category("All Goals");
foreach($data->list as $item)
{
   
    $title=rtrim($item->title,"HD");
    $t=explode(" | ",$title);
    if($t[0]=="All Goals"){
       $title="Match Highlights ".ltrim($title,"All Goals");
       //echo $title.'<br/>';
       $highlght_cat = wp_create_category("Match Highlights");
       $category=array(1,$all_cat,$highlght_cat);
      }
      else{
          $category=array(1,$all_cat);
           //echo $title.'<br/>';
        }
    $c=explode('-',$t[1]);
    $d=explode(' ',$c[1]);
    $tags=$c[0].",".$d[0];
    
    $image=stripcslashes($item->thumbnail_480_url);
    
    $image = file_get_contents($image);
    $imagename="wp-content/uploads/dmimages/".$item->id.".jpg";
    file_put_contents($imagename, $image);
    
    $descritpion=$item->description;
    $videourl='www.dailymotion.com/embed/video/'.$item->id;
    
    $checkhash=md5($descritpion);
    $result1 = mysql_query("SELECT vid_hash FROM videos WHERE vid_hash='$checkhash'", $db_con);
    if(mysql_num_rows($result1)<1)
    {
        mysql_query("INSERT ignore INTO videos (vid_hash,source) values('$checkhash','Dailymotion')", $db_con) ;
        $content= '<div class="size"><iframe src="//'.$videourl.'?PARAMS" frameborder="0" class="size" allowfullscreen ></iframe></div>'
                . '<div><p>'.$descritpion. '</p></div>';
        //$slugres=explode;
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
                                'post_category'  => $category,
                                'tags_input'     => $tags
                            ));
          
          if ($pid){
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
             
              echo $title.'<br/>';
          }

            
    }
}
 add_filter('content_save_pre', 'wp_filter_post_kses');
    add_filter('content_filtered_save_pre', 'wp_filter_post_kses');
    mysql_close($db_con); 