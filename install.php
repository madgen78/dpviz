<?php

if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed'); }
//	License for all code of this FreePBX module can be found in the license file inside the module directory
//	Copyright 2006-2014 Schmooze Com Inc.
//
global $db;
global $amp_conf;

$autoincrement = (($amp_conf["AMPDBENGINE"] == "sqlite") || ($amp_conf["AMPDBENGINE"] == "sqlite3")) ? "AUTOINCREMENT":"AUTO_INCREMENT";

// Persistent install identity table.
// Keep this outside normal module KV so install_uuid survives remove/uninstall + reinstall.
$sql = "SHOW TABLES LIKE 'dpviz_persist'";
$exists = $db->getOne($sql);

if (empty($exists)) {
    $sql = "CREATE TABLE dpviz_persist (
        id INTEGER NOT NULL PRIMARY KEY,
        install_uuid VARCHAR(36) NOT NULL
    )";
    $check = $db->query($sql);
    if (DB::IsError($check)) {
        die_freepbx("Can not create dpviz_persist table");
    }
}

// Check if table exists first
$sql = "SHOW TABLES LIKE 'dpviz'";
$exists = $db->getOne($sql);
$table_created = false;

if (empty($exists)) {
    // Table doesn't exist, so create it
    $sql = "CREATE TABLE dpviz (
        id INTEGER NOT NULL PRIMARY KEY $autoincrement,
        datetime TINYINT(1) NOT NULL DEFAULT 1,
        horizontal TINYINT(1) NOT NULL DEFAULT 0,
        panzoom TINYINT(1) NOT NULL DEFAULT 1,
        dynmembers TINYINT(1) NOT NULL DEFAULT 0,
        combineQueueRing TINYINT(1) NOT NULL DEFAULT 0,
				extOptional TINYINT(1) NOT NULL DEFAULT 1,
        fmfm TINYINT(1) NOT NULL DEFAULT 1
    )";
    $check = $db->query($sql);
    if (DB::IsError($check)) {
        die_freepbx("Can not create dpviz table");
    }

    $table_created = true;
}

// Insert default row if the table was just created
if ($table_created) {
    $sql = "INSERT INTO dpviz (datetime, horizontal, panzoom, dynmembers, combineQueueRing, extOptional, fmfm) VALUES (1, 0, 1, 0, 0, 1, 1)";
    $check = $db->query($sql);
    if (DB::IsError($check)) {
        die_freepbx("Failed to insert initial row");
    }
}


// Version 0.22 adds minimal view
$sql = "SELECT minimal FROM dpviz";
$check = $db->getRow($sql, DB_FETCHMODE_ASSOC);
if(DB::IsError($check)) {
	// add new field
    $sql = "ALTER TABLE dpviz ADD minimal TINYINT(1) NOT NULL DEFAULT 0;";
    $result = $db->query($sql);
    if(DB::IsError($result)) { die_freepbx($result->getDebugInfo()); }
		
		// Only update row if the column was just added
    $sql = "UPDATE dpviz SET minimal = 0 WHERE id = 1;";
    $result = $db->query($sql);
    if(DB::IsError($result)) { die_freepbx($result->getDebugInfo()); }
}

// Version 0.23 adds queue_member_display
$sql = "SELECT queue_member_display FROM dpviz";
$check = $db->getRow($sql, DB_FETCHMODE_ASSOC);
if(DB::IsError($check)) {
	// add new field
    $sql = "ALTER TABLE dpviz ADD queue_member_display TINYINT(1) NOT NULL DEFAULT 2;";
    $result = $db->query($sql);
    if(DB::IsError($result)) { die_freepbx($result->getDebugInfo()); }
		
		// Only update row if the column was just added
    $sql = "UPDATE dpviz SET queue_member_display = 2 WHERE id = 1;";
    $result = $db->query($sql);
    if(DB::IsError($result)) { die_freepbx($result->getDebugInfo()); }
}

