<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * This file keeps track of upgrades to the plagiarism module
 *
 * Sometimes, changes between versions involve alterations to database
 * structures and other major things that may break installations. The upgrade
 * function in this file will attempt to perform all the necessary actions to
 * upgrade your older installation to the current version. If there's something
 * it cannot do itself, it will tell you what you need to do.  The commands in
 * here will all be database-neutral, using the functions defined in DLL libraries.
 *
 * @package    mod
 * @subpackage plagiarism
 * @copyright  2011 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Execute plagiarism upgrade from the given old version
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_local_plagiarism_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager(); // loads ddl manager and xmldb classes

    // And upgrade begins here. For each one, you'll need one
    // block of code similar to the next one. Please, delete
    // this comment lines once this file start handling proper
    // upgrade code.

    // if ($oldversion < YYYYMMDD00) { //New version in version.php
    //
    // }

    // Lines below (this included)  MUST BE DELETED once you get the first version
    // of your module ready to be installed. They are here only
    // for demonstrative purposes and to show how the plagiarism
    // iself has been upgraded.

    // For each upgrade block, the file plagiarism/version.php
    // needs to be updated . Such change allows Moodle to know
    // that this file has to be processed.

    // To know more about how to write correct DB upgrade scripts it's
    // highly recommended to read information available at:
    //   http://docs.moodle.org/en/Development:XMLDB_Documentation
    // and to play with the XMLDB Editor (in the admin menu) and its
    // PHP generation posibilities.

    // First example, some fields were added to install.xml on 2007/04/01
    

    if ($oldversion < 2012022402) {

        // Changing type of field wordno on table wn_synonyms to int
        $table = new xmldb_table('wn_synonyms');
        $field = new xmldb_field('wordno', XMLDB_TYPE_INTEGER, '6', null, XMLDB_NOTNULL, null, null, 'id');

        // Launch change of type for field wordno
        $dbman->change_field_type($table, $field);
        
        
        // Define table plagiarism_settings to be renamed to NEWNAMEGOESHERE
        $table = new xmldb_table('set_plagiarism');

        // Launch rename table for plagiarism_settings
        $dbman->rename_table($table, 'plagiarism_settings');
        
        
        // Define table plagiarism_settings to be renamed to NEWNAMEGOESHERE
        $table = new xmldb_table('wn_synonyms');

        // Launch rename table for plagiarism_settings
        $dbman->rename_table($table, 'plagiarism_wn_synonyms');
        
        // Define index wordno_index (not unique) to be added to plagiarism_wn_synonyms
        $table = new xmldb_table('plagiarism_wn_synonyms');
        $index = new xmldb_index('wordno_index', XMLDB_INDEX_NOTUNIQUE, array('wordno'));

        // Conditionally launch add index wordno_index
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }                
        
        $table = new xmldb_table('plagiarism_wn_synonyms');
        $index = new xmldb_index('lemma_index', XMLDB_INDEX_NOTUNIQUE, array('lemma'));

        // Conditionally launch add index lemma_index
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }        

        // plagiarism savepoint reached
        upgrade_plugin_savepoint(true, 2012022402, 'local', 'plagiarism');
    }


		return true;

}

