<?php
session_start();

require_once '../../../dryden/loader.inc.php';

require_once '../../../cnf/db.php';
require_once '../../../dryden/db/driver.class.php';
require_once '../../../dryden/debug/logger.class.php';
require_once '../../../inc/dbc.inc.php';
require_once '../../../dryden/runtime/dataobject.class.php';
require_once '../../../dryden/sys/versions.class.php';
require_once '../../../dryden/ctrl/options.class.php';
require_once '../../../dryden/ctrl/auth.class.php';
require_once '../../../dryden/ctrl/users.class.php';

if (ctrl_auth::RequireUser()) {
    $userId = $_SESSION['zpuid'];
    $currentUser = ctrl_users::GetUserDetail($userId);
    $path = ctrl_options::GetSystemOption('hosted_dir');
    $loadedFile = false;
    if (file_exists($path . $currentUser["username"] . "/elfilemanager_config.xml")) {
        $xml = simplexml_load_string(file_get_contents($path . $currentUser["username"] . "/elfilemanager_config.xml"));
        $theme = $xml->theme;
        $lang = $xml->lang;
        $loadedFile = true;
    }
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="utf-8">
        <title></title>

        <!-- jQuery and jQuery UI (REQUIRED) -->
        <link rel="stylesheet" type="text/css"
              href="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.css">
        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
        <script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js"></script>

        <!-- elFinder CSS (REQUIRED) -->
        <link rel="stylesheet" type="text/css" href="css/elfinder.min.css">
        <?php if ($loadedFile) { ?>
            <link rel="stylesheet" type="text/css" href="themes/<?php echo $theme; ?>/css/theme.css">
        <?php } ?>

        <!-- elFinder JS (REQUIRED) -->
        <script src="js/elfinder.min.js"></script>

        <!-- elFinder translation (OPTIONAL) -->
        <?php if ($loadedFile) { ?>
            <script src="js/i18n/elfinder.<?php echo $lang; ?>.js"></script>
        <?php } ?>


        <!-- elFinder initialization (REQUIRED) -->
        <script type="text/javascript" charset="utf-8">
            // Documentation for client options:
            // https://github.com/Studio-42/elFinder/wiki/Client-configuration-options
            $(document).ready(function () {
                $('#elfinder').elfinder({
                    url: 'php/connector.minimal.php',
                    <?php if($loadedFile) { ?>
                    lang: '<?php echo $lang; ?>',
                    <?php } ?>
                    customData: {path: "<?php echo $path . '/' . $currentUser["username"] . '/'; ?>"}
                });
            });
        </script>
    </head>
    <body>
    <!-- Element where elFinder will be created (REQUIRED) -->
    <div id="elfinder"></div>

    </body>
    </html>
<?php } else {
    ?>
    <body style="background: #F3F3F3;">
    <h2>Unauthorized Access!</h2>
    </body>
<?php } ?>
