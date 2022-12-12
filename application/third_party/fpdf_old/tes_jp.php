<?php
define('FPDF_FONTPATH','font/');
require('fpdf.php');

$SJIS_widths=array(' '=>278,'!'=>299,'"'=>353,'#'=>614,'$'=>614,'%'=>721,'&'=>735,'\''=>216,
	'('=>323,')'=>323,'*'=>449,'+'=>529,','=>219,'-'=>306,'.'=>219,'/'=>453,'0'=>614,'1'=>614,
	'2'=>614,'3'=>614,'4'=>614,'5'=>614,'6'=>614,'7'=>614,'8'=>614,'9'=>614,':'=>219,';'=>219,
	'<'=>529,'='=>529,'>'=>529,'?'=>486,'@'=>744,'A'=>646,'B'=>604,'C'=>617,'D'=>681,'E'=>567,
	'F'=>537,'G'=>647,'H'=>738,'I'=>320,'J'=>433,'K'=>637,'L'=>566,'M'=>904,'N'=>710,'O'=>716,
	'P'=>605,'Q'=>716,'R'=>623,'S'=>517,'T'=>601,'U'=>690,'V'=>668,'W'=>990,'X'=>681,'Y'=>634,
	'Z'=>578,'['=>316,'\\'=>614,']'=>316,'^'=>529,'_'=>500,'`'=>387,'a'=>509,'b'=>566,'c'=>478,
	'd'=>565,'e'=>503,'f'=>337,'g'=>549,'h'=>580,'i'=>275,'j'=>266,'k'=>544,'l'=>276,'m'=>854,
	'n'=>579,'o'=>550,'p'=>578,'q'=>566,'r'=>410,'s'=>444,'t'=>340,'u'=>575,'v'=>512,'w'=>760,
	'x'=>503,'y'=>529,'z'=>453,'{'=>326,'|'=>380,'}'=>326,'~'=>387);

  
class PDF extends FPDF {
    var $tablewidths;
    var $tablealign;
    var $footerset;
    var $headerset;
    var $dataHeader;
    var $dataItems;
    var $drawTableinProgress = false;
    
    function PDF($orientation='P',$unit='mm',$format='A4')
    {
        //Call parent constructor
        $this->FPDF($orientation,$unit,$format);
    }

    function AddCIDFont($family,$style,$name,$cw,$CMap,$registry)
    {
    	$fontkey=strtolower($family).strtoupper($style);
    	if(isset($this->fonts[$fontkey]))
    		$this->Error("CID font already added: $family $style");
    	$i=count($this->fonts)+1;
    	$this->fonts[$fontkey]=array('i'=>$i,'type'=>'Type0','name'=>$name,'up'=>-120,'ut'=>40,'cw'=>$cw,'CMap'=>$CMap,'registry'=>$registry);
    }

    function AddCIDFonts($family,$name,$cw,$CMap,$registry)
    {
    	$this->AddCIDFont($family,'',$name,$cw,$CMap,$registry);
    	$this->AddCIDFont($family,'B',$name.',Bold',$cw,$CMap,$registry);
    	$this->AddCIDFont($family,'I',$name.',Italic',$cw,$CMap,$registry);
    	$this->AddCIDFont($family,'BI',$name.',BoldItalic',$cw,$CMap,$registry);
    }

    function AddSJISFont($family='SJIS')
    {
    	//Add SJIS font with proportional Latin
    	$name='KozMinPro-Regular-Acro';
    	$cw=$GLOBALS['SJIS_widths'];
    	$CMap='90msp-RKSJ-H';
    	$registry=array('ordering'=>'Japan1','supplement'=>2);
    	$this->AddCIDFonts($family,$name,$cw,$CMap,$registry);
    }

    function AddSJIShwFont($family='SJIS-hw')
    {
    	//Add SJIS font with half-width Latin
    	$name='KozMinPro-Regular-Acro';
    	for($i=32;$i<=126;$i++)
    		$cw[chr($i)]=500;
    	$CMap='90ms-RKSJ-H';
    	$registry=array('ordering'=>'Japan1','supplement'=>2);
    	$this->AddCIDFonts($family,$name,$cw,$CMap,$registry);
    }

    function GetStringWidth($s)
    {
    	if($this->CurrentFont['type']=='Type0')
    		return $this->GetSJISStringWidth($s);
    	else
    		return parent::GetStringWidth($s);
    }

    function GetSJISStringWidth($s)
    {
    	//SJIS version of GetStringWidth()
    	$l=0;
    	$cw=&$this->CurrentFont['cw'];
    	$nb=strlen($s);
    	$i=0;
    	while($i<$nb)
    	{
    		$o=ord($s{$i});
    		if($o<128)
    		{
    			//ASCII
    			$l+=$cw[$s{$i}];
    			$i++;
    		}
    		elseif($o>=161 and $o<=223)
    		{
    			//Half-width katakana
    			$l+=500;
    			$i++;
    		}
    		else
    		{
    			//Full-width character
    			$l+=1000;
    			$i+=2;
    		}
    	}
    	return $l*$this->FontSize/1000;
    }

