<?php if(!class_exists('WP_Quara_badge')){die();} ?>
<p>
    <label>
        Title<br/>
        <input class="widefat" name="wp_quora_badge_title" type="text" value="<?php if($data && isset($data['title'])){echo $data['title'];} ?>" />
    </label>
</p>
<p>
    <label>
        Quora Profile Link<br/>
        <input class="widefat" name="wp_quora_badge_profile_link" type="text" value="<?php if($data && isset($data['profile'])){echo $data['profile'];} ?>" />
    </label>
</p>
