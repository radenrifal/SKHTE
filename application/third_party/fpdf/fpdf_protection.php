<?php
/****************************************************************************
* Software: FPDF_Protection                                                 *
* Version:  1.05                                                            *
* Date:     2018-03-19                                                      *
* Author:   Klemen VODOPIVEC                                                *
* License:  FPDF                                                            *
*                                                                           *
* Thanks:  Cpdf (http://www.ros.co.nz/pdf) was my working sample of how to  *
*          implement protection in pdf.                                     *
****************************************************************************/

include_once APPPATH . '/third_party/fpdf/fpdf.php';

if(function_exists('openssl_encrypt'))
{
	function RC4($key, $data)
	{
		return openssl_encrypt($data, 'RC4-40', $key, OPENSSL_RAW_DATA);
	}
}
elseif(function_exists('mcrypt_encrypt'))
{
	function RC4($key, $data)
	{
		return @mcrypt_encrypt(MCRYPT_ARCFOUR, $key, $data, MCRYPT_MODE_STREAM, '');
	}
}
else
{
	function RC4($key, $data)
	{
		static $last_key, $last_state;

		if($key != $last_key)
		{
			$k = str_repeat($key, 256/strlen($key)+1);
			$state = range(0, 255);
			$j = 0;
			for ($i=0; $i<256; $i++){
				$t = $state[$i];
				$j = ($j + $t + ord($k[$i])) % 256;
				$state[$i] = $state[$j];
				$state[$j] = $t;
			}
			$last_key = $key;
			$last_state = $state;
		}
		else
			$state = $last_state;

		$len = strlen($data);
		$a = 0;
		$b = 0;
		$out = '';
		for ($i=0; $i<$len; $i++){
			$a = ($a+1) % 256;
			$t = $state[$a];
			$b = ($b+$t) % 256;
			$state[$a] = $state[$b];
			$state[$b] = $t;
			$k = $state[($state[$a]+$state[$b]) % 256];
			$out .= chr(ord($data[$i]) ^ $k);
		}
		return $out;
	}
}

class FPDF_Protection extends FPDF
{
	protected $encrypted = false;  //whether document is protected
	protected $Uvalue;             //U entry in pdf document
	protected $Ovalue;             //O entry in pdf document
	protected $Pvalue;             //P entry in pdf document
	protected $enc_obj_id;         //encryption object id

	/**
	* Function to set permissions as well as user and owner passwords
	*
	* - permissions is an array with values taken from the following list:
	*   copy, print, modify, annot-forms
	*   If a value is present it means that the permission is granted
	* - If a user password is set, user will be prompted before document is opened
	* - If an owner password is set, document can be opened in privilege mode with no
	*   restriction if that password is entered
	*/
	function SetProtection($permissions=array(), $user_pass='', $owner_pass=null)
	{
		$options = array('print' => 4, 'modify' => 8, 'copy' => 16, 'annot-forms' => 32 );
		$protection = 192;
		foreach($permissions as $permission)
		{
			if (!isset($options[$permission]))
				$this->Error('Incorrect permission: '.$permission);
			$protection += $options[$permission];
		}
		if ($owner_pass === null)
			$owner_pass = uniqid(rand());
		$this->encrypted = true;
		$this->padding = "\x28\xBF\x4E\x5E\x4E\x75\x8A\x41\x64\x00\x4E\x56\xFF\xFA\x01\x08".
						"\x2E\x2E\x00\xB6\xD0\x68\x3E\x80\x2F\x0C\xA9\xFE\x64\x53\x69\x7A";
		$this->_generateencryptionkey($user_pass, $owner_pass, $protection);
	}

	function SetDash($black=false, $white=false)
  {
      if($black and $white)
          $s=sprintf('[%.3f %.3f] 0 d', $black*$this->k, $white*$this->k);
      else
          $s='[] 0 d';
      $this->_out($s);
  }
  
  function RoundedRect($x, $y, $w, $h, $r, $corners = '1234', $style = '')
  {
      $k = $this->k;
      $hp = $this->h;
      if($style=='F')
          $op='f';
      elseif($style=='FD' || $style=='DF')
          $op='B';
      else
          $op='S';
      $MyArc = 4/3 * (sqrt(2) - 1);
      $this->_out(sprintf('%.2F %.2F m',($x+$r)*$k,($hp-$y)*$k ));

      $xc = $x+$w-$r;
      $yc = $y+$r;
      $this->_out(sprintf('%.2F %.2F l', $xc*$k,($hp-$y)*$k ));
      if (strpos($corners, '2')===false)
          $this->_out(sprintf('%.2F %.2F l', ($x+$w)*$k,($hp-$y)*$k ));
      else
          $this->_Arc($xc + $r*$MyArc, $yc - $r, $xc + $r, $yc - $r*$MyArc, $xc + $r, $yc);

      $xc = $x+$w-$r;
      $yc = $y+$h-$r;
      $this->_out(sprintf('%.2F %.2F l',($x+$w)*$k,($hp-$yc)*$k));
      if (strpos($corners, '3')===false)
          $this->_out(sprintf('%.2F %.2F l',($x+$w)*$k,($hp-($y+$h))*$k));
      else
          $this->_Arc($xc + $r, $yc + $r*$MyArc, $xc + $r*$MyArc, $yc + $r, $xc, $yc + $r);

      $xc = $x+$r;
      $yc = $y+$h-$r;
      $this->_out(sprintf('%.2F %.2F l',$xc*$k,($hp-($y+$h))*$k));
      if (strpos($corners, '4')===false)
          $this->_out(sprintf('%.2F %.2F l',($x)*$k,($hp-($y+$h))*$k));
      else
          $this->_Arc($xc - $r*$MyArc, $yc + $r, $xc - $r, $yc + $r*$MyArc, $xc - $r, $yc);

      $xc = $x+$r ;
      $yc = $y+$r;
      $this->_out(sprintf('%.2F %.2F l',($x)*$k,($hp-$yc)*$k ));
      if (strpos($corners, '1')===false)
      {
          $this->_out(sprintf('%.2F %.2F l',($x)*$k,($hp-$y)*$k ));
          $this->_out(sprintf('%.2F %.2F l',($x+$r)*$k,($hp-$y)*$k ));
      }
      else
          $this->_Arc($xc - $r, $yc - $r*$MyArc, $xc - $r*$MyArc, $yc - $r, $xc, $yc - $r);
      $this->_out($op);
  }

/****************************************************************************
*                                                                           *
*                              Private methods                              *
*                                                                           *
****************************************************************************/

	function _putstream($s)
	{
		if ($this->encrypted)
			$s = RC4($this->_objectkey($this->n), $s);
		parent::_putstream($s);
	}

	function _textstring($s)
	{
		if (!$this->_isascii($s))
			$s = $this->_UTF8toUTF16($s);
		if ($this->encrypted)
			$s = RC4($this->_objectkey($this->n), $s);
		return '('.$this->_escape($s).')';
	}

	/**
	* Compute key depending on object number where the encrypted data is stored
	*/
	function _objectkey($n)
	{
		return substr($this->_md5_16($this->encryption_key.pack('VXxx',$n)),0,10);
	}

	function _putresources()
	{
		parent::_putresources();
		if ($this->encrypted) {
			$this->_newobj();
			$this->enc_obj_id = $this->n;
			$this->_put('<<');
			$this->_putencryption();
			$this->_put('>>');
			$this->_put('endobj');
		}
	}

	function _putencryption()
	{
		$this->_put('/Filter /Standard');
		$this->_put('/V 1');
		$this->_put('/R 2');
		$this->_put('/O ('.$this->_escape($this->Ovalue).')');
		$this->_put('/U ('.$this->_escape($this->Uvalue).')');
		$this->_put('/P '.$this->Pvalue);
	}

	function _puttrailer()
	{
		parent::_puttrailer();
		if ($this->encrypted) {
			$this->_put('/Encrypt '.$this->enc_obj_id.' 0 R');
			$this->_put('/ID [()()]');
		}
	}

	/**
	* Get MD5 as binary string
	*/
	function _md5_16($string)
	{
		return md5($string, true);
	}

	/**
	* Compute O value
	*/
	function _Ovalue($user_pass, $owner_pass)
	{
		$tmp = $this->_md5_16($owner_pass);
		$owner_RC4_key = substr($tmp,0,5);
		return RC4($owner_RC4_key, $user_pass);
	}

	/**
	* Compute U value
	*/
	function _Uvalue()
	{
		return RC4($this->encryption_key, $this->padding);
	}

	/**
	* Compute encryption key
	*/
	function _generateencryptionkey($user_pass, $owner_pass, $protection)
	{
		// Pad passwords
		$user_pass = substr($user_pass.$this->padding,0,32);
		$owner_pass = substr($owner_pass.$this->padding,0,32);
		// Compute O value
		$this->Ovalue = $this->_Ovalue($user_pass,$owner_pass);
		// Compute encyption key
		$tmp = $this->_md5_16($user_pass.$this->Ovalue.chr($protection)."\xFF\xFF\xFF");
		$this->encryption_key = substr($tmp,0,5);
		// Compute U value
		$this->Uvalue = $this->_Uvalue();
		// Compute P value
		$this->Pvalue = -(($protection^255)+1);
	}
  
  function ClippingText($x, $y, $txt, $outline=false)
  {
      $op=$outline ? 5 : 7;
      $this->_out(sprintf('q BT %.2F %.2F Td %d Tr (%s) Tj ET',
          $x*$this->k,
          ($this->h-$y)*$this->k,
          $op,
          $this->_escape($txt)));
  }

  function ClippingRect($x, $y, $w, $h, $outline=false)
  {
      $op=$outline ? 'S' : 'n';
      $this->_out(sprintf('q %.2F %.2F %.2F %.2F re W %s',
          $x*$this->k,
          ($this->h-$y)*$this->k,
          $w*$this->k,-$h*$this->k,
          $op));
  }

  function _Arc($x1, $y1, $x2, $y2, $x3, $y3)
  {
      $h = $this->h;
      $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c ', $x1*$this->k, ($h-$y1)*$this->k,
          $x2*$this->k, ($h-$y2)*$this->k, $x3*$this->k, ($h-$y3)*$this->k));
  }

  function ClippingRoundedRect($x, $y, $w, $h, $r, $outline=false)
  {
      $k = $this->k;
      $hp = $this->h;
      $op=$outline ? 'S' : 'n';
      $MyArc = 4/3 * (sqrt(2) - 1);

      $this->_out(sprintf('q %.2F %.2F m',($x+$r)*$k,($hp-$y)*$k ));
      $xc = $x+$w-$r ;
      $yc = $y+$r;
      $this->_out(sprintf('%.2F %.2F l', $xc*$k,($hp-$y)*$k ));

      $this->_Arc($xc + $r*$MyArc, $yc - $r, $xc + $r, $yc - $r*$MyArc, $xc + $r, $yc);
      $xc = $x+$w-$r ;
      $yc = $y+$h-$r;
      $this->_out(sprintf('%.2F %.2F l',($x+$w)*$k,($hp-$yc)*$k));
      $this->_Arc($xc + $r, $yc + $r*$MyArc, $xc + $r*$MyArc, $yc + $r, $xc, $yc + $r);
      $xc = $x+$r ;
      $yc = $y+$h-$r;
      $this->_out(sprintf('%.2F %.2F l',$xc*$k,($hp-($y+$h))*$k));
      $this->_Arc($xc - $r*$MyArc, $yc + $r, $xc - $r, $yc + $r*$MyArc, $xc - $r, $yc);
      $xc = $x+$r ;
      $yc = $y+$r;
      $this->_out(sprintf('%.2F %.2F l',($x)*$k,($hp-$yc)*$k ));
      $this->_Arc($xc - $r, $yc - $r*$MyArc, $xc - $r*$MyArc, $yc - $r, $xc, $yc - $r);
      $this->_out(' W '.$op);
  }

  function ClippingEllipse($x, $y, $rx, $ry, $outline=false)
  {
      $op=$outline ? 'S' : 'n';
      $lx=4/3*(M_SQRT2-1)*$rx;
      $ly=4/3*(M_SQRT2-1)*$ry;
      $k=$this->k;
      $h=$this->h;
      $this->_out(sprintf('q %.2F %.2F m %.2F %.2F %.2F %.2F %.2F %.2F c',
          ($x+$rx)*$k,($h-$y)*$k,
          ($x+$rx)*$k,($h-($y-$ly))*$k,
          ($x+$lx)*$k,($h-($y-$ry))*$k,
          $x*$k,($h-($y-$ry))*$k));
      $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c',
          ($x-$lx)*$k,($h-($y-$ry))*$k,
          ($x-$rx)*$k,($h-($y-$ly))*$k,
          ($x-$rx)*$k,($h-$y)*$k));
      $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c',
          ($x-$rx)*$k,($h-($y+$ly))*$k,
          ($x-$lx)*$k,($h-($y+$ry))*$k,
          $x*$k,($h-($y+$ry))*$k));
      $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c W %s',
          ($x+$lx)*$k,($h-($y+$ry))*$k,
          ($x+$rx)*$k,($h-($y+$ly))*$k,
          ($x+$rx)*$k,($h-$y)*$k,
          $op));
  }

  function ClippingCircle($x, $y, $r, $outline=false)
  {
      $this->ClippingEllipse($x, $y, $r, $r, $outline);
  }

  function ClippingPolygon($points, $outline=false)
  {
      $op=$outline ? 'S' : 'n';
      $h = $this->h;
      $k = $this->k;
      $points_string = '';
      for($i=0; $i<count($points); $i+=2){
          $points_string .= sprintf('%.2F %.2F', $points[$i]*$k, ($h-$points[$i+1])*$k);
          if($i==0)
              $points_string .= ' m ';
          else
              $points_string .= ' l ';
      }
      $this->_out('q '.$points_string . 'h W '.$op);
  }

  function UnsetClipping()
  {
      $this->_out('Q');
  }

  function ClippedCell($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=false, $link='')
  {
      if($border || $fill || $this->y+$h>$this->PageBreakTrigger)
      {
          $this->Cell($w,$h,'',$border,0,'',$fill);
          $this->x-=$w;
      }
      $this->ClippingRect($this->x,$this->y,$w,$h);
      $this->Cell($w,$h,$txt,'',$ln,$align,false,$link);
      $this->UnsetClipping();
  }
}

?>
