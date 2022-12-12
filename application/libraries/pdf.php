<?php
class pdf {
  function __construct() {
    include_once APPPATH . '/third_party/fpdf/fpdf_protection.php';
    include_once APPPATH . '/third_party/fpdf/exfpdf.php';
    include_once APPPATH . '/third_party/fpdf/easyTable.php';
  }
}
?>