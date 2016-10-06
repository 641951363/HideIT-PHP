<?php
/**
 * @author 641951363
 * @mail 641951363@mail.bg
 */

 
namespace NVCH\HideIT;

if (!extension_loaded('gd'))
{
    throw new Exception('GD extension seems not to be installed !');
}

class HideIT
{
	
	private $__ImagePath = '';
	private $__ImageX = 0;
	private $__ImageY = 0;
	private $__GDImageResource;
	public $MaxSpace = 0;
	
	/***
	* Read last bits from r,g,b and return binary string
	*
	* @param int $rgb
	* @return string
	*/
	private static function Rgb2Binary($rgb)
	{
		$b1 = ($rgb & (1 << 16)) != 0?'1':'0';
		$b2 = ($rgb & (1 << 8)) != 0?'1':'0';
		$b3 = ($rgb & (1 << 0)) != 0?'1':'0';
		
		return $b1.$b2.$b3;
	}
	
	/***
	* Assings 3 bits to pixel
	*
	* @param int $pixel
	* @param string $bits
	* @return int
	*/
	private static function Binary2Rgb($pixel,$bits)
	{
		$pixel = ($pixel & ~(1 << 16)) | (intval($bits[0]) << 16);
		$pixel = ($pixel & ~(1 << 8)) | (intval($bits[1]) << 8);
		$pixel = ($pixel & ~(1 << 0)) | (intval($bits[2]) << 0);
		
		return $pixel;
	}
	
	/***
	* Calc how many bits can be hidden in loaded resource
	*
	* @return void
	*/
	private function MaxSpace()
	{
		$MaxSpace = $this->__ImageX*$this->__ImageY;
		$MaxSpace = $MaxSpace * 3;
		$MaxSpace = $MaxSpace - 30;
		
		$this->MaxSpace = $MaxSpace;
	}
	
	/***
	* Hide string in pixels
	*
	* @param string $msg
	* @return void
	*/
	private function WriteMsg($msg)
	{
		$pixel = 0;
		$head = decbin(strlen($msg));
		$head = str_pad($head, 30, '0', STR_PAD_LEFT); // head need to be always 30 bits
		$msg = $this->Ascii2Bin($msg);

		$bin = $head . $msg;
		$bl = strlen($bin);
		
		if ($bl % 3 != 0)
		{
			$bin .= str_repeat('0',3-($bl % 3));
		}
		
		$i = 0;
		for ($x=0; $x < $this->__ImageX; $x++)
		{
			for ($y=0; $y < $this->__ImageY; $y++)
			{
				if (!isset($bin[$i]))break 2;
				
				$pixel = imagecolorat($this->__GDImageResource,$x,$y);
				$pixel = $this->Binary2Rgb($pixel,$bin[$i++].$bin[$i++].$bin[$i++]);
				imagesetpixel($this->__GDImageResource,$x,$y,$pixel);
			}
		}
	}
	
	/***
	* Read hidden string from image
	*
	* @param void
	* @return string | false
	*/
	private function ReadMsg()
	{
		$msg = '';
		$msgl = 11;
		$msgb = 31;
		
		$i = 0;
		for ($x=0; $x < $this->__ImageX; $x++)
		{
			for ($y=0; $y < $this->__ImageY; $y++)
			{
				$i++;
				if ($i == 11)
				{
					$msgb = bindec($msg)*8;
					$msgl = ceil($msgb/3);
					
					if ($msgb < 8)return false;
					if ($msgl > $this->__ImageX*$this->__ImageY)return false;
					
					$msgl += 11;
					$msg = '';
				}
				if ($i == $msgl)break 2;
				$msg .= $this->Rgb2Binary(imagecolorat($this->__GDImageResource,$x,$y));
			}
		}
		$msg = substr($msg,0,$msgb);
		
		try
		{
			$msg = $this->Bin2Ascii($msg);
		}catch (Exception $e)
		{
			
			return false;
		}
		
		return $msg;
	}
	
