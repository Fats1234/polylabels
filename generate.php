<?php
   include "header.php";
?>

<?php
   require_once "config.php";
   require_once "class/PolyLabel.php";

   if (isset($_POST['labelID'])){
      $labelsdb = new mysqli($host,$dbuser,$dbpass,$database);
     
      $label = new PolyLabel($labelsdb,$_POST['labelID']);     
      
      //set header
      $fieldVals=array();
      foreach($label->staticFields as $staticField){
         $csvHeader.=$staticField->csvname.",";
         $staticRowStr.=str_replace(",","\,",$_POST[$staticField->csvname]).",";
         $fieldVals[$staticField->csvname]=$_POST[$staticField->csvname];
      }
     
      $fullRowStrs=array();
      //for now make quantity and serial fields mutually exclusive (ie a label cannot have both serial and quantity)
      if($label->qtyField){
         $csvHeader.=$label->qtyField->csvname.",";
         if(isset($_POST['auto'])){
            $quotient=floor($_POST['totalsys']/$_POST['sysperpack']);
            $remainder=$_POST['totalsys']%$_POST['sysperpack'];
            for($i=0;$i<$quotient;$i++){
               $fullRowStrs[$i]=$_POST['sysperpack'].",";
            }
            if($remainder > 0){
               array_push($fullRowStrs,$remainder.",");
            }
         }elseif(isset($_POST['manual'])){
            $qtyList=explode("\n",str_replace("\r","",trim($_POST['qtyinput'])));
            for($i=0;$i<count($qtyList);$i++){
               $fullRowStrs[$i]=$qtyList[$i].",";
            }
         } 
      }else{
         foreach($label->serialFields as $serialField){
            $serialNums=array();
            $csvHeader.=$serialField->csvname.",";
            if(isset($_POST['range'])){
               for($i=0;$i<$_POST['numsystems'];$i++){
                  if(strcmp(substr($_POST[$serialField->csvname."box"],0,1),"0")==0){
                     $serialNums[$i]=str_pad($_POST[$serialField->csvname."box"]+$i,strlen($_POST[$serialField->csvname."box"]),"0",STR_PAD_LEFT);
                  }else{
                     $serialNums[$i]=$_POST[$serialField->csvname."box"]++;
                  }
                  $fullRowStrs[$i].=$serialNums[$i].",";
               }
               //update the last value of serialNums plus 1 to database as the new value
               if(strcmp(substr($_POST[$serialField->csvname."box"],0,1),"0")==0){         
                  $serialField->updateFieldValue($labelsdb,str_pad(end($serialNums)+1,strlen($_POST[$serialField->csvname."box"]),"0",STR_PAD_LEFT));
               }else{
                  $serialField->updateFieldValue($labelsdb,$_POST[$serialField->csvname."box"]);
               }
            }elseif(isset($_POST['input'])){
               $serialNums=explode("\n",str_replace("\r","",trim($_POST[$serialField->csvname."area"])));
               for($i=0;$i<count($serialNums);$i++){
                  $fullRowStrs[$i].=str_replace(",","\,",$serialNums[$i]).",";
               }
            }
            $fieldVals[$serialField->csvname]=$serialNums; //serialNums is an array
         }
      }

      if(count($fullRowStrs)){
         foreach($fullRowStrs as $index => $serialRowStr){
            $fullRowStrs[$index]=$staticRowStr.$serialRowStr;
         }
      }else{
         array_push($fullRowStr,$staticRowStr);
      }

      foreach($label->barcodes as $barcode){
         $csvHeader.=$barcode->name.",";
         foreach($fullRowStrs as $index => $rowString){
            $barcodeStr=$barcode->prefix;
            foreach($barcode->fieldsUsed as $barcodeField){
               if(is_array($fieldVals[$barcodeField->csvname])){
                  $barcodeStr.=$fieldVals[$barcodeField->csvname][$index];
               }else{
                 $barcodeStr.=$fieldVals[$barcodeField->csvname];
               }
            }
            $barcodeStr.=$barcode->suffix;
            $fullRowStrs[$index]=$rowString.$barcodeStr.",";
         }
      }
     
      //start writing csv file;
      $fh=fopen("tmp/label.csv","wt");
 
      fwrite($fh,$csvHeader."\n");
      
      foreach($fullRowStrs as $rowString){
         for($i=0;$i<$_POST['numcopies'];$i++){
            fwrite($fh,$rowString."\n");
         }
      }

      fclose($fh);
      $labelsdb->close();

      exec("mv tmp/label.csv archive/label.csv");
      $timestamp=date("Y-m-d-His");
      $startPosition=$_POST['startposition'];
      exec("cp archive/label.csv archive/label-$timestamp.csv");
      exec("glabels-3-batch -o output/$label->name/$label->name-$timestamp.pdf -f $startPosition -i archive/label.csv glabels/$label->glabelsFile");

      echo "<font size=\"6\">The label file is ready.  Right click the link below and save as or click to open:</font><br />\n";
      echo "<a href=\"output/$label->name/$label->name-$timestamp.pdf\"><font size=\"6\">$label->name-$timestamp.pdf</font></a><br /><br />\n";
      echo "<font size=\"6\">Alternatively, the file is available at:</font><br />\n";
      echo "<font size=\"6\">\\\\production03\\share001\\labels_pdf\\$label->name\\$label->name-$timestamp.pdf</font><br />\n";
      echo "<font size=\"6\">R:\\labels_pdf\\$label->name\\$label->name-$timestamp.pdf</font><br />\n";

   }
?>

<img src=images/adobe-options.png>

<?php
   include "footer.php";
?>