// Version 0.23 adds ring_member_display
$sql = "SELECT ring_member_display FROM dpviz";
$check = $db->getRow($sql, DB_FETCHMODE_ASSOC);
if(DB::IsError($check)) {
	// add new field
    $sql = "ALTER TABLE dpviz ADD ring_member_display TINYINT(1) NOT NULL DEFAULT 2;";
    $result = $db->query($sql);
    if(DB::IsError($result)) { die_freepbx($result->getDebugInfo()); }
		
		// Only update row if the column was just added
    $sql = "UPDATE dpviz SET ring_member_display = 2 WHERE id = 1;";
    $result = $db->query($sql);
    if(DB::IsError($result)) { die_freepbx($result->getDebugInfo()); }
}

// Version 0.24 adds queue_penalty
$sql = "SELECT queue_penalty FROM dpviz";
$check = $db->getRow($sql, DB_FETCHMODE_ASSOC);
if(DB::IsError($check)) {
	// add new field
    $sql = "ALTER TABLE dpviz ADD queue_penalty TINYINT(1) NOT NULL DEFAULT 0;";
    $result = $db->query($sql);
    if(DB::IsError($result)) { die_freepbx($result->getDebugInfo()); }
		
		// Only update row if the column was just added
    $sql = "UPDATE dpviz SET queue_penalty = 0 WHERE id = 1;";
    $result = $db->query($sql);
    if(DB::IsError($result)) { die_freepbx($result->getDebugInfo()); }
}

// Version 0.27 adds allowlist, blacklist, autoplay
$sql = "SELECT allowlist FROM dpviz";
$check = $db->getRow($sql, DB_FETCHMODE_ASSOC);
if(DB::IsError($check)) {
	// add new field
    $sql = "ALTER TABLE dpviz ADD allowlist TINYINT(1) NOT NULL DEFAULT 0;";
    $result = $db->query($sql);
    if(DB::IsError($result)) { die_freepbx($result->getDebugInfo()); }
		
		// Only update row if the column was just added
    $sql = "UPDATE dpviz SET allowlist = 0 WHERE id = 1;";
    $result = $db->query($sql);
    if(DB::IsError($result)) { die_freepbx($result->getDebugInfo()); }
}

$sql = "SELECT blacklist FROM dpviz";
$check = $db->getRow($sql, DB_FETCHMODE_ASSOC);
if(DB::IsError($check)) {
	// add new field
    $sql = "ALTER TABLE dpviz ADD blacklist TINYINT(1) NOT NULL DEFAULT 0;";
    $result = $db->query($sql);
    if(DB::IsError($result)) { die_freepbx($result->getDebugInfo()); }
		
		// Only update row if the column was just added
    $sql = "UPDATE dpviz SET blacklist = 0 WHERE id = 1;";
    $result = $db->query($sql);
    if(DB::IsError($result)) { die_freepbx($result->getDebugInfo()); }
}

$sql = "SELECT autoplay FROM dpviz";
$check = $db->getRow($sql, DB_FETCHMODE_ASSOC);
if(DB::IsError($check)) {
	// add new field
    $sql = "ALTER TABLE dpviz ADD autoplay TINYINT(1) NOT NULL DEFAULT 0;";
    $result = $db->query($sql);
    if(DB::IsError($result)) { die_freepbx($result->getDebugInfo()); }
		
		// Only update row if the column was just added
    $sql = "UPDATE dpviz SET autoplay = 0 WHERE id = 1;";
    $result = $db->query($sql);
    if(DB::IsError($result)) { die_freepbx($result->getDebugInfo()); }
}

// Version 0.28 adds displaydestinations
$sql = "SELECT displaydestinations FROM dpviz";
$check = $db->getRow($sql, DB_FETCHMODE_ASSOC);

if(DB::IsError($check)) {
    // Add new field
    $sql = "ALTER TABLE dpviz ADD displaydestinations TINYINT(1) NOT NULL DEFAULT 0;";
    $result = $db->query($sql);
    if(DB::IsError($result)) { die_freepbx($result->getDebugInfo()); }

    // Count total incoming routes
    $sql = "SELECT COUNT(*) AS total FROM incoming";
    $countResult = $db->getRow($sql, DB_FETCHMODE_ASSOC);
    if(DB::IsError($countResult)) { die_freepbx($countResult->getDebugInfo()); }

    $total = isset($countResult['total']) ? (int)$countResult['total'] : 0;

    // Set default based on number of routes
    $defaultValue = ($total > 100) ? 0 : 1;

    $sql = "UPDATE dpviz SET displaydestinations = " . q($defaultValue) . " WHERE id = 1;";
    $result = $db->query($sql);
    if(DB::IsError($result)) { die_freepbx($result->getDebugInfo()); }
}