	function __construct($path = NULL)
	{
		if ($path !== NULL)$this->LoadImage($path);
	}
	
	function __destruct()
	{
		imagedestroy($this->__GDImageResource);
	}
	
	/***
	* Convert from binary to ascii
	*
	* @param string $binary
	* @return string
	* @throws \Exception
	*/
	public function Bin2Ascii($binary)
	{
		$binary = preg_replace( '/[^0-1]/', '', $binary);
		if (strlen($binary) % 8 != 0)
		{
			throw new \Exception('Invalid binary input !');
		}
		
		$binary = str_split($binary, 8);
		$r = '';
		
		foreach ($binary as $c)
		{
			$r .= pack('H*', base_convert($c, 2, 16));
		}
		
		return $r;
	}
	
	/***
	* Convert from ascii to binary
	*
	* @param string $ascii
	* @return string
	*/
	public function Ascii2Bin($ascii)
	{
		$ascii = unpack('H*', $ascii);
		$ascii_ch = str_split($ascii[1], 2);
		$asciiBits = '';
		$tmp = '';
		
		foreach ($ascii_ch as $c)
		{
			$tmp = base_convert($c, 16, 2);
			$asciiBits .= str_pad($tmp, 8, '0', STR_PAD_LEFT);
		}
		
		return $asciiBits;
	}
	
	/***
	* Set image path
	*
	* @param string $path
	* @return void
	* @throws \Exception
	*/
	public function LoadImage($path)
	{
		if (!file_exists($path))
		{
			throw new \Exception('seems path (' . $path . ') not be valid !');
		}
		
		$ImgSize = getimagesize($path);
		
		if ($ImgSize === false)
			throw new \Exception('Image format not supported !');
		
		if (($ImgSize[0]+$ImgSize[1]) == 0)
			throw new \Exception('Image format not supported !');
		
		if (($ImgSize[0]*$ImgSize[1]) < 50)
			throw new \Exception('Image is too small !');
		
		$this->__ImagePath = $path;
		$this->__ImageX = $ImgSize[0];
		$this->__ImageY = $ImgSize[1];
		$this->__GDImageResource = imagecreatefromstring(file_get_contents($path));
		$this->MaxSpace();
	}
	
	/***
	* Hide $msg string in given image  resource
	*
	* @param string $msg
	* @return void
	* @throws \Exception
	*/
	public function HideIT($msg)
	{
		if (empty($this->__ImagePath))
			throw new \Exception('Image resource not loaded !');
		
		if (empty($msg))
			throw new \Exception('$msg need to be at lest 1 char !');
		
		if (strlen($msg)*8 > $this->MaxSpace)
			throw new \Exception('$msg it too long for given resource !');
		
		$this->WriteMsg($msg);
	}
	
	/***
	* Retrive $msg string in given image resource
	*
	* @return string
	* @throws \Exception
	*/
	public function ShowIT()
	{
		if (empty($this->__ImagePath))
			throw new \Exception('Image resource not loaded !');
		
		$msg = $this->ReadMsg();
		
		if ($msg === false)
			throw new \Exception('Seems like there is no message in resource !');
		
		return $msg;
	}
	
	/***
	* Save resource in given path or rewrite original image
	*
	* @param string $format jpeg | png | gif
	* @param string $path 
	* @return void
	* @throws \Exception
	*/
	public function SaveIT($format = 'jpeg',$path = NULL)
	{
		if ($path === NULL)
			$path = $this->__ImagePath;
		
		if (!is_writable(dirname($path)))
			throw new \Exception('Seems like there is such directory or php has no permissions to write in directory !');
		
		if (empty(pathinfo($path)['filename']))
			throw new \Exception('Invalid filename !');
		
		switch ($format)
		{
			case 'jpeg': imagejpeg($this->__GDImageResource, $path, 100); break;
			case 'png': imagepng ($this->__GDImageResource, $path, 9); break;
			case 'gif': imagegif($this->__GDImageResource, $path); break;
			default: throw new \Exception('Fromat is not supported !');
		}
	}
}
?>