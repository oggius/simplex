<?php
$captchaCfg = array(
    'code' => '',
    'min_length' => 5,
    'max_length' => 5,
    'png_backgrounds' => array(ROOT . 'theme/images/captcha/bg.png'),
    'fonts' => array(ROOT . 'theme/fonts/times_new_yorker.ttf'),
    'characters' => 'abcdefghjkmnpqrstuvwxyz23456789',
    'min_font_size' => 12,
    'max_font_size' => 15,
    'color' => '#000',
    'angle_min' => 0,
    'angle_max' => 15,
    'shadow' => true,
    'shadow_color' => '#CCC',
    'shadow_offset_x' => -2,
    'shadow_offset_y' => 2
);