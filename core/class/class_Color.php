<?php
/**
 * This file is part of the ImagePalette package.
 *
 * (c) Brian McDonald <brian@brianmcdonald.io>
 * (c) gandalfx - https://github.com/gandalfx
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * 
 * Represents an argb color
 * 
 * @author  Gandalfx
 * @package BrianMcdo\ImagePalette
 * 
 */
class Color {
    
    /**
     * Red value
     * @var int
     */
    public $r;
    
    /**
     * Green value
     * @var int
     */
    public $g;
    
    /**
     * Blue value
     * @var int
     */
    public $b;
    
    /**
     * Alpha value
     * @var int
     */
    public $a;


	/**
	 * Construct new Color
	 *
	 * @param int|array $color
	 * @param bool $short
	 */
	//颜色16进制转RGB
	static function hex2rgb($color){
		if ( $color[0] == '#' ) {
			$color = substr( $color, 1 );
		}
		
		if ( strlen( $color ) == 6 ) {
			list( $r, $g, $b ) = array( $color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5] );
		} elseif ( strlen( $color ) == 3 ) {
			list( $r, $g, $b ) = array( $color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2] );
		} else {
			return false;
		}
		$r = hexdec( $r );
		$g = hexdec( $g );
		$b = hexdec( $b );
		$a = hexdec( $b );
		return array( $r,  $g,  $b );
	}
    public function __construct($color = 0x000000, $short = false)
    {
        if (is_numeric($color)) {
            
            if ($short) {
                $this->r = (($color >>  8) & 0xf) * 0x11;
                $this->g = (($color >>  4) & 0xf) * 0x11;
                $this->b =  ($color        & 0xf) * 0x11;
                $this->a = (($color >> 12) & 0xf) * 0x11;
            } else {
                $this->r = ($color >> 16) & 0xff;
                $this->g = ($color >>  8) & 0xff;
                $this->b =  $color        & 0xff;
                $this->a = ($color >> 24) & 0xff;
            }
            
        } elseif (is_array($color)) {
            
            list($this->r, $this->g, $this->b) = $color;
            
            if (count($color) > 3)
                $this->a = $color[3];
        }
    }

	/**
	 * Some useful magic getters
	 *
	 * @param  string $property
	 * @throws \Exception
	 * @return mixed
	 */
    public function __get($property)
    {
        $method = 'to' . ucfirst($property);
        if (method_exists($this, $method))
            return $this->$method();
        
        switch ($property) {
            case 'red':   case 'getRed':
                return $this->r;
            case 'green': case 'getGreen':
                return $this->g;
            case 'blue':  case 'getBlue':
                return $this->b;
            case 'alpha': case 'getAlpha':
                return $this->a;
        }
        
        throw new \Exception("Property $property does not exist");
    }
    
    /**
     * Magic method, alias for toHexString
     * 
     * @see  toHexString()
     * @return string
     */
    public function __toString()
    {
        return $this->toHexString();
    }
    
    /**
     * Returns the difference between this color
     * and the provided color
     * using simple pythagoras *without* sqrt
     * 
     * @param  int|Color
     * @return int
     */
    public function getDiff($color)
    {
        if (!$color instanceof Color) {
            $color = new Color($color);
        }
        return pow($this->r - $color->r, 2)
             + pow($this->g - $color->g, 2)
             + pow($this->b - $color->b, 2);
    }
    
    /**
     * Whether or not this color has an alpha value > 0
     * 
     * @see isTransparent for full transparency
     * @return boolean
     */
    public function hasAlpha()
    {
        return (boolean) $this->a;
    }

	/**
	 * Detect Transparency using GD
	 * Returns true if the provided color has zero opacity
	 *
	 * @return bool
	 */
    public function isTransparent()
    {
        return $this->a === 127;
    }
    
    /**
     * Returns an array containing int values for
     * red, green, blue and alpha
     * 
     * @return array
     */
    public function toArray()
    {
        return array($this->r, $this->g, $this->b, $this->a);
    }
    
    /**
     * Returns an array containing int values for
     * red, green and blue
     * 
     * @return array
     */
    public function toRgb()
    {
        return array($this->r, $this->g, $this->b);
    }
    
    /**
     * Returns an array containing int values for
     * red, green and blue and a double for alpha
     * 
     * @return array
     */
    public function toRgba()
    {
        return array($this->r, $this->g, $this->b, 1 - $this->a / 0x100);
    }
    
    /**
     * Returns an int representing the color
     * defined by the red, green and blue values
     * 
     * @return int
     */
    public function toInt()
    {
        return ($this->r << 16) | ($this->g << 8) | $this->b;
    }
    
    /**
     * Render 6-digit hexadecimal string representation
     * like '#abcdef'
     * 
     * @param  string  $prefix  defaults to '#'
     * @return string
     */
    public function toHexString($prefix = '#')
    {
        return $prefix . str_pad(dechex($this->toInt()), 6, '0', STR_PAD_LEFT);
    }
    
    /**
     * Render 3-integer decimal string representation
     * like 'rgb(123,0,20)'
     * 
     * @param  string  $prefix  defaults to 'rgb'
     * @return string
     */
    public function toRgbString($prefix = 'rgb')
    {
        return $prefix  . '('
             . $this->r . ','
             . $this->g . ','
             . $this->b . ')';
    }
    
    /**
     * Render 3-integer decimal string representation
     * like 'rgba(123,0,20,0.5)'
     * 
     * @param  string  $prefix          defaults to 'argb'
     * @param  int     $alphaPrecision  max alpha digits, default 2
     * @return string
     */
    public function toRgbaString($prefix = 'rgba', $alphaPrecision = 2)
    {
        return $prefix  . '('
             . $this->r . ','
             . $this->g . ','
             . $this->b . ','
             . round(1 - $this->a / 0x100, $alphaPrecision) . ')';
    }
}
