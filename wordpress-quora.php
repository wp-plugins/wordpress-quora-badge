<?php
/*
Plugin Name: WordPress Quora Badge
Plugin URI: http://wpoid.com/plugins
Description: A plugin to show your Quora activities. Brought to you from WPoid (http://twitter.com/wpoid).
Author: Aman Kumar Jain
Version: 0.2.5
Author URI: http://amanjain.com
*/

/*
This program is free software; you can redistribute it and/or modify 
it under the terms of the GNU General Public License as published by 
the Free Software Foundation; version 2 of the License.

This program is distributed in the hope that it will be useful, 
but WITHOUT ANY WARRANTY; without even the implied warranty of 
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the 
GNU General Public License for more details. 

You should have received a copy of the GNU General Public License 
along with this program; if not, write to the Free Software 
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA 
*/

if(!class_exists('WP_Quara_badge'))
{
	class WP_Quara_badge
	{
		function widget_init()
		{
			wp_register_sidebar_widget("wp_quara_badge", "WordPress Quora Badge",   array(&$this, 'widget_display'));
			register_widget_control("wp_quara_badge", array(&$this, 'badge_control'));
		}
		
		function badge_control()
		{
			$data = get_option('wp_quora_badge');
			if(isset($_POST['wp_quora_badge_title']))
			{
				$data['title'] = attribute_escape($_POST['wp_quora_badge_title']);
				$profile=attribute_escape(rtrim(trim($_POST['wp_quora_badge_profile_link']), '/'));
				if(($result=$this->get_content($profile)))
				{
					if($result['success']===true)
					{
						$data['profile'] = $profile;
						$data['data'] = $result['data'];
						if(($activity = $this->fetch_rss($profile."/rss", $data['data']['name'])))
						{
							$data['data']['activity'] = $activity;
						}
					}
					else
					{
						?>
						<p class="widget-error"><strong><?php echo $result['reason']; ?></strong></p>
						<?php
					}
				}
				update_option('wp_quora_badge', $data);
				$data['profile'] = $profile;
			}
			include(dirname(__FILE__).DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.'widget_control.php');
		}
		
		function widget_display($args)
		{
			if(($data=get_option('wp_quora_badge')))
			{
				include(dirname(__FILE__).DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.'widget_view.php');
			}
		}
		
		function fetch_rss($url, $name)
		{
			include_once(ABSPATH.WPINC.DIRECTORY_SEPARATOR.'feed.php');
			$rss = fetch_feed($url);
			if(!is_wp_error($rss))
			{
				$rss_item=array();
				$maxitems = $rss->get_item_quantity();
				$rss_items = $rss->get_items(0, $maxitems>5?5:$maxitems); 

				foreach($rss_items as $item):
					preg_match_all('#<div[^>]*>(.*?)</div>#', $item->get_description(), $matches); 
					$matches[1][0] = explode(": ", $matches[1][0]);
					if(sizeOf($matches[1][0])==2)
					{
						$matches[1][0] = $matches[1][0][1];
					}
					else
					{
						$matches[1][0]=$matches[1][0][0];
					}
					$matches[1][0]=trim($matches[1][0]);
					$matches[1][0]=substr($matches[1][0], strlen($name), -1).",";
					if($matches[1][0] == ',')
					{
						$matches[1][0] = '';
					}
					$rss_item[]=
						$matches[1][0].
						"<a href='".$item->get_permalink()."' title='Posted on ".$item->get_date('j F Y | g:i a')."'>".$item->get_title()."</a>";
				endforeach;
				return $rss_item;
			}
			return false;
		}
		
		function get_content($url)
		{
			include_once(ABSPATH.WPINC.DIRECTORY_SEPARATOR.'http.php');
			$html = wp_remote_get($url);
			if(!is_wp_error($html))
			{
				$html=$html['body'];
				$html = mb_convert_encoding($html, 'HTML-ENTITIES', "UTF-8");
				$dom = new DOMDocument();
				@$dom->loadHTML($html);
				$xpath=new Domxpath($dom);
				$items=$xpath->query("//div[contains(@class, 'profile_icons')]");
				
				if($items->length!==0)
				{
					$name=$items->item(0)->parentNode;
					$name->removeChild($name->firstChild);
					//$img=$xpath->query("//img[contains(@class, 'profile_photo_img')]");
					$img=$xpath->query("//div[contains(@class, 'side_col')]//img[contains(@class, 'profile_photo_img')]");
					$count=$xpath->query("//div[contains(@class, 'mini_count')]");
					if($name && $img->length>=1 && $count->length==3)
					{
						$all_count=array();
						$all_count['Followers']=$this->parseNumber($count->item(0)->nodeValue);
						$all_count['Following']=$this->parseNumber($count->item(1)->nodeValue);
						$all_count['@Mentions']=$this->parseNumber($count->item(2)->nodeValue);
						return array('success'=>true, 'data'=>array(
																	'name'=>$this->innerHTML($name),
																	'img'=>$img->item(0)->getAttribute('src'),
																	'count'=>$all_count
																));
					}
				}
				return array('success'=>false, 'reason'=>'Sorry! either the URL is incorrect, or the HTML structure of Quara profile page is changed.');
			}
			return array('success'=>false, 'reason'=>'Couldnot fetch profile page, please check if the URL is correct.');
		}
		
		function parseNumber($string)
		{
			return preg_replace("/[^0-9]/", '', $string);
		}
		
		function innerHTML($node)
		{
			$innerHTML = '';
			foreach($node->childNodes as $child)
			{
				$innerHTML .= $child->ownerDocument->saveXML($child);
			}
			return $innerHTML;
		}
		
		function cron()
		{
			if(($data = get_option('wp_quora_badge')))
			{
				if(($result=$this->get_content($data['profile'])))
				{
					if($result['success']===true)
					{
						$result['activity'] = $data['data']['activity'];
						$data['data'] = $result['data'];
						if(($activity = $this->fetch_rss($data['profile']."/rss", $data['data']['name'])))
						{
							$data['data']['activity'] = $activity;
						}
					}
				}
				update_option('wp_quora_badge', $data);
			}
		}
		
		function uninstall_cron()
		{
			wp_clear_scheduled_hook('wp_quora_badge_cron');
		}
		
		function check_installation()
		{
			if(!function_exists('phpversion') || intval(phpversion())<5)
			{
				trigger_error('PHP5 or greater is required to use this plugin.', E_USER_ERROR);
			}
			else if(!class_exists('DOMDocument') || !class_exists('Domxpath'))
			{
				trigger_error('Looks like libxml php extension is not installed. libxml is required to run this plugin.', E_USER_ERROR);
			}
		}
	}
}

$wp_quara_badge = new WP_Quara_badge();
register_activation_hook(__FILE__, array(&$wp_quara_badge, 'check_installation'));
add_action('wp_quora_badge_cron', array(&$wp_quara_badge, 'cron'));
if(!wp_next_scheduled('wp_quora_badge_cron'))
{
	wp_schedule_event(time(), 'hourly', 'wp_quora_badge_cron');
}
add_action('plugins_loaded', array(&$wp_quara_badge, 'widget_init'));
register_deactivation_hook(__FILE__, array(&$wp_quara_badge, 'uninstall_cron'));
