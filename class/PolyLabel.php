<?php
   require_once("PolyLabelField.php");
   require_once("PolyLabelBarcode.php");
   
   class PolyLabel{
      private $id;
      private $name;
      private $description;
      private $active;
      private $samplePic;
      private $sampleDescPic;
      private $glabelsFile;
      private $numcopies; //default number of copies
      private $staticFields=array(); //array of PolyLabelField Objects
      private $serialFields=array(); //array of Serial PolyLabelField Objects
      private $qtyField; //only one quantity field per label allowed  
      private $barcodes=array(); //array of Barcode Objects
 
      public function __construct(mysqli $labelsdb, $labelID=""){
         if(!empty($labelID)){
            //set name property
            $this->id=$labelID;
         
            //query database for various properties
            $labelQuery="SELECT labels_name, labels_picture_file, labels_picture_descriptive_file, 
                             labels_glabels_file, labels_description, labels_num_copies, labels_active FROM polylabels_labels
                             WHERE labels_id=$this->id";

            //echo $labelQuery;

            //set properties from database results
            $labelResults=$labelsdb->query($labelQuery);
            list($this->name,$this->samplePic,$this->sampleDescPic,$this->glabelsFile, $this->description, $this->numcopies, $this->active)=$labelResults->fetch_row();

            //results no longer needed
            $labelResults->free();
         
            $this->setFields($labelsdb);
            $this->setBarcodes($labelsdb);
         }else{
            $this->addNewLabel($labelsdb);
         }
      }

      function __get($propName){
         $allowedVars = array("name","description","samplePic","sampleDescPic","glabelsFile","numcopies",
                              "staticFields","serialFields","qtyField","barcodes","active");
         if (in_array($propName,$allowedVars)){
            return $this->$propName;
         }else{
            echo "ERROR! No such property or property cannot be accessed directly in PolyLabel Object!";
         }
      }

      /*return all fields in a single array*/
      public function getAllFields(){
         $fieldsArray = array();
         
         if(count($this->staticFields)) $fieldsArray = array_merge($fieldsArray,$this->staticFields);
         if(count($this->serialFields)) $fieldsArray = array_merge($fieldsArray,$this->serialFields);
         if($this->qtyField) array_push($fieldsArray,$this->qtyField);
         
         return $fieldsArray;
      }
      
      /*function to change the name of the label*/
      public function updateName(mysqli $labelsdb,$newName){
         $updateQuery="UPDATE polylabels_labels SET labels_name='$newName' WHERE labels_id=$this->id";
         if($labelsdb->query($updateQuery)) $this->name=$newName;
      }
      
      /*function to change the description of the label*/
      public function updateDesc(mysqli $labelsdb,$newDesc){
         $updateQuery="UPDATE polylabels_labels SET labels_description='$newDesc' WHERE labels_id=$this->id";
         if($labelsdb->query($updateQuery)) $this->description=$newDesc;
      }
      
      /*function to change the default Number of Copies*/
      public function updateNumCopies(mysqli $labelsdb,$newNumCopies){
         $updateQuery="UPDATE polylabels_labels SET labels_num_copies=$newNumCopies WHERE labels_id=$this->id";
         if($labelsdb->query($updateQuery)) $this->numcopies=$newNumCopies;
      }

      /*function to change the company that the label belongs to*/
      public function updateCompany(mysqli $labelsdb,$newCompany){
         //To be implemented later
      }
      
      /*function to change the active state of the label.  If active, set inactive and vice versa*/
      public function changeActiveState(mysqli $labelsdb){
         if($this->active){
            $newState=0;
         }else{
            $newState=1;
         }

         $updateQuery="UPDATE polylabels_labels SET labels_active=$newState WHERE labels_id=$this->id";

         if($labelsdb->query($updateQuery)){ $this->active=$newState; }
      }
      
      public function addField(mysqli $labelsdb,$fieldName,$fieldCSVName,$fieldType){

         if(strcmp($fieldType,"serial")==0) $isSerial=1;
         if(strcmp($fieldType,"quantity")==0) $isQuantity=1;
         
         $newField = new PolyLabelField($labelsdb);
         $newField->updateName($labelsdb,$fieldName);
         $newField->updateCSVName($labelsdb,$fieldCSVName);
         $newField->updateFieldType($labelsdb,$fieldType);
         $newField->updateLabelID($labelsdb,$this->id);
         
         if($isSerial || $isQuantity){ 
            $newField->addFieldValue($labelsdb,$newField->id);
            $this->setFields($labelsdb);
         }
      }      

      public function delField(mysqli $labelsdb,PolyLabelField $field){
         foreach($this->barcodes as $barcode){
            $barcode->delUsedField($labelsdb,$field);
         }
         $field->delField($labelsdb);
         $this->setFields($labelsdb);
      }
      
      public function addBarcode(mysqli $labelsdb,$name,$prefix,$suffix){
         $newBarcode = new PolyLabelBarcode($labelsdb);
         $newBarcode->updateName($labelsdb,$name);
         $newBarcode->updatePrefix($labelsdb,$prefix);
         $newBarcode->updateSuffix($labelsdb,$suffix);
         $newBarcode->updateLabelID($labelsdb,$this->id);
         
         $this->setBarcodes($labelsdb);
         return $newBarcode->id;
      }
      
      public function setGlabelsFile(mysqli $labelsdb,$file){
         $query="UPDATE polylabels_labels SET labels_glabels_file='$file' WHERE labels_id=$this->id";
         if($labelsdb->query($query)){
            $this->glabelsFile=$file;
         }
      }
      
      public function setSamplePic(mysqli $labelsdb,$samplePicture){
         $query="UPDATE polylabels_labels SET labels_picture_file='$samplePicture' WHERE labels_id=$this->id";
         if($labelsdb->query($query)){
            $this->samplePic=$samplePicture;
         }
      }
      
      public function setSampleDescPic(mysqli $labelsdb,$sampleDescPicture){
         $query="UPDATE polylabels_labels SET labels_picture_descriptive_file='$sampleDescPicture' WHERE labels_id=$this->id";
         if($labelsdb->query($query)){
            $this->sampleDescPic=$sampleDescPicture;
         }
      }
      
      private function setFields(mysqli $labelsdb){
         //BAD IMPLEMENTATION?
         $fieldsQuery="SELECT fields_name_id, fields_is_serial, fields_is_quantity FROM polylabels_fields_names WHERE fields_label_id=$this->id";
         //echo $fieldsQuery;

         $fieldResults=$labelsdb->query($fieldsQuery);
         $this->serialFields=array();
         $this->staticFields=array();         

         while(list($fieldID,$fields_is_serial,$fields_is_quantity)=$fieldResults->fetch_row()){
            $fieldObj = new PolyLabelField($labelsdb,$fieldID);
            if ($fields_is_serial){
               array_push($this->serialFields,$fieldObj);
            }elseif($fields_is_quantity){
               $this->qtyField=$fieldObj;
            }else{
               array_push($this->staticFields,$fieldObj);
            }
         }

         $fieldResults->free();
      }

      private function setBarcodes(mysqli $labelsdb){
         //BAD IMPLEMENTATION?
         $barcodeQuery="SELECT barcode_id FROM polylabels_barcodes WHERE barcode_labels_id=$this->id";
         $barcodeResults=$labelsdb->query($barcodeQuery);

         while(list($barcodeID)=$barcodeResults->fetch_row()){
            $barcodeObj = new PolyLabelBarcode($labelsdb,$barcodeID);
            array_push($this->barcodes,$barcodeObj);
         }
         $barcodeResults->free();
      }
      
      private function addNewLabel(mysqli $labelsdb){
         $addQuery="INSERT INTO polylabels_labels";
         if($labelsdb->query($addQuery)) $this->id = $labelsdb->insert_id;
         
      }

   }

?>
