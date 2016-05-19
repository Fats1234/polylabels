<?php
   include('../header.php');
   require_once "../config.php";

   if(isset($_POST['create'])){
      //sanitize input later
      $labelsdb = new mysqli($host,$dbuser,$dbpass,$database);

      $newLabelName=$_POST['newLabelName'];
      $newLabelDesc=$_POST['newLabelDesc'];

      if (!$labelsdb){
         echo "Could not connect to $database database";
         exit(1);
      }

      $newLabelQuery="INSERT INTO polylabels_labels SET labels_name='$newLabelName', labels_description='$newLabelDesc'";

      if($labelsdb->query($newLabelQuery)){
         mkdir("../output/$newLabelName",0775);
         header("Location: edit.php?label=$newLabelName");
      }else{
         echo "Query Error: $newLabelQuery!<br>\n";
         printf("Error: %s\n", $labelsdb->sqlstate);
      }

   }else{
      echo "Nothing to create...";
   }

   include('../footer.php');
?>
