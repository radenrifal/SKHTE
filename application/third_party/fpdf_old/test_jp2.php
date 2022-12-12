<?php
require('fpdf.php');            // Original Class
require('font/mbttfdef.php');   // Multi-Byte TrueType Font Define

// FPDF Version Check
if ((float) FPDF_VERSION < 1.51) die("You need FPDF version 1.51");

// Encoding & CMap List (CMap information from Acrobat Reader Resource/CMap folder)
$MBCMAP['BIG5']   = array ('CMap'=>'ETenms-B5-H'   ,'Ordering'=>'CNS1'  ,'Supplement'=>0);
$MBCMAP['GB']     = array ('CMap'=>'GBKp-EUC-H'    ,'Ordering'=>'GB1'   ,'Supplement'=>2);
$MBCMAP['SJIS']   = array ('CMap'=>'90msp-RKSJ-H'  ,'Ordering'=>'Japan1','Supplement'=>2);
$MBCMAP['UNIJIS'] = array ('CMap'=>'UniJIS-UTF16-H','Ordering'=>'Japan1','Supplement'=>5);
$MBCMAP['EUC-JP'] = array ('CMap'=>'EUC-H'         ,'Ordering'=>'Japan1','Supplement'=>1);
// EUC-JP has *problem* of underline and not support half-pitch characters.

// if you want convert encoding to SJIS from EUC-JP, you must change $EUC2SJIS to true.
$EUC2SJIS = false;

// Short Font Name ------------------------------------------------------------
// For Acrobat Reader (Windows, MacOS, Linux, Solaris etc)
DEFINE("BIG5",    'MSungStd-Light-Acro');
DEFINE("GB",      'STSongStd-Light-Acro');
DEFINE("KOZMIN",  'KozMinPro-Regular-Acro');
// For Japanese Windows Only
DEFINE("GOTHIC",  'MS-Gothic');
DEFINE("PGOTHIC", 'MS-PGothic');
DEFINE("UIGOTHIC",'MS-UIGothic');
DEFINE("MINCHO",  'MS-Mincho');
DEFINE("PMINCHO", 'MS-PMincho');

class PDF extends FPDF
{
    var $tablewidths;
    var $tablealign;
    var $footerset;
    var $dataHeader;
    var $dataItems;
    var $drawTableinProgress = false;
    var $MyFont = 'MS-PGothic';
    
    function PDF($orientation='P',$unit='mm',$format='A4')
    {
        //Call parent constructor
        $this->FPDF($orientation,$unit,$format);
    }

    // For Outline, Title, Sub-Title and ETC Multi-Byte Encoding
    function _unicode($txt)
    {
        if (function_exists('mb_detect_encoding')) {
            if (mb_detect_encoding($txt) != "ASCII") {
                $txt = chr(254).chr(255).mb_convert_encoding($txt,"UTF-16","auto");
            }
        }
        return $txt;
    }

    function AddCIDFont($family,$style,$name,$cw,$CMap,$registry,$ut,$up)
    {
      $i=count($this->fonts)+1;
      $fontkey=strtolower($family).strtoupper($style);
      $this->fonts[$fontkey] =
            array('i'=>$i,'type'=>'Type0','name'=>$name,'up'=>$up,'ut'=>$ut,'cw'=>$cw,'CMap'=>$CMap,'registry'=>$registry);
    }

    function AddMBFont($family='',$enc='')
    {
        global $MBTTFDEF,$MBCMAP;
        $gt=$MBTTFDEF;
        $gc=$MBCMAP;
        if ($enc == '' || isset($gc[$enc]) == false) {
            die("AddMBFont: ERROR Encoding [$enc] Undefine.");
        }
        if (isset($gt[$family])) {
            $ut=$gt[$family]['ut'];
            $up=$gt[$family]['up'];
            $cw=$gt[$family]['cw'];
            $cm=$gc[$enc]['CMap'];
            $od=$gc[$enc]['Ordering'];
            $sp=$gc[$enc]['Supplement'];
            $registry=array('ordering'=>$od,'supplement'=>$sp);
            $this->AddCIDFont($family,''  ,"$family"           ,$cw,$cm,$registry,$ut,$up);
            $this->AddCIDFont($family,'B' ,"$family,Bold"      ,$cw,$cm,$registry,$ut,$up);
            $this->AddCIDFont($family,'I' ,"$family,Italic"    ,$cw,$cm,$registry,$ut,$up);
            $this->AddCIDFont($family,'BI',"$family,BoldItalic",$cw,$cm,$registry,$ut,$up);
        } else {
            die("AddMBFont: ERROR FontName [$family] Undefine.");
        }
    }

