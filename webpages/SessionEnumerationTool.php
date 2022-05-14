<?php
    global $title;
    $title = "Session Enumeration Tool";
    require_once('StaffCommonCode.php');
    staff_header($title, true);
?>

</div>
<div class="container">

<?php RenderXSLT('SessionEnumeratorTool.xsl', array()); ?>

</div>
<div class="container-fluid">



<?php staff_footer(); ?>