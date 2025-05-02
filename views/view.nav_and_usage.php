<?php if (!defined('FREEPBX_IS_AUTH')) { exit(_('No direct script access allowed')); } ?>

<div class="display no-border">
    <div class="row">
        <div class="col-sm-12">
            <div class="fpbx-container">

                <div class="panel  panel-info panel-help">
                    <div class="panel-heading">
                        <h3 class="panel-title"><i class="fa fa-info-circle fa-lg fa-fw"></i> <?= _("Information on how to navigate and use the dial plane flow") ?></h3>
                    </div>
                    <div class="panel-body">

                        <div class="table-responsive rounded">
                            <table class="table table-striped table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th class="text-center"><?= _("Feature") ?></th>
                                        <th><?= _("Description") ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="text-center"><strong><?= _("Redraw from a Node") ?></strong></td>
                                        <td><?= _("Press <strong>Ctrl</strong> (<strong>Cmd</strong> on macOS) and left-click a node to make it the new starting point in the diagram. To revert, <strong>Ctrl/Cmd + left-click</strong> the parent node.") ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-center"><strong><?= _("Highlight Paths") ?></strong></td>
                                        <td><?= _("Click <strong>Highlight Paths</strong>, then select a node or edge (links are inactive). Click <strong>Remove Highlights</strong> to clear.") ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-center"><strong><?= _("Hover") ?></strong></td>
                                        <td><?= _("Hover over a path to highlight between destinations.") ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-center"><strong><?= _("Open Destinations") ?></strong></td>
                                        <td><?= _("Click a destination to open it in a new tab.") ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-center"><strong><?= _("Open Time Groups") ?></strong></td>
                                        <td><?= _("Click on a \"<strong>Match: (timegroup)</strong>\" or \"<strong>NoMatch</strong>\" to open in a new tab.") ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-center"><strong><?= _("Pan") ?></strong></td>
                                        <td><?= _("Hold the left mouse button and drag to move the view.") ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-center"><strong><?= _("Zoom") ?></strong></td>
                                        <td><?= _("Use the mouse wheel to zoom in and out.") ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                    </div>
                    <div class="panel-footer"></div>
                </div>

            </div>
        </div>
    </div>
</div>
