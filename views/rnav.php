<?php if (!defined('FREEPBX_IS_AUTH')) { exit(_('No direct script access allowed')); } ?>

<div id="toolbar-all">
    <h2><?= _("Inbound Routes") ?></h2>
</div>
<table id="dpviz-side"
    class="table"
    data-escape="true"
    data-url="<?= $url_ajax ?>"
    data-cache="false"
    data-cookie="true"
    data-cookie-id-table="dpviz-rnav-side"
    data-toolbar="#toolbar-all"
    data-toggle="table"
    data-search="true"
    data-show-columns="true"
    data-show-refresh="true"
    >
    <thead>
        <tr>
            <th data-field="extension" data-formatter="bootnavvizFormatter" data-sortable="true" data-switchable="false"><?= _("DID / CID") ?></th>
            <th data-field="description" data-sortable="true"><?= _("Description") ?></th>
            <th data-field="destination" data-formatter="DIDdestFormatter" data-sortable="true" data-visible="false"><?= _("Destination") ?></th>
        </tr>
    </thead>
</table>
