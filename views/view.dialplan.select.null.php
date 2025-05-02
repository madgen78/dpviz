<?php if (!defined('FREEPBX_IS_AUTH')) { exit(_('No direct script access allowed')); } ?>

<div class="display no-border">
    <div class="row">
        <div class="col-sm-12">
            <div class="fpbx-container">

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
