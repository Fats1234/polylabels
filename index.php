<?php
   include('header.php');
   require_once "config.php";
   require_once "HTML/Table.php";
   require_once "functions.php";
   require_once "class/PolyLabel.php";

   $labelsdb = new mysqli($host,$dbuser,$dbpass,$database);
   
   if (!$labelsdb){
      echo "Could not connect to $database database";
      exit(1);   
   }
   
   //query the database for all labels in the main table
   $query="SELECT labels_id FROM polylabels_labels WHERE labels_active=1";
   //echo $query;

   $labelsCatalog=$labelsdb->query($query);

   echo "<h1>Choose the label that you would like to create</h1>\n";
   $attrs = array('width' => '100%','border' => '1');
   $labelTable = new HTML_Table($attrs);
   $labelTable->setHeaderContents(0,0,"Label Sample");
   $labelTable->setHeaderContents(0,1,"Label Name");
   $labelTable->setHeaderContents(0,2,"Label Description");
   $labelTable->setHeaderContents(0,3,"");

   $row=1;

   while(list($labelID)=$labelsCatalog->fetch_row()){
      $label = new PolyLabel($labelsdb,$labelID);
      $labelTable->setCellContents($row,0,"<img src=\"images/$label->samplePic\">");
      $labelTable->setCellContents($row,1,$label->name);
      $labelTable->setCellContents($row,2,$label->description);
      $labelTable->setCellContents($row,3,startForm("create.php","GET")."\t\t\t".genHidden("label",$label->name)."\t\t\t".
                                                    genButton("Setup $label->name")."\t\t\t".
                                                    endForm());
      $row++;
   }
   
   $labelTable->setAllAttributes("align=\"center\"");

   echo $labelTable->toHTML();

   $labelsCatalog->free();   
   $labelsdb->close();  
 
   include('footer.php');
?>
