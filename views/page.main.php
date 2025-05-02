<?php if (!defined('FREEPBX_IS_AUTH')) { exit(_('No direct script access allowed')); } ?>

<div class="container-fluid">
    <h1><?= _("Dial Plan Vizualizer") ?></h1>
    <div class="display full-border">
        <div class="row">
            <div class="col-sm-12">
                <div class="fpbx-container">
                    <ul class="nav nav-tabs" role="tablist">
                        <li role="presentation" data-name="dpbox" class="active">
                            <a href="#dpbox" aria-controls="dpbox" role="tab" data-toggle="tab">
                                <?= _("Dial Plan") ?>
                            </a>
                        </li>
                        <li role="presentation" data-name="settings" class="change-tab">
                            <a href="#settings" aria-controls="settings" role="tab" data-toggle="tab">
                                <?= _("Settings") ?>
                            </a>
                        </li>
                        <li role="presentation" data-name="NavAndUsage" class="change-tab">
                            <a href="#NavAndUsage" aria-controls="NavAndUsage" role="tab" data-toggle="tab">
                                <?= _("Navigation and Usage") ?>
                            </a>
                        </li>
                    </ul>
                    <div class="tab-content display">
                        <div role="tabpanel" id="dpbox" class="tab-pane active">
                            <?= $dpviz->showPage('dialplan') ?>
                        </div>
                        <div role="tabpanel" id="settings" class="tab-pane">
                            <?= $dpviz->showPage('options') ?>
                        </div>
                        <div role="tabpanel" id="NavAndUsage" class="tab-pane">
                            <?= $dpviz->showPage('NavAndUsage') ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