    function GetStringWidth($s)
    {
      if($this->CurrentFont['type']=='Type0')
        return $this->GetMBStringWidth($s);
      else
        return parent::GetStringWidth($s);
    }

    function GetMBStringWidth($s)
    {
      //Multi-byte version of GetStringWidth()
      $l=0;
      $cw=&$this->CurrentFont['cw'];
      $japanese = ($this->CurrentFont['registry']['ordering'] == 'Japan1');
      $nb=strlen($s);
      $i=0;
      while($i<$nb)
      {
        $c=$s[$i];
        if(ord($c)<128)
        {
          $l+=$cw[$c];
          $i++;
        }
        else
        {
          $hwkana = ($japanese && ord($c)==142);
          $l+=$hwkana ? 500 : 1000;
          $i+=2;
        }
      }
      return $l*$this->FontSize/1000;
    }
    

    // Function Cell override for Encode Change.
    function Cell($w, $h = 0, $txt = '', $border = 0, $ln = 0, $align = '', $fill = 0, $link = '')
    {

        $k = $this->k;

        if ($this->y + $h > $this->PageBreakTrigger
            && !$this->InFooter
            && $this->AcceptPageBreak()) {
            $x  = $this->x;
            $ws = $this->ws;
            if ($ws > 0) {
                $this->ws = 0;
                $this->_out('0 Tw');
            }
            $this->AddPage($this->CurOrientation);
            $this->x = $x;
            if ($ws > 0) {
                $this->ws = $ws;
                $this->_out(sprintf('%.3f Tw', $ws * $k));
            }
        } // end if

        if ($w == 0) {
            $w = $this->w - $this->rMargin - $this->x;
        }

        $s          = '';
        if ($fill == 1 || $border == 1) {
            if ($fill == 1) {
                $op = ($border == 1) ? 'B' : 'f';
            } else {
                $op = 'S';
            }
            $s      = sprintf('%.2f %.2f %.2f %.2f re %s ', $this->x * $k, ($this->h - $this->y) * $k, $w * $k, -$h * $k, $op);
        } // end if

        if (is_string($border)) {
            $x     = $this->x;
            $y     = $this->y;
            if (strpos(' ' . $border, 'L')) {
                $s .= sprintf('%.2f %.2f m %.2f %.2f l S ', $x * $k, ($this->h - $y) * $k, $x * $k, ($this->h - ($y+$h)) * $k);
            }
            if (strpos(' ' . $border, 'T')) {
                $s .= sprintf('%.2f %.2f m %.2f %.2f l S ', $x * $k, ($this->h - $y) * $k, ($x + $w) * $k, ($this->h - $y) * $k);
            }
            if (strpos(' ' . $border, 'R')) {
                $s .= sprintf('%.2f %.2f m %.2f %.2f l S ', ($x + $w) * $k, ($this->h - $y) * $k, ($x + $w) * $k, ($this->h - ($y + $h)) * $k);
            }
            if (strpos(' ' . $border, 'B')) {
                $s .= sprintf('%.2f %.2f m %.2f %.2f l S ', $x * $k, ($this->h - ($y + $h)) * $k, ($x + $w) * $k, ($this->h - ($y + $h)) * $k);
            }
        } // end if

        if ($txt != '') {
            if ($align == 'R') {
                $dx = $w - $this->cMargin - $this->GetStringWidth($txt);
            }
            else if ($align == 'C') {
                $dx = ($w - $this->GetStringWidth($txt)) / 2;
            }
            else {
                $dx = $this->cMargin;
            }
            // For Japanese Encode Change
            global $EUC2SJIS;
            if ($EUC2SJIS && function_exists('mb_convert_encoding')) {
                $txt = mb_convert_encoding($txt,"SJIS","EUC-JP");
            }
            $txt    = str_replace(')', '\\)', str_replace('(', '\\(', str_replace('\\', '\\\\', $txt)));
            if ($this->ColorFlag) {
                $s  .= 'q ' . $this->TextColor . ' ';
            }
            $s      .= sprintf('BT %.2f %.2f Td (%s) Tj ET', ($this->x + $dx) * $k, ($this->h - ($this->y + .5 * $h + .3 * $this->FontSize)) * $k, $txt);
            $txt = stripslashes($txt);
            if ($this->underline) {
                $s  .= ' ' . $this->_dounderline($this->x+$dx, $this->y + .5 * $h + .3 * $this->FontSize, $txt);
            }
            if ($this->ColorFlag) {
                $s  .= ' Q';
            }
            if ($link) {
                $this->Link($this->x + $dx, $this->y + .5 * $h - .5 * $this->FontSize, $this->GetStringWidth($txt), $this->FontSize, $link);
            }
        } // end if

        if ($s) {
            $this->_out($s);
        }
        $this->lasth = $h;

        if ($ln > 0) {
            // Go to next line
            $this->y     += $h;
            if ($ln == 1) {
                $this->x = $this->lMargin;
            }
        } else {
            $this->x     += $w;
        }
    } // end of the "Cell()" method