// Version 0.30 adds inuseby
$sql = "SELECT inuseby FROM dpviz";
$check = $db->getRow($sql, DB_FETCHMODE_ASSOC);
if(DB::IsError($check)) {
	// add new field
    $sql = "ALTER TABLE dpviz ADD inuseby TINYINT(1) NOT NULL DEFAULT 0;";
    $result = $db->query($sql);
    if(DB::IsError($result)) { die_freepbx($result->getDebugInfo()); }
		
		// Only update row if the column was just added
    $sql = "UPDATE dpviz SET inuseby = 0 WHERE id = 1;";
    $result = $db->query($sql);
    if(DB::IsError($result)) { die_freepbx($result->getDebugInfo()); }
}

// Version 0.32 adds insertnode and custom_datetime
$sql = "SELECT insertnode FROM dpviz";
$check = $db->getRow($sql, DB_FETCHMODE_ASSOC);
if(DB::IsError($check)) {
	// add new field
    $sql = "ALTER TABLE dpviz ADD insertnode TINYINT(1) NOT NULL DEFAULT 0;";
    $result = $db->query($sql);
    if(DB::IsError($result)) { die_freepbx($result->getDebugInfo()); }
		
		// Only update row if the column was just added
    $sql = "UPDATE dpviz SET insertnode = 0 WHERE id = 1;";
    $result = $db->query($sql);
    if(DB::IsError($result)) { die_freepbx($result->getDebugInfo()); }
}

$sql = "SELECT custom_datetime FROM dpviz";
$check = $db->getRow($sql, DB_FETCHMODE_ASSOC);
if(DB::IsError($check)) {
	// add new field
    $sql = "ALTER TABLE dpviz ADD custom_datetime VARCHAR(20) DEFAULT NULL;";
    $result = $db->query($sql);
    if(DB::IsError($result)) { die_freepbx($result->getDebugInfo()); }
		
		// Only update row if the column was just added
    $sql = "UPDATE dpviz SET custom_datetime = NULL WHERE id = 1;";
    $result = $db->query($sql);
    if(DB::IsError($result)) { die_freepbx($result->getDebugInfo()); }
}

// Check if table exists first
$sql = "SHOW TABLES LIKE 'dpviz_views'";
$exists = $db->getOne($sql);
$table_created = false;

if (empty($exists)) {
    // Table doesn't exist, so create it
    $sql = "CREATE TABLE dpviz_views (
        id INTEGER NOT NULL PRIMARY KEY $autoincrement,
        description VARCHAR(50) NULL,
        ext VARCHAR(50) NULL,
        jump VARCHAR(50) NULL,
        skip VARCHAR(255)
    )";
    $check = $db->query($sql);
    if (DB::IsError($check)) {
        die_freepbx("Can not create dpviz_views table");
    }

    $table_created = true;
}


//gpg key import if framework >= 17
$fpr = "AEC3F282013E200B0E35A6D058E80D46FED5C0E3"; // full fingerprint