    function MultiCell($w,$h,$txt,$border=0,$align='L',$fill=0)
    {
    	if($this->CurrentFont['type']=='Type0')
    		$this->SJISMultiCell($w,$h,$txt,$border,$align,$fill);
    	else
    		parent::MultiCell($w,$h,$txt,$border,$align,$fill);
    }

    function SJISMultiCell($w,$h,$txt,$border=0,$align='L',$fill=0)
    {
    	//Output text with automatic or explicit line breaks
    	$cw=&$this->CurrentFont['cw'];
    	if($w==0)
    		$w=$this->w-$this->rMargin-$this->x;
    	$wmax=($w-2*$this->cMargin)*1000/$this->FontSize;
    	$s=str_replace("\r",'',$txt);
    	$nb=strlen($s);
    	if($nb>0 and $s{$nb-1}=="\n")
    		$nb--;
    	$b=0;
    	if($border)
    	{
    		if($border==1)
    		{
    			$border='LTRB';
    			$b='LRT';
    			$b2='LR';
    		}
    		else
    		{
    			$b2='';
    			if(is_int(strpos($border,'L')))
    				$b2.='L';
    			if(is_int(strpos($border,'R')))
    				$b2.='R';
    			$b=is_int(strpos($border,'T')) ? $b2.'T' : $b2;
    		}
    	}
    	$sep=-1;
    	$i=0;
    	$j=0;
    	$l=0;
    	$nl=1;
    	while($i<$nb)
    	{
    		//Get next character
    		$c=$s{$i};
    		$o=ord($c);
    		if($o==10)
    		{
    			//Explicit line break
    			$this->Cell($w,$h,substr($s,$j,$i-$j),$b,2,$align,$fill);
    			$i++;
    			$sep=-1;
    			$j=$i;
    			$l=0;
    			$nl++;
    			if($border and $nl==2)
    				$b=$b2;
    			continue;
    		}
    		if($o<128)
    		{
    			//ASCII
    			$l+=$cw[$c];
    			$n=1;
    			if($o==32)
    				$sep=$i;
    		}
    		elseif($o>=161 and $o<=223)
    		{
    			//Half-width katakana
    			$l+=500;
    			$n=1;
    			$sep=$i;
    		}
    		else
    		{
    			//Full-width character
    			$l+=1000;
    			$n=2;
    			$sep=$i;
    		}
    		if($l>$wmax)
    		{
    			//Automatic line break
    			if($sep==-1 or $i==$j)
    			{
    				if($i==$j)
    					$i+=$n;
    				$this->Cell($w,$h,substr($s,$j,$i-$j),$b,2,$align,$fill);
    			}
    			else
    			{
    				$this->Cell($w,$h,substr($s,$j,$sep-$j),$b,2,$align,$fill);
    				$i=($s[$sep]==' ') ? $sep+1 : $sep;
    			}
    			$sep=-1;
    			$j=$i;
    			$l=0;
    			$nl++;
    			if($border and $nl==2)
    				$b=$b2;
    		}
    		else
    		{
    			$i+=$n;
    			if($o>=128)
    				$sep=$i;
    		}
    	}
    	//Last chunk
    	if($border and is_int(strpos($border,'B')))
    		$b.='B';
    	$this->Cell($w,$h,substr($s,$j,$i-$j),$b,2,$align,$fill);
    	$this->x=$this->lMargin;
    }
    
    function Write($h,$txt,$link='')
    {
    	if($this->CurrentFont['type']=='Type0')
    		$this->SJISWrite($h,$txt,$link);
    	else
    		parent::Write($h,$txt,$link);
    }

