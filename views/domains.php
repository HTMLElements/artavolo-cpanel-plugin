<?php
if (!isset($domains)) {
    return;
}
if (!isset($admin_view)) {
    $admin_view = false;
}

$siteCount = 0;
if ($domains and ! empty($domains)) {
    $siteCount = count($domains);
}
?>

<div class="instance-list">
    <table class="table table-striped responsive-table">
        <thead>
            <tr>
                <th>Domain</th>

                <?php if ($admin_view): ?>
                    <th>User</th>
                <?php endif; ?>
                <th>Version</th>
                <th>Created at</th>
                <th>File Path</th>
                <!--<th class="text-right">Actions</th>-->
            </tr>
        </thead>
        <tbody>
            <?php if ($domains): ?>
                <?php foreach ($domains as $key => $domain): ?>
                    <?php
                    $mainDir = $domain['documentroot'];


                    if (isset($_GET['search'])) {
                        if (strpos($domain['domain'], $_GET['search']) === false)
                            continue;
                    }
                    ?>
                    <tr>
                        <td>
                            <a href="http://<?php echo $domain['domain']; ?>" target="_blank">
                                <!--<img src="../assets/pt-icon.png" class="pt-icon" />--> 
                                <?php echo $domain['domain']; ?>
                            </a>

                            <?php if (isset($domain['type']) and $domain['type']): ?>
                                <span class="label label-success" title="<?php echo $domain['type']; ?>" style="margin-left:10px;"><?php echo PlumtexHelpers::titlelize($domain['type']); ?></span>
                            <?php endif; ?>
                        </td>
                        <?php if ($admin_view): ?>
                            <td><?php echo $domain['user']; ?></td>
                        <?php endif; ?>

                        <td><?php echo $domain['version']; ?></td>
                        <td><?php echo date('Y M d', strtotime($domain['created_at'])); ?></td>
                        <td>
                            <?php echo $domain['documentroot']; ?>

                            <?php if ($admin_view and isset($domain['is_symlink']) and $domain['is_symlink']): ?>
                                <span class="label label-default" title="<?php echo $domain['symlink_target']; ?>">symlink</span>
                            <?php elseif ($admin_view and isset($domain['is_symlink']) and ! $domain['is_symlink']): ?>
                                <span class="label label-success" title="<?php echo $domain['documentroot']; ?>">standalone</span>
                            <?php endif; ?>
                        </td>

                        <td class="action" style="text-align: right; display: none;">
                            <?php if ($admin_view): ?>
                                <form method="POST" id="updateSite-<?php print $key; ?>">
                                    <input type="hidden" name="_action" value="_do_update">
                                    <input type="hidden" name="domain" value="<?php echo htmlspecialchars(json_encode($domain)); ?>">

                                    <?php if ($admin_view and isset($domain['is_symlink']) and $domain['is_symlink']): ?>
                                        <button type="submit" target="#updateSite-<?php print $key; ?>" class="btn btn-success"><i class="fas fa-sync-alt"></i> Update</button>
                                    <?php elseif ($admin_view and isset($domain['is_symlink']) and ! $domain['is_symlink']): ?>
                                        <button type="submit" target="#updateSite-<?php print $key; ?>" class="btn btn-warning"><i class="fas fa-sync-alt"></i> Rebase</button>
                                    <?php endif; ?>

                                </form>
                            <?php endif; ?>

                            <?php //if (!$admin_view): ?>
                            <!--<button type="button" class="btn btn-danger" data-toggle="modal" data-target="#removeSite-<?php print $key; ?>"><i class="fa fa-trash"></i> Remove</button>-->
                            <?php //endif; ?>

                            <!-- Modal Delete Accept -->
                            <div class="modal fade" id="removeSite-<?php print $key; ?>" tabindex="-1" role="dialog" aria-labelledby="removeSiteLabel">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <form method="POST">
                                            <div class="modal-body" style="padding: 70px 0;">
                                                <h4 class="modal-title text-center">Are you sure you want to delete this website?</h4>
                                                <input type="hidden" name="_action" value="uninstall">
                                                <input type="hidden" name="domain" value="<?php echo htmlspecialchars(json_encode($domain)); ?>">
                                            </div>

                                            <div class="modal-footer" style="margin: 0;">
                                                <button type="button" class="btn btn-success" data-dismiss="modal">No</button>
                                                <button type="submit" class="btn btn-danger">Yes, delete my website</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <br>
    <small class="small">You have <?php print $siteCount ?> installations</small>

    <?php if (!isset($_GET['search']) && $siteCount == 0): ?>
        <div id="row-no-instances" class="instance-list-callout callout callout-info">
            <i class="fa fa-exclamation-circle"></i>
            <span id="no-installation-msg" class="callout-message">
                There is no Artavolo installations yet.
            </span>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['search']) && $siteCount == 0): ?>
        <div id="row-no-matches" class="instance-list-callout callout callout-info">
            <i class="fa fa-exclamation-circle"></i>
            No Artavolo installations match your search criteria.
        </div>
    <?php endif; ?>

    <?php if ($siteCount > 10): ?>
        <div id="loading-callout-large-set" class="instance-list-callout callout callout-warning">
            <i class="fa fa-exclamation-circle"></i>
            This account contains many Artavolo installations. Some operations may require more time.
        </div>
    <?php endif; ?>
</div>