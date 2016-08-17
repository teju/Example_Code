<?php
/**
 * @package dompdf
 * @link    http://www.dompdf.com/
 * @author  Benj Carson <benjcarson@digitaljunkies.ca>
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 * @version $Id: table_cell_positioner.cls.php,v 1.1.1.1 2012/09/12 04:05:14 avinash Exp $
 */

/**
 * Positions table cells
 *
 * @access private
 * @package dompdf
 */
class Table_Cell_Positioner extends Positioner {

  function __construct(Frame_Decorator $frame) { parent::__construct($frame); }
  
  //........................................................................

  function position() {

    $table = Table_Frame_Decorator::find_parent_table($this->_frame);
    $cellmap = $table->get_cellmap();
    $this->_frame->set_position($cellmap->get_frame_position($this->_frame));

  }
}
