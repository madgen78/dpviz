<?php if (!defined('FREEPBX_IS_AUTH')) { exit(_('No direct script access allowed')); } ?>

<div class="display no-border">
    <div class="row">
        <div class="col-sm-12">
            <div class="fpbx-container">

                <div class="btn-toolbar bg-light border rounded p-3 shadow-sm mb-4" role="toolbar" id="btn-toolbar">
                    <div class="btn-toolbar w-100" role="toolbar">
                        <div class="btn-group mr-2" role="group">
                            <button type="button" class="btn btn-primary" id="reload-dpp" disabled>
                                <i class="fa fa-refresh"></i> <?= _("Reload Page") ?>
                            </button>
                        </div>
                        <div class="btn-group mr-2" role="group">
                            <button type="button" class="btn btn-default" id="toolbar_btn_focus" disabled>
                                <i class="fa fa-magic"></i> <?= _("Highlight Paths") ?>
                            </button>
                        </div>
                        <div class="input-group mr-2 flex-grow-1" role="group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">
                                    <i class="fa fa-file mr-2"></i> <?= _("Export as") ?>
                                </span>
                            </div>
                            <input type="text" class="form-control flex-grow-1" id="filename_input" value="" disabled>
                            <div class="input-group-append">
                                <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" disabled>
                                    <i class="fa fa-download"></i> <?= _("Download") ?>
                                </button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item export-option-scale" href="#" data-scale="4">
                                        <i class="fa fa-star"></i> <?= _("High") ?>
                                    </a>
                                    <a class="dropdown-item export-option-scale" href="#" data-scale="2">
                                        <i class="fa fa-circle"></i> <?= _("Standard") ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="vizContainer" class="display full-border" style="min-height: 65vh;">
                    <div class="row mb-4 align-items-center" id="vizContainerHeader">
                        <div class="col-3"></div>
                        <div class="col-6 text-center">
                            <h2 class="fw-bold mb-0" id="vizContainerTitle" style="text-shadow: 1px 1px 2px rgba(0,0,0,0.2);"></h2>
                        </div>
                        <div class="col-3 text-right">
                            <h6 class="text-muted mb-0" id="vizContainerDatetime"></h6>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12" id="vizContainerBody">

                            <div class="card" style="max-width: 50%; margin: 0 auto;">
                                <div class="card-header"></div>
                                <div class="row no-gutters">
                                    <div class="col-md-4">
                                        <img src="modules/dpviz/assets/img/select_null.png">
                                    </div>
                                    <div class="col-md-8">
                                        <div class="card-body">
                                            <h5 class="card-title"><?= _("No Dial Plan has been selected for display!") ?></h5>
                                            <p class="card-text"><?= _("In order to generate and view the corresponding call flow, please select a Dial Plan from the available menu. Once selected, the detailed call flow information will load automatically, allowing you to review and analyze the call routing configuration associated with that plan. This selection is required to proceed with inspecting or editing the call flow.") ?></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer"></div>
                            </div>

                        </div>
                    </div>
                </div>


            </div>
        </div>
    </div>
</div>
