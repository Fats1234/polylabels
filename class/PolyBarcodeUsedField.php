<?php
   require_once("PolyLabelField.php");

   class PolyBarcodeUsedField extends PolyLabelField{
      private $barcodeUsedFieldID;
      private $order;

      public function __construct(mysqli $labelsdb,$usedFieldID=""){
         if(!empty($usedFieldID)){
            $this->barcodeUsedFieldID=$usedFieldID;            
            $field_name_id=$this->getFieldID($labelsdb);
            parent::__construct($labelsdb, $field_name_id);
         }else{
            $this->addUsedField($labelsdb);
         }
         $this->setOrder($labelsdb);
      }
      
      function __get($propName){
         $allowedVars=array("id","name","csvname","type","order","barcodeUsedFieldID");
         if (in_array($propName,$allowedVars)){
            return $this->$propName;
         }else{
            echo "ERROR! No such property $propName or property cannot be accessed directly in PolyBarcodeUsedField Object!";
         }
      }

      public function updateOrder(mysqli $labelsdb,$newOrder){
         $orderQuery="UPDATE polylabels_barcodes_used_fields SET barcodes_fields_order=$newOrder WHERE barcodes_used_fields_id=$this->barcodeUsedFieldID";
         //echo $orderQuery;

         if($labelsdb->query($orderQuery)){
            $this->order=$newOrder;
            return 1;
         }
         return 0;
      }
      
      public function updateBarcodeID($labelsdb,$barcodeID){
         $updateQuery="UPDATE polylabels_barcodes_used_fields SET barcodes_id=$barcodeID WHERE barcodes_used_fields_id=$this->barcodeUsedFieldID";
         //echo $updateQuery;
         
         if($labelsdb->query($updateQuery)) return 1;return 0;
      }
      
      public function updateFieldID($labelsdb,$fieldID){
         $updateQuery="UPDATE polylabels_barcodes_used_fields SET barcodes_fields_names_id=$fieldID WHERE barcodes_used_fields_id=$this->barcodeUsedFieldID";
         //echo $updateQuery;
         
         if($labelsdb->query($updateQuery)){
            parent::__construct($labelsdb,$fieldID);
            return 1;
         }
         return 0;
      }
      
      public function delUsedField(mysqli $labelsdb){
         $delQuery="DELETE FROM polylabels_barcodes_used_fields WHERE barcodes_used_fields_id=$this->barcodeUsedFieldID";
         //echo $remQuery;
         
         if($labelsdb->query($delQuery)) return 1;return 0;
      }
      
      private function addUsedField(mysqli $labelsdb){
         $addQuery = "INSERT INTO polylabels_barcodes_used_fields SET barcodes_id=0,barcodes_fields_names_id=0";
         //echo $addQuery;
         
         if($labelsdb->query($addQuery)){
            $this->barcodeUsedFieldID = $labelsdb->insert_id;
            //echo $this->barcodeUsedFieldID;
         }
      }
      
      private function getFieldID($labelsdb){
         $idQuery="SELECT barcodes_fields_names_id FROM polylabels_barcodes_used_fields WHERE barcodes_used_fields_id=$this->barcodeUsedFieldID";
         //echo $idQuery;
         $idResults=$labelsdb->query($idQuery);
         list($field_name_id)=$idResults->fetch_row();

         return $field_name_id;
      }

      private function setOrder(mysqli $labelsdb){
         $orderQuery="SELECT barcodes_fields_order FROM polylabels_barcodes_used_fields WHERE barcodes_used_fields_id=$this->barcodeUsedFieldID";
         $orderResult=$labelsdb->query($orderQuery);
         list($this->order)=$orderResult->fetch_row();
      }
   }
?>
