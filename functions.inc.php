<?php
    if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed'); }

    // This function is deprecated and will be removed in a future version
    function dpplog($level, $msg)
    {
        // FreePBX::Modules()->deprecatedFunction();
        return FreePBX::Dpviz()->dpp->log($level, $msg);
    }
