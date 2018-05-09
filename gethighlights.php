<?php
require_once('php/autoloader.php');
include_once('php/simple_html_dom.php');
$feed = new SimplePie();
 
// Set which feed to process
$url="http://feeds.feedburner.com/latest-football-highlight";
 $feed->set_feed_url($url);
// Run SimplePie.
$feed->init();
 
// This makes sure that the content is sent to the browser as text/html and the UTF-8 character set (since we didn't change it).
$feed->handle_content_type();
foreach ($feed->get_items() as $item):
     $category=array(1);
           $html=  file_get_html($item->get_permalink());
            $title=$html->find('div.post',0)->find('h1',0)->innertext;
            //$league_title=$html->find('div.entry',0)->find('p',0)->find('strong',1)->find('a',0)->innertext;
            $league_title=$html->find('div.entry',0)->find('a',0)->innertext;
            $videourl=$html->find('div[id=highlights]',0)->find('p',0)->find('iframe',0)->src;
            $desc=explode('|',strip_tags($html->find('p.postmeta',0)->innertext));
            $checkhash=md5($descritpion);
            $title.= ' '.$desc[0];
            $a=explode('vs',$desc[1]);
            $b=rtrim($a[1],'Highlights');
            $tags=$desc[1].','.$a[0].','.$b;
            
            switch ($desc[1]){
                case 'English Premier League (EPL)':
                    array_push($category,119);
                    $imagename="wp-content/uploads/dmimages/epl.jpg";
                    break;
                case 'Champions League';
                    array_push($category,122);
                    $imagename="wp-content/uploads/dmimages/uefa-champions.jpg";
                    break;
                case 'Uefa Europa League';
                    array_push($category,116);
                    $imagename="wp-content/uploads/dmimages/europaleague.png";
                    break;
                case 'Bundesliga';
                    array_push($category,1);
                    $imagename="wp-content/uploads/dmimages/bundesliga.gif";
                    break;
                case 'Serie A';
                    array_push($category,121);
                    $imagename="wp-content/uploads/dmimages/seriea.gif";
                    break;
                case 'La Liga';
                    array_push($category,120);
                    $imagename="wp-content/uploads/dmimages/laliga.gif";
                    break;
            }
            
            
            $result1 = mysql_query("SELECT vid_hash FROM videos WHERE vid_hash='$checkhash'", $db_con);
            if(mysql_num_rows($result1)<1)
            {
                mysql_query("INSERT ignore INTO videos (vid_hash,matchId) values('$checkhash','Football Highlights')", $db_con) ;
                 $content= '<div class="size"><iframe src="//'.$videourl.'" frameborder="0" class="size" allowfullscreen ></iframe></div>'
                . '<div><p>'.$descritpion. '</p></div>';
                //$slugres=explode;
                $slug=to_permalink($title);
                $cat = wp_create_category($league_title);
                $category=array(1,$cat);
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
endforeach;

