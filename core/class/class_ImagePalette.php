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
 * Class ImagePalette
 *
 * Gets the prominent colors in a given image. To get common color matching, all pixels are matched
 * against a white-listed color palette.
 *
 * @package BrianMcdo\ImagePalette
 */
class ImagePalette implements IteratorAggregate
{
    /**
     * File or Url
     * @var string
     */
    protected $file;
    
    /**
     * Loaded Image
     * @var object
     */
    protected $loadedImage;
    
    /**
     * Process every Nth pixel
     * @var int
     */
    protected $precision;

    /**
     * Width of image
     * @var integer
     */
    protected $width;

    /**
     * Height of image
     * @var integer
     */
    protected $height;

    /**
     * Number of colors to return
     * @var integer
     */
    protected $paletteLength;

    /**
     * Colors Whitelist
     * @var array
     */
   /* protected $whiteList = array(
        0x660000, 0x990000, 0xcc0000, 0xcc3333, 0xea4c88, 0x993399,
        0x663399, 0x333399, 0x0066cc, 0x0099cc, 0x66cccc, 0x77cc33,
        0x669900, 0x336600, 0x666600, 0x999900, 0xcccc33, 0xffff00,
        0xffcc33, 0xff9900, 0xff6600, 0xcc6633, 0x996633, 0x663300,
        0x000000, 0x999999, 0xcccccc, 0xffffff, 0xE7D8B1, 0xFDADC7,
        0x424153, 0xABBCDA, 0xF5DD01
    );*/
	protected $whiteList = array(
		0x111111,0xFFFFFF,0x9E9E9E,0xA48057,0xFC85B3,0xFF2727,0xFFA34B,0xFFD534,0x47C595,0x51C4C4,0x2B76E7,0x6D50ED
    );
    protected $whiteList1 = array();
    /**
     * Colors that were found to be prominent
     * Array of Color objects
     * 
     * @var array
     */
    protected $palette;
	
	protected $colorNumber;
    
    /**
     * Library used
     * Supported are GD and Imagick
     * @var string
     */
    protected $lib;

	/**
	 * Constructor
	 * @param string $file
	 * @param int $precision
	 * @param int $paletteLength
	 * @param string $library
	 */
    public function __construct($file, $precision = 10, $paletteLength = 5, $library = 'gd',$whiteList=array())
    {
        $this->file = $file;
        $this->precision = $precision;
        $this->paletteLength = $paletteLength;
        
        // use provided libname or auto-detect
        $this->lib = $this->graphicsLibrary($library);
        
        // create an array with color ints as keys
        $this->whiteList = array_fill_keys($whiteList?$whiteList:$this->whiteList, 0);
        $this->whiteList1 = array_fill_keys($whiteList?$whiteList:$this->whiteList, array());

        $this->process($this->lib);
    }

    /**
     * Select graphics library to use for processing
     *
     * @param string $lib
     * @return mixed
     * @throws \Exception
     */
    protected function graphicsLibrary($lib = 'gd')
    {
        $libraries = [
            'gd' => 'GD',
            'imagick' => 'Imagick',
            //'gmagick' => [false, 'Gmagick']
        ];

        if( ! array_key_exists($lib, $libraries))
        {
            throw new Exception('This extension specified is not supported.');
        }

        if( ! extension_loaded($lib))
        {
            throw new Exception('This extension is not installed');
        }

        return  $libraries[$lib];
    }
    
    /**
     * Select a graphical library and start generating the Image Palette
     * @param string $lib
     * @throws \Exception
     */
    protected function process($lib)
    {
       if(!$this->{'setWorkingImage' . $lib} ()) return false;
        $this->{'setImagesize' . $lib} ();
		
        $this->readPixels();
		$ps=array_keys($this->whiteList);
        // sort whiteList
		arsort($this->whiteList1);
		$total=0;
		foreach($this->whiteList as $key => $v){
			$total+=intval($v);
		}
		//print_r($this->whiteList);exit('ddd');
		$arr=array();
		$arr1=array();
		foreach($this->whiteList1 as $key =>$val){
			if(!($this->whiteList[$key])) continue;
			arsort($val);
			$v=array_keys($val);
			$arr[$v[0]]=round(($this->whiteList[$key]/$total)*100,2);
			$arr1[$v[0]]=array_search($key,$ps);
		}
		
		$arr=array_slice($arr,0,$this->paletteLength,true);
		$arr1=array_slice($arr1,0,$this->paletteLength,true);
		
		$this->palette=$arr;
		$this->colorNumber=$arr1;
		
        // sort whiteList accordingly
       // $this->filter($arr);
		
			/*array_map(
				function($color) {
					return new Color($color);
				},
				array_keys($arr)
			);*/
		
    }
	public function getPalette(){
		return $this->palette;
	}
	public function getColorNumber(){
		return $this->colorNumber;
	}
	
	public function getWhiteList1(){
		return $this->whiteList1;
	}
	public function getWidth(){
		return $this->width;
	}
	public function getHeight(){
		return $this->height;
	}

	/**
	 * Load and set the working image.
	 */
    protected function setWorkingImageGD()
    {
        //$extension = 'jpg';//pathinfo($this->file, PATHINFO_EXTENSION);
		$info=@getimagesize($this->file);
        switch ($info['mime'])
        {
          case 'image/png':
                $this->loadedImage = imagecreatefrompng($this->file);
                break;

           case 'image/jpeg':
                $this->loadedImage = imagecreatefromjpeg($this->file);
                break;

          case 'image/gif':
                $this->loadedImage = imagecreatefromgif($this->file);
                break;
			default:
                throw new Exception("The file type .$extension is not supported.");
        }
		return ture;
    }

