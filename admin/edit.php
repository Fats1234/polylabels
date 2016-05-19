<?php
   include("../header.php");
   require_once "../functions.php";
   require_once "../config.php";
   require_once "../class/PolyLabel.php";
   require_once "HTML/Table.php";
      
   if($labelname = $_GET['label']){
      //connect to database and grab the label ID using the label name
      //echo "Connecting to Database...";
      $labelsdb = new mysqli($host,$dbuser,$dbpass,$database);

      $labelQuery = "SELECT labels_id FROM polylabels_labels WHERE labels_name='$labelname'";

      $labelsResult=$labelsdb->query($labelQuery);
      list($labelID)=$labelsResult->fetch_row();
      $labelsResult->free();

      //create a new PolyLabel object using the Label ID
      if(isset($_POST['fieldID'])){
         $field = new PolyLabelField($labelsdb,$_POST['fieldID']);
      }
      $label = new PolyLabel($labelsdb,$labelID);
      
      if(isset($_POST['setName'])){$label->updateName($labelsdb,$_POST['labelName']);header("Location: edit.php?label=$label->name");}
      if(isset($_POST['setDesc'])){$label->updateDesc($labelsdb,$_POST['labelDesc']);header("Location: edit.php?label=$label->name");}
      if(isset($_POST['changeState'])){$label->changeActiveState($labelsdb);header("Location: edit.php?label=$labelname");}
      if(isset($_POST['setNumCopies'])){ $label->updateNumCopies($labelsdb,$_POST['numCopies']); header("Location: edit.php?label=$labelname");}
      if(isset($_POST['addField'])){
         $label->addField($labelsdb,$_POST['newFieldName'],$_POST['newCSVName'],$_POST['newFieldType']);
         header("Location: edit.php?label=$labelname");
      }
      if(isset($_POST['delField'])){$label->delField($labelsdb,$field); header("Location: edit.php?label=$labelname");}
      if(isset($_POST['addVal'])){$field->addFieldValue($labelsdb,$_POST['newValue']); header("Location: edit.php?label=$labelname");}
      if(isset($_POST['delVal'])){
         $fieldValueID=$field->getFieldValueID($labelsdb,$_POST['fieldValue']);
         $field->delFieldValue($labelsdb,$fieldValueID);
         header("Location: edit.php?label=$labelname");
      }
      if(isset($_POST['modVal'])){$field->updateFieldValue($labelsdb,$_POST['newValue']); header("Location: edit.php?label=$labelname");}
      if(isset($_POST['setGlabelsFile'])){
         if (move_uploaded_file($_FILES['glabelsFile']['tmp_name'], "../glabels/$labelname.glabels")){
            $label->setGlabelsFile($labelsdb,$labelname.".glabels");
            header("Location: edit.php?label=$labelname");
         }else{
            echo "Error uploading glabels file!";
         }
      }      
      if(isset($_POST['setSamplePic'])){
         $filename=$_FILES['samplePic']['name'];
         $extension=end((explode(".", $filename)));
         if (move_uploaded_file($_FILES['samplePic']['tmp_name'], "../images/$labelname.".$extension)){
            $label->setSamplePic($labelsdb,$labelname.".".$extension);
            header("Location: edit.php?label=$labelname");
         }else{
            echo "Error uploading sample picture!";
         }
      }
      if(isset($_POST['setSampleDescPic'])){
         $filename=$_FILES['sampleDescPic']['name'];
         $extension=end((explode(".", $filename)));
         if (move_uploaded_file($_FILES['sampleDescPic']['tmp_name'], "../images/$labelname-desc.".$extension)){
            $label->setSampleDescPic($labelsdb,$labelname."-desc.".$extension);
            header("Location: edit.php?label=$labelname");
         }else{
            echo "Error uploading sample descriptive picture!";
         }
      }
      
      /********************Fields Settings Table************************/
      echo "<font size=\"6\">Label Settings</font>";

      $attrs = array('width' => '100%','border' => '1');
      $settingsTable = new HTML_Table($attrs);

      $row=0;
      
      $settingsTable->setHeaderContents($row,0,"Setting");
      $settingsTable->setHeaderContents($row,1,"Current Value");
      $settingsTable->setHeaderContents($row,2,"Set New Value");
      $row++;

      $settingsTable->setCellContents($row,0,"Active/Inactive");
      $settingsTable->setCellContents($row,1,$label->active);
      $activeArray=array("activate","deactivate");
      //$settingsTable->setCellContents($row,2,startForm("edit.php?label=$labelname","POST").genDropBox("active",$activeArray));
      $settingsTable->setCellContents($row,3,startForm("edit.php?label=$labelname","POST").genButton("Change State","changeState").endForm());
      $row++;
      
      $settingsTable->setCellContents($row,0,"Label Name");
      $settingsTable->setCellContents($row,1,$label->name);
      $settingsTable->setCellContents($row,2,startForm("edit.php?label=$labelname","POST").genTextBox("labelName",$label->name));
      $settingsTable->setCellContents($row,3,genButton("Set Label Name","setName").endForm());
      $row++;
      
      $settingsTable->setCellContents($row,0,"Label Description");
      $settingsTable->setCellContents($row,1,$label->description);
      $settingsTable->setCellContents($row,2,startForm("edit.php?label=$labelname","POST").genTextArea("labelDesc",10,50,$label->description));
      $settingsTable->setCellContents($row,3,genButton("Set Label Description","setDesc").endForm());
      $row++;

      $settingsTable->setCellContents($row,0,"Default Number of Copies");
      $settingsTable->setCellContents($row,1,$label->numcopies);
      $settingsTable->setCellContents($row,2,startForm("edit.php?label=$labelname","POST").genTextBox("numCopies",$label->numcopies));
      $settingsTable->setCellContents($row,3,genButton("Set Copies","setNumCopies").endForm());
      $row++;
      
      $settingsTable->setCellContents($row,0,"Glabels File");
      $settingsTable->setCellContents($row,1,$label->glabelsFile);
      $settingsTable->setCellContents($row,2,startForm("edit.php?label=$labelname","POST",TRUE).genUpload("glabelsFile"));
      $settingsTable->setCellContents($row,3,genButton("Set Glabels File","setGlabelsFile").endForm());
      $row++;
      
      $settingsTable->setCellContents($row,0,"Sample Picture");
      $settingsTable->setCellContents($row,1,"<img src=\"../images/$label->samplePic\">");
      $settingsTable->setCellContents($row,2,startForm("edit.php?label=$labelname","POST",TRUE).genUpload("samplePic"));
      $settingsTable->setCellContents($row,3,genButton("Set Sample Picture","setSamplePic").endForm());
      $row++;

      $settingsTable->setCellContents($row,0,"Sample Descriptive Picture");
      $settingsTable->setCellContents($row,1,"<img src=\"../images/$label->sampleDescPic\">");
      $settingsTable->setCellContents($row,2,startForm("edit.php?label=$labelname","POST",TRUE).genUpload("sampleDescPic"));
      $settingsTable->setCellContents($row,3,genButton("Set Sample Descriptive Picture","setSampleDescPic").endForm());
      $row++;
      
      echo $settingsTable->toHTML();
      echo "\n<br><br><br>";

      /******************Fields Table*********************************/
      $attrs = array('width' => '100%','border' => '1');
      $fieldsTable = new HTML_Table($attrs);
      $row=0;

      echo "<font size=\"6\">Label Fields</font>";

      $fieldsTable->setHeaderContents($row,0,"Name");
      $fieldsTable->setHeaderContents($row,1,"CSV Name");
      $fieldsTable->setHeaderContents($row,2,"Type");
      $row++;

      /*put all field objects into a single array*/
      $fieldsArray = array();
      if(count($label->staticFields)) $fieldsArray = array_merge($fieldsArray,$label->staticFields);
      if(count($label->serialFields)) $fieldsArray = array_merge($fieldsArray,$label->serialFields);
      if($label->qtyField) array_push($fieldsArray,$label->qtyField);

      foreach($fieldsArray as $field){
         $fieldsTable->setCellContents($row,0,$field->name);
         $fieldsTable->setCellContents($row,1,$field->csvname);
         $fieldsTable->setCellContents($row,2,$field->type);
         $fieldsTable->setCellContents($row,3,startForm("edit.php?label=$labelname","POST")."\t\t\t".
                                                         genHidden("fieldID",$field->id)."\t\t\t".
                                                         genButton("Delete Field","delField")."\t\t\t".
                                                         endForm());
         $row++;
      }

      $fieldTypes=array("static","serial","quantity");
      $fieldsTable->setCellContents($row,0,"\n".startForm("edit.php?label=$labelname","POST").genTextBox("newFieldName"));
      $fieldsTable->setCellContents($row,1,genTextBox("newCSVName"));
      $fieldsTable->setCellContents($row,2,genDropBox("newFieldType",$fieldTypes));
      $fieldsTable->setCellContents($row,3,genButton("Create New Field","addField").endForm());

      $attrs = array('align' => 'left','valign' => 'middle');
      $fieldsTable->setAllAttributes($attrs);

      echo $fieldsTable->toHTML();
      echo "\n<br><br><br>";

      /*****************Field Values Table*******************************/
      $attrs = array('width' => '100%','border' => '1');
      $valuesTable = new HTML_Table($attrs);
      $row=0;

      echo "<font size=\"6\">Label Field Values</font>";
      $valuesTable->setHeaderContents($row,0,"Field Name");
      $valuesTable->setHeaderContents($row,1,"Field Value(s)");
      $valuesTable->setHeaderContents($row,2,"Add/Modify Value");
      $row++;

      foreach($fieldsArray as $field){
         $curValFormStr="";
         $newValFormStr="";

         if (strcmp($field->type,"static")==0){
            $curValFormStr=startForm("edit.php?label=$labelname","POST").genDropBox("fieldValue",$field->presetVals).
                              genHidden("fieldID",$field->id).genButton("Delete Value","delVal").endForm();
            $newValFormStr=startForm("edit.php?label=$labelname","POST").genTextBox("newValue").
                              genHidden("fieldID",$field->id).genButton("Add Value","addVal").endForm();
         }else{
            $curValFormStr=$field->presetVals[0];
            $newValFormStr=startForm("edit.php?label=$labelname","POST").genTextBox("newValue",$field->presetVals[0]).
                              genHidden("fieldID",$field->id).genButton("Modify Value","modVal").endForm();
         }
         $valuesTable->setCellContents($row,0,$field->name);
         $valuesTable->setCellContents($row,1,$curValFormStr);
         $valuesTable->setCellContents($row,2,$newValFormStr);
         $row++; 
      }

      echo $valuesTable->toHTML();
      echo "\n<br><br><br>";

      //*******************Barcode Fields********************************
      echo "<font size=\"6\">Barcode Fields</font>";
      
      $attrs = array('width' => '100%','border' => '1');
      $barcodesTable = new HTML_Table($attrs);
      $row=0;

      $barcodesTable->setHeaderContents($row,0,"Name");
      $barcodesTable->setHeaderContents($row,1,"Prefix");
      $barcodesTable->setHeaderContents($row,2,"Suffix");
      $barcodesTable->setHeaderContents($row,3,"Fields Used");
      $row++;   

      foreach($label->barcodes as $barcode){
         $barcodesTable->setCellContents($row,0,$barcode->name);
         $barcodesTable->setCellContents($row,1,$barcode->prefix);
         $barcodesTable->setCellContents($row,2,$barcode->suffix);
         $fieldsCSVNames=array();
         foreach($barcode->fieldsUsed as $field) array_push($fieldsCSVNames,$field->csvname);
         $barcodesTable->setCellContents($row,3,genDropBox("fields",$fieldsCSVNames));
         $barcodesTable->setCellContents($row,4,startForm("edit_barcode.php","GET").
                                                   genHidden("barcodeid",$barcode->id).
                                                   genButton("Edit Barcode").endForm());
         $row++;
      }

      $barcodesTable->setCellContents($row,0,startForm("edit_barcode.php","POST").genTextBox("newBarName"));
      $barcodesTable->setCellContents($row,1,genTextBox("newBarPrefix"));
      $barcodesTable->setCellContents($row,2,genTextBox("newBarSuffix"));
      $barcodesTable->setCellContents($row,4,genHidden("labelID",$labelID).
                                                genButton("Create Barcode","newBarcode").endForm());
 
      echo $barcodesTable->toHTML();

   }

   include("../footer.php");
?>
