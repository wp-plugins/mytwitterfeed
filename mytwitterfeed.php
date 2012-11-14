<?php

/*

	Plugin Name: MyTwitterFeed
	Plugin URI: http://www.wmappz.com/wordpress/mytwitterfeed
	Description: Show your latest tweets from your twitter account
	Author: WmAppz
	Version: 1.0
	Author URI: http://www.wmappz.com
	
	
    Copyright 2012  WmApzz (email : mytwitterfeed@wmappz.com)

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

// widget

class MyTwitterFeed extends WP_Widget {
	
	function MyTwitterFeed() {
		$widget_options = array(
		'classname'		=>		'mytwitterfeed-widget',
		'description' 	=>		'Widget which puts the twitter feed on your website.'
		);
		
		parent::WP_Widget('mytwitterfeed-widget', 'MyTwitterFeed', $widget_options);
	}
	
	function widget($args, $instance) {
		extract($args);
		$title = apply_filters( 'widget_title', 'Tweets @'.$instance['username'] );
		?>
		<?php echo $before_widget; 
if ( ! empty( $title ) )
			echo $before_title . $title . $after_title;

		echo '<div class="mytwitterfeed"> 

				<div class="content_tweets_'.$this->get_field_id('id').'"> </div> 
				<div class="tweets_footer">
					<span id="bird"></span>
				</div> 
			</div>';?>

		<? showtweets($instance['username'],$instance['limit'], $instance['showtags'], $instance['showmention']); ?>

		<?php echo $after_widget; ?>
		<?php 
	}

	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['username'] = strip_tags( $new_instance['username'] );
		$instance['limit'] = strip_tags( $new_instance['limit'] );
		$instance['showtags'] = strip_tags( $new_instance['showtags'] );
		$instance['showmention'] = strip_tags( $new_instance['showmention'] );

		return $instance;
	}

	
	function form($instance) {
		?>
		<p><label for="<?php echo $this->get_field_id('username')?>">
		Twitter account:</label>
		<input class="widefat" id="<?php echo $this->get_field_id('username')?>" 
		name="<?php echo $this->get_field_name('username')?>"
		type="text" value="<?php echo $instance['username'];?>"  />
		</p>
		<p><label for="<?php echo $this->get_field_id('limit')?>">
		Number of tweets:</label>
		<input class="widefat" id="<?php echo $this->get_field_id('limit')?>" 
		name="<?php echo $this->get_field_name('limit')?>"
		type="text" value="<?php echo $instance['limit'];?>"  />
		</p>
		<p>
		<input type="checkbox" id="<?php echo $this->get_field_id('showtags')?>" 
		name="<?php echo $this->get_field_name('showtags')?>" <? if ($instance['showtags']) { echo"checked='checked'"; } ?>"/>
		<label for="<?php echo $this->get_field_id('showtags')?>">
		Show # tags</label>
		</p>
		<p>
		<input type="checkbox" id="<?php echo $this->get_field_id('showmention')?>" 
		name="<?php echo $this->get_field_name('showmention')?>" <? if ($instance['showmention']) { echo"checked='checked'"; } ?>" />
		<label for="<?php echo $this->get_field_id('showmention')?>">
		Show @ mentions</label>
		</p>
		<?php 
	}
}

function mytwitterfeed_widget_init() {
	register_widget('MyTwitterFeed');
}
add_action('widgets_init', 'mytwitterfeed_widget_init');


	function showtweets($username,$limit,$showtags,$showmentions) {

	if (!ctype_digit($limit)) { $limit=10;} else if ($limit<1) { $limit=10; }

	$jsonurl = "http://api.twitter.com/1/statuses/user_timeline.json?screen_name=$username&count=$limit&include_entities=1&include_rts=true";
	$json = file_get_contents($jsonurl,0,null,null);
	$json_output = json_decode($json);

	foreach ( $json_output as $trend )
{
	$image=$trend->user->profile_image_url;
	$screenname=$trend->user->screen_name;
	$name=$trend->user->name;
	
	$tweet=$trend->text;

	$hashtags=$trend->entities->hashtags;
	
	foreach ( $hashtags as $tag) 
	{ $replace="#" . $tag->text; 
	if ($showtags)
	{ $tweet=str_replace($replace,"<a href=\"http://www.twitter.com/search?q=%23$tag->text\" target=\"_blank\">$replace</a>",$tweet); } else
	{ $tweet=str_replace($replace,"",$tweet); }
	}

	$mentions=$trend->entities->user_mentions;
	
	foreach ( $mentions as $mention) 
	{ $replace="@" . $mention->screen_name; 
	if ($showmentions)
	{ $tweet=str_replace($replace,"<a href=\"http://www.twitter.com/$mention->screen_name\" target=\"_blank\">$replace</a>",$tweet); } else
	{ $tweet=str_replace($replace,"",$tweet); }
	}


	$links=$trend->entities->urls;
	foreach ( $links as $link) 
	{ 
	$tweet=str_replace($link->url,"<a href=\"$link->url\" target=\"_blank\">$link->url</a>",$tweet);
	}
	
	echo "<div style=\"clear:both;margin-bottom: 5px; padding-bottom: 5px; overflow: auto; border-bottom: solid 1px #CCC;\" class=\"mytwitterfeed_tweet\"><div style=\"float: left; width: 60px;\"><img  src=\"$image\"></div><div style=\"margin-left: 60px; \"><a style=\"text-decoration:underline\" href=\"http://www.twitter.com/$screenname\" target=\"_blank\">@$name</a>: $tweet</div></div>";
}

}