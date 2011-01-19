<?php if(!class_exists('WP_Quara_badge')){die();} ?>
<?php
echo $args['before_widget'];
echo $args['before_title'];
	echo $data['title'];
echo $args['after_title'];
?>
	<ul>
		<li style="display:block; overflow:auto;">
			<span style="display:block; float:left; width:100px; height:100px; margin:0px; padding:0px;"><img style="margin:0px; padding:0px;" src="<?php echo $data['data']['img']; ?>" alt="<?php echo $data['data']['name']; ?> on Quora"/></span>
			<span style="display:block; float:left; margin:0px; padding:0px; padding-left:10px;">
					<span style="display:block;"><a href="<?php echo $data['profile']; ?>"><?php echo $data['data']['name']; ?></a></span>
					<span style="display:block;"><a href="<?php echo $data['profile']; ?>/followers"><?php echo $data['data']['count']['Followers']; ?> Followers</a></span>
					<span style="display:block;"><a href="<?php echo $data['profile']; ?>/following"><?php echo $data['data']['count']['Following']; ?> Following</a></span>
					<span style="display:block;"><a href="<?php echo $data['profile']; ?>/mentions"><?php echo $data['data']['count']['@Mentions']; ?> Mentions</a></span>
			</span>
		</li>
		<?php
		if(isset($data['data']['activity']) && is_array($data['data']['activity']))
		{
			foreach($data['data']['activity'] as $activity)
			{
				?>
				<li><?php echo $activity; ?></li>
				<?php
			}
		}
		?>
	</ul>
<?php
echo $args['after_widget'];
?>