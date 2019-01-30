<?php
/*
 * studip_seminar_info.php - base class for seminars
 *
 * Copyright (C) 2006 - Marco Diedrich (mdiedric@uos.de)
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
  * 
 */

class Studip_Date extends Studip_Ws_Struct
{
    function init() {
        Studip_Date::add_element('id', 'integer');
        Studip_Date::add_element('begin_time', 'string');
        Studip_Date::add_element('end_time', 'string');
        Studip_Date::add_element('description', 'string'); 
        Studip_Date::add_element('location', 'string'); 
        Studip_Date::add_element('lecturers', array('Studip_User'));
        
    }
}