    function MultiCell($w,$h,$txt,$border=0,$align='L',$fill=0)
    {
      if($this->CurrentFont['type']=='Type0')
        $this->MBMultiCell($w,$h,$txt,$border,$align,$fill);
      else
        parent::MultiCell($w,$h,$txt,$border,$align,$fill);
    }

    function MBMultiCell($w,$h,$txt,$border=0,$align='L',$fill=0)
    {
      //Multi-byte version of MultiCell()
      $cw=&$this->CurrentFont['cw'];
      $japanese = ($this->CurrentFont['registry']['ordering'] == 'Japan1');
      if($w==0)
        $w=$this->w-$this->rMargin-$this->x;
      $wmax=($w-2*$this->cMargin)*1000/$this->FontSize;
      $s=str_replace("\r",'',$txt);
      $nb=strlen($s);
      if($nb>0 and $s[$nb-1]=="\n")
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
      $ns=0;
      $nl=1;
      $ascii=true;
      while($i<$nb)
      {
        //Get next character
        $c=$s[$i];
        //Check if ASCII or MB
        $prev_ascii=$ascii;
        $ascii=(ord($c)<128);
        $hwkana = ($japanese && ord($c)==142);
        if($c=="\n")
        {
          //Explicit line break
          if($this->ws>0)
          {
            $this->ws=0;
            $this->_out('0 Tw');
          }
          $this->Cell($w,$h,substr($s,$j,$i-$j),$b,2,$align,$fill);
          $i++;
          $sep=-1;
          $j=$i;
          $l=0;
          $ns=0;
          $nl++;
          if($border and $nl==2)
            $b=$b2;
          continue;
        }
        if(!($ascii && $prev_ascii) && $i != $j)
        {
          $sep=$i;
          $ls=$l;
        }
        elseif($c==' ')
        {
          $sep=$i;
          $ls=$l;
          $ns++;
        }
        $l+=$ascii ? $cw[$c] : $hwkana ? 500 : 1000;
        if($l>$wmax)
        {
          //Automatic line break
          if($sep==-1)
          {
            if($i==$j)
              $i+=$ascii ? 1 : 2;
            if($this->ws>0)
            {
              $this->ws=0;
              $this->_out('0 Tw');
            }
            $this->Cell($w,$h,substr($s,$j,$i-$j),$b,2,$align,$fill);
          }
          else
          {
            if($align=='J')
            {
              if($s[$sep]==' ')
                $ns--;
              if($s[$i-1]==' ')
              {
                $ns--;
                $ls-=$cw[' '];
              }
              $this->ws=($ns>0) ? ($wmax-$ls)/1000*$this->FontSize/$ns : 0;
              $this->_out(sprintf('%.3f Tw',$this->ws*$this->k));
            }
            $this->Cell($w,$h,substr($s,$j,$sep-$j),$b,2,$align,$fill);
            $i=($s[$sep]==' ') ? $sep+1 : $sep;
          }
          $sep=-1;
          $j=$i;
          $l=0;
          $ns=0;
          $nl++;
          if($border and $nl==2)
            $b=$b2;
        }
        else
          $i+=$ascii ? 1 : 2;
      }
      //Last chunk
      if($this->ws>0)
      {
        $this->ws=0;
        $this->_out('0 Tw');
      }
      if($border and is_int(strpos($border,'B')))
        $b.='B';
      $this->Cell($w,$h,substr($s,$j,$i-$j),$b,2,$align,$fill);
      $this->x=$this->lMargin;
    }

    function Write($h,$txt,$link='')
    {
      if($this->CurrentFont['type']=='Type0')
        $this->MBWrite($h,$txt,$link);
      else
        parent::Write($h,$txt,$link);
    }

