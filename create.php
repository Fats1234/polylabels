<?php
   include "header.php";
   require_once "config.php";
   require_once "HTML/Table.php";
   require_once "functions.php";
   require_once "class/PolyLabel.php";

   //get label type from GET header in url
   if ($labelname = $_GET['label']){
   
      $labelsdb = new mysqli($host,$dbuser,$dbpass,$database);
   
      $labelQuery = "SELECT labels_id FROM polylabels_labels WHERE labels_name='$labelname'";

      $labelsResult=$labelsdb->query($labelQuery);
      list($labelID)=$labelsResult->fetch_row();
      $labelsResult->free();   

      $label = new PolyLabel($labelsdb,$labelID);
      
      //if it has a stamp then we need stamp label as well
      if($label->stampID)
         $stamp = new PolyLabel($labelsdb,$label->stampID);

      echo "<font size=\"6\">Label Name: $label->name</font><br>";
      echo "<img src=\"images/$label->sampleDescPic\"><br /><br />\n";
  
      //$attrs = array('border' => '1');

      echo startForm("generate.php","POST",FALSE,"labelForm");
      echo genHidden("labelID",$labelID);
 
      /*Generate Static Fields Table*/
      $fieldsTable = new HTML_Table(); 
      $row=0;
      
      $fieldsTable->setCellContents($row,0,"<font size=\"6\">Label Static Fields</font>");
      $row++;

      foreach($label->staticFields as $field){
         if ($field->hidden){
            echo genHidden($field->csvname,$field->presetVals[0]);
         }else{
            $fieldsTable->setCellContents($row,0,"$field->name");
            if(isset($_POST[$field->csvname]))
               $fieldsTable->setCellContents($row,1,genTextBox($field->csvname,$_POST[$field->csvname]));
            else
               $fieldsTable->setCellContents($row,1,genTextBox($field->csvname,$field->presetVals[0]));
            if(count($field->presetVals) > 1){
                  $dropdownjavascript = "\n<script type=\"text/javascript\">\n";
                  $dropdownjavascript .= "   var $field->csvname"."tb = document.getElementById('$field->csvname');\n";
                  $dropdownjavascript .= "   var $field->csvname"."dd = document.getElementById('$field->csvname"."drop');\n";
                  $dropdownjavascript .= "   $field->csvname"."dd.onchange = function(){\n";
                  $dropdownjavascript .= "      $field->csvname"."tb.value = this.value;\n";
                  $dropdownjavascript .= "   }\n";
                  $dropdownjavascript .= "</script>\n";
                  $fieldsTable->setCellContents($row,2,genDropBox($field->csvname."drop",$field->presetVals).$dropdownjavascript);
            }         
            $row++;
         }         
      }
      $row++;

      /*Generate Label Settings Section (for number of copies and first label start position*/
      $fieldsTable->setCellContents($row,0,"<font size=\"6\">Label Settings</font>");
      
      $row++;

      $fieldsTable->setCellContents($row,0,"Number of Copies");
      $fieldsTable->setCellContents($row,1,genTextBox("numcopies","$label->numcopies"));
      $fieldsTable->setCellContents($row,2,"Copies of each label to generate?");

      $row++;

      $fieldsTable->setCellContents($row,0,"Starting Position");
      $fieldsTable->setCellContents($row,1,genTextBox("startposition","1"));
      $fieldsTable->setCellContents($row,2,"Position of the first label on label paper?");
   
      $row++;
      $row++;
      
      if(count($label->serialFields)){
         $fieldsTable->setCellContents($row,0,"<font size=\"6\">Label Serial Fields</font>");      
         $row++; 
         
         $fieldsTable->setCellContents($row,0,"");
         $fieldsTable->setCellContents($row,1,"Serial Start");
         $fieldsTable->setCellContents($row,2,"Number of Systems");
         $row++;
         
         $displayRange=1;
         foreach($label->serialFields as $index => $serialfield){
            /*only allow ranges to be generated if a preset start value for ALL serial number exists*/
            if(!$serialfield->presetVals[0]){               
               $row--;
               $fieldsTable->setCellContents($row,2,""); //clear this Cell so that Number of Systems does not display
               $displayRange=0;
               break;
            }
            $fieldsTable->setCellContents($row+$index,0,"$serialfield->name");
            $fieldsTable->setCellContents($row+$index,1,genTextBox($serialfield->csvname."box",$serialfield->presetVals[0]));
            $displayRange=$row+index;
         }
         
         if($displayRange){
            $row=$displayRange;
            $fieldsTable->setCellContents($row,2,genTextBox("numsystems","1"));
            $fieldsTable->setCellContents($row,3,genButton("Generate Range","range"));         
            $row++;
         }
                  
         foreach($label->serialFields as $serialfield){
            $fieldsTable->setCellContents($row,0,"$serialfield->name");
            
            $serialinput="";
            if(isset($_POST['serialinput']) || isset($_POST[$serialfield->csvname])){
               $serialinput.=$_POST['serialinput'];
               $serialinput.=$_POST[$serialfield->csvname];
            }
            $fieldsTable->setCellContents($row,1,genTextArea($serialfield->csvname."area","10","34",$serialinput));
            $row++;
         }
      
         $fieldsTable->setCellContents($row-1,2,genButton("Generate Input","input"));
         $row++;
      }

      if(count($label->qtyField)){
         $fieldsTable->setCellContents($row,0,"<font size=\"6\">Label Quantity Fields</font>");
         
         $row++;
         
         $fieldsTable->setCellContents($row,0,"");
         $fieldsTable->setCellContents($row,1,"Total Systems");
         $fieldsTable->setCellContents($row,2,"Systems per Pack");

         $row++;

         $fieldsTable->setCellContents($row,0,$label->qtyField->name);
         $fieldsTable->setCellContents($row,1,genTextBox("totalsys","1"));
         $fieldsTable->setCellContents($row,2,genTextBox("sysperpack",$label->qtyField->presetVals[0]));

         $fieldsTable->setCellContents($row,3,genButton("Generate","auto"));

         $row++;
         
         $fieldsTable->setCellContents($row,1,genTextArea("qtyinput","10","34"));
         $fieldsTable->setCellContents($row,2,genButton("Generate","manual"));

         $row++;
      }

      //Next we need to generate all hidden fields for the stamp information and allow it to be passed here via post
      if($label->stampID){
         foreach($stamp->staticFields as $field){
            if(isset($_POST[$field->csvname]))
               echo genHidden($field->csvname,$_POST[$field->csvname]);
            else
               echo genHidden($field->csvname,$field->presetVals[0]);
         }
         foreach($stamp->serialFields as $serialfield){
            if(isset($_POST[$serialfield->csvname])){
               echo genHidden($serialfield->csvname,$_POST[$serialfield->csvname]);
            }
         }
      }
      
      $attrs = array('width'=>'250');
      $fieldsTable->setColAttributes(0,$attrs);
      $attrs = array('width'=>'250');
      $fieldsTable->setColAttributes(1,$attrs);
      $attrs = array('width'=>'250');
      $fieldsTable->setColAttributes(2,$attrs);
      $attrs = array('valign'=>'bottom');
      $fieldsTable->setColAttributes(5,$attrs);

      echo $fieldsTable->toHTML();
      echo endForm();
      
      if(isset($_POST['noedit'])){
         echo "<script type=\"text/javascript\">\n";
         echo "document.getElementById(\"labelForm\").submit();\n";
         echo "</script>\n";
      }

   }
   //Display picture of the label description.

   include "footer.php";
?>