	/**
	 * Load and set working image
	 *
	 * @todo needs work
	 * @return mixed
	 */
    protected function setWorkingImageImagick()
    {
        $file = file_get_contents($this->file);
        $temp = tempnam(DZZ_ROOT.'./data/attachment/cache', uniqid("ImagePalette_", true));
        if(!file_put_contents($temp, $file)) return false;
        $this->loadedImage = new Imagick($temp);
		$this->loadedImage ->thumbnailImage(64,64,true);
		@unlink($temp);
		return true;
    }

	/**
	 * Load and set working image
	 *
	 * @todo needs work
	 * @throws \Exception
	 * @return mixed
	 */
    protected function setWorkingImageGmagick()
    {
        throw new Exception("Gmagick not supported");
    }
    
    /**
     * Get and set size of the image using GD.
     */
    protected function setImageSizeGD()
    {
        list($this->width, $this->height) = getimagesize($this->file);
    }
    
    /**
     * Get and set size of image using ImageMagick.
     */
    protected function setImageSizeImagick()
    {
        $d = $this->loadedImage->getImageGeometry();
		
        $this->width  = $d['width'];
        $this->height = $d['height'];
		
    }
    
    /**
     * For each interesting pixel, add its closest color to the loaded colors array
     * 
     * @return mixed
     */
    protected function readPixels()
    {
        // Row
        for ($x = 0; $x < $this->width; $x += $this->precision)
        {
            // Column
            for ($y = 0; $y < $this->height; $y += $this->precision)
            {
                
                $color = $this->getPixelColor($x, $y);
                
                // transparent pixels don't really have a color
                if ($color->isTransparent())
                    continue 1;
                // increment closes whiteList color (key)
                $this->whiteList[ $this->getClosestColor($color) ]++;
				//$this->whiteList1[$color->toInt()]++;
            }
        }
    }
    
    /**
     * Get closest matching color
     * 
     * @param Color $color
     * @return int
     */
    protected function getClosestColor(Color $color)
    {
        $cint=$color->toInt();
		
        $bestDiff = PHP_INT_MAX;
        
        // default to black so hhvm won't cry
        $bestColor = 0x000000;
		$rgbarr=array();
        foreach ($this->whiteList as $wlColor => $hits)
        {
			
            // calculate difference (don't sqrt)
            $diff = $color->getDiff($wlColor);
            
            // see if we got a new best
            if ($diff < $bestDiff)
            {
                $bestDiff = $diff;
                $bestColor = $wlColor;
            }
        }
		
		
		if(!isset( $this->whiteList1[$bestColor][$cint]))  $this->whiteList1[$bestColor][$cint]=1;
        else {
			$this->whiteList1[$bestColor][$cint]++;
		}
        return $bestColor;
    }
    
    /**
     * Returns an array describing the color at x,y
     * At index 0 is the color as a whole int (may include alpha)
     * At index 1 is the color's red value
     * At index 2 is the color's green value
     * At index 3 is the color's blue value
     * 
     * @param  int $x
     * @param  int $y
     * @return Color
     */
    protected function getPixelColor($x, $y)
    {
        return $this->{'getPixelColor' . $this->lib} ($x, $y);
    }
    
    /**
     * Using to retrieve color information about a specified pixel
     * 
     * @see  getPixelColor()
     * @param  int $x
     * @param  int $y
     * @return Color
     */
    protected function getPixelColorGD($x, $y)
    {
        $color = imagecolorat($this->loadedImage, $x, $y);
      
        return new Color (
            $color
            // $rgb['red'],
            // $rgb['green'],
            // $rgb['blue']
        );
    }
    
    /**
     * Using to retrieve color information about a specified pixel
     * 
     * @see  getPixelColor()
     * @param  int $x
     * @param  int $y
     * @return Color
     */
    protected function getPixelColorImagick($x, $y)
    {
        $rgb = $this->loadedImage->getImagePixelColor($x, $y)->getColor();
        foreach($rgb as $k => $v){
			if($v<0) $v=0;
			$rgb[$k]=$v;
		}
		
        return new Color([
            $rgb['r'],
            $rgb['g'],
            $rgb['b'],
        ]);
    }

    protected function getPixelColorGmagick($x, $y)
    {
        throw new Exception("Gmagick not supported: ($x, $y)");
    }
    
    /**
     * Returns an array of Color objects
     * 
     * @param  int $paletteLength
     * @return array
     */
    public function getColors($paletteLength = null)
    {
        // allow custom length calls
        if ( ! is_numeric($paletteLength))
        {
            $paletteLength = $this->paletteLength;
        }
        
        // take the best hits
        return array_slice($this->palette, 0, $paletteLength, true);
    }
    
    /**
     * Returns a json encoded version of the palette
     * 
     * @return string
     */
    public function __toString()
    {
        // Color PHP 5.3 compatible -> not JsonSerializable :(
        return json_encode(array_map(
            function($color) {
                return (string) $color;
            },
            $this->getColors()
        ));
    }

	/**
	 * Convenient getter access as properties
	 *
	 * @param $name
	 * @throws \Exception
	 * @return  mixed
	 */
    public function __get($name)
    {
        $method = 'get' . ucfirst($name);

        if (method_exists($this, $method))
        {
            return $this->$method();
        }

        throw new Exception("Method $method does not exist");
    }
    
    /**
     * Returns the palette for implementation of the IteratorAggregate interface
     * Used in foreach loops
     * 
     * @see  getColors()
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->getColors());
    }
}
