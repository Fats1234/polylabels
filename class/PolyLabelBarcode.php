<?php
   require_once("PolyBarcodeUsedField.php");

   class PolyLabelBarcode{
      private $id;
      private $name;
      private $prefix;
      private $suffix;
      private $fieldsUsed=array(); //an array of PolyBarcodeUsedField objects in the order that they are used

      public function __construct(mysqli $labelsdb, $barcode_id=""){
         if(!empty($barcode_id)){
            $this->id=$barcode_id;
         
            $this->setNamePrefixSuffix($labelsdb);
            $this->setFieldsUsed($labelsdb);
         }else{
            $this->addNewBarcode($labelsdb);
         }
      }
      
      function __get($propName){
         $allowedVars=array("id","name","prefix","suffix","fieldsUsed");
         if (in_array($propName,$allowedVars)){
            return $this->$propName;
         }else{
            echo "ERROR! No such property $propName or property cannot be accessed directly in PolyLabelBarcode Object!";
         }
      }
      
      public function updateName($labelsdb,$newName){
         $nameQuery="UPDATE polylabels_barcodes SET barcode_name='$newName' WHERE barcode_id=$this->id";
         
         if($labelsdb->query($nameQuery)) $this->setNamePrefixSuffix($labelsdb);
      }

      public function updatePrefix($labelsdb,$prefix){
         $prefixQuery="UPDATE polylabels_barcodes SET barcode_prefix='$prefix' WHERE barcode_id=$this->id";
         
         if($labelsdb->query($prefixQuery)) $this->setNamePrefixSuffix($labelsdb);
      }
      
      public function updateSuffix($labelsdb,$suffix){
         $suffixQuery="UPDATE polylabels_barcodes SET barcode_suffix='$suffix' WHERE barcode_id=$this->id";
         
         if($labelsdb->query($suffixQuery)) $this->setNamePrefixSuffix($labelsdb);         
      }
      
      public function updateLabelID($labelsdb,$newLabelID){
         $labelIDQuery="UPDATE polylabels_barcodes SET barcode_labels_id='$newLabelID' WHERE barcode_id=$this->id";
         
         $labelsdb->query($labelIDQuery);
      }
      
      public function getLabelID($labelsdb){
         $idQuery="SELECT barcode_labels_id FROM polylabels_barcodes WHERE barcode_id=$this->id";
         //echo $idQuery;
         
         $idResult=$labelsdb->query($idQuery);
         
         list($labelID)=$idResult->fetch_row();
         //echo $labelID;
         return $labelID;
      }
      
      public function addUsedField($labelsdb,$fieldID,$order="0"){
         $usedField = new PolyBarcodeUsedField($labelsdb);
         $usedField->updateBarcodeID($labelsdb,$this->id);
         $usedField->updateFieldID($labelsdb,$fieldID);
         $usedField->updateOrder($labelsdb,$order);
         
         $this->updateFieldsOrder($labelsdb);
      }
      
      public function delUsedField($labelsdb,$usedField){         
         if($usedField instanceof PolyBarcodeUsedField){
            $usedField->delUsedField($labelsdb); 
         }elseif($usedField instanceof PolyLabelField){
            foreach($fieldsUsed as $barcodeUsedField){
               if($barcodeUsedField->id == $usedField->id){
                  $barcodeUsedField->delUsedField($labelsdb);
               }
            }
         }
         $this->updateFieldsOrder($labelsdb);
      }
      
      public function updateFieldsOrder(mysqli $labelsdb){
         $this->setFieldsUsed($labelsdb);
         foreach($this->fieldsUsed as $index => $field){
            $field->updateOrder($labelsdb,$index+1);
         }
         $this->setFieldsUsed($labelsdb);
      }
      
      private function setNamePrefixSuffix(mysqli $labelsdb){
         $barcodeQuery="SELECT barcode_name, barcode_prefix, barcode_suffix FROM polylabels_barcodes WHERE barcode_id=$this->id";
         //echo $barcodeQuery;         
 
         $barcodeResult=$labelsdb->query($barcodeQuery);
         list($this->name,$this->prefix,$this->suffix)=$barcodeResult->fetch_row();
         $barcodeResult->free();
      }

      private function setFieldsUsed(mysqli $labelsdb){
         $barcodeQuery="SELECT barcodes_used_fields_id FROM polylabels_barcodes_used_fields WHERE barcodes_id=$this->id ORDER BY barcodes_fields_order";
         //echo $barcodeQuery;
         
         $barcodeResult=$labelsdb->query($barcodeQuery);
         $this->fieldsUsed=array();
         while(list($usedFieldID)=$barcodeResult->fetch_row()){
            $usedField = new PolyBarcodeUsedField($labelsdb,$usedFieldID);
            array_push($this->fieldsUsed,$usedField);           
         }
         $barcodeResult->free();
      }
      
      private function addNewBarcode(mysqli $labelsdb){
         $addQuery="INSERT INTO polylabels_barcodes () VALUES()";
         if($labelsdb->query($addQuery)) $this->id=$labelsdb->insert_id;
      }

   }
?>
