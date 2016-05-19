<?php
   include("../header.php");
   require_once "../functions.php";
   require_once "../config.php";
   require_once "../class/PolyLabel.php";
   require_once "HTML/Table.php";

   $labelsdb = new mysqli($host,$dbuser,$dbpass,$database);

   if(isset($_POST['newBarcode'])){
      $label = new PolyLabel($labelsdb,$_POST['labelID']);
      $barcodeID=$label->addBarcode($labelsdb,$_POST['newBarName'],$_POST['newBarPrefix'],$_POST['newBarSuffix']);
      header("Location: edit_barcode.php?barcodeid=$barcodeID");
   }

   if($barcodeID=$_GET['barcodeid']){
            
      /*list all fields that can be used*/
      $barcode = new PolyLabelBarcode($labelsdb,$barcodeID);
      if(isset($_POST['usedFieldID'])){
         $usedField = new PolyBarcodeUsedField($labelsdb,$_POST['usedFieldID']);
      }
      
      if(isset($_POST['modName'])){ $barcode->updateName($labelsdb,$_POST['barName']); header("Location: edit_barcode.php?barcodeid=$barcodeID"); }
      if(isset($_POST['modPrefix'])){ $barcode->updatePrefix($labelsdb,$_POST['barPrefix']); header("Location: edit_barcode.php?barcodeid=$barcodeID"); }
      if(isset($_POST['modSuffix'])){ $barcode->updateSuffix($labelsdb,$_POST['barSuffix']); header("Location: edit_barcode.php?barcodeid=$barcodeID"); }
      if(isset($_POST['addField'])) { $barcode->addUsedField($labelsdb,$_POST['fieldID'],$_POST['order']); header("Location: edit_barcode.php?barcodeid=$barcodeID"); }
      if(isset($_POST['remField'])) { $barcode->delUsedField($labelsdb,$usedField); header("Location: edit_barcode.php?barcodeid=$barcodeID"); }
      if(isset($_POST['modOrder'])){
         foreach($barcode->fieldsUsed as $usedField) $usedField->updateOrder($labelsdb,$_POST[$usedField->barcodeUsedFieldID.'_order']);
         $barcode->updateFieldsOrder($labelsdb);
         header("Location: edit_barcode.php?barcodeid=$barcodeID");
      }
      
      /*Generate Barcode Settings Table*/
      echo "<font size=\"6\">Barcode Settings</font><br>";
      $row=0;
      
      $attrs = array('width' => '800','border' => '1');
      $barcodeSettingsTable = new HTML_Table($attrs);
      
      $barcodeSettingsTable->setHeaderContents($row,0,"Setting");
      $barcodeSettingsTable->setHeaderContents($row,1,"Current Value");
      $barcodeSettingsTable->setHeaderContents($row,2,"Set New Value");
      $row++;
      
      $barcodeSettingsTable->setCellContents($row,0,"Name");
      $barcodeSettingsTable->setCellContents($row,1,$barcode->name);
      $barcodeSettingsTable->setCellContents($row,2,startForm("edit_barcode.php?barcodeid=$barcodeID","POST").
                                                    genTextBox("barName"));
      $barcodeSettingsTable->setCellContents($row,3,genButton("Modify Name","modName").endForm());
      $row++;
      
      $barcodeSettingsTable->setCellContents($row,0,"Prefix");
      $barcodeSettingsTable->setCellContents($row,1,$barcode->prefix);
      $barcodeSettingsTable->setCellContents($row,2,startForm("edit_barcode.php?barcodeid=$barcodeID","POST").
                                                    genTextBox("barPrefix"));
      $barcodeSettingsTable->setCellContents($row,3,genButton("Modify Prefix","modPrefix").endForm());
      $row++;
      
      $barcodeSettingsTable->setCellContents($row,0,"Suffix");
      $barcodeSettingsTable->setCellContents($row,1,$barcode->suffix);
      $barcodeSettingsTable->setCellContents($row,2,startForm("edit_barcode.php?barcodeid=$barcodeID","POST").
                                                    genTextBox("barSuffix"));
      $barcodeSettingsTable->setCellContents($row,3,genButton("Modify Suffix","modSuffix").endForm());
      $row++;
      
      $barcodeSettingsTable->setColAttributes(0,array('width' => '100'));
      $barcodeSettingsTable->setColAttributes(1,array('width' => '200'));
      $barcodeSettingsTable->setColAttributes(2,array('width' => '300'));
      $barcodeSettingsTable->setColAttributes(1,array('width' => '200'));
      
      echo $barcodeSettingsTable->toHTML();
      echo "\n<br><br><br>";
      
      /*Generate Label Fields Table*/
      echo "<font size=\"6\">Available Label Fields</font><br>";
      
      $fieldsTable = new HTML_Table($attrs);
      $row=0;
      
      $labelID=$barcode->getLabelID($labelsdb);
      
      $label = new PolyLabel($labelsdb,$labelID);
      //echo $label->name;
      
      $labelFields=$label->getAllFields();
      
      $fieldsTable->setHeaderContents($row,0,"Field Name");
      $fieldsTable->setHeaderContents($row,1,"Barcode Order");
      $fieldsTable->setHeaderContents($row,2,"Add Field");
      $row++;
      
      foreach($labelFields as $field){
         $fieldsTable->setCellContents($row,0,startForm("edit_barcode.php?barcodeid=$barcodeID","POST").$field->csvname);
         $fieldsTable->setCellContents($row,1,genTextBox("order","1"));
         $fieldsTable->setCellContents($row,2,genHidden("fieldID",$field->id).genButton("Add Field to Barcode","addField").endForm());
         $row++;
      }
      
      echo $fieldsTable->toHTML();
      echo "\n<br><br><br>";
      
      /*Generate Barcode Used Fields Table*/
      echo "<font size=\"6\">Barcode Used Fields</font><br>";
      
      $usedFieldsTable = new HTML_Table($attrs);
      $row=0;
      
      $usedFieldsTable->setHeaderContents($row,0,"Field CSV Name");
      $usedFieldsTable->setHeaderContents($row,1,"Order");
      $row++;
      
      foreach($barcode->fieldsUsed as $usedField){
         $usedFieldsTable->setCellContents($row,0,startForm("edit_barcode.php?barcodeid=$barcodeID","POST").$usedField->csvname);
         $usedFieldsTable->setCellContents($row,1,$usedField->order.genHidden("usedFieldID",$usedField->barcodeUsedFieldID));
         $usedFieldsTable->setCellContents($row,2,genButton("Remove Field From Barcode","remField").endForm());
         $row++;
      }
      
      echo $usedFieldsTable->toHTML();
      echo "\n<br><br><br>";
      
      /*Generate Barcode Used Fields Reordering Table*/
      echo "<font size=\"6\">Set Barcode Used Fields Order</font><br>";
      echo startForm("edit_barcode.php?barcodeid=$barcodeID","POST");
      
      $orderFieldsTable = new HTML_TABLE($attrs);
      $row=0;
      
      $orderFieldsTable->setHeaderContents($row,0,"Field CSV Name");
      $orderFieldsTable->setHeaderContents($row,1,"Order");
      $row++;
      
      foreach ($barcode->fieldsUsed as $usedField){
         $orderFieldsTable->setCellContents($row,0,$usedField->csvname);
         $orderFieldsTable->setCellContents($row,1,genTextbox($usedField->barcodeUsedFieldID."_order",$usedField->order));
         $row++;
      }
      
      $orderFieldsTable->setCellContents($row,1,genButton("Update Order","modOrder"));
      
      echo $orderFieldsTable->toHTML();
      echo endForm();
      
   }

   include("../footer.php");
?>
