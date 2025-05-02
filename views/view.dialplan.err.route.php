<?php if (!defined('FREEPBX_IS_AUTH')) { exit(_('No direct script access allowed')); } ?>

<div class="display no-border">
    <div class="row">
        <div class="col-sm-12">
            <div class="fpbx-container">

                <div class="card" style="max-width: 50%; margin: 0 auto;">
                    <div class="card-header"></div>
                    <div class="row no-gutters">
                        <div class="col-md-4">
                            <img src="modules/dpviz/assets/img/select_error.png">
                        </div>
                        <div class="col-md-8">
                            <div class="card-body">
                                <h5 class="card-title"><?= sprintf(_("Error: Could not find inbound route for '%s'"), $iroute) ?></h5>
                                <p class="card-text"><?= sprintf(_("The system was unable to locate a matching inbound route for the specified destination: '%s'. This may indicate that the route has been deleted, is misconfigured, or that the call is being directed to a non-existent or unregistered pattern. Please verify the inbound route settings and ensure that the destination is correctly defined within your Dial Plan configuration."), $iroute) ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer"></div>
                </div>

            </div>
        </div>
    </div>
</div>
