<?php
/**
 * @package dompdf
 * @link    http://www.dompdf.com/
 * @author  Benj Carson <benjcarson@digitaljunkies.ca>
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 * @version $Id: null_positioner.cls.php,v 1.1.1.1 2012/09/12 04:05:14 avinash Exp $
 */

/**
 * Dummy positioner
 *
 * @access private
 * @package dompdf
 */
class Null_Positioner extends Positioner {

  function __construct(Frame_Decorator $frame) {
    parent::__construct($frame);
  }

  function position() { return; }
  
}
