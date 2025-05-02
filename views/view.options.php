<?php if (!defined('FREEPBX_IS_AUTH')) { exit(_('No direct script access allowed')); } ?>

<div class="display no-border">
    <div class="row">
        <div class="col-sm-12">
            <div class="fpbx-container">

                <!--check for updates-->
                <div class="element-container">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="row">
                                <div class="col-md-3">
                                    <button id="check-update-btn" class="btn btn-default"><i class="fa fa-github fa-lg"></i> <?= _("Check for Updates") ?></button>&nbsp;<a href="https://github.com/madgen78/dpviz/" target="_blank"></a>
                                </div>
                                <div class="col-md-9">
                                    <div id="update-result"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--END check for updates-->

                <form class="fpbx-submit" name="editDpviz">
                    <?php
                    foreach ($tab['settings'] as $key_order => $setting)
                    {
                        switch($setting['type'])
                        {
                            case 'checkbox':
                                $setting['val_yes'] = $setting['val_yes'] ?? 1;
                                $setting['val_no']  = $setting['val_no'] ?? 0;
                                ?>
                                <div class="element-container">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="row">
                                                <div class="form-group">
                                                    <div class="col-md-3">
                                                        <label class="control-label" for="<?= $setting['id'] ?>"><?= $setting['label'] ?></label>
                                                        <i class="fa fa-question-circle fpbx-help-icon" data-for="<?= $setting['id'] ?>"></i>
                                                    </div>
                                                    <div class="col-md-9 radioset">
                                                        <input type="radio" name="<?= $setting['id'] ?>" id="<?= $setting['id'] ?>yes" value="<?= $setting['val_yes'] ?>" <?= ($setting['val'] ? "CHECKED":"") ?>>
                                                        <label for="<?= $setting['id'] ?>yes"><?= _("Yes");?></label>
                                                        <input type="radio" name="<?= $setting['id'] ?>" id="<?= $setting['id'] ?>no" value="<?= $setting['val_no'] ?>" <?= ($setting['val'] ? "":"CHECKED") ?>>
                                                        <label for="<?= $setting['id'] ?>no"><?= _("No");?></label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <span id="<?= $setting['key'] ?>-help" class="help-block fpbx-help-block"><?= $setting['help'] ?></span>
                                        </div>
                                    </div>
                                </div>
                                <?php
                            break;

                            case 'radioset':
                                $setting['options'] = $setting['options'] ?? [];
                                ?>
                                <div class="element-container">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="row">
                                                <div class="form-group">
                                                    <div class="col-md-3">
                                                        <label class="control-label" for="<?= $setting['id'] ?>"><?= $setting['label'] ?></label>
                                                        <i class="fa fa-question-circle fpbx-help-icon" data-for="<?= $setting['id'] ?>"></i>
                                                    </div>
                                                    <div class="col-md-9 radioset">
                                                        <?php foreach ($setting['options'] as $key_option => $val_option): ?>
                                                            <input type="radio" name="<?= $setting['id'] ?>" id="<?= $key_option ?>" value="<?= $val_option['value'] ?>" <?= ($setting['val'] == $val_option['value'] ? "CHECKED" : "") ?>>
                                                            <label for="<?= $key_option ?>"><?= $val_option['label'] ?></label>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <span id="<?= $setting['key'] ?>-help" class="help-block fpbx-help-block"><?= $setting['help'] ?></span>
                                        </div>
                                    </div>
                                </div>
                                <?php
                            break;
                        }
                    }
                    ?>
                    <!-- button submit -->
                    <div class="row">
                        <div class="col-md-12 text-right">
                            <div class="btn-group" role="group" aria-label="Actions">
                                <button type="button" class="btn btn-primary" name="submit" id="settings_submit">
                                    <i class="fa fa-save me-2"></i>
                                    <?= _('Save Changes') ?>
                                </button>
                                <button type="button" class="btn btn-danger" name="reset"  id="settings_reset">
                                    <i class="fa fa-undo me-2"></i>
                                    <?= _('Reset Default') ?>
                                </button>
                            </div>
                        </div>
                    </div>
                    <!-- END button submit -->

                </form>
            </div>
        </div>
    </div>
</div>
