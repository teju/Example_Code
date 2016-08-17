<?php
/**
 * @package dompdf
 * @link    http://www.dompdf.com/
 * @author  Benj Carson <benjcarson@digitaljunkies.ca>
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 * @version $Id: dompdf_exception.cls.php,v 1.1.1.1 2012/09/12 04:05:14 avinash Exp $
 */

/**
 * Standard exception thrown by DOMPDF classes
 *
 * @package dompdf
 */
class DOMPDF_Exception extends Exception {

  /**
   * Class constructor
   *
   * @param string $message Error message
   * @param int $code Error code
   */
  function __construct($message = null, $code = 0) {
    parent::__construct($message, $code);
  }

}
