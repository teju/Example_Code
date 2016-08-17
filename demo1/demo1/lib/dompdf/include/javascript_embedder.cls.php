<?php
/**
 * @package dompdf
 * @link    http://www.dompdf.com/
 * @author  Fabien Ménager <fabien.menager@gmail.com>
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 * @version $Id: javascript_embedder.cls.php,v 1.1.1.1 2012/09/12 04:05:14 avinash Exp $
 */

/**
 * Embeds Javascript into the PDF document
 *
 * @access private
 * @package dompdf
 */
class Javascript_Embedder {
  
  /**
   * @var DOMPDF
   */
  protected $_dompdf;

  function __construct(DOMPDF $dompdf) {
    $this->_dompdf = $dompdf;
  }

  function insert($code) {
    $this->_dompdf->get_canvas()->javascript($code);
  }

  function render($frame) {
    if ( !DOMPDF_ENABLE_JAVASCRIPT )
      return;
      
    $this->insert($frame->get_node()->nodeValue);
  }
}
