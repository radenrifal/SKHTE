<?php
define('FPDF_FONTPATH','font/');
require('fpdf.php');

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
  $arrFormalEducation[] = array("1993-1997 ds a", "ITS", "Management Information System", "Adelaide as s Australia");
  $arrFormalEducation[] = array("1993-1997 ds a", "ITS", "Management Information System", "Adelaide as s Australia");
  $arrFormalEducation[] = array("1993-1997 ds a", "ITS", "Management Information System", "Adelaide as s Australia");
  $arrFormalEducation[] = array("1993-1997 ds a", "ITS", "Management Information System", "Adelaide as s Australia");
  $arrFormalEducation[] = array("1993-1997 ds a", "ITS", "Management Information System", "Adelaide as s Australia");
  $arrFormalEducation[] = array("1993-1997 ds a", "ITS", "Management Information System", "Adelaide as s Australia");
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
                 

$pdf->SetFont('Arial','',10);
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