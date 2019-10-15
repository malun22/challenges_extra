<?php
 /*********************************************************\
 *                 challenges_extra Plugin                 *
 ***********************************************************
 *                        Features                         *
 * - Adds a challenges_extra table to SQL database, so you *
 *   can add further columns to it without editing the     *
 *   normal challenges table, which may cause some issues  *
 * - It only stores the map Uid which you can get by using *
 *   $uid = $aseco->server->challenge->uid;                *
 * - How to add a column is specified further down         *
 ***********************************************************
 *                   Created by Malun                      *
 ***********************************************************
 *                   Dependencies: none                    *
 ***********************************************************
 *                         License                         *
 * LICENSE: This program is free software: you can         *
 * redistribute it and/or modify it under the terms of the *
 * GNU General Public License as published by the Free     *
 * Software Foundation, either version 3 of the License,   *
 * or (at your option) any later version.                  *
 *                                                         *
 * This program is distributed in the hope that it will be *
 * useful, but WITHOUT ANY WARRANTY; without even the      *
 * implied warranty of MERCHANTABILITY or FITNESS FOR A    *
 * PARTICULAR PURPOSE.  See the GNU General Public License *
 * for more details.                                       *
 *                                                         *
 * You should have received a copy of the GNU General      *
 * Public License along with this program.  If not,        *
 * see <http://www.gnu.org/licenses/>.                     *
 ***********************************************************
 *                       Installation                      *
 * - Put this plugin in /Controllers/XASECO/plugins        *
 * - activate the plugin in                                *
 *   /TMF04445/Controllers/XASECO/plugins.xml              *
 * - Better put it above plugins which use the db          *
 \*********************************************************/
 
 // Adding a new column to challenges_extra works like this:
/*	$result = mysql_query('SHOW COLUMNS FROM `challenges_extra`;');
	
	if ($result) {
		while ($row = mysql_fetch_row($result)) {
			$fields[] = $row[0];
		}
		mysql_free_result($result);
	}
	
	// Add `column_name` column to `challenges_extra` table if not yet done
	if ( !in_array('column_name', $fields) ) {
			$aseco->console('   + Adding column `column_name` at table `challenges_extra`.');
			mysql_query('ALTER TABLE challenges_extra ADD column_name tinyint(1) DEFAULT 0 COMMENT "Added by plugin.name.php"');
	} else { $aseco->console('   + Found column `column_name` at table `challenges_extra`.'); } */

Aseco::registerEvent('onSync', 'ldce_init');
Aseco::registerEvent('onNewChallenge', 'ldce_onNewChallenge');

function ldce_init($aseco) {
	$aseco->console('**********[plugin.challenges_extra.php'. $aseco->server->getGame() .']**********');
	$aseco->console('>> Checking Database for required extensions...');
	
	// Create challenges_extra table if not exist already
	mysql_query('CREATE TABLE IF NOT EXISTS `challenges_extra` (
		  `Id` int(11) NOT NULL auto_increment,
		  `ChallengeUid` varchar(27) NOT NULL default 0,
		  PRIMARY KEY (`Id`),
		  UNIQUE KEY (`ChallengeUid`)
		) ENGINE=MyISAM;');
	
	// Check if table is empty
	$result = mysql_query('SELECT Table_Rows FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = "challenges_extra"');
	while ($dbrow = mysql_fetch_array($result)) {
		$amountrows = $dbrow[0];
	}
	mysql_free_result($result);
	
	if ($amountrows == '0') {
		$aseco->console('   + Table challenges_extra got created.');
		// Copy all existing Uid's to challenges_extra
		$result = mysql_query("SELECT Uid FROM challenges");
		
		while ($dbrow = mysql_fetch_array($result)) {
			mysql_query('INSERT INTO challenges_extra(ChallengeUid) VALUES ('. quotedString($dbrow[0]) .')');
		}
		mysql_free_result($result);
		$aseco->console('   + All Uid\'s from table challenges got copied to challenges_extra.');
	} else {
		$aseco->console('   + Table challenges_extra exists already.');
	}
}

function ldce_onNewChallenge($aseco) {
	// Check if Uid is already in challenges_extra
	
	$uid = $aseco->server->challenge->uid;
	
	$result = mysql_query('SELECT * FROM challenges_extra WHERE ChallengeUid = '. quotedString($uid) .' LIMIT 1;');
	
	if (mysql_num_rows($result) == 0) {
		// Map is not yet in db
		mysql_free_result($result);
		mysql_query('INSERT INTO challenges_extra(ChallengeId) VALUES ('. quotedString($uid) .')');
		$aseco->console('>> [plugin.challenges_extra.php] '. $aseco->server->challenge->name .' (uid = '. $uid .') got added to the table challenges_extra.');
	}
}
?>