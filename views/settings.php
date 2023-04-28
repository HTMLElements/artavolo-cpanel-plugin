<?php
if (!isset($settings) or ! $settings) {
    $settings = array();
}
$white_label_key = isset($settings['key']) ? $settings['key'] : '';
$auto_install = isset($settings['auto_install']) ? $settings['auto_install'] : '';
//$install_type = isset($settings['install_type']) && $settings['install_type'] == 'symlinked';
$install_type = isset($settings['install_type']) ? $settings['install_type'] : '';
$db_driver = isset($settings['db_driver']) ? $settings['db_driver'] : '';
?>

<form method="POST">
    <div class="row">
        <div class="col-md-6">
            <input type="hidden" name="save_settings" value="1">

            <h2>Installation settings</h2>

            <div>
                <label>
                    <input type="radio" name="auto_install" value="1" <?php echo $auto_install == '1' ? 'checked' : ''; ?>>
                    Automatically install Artavolo on new domains creation. <a href="#" data-toggle="tooltip" title="You must enable the Artavolo feature in your packages settings">[?]</a>
                </label>
                <br>

                <label>
                    <input type="radio" name="auto_install" value="0" <?php echo $auto_install == '0' ? 'checked' : ''; ?>>
                    Allow users to Manually install Artavolo from cPanel. <a href="#" data-toggle="tooltip" title="You must enable the Artavolo feature in your packages settings">[?]</a>
                </label>
                <br>

                <label><input type="radio" name="auto_install" <?= ($auto_install == 'disabled' OR $auto_install == '') ? 'checked' : ''; ?> value="disabled"> Disabled for all users</label>
                <br>
            </div>
        </div>

        <div class="col-md-4">
            <h2>Installation Type</h2>

            <div>
                <label>
                    <input type="radio" name="install_type" value="normal" disabled <?php
                    if ($install_type == 'normal') {
                        print 'checked';
                    }
                    ?>>
                    Copy files  / Standalone <a href="#" data-toggle="tooltip" title="All source code is copied in the folder of the user. With this way the site will be standalone.">[?]</a>
                </label>
                <br>
                <label>
                    <input type="radio" name="install_type" value="symlinked" <?php
                    if ($install_type == 'symlinked' or $install_type == false) {
                        print 'checked';
                    }
                    ?>>
                    Sym-linked (recommended) <a href="#" data-toggle="tooltip" title="Code is symliked from shared folder for all users. With this way you save a big amount of disk space on the server.">[?]</a>
                </label>
                <br>
            </div>
            <?php /*
              <h2>Database Driver</h2>

              <label>
              <select name="db_driver" class="form-control">

              <option <?php if ($db_driver == 'mysql') {
              print 'selected';
              } ?> value="mysql">MySQL
              </option>
              <option <?php if ($db_driver == 'sqlite') {
              print 'selected';
              } ?> value="sqlite">SQLite
              </option>
              </select>
              </label>
             */ ?>

            <div class="row">
                <div class="col-xs-12">
                    <button type="submit" class="btn btn-primary" style="margin-top: 15px;">Save</button>
                </div>
            </div>
        </div>
    </div>
</form>