    function MBWrite($h,$txt,$link)
    {
      //Multi-byte version of Write()
      $cw=&$this->CurrentFont['cw'];
      $japanese = ($this->CurrentFont['registry']['ordering'] == 'Japan1');
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
        $c=$s[$i];
        //Check if ASCII or MB
        $ascii=(ord($c)<128);
        $hwkana = ($japanese && ord($c)==142);
        if($c=="\n")
        {
          //Explicit line break
          $this->Cell($w,$h,substr($s,$j,$i-$j),0,2,'',0,$link);
          $i++;
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
          continue;
        }
        if(!$ascii or $c==' ')
          $sep=$i;
        $l+=$ascii ? $cw[$c] : $hwkana ? 500 : 1000;
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
              $i++;
              $nl++;
              continue;
            }
            if($i==$j)
              $i+=$ascii ? 1 : 2;
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
          $i+=$ascii ? 1 : 2;
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
      $this->_out($W.']');
      if($font['registry']['ordering'] == 'Japan1')
        $this->_out(' 231 325 500 631 [500] 326 389 500');
      $this->_out(']');
      $this->_out('>>');
      $this->_out('endobj');
      //Font descriptor
      $this->_newobj();
      $this->_out('<</Type /FontDescriptor');
      $this->_out('/FontName /'.$font['name']);
      $this->_out('/Flags 6');
      $this->_out('/FontBBox [0 0 1000 1000]');
      $this->_out('/ItalicAngle 0');
      $this->_out('/Ascent 1000');
      $this->_out('/Descent 0');
      $this->_out('/CapHeight 1000');
      $this->_out('/StemV 10');
      $this->_out('>>');
      $this->_out('endobj');
    }

    function Footer() {
        // Check if Footer for this page already exists (do the same for Header())
        if(!isset($this->footerset[$this->page])) {
          $this->SetY(-25);          
          $this->SetFont($this->MyFont,'B',14);
          $this->Cell(0,5,'PT. JAC INDONESIA',0,2,'C');

          $this->SetFont($this->MyFont,'',9);
          $this->Cell(0,4,"Spinindo Building 1st Floor Jl.Wahid Hasyim No. 76 Jakarta 10340",0,2,'C');
          $this->Cell(0,4,"Phone : +62-21 315-9504, 315-9506 ; Fax : +62-21 315-9520, 315-06608",0,2,'C');
          $this->Cell(0,4,"e-Mail : recruitment@jacindonesia.com, bussiness-center@jacindonesia.com",0,2,'C');
          $this->Cell(0,4,"http://www.jacindonesia.com",0,0,'C');        
          if ($this->drawTableinProgress)
          {
            $this->Line($this->lMargin,$this->h - $this->bMargin, $this->w - $this->rMargin, $this->h - $this->bMargin);
          }
          //$this->SetFont($this->MyFont,'',10);
          $this->SetFont($this->MyFont,'',10);

          // set footerset
          $this->footerset[$this->page] = 1;
        }
    }

    function Header()
    {
        $this->SetXY(174,12);
        $this->SetFont($this->MyFont,'',9);
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
          //$this->SetFont($this->MyFont,'B',10);
          $this->SetFont($this->MyFont,'B',10);

          if ($this->dataHeader != null)
          {
            foreach($this->dataHeader as $col => $txt)
              $this->Cell($this->tablewidths[$col],6,$txt,1,0,'C',1);
            
            //$this->SetFont($this->MyFont,'',10);
            $this->SetFont($this->MyFont,'',10);
            $this->SetXY($this->lMargin,$this->tMargin + 8);
          }
          else
          {
            $this->Line($this->lMargin,$this->tMargin, $this->w - $this->rMargin, $this->tMargin);
            $this->SetXY($this->lMargin,$this->tMargin + 2);
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
        
        $this->SetFont($this->MyFont,'',8);
        $this->SetXY(150, 32);
        $this->Cell(40,0,$strDataID,0,0,'C');
        $this->SetXY(150, 42);
        $this->Cell(40,0,$strDate,0,0,'C');

        $startY = 50;
        $this->SetFont($this->MyFont,'B',10);
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
            $this->SetFont($this->MyFont,'B',10);
            $this->SetXY($l, $startY);
            $this->Cell(0,$lineheight,$key,0,0,'L');
            
            $this->SetXY($c1, $startY);
            $this->Cell(6,$lineheight,":",0,0,'L');
            $this->SetFont($this->MyFont,'',10);
            $this->SetXY($c1 + 4, $startY);
            $this->MultiCell(55, $lineheight,$value, 0,'L',0);
            $maxY = $this->getY();
          }
          else
          {
            $this->SetFont($this->MyFont,'B',10);
            $this->SetXY($l2, $startY);
            $this->Cell(0,$lineheight,$key,0,0,'L');
            $this->SetXY($c2, $startY);
            $this->Cell(6,$lineheight,":",0,0,'L');
            $this->SetFont($this->MyFont,'',10);
            $this->SetXY($c2 + 4, $startY);
            $this->MultiCell(59, $lineheight,$value, 0,'L',0);
            if ($maxY < $this->getY()) $startY = $this->getY();
            else $startY = $maxY;
            //$startY += $lineheight;
          }
        }
    }
    
    
    function printTable($caption, $arrHeader, $arrData, $minHeight=0, $lineheight=4.5) 
    {
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
    
        $this->SetFont($this->MyFont,'B',12);
        $this->SetXY($l, $startheight);
        $this->Cell(0,6,$caption,0,1,'L');
        
        $startheight = $this->getY();

        $this->SetFillColor(233,233,233);
        $this->SetFont($this->MyFont,'B',10);
        if ($arrHeader != null)
        {
          foreach($arrHeader AS $col => $txt)
            $this->Cell($this->tablewidths[$col],6,$txt,1,0,'C',1);
          
          $this->Ln();
        }
        $this->SetFont($this->MyFont,'',10);
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
          $startY = $h;
        }        
        
        $this->SetFont($this->MyFont,'B',11);
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
    
}




$pdf = new PDF('P','mm','A4');

$pdf->AddMBFont(GOTHIC ,'SJIS');
$pdf->AddMBFont(PGOTHIC,'SJIS');
$pdf->AddMBFont(MINCHO ,'SJIS');
$pdf->AddMBFont(PMINCHO,'SJIS');
$pdf->AddMBFont(KOZMIN ,'SJIS');

$pdf->SetDisplayMode(95);
$pdf->SetMargins(15,30);
$pdf->SetAutoPageBreak(true,30);

$FONT = GOTHIC;

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
  $arrFormalEducation[] = array("1993-1997 ds a", "ITS", "Management Information System", "Adelaide as s Australia");

  $arrInformalEducation = array();
  $arrInformalEducation[] = array("2004", "ISO 900 1:2000 - Core T eam (1 Day)", "Global Solu tions"); 
  $arrInformalEducation[] = array("2004", "ISO 900 1:2000 - Core T eam (1 Day)", "Global Solu tions"); 
  $arrInformalEducation[] = array("2004", "ISO 900 1:2000 - Core T eam (1 Day)", "Global Solu tions"); 
 
  $arrLanguageSkill = array();
  $arrLanguageSkill[] = array("English", "Good", "Good");
  $arrLanguageSkill[] = array("English", "Good", "Good");
  
  
$strDataID = "20060831-LS-6370";
$strDate = "03 Oct 2006";
$strTitle = "Mr.";
$strName = "Reza Febian";
$strPosition = "Director";      
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
$pdf->printTable("FORMAL EDUCATION :",$arrHeader, $arrFormalEducation, 20);

$pdf->tablewidths = array(26,127,27);
$pdf->tablealign = array('L','L','L');
$arrHeader = array('PERIOD','SCHOOL / COURSE / TRAINING','PLACE');
$pdf->printTable("INFORMAL EDUCATION :",$arrHeader, $arrInformalEducation, 20);

$pdf->tablewidths = array(60,60,60);
$pdf->tablealign = array('L','L','L');
$arrHeader = array('LANGUAGE','SPEAKING','WRITING');
$pdf->printTable("LANGUAGE SKILL :",$arrHeader, $arrLanguageSkill, 20, 4);

$pdf->tablewidths = array(21,53,79,27);
$pdf->tablealign = array('C','C','L','C');
$arrHeader = array('PERIOD','COMPANY','POSITION','PLACE');
$pdf->printTable("WORK EXPERIENCE :",$arrHeader, $arrWorkingExperience, 15);

$pdf->tablewidths = array(180);
$pdf->tablealign = array('L');
$arrComputerSkill[] = array("MS OFFICE");
$pdf->printTable("COMPUTER SKILL :",null, $arrComputerSkill, 15);

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
$pdf->printTable("A QUALIFICATION / ASSESSMENT SUMMARY :",null, $arrQualification, 15);

$strExpSalary = "(".$strCurr.") ".$strSalary." ".$strNego;
$pdf->printRenumerationPackage($strExpSalary, $strBenefit, $strAvail);

//$pdf->morepagestable();
$pdf->Output('test.pdf',"I");

?>
