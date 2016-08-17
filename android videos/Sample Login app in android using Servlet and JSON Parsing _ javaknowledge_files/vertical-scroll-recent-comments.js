/**
 *     Vertical scroll recent comments
 *     Copyright (C) 2011 - 2014 www.gopiplus.com
 *     http://www.gopiplus.com/work/2010/07/18/vertical-scroll-recent-comments/
 * 
 *     This program is free software: you can redistribute it and/or modify
 *     it under the terms of the GNU General Public License as published by
 *     the Free Software Foundation, either version 3 of the License, or
 *     (at your option) any later version.
 * 
 *     This program is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *     GNU General Public License for more details.
 * 
 *     You should have received a copy of the GNU General Public License
 *     along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */	
 
function vsrc_scroll() {
	vsrc_obj.scrollTop = vsrc_obj.scrollTop + 1;
	vsrc_scrollPos++;
	if ((vsrc_scrollPos%vsrc_heightOfElm) == 0) {
		vsrc_numScrolls--;
		if (vsrc_numScrolls == 0) {
			vsrc_obj.scrollTop = '0';
			vsrc_content();
		} else {
			if (vsrc_scrollOn == 'true') {
				vsrc_content();
			}
		}
	} else {
		var speed = 60 - ( vsrc_speed * 10 );
		setTimeout("vsrc_scroll();", speed);
	}
}

var vsrc_Num = 0;
/*
Creates amount to show + 1 for the scrolling ability to work
scrollTop is set to top position after each creation
Otherwise the scrolling cannot happen
*/
function vsrc_content() {
	var tmp_vsrc = '';

	w_vsrc = vsrc_Num - parseInt(vsrc_numberOfElm);
	if (w_vsrc < 0) {
		w_vsrc = 0;
	} else {
		w_vsrc = w_vsrc%vsrc_array.length;
	}
	
	// Show amount of vsrru
	var elementsTmp_vsrc = parseInt(vsrc_numberOfElm) + 1;
	for (i_vsrc = 0; i_vsrc < elementsTmp_vsrc; i_vsrc++) {
		
		tmp_vsrc += vsrc_array[w_vsrc%vsrc_array.length];
		w_vsrc++;
	}

	vsrc_obj.innerHTML 	= tmp_vsrc;
	
	vsrc_Num 			= w_vsrc;
	vsrc_numScrolls 	= vsrc_array.length;
	vsrc_obj.scrollTop 	= '0';
	// start scrolling
	setTimeout("vsrc_scroll();", vsrc_waitseconds * 2000);
}