if (function_exists('get_framework_version') && version_compare(get_framework_version(), '17', '>=')) {

    $localKeyFile = __DIR__ . "/publickey.asc";
    if (file_exists($localKeyFile)) {

        $user = trim(shell_exec('whoami'));

        // ---- Key Import ----
        $cmdImport = "gpg --batch --import " . escapeshellarg($localKeyFile);
        if ($user !== 'asterisk') {
            $cmdImport = "sudo -u asterisk " . $cmdImport;
        }
        exec($cmdImport . " 2>&1", $out1, $rc1);

        // ---- Ownertrust Import ----
        $ownertrust = $fpr . ":6:\n";  // 6 = ultimate
        $cmdTrust   = "gpg --batch --import-ownertrust";
        if ($user !== 'asterisk') {
            $cmdTrust = "sudo -u asterisk " . $cmdTrust;
        }

        $descriptors = [
            0 => ["pipe","r"],
            1 => ["pipe","w"],
            2 => ["pipe","w"]
        ];
        $proc = proc_open($cmdTrust, $descriptors, $pipes);
        if (is_resource($proc)) {
            fwrite($pipes[0], $ownertrust);
            fclose($pipes[0]);
            $out2 = stream_get_contents($pipes[1]); fclose($pipes[1]);
            $err2 = stream_get_contents($pipes[2]); fclose($pipes[2]);
            $rc2  = proc_close($proc);
        } else {
            $out2 = "";
            $err2 = "proc_open failed";
            $rc2  = 1;
        }

        // ---- Optional logging ----
        if (function_exists('out')) {
            // out("Key import rc=$rc1"); foreach ($out1 as $l) out("  ".$l);
            // out("Ownertrust rc=$rc2"); if ($out2) out("  ".$out2); if ($err2) out("  ".$err2);
        }

    } else {
        if (function_exists('out')) {
            out("Public key file not found: $localKeyFile");
        }
    }
}


// Version 1.0.33 adds exportprefix
$sql = "SELECT exportprefix FROM dpviz";
$check = $db->getRow($sql, DB_FETCHMODE_ASSOC);
if(DB::IsError($check)) {
    $sql = "ALTER TABLE dpviz ADD exportprefix VARCHAR(60) DEFAULT ''";
    $result = $db->query($sql);
    if(DB::IsError($result)) { die_freepbx($result->getDebugInfo()); }

    $sql = "UPDATE dpviz SET exportprefix = '' WHERE id = 1;";
    $result = $db->query($sql);
    if(DB::IsError($result)) { die_freepbx($result->getDebugInfo()); }
}

// Version 1.0.33 adds hidewhatsnew and resets it on install/upgrade
$sql = "SELECT hidewhatsnew FROM dpviz";
$check = $db->getRow($sql, DB_FETCHMODE_ASSOC);
if(DB::IsError($check)) {
    $sql = "ALTER TABLE dpviz ADD hidewhatsnew TINYINT(1) NOT NULL DEFAULT 0";
    $result = $db->query($sql);
    if(DB::IsError($result)) { die_freepbx($result->getDebugInfo()); }
}

$sql = "UPDATE dpviz SET hidewhatsnew = 0 WHERE id = 1;";
$result = $db->query($sql);
if(DB::IsError($result)) { die_freepbx($result->getDebugInfo()); }

// Migrate legacy module-KV install_uuid into the persistent table once.
$sql = "SELECT install_uuid FROM dpviz_persist WHERE id = 1";
$persistedUuid = $db->getOne($sql);
if (DB::IsError($persistedUuid)) {
    $persistedUuid = '';
}

if (empty($persistedUuid)) {
    $legacyUuid = '';
    $sql = "SHOW TABLES LIKE 'kvstore_FreePBX_modules_Dpviz'";
    $kvExists = $db->getOne($sql);
    if (!DB::IsError($kvExists) && !empty($kvExists)) {
        $sql = "SELECT val FROM kvstore_FreePBX_modules_Dpviz WHERE id = 'noid' AND `key` = 'install_uuid'";
        $legacyUuid = $db->getOne($sql);
        if (DB::IsError($legacyUuid)) {
            $legacyUuid = '';
        }
    }

    if (!empty($legacyUuid)) {
        $sql = "REPLACE INTO dpviz_persist (id, install_uuid) VALUES (1, " . $db->quoteSmart($legacyUuid) . ")";
        $check = $db->query($sql);
        if (DB::IsError($check)) {
            die_freepbx("Failed to migrate install_uuid into dpviz_persist");
        }
    }
}


try {
    $dpviz = \FreePBX::Dpviz();
    if ($dpviz) {
        $dpviz->restoreSettingsRowFromKv();
        $dpviz->syncSettingsRowToKv();
    }
} catch (\Exception $e) {
    if (function_exists('out')) {
        out('dpviz KV restore skipped: ' . $e->getMessage());
    }
}
