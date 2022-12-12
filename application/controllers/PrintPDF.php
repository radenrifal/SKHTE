<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class PrintPDF extends CI_Controller {
	function __construct() {
    parent::__construct();
    $this->load->library('pdf');
    $this->load->library('ciqrcode'); //pemanggilan library QR CODE
  }
    
  function index()
  {
    $pdf = new exFPDF();
    $pdf->FPDF('L', 'mm', "A4");
    $pdf->SetMargins(1, 10, 1);

    //$pdf = new FPDF_Protection();
    //$pdf->FPDF('L', 'mm', "A4");
    //$pdf->lMargin = 1;
    //$pdf->rMargin = 1;
    //$pdf->tMargin = 1;
    //$pdf->bMargin = 2;
    $pdf->AliasNbPages();
    
    $arrParams = array(
      'nation'            => 'INDONESIA',
      'name'              => 'Sapi',
      'ear_number'        => '001',
      'register_number'   => 'ASDFA',
      'gender'            => 'Betina',
      'birthday'          => '2020-01-01',
      'owner'             => 'BALAI EMBRIO TERNAK CIPELANG',
      'address'           => 'Cipelang Bogor',
    );
    
    if(!empty($arrParams)){
      $pdf->addPage();
      $pdf->SetAutoPageBreak(false);
      $x = $pdf->GetX(); //1
      $y = $pdf->GetY(); //1
      $pdf->SetY($y);
      
      /*
      $config['cacheable']    = true; //boolean, the default is true
      $config['cachedir']     = './assets/'; //string, the default is application/cache/
      $config['errorlog']     = './assets/'; //string, the default is application/logs/
      $config['imagedir']     = './assets/images/barcode/'; //direktori penyimpanan qr code
      $config['quality']      = true; //boolean, the default is true
      $config['size']         = '1024'; //interger, the default is 1024
      $config['black']        = array(224,255,255); // array, default is array(255,255,255)
      $config['white']        = array(70,130,180); // array, default is array(0,0,0)
      $this->ciqrcode->initialize($config);
      $image_name             = strtolower($arrParams['register_number'].".png"); //buat name dari qr code sesuai dengan nim
      $params['data']         = $arrParams['register_number']; //data yang akan di jadikan QR CODE
      $params['level']        = 'H'; //H=High
      $params['size']         = 10;
      $params['savename']     = FCPATH.$config['imagedir'].$image_name; //simpan image QR CODE ke folder assets/images/
      
      */
      $image_name             = strtolower($arrParams['register_number'].".png"); //buat name dari qr code sesuai dengan nim
      
      //create a QR Code and save it as a png image file named test.png
      QRcode::png($arrParams['register_number'], 'assets/images/barcode/'.$image_name);  
      
      $pdf->Image(BASE_URL."assets/images/logo_balai.jpg",15,10,30); 
      $pdf->Image(BASE_URL."assets/images/logo_bet.jpg",252,10,30); 
      $pdf->Image(BASE_URL."assets/images/background_bet.jpg",67,20,145); 
      $pdf->Image(BASE_URL."assets/images/sertifikasi.jpg",240,180,45); 
      $pdf->Image(BASE_URL."assets/images/logo_sertifikasi.jpg", 176,160,12); 

      //this is the second method
      $pdf->Image(BASE_URL."assets/images/barcode/".$image_name, 198, 141, 25, 25, "png");
      
      $pdf->ClippingRect(36,66,72,52,true);
      $pdf->Image(BASE_URL."assets/images/img1.jpg", 37,67,70,50); 
      $pdf->UnsetClipping();
      
      $pdf->SetY($y);
      
      //START HEADER
      $pdf->SetFont('Arial','',18);
      $pdf->SetWidths(array(10,275,10));
      $border = array('', '', '');
      $align  = array('', 'C', '');
      $style  = array('', 'B', ''); 
   
      $caption = array('', 'BALAI EMBRIO TERNAK CIPELANG', '');
      $pdf->FancyRow($caption, $border, $align, $style); 
      $pdf->Ln(3);
      $pdf->SetFont('Arial','',16);
      $caption = array('', 'DIREKTORAT JENDERAL PETERNAKAN DAN KESEHATAN HEWAN', '');
      $pdf->FancyRow($caption, $border, $align, $style);  
      $pdf->Ln(3);
      $caption = array('', 'KEMENTERIAN PERTANIAN', '');
      $pdf->FancyRow($caption, $border, $align, $style);   
      $pdf->Ln(5);
      $pdf->SetFont('Arial','',14);
      $caption = array('', 'SURAT KETERANGAN HASIL TRANSFER EMBRIO', '');
      $pdf->FancyRow($caption, $border, $align, $style); 
      //END HEADER
      
      $pdf->Ln(5);
      $pdf->SetFont('Arial','',12);
      $pdf->SetWidths(array(10,33,25,5,105,5,30,5,60,10));
      $border = array('', '', '', '', '', '', '', '', '', '');
      $align  = array('', 'C', '', 'C', 'L', '', '', '', '', '');
      $style  = array('', 'B', '', '', '', '', '', '', '', '');
      $caption = array('', '', 'Bangsa', ':', $arrParams['nation'], '', 'No. Registrasi', ':', $arrParams['register_number'], '');
      $pdf->FancyRow($caption, $border, $align, $style); 
      $caption = array('', '', 'Nama', ':', $arrParams['name'], '', 'Jenis Kelamin', ':', $arrParams['gender'], '');
      $pdf->FancyRow($caption, $border, $align, $style); 
      $caption = array('', '', 'No. Telinga', ':', $arrParams['ear_number'], '', 'Tanggal Lahir', ':', $this->tanggal_indo($arrParams['birthday']), '');
      $pdf->FancyRow($caption, $border, $align, $style);
      
      $x = $pdf->GetX(); 
      $y = $pdf->GetY(); 
      
      $pdf->Ln(5);
      
      $xD = $pdf->GetX(); 
      
      $pdf->Cell(5,5, '',0,0,'C');
      $pdf->SetFont('Arial','',12);
      $pdf->Cell(185,10,'',0,0,'C');
      $pdf->MultiCell(70,10, 'LOREM IPSUM DOLOR',1,'C',0, 3, 3, 3, 3);
      $pdf->Ln(3);
      $pdf->setX($xD+5);
      $pdf->Cell(185,10,'',0,0,'C');
      $pdf->MultiCell(70,10, 'LOREM IPSUM DOLOR',1,'C',0, 3, 3, 3, 3);
      $pdf->Ln(3);
      $pdf->setX($xD+5);
      $pdf->Cell(185,10,'',0,0,'C');
      $pdf->MultiCell(70,10, 'LOREM IPSUM DOLOR',1,'C',0, 3, 3, 3, 3);
      $pdf->Ln(3);
      $pdf->setX($xD+5);
      $pdf->Cell(185,10,'',0,0,'C');
      $pdf->MultiCell(70,10, 'LOREM IPSUM DOLOR',1,'C',0, 3, 3, 3, 3);
      
      /*
      $pdf->SetFont('Arial','',12);
      $pdf->SetWidths(array(10,25,35,5,115,60,10));
      $border = array('', '', '', '', '', '1', '');
      $align  = array('', '', '', '', '', 'C', '');
      $style  = array('', '', '', '', '', '', '');
      $caption = array('', '', '', '', '', 'LOREM IPSUM DOLOR', '');
      $pdf->FancyRow($caption, $border, $align, $style);
      $pdf->Ln(3);
      $caption = array('', '', '', '', '', 'LOREM IPSUM DOLOR', '');
      $pdf->FancyRow($caption, $border, $align, $style);
      $pdf->Ln(3);
      $caption = array('', '', '', '', '', 'LOREM IPSUM DOLOR', '');
      $pdf->FancyRow($caption, $border, $align, $style);
      $pdf->Ln(3);
      $caption = array('', '', '', '', '', 'LOREM IPSUM DOLOR', '');
      $pdf->FancyRow($caption, $border, $align, $style);
      */
      $pdf->SetY($y+6);
      $pdf->SetX($x);
      
      $pdf->Line(184,$y+16,188,$y+16);
      $pdf->Line(188,$y+10,191,$y+10);
      $pdf->Line(188,$y+23,191,$y+23);
      $pdf->Line(188,$y+10,188,$y+23);
      
      $pdf->Line(184,$y+42,188,$y+42);
      $pdf->Line(188,$y+36,191,$y+36);
      $pdf->Line(188,$y+49,191,$y+49);
      $pdf->Line(188,$y+36,188,$y+49);
      
      $pdf->SetWidths(array(10,25,70,18,60));
      $border = array('', '', '', '', 'LTR',);
      $align  = array('', '', '', 'C', 'C');
      $style  = array('', '', '', '', '');
      $caption = array('', '', '', '', '');
      $pdf->FancyRow($caption, $border, $align, $style);
      $border = array('', '', '', '', 'LR',);
      $caption = array('', '', '', 'Bapak (Sire)', 'LOREM IPSUM DOLOR SIT AMET CONSECTETUR');
      $pdf->FancyRow($caption, $border, $align, $style);
      $border = array('', '', '', '', 'LBR',);
      $caption = array('', '', '', '', '');
      $pdf->FancyRow($caption, $border, $align, $style);
      $pdf->Ln(6);
      
      $border = array('', '', '', '', 'LTR',);
      $caption = array('', '', '', '', '');
      $pdf->FancyRow($caption, $border, $align, $style);
      $border = array('', '', '', '', 'LR',);
      $caption = array('', '', '', 'Induk (Dam)', 'LOREM IPSUM DOLOR SIT AMET CONSECTETUR');
      $pdf->FancyRow($caption, $border, $align, $style);
      $border = array('', '', '', '', 'LBR',);
      $caption = array('', '', '', '', '');
      $pdf->FancyRow($caption, $border, $align, $style);
      
      $pdf->SetY($y);
      $pdf->SetX($x);
      $pdf->Ln(60);
      //$pdf->SetWidths(array(10,33,21,3,114,5,25,3,60,10));
      $pdf->SetWidths(array(10,33,25,5,105,5,30,5,60,10));
      $border = array('', '', '', '', '', '', '', '', '', '');
      $align  = array('', 'C', '', 'C', 'L', '', '', '', '', '');
      $style  = array('', 'B', '', '', '', '', '', '', '', '');
      $caption = array('', '', 'Pemilik', ':', $arrParams['owner'], '', '', '', '', '');
      $pdf->FancyRow($caption, $border, $align, $style);
      $caption = array('', '', 'Alamat', ':', $arrParams['address'], '', '', '', '', '');
      $pdf->FancyRow($caption, $border, $align, $style);

      $pdf->Ln(1);
      $pdf->SetWidths(array(10,33,25,5,108,5,70,5,65,10));
      $border = array('', '', '', '', '', '', '', '');
      $align  = array('', 'C', '', 'C', 'L', '', '', '');
      $style  = array('', 'B', '', '', '', '', '', '');
      $caption = array('', '', '', '', '', '', 'Cipelang - Bogor, '.$this->tanggal_indo(date('Y-m-d')), '');
      $pdf->FancyRow($caption, $border, $align, $style);
      $caption = array('', '', '', '', '', '', 'Kepala Balai Embrio Ternak', '');
      $pdf->FancyRow($caption, $border, $align, $style);
      $pdf->Ln(20);
      $pdf->SetTextColor(160,160,160);
      $style  = array('', 'B', '', '', '', '', '', '');
      $caption = array('', '', '', '', '', '', 'Ditandatangani secara elektronik', '');
      $pdf->FancyRow($caption, $border, $align, $style);
      $pdf->SetTextColor(0,0,0);
      $style  = array('', 'B', '', '', '', '', 'U', '');
      $caption = array('', '', '', '', '', '', 'Drh. Oloan Parlindungan MP', '');
      $pdf->FancyRow($caption, $border, $align, $style);
      $style  = array('', 'B', '', '', '', '', '', '');
      $caption = array('', '', '', '', '', '', 'NIP. 19641126 199203 1 001', '');
      $pdf->FancyRow($caption, $border, $align, $style);
      
      $pdf->SetTextColor(160,160,160);
      $pdf->SetFont('Arial','',9);
      $pdf->SetWidths(array(15,5,250));
      $border = array('', '', '');
      $align  = array('', 'C', '');
      $style  = array('', '', '');
      $caption = array('', '', 'Catatan : ');
      $pdf->FancyRow($caption, $border, $align, $style);
      $caption = array('', '-', 'UU ITE No 11 Tahun 2008 Pasal 5 Ayat 1');
      $pdf->FancyRow($caption, $border, $align, $style);
      $caption = array('', '-', 'Informasi Elektronik dan/atau Dokumen Elektronik dan atau hasil cetaknya merupakan alat bukti hukum yang sah ');
      $pdf->FancyRow($caption, $border, $align, $style);
      $caption = array('', '-', 'Dokumen ini telah ditandatangai secara elektronik menggunakan sertifikat elektronik yang diterbitkan BSrE');
      $pdf->FancyRow($caption, $border, $align, $style);
      
      $fileName = strtoupper("REPORT SKHTE");
      $type = 'I';
      $pdf->output("$fileName.pdf", $type);
    }
  }

  public function carddonor()
	{
    //error_reporting(E_ALL);
    //ini_set('display_errors', 1);

		$pdf = new exFPDF();
    $pdf->FPDF('L', 'mm', "A4");
    $pdf->SetMargins(1, 5, 1);
    $pdf->AliasNbPages();

    $pdf->AddPage();
    $pdf->SetFont('arial','',18);
    $pdf->AddFont('FontUTF8','','Arimo-Regular.php'); 
    $pdf->AddFont('FontUTF8','B','Arimo-Bold.php');
    $pdf->AddFont('FontUTF8','I','Arimo-Italic.php');
    $pdf->AddFont('FontUTF8','BI','Arimo-BoldItalic.php');

    $pdf->SetAutoPageBreak(true, 1);
    $pdf->SetFooterShow(true);

    $x = $pdf->GetX();
    $y = $pdf->GetY();
    $pdf->SetX($x);
    $pdf->SetY($y);
    
    $strWidthCol = '{300}';
    $table = new easyTable($pdf, $strWidthCol, 'align:C; paddingY:0.5;');
    $table->rowStyle('align:{C}; font-style:B;');
    $table->easyCell('KARTU TERNAK DONOR', 'align:C;'); 
    $table->printRow(true); 
    $table->endTable();

    $pdf->Image(BASE_URL."assets/images/logo_balai.jpg",230,15,28); 
    $pdf->Image(BASE_URL."assets/images/logo_bet.png",249,29,17); 
    //$pdf->Image(BASE_URL."assets/images/background_bet.jpg",67,20,145); 

    $x = $pdf->GetX();
    $y = $pdf->GetY();
    //set the outline color
    $pdf->SetDrawColor(0);
    //set the outline width (note that only its outer half will be shown)
    $pdf->SetLineWidth(1);
    $pdf->ClippingRoundedRect($x+80, $y, 40, 30, 3, true);
    $pdf->Image(BASE_URL."assets/images/img1.jpg", $x+80, $y, 40, 30); 
    $pdf->UnsetClipping();
    
    $pdf->ClippingRoundedRect($x+125, $y, 40, 30, 3, true);
    $pdf->Image(BASE_URL."assets/images/img1.jpg", $x+125, $y, 40, 30); 
    $pdf->UnsetClipping();
    
    $pdf->ClippingRoundedRect($x+170, $y, 40, 30, 3, true);
    $pdf->Image(BASE_URL."assets/images/img1.jpg", $x+170, $y, 40, 30); 
    $pdf->UnsetClipping();

    $pdf->SetY($y+30);

    $pdf->SetFont('arial','',10);
    $pdf->SetLineWidth(0);

    $strWidthCol = '{200, 100}';
    $table = new easyTable($pdf, $strWidthCol, 'align:C; paddingY:0.5;');
    $table->rowStyle('align:{C}; font-style:B;');
    $table->easyCell('', 'align:C;'); 
    $table->easyCell('BALAI EMBRIO TERNAK', 'align:C;');
    $table->printRow(true); 
    $table->endTable();

    $arrHeadParams = array(
      0 => array(
        'textLeft'    => "NAMA TERNAK",
        'valueLeft'   => "SAPI PERAH",
        'textRight'   => "NAMA INDUK",
        'valueRight'  => "MAMAH SAPI",
        'ln'  => 0,
      ),
      1 => array(
        'textLeft'    => "NO. TELINGA",
        'valueLeft'   => "T00123",
        'textRight'   => "KODE INDUK",
        'valueRight'  => "ZE00123",
        'ln'  => 0,
      ),
      2 => array(
        'textLeft'    => "TANGGAL LAHIR",
        'valueLeft'   => $this->tanggal_indo("2022-08-01"),
        'textRight'   => "ASAL",
        'valueRight'  => "BOGOR",
        'ln'  => 0,
      ),
      3 => array(
        'textLeft'    => "STATUS ASET",
        'valueLeft'   => "ACTIVE",
        'textRight'   => "",
        'valueRight'  => "",
        'ln'  => 0,
      ),
      4 => array(
        'textLeft'    => "ALASAN KELUAR",
        'valueLeft'   => "DIPERJUAL BELIKAN",
        'textRight'   => "",
        'valueRight'  => "",
        'ln'  => 0,
      ),
      5 => array(
        'textLeft'    => "TANGGAL KELUAR",
        'valueLeft'   => $this->tanggal_indo("2022-08-15"),
        'textRight'   => "",
        'valueRight'  => "",
        'ln'  => 2,
      ),
      6 => array(
        'textLeft'    => "SILSILAH KETUA",
        'valueLeft'   => "-",
        'textRight'   => "",
        'valueRight'  => "",
        'ln'  => 2,
      ),
      7 => array(
        'textLeft'    => "NAMA BULL",
        'valueLeft'   => "AABBCCDDEE",
        'textRight'   => "",
        'valueRight'  => "",
        'ln'  => 0,
      ),
      8 => array(
        'textLeft'    => "KODE BULL",
        'valueLeft'   => "BB000123",
        'textRight'   => "",
        'valueRight'  => "",
        'ln'  => 0,
      ),
      9 => array(
        'textLeft'    => "ASAL",
        'valueLeft'   => "BOGOR",
        'textRight'   => "",
        'valueRight'  => "",
        'ln'  => 0,
      ),
    );

    $strWidthCol = '{10, 50, 5, 100, 1, 50, 5, 100}';
    $table = new easyTable($pdf, $strWidthCol, 'align:C; paddingY:0.5;');
    if(!empty($arrHeadParams))
    {
      foreach($arrHeadParams AS $idx => $value){
        $table->rowStyle('align:{C, L, C, L, C, L, C, L}; font-style:B;');
        $table->easyCell('', ''); 
        if(!empty($value['textLeft'])){
          $table->easyCell($value['textLeft'], '');
          $table->easyCell(':', '');
          $table->easyCell($value['valueLeft'], '');
        }
        $table->easyCell('', '');
        if(!empty($value['textRight'])){
          $table->easyCell($value['textRight'], '');
          $table->easyCell(':', '');
          $table->easyCell($value['valueRight'], '');
        }
        $table->printRow(true); 
        $pdf->ln($value['ln']);
      }
    }
    $table->endTable();
    
    $x = $pdf->GetX();
    $y = $pdf->GetY();

    //147 KIRI ---- 1
    $pdf->SetFont('arial', '', 10);
    $strWidthCol = '{10, 50, 87}';
    $table = new easyTable($pdf, $strWidthCol, 'align:L;');
    $table->rowStyle('align:{L};');
    $table->easyCell('', 'align:L;');
    $table->easyCell('PRODUKSI', 'align:C; font-style:B; bgcolor:#3D8CFF; font-color:#ffffff;');
    $table->easyCell('', 'align:L;');
    $table->printRow(true); 
    $table->endTable();

    $pdf->SetFont('arial', '', 8);
    $strWidthCol = '{10, 10, 43, 15, 27, 10, 10, 10, 15}';
    $table = new easyTable($pdf, $strWidthCol, 'align:L; border:1;');
    $table->rowStyle('align:{CCCCCCCCC}; font-style:B;');
    $table->easyCell('', 'rowspan:2; border:0;');
    $table->easyCell('NO', 'rowspan:2;');
    $table->easyCell('TANGGAL PRODUKSI/FLUSHING', 'rowspan:2;');
    $table->easyCell('JUMLAH EMBRIO', 'rowspan:2;');
    $table->easyCell('KUALITAS EMBRIO', 'colspan:4;');
    $table->easyCell('KODE EMBRIO', 'rowspan:2;');
    $table->printRow(true);

    $table->rowStyle('align:{CCCCCCCCC}; font-style:B;');
    $table->easyCell('TRANSFERABLE');
    $table->easyCell('DG');
    $table->easyCell('UF');
    $table->easyCell('UF');
    $table->printRow(true); 

    for($i=1; $i<=5; $i++){
      $table->rowStyle('align:{CRLCCCCCL};');
      $table->easyCell('', 'border:0;');
      $table->easyCell($i.'.');
      $table->easyCell("2022-08-0".$i."/ LOREM IPSUM");
      $table->easyCell(57);
      $table->easyCell('YA');
      $table->easyCell('1');
      $table->easyCell('2');
      $table->easyCell('3');
      $table->easyCell('TF00123');
      $table->easyCell('', 'border:0;');
      $table->printRow(true);
    }
    $table->endTable(3);
    $xL1 = $pdf->GetX();
    $yL1 = $pdf->GetY();
    //END 147 KIRI ---- 1

    //147 KANAN --- 1
    $pdf->SetX($x);
    $pdf->SetY($y);
    $pdf->SetFont('arial', '', 10);
    $strWidthCol = '{5, 50, 87, 5}';
    $table = new easyTable($pdf, $strWidthCol, 'align:R;');
    $table->rowStyle('align:{L};');
    $table->easyCell('', 'align:L;');
    $table->easyCell('PEMBUNTINGAN DONOR', 'align:C; font-style:B; bgcolor:#FFE51E;');
    $table->easyCell('', 'align:L;');
    $table->easyCell('', 'align:L;');
    $table->printRow(true); 
    $table->endTable();

    $pdf->SetFont('arial', '', 8);
    $strWidthCol = '{10, 10, 33, 17, 21, 12, 17, 22, 10}';
    $table = new easyTable($pdf, $strWidthCol, 'align:R; border:1;');
    $table->rowStyle('align:{CCCCCCCCC}; font-style:B;');
    $table->easyCell('', 'rowspan:2; border:0;');
    $table->easyCell('NO', 'rowspan:2;');
    $table->easyCell('FLUSHING TERAKHIR', 'rowspan:2;');
    $table->easyCell('TANGGAL IB', 'rowspan:2;');
    $table->easyCell('PKB', 'colspan:2;');
    $table->easyCell('TANGGAL PATRUS', 'rowspan:2;');
    $table->easyCell('KETERANGAN', 'rowspan:2;');
    $table->easyCell('', 'rowspan:2; border:0;');
    $table->printRow(true);

    $table->rowStyle('align:{CCCCCCCCC};');
    $table->easyCell('TANGGAL');
    $table->easyCell('HASIL');
    $table->printRow(true); 

    for($i=1; $i<=5; $i++){
      $table->rowStyle('align:{CRLCCCCCL};');
      $table->easyCell('', 'border:0;');
      $table->easyCell($i.'.');
      $table->easyCell('');
      $table->easyCell('');
      $table->easyCell('');
      $table->easyCell('');
      $table->easyCell('');
      $table->easyCell('');
      $table->easyCell('', 'border:0;');
      $table->printRow(true);
    }
    $table->endTable(3);
    $xR1 = $pdf->GetX();
    $yR1 = $pdf->GetY();
    //END 147 KANAN --- 1

    //147 KANAN --- 2
    $pdf->SetFont('arial', '', 10);
    $strWidthCol = '{5, 50, 87, 5}';
    $table = new easyTable($pdf, $strWidthCol, 'align:R;');
    $table->rowStyle('align:{L};');
    $table->easyCell('', 'align:L;');
    $table->easyCell('PRODUKSI SUSU', 'align:C; font-style:B; bgcolor:#b2b2b2;');
    $table->easyCell('', 'align:L;');
    $table->easyCell('', 'align:L;');
    $table->printRow(true); 
    $table->endTable();
    
    $pdf->SetFont('arial', '', 8);
    $strWidthCol = '{5, 30, 50, 62}';
    $table = new easyTable($pdf, $strWidthCol, 'align:R; border:1;');
    $table->rowStyle('align:{CCCC}; font-style:B; ');
    $table->easyCell('', 'border:0;');
    $table->easyCell('LAKTASI Ke -');
    $table->easyCell('RATA - RATA PRODUKSI (L)');
    $table->easyCell('', 'border:0;');
    $table->printRow(true);

    for($i=1; $i<=1; $i++){
      $table->rowStyle('align:{CLLCCCCCL};');
      $table->easyCell('', 'border:0;');
      $table->easyCell('PERTAMA');
      $table->easyCell('1256 /hari');
      $table->easyCell('', 'border:0;');
      $table->printRow(true);
    }
    $table->endTable(3);
    //END 147 KANAN --- 2

    //147 KIRI --- 2
    $pdf->SetX($xL1);
    $pdf->SetY($yL1);
    $xL2 = $pdf->GetX();
    $yL2 = $pdf->GetY();
    $pdf->SetFont('arial', '', 10);
    $strWidthCol = '{10, 50, 87}';
    $table = new easyTable($pdf, $strWidthCol, 'align:L;');
    $table->rowStyle('align:{L};');
    $table->easyCell('', 'align:L;');
    $table->easyCell('STATUS KESEHATAN', 'align:C; font-style:B; bgcolor:#d60000; font-color:#ffffff;');
    $table->easyCell('', 'align:L;');
    $table->printRow(true); 
    $table->endTable();

    $pdf->SetFont('arial', '', 8);
    $strWidthCol = '{10, 10, 50, 22, 23, 35}';
    $table = new easyTable($pdf, $strWidthCol, 'align:L; border:1;');
    $table->rowStyle('align:{CCCCCC}; font-style:B;');
    $table->easyCell('', 'border:0;');
    $table->easyCell('NO');
    $table->easyCell('GEJALA KLINIS');
    $table->easyCell('DIAGNOSA');
    $table->easyCell('PENGOBATAN');
    $table->easyCell('KETERANGAN');
    $table->printRow(true);

    for($i=1; $i<=5; $i++){
      $table->rowStyle('align:{CLLLLL};');
      $table->easyCell('', 'border:0;');
      $table->easyCell($i.'.');
      $table->easyCell('');
      $table->easyCell('');
      $table->easyCell('');
      $table->easyCell('');
      $table->easyCell('', 'border:0;');
      $table->printRow(true);
    }
    $table->endTable(3);
    //END 147 KIRI --- 2

    $fileName = strtoupper("KARTU DONOR");
    $type     = 'I';
    $pdf->output("$fileName.pdf", $type);
	}

  public function cardpedet()
	{
    //error_reporting(E_ALL);
    //ini_set('display_errors', 1);

		$pdf = new exFPDF();
    $pdf->FPDF('L', 'mm', "A4");
    $pdf->SetMargins(1, 5, 1);
    $pdf->AliasNbPages();

    $pdf->AddPage();
    $pdf->SetFont('arial','',18);
    $pdf->AddFont('FontUTF8','','Arimo-Regular.php'); 
    $pdf->AddFont('FontUTF8','B','Arimo-Bold.php');
    $pdf->AddFont('FontUTF8','I','Arimo-Italic.php');
    $pdf->AddFont('FontUTF8','BI','Arimo-BoldItalic.php');

    $pdf->SetAutoPageBreak(true, 1);
    $pdf->SetFooterShow(true);

    $x = $pdf->GetX();
    $y = $pdf->GetY();
    $pdf->SetX($x);
    $pdf->SetY($y);
    
    $strWidthCol = '{300}';
    $table = new easyTable($pdf, $strWidthCol, 'align:C; paddingY:0.5;');
    $table->rowStyle('align:{C}; font-style:B;');
    $table->easyCell('KARTU TERNAK PEDET', 'align:C;'); 
    $table->printRow(true); 
    $table->endTable();

    $pdf->Image(BASE_URL."assets/images/logo_balai.jpg",230,15,28); 
    $pdf->Image(BASE_URL."assets/images/logo_bet.png",249,29,17); 
    //$pdf->Image(BASE_URL."assets/images/background_bet.jpg",67,20,145); 

    $x = $pdf->GetX();
    $y = $pdf->GetY();
    //set the outline color
    $pdf->SetDrawColor(0);
    //set the outline width (note that only its outer half will be shown)
    $pdf->SetLineWidth(1);
    $pdf->ClippingRoundedRect($x+80, $y, 40, 30, 3, true);
    $pdf->Image(BASE_URL."assets/images/img1.jpg", $x+80, $y, 40, 30); 
    $pdf->UnsetClipping();
    
    $pdf->ClippingRoundedRect($x+125, $y, 40, 30, 3, true);
    $pdf->Image(BASE_URL."assets/images/img1.jpg", $x+125, $y, 40, 30); 
    $pdf->UnsetClipping();
    
    $pdf->ClippingRoundedRect($x+170, $y, 40, 30, 3, true);
    $pdf->Image(BASE_URL."assets/images/img1.jpg", $x+170, $y, 40, 30); 
    $pdf->UnsetClipping();

    $pdf->SetY($y+30);

    $pdf->SetFont('arial','',10);
    $pdf->SetLineWidth(0);

    $strWidthCol = '{200, 100}';
    $table = new easyTable($pdf, $strWidthCol, 'align:C; paddingY:0.5;');
    $table->rowStyle('align:{C}; font-style:B;');
    $table->easyCell('', 'align:C;'); 
    $table->easyCell('BALAI EMBRIO TERNAK', 'align:C;');
    $table->printRow(true); 
    $table->endTable();

    $arrHeadParams = array(
      0 => array(
        'textLeft'    => "NAMA TERNAK",
        'valueLeft'   => "SAPI PERAH",
        'textRight'   => "NAMA INDUK",
        'valueRight'  => "MAMAH SAPI",
        'ln'  => 0,
      ),
      1 => array(
        'textLeft'    => "NO. TELINGA",
        'valueLeft'   => "T00123",
        'textRight'   => "KODE INDUK",
        'valueRight'  => "ZE00123",
        'ln'  => 0,
      ),
      2 => array(
        'textLeft'    => "TANGGAL LAHIR",
        'valueLeft'   => $this->tanggal_indo("2022-08-01"),
        'textRight'   => "ASAL",
        'valueRight'  => "BOGOR",
        'ln'  => 0,
      ),
      3 => array(
        'textLeft'    => "STATUS ASET",
        'valueLeft'   => "ACTIVE",
        'textRight'   => "",
        'valueRight'  => "",
        'ln'  => 0,
      ),
      4 => array(
        'textLeft'    => "ALASAN KELUAR",
        'valueLeft'   => "DIPERJUAL BELIKAN",
        'textRight'   => "",
        'valueRight'  => "",
        'ln'  => 0,
      ),
      5 => array(
        'textLeft'    => "TANGGAL KELUAR",
        'valueLeft'   => $this->tanggal_indo("2022-08-15"),
        'textRight'   => "",
        'valueRight'  => "",
        'ln'  => 2,
      ),
      6 => array(
        'textLeft'    => "SILSILAH KETUA",
        'valueLeft'   => "-",
        'textRight'   => "",
        'valueRight'  => "",
        'ln'  => 2,
      ),
      7 => array(
        'textLeft'    => "NAMA BULL",
        'valueLeft'   => "AABBCCDDEE",
        'textRight'   => "",
        'valueRight'  => "",
        'ln'  => 0,
      ),
      8 => array(
        'textLeft'    => "KODE BULL",
        'valueLeft'   => "BB000123",
        'textRight'   => "",
        'valueRight'  => "",
        'ln'  => 0,
      ),
      9 => array(
        'textLeft'    => "ASAL",
        'valueLeft'   => "BOGOR",
        'textRight'   => "",
        'valueRight'  => "",
        'ln'  => 0,
      ),
    );

    $strWidthCol = '{10, 50, 5, 100, 1, 50, 5, 100}';
    $table = new easyTable($pdf, $strWidthCol, 'align:C; paddingY:0.5;');
    if(!empty($arrHeadParams))
    {
      foreach($arrHeadParams AS $idx => $value){
        $table->rowStyle('align:{C, L, C, L, C, L, C, L}; font-style:B;');
        $table->easyCell('', ''); 
        if(!empty($value['textLeft'])){
          $table->easyCell($value['textLeft'], '');
          $table->easyCell(':', '');
          $table->easyCell($value['valueLeft'], '');
        }
        $table->easyCell('', '');
        if(!empty($value['textRight'])){
          $table->easyCell($value['textRight'], '');
          $table->easyCell(':', '');
          $table->easyCell($value['valueRight'], '');
        }
        $table->printRow(true); 
        $pdf->ln($value['ln']);
      }
    }
    $table->endTable();
    
    $x = $pdf->GetX();
    $y = $pdf->GetY();

    //147 KIRI ---- 1
    $pdf->SetFont('arial', '', 10);
    $strWidthCol = '{10, 50, 87}';
    $table = new easyTable($pdf, $strWidthCol, 'align:L;');
    $table->rowStyle('align:{L};');
    $table->easyCell('', 'align:L;');
    $table->easyCell('CATATAN PERTUMBUHAN', 'align:C; font-style:B; bgcolor:#3D8CFF; font-color:#ffffff;');
    $table->easyCell('', 'align:L;');
    $table->printRow(true); 
    $table->endTable();

    $pdf->SetFont('arial', '', 8);
    $strWidthCol = '{10, 10, 27, 12, 12, 12, 20, 12, 35}';
    $table = new easyTable($pdf, $strWidthCol, 'align:L; border:1;');
    $table->rowStyle('align:{CCCCCCCCC}; font-style:B;');
    $table->easyCell('', 'border:0;');
    $table->easyCell('NO');
    $table->easyCell('TANGGAL');
    $table->easyCell('BB');
    $table->easyCell('LD');
    $table->easyCell('PG');
    $table->easyCell('LINGKAR SCORTUM');
    $table->easyCell('BCS');
    $table->easyCell('KETERANGAN');
    $table->printRow(true);

    //Isi dengan sesuai data
    for($i=1; $i<=5; $i++)
    {
      //rowdata
      $table->rowStyle('align:{CRCCCCCCL};');
      $table->easyCell('', 'border:0;');
      $table->easyCell($i.'.');
      $table->easyCell("2022-08-0".$i);
      $table->easyCell(57);
      $table->easyCell(103);
      $table->easyCell(77);
      $table->easyCell(25);
      $table->easyCell(3);
      $table->easyCell('BAIK');
      $table->easyCell('', 'border:0;');
      $table->printRow(true);
    }
    $table->endTable(3);
    $xL1 = $pdf->GetX();
    $yL1 = $pdf->GetY();
    //END 147 KIRI ---- 1

    //147 KANAN --- 1
    $pdf->SetX($x);
    $pdf->SetY($y);
    $pdf->SetFont('arial', '', 10);
    $strWidthCol = '{5, 50, 85, 5}';
    $table = new easyTable($pdf, $strWidthCol, 'align:R;');
    $table->rowStyle('align:{L};');
    $table->easyCell('', 'align:L;');
    $table->easyCell('STATUS KESEHATAN', 'align:C; font-style:B; bgcolor:#FFE51E;');
    $table->easyCell('', 'align:L;');
    $table->easyCell('', 'align:L;');
    $table->printRow(true); 
    $table->endTable();

    $pdf->SetFont('arial', '', 8);
    $strWidthCol = '{10, 10, 40, 22, 23, 35, 10}';
    $table = new easyTable($pdf, $strWidthCol, 'align:R; border:1;');
    $table->rowStyle('align:{CCCCCC}; font-style:B;');
    $table->easyCell('', 'border:0;');
    $table->easyCell('NO');
    $table->easyCell('GEJALA KLINIS');
    $table->easyCell('DIAGNOSA');
    $table->easyCell('PENGOBATAN');
    $table->easyCell('KETERANGAN');
    $table->easyCell('', 'border:0;');
    $table->printRow(true);

    for($i=1; $i<=5; $i++){
      $table->rowStyle('align:{CRLLLL};');
      $table->easyCell('', 'border:0;');
      $table->easyCell($i.'.');
      $table->easyCell('');
      $table->easyCell('');
      $table->easyCell('');
      $table->easyCell('');
      $table->easyCell('', 'border:0;');
      $table->printRow(true);
    }
    $table->endTable(3);
    $xR1 = $pdf->GetX();
    $yR1 = $pdf->GetY();
    //END 147 KANAN --- 1

    $pdf->ln(5);
    $pdf->SetFont('arial', '', 12);
    $strWidthCol = '{10, 50, 87}';
    $table = new easyTable($pdf, $strWidthCol, 'align:L;');
    $table->rowStyle('align:{L};');
    $table->easyCell('', 'align:L;');
    $table->easyCell('Keterangan : ', 'align:R; font-style:IB;');
    $table->easyCell('', 'align:L;');
    $table->printRow(true); 
    $table->endTable();

    $pdf->SetFont('arial', '', 10);
    $strWidthCol = '{50, 50, 50, 50}';
    $table = new easyTable($pdf, $strWidthCol, 'align:C;');
    $table->rowStyle('align:{L}; font-style:B;');
    $table->easyCell('BB : BERAT BADAN', 'align:L;');
    $table->easyCell('LB : LINGKAR DADA', 'align:L;');
    $table->easyCell('TG : TINGGI GUMBA', 'align:L;');
    $table->easyCell('PB : PANJANG BADAN', 'align:L;');
    $table->printRow(true); 
    $table->endTable();

    $fileName = strtoupper("KARTU PEDET");
    $type     = 'I';
    $pdf->output("$fileName.pdf", $type);
	}

  public function cardresipien()
	{
    //error_reporting(E_ALL);
    //ini_set('display_errors', 1);

		$pdf = new exFPDF();
    $pdf->FPDF('L', 'mm', "A4");
    $pdf->SetMargins(1, 5, 1);
    $pdf->AliasNbPages();

    $pdf->AddPage();
    $pdf->SetFont('arial','',18);
    $pdf->AddFont('FontUTF8','','Arimo-Regular.php'); 
    $pdf->AddFont('FontUTF8','B','Arimo-Bold.php');
    $pdf->AddFont('FontUTF8','I','Arimo-Italic.php');
    $pdf->AddFont('FontUTF8','BI','Arimo-BoldItalic.php');

    $pdf->SetAutoPageBreak(true, 1);
    $pdf->SetFooterShow(true);

    $x = $pdf->GetX();
    $y = $pdf->GetY();
    $pdf->SetX($x);
    $pdf->SetY($y);
    
    $strWidthCol = '{300}';
    $table = new easyTable($pdf, $strWidthCol, 'align:C; paddingY:0.5;');
    $table->rowStyle('align:{C}; font-style:B;');
    $table->easyCell('KARTU TERNAK RESIPIEN', 'align:C;'); 
    $table->printRow(true); 
    $table->endTable();

    $pdf->Image(BASE_URL."assets/images/logo_balai.jpg",230,15,28); 
    $pdf->Image(BASE_URL."assets/images/logo_bet.png",249,29,17); 
    //$pdf->Image(BASE_URL."assets/images/background_bet.jpg",67,20,145); 

    $x = $pdf->GetX();
    $y = $pdf->GetY();
    //set the outline color
    $pdf->SetDrawColor(0);
    //set the outline width (note that only its outer half will be shown)
    $pdf->SetLineWidth(1);
    $pdf->ClippingRoundedRect($x+80, $y, 40, 30, 3, true);
    $pdf->Image(BASE_URL."assets/images/img1.jpg", $x+80, $y, 40, 30); 
    $pdf->UnsetClipping();
    
    $pdf->ClippingRoundedRect($x+125, $y, 40, 30, 3, true);
    $pdf->Image(BASE_URL."assets/images/img1.jpg", $x+125, $y, 40, 30); 
    $pdf->UnsetClipping();
    
    $pdf->ClippingRoundedRect($x+170, $y, 40, 30, 3, true);
    $pdf->Image(BASE_URL."assets/images/img1.jpg", $x+170, $y, 40, 30); 
    $pdf->UnsetClipping();

    $pdf->SetY($y+30);

    $pdf->SetFont('arial','',10);
    $pdf->SetLineWidth(0);

    $strWidthCol = '{200, 100}';
    $table = new easyTable($pdf, $strWidthCol, 'align:C; paddingY:0.5;');
    $table->rowStyle('align:{C}; font-style:B;');
    $table->easyCell('', 'align:C;'); 
    $table->easyCell('BALAI EMBRIO TERNAK', 'align:C;');
    $table->printRow(true); 
    $table->endTable();

    $arrHeadParams = array(
      0 => array(
        'textLeft'    => "NAMA TERNAK",
        'valueLeft'   => "SAPI PERAH",
        'textRight'   => "NAMA INDUK",
        'valueRight'  => "MAMAH SAPI",
        'ln'  => 0,
      ),
      1 => array(
        'textLeft'    => "NO. TELINGA",
        'valueLeft'   => "T00123",
        'textRight'   => "KODE INDUK",
        'valueRight'  => "ZE00123",
        'ln'  => 0,
      ),
      2 => array(
        'textLeft'    => "TANGGAL LAHIR",
        'valueLeft'   => $this->tanggal_indo("2022-08-01"),
        'textRight'   => "ASAL",
        'valueRight'  => "BOGOR",
        'ln'  => 0,
      ),
      3 => array(
        'textLeft'    => "STATUS ASET",
        'valueLeft'   => "ACTIVE",
        'textRight'   => "",
        'valueRight'  => "",
        'ln'  => 0,
      ),
      4 => array(
        'textLeft'    => "ALASAN KELUAR",
        'valueLeft'   => "DIPERJUAL BELIKAN",
        'textRight'   => "",
        'valueRight'  => "",
        'ln'  => 0,
      ),
      5 => array(
        'textLeft'    => "TANGGAL KELUAR",
        'valueLeft'   => $this->tanggal_indo("2022-08-15"),
        'textRight'   => "",
        'valueRight'  => "",
        'ln'  => 2,
      ),
      6 => array(
        'textLeft'    => "SILSILAH KETUA",
        'valueLeft'   => "-",
        'textRight'   => "",
        'valueRight'  => "",
        'ln'  => 2,
      ),
      7 => array(
        'textLeft'    => "NAMA BULL",
        'valueLeft'   => "AABBCCDDEE",
        'textRight'   => "",
        'valueRight'  => "",
        'ln'  => 0,
      ),
      8 => array(
        'textLeft'    => "KODE BULL",
        'valueLeft'   => "BB000123",
        'textRight'   => "",
        'valueRight'  => "",
        'ln'  => 0,
      ),
      9 => array(
        'textLeft'    => "ASAL",
        'valueLeft'   => "BOGOR",
        'textRight'   => "",
        'valueRight'  => "",
        'ln'  => 0,
      ),
    );

    $strWidthCol = '{10, 50, 5, 100, 1, 50, 5, 100}';
    $table = new easyTable($pdf, $strWidthCol, 'align:C; paddingY:0.5;');
    if(!empty($arrHeadParams))
    {
      foreach($arrHeadParams AS $idx => $value){
        $table->rowStyle('align:{C, L, C, L, C, L, C, L}; font-style:B;');
        $table->easyCell('', ''); 
        if(!empty($value['textLeft'])){
          $table->easyCell($value['textLeft'], '');
          $table->easyCell(':', '');
          $table->easyCell($value['valueLeft'], '');
        }
        $table->easyCell('', '');
        if(!empty($value['textRight'])){
          $table->easyCell($value['textRight'], '');
          $table->easyCell(':', '');
          $table->easyCell($value['valueRight'], '');
        }
        $table->printRow(true); 
        $pdf->ln($value['ln']);
      }
    }
    $table->endTable();
    
    $x = $pdf->GetX();
    $y = $pdf->GetY();

    //147 KIRI ---- 1
    $pdf->SetFont('arial', '', 10);
    $strWidthCol = '{10, 50, 87}';
    $table = new easyTable($pdf, $strWidthCol, 'align:L;');
    $table->rowStyle('align:{L};');
    $table->easyCell('', 'align:L;');
    $table->easyCell('APLIKASI TE', 'align:C; font-style:B; bgcolor:#3D8CFF; font-color:#ffffff;');
    $table->easyCell('', 'align:L;');
    $table->printRow(true); 
    $table->endTable();

    $pdf->SetFont('arial', '', 8);
    $strWidthCol = '{10, 10, 40, 40, 30, 20}';
    $table = new easyTable($pdf, $strWidthCol, 'align:L; border:1;');
    $table->rowStyle('align:{CCCCCCCCC}; font-style:B;');
    $table->easyCell('', 'border:0;');
    $table->easyCell('NO');
    $table->easyCell('TANGGAL PRODUKSI/FLUSHING');
    $table->easyCell('TANGGAL ESTRUS/METESTRUS');
    $table->easyCell('TANGGAL TE');
    $table->easyCell('KODE EMBRIO');
    $table->printRow(true);

    for($i=1; $i<=5; $i++){
      $table->rowStyle('align:{CRLCCCCCL};');
      $table->easyCell('', 'border:0;');
      $table->easyCell($i.'.');
      $table->easyCell("2022-08-0".$i."/ LOREM IPSUM");
      $table->easyCell("2022-08-0".$i);
      $table->easyCell("2022-08-0".$i);
      $table->easyCell('TF00123');
      $table->easyCell('', 'border:0;');
      $table->printRow(true);
    }
    $table->endTable(3);
    $xL1 = $pdf->GetX();
    $yL1 = $pdf->GetY();
    //END 147 KIRI ---- 1

    //147 KANAN --- 1
    $pdf->SetX($x);
    $pdf->SetY($y);
    $pdf->SetFont('arial', '', 10);
    $strWidthCol = '{5, 70, 67, 5}';
    $table = new easyTable($pdf, $strWidthCol, 'align:R;');
    $table->rowStyle('align:{L};');
    $table->easyCell('', 'align:L;');
    $table->easyCell('PEMERIKSAAN KEBUNTINGAN', 'align:C; font-style:B; bgcolor:#FFE51E;');
    $table->easyCell('', 'align:L;');
    $table->easyCell('', 'align:L;');
    $table->printRow(true); 
    $table->endTable();

    $pdf->SetFont('arial', '', 8);
    $strWidthCol = '{10, 10, 22, 22, 21, 15, 17, 22, 13}';
    $table = new easyTable($pdf, $strWidthCol, 'align:R; border:1;');
    $table->rowStyle('align:{CCCCCCCCC}; font-style:B;');
    $table->easyCell('', 'rowspan:2; border:0;');
    $table->easyCell('NO', 'rowspan:2;');
    $table->easyCell('TANGGAL TE', 'rowspan:2;');
    $table->easyCell('TANGGAL IB', 'rowspan:2;');
    $table->easyCell('PKB', 'colspan:2;');
    $table->easyCell('TANGGAL PATRUS', 'rowspan:2;');
    $table->easyCell('KETERANGAN', 'rowspan:2;');
    $table->easyCell('', 'rowspan:2; border:0;');
    $table->printRow(true);

    $table->rowStyle('align:{CCCCCCCCC};');
    $table->easyCell('TANGGAL');
    $table->easyCell('HASIL');
    $table->printRow(true); 

    for($i=1; $i<=5; $i++){
      $table->rowStyle('align:{CRLCCCCCL};');
      $table->easyCell('', 'border:0;');
      $table->easyCell($i.'.');
      $table->easyCell('');
      $table->easyCell('');
      $table->easyCell('');
      $table->easyCell('');
      $table->easyCell('');
      $table->easyCell('');
      $table->easyCell('', 'border:0;');
      $table->printRow(true);
    }
    $table->endTable(3);
    $xR1 = $pdf->GetX();
    $yR1 = $pdf->GetY();
    //END 147 KANAN --- 1

    //147 KANAN --- 2
    $pdf->SetFont('arial', '', 10);
    $strWidthCol = '{5, 50, 87, 5}';
    $table = new easyTable($pdf, $strWidthCol, 'align:R;');
    $table->rowStyle('align:{L};');
    $table->easyCell('', 'align:L;');
    $table->easyCell('PRODUKSI SUSU', 'align:C; font-style:B; bgcolor:#b2b2b2;');
    $table->easyCell('', 'align:L;');
    $table->easyCell('', 'align:L;');
    $table->printRow(true); 
    $table->endTable();
    
    $pdf->SetFont('arial', '', 8);
    $strWidthCol = '{5, 30, 50, 62}';
    $table = new easyTable($pdf, $strWidthCol, 'align:R; border:1;');
    $table->rowStyle('align:{CCCC}; font-style:B; ');
    $table->easyCell('', 'border:0;');
    $table->easyCell('LAKTASI Ke -');
    $table->easyCell('RATA - RATA PRODUKSI (L)');
    $table->easyCell('', 'border:0;');
    $table->printRow(true);

    for($i=1; $i<=1; $i++){
      $table->rowStyle('align:{CLLCCCCCL};');
      $table->easyCell('', 'border:0;');
      $table->easyCell('PERTAMA');
      $table->easyCell('1256 /hari');
      $table->easyCell('', 'border:0;');
      $table->printRow(true);
    }
    $table->endTable(3);
    //END 147 KANAN --- 2

    //147 KIRI --- 2
    $pdf->SetX($xL1);
    $pdf->SetY($yL1);
    $xL2 = $pdf->GetX();
    $yL2 = $pdf->GetY();
    $pdf->SetFont('arial', '', 10);
    $strWidthCol = '{10, 50, 87}';
    $table = new easyTable($pdf, $strWidthCol, 'align:L;');
    $table->rowStyle('align:{L};');
    $table->easyCell('', 'align:L;');
    $table->easyCell('STATUS KESEHATAN', 'align:C; font-style:B; bgcolor:#d60000; font-color:#ffffff;');
    $table->easyCell('', 'align:L;');
    $table->printRow(true); 
    $table->endTable();

    $pdf->SetFont('arial', '', 8);
    $strWidthCol = '{10, 10, 50, 22, 23, 35}';
    $table = new easyTable($pdf, $strWidthCol, 'align:L; border:1;');
    $table->rowStyle('align:{CCCCCC}; font-style:B;');
    $table->easyCell('', 'border:0;');
    $table->easyCell('NO');
    $table->easyCell('GEJALA KLINIS');
    $table->easyCell('DIAGNOSA');
    $table->easyCell('PENGOBATAN');
    $table->easyCell('KETERANGAN');
    $table->printRow(true);

    for($i=1; $i<=5; $i++){
      $table->rowStyle('align:{CLLLLL};');
      $table->easyCell('', 'border:0;');
      $table->easyCell($i.'.');
      $table->easyCell('');
      $table->easyCell('');
      $table->easyCell('');
      $table->easyCell('');
      $table->easyCell('', 'border:0;');
      $table->printRow(true);
    }
    $table->endTable(3);
    //END 147 KIRI --- 2

    $fileName = strtoupper("KARTU RESIPIEN");
    $type     = 'I';
    $pdf->output("$fileName.pdf", $type);
	}

  public function cardbibit()
	{
    //error_reporting(E_ALL);
    //ini_set('display_errors', 1);

		$pdf = new exFPDF();
    $pdf->FPDF('L', 'mm', "A4");
    $pdf->SetMargins(1, 5, 1);
    $pdf->AliasNbPages();

    $pdf->AddPage();
    $pdf->SetFont('arial','',18);
    $pdf->AddFont('FontUTF8','','Arimo-Regular.php'); 
    $pdf->AddFont('FontUTF8','B','Arimo-Bold.php');
    $pdf->AddFont('FontUTF8','I','Arimo-Italic.php');
    $pdf->AddFont('FontUTF8','BI','Arimo-BoldItalic.php');

    $pdf->SetAutoPageBreak(true, 1);
    $pdf->SetFooterShow(false);

    $x = $pdf->GetX();
    $y = $pdf->GetY();
    $pdf->SetX($x);
    $pdf->SetY($y);

    //$pdf->Image(BASE_URL."assets/images/logo_balai.jpg",230,15,28); 
    //$pdf->Image(BASE_URL."assets/images/logo_bet.png",249,29,17); 
    $pdf->Image(BASE_URL."assets/images/background.png", 0, 0, 300); 
    
    $pdf->SetY($y+20);
    $strWidthCol = '{300}';
    $table = new easyTable($pdf, $strWidthCol, 'align:C; paddingY:0.5;');
    $table->rowStyle('align:{C}; font-style:B;');
    $table->easyCell('KARTU TERNAK CALON BIBIT', 'align:C;'); 
    $table->printRow(true); 
    $table->endTable();

    $x = $pdf->GetX();
    $y = $pdf->GetY();
    //set the outline color
    $pdf->SetDrawColor(0);

    $pdf->SetFont('arial','',10);
    $pdf->SetLineWidth(0);

    $arrHeadParams = array(
      0 => array(
        'textLeft'    => "NAMA TERNAK",
        'valueLeft'   => "SAPI PERAH",
        'textRight'   => "",
        'valueRight'  => "",
        'ln'  => 0,
      ),
      1 => array(
        'textLeft'    => "NO. TELINGA",
        'valueLeft'   => "T00123",
        'textRight'   => "",
        'valueRight'  => "",
        'ln'  => 0,
      ),
      2 => array(
        'textLeft'    => "TANGGAL LAHIR",
        'valueLeft'   => $this->tanggal_indo("2022-08-01"),
        'textRight'   => "",
        'valueRight'  => "",
        'ln'  => 0,
      ),
      3 => array(
        'textLeft'    => "STATUS ASET",
        'valueLeft'   => "ACTIVE",
        'textRight'   => "",
        'valueRight'  => "",
        'ln'  => 0,
      ),
      4 => array(
        'textLeft'    => "ALASAN KELUAR",
        'valueLeft'   => "DIPERJUAL BELIKAN",
        'textRight'   => "",
        'valueRight'  => "",
        'ln'  => 0,
      ),
      5 => array(
        'textLeft'    => "TANGGAL KELUAR",
        'valueLeft'   => $this->tanggal_indo("2022-08-15"),
        'textRight'   => "",
        'valueRight'  => "",
        'ln'  => 2,
      ),
      6 => array(
        'textLeft'    => "SILSILAH KETUA",
        'valueLeft'   => "-",
        'textRight'   => "",
        'valueRight'  => "",
        'ln'  => 2,
      ),
      7 => array(
        'textLeft'    => "NAMA BULL",
        'valueLeft'   => "AABBCCDDEE",
        'textRight'   => "NAMA INDUK",
        'valueRight'  => "MAMAH SAPI",
        'ln'  => 0,
      ),
      8 => array(
        'textLeft'    => "KODE BULL",
        'valueLeft'   => "BB000123",
        'textRight'   => "KODE INDUK",
        'valueRight'  => "ZE00123",
        'ln'  => 0,
      ),
      9 => array(
        'textLeft'    => "ASAL",
        'valueLeft'   => "BOGOR",
        'textRight'   => "ASAL",
        'valueRight'  => "BOGOR",
        'ln'  => 0,
      ),
    );

    $strWidthCol = '{35, 43, 5, 70, 1, 40, 5, 72}';
    $table = new easyTable($pdf, $strWidthCol, 'align:C; paddingY:0.5;');
    if(!empty($arrHeadParams))
    {
      foreach($arrHeadParams AS $idx => $value){
        $table->rowStyle('align:{C, L, C, L, C, L, C, L}; font-style:B;');
        $table->easyCell('', ''); 
        if(!empty($value['textLeft'])){
          $table->easyCell($value['textLeft'], '');
          $table->easyCell(':', '');
          $table->easyCell($value['valueLeft'], '');
        }
        $table->easyCell('', '');
        if(!empty($value['textRight'])){
          $table->easyCell($value['textRight'], '');
          $table->easyCell(':', '');
          $table->easyCell($value['valueRight'], '');
        }
        $table->printRow(true); 
        $pdf->ln($value['ln']);
      }
    }
    $table->endTable();
    $pdf->ln(5);
    
    $x = $pdf->GetX();
    $y = $pdf->GetY();

    //147 KIRI ---- 1
    $pdf->SetFont('arial', '', 12);
    $strWidthCol = '{10, 70, 120}';
    $table = new easyTable($pdf, $strWidthCol, 'align:C;');
    $table->rowStyle('align:{L};');
    $table->easyCell('', 'align:L;');
    $table->easyCell('CATATAN PERTUMBUHAN', 'align:L; font-style:B;');
    $table->easyCell('', 'align:L;');
    $table->printRow(true); 
    $table->endTable();

    $pdf->SetFont('arial', '', 10);
    $strWidthCol = '{10, 35, 15, 15, 15, 15, 25, 25, 45}';
    $table = new easyTable($pdf, $strWidthCol, 'align:C; border:1;');
    $table->rowStyle('align:{CCCCCCCCC}; font-style:B;');
    $table->easyCell('NO');
    $table->easyCell('TANGGAL');
    $table->easyCell('BB');
    $table->easyCell('LD');
    $table->easyCell('PB');
    $table->easyCell('TG');
    $table->easyCell('LINGKAR SCROTUM');
    $table->easyCell('BCS');
    $table->easyCell('KETERANGAN');
    $table->printRow(true);

    for($i=1; $i<=5; $i++){
      $table->rowStyle('align:{RCCCCCCCL};');
      $table->easyCell($i.'.');
      $table->easyCell("2022-08-0".$i);
      $table->easyCell(57);
      $table->easyCell(127);
      $table->easyCell(25);
      $table->easyCell(77);
      $table->easyCell(13);
      $table->easyCell(3);
      $table->easyCell('LOREM IPSUM');
      $table->easyCell('');
      $table->printRow(true);
    }
    $table->endTable(3);
    $xL1 = $pdf->GetX();
    $yL1 = $pdf->GetY();
    //END 147 KIRI ---- 1

    $pdf->ln(5);
    $pdf->SetFont('arial', '', 12);
    $strWidthCol = '{25, 50, 85}';
    $table = new easyTable($pdf, $strWidthCol, 'align:L;');
    $table->rowStyle('align:{L};');
    $table->easyCell('', 'align:L;');
    $table->easyCell('Keterangan : ', 'align:R; font-style:IB;');
    $table->easyCell('', 'align:L;');
    $table->printRow(true); 
    $table->endTable();

    $pdf->SetFont('arial', '', 10);
    $strWidthCol = '{50, 50, 50, 50}';
    $table = new easyTable($pdf, $strWidthCol, 'align:C;');
    $table->rowStyle('align:{L}; font-style:B;');
    $table->easyCell('BB : BERAT BADAN', 'align:L;');
    $table->easyCell('LB : LINGKAR DADA', 'align:L;');
    $table->easyCell('TG : TINGGI GUMBA', 'align:L;');
    $table->easyCell('PB : PANJANG BADAN', 'align:L;');
    $table->printRow(true); 
    $table->endTable();

    $fileName = strtoupper("KARTU CALON BIBIT");
    $type     = 'I';
    $pdf->output("$fileName.pdf", $type);
	}

  public function sktb()
  {
    //error_reporting(E_ALL);
    //ini_set('display_errors', 1);

		$pdf = new exFPDF();
    $pdf->FPDF('P', 'mm', "A4");
    $pdf->SetMargins(10, 10, 10);
    $pdf->AliasNbPages();

    $pdf->SetFont('times', '', 12);
    $pdf->AddFont('FontUTF8', '', 'Arimo-Regular.php'); 
    $pdf->AddFont('FontUTF8', 'B', 'Arimo-Bold.php');
    $pdf->AddFont('FontUTF8', 'I', 'Arimo-Italic.php');
    $pdf->AddFont('FontUTF8', 'BI', 'Arimo-BoldItalic.php');
    
    $arrData = array(
      0 => array(
        "nation"              => "BRANGUS",
        "no_registration"     => "BBM01INAH107432T",
        "no_ternak"           => "142034T/1074T",
        "name"                => "BAMESWARA TESTAMENT",
        "gender"              => "JANTAN",
        "birthday"            => "2022-04-28",
        "owner"               => "BET CIPELANG",
        "address"             => "KEC. CIJERUK, KAB. BOGOR, JAWA BARAT",
        "image"               => "img1.jpg",
        "family_tree_father"  => array(
          "data1"             => "CSONKA OF BRINKS 30R4",
          "data2"             => "DMR TETSAMENT 99Y43",
          "data3"             => "MISS BRINKS GAUCHO 99H51",
        ),
        "family_tree_mother"  => array(
          "data1"             => "SUHNS NEXT STEP 331R7",
          "data2"             => "CB 12RH212 / CASTLE LOCHROSE 212",
          "data3"             => "CASTLE BRANGUS LOCHROSE",
        ),
      ),
      1 => array(
        "nation"              => "BRANGUS",
        "no_registration"     => "BBM01INAH107432T",
        "no_ternak"           => "142034T/1074T",
        "name"                => "BAMESWARA TESTAMENT",
        "gender"              => "JANTAN",
        "birthday"            => "2022-04-28",
        "owner"               => "BET CIPELANG",
        "image"               => "",
        "address"             => "KEC. CIJERUK, KAB. BOGOR, JAWA BARAT",
        "family_tree_father"  => array(
          "data1"             => "",
          "data2"             => "",
          "data3"             => "",
        ),
        "family_tree_mother"  => array(
          "data1"             => "",
          "data2"             => "",
          "data3"             => "",
        ),
      ),
    );
    
    if(!empty($arrData)){
      foreach($arrData AS $row){
        $pdf->addPage();
        $pdf->SetAutoPageBreak(false);
        $x = $pdf->GetX(); //1
        $y = $pdf->GetY(); //1
        $pdf->SetY($y);
        
        //START HEADER
        $pdf->Image(BASE_URL."assets/images/logo_balai.jpg",10,10,20); 
        $pdf->Image(BASE_URL."assets/images/logo_bet.jpg",180,10,20); 
        $pdf->Image(BASE_URL."assets/images/background_bet.jpg",25,55,160); 
  
        $strWidthCol = '{200}';
        $table = new easyTable($pdf, $strWidthCol, 'align:C; paddingY:0.5; border: 0;');
        $table->rowStyle('align:{C}; font-style:B; font-size: 14px;');
        $table->easyCell('BALAI EMBRIO TERNAK CIPELANG', 'align:C;'); 
        $table->printRow(true); 
        $table->rowStyle('align:{C}; font-style:B; font-size: 12px;');
        $table->easyCell('DIREKTORAT JENDERAL PETERNAKAN DAN KESEHATAN HEWAN', 'align:C;'); 
        $table->printRow(true);
        $table->rowStyle('align:{C}; font-style:B; font-size: 14px;');
        $table->easyCell('KEMENTERIAN PERTANIAN', 'align:C;'); 
        $table->printRow(true); 
        $pdf->ln(3);
        $table->rowStyle('align:{C}; font-style:B; font-size: 14px;');
        $table->easyCell('SURAT KETERANGAN TERNAK BIBIT', 'align:C;'); 
        $table->printRow(true); 
        $table->endTable(2);
        //END HEADER
  
        //START BODY
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $position = 117;

        $pdf->ClippingRect($x+$position,$y,72,52,true);
        if(!empty($row['image'])){
          $pdf->Image(BASE_URL."assets/images/".$row['image'], $x+1+$position,$y+1,70,50); 
        }
        $pdf->UnsetClipping();

        $pdf->SetFont('times', '', 12);
        //DATA TERNAK
        $strWidthCol = '{40, 5, 70}';
        $table = new easyTable($pdf, $strWidthCol, 'align:L; paddingY:0.5; border: 0;');
        $table->rowStyle('align:{LCL};');
        $table->easyCell('BANGSA'); 
        $table->easyCell(':'); 
        $table->easyCell($row['nation']); 
        $table->printRow(true); 
        $table->rowStyle('align:{LCL};');
        $table->easyCell('NO. REGISTRASI'); 
        $table->easyCell(':'); 
        $table->easyCell($row['no_registration']); 
        $table->printRow(true); 
        $table->rowStyle('align:{LCL};');
        $table->easyCell('NO. TERNAK'); 
        $table->easyCell(':'); 
        $table->easyCell($row['no_ternak']); 
        $table->printRow(true); 
        $table->rowStyle('align:{LCL};');
        $table->easyCell('NAMA'); 
        $table->easyCell(':'); 
        $table->easyCell($row['name']); 
        $table->printRow(true); 
        $table->rowStyle('align:{LCL};');
        $table->easyCell('JENIS KELAMIN'); 
        $table->easyCell(':'); 
        $table->easyCell($row['gender']); 
        $table->printRow(true); 
        $table->rowStyle('align:{LCL};');
        $table->easyCell('TANGGAL LAHIR'); 
        $table->easyCell(':'); 
        $table->easyCell($row['birthday']); 
        $table->printRow(true); 
        $table->rowStyle('align:{LCL};');
        $table->easyCell('PEMILIK'); 
        $table->easyCell(':'); 
        $table->easyCell($row['owner']); 
        $table->printRow(true); 
        $table->rowStyle('align:{LCL};');
        $table->easyCell('ALAMAT'); 
        $table->easyCell(':'); 
        $table->easyCell($row['address']); 
        $table->printRow(true); 
        $table->endTable();

        $arrFamFather = $row['family_tree_father'];
        $arrFamMother = $row['family_tree_mother'];

        $strWidthCol = '{30, 5, 5, 80}';
        $table = new easyTable($pdf, $strWidthCol, 'align:L; paddingY:0.5; border: 0;');
        $table->rowStyle('align:{LCCL}; font-style:B;');
        $table->easyCell('SILSILAH', 'colspan: 4;'); 
        $table->printRow(true); 
        $table->rowStyle('align:{LCCL};');
        $table->easyCell(''); 
        $table->easyCell(''); 
        $table->easyCell(''); 
        $table->easyCell($arrFamFather['data1']); 
        $table->printRow(true); 
        $table->rowStyle('align:{RCL};');
        $table->easyCell('BAPAK', 'font-style:B;'); 
        $table->easyCell(':'); 
        $table->easyCell($arrFamFather['data2'], 'colspan: 2;'); 
        $table->printRow(true); 
        $table->rowStyle('align:{LCCL};');
        $table->easyCell(''); 
        $table->easyCell(''); 
        $table->easyCell(''); 
        $table->easyCell($arrFamFather['data3']); 
        $table->printRow(true); 

        $table->rowStyle('align:{LCCL}; font-style:B;');
        $table->easyCell($row['no_ternak']." / ".$row['name'], 'colspan: 4;'); 
        $table->printRow(true); 

        $table->rowStyle('align:{LCCL};');
        $table->easyCell(''); 
        $table->easyCell(''); 
        $table->easyCell(''); 
        $table->easyCell($arrFamMother['data1']); 
        $table->printRow(true); 
        $table->rowStyle('align:{RCL};');
        $table->easyCell('INDUK', 'font-style:B;'); 
        $table->easyCell(':'); 
        $table->easyCell($arrFamMother['data2'], 'colspan: 2;'); 
        $table->printRow(true); 
        $table->rowStyle('align:{LCCL};');
        $table->easyCell(''); 
        $table->easyCell(''); 
        $table->easyCell(''); 
        $table->easyCell($arrFamMother['data3']); 
        $table->printRow(true); 
        $table->endTable();

        //START KUANTITATIF
        $strWidthCol = '{75, 15, 15, 15, 15, 15, 50}';
        $table = new easyTable($pdf, $strWidthCol, 'align:L; paddingY:0.5;');
        $table->rowStyle('align:{L}; font-style:B;');
        $table->easyCell('PERFORMAN KUANTITATIF', 'colspan: 7;'); 
        $table->printRow(true); 
        $table->rowStyle('align:{CCCCCCC}; border: 1; font-style:B;');
        $table->easyCell('UMUR', 'rowspan: 2;'); 
        $table->easyCell('PARAMETER', 'colspan: 5'); 
        $table->easyCell('KETERANGAN', 'rowspan: 2;'); 
        $table->printRow(true); 
        $table->rowStyle('align:{CCCCC}; border: 1; font-style:B;');
        $table->easyCell('BB (kg)'); 
        $table->easyCell('LD (cm)'); 
        $table->easyCell('PB (cm)'); 
        $table->easyCell('TP (cm)'); 
        $table->easyCell('LS (cm)'); 
        $table->printRow(true); 

        $arrDataKuantitatif = array(
          0 => array(
            'AGE'       => "LAHIR",
            'BB'        => 41.0,
            'LD'        => 74,
            'PB'        => 68,
            'TP'        => 72,
            'LS'        => "-",
            'NOTE'      => "",
          ),
          1 => array(
            'AGE'       => "205 HARI",
            'BB'        => 181.8,
            'LD'        => 114,
            'PB'        => 91,
            'TP'        => 91,
            'LS'        => "-",
            'NOTE'      => "",
          ),
          2 => array(
            'AGE'       => "12 BULAN (365 HARI)",
            'BB'        => 291.3,
            'LD'        => 125,
            'PB'        => 106,
            'TP'        => 101,
            'LS'        => 14,
            'NOTE'      => "",
          ),
          3 => array(
            'AGE'       => "18 BULAN (550 HARI)",
            'BB'        => 382.5,
            'LD'        => 168,
            'PB'        => 128,
            'TP'        => 121,
            'LS'        => 30,
            'NOTE'      => "",
          ),
        );

        if(!empty($arrDataKuantitatif)){
          foreach($arrDataKuantitatif AS $rowKuantitatif){
            $table->rowStyle('align:{LCCCCCL}; border: 1;');
            $table->easyCell($rowKuantitatif['AGE']); 
            $table->easyCell($rowKuantitatif['BB']); 
            $table->easyCell($rowKuantitatif['LD']); 
            $table->easyCell($rowKuantitatif['PB']); 
            $table->easyCell($rowKuantitatif['TP']); 
            $table->easyCell($rowKuantitatif['LS']); 
            $table->easyCell($rowKuantitatif['NOTE']); 
            $table->printRow(true); 
          }
        }
        else{
          $table->rowStyle('align:{LCCCCCL}; border: 1;');
          $table->easyCell(''); 
          $table->easyCell(''); 
          $table->easyCell(''); 
          $table->easyCell(''); 
          $table->easyCell(''); 
          $table->easyCell(''); 
          $table->easyCell(''); 
          $table->printRow(true); 
        }
        $table->rowStyle('align:{L}; font-size: 10px;');
        $table->easyCell('Keterangan : BB = Bobot Badan; LD = Lingkar Dada; PB = Panjang Badan; TP = Tinggi Pundah; LS = Lingkar Scrotum ', 'colspan: 7;'); 
        $table->printRow(true); 
        $table->endTable();
        //END KUANTITATIF

        //START KUALITATIF
        $strWidthCol = '{22, 22, 22, 22, 22, 22, 22, 40}';
        $table = new easyTable($pdf, $strWidthCol, 'align:L; paddingY:0.5;');
        $table->rowStyle('align:{L}; font-style:B;');
        $table->easyCell('PERFORMAN KUALITATIF', 'colspan: 8;'); 
        $table->printRow(true); 
        $table->rowStyle('align:{CCCCCCCC}; border: 1; font-style:B;');
        $table->easyCell('PARAMETER', 'colspan: 7;'); 
        $table->easyCell('CIRI KHAS BANGSA', 'rowspan: 2;'); 
        $table->printRow(true); 
        $table->rowStyle('align:{CCCCCCC}; border: 1; font-style:B; valign: M;');
        $table->easyCell('WARNA BULU'); 
        $table->easyCell('TANDUK'); 
        $table->easyCell('WARNA MONCONG'); 
        $table->easyCell('PUNUK'); 
        $table->easyCell('WARNA RAMBUT EKOR'); 
        $table->easyCell('WARNA MATA'); 
        $table->easyCell('BENTUK TELINGA'); 
        $table->printRow(true); 

        $arrDataKualitatif = array(
          0 => array(
            "data1"   => "Hitam",
            "data2"   => "Bertanduk/Dehomed",
            "data3"   => "Hitam",
            "data4"   => "Berpunuk Kecil",
            "data5"   => "Hitam",
            "data6"   => "Cerah",
            "data7"   => "Besar, tegak kesamping",
            "data8"   => "Warna hitam pada seluruh bagian tubuh, bergelambir",
          )
        );

        if(!empty($arrDataKualitatif)){
          foreach($arrDataKualitatif AS $rowKualitatif){
            $table->rowStyle('align:{LLLLLLLL}; border: 1;');
            $table->easyCell($rowKualitatif['data1']);  
            $table->easyCell($rowKualitatif['data2']); 
            $table->easyCell($rowKualitatif['data3']); 
            $table->easyCell($rowKualitatif['data4']); 
            $table->easyCell($rowKualitatif['data5']); 
            $table->easyCell($rowKualitatif['data6']); 
            $table->easyCell($rowKualitatif['data7']); 
            $table->easyCell($rowKualitatif['data8']); 
            $table->printRow(true);
          }
        }
        else{
          $table->rowStyle('align:{LLLLLLLL}; border: 1;');
          $table->easyCell(''); 
          $table->easyCell(''); 
          $table->easyCell(''); 
          $table->easyCell(''); 
          $table->easyCell(''); 
          $table->easyCell(''); 
          $table->easyCell(''); 
          $table->easyCell(''); 
          $table->printRow(true);
        }
        $table->endTable(2);
        //END KUALITATIF

        //START DATE AND TTD
        $strWidthCol = '{120, 80}';
        $table = new easyTable($pdf, $strWidthCol, 'align:R; paddingY:0.5; border: 0;');
        $table->rowStyle('align:{CL};');
        $table->easyCell(''); 
        $table->easyCell('Bogor, '.$this->tanggal_indo(date('Y-m-d'))); 
        $table->printRow(true); 
        $table->rowStyle('align:{CL};');
        $table->easyCell(''); 
        $table->easyCell('Kepala Balai Embrio Ternak'); 
        $table->printRow(true); 
        $pdf->ln(15);
        $table->rowStyle('align:{CL};');
        $table->easyCell(''); 
        $table->easyCell('Drh. Oloan Parlingungan, MP.'); 
        $table->printRow(true); 
        $table->rowStyle('align:{CL};');
        $table->easyCell(''); 
        $table->easyCell('NIP. 196411261992031001'); 
        $table->printRow(true); 
        $table->endTable(3);
        //END DATE AND TTD
        //END BODY
      }
      
      $fileName = strtoupper("SURAT KETERANGAN TERNAK BIBIT");
      $type     = 'I';
      $pdf->output("$fileName.pdf", $type);
    }
  }

  function tanggal_indo($tanggal, $cetak_hari = false)
  {
    $hari = array ( 1 =>    'Senin',
      'Selasa',
      'Rabu',
      'Kamis',
      'Jumat',
      'Sabtu',
      'Minggu'
    );
        
    $bulan = array (1 =>   'Januari',
      'Februari',
      'Maret',
      'April',
      'Mei',
      'Juni',
      'Juli',
      'Agustus',
      'September',
      'Oktober',
      'November',
      'Desember'
    );
    $split 	  = explode('-', $tanggal);
    $tgl_indo = $split[2] . ' ' . $bulan[ (int)$split[1] ] . ' ' . $split[0];
    
    if ($cetak_hari) {
      $num = date('N', strtotime($tanggal));
      return $hari[$num] . ', ' . $tgl_indo;
    }
    return $tgl_indo;
  }
}