    function SJISWrite($h,$txt,$link)
    {
    	//SJIS version of Write()
    	$cw=&$this->CurrentFont['cw'];
    	$w=$this->w-$this->rMargin-$this->x;
    	$wmax=($w-2*$this->cMargin)*1000/$this->FontSize;
    	$s=str_replace("\r",'',$txt);
    	$nb=strlen($s);
    	$sep=-1;
    	$i=0;
    	$j=0;
    	$l=0;
    	$nl=1;
    	while($i<$nb)
    	{
    		//Get next character
    		$c=$s{$i};
    		$o=ord($c);
    		if($o==10)
    		{
    			//Explicit line break
    			$this->Cell($w,$h,substr($s,$j,$i-$j),0,2,'',0,$link);
    			$i++;
    			$sep=-1;
    			$j=$i;
    			$l=0;
    			if($nl==1)
    			{
    				//Go to left margin
    				$this->x=$this->lMargin;
    				$w=$this->w-$this->rMargin-$this->x;
    				$wmax=($w-2*$this->cMargin)*1000/$this->FontSize;
    			}
    			$nl++;
    			continue;
    		}
    		if($o<128)
    		{
    			//ASCII
    			$l+=$cw[$c];
    			$n=1;
    			if($o==32)
    				$sep=$i;
    		}
    		elseif($o>=161 and $o<=223)
    		{
    			//Half-width katakana
    			$l+=500;
    			$n=1;
    			$sep=$i;
    		}
    		else
    		{
    			//Full-width character
    			$l+=1000;
    			$n=2;
    			$sep=$i;
    		}
    		if($l>$wmax)
    		{
    			//Automatic line break
    			if($sep==-1 or $i==$j)
    			{
    				if($this->x>$this->lMargin)
    				{
    					//Move to next line
    					$this->x=$this->lMargin;
    					$this->y+=$h;
    					$w=$this->w-$this->rMargin-$this->x;
    					$wmax=($w-2*$this->cMargin)*1000/$this->FontSize;
    					$i+=$n;
    					$nl++;
    					continue;
    				}
    				if($i==$j)
    					$i+=$n;
    				$this->Cell($w,$h,substr($s,$j,$i-$j),0,2,'',0,$link);
    			}
    			else
    			{
    				$this->Cell($w,$h,substr($s,$j,$sep-$j),0,2,'',0,$link);
    				$i=($s[$sep]==' ') ? $sep+1 : $sep;
    			}
    			$sep=-1;
    			$j=$i;
    			$l=0;
    			if($nl==1)
    			{
    				$this->x=$this->lMargin;
    				$w=$this->w-$this->rMargin-$this->x;
    				$wmax=($w-2*$this->cMargin)*1000/$this->FontSize;
    			}
    			$nl++;
    		}
    		else
    		{
    			$i+=$n;
    			if($o>=128)
    				$sep=$i;
    		}
    	}
    	//Last chunk
    	if($i!=$j)
    		$this->Cell($l/1000*$this->FontSize,$h,substr($s,$j,$i-$j),0,0,'',0,$link);
    }

    function _putfonts()
    {
    	$nf=$this->n;
    	foreach($this->diffs as $diff)
    	{
    		//Encodings
    		$this->_newobj();
    		$this->_out('<</Type /Encoding /BaseEncoding /WinAnsiEncoding /Differences ['.$diff.']>>');
    		$this->_out('endobj');
    	}
    	$mqr=get_magic_quotes_runtime();
    	set_magic_quotes_runtime(0);
    	foreach($this->FontFiles as $file=>$info)
    	{
    		//Font file embedding
    		$this->_newobj();
    		$this->FontFiles[$file]['n']=$this->n;
    		if(defined('FPDF_FONTPATH'))
    			$file=FPDF_FONTPATH.$file;
    		$size=filesize($file);
    		if(!$size)
    			$this->Error('Font file not found');
    		$this->_out('<</Length '.$size);
    		if(substr($file,-2)=='.z')
    			$this->_out('/Filter /FlateDecode');
    		$this->_out('/Length1 '.$info['length1']);
    		if(isset($info['length2']))
    			$this->_out('/Length2 '.$info['length2'].' /Length3 0');
    		$this->_out('>>');
    		$f=fopen($file,'rb');
    		$this->_putstream(fread($f,$size));
    		fclose($f);
    		$this->_out('endobj');
    	}
    	set_magic_quotes_runtime($mqr);
    	foreach($this->fonts as $k=>$font)
    	{
    		//Font objects
    		$this->_newobj();
    		$this->fonts[$k]['n']=$this->n;
    		$this->_out('<</Type /Font');
    		if($font['type']=='Type0')
    			$this->_putType0($font);
    		else
    		{
    			$name=$font['name'];
    			$this->_out('/BaseFont /'.$name);
    			if($font['type']=='core')
    			{
    				//Standard font
    				$this->_out('/Subtype /Type1');
    				if($name!='Symbol' and $name!='ZapfDingbats')
    					$this->_out('/Encoding /WinAnsiEncoding');
    			}
    			else
    			{
    				//Additional font
    				$this->_out('/Subtype /'.$font['type']);
    				$this->_out('/FirstChar 32');
    				$this->_out('/LastChar 255');
    				$this->_out('/Widths '.($this->n+1).' 0 R');
    				$this->_out('/FontDescriptor '.($this->n+2).' 0 R');
    				if($font['enc'])
    				{
    					if(isset($font['diff']))
    						$this->_out('/Encoding '.($nf+$font['diff']).' 0 R');
    					else
    						$this->_out('/Encoding /WinAnsiEncoding');
    				}
    			}
    			$this->_out('>>');
    			$this->_out('endobj');
    			if($font['type']!='core')
    			{
    				//Widths
    				$this->_newobj();
    				$cw=&$font['cw'];
    				$s='[';
    				for($i=32;$i<=255;$i++)
    					$s.=$cw[chr($i)].' ';
    				$this->_out($s.']');
    				$this->_out('endobj');
    				//Descriptor
    				$this->_newobj();
    				$s='<</Type /FontDescriptor /FontName /'.$name;
    				foreach($font['desc'] as $k=>$v)
    					$s.=' /'.$k.' '.$v;
    				$file=$font['file'];
    				if($file)
    					$s.=' /FontFile'.($font['type']=='Type1' ? '' : '2').' '.$this->FontFiles[$file]['n'].' 0 R';
    				$this->_out($s.'>>');
    				$this->_out('endobj');
    			}
    		}
    	}
    }

    function _putType0($font)
    {
    	//Type0
    	$this->_out('/Subtype /Type0');
    	$this->_out('/BaseFont /'.$font['name'].'-'.$font['CMap']);
    	$this->_out('/Encoding /'.$font['CMap']);
    	$this->_out('/DescendantFonts ['.($this->n+1).' 0 R]');
    	$this->_out('>>');
    	$this->_out('endobj');
    	//CIDFont
    	$this->_newobj();
    	$this->_out('<</Type /Font');
    	$this->_out('/Subtype /CIDFontType0');
    	$this->_out('/BaseFont /'.$font['name']);
    	$this->_out('/CIDSystemInfo <</Registry (Adobe) /Ordering ('.$font['registry']['ordering'].') /Supplement '.$font['registry']['supplement'].'>>');
    	$this->_out('/FontDescriptor '.($this->n+1).' 0 R');
    	$W='/W [1 [';
    	foreach($font['cw'] as $w)
    		$W.=$w.' ';
    	$this->_out($W.'] 231 325 500 631 [500] 326 389 500]');
    	$this->_out('>>');
    	$this->_out('endobj');
    	//Font descriptor
    	$this->_newobj();
    	$this->_out('<</Type /FontDescriptor');
    	$this->_out('/FontName /'.$font['name']);
    	$this->_out('/Flags 6');
    	$this->_out('/FontBBox [0 -200 1000 900]');
    	$this->_out('/ItalicAngle 0');
    	$this->_out('/Ascent 800');
    	$this->_out('/Descent -200');
    	$this->_out('/CapHeight 800');
    	$this->_out('/StemV 60');
    	$this->_out('>>');
    	$this->_out('endobj');
    }

    function _beginpage($orientation) {
        $this->page++;
        if(!isset($this->pages[$this->page])) // solved the problem of overwriting a page, if it already exists
            $this->pages[$this->page]='';
        $this->state=2;
        $this->x=$this->lMargin;
        $this->y=$this->tMargin;
        $this->lasth=0;
        $this->FontFamily='';
        //Page orientation
        if(!$orientation)
            $orientation=$this->DefOrientation;
        else
        {
            $orientation=strtoupper($orientation{0});
            if($orientation!=$this->DefOrientation)
                $this->OrientationChanges[$this->page]=true;
        }
        if($orientation!=$this->CurOrientation)
        {
            //Change orientation
            if($orientation=='P')
            {
                $this->wPt=$this->fwPt;
                $this->hPt=$this->fhPt;
                $this->w=$this->fw;
                $this->h=$this->fh;
            }
            else
            {
                $this->wPt=$this->fhPt;
                $this->hPt=$this->fwPt;
                $this->w=$this->fh;
                $this->h=$this->fw;
            }
            $this->PageBreakTrigger=$this->h-$this->bMargin;
            $this->CurOrientation=$orientation;
        }
    }
    
    function Footer() {
        // Check if Footer for this page already exists (do the same for Header())
        if(!isset($this->footerset[$this->page])) {
          $this->SetY(-25);          
          $this->SetFont('Arial','B',14);
          $this->Cell(0,5,'PT. JAC INDONESIA',0,2,'C');

          $this->SetFont('Arial','',9);
          $this->Cell(0,4,"Spinindo Building 1st Floor Jl.Wahid Hasyim No. 76 Jakarta 10340",0,2,'C');
          $this->Cell(0,4,"Phone : +62-21 315-9504, 315-9506 ; Fax : +62-21 315-9520, 315-06608",0,2,'C');
          $this->Cell(0,4,"e-Mail : recruitment@jacindonesia.com, bussiness-center@jacindonesia.com",0,2,'C');
          $this->Cell(0,4,"http://www.jacindonesia.com",0,0,'C');        
          if ($this->drawTableinProgress)
          {
            $this->Line($this->lMargin,$this->h - $this->bMargin, $this->w - $this->rMargin, $this->h - $this->bMargin);
          }
          //$this->SetFont('Arial','',10);
          $this->SetFont('SJIS','',10);

          // set footerset
          $this->footerset[$this->page] = 1;
        }
    }

    function Header()
    {
    	//To be implemented in your own inherited class
      if(!isset($this->headerset[$this->page])) 
      {
        $this->SetXY(174,12);
        $this->SetFont('SJIS','',9);
        //Page number
        $this->Cell(0,0,'Page '.$this->PageNo().' of {nb}',0,0,'L');

        if ($this->page==1)
        {
          $this->Image("mainmenu_data/confi.png", 153, 34, 0, 0, "png");
          $this->Image("mainmenu_data/jac.png",19, 8, 13, 17,"png");
          $this->Image("mainmenu_data/headerjac.jpg",63, 7, 0, 0,"jpg");
        }
        
        $this->SetXY($this->lMargin,$this->tMargin);
        
        
        if ($this->drawTableinProgress)
        {
          $this->SetFillColor(233,233,233);
          //$this->SetXY($this->lMargin,$this->tMargin);
          //$this->SetFont('Arial','B',10);
          $this->SetFont('SJIS','B',10);

          if ($this->dataHeader != null)
          {
            foreach($this->dataHeader as $col => $txt)
              $this->Cell($this->tablewidths[$col],6,$txt,1,0,'C',1);
            
            //$this->SetFont('Arial','',10);
            $this->SetFont('SJIS','',10);
            $this->SetXY($this->lMargin,$this->tMargin + 8);
          }
          else
          {
            $this->Line($this->lMargin,$this->tMargin, $this->w - $this->rMargin, $this->tMargin);
            $this->SetXY($this->lMargin,$this->tMargin + 2);
          }
        }

        // set headerset
        $this->headerset[$this->page] = 1;
      }
      else
      {
        if ($this->drawTableinProgress)
        {
          $this->SetXY($this->lMargin,$this->tMargin + 8);
        }
      }
    }

    function printPersonalDetail($strDataID, $strDate, $arrData, $lineheight=5) 
    {
        
        // some things to set and 'remember'
        $l = $this->lMargin;
        $startheight = $h = 30;
        $startpage = $currpage = $this->page;
    
        // calculate the whole width
        $fullwidth = $this->w - $this->lMargin - $this->rMargin;
        
        $this->SetFont('SJIS','',8);
        $this->SetXY(150, 32);
        $this->Cell(40,0,$strDataID,0,0,'C');
        $this->SetXY(150, 42);
        $this->Cell(40,0,$strDate,0,0,'C');

        $startY = 50;
        $this->SetFont('SJIS','B',10);
        $this->SetXY($l, $startY);
        $this->Cell(0,0,"PERSONAL DETAIL",0,1,'L');

        $startY += 8;

        $c1 = 50;
        $l2 = 109;
        $c2 = 144;
        $oddColumn = false;
        $maxY = 0;
        foreach ($arrData as $key => $value)
        {
          $oddColumn = !$oddColumn;
          if ($key == "") continue;
          if ($oddColumn)
          {
            //$this->SetFont('Arial','B',10);
            $this->SetFont('SJIS','B',10);

            $this->SetXY($l, $startY);
            $this->Cell(0,$lineheight,$key,0,0,'L');
            
            $this->SetXY($c1, $startY);
            $this->Cell(6,$lineheight,":",0,0,'L');
            
            $this->SetFont('SJIS','',10);
            //$this->SetFont('Arial','',10);
            $this->SetXY($c1 + 4, $startY);
            $this->MultiCell(55, $lineheight,$value, 0,'L',0);
            $maxY = $this->getY();
          }
          else
          {
            $this->SetFont('SJIS','B',10);
            //$this->SetFont('Arial','B',10);
            $this->SetXY($l2, $startY);
            $this->Cell(0,$lineheight,$key,0,0,'L');
            $this->SetXY($c2, $startY);
            $this->Cell(6,$lineheight,":",0,0,'L');
            
            $this->SetFont('SJIS','',10);
            //$this->SetFont('Arial','',10);
            $this->SetXY($c2 + 4, $startY);
            $this->MultiCell(59, $lineheight,$value, 0,'L',0);
            if ($maxY < $this->getY()) $startY = $this->getY();
            else $startY = $maxY;
            //$startY += $lineheight;
          }
        }
    }
    
    
    function printTable($caption, $arrHeader, $arrData, $lineheight=4.5) 
    {
        $l = $this->lMargin;
        $startheight = $h = $this->getY();
        $startpage = $currpage = $this->page;
        
        // calculate the whole width
        $fullwidth = 0;
        foreach($this->tablewidths AS $width) 
        {
          $fullwidth += $width;
        }
    
        $this->SetFont('SJIS','B',12);
        //$this->SetFont('Arial','B',12);
        $this->SetXY($l, $startheight);
        $this->Cell(0,6,$caption,0,1,'L');
        
        $startheight = $this->getY();

        $this->SetFillColor(233,233,233);
        $this->SetFont('SJIS','B',10);
        //$this->SetFont('Arial','B',10);
        if ($arrHeader != null)
        {
          foreach($arrHeader AS $col => $txt)
            $this->Cell($this->tablewidths[$col],6,$txt,1,0,'C',1);
          
          $this->Ln();
        }
        $this->SetFont('SJIS','',10);
        //$this->SetFont('Arial','',10);
        $this->dataHeader = $arrHeader;
        
        $h = $this->GetY();
        $this->drawTableinProgress = true;
        $maxpage = 0;
        foreach($arrData AS $row => $data)
        {
            $this->page = $currpage;
            // write the horizontal borders
            $this->Line($l,$h,$fullwidth+$l,$h);
            $h+=1;
            // write the content and remember the height of the highest col
            foreach($data AS $col => $txt) 
            {
              $this->page = $currpage;
              
              $this->SetXY($l,$h);
              $this->MultiCell($this->tablewidths[$col],$lineheight,$txt,0,$this->tablealign[$col],0);
              $l += $this->tablewidths[$col];
              if (isset($tmpheight[$row.'-'.$this->page]))
              {
                if($tmpheight[$row.'-'.$this->page] < $this->GetY()) 
                  $tmpheight[$row.'-'.$this->page] = $this->GetY();
              }
              else
              {
                $tmpheight[$row.'-'.$this->page] = $this->GetY();
              }
              if($this->page > $maxpage)
                $maxpage = $this->page;
            }
    
            // get the height we were in the last used page
            $h = $tmpheight[$row.'-'.$maxpage]+1;
            // set the "pointer" to the left margin
            $l = $this->lMargin;
            // set the $currpage to the last page
            $currpage = $maxpage;
        }
        // draw the borders
        // we start adding a horizontal line on the last page
        $this->page = $maxpage;
        $this->Line($l,$h,$fullwidth+$l,$h);
        // now we start at the top of the document and walk down
        for($i = $startpage; $i <= $maxpage; $i++) {
            $this->page = $i;
            $l = $this->lMargin;
            $t  = ($i == $startpage) ? $startheight : $this->tMargin;
            $lh = ($i == $maxpage)   ? $h : $this->h-$this->bMargin;
            $this->Line($l,$t,$l,$lh);
            foreach($this->tablewidths AS $width) 
            {
                $l += $width;
                $this->Line($l,$t,$l,$lh);
            }
        }
        // set it to the last page, if not it'll cause some problems
        $this->page = $maxpage;
        $this->drawTableinProgress = false;
        $this->setY($h+8);
    }
    
    function printRenumerationPackage($strExpSalary, $strBenefit, $strAvail, $lineheight=5) 
    {
        $spaceNeeded = (substr_count($strBenefit,"\n") + 1) * $lineheight + 30;
        if ($this->y >= $this->h - $this->bMargin - $spaceNeeded)
        {
          $this->AddPage();
          $l = $this->lMargin;
          $startheight = $h = $this->getY();
          $startY = $startheight;
        }
        else
        {
          $l = $this->lMargin;
          $startheight = $h = $this->getY();
          $startY = $h+10;
        }        
        
        $this->SetFont('SJIS','B',11);
        //$this->SetFont('Arial','B',11);
        $this->SetXY($l, $startY);
        $this->Cell(0,10,"RENUMERATION PACKAGE",0,1,'L');

        $startY += 10;

        $c1 = 65;

        $this->SetXY($l, $startY);
        $this->Cell(0,$lineheight,"  •  Expected Salary",0,0,'L');
        
        $this->SetXY($c1, $startY);
        $this->Cell(6,$lineheight,":",0,0,'L');
        $this->SetXY($c1 + 4, $startY);
        $this->MultiCell(55, $lineheight,$strExpSalary, 0,'L',0);

        $startY = $this->getY()+2;
        $this->SetXY($l, $startY);
        $this->Cell(0,$lineheight,"  •  Other Benefits",0,0,'L');
        
        $this->SetXY($c1, $startY);
        $this->Cell(6,$lineheight,":",0,0,'L');
        $this->SetXY($c1 + 4, $startY);
        $this->MultiCell(55, $lineheight,$strBenefit, 0,'L',0);

        $startY = $this->getY() + 5;
        $this->SetXY($l, $startY);
        $this->Cell(0,$lineheight,"AVAILABILITY",0,0,'L');
        
        $this->SetXY($c1, $startY);
        $this->Cell(6,$lineheight,":",0,0,'L');
        $this->SetXY($c1 + 4, $startY);
        $this->MultiCell(55, $lineheight,$strAvail, 0,'L',0);
    }
    
} // end of class


$pdf = new PDF('P','mm','A4');

$pdf->AddSJISFont();

$pdf->SetDisplayMode(95);
$pdf->SetMargins(15,30);
$pdf->SetAutoPageBreak(true,30);

$arrWorkingExperience = array();
$arrWorkingExperience[] = array("Jan&nbsp;2003 \n\nUntil\n\n Sep&nbsp;2006",
  "<b>PT. Honda Precision Parts Manufacturing</b>\n\n\n
A Honda Group Company which Manufactures Automatic Transmission, Engine Valve and other automotive precision parts. (Total Number of Workforce 1,500 People)",
  "PT. Honda Precision Parts Manufacturing\nasd\nasdadsd\nA Honda Group Company which Manufactures Automatic Transmission, Engine Valve and other automotive precision parts. (Total Number of Workforce 1,500 People)
        Deputy General Manager - Production Management Div
        =======asd======\n
        Apr 2004 - Present\n
        Deputy General Manager- Production Management Division\n
        ¿ Provided leadership and direction to Production Planning, Sales, Logistics, Purchasing and Information Technology to ensure company meets and exceeds its business plan targets including all agreed commercial and contractual Key Performance Indicators for our clientele.\n
        ¿ Developed divisional operating budget based on detailed forecasts and managed production management division to operate effectively within the operating budget.\n
        Apr 2004 - Present\n
        Deputy General Manager- Production Management Division\n
        ¿ Provided leadership and direction to Production Planning, Sales, Logistics, Purchasing and Information Technology to ensure company meets and exceeds its business plan targets including all agreed commercial and contractual Key Performance Indicators for our clientele.\n
        ¿ Developed divisional operating budget based on detailed forecasts and managed production management division to operate effectively within the operating budget.\n
        Deputy General Manager- Production Management Division\n
        ¿ Provided leadership and direction to Production Planning, Sales, Logistics, Purchasing and Information Technology to ensure company meets and exceeds its business plan targets including all agreed commercial and contractual Key Performance Indicators for our clientele.\n
        ¿ Developed divisional operating budget based on detailed forecasts and managed production management division to operate effectively within the operating budget.\n
        Apr 2004 - Present\n
        Deputy General Manager- Production Management Division\n
        ¿ Provided leadership and direction to Production Planning, Sales, Logistics, Purchasing and Information Technology to ensure company meets and exceeds its business plan targets including all agreed commercial and contractual Key Performance Indicators for our clientele.\n
        ¿ Developed divisional operating budget based on detailed forecasts and managed production management division to operate effectively within the operating budget.\n
        ¿ Held of business plan meeting with our strategic partners on such matter as profit management, information exchange and performance evaluation and feedback.\n
        ¿ Liaised with Government Officials, External Auditors, etc.\n
        Achievements:\nadsadsadsad\nasdasdasdaadsadsadsad\nasdasdasdaadsadsadsad\n
       adsadsadsad\nasdasdasdaadsadsadsad\nasdasdasdaadsadsadsad\nasdasdasda",
      "Cikampek");
$arrWorkingExperience[] = array("Jan&nbsp;2003 \n\nUntil\n\n Sep&nbsp;2006",
  "<b>PT. Honda Precision Parts Manufacturing</b>\n\n\n
A Honda Group Company which Manufactures Automatic Transmission, Engine Valve and other automotive precision parts. (Total Number of Workforce 1,500 People)",
  "PT. Honda Precision Parts Manufacturing\nasd\nasdadsd\nA Honda Group Company which Manufactures Automatic Transmission, Engine Valve and other automotive precision parts. (Total Number of Workforce 1,500 People)
        Deputy General Manager - Production Management Div\n=======asd======\n
        Apr 2004 - Present\n
        Deputy General Manager- Production Management Division\n
     ¿ Held of business plan meeting with our strategic partners on such matter as profit management, information exchange and performance evaluation and feedback.\n
        Deputy General Manager- Production Management Division\n
        Report to Production Management Director\n
     ¿ Held of business plan meeting with our strategic partners on such matter as profit management, information exchange and performance evaluation and feedback.\n
        ¿ Liaised with Government Officials, External Auditors, etc.\n
        Achievements:\nadsadsadsad",
      "Cikampek");

  $arrFormalEducation = array();
  $arrFormalEducation[] = array("1993-1997 ds a", "ITS", "Management Information System", "Adelaide as s Australia");
  $arrFormalEducation[] = array("1993-1997 ds a", "ITS", "Management Information System", "Adelaide as s Australia");

  $arrInformalEducation = array();
  $arrInformalEducation[] = array("2004", "ISO 900 1:2000 - Core T eam (1 Day)", "Global Solu tions"); 
  $arrInformalEducation[] = array("2004", "ISO 900 1:2000 - Core T eam (1 Day)", "Global Solu tions"); 
 
  $arrLanguageSkill = array();
  $arrLanguageSkill[] = array("English", "Good", "Good");
  $arrLanguageSkill[] = array("English", "Good", "Good");
  
  
$strDataID = "20060831-LS-6370";
$strDate = "03 Oct 2006";
$strTitle = "Mr.";
$strName = "é•·é‡Ž";
$strPosition = "å·®è‘—";      
$strAddress  = "Tanjung Duren Barat No. 1, Jakarta Selatan";
$strNationality  = "Indonesian";
$strDateBirth = "07 - 02 - 1973";
$strPlace = "Jakarta";
$strReligion = "Moslem";
$strMarital= "Married";
$strChild = "1";
$strAge = "33";
$strBlood = "AB";
$strDriving = "A";
$strOwnVech = "Car";
$strCurr = "Rp.";
$strSalary = "5000000";
$strNego = "nego";
$strAvail = "Now";
$strBenefit = "- Cel phone\n- Health insurance\n- Car";
$arrData = array("NAME" => $strTitle." ".$strName, "POSITION" => $strPosition,
                 "ADDRESS" => $strAddress, "NATIONALITY" => $strNationality,
                 "PLACE / DOB" => $strPlace." / ".$strDateBirth, "RELIGION" => $strReligion,
                 "MARITAL STATUS" => $strMarital." - ".$strChild." Children", "AGE" => $strAge,
                 "BLOOD TYPE" => $strBlood, "DRIVING LICENSE" => $strDriving,
                 "" => "", "OWN VEHICLE" => $strOwnVech);
                 

$pdf->Open();
$pdf->AddPage();
$pdf->AliasNbPages();
$pdf->drawTableinProgress = false;
$pdf->printPersonalDetail($strDataID, $strDate, $arrData);

$pdf->tablewidths = array(26,63,64,27);
$pdf->tablealign = array('L','L','L','L');
$arrHeader = array('PERIOD','SCHOOL','MAJOR','PLACE');
$pdf->printTable("FORMAL EDUCATION :",$arrHeader, $arrFormalEducation);

if ($pdf->y >= $pdf->h - $pdf->bMargin - 20)
{
  $pdf->AddPage();
}
$pdf->tablewidths = array(26,127,27);
$pdf->tablealign = array('L','L','L');
$arrHeader = array('PERIOD','SCHOOL / COURSE / TRAINING','PLACE');
$pdf->printTable("INFORMAL EDUCATION :",$arrHeader, $arrInformalEducation);

if ($pdf->y >= $pdf->h - $pdf->bMargin - 20)
{
  $pdf->AddPage();
}
$pdf->tablewidths = array(60,60,60);
$pdf->tablealign = array('L','L','L');
$arrHeader = array('LANGUAGE','SPEAKING','WRITING');
$pdf->printTable("LANGUAGE SKILL :",$arrHeader, $arrLanguageSkill, 4);

if ($pdf->y >= $pdf->h - $pdf->bMargin - 20)
{
  $pdf->AddPage();
}
$pdf->tablewidths = array(21,53,79,27);
$pdf->tablealign = array('C','C','L','C');
$arrHeader = array('PERIOD','COMPANY','POSITION','PLACE');
$pdf->printTable("WORK EXPERIENCE :",$arrHeader, $arrWorkingExperience);

if ($pdf->y >= $pdf->h - $pdf->bMargin - 15)
{
  $pdf->AddPage();
}
$pdf->tablewidths = array(180);
$pdf->tablealign = array('L');
$arrComputerSkill[] = array("MS OFFICE");
$pdf->printTable("COMPUTER SKILL :",null, $arrComputerSkill);

if ($pdf->y >= $pdf->h - $pdf->bMargin - 15)
{
  $pdf->AddPage();
}
$pdf->tablewidths = array(180);
$pdf->tablealign = array('L');
$arrQualification[] = array("Reza is a qualified professional in Production Management. He possesses Master Of Commerce in Economic and Finance from University of South Australia.<br />
        <br />
        previous experiences describe his ability to rapidly achieve organizational integration, easily assimilate job requirements and aggressively employ new methodologies. Energetic and self-motivated team player/builder.   <br />
      nces describe his ability to rapidly achieve organizational integration, easily assimilate job requirements and aggressively employ new methodologies. Energetic and self-motivated team player/builder.   <br />
        <br />
        previous experiences describe his ability to rapidly achieve organizational integration, easily assimilate job requirements and aggressively employ new methodologies. Energetic and self-motivated team player/builder.   <br />
        <br />
        During interview, Reza");
$pdf->printTable("A QUALIFICATION / ASSESSMENT SUMMARY :",null, $arrQualification);

$strExpSalary = "(".$strCurr.") ".$strSalary." ".$strNego;
$pdf->printRenumerationPackage($strExpSalary, $strBenefit, $strAvail);

//$pdf->morepagestable();
$pdf->Output('test.pdf',"I");
?>