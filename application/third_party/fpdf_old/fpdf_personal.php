<?php
define('FPDF_FONTPATH','font/');
require('fpdf.php');
require_once("phpqrcode/qrlib.php");

class PDF extends FPDF {
    var $tablewidths;
    var $tablealign;
    var $footerset;
    var $dataHeader;
    var $dataItems;
    var $drawTableinProgress = false;
    
    function PDF($orientation='P',$unit='mm',$format='A4')
    {
        //Call parent constructor
        $this->FPDF($orientation,$unit,$format);
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
          $this->SetFont('Arial','',10);
          // set footerset
          $this->footerset[$this->page] = 1;
        }
    }
        
        
    function Header()
    {
    	//To be implemented in your own inherited class
        $this->SetXY(174,12);
        $this->SetFont('Arial','',9);
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
          $this->SetFont('Arial','B',10);
          if ($this->dataHeader != null)
          {
            foreach($this->dataHeader as $col => $txt)
              $this->Cell($this->tablewidths[$col],6,$txt,1,0,'C',1);
            
            $this->SetFont('Arial','',10);
            $this->SetXY($this->lMargin,$this->tMargin + 8);
          }
          else
          {
            $this->Line($this->lMargin,$this->tMargin, $this->w - $this->rMargin, $this->tMargin);
            $this->SetXY($this->lMargin,$this->tMargin + 2);
          }
        }

    }

        
    function Cell($w,$h=0,$txt='',$border=0,$ln=0,$align='',$fill=0,$link='')
    {
    	//Output a cell
    	$k=$this->k;
    	if($this->y+$h>$this->PageBreakTrigger && !$this->InFooter && $this->AcceptPageBreak())
    	{
    		//Automatic page break
    		$x=$this->x;
    		$ws=$this->ws;
    		if($ws>0)
    		{
    			$this->ws=0;
    			$this->_out('0 Tw');
    		}
    		$this->AddPage($this->CurOrientation);
    		$this->x=$x;
    		if($ws>0)
    		{
    			$this->ws=$ws;
    			$this->_out(sprintf('%.3f Tw',$ws*$k));
    		}
    	}
    	if($w==0)
    		$w=$this->w-$this->rMargin-$this->x;
    	$s='';
    	if($fill==1 || $border==1)
    	{
    		if($fill==1)
    			$op=($border==1) ? 'B' : 'f';
    		else
    			$op='S';
    		$s=sprintf('%.2f %.2f %.2f %.2f re %s ',$this->x*$k,($this->h-$this->y)*$k,$w*$k,-$h*$k,$op);
    	}
    	if(is_string($border))
    	{
    		$x=$this->x;
    		$y=$this->y;
    		if(strpos($border,'L')!==false)
    			$s.=sprintf('%.2f %.2f m %.2f %.2f l S ',$x*$k,($this->h-$y)*$k,$x*$k,($this->h-($y+$h))*$k);
    		if(strpos($border,'T')!==false)
    			$s.=sprintf('%.2f %.2f m %.2f %.2f l S ',$x*$k,($this->h-$y)*$k,($x+$w)*$k,($this->h-$y)*$k);
    		if(strpos($border,'R')!==false)
    			$s.=sprintf('%.2f %.2f m %.2f %.2f l S ',($x+$w)*$k,($this->h-$y)*$k,($x+$w)*$k,($this->h-($y+$h))*$k);
    		if(strpos($border,'B')!==false)
    			$s.=sprintf('%.2f %.2f m %.2f %.2f l S ',$x*$k,($this->h-($y+$h))*$k,($x+$w)*$k,($this->h-($y+$h))*$k);
    	}
    	if($txt!=='')
    	{
        $teksku = str_replace("<strong>","",$txt);
        $teksku = str_replace("</strong>","",$teksku);
        $teksku = str_replace("<em>","",$teksku);
        $teksku = str_replace("</em>","",$teksku);
    		if($align=='R')
    			$dx=$w-$this->cMargin-$this->GetStringWidth($teksku);
    		elseif($align=='C')
    			$dx=($w-$this->GetStringWidth($teksku))/2;
    		else
    			$dx=$this->cMargin;
    		if($this->ColorFlag)
    			$s.='q '.$this->TextColor.' ';
    		
        if (strpos($txt, '<strong><em>') !== false)
        {
          $this->SetFont($this->FontFamily,'BI',$this->FontSizePt);
        }
        else if (strpos($txt, '<strong>') !== false)
        {
          $this->SetFont($this->FontFamily,'B',$this->FontSizePt);
        }
        else if (strpos($txt, '<em>') !== false)
        {
          $this->SetFont($this->FontFamily,'I',$this->FontSizePt);
        }
        $txt2=str_replace(')','\\)',str_replace('(','\\(',str_replace('\\','\\\\',$teksku)));
        $s.=sprintf('BT %.2f %.2f Td (%s) Tj ET',($this->x+$dx)*$k,($this->h-($this->y+.5*$h+.3*$this->FontSize))*$k,$txt2);
        
        
    		if($this->underline)
    			$s.=' '.$this->_dounderline($this->x+$dx,$this->y+.5*$h+.3*$this->FontSize,$txt);
    		if($this->ColorFlag)
    			$s.=' Q';
    		if($link)
    			$this->Link($this->x+$dx,$this->y+.5*$h-.5*$this->FontSize,$this->GetStringWidth($txt),$this->FontSize,$link);
    	}
    	if($s)
    		$this->_out($s);
        
      if($txt!=='')
      {
        if (strpos($txt, '</em></strong>') !== false)
        {
          $this->SetFont($this->FontFamily,'',$this->FontSizePt);
        }
        else if (strpos($txt, '</strong>') !== false)
        {
          $this->SetFont($this->FontFamily,'',$this->FontSizePt);
        }
        else if (strpos($txt, '</em>') !== false)
        {
          $this->SetFont($this->FontFamily,'',$this->FontSizePt);
        }
      }
    	$this->lasth=$h;
    	if($ln>0)
    	{
    		//Go to next line
    		$this->y+=$h;
    		if($ln==1)
    			$this->x=$this->lMargin;
    	}
    	else
    		$this->x+=$w;
    }

    function printPersonalDetail($strDataID, $strDate, $arrData, $lineheight=5) 
    {
        
        // some things to set and 'remember'
        $l = $this->lMargin;
        $startheight = $h = 30;
        $startpage = $currpage = $this->page;
    
        // calculate the whole width
        $fullwidth = $this->w - $this->lMargin - $this->rMargin;
        
        $this->SetFont('Arial','',8);
        $this->SetXY(150, 32);
        $this->Cell(40,0,$strDataID,0,0,'C');
        $this->SetXY(150, 42);
        $this->Cell(40,0,$strDate,0,0,'C');

        $startY = 50;
        $this->SetFont('Arial','B',10);
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
            $this->SetFont('Arial','B',10);
            $this->SetXY($l, $startY);
            $this->Cell(0,$lineheight,$key,0,0,'L');
            
            $this->SetXY($c1, $startY);
            $this->Cell(6,$lineheight,":",0,0,'L');
            $this->SetFont('Arial','',10);
            $this->SetXY($c1 + 4, $startY);
            $this->MultiCell(55, $lineheight,$value, 0,'L',0);
            $maxY = $this->getY();
          }
          else
          {
            $this->SetFont('Arial','B',10);
            $this->SetXY($l2, $startY);
            $this->Cell(0,$lineheight,$key,0,0,'L');
            $this->SetXY($c2, $startY);
            $this->Cell(6,$lineheight,":",0,0,'L');
            $this->SetFont('Arial','',10);
            $this->SetXY($c2 + 4, $startY);
            $this->MultiCell(59, $lineheight,$value, 0,'L',0);
            if ($maxY < $this->getY()) $startY = $this->getY();
            else $startY = $maxY;
            //$startY += $lineheight;
          }
        }
      $this->setY($this->getY() + 6);
    }
    
    
    function printTable($caption, $arrHeader, $arrData, $minHeight=0, $lineheight=4.5) 
    {
        if (count($arrData)==0) return;
        $startheight = $h = $this->getY();
        if ($minHeight!=0)
          if ($this->y >= $this->h - $this->bMargin - $minHeight)
          {
            $this->AddPage();
            $startheight = $h = $this->getY();
          }
        
        $l = $this->lMargin;
        $startpage = $currpage = $this->page;
        
        // calculate the whole width
        $fullwidth = 0;
        foreach($this->tablewidths AS $width) 
        {
          $fullwidth += $width;
        }
    
        $this->SetFont('Arial','B',12);
        $this->SetXY($l, $startheight);
        $this->Cell(0,6,$caption,0,1,'L');
        
        $startheight = $this->getY();

        $this->SetFillColor(233,233,233);
        $this->SetFont('Arial','B',10);
        if ($arrHeader != null)
        {
          foreach($arrHeader AS $col => $txt)
            $this->Cell($this->tablewidths[$col],6,$txt,1,0,'C',1);
          
          $this->Ln();
        }
        $this->SetFont('Arial','',10);
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
              $txt = str_replace("<BR>","\n",$txt);
              $txt = str_replace("<br />","\n",$txt);
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
          $startY = $h;
        }        
        
        $this->SetFont('Arial','B',11);
        $this->SetXY($l, $startY);
        $this->Cell(0,10,"RENUMERATION PACKAGE",0,1,'L');

        $startY += 10;

        $c1 = 65;

        $this->SetXY($l, $startY);
        $this->Cell(0,$lineheight,"  » Expected Salary",0,0,'L');
        
        $this->SetXY($c1, $startY);
        $this->Cell(6,$lineheight,":",0,0,'L');
        $this->SetXY($c1 + 4, $startY);
        $this->MultiCell(55, $lineheight,$strExpSalary, 0,'L',0);

        $startY = $this->getY()+2;
        $this->SetXY($l, $startY);
        $this->Cell(0,$lineheight,"  » Other Benefits",0,0,'L');
        
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

?>