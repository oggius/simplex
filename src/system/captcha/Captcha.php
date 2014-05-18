<?php
namespace system\captcha;

use system\basic\exceptions\WrongConfigException;

class Captcha {
    /**
     * @var string
     */
    protected $_code;

    protected $_config = array();

    /**
     * @var Resource
     */
    protected $_imageRes;

    /**
     * @param array $captchaConfig
     */
    public function __construct(array $captchaConfig)
    {
        // Generate CAPTCHA code if not set by user
        if( empty($captchaConfig['code']) ) {
            $code = '';
            $length = rand($captchaConfig['min_length'], $captchaConfig['max_length']);
            while( strlen($code) < $length ) {
                $code .= substr($captchaConfig['characters'], mt_rand() % (strlen($captchaConfig['characters'])), 1);
            }
        }
        $this->_code = $code;
        $this->_config = $captchaConfig;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->_code;
    }

    /**
     * @throws \system\basic\exceptions\WrongConfigException
     */
    public function render()
    {
        $captchaConfig = $this->_config;
        if (empty($captchaConfig)) {
            throw new WrongConfigException('Captcha config not set', 500);
        }

        // Use milliseconds instead of seconds
        srand(microtime() * 100);

        // Pick random background, get info, and start captcha
        $background = $captchaConfig['png_backgrounds'][mt_rand(0, count($captchaConfig['png_backgrounds']) -1)];
        list($bg_width, $bg_height, $bg_type, $bg_attr) = getimagesize($background);

        // Create captcha object
        $captcha = imagecreatefrompng($background);
        imagealphablending($captcha, true);
        imagesavealpha($captcha , true);

        $color = $this->_hex2rgb($captchaConfig['color']);
        $color = imagecolorallocate($captcha, $color['r'], $color['g'], $color['b']);

        // Determine text angle
        $angle = rand( $captchaConfig['angle_min'], $captchaConfig['angle_max'] ) * (rand(0, 1) == 1 ? -1 : 1);

        // Select font randomly
        $font = $captchaConfig['fonts'][rand(0, count($captchaConfig['fonts']) - 1)];

        // Verify font file exists
        if( !file_exists($font) ) {
            throw new WrongConfigException('Font file not found: ' . $font);
        }

        //Set the font size.
        $font_size = rand($captchaConfig['min_font_size'], $captchaConfig['max_font_size']);
        $text_box_size = imagettfbbox($font_size, $angle, $font, $this->_code);

        // Determine text position
        $box_width = abs($text_box_size[6] - $text_box_size[2]);
        $box_height = abs($text_box_size[5] - $text_box_size[1]);
        $text_pos_x_min = 0;
        $text_pos_x_max = ($bg_width) - ($box_width);
        $text_pos_x = rand($text_pos_x_min, $text_pos_x_max);
        $text_pos_y_min = $box_height;
        $text_pos_y_max = ($bg_height) - ($box_height / 2);
        $text_pos_y = rand($text_pos_y_min, $text_pos_y_max);

        // Draw shadow
        if( $captchaConfig['shadow'] ){
            $shadow_color = $this->_hex2rgb($captchaConfig['shadow_color']);
            $shadow_color = imagecolorallocate($captcha, $shadow_color['r'], $shadow_color['g'], $shadow_color['b']);
            imagettftext($captcha, $font_size, $angle, $text_pos_x + $captchaConfig['shadow_offset_x'], $text_pos_y + $captchaConfig['shadow_offset_y'], $shadow_color, $font, $captchaConfig['code']);
        }

        // Draw text
        imagettftext($captcha, $font_size, $angle, $text_pos_x, $text_pos_y, $color, $font, $this->_code);

        // Output image
        imagepng($captcha);
    }

    /**
     * @param $hex_str
     * @param bool $return_string
     * @param string $separator
     * @return array|bool|string
     */
    private function _hex2rgb($hex_str, $return_string = false, $separator = ',') {
        $hex_str = preg_replace("/[^0-9A-Fa-f]/", '', $hex_str); // Gets a proper hex string
        $rgb_array = array();
        if( strlen($hex_str) == 6 ) {
            $color_val = hexdec($hex_str);
            $rgb_array['r'] = 0xFF & ($color_val >> 0x10);
            $rgb_array['g'] = 0xFF & ($color_val >> 0x8);
            $rgb_array['b'] = 0xFF & $color_val;
        } elseif( strlen($hex_str) == 3 ) {
            $rgb_array['r'] = hexdec(str_repeat(substr($hex_str, 0, 1), 2));
            $rgb_array['g'] = hexdec(str_repeat(substr($hex_str, 1, 1), 2));
            $rgb_array['b'] = hexdec(str_repeat(substr($hex_str, 2, 1), 2));
        } else {
            return false;
        }
        return $return_string ? implode($separator, $rgb_array) : $rgb_array;
    }

}