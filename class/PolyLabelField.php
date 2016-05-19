<?php
   /*This object owns the database tables: polylabels_fields_names, polylabels_fields_values*/

   class PolyLabelField{
      protected $id;
      protected $name;
      protected $csvname;
      protected $type;
      protected $presetVals=array(); //an array of pre-set values
      protected $hidden;
      
      public function __construct(mysqli $labelsdb, $field_name_id=""){
         //set id
         if(!empty($field_name_id)){
            $this->id = $field_name_id;
 
            $this->setNameType($labelsdb);
            $this->setValsArray($labelsdb);
         }else{
            $this->addNewField($labelsdb);
         }
         
      }

      function __get($propName){
         $allowedVars=array("id","name","csvname","type","presetVals","hidden");
         if (in_array($propName,$allowedVars)){
            return $this->$propName;
         }else{
            echo "ERROR! No such property $propName or property cannot be accessed directly in PolyLabelField Object!";
         }
      }      
      
      public function getFieldValueID($labelsdb,$value){
         $idQuery="SELECT fields_value_id FROM polylabels_fields_values WHERE fields_name_id=$this->id AND fields_value='$value'";
         //echo $idQuery;
         if($idResults=$labelsdb->query($idQuery)){
            if(list($fieldValueID)=$idResults->fetch_row()){
               return $fieldValueID;
            }
         }
         
         return 0;
      }
      
      public function updateName(mysqli $labelsdb,$newName){
         $updateQuery="UPDATE polylabels_fields_names SET fields_name='$newName' WHERE fields_name_id=$this->id";
         
         if($labelsdb->query($updateQuery)) $this->name=$newName;
      }
      
      public function updateCSVName(mysqli $labelsdb,$newCSVName){
         $updateQuery="UPDATE polylabels_fields_names SET fields_csv_name='$newCSVName' WHERE fields_name_id=$this->id";
         
         if($labelsdb->query($updateQuery)) $this->csvname=$newCSVName;
         
      }
      
      public function updateLabelID(mysqli $labelsdb,$newLabelID){
         $updateQuery="UPDATE polylabels_fields_names SET fields_label_id=$newLabelID WHERE fields_name_id=$this->id";
         
         $labelsdb->query($updateQuery);
      }
      
      public function updateFieldType(mysqli $labelsdb,$fieldType){
         $isSerial=0;
         $isQuantity=0;
         
         if(strcmp($fieldType,"serial")==0) $isSerial=1;
         if(strcmp($fieldType,"quantity")==0) $isQuantity=1;
         
         $updateQuery="UPDATE polylabels_fields_names SET fields_is_serial=$isSerial, fields_is_quantity=$isQuantity WHERE fields_name_id=$this->id";
         
         if($labelsdb->query($updateQuery)) $this->type=$fieldType;
      }
      
      public function delField(mysqli $labelsdb){
         //first delete all field values associated with the field
         $getFieldValsQuery="SELECT fields_value_id FROM polylabels_fields_values WHERE fields_name_id=$this->id";
         $fieldValues=$labelsdb->query($getFieldValsQuery);
         while(list($fieldValueID)=$fieldValues->fetch_row()){
            $this->delFieldValue($labelsdb,$fieldValueID);
         }
         
         $delQuery="DELETE FROM polylabels_fields_names WHERE fields_name_id=$this->id";
         $labelsdb->query($delQuery);
         
      }
      
      public function addFieldValue(mysqli $labelsdb,$value=""){
         $addQuery="INSERT INTO polylabels_fields_values SET fields_name_id=$this->id,fields_value='$value'";

         if($labelsdb->query($addQuery)){
            $this->setValsArray($labelsdb);
            return 1;
         }else{
            return 0;
         } 
      }
      
      public function updateFieldValue(mysqli $labelsdb,$newValue){
         $modQuery="UPDATE polylabels_fields_values SET fields_value='$newValue' WHERE fields_name_id=$this->id";
         
         if($labelsdb->query($modQuery)){
            $this->setValsArray($labelsdb);
            return 1;
         }else{
            return 0;
         }
      }

      public function delFieldValue(mysqli $labelsdb,$fieldValueID){
         $delQuery="DELETE FROM polylabels_fields_values WHERE fields_value_id=$fieldValueID";
         echo $delQuery."<br>";
         
         if($labelsdb->query($delQuery)){
            $this->setValsArray($labelsdb);
            return 1;
         }else{
            return 0;
         }
      }

      protected function setNameType(mysqli $labelsdb){
         //query database for field name and csv name
         $fieldsQuery="SELECT fields_name, fields_csv_name, fields_is_serial, fields_is_quantity, fields_is_hidden FROM polylabels_fields_names WHERE fields_name_id=$this->id";
         $fieldResult=$labelsdb->query($fieldsQuery);
         list($this->name,$this->csvname,$isSerial,$isQty,$this->hidden)=$fieldResult->fetch_row();
         
         if($isSerial){
            $this->type="serial";
         }elseif($isQty){
            $this->type="quantity";
         }else{
            $this->type="static";
         }
         $fieldResult->free();
      }

      protected function setValsArray(mysqli $labelsdb){
         //clear Array
         $this->presetVals=array();
        
         //query database for preset values default(s)
         $fieldsQuery="SELECT fields_value,field_is_date FROM polylabels_fields_values WHERE fields_name_id=$this->id AND field_is_default=1";
         $fieldResult=$labelsdb->query($fieldsQuery);
         while(list($default,$isdate)=$fieldResult->fetch_row()){
            if($isdate){
               array_push($this->presetVals,date($default));
            }else{ 
               array_push($this->presetVals,$default);
            }
         }
         $fieldResult->free();

         //query database for non-default values
         $fieldsQuery="SELECT fields_value,field_is_date FROM polylabels_fields_values WHERE fields_name_id=$this->id AND field_is_default=0";
         $fieldResult=$labelsdb->query($fieldsQuery);
         while(list($value,$isdate)=$fieldResult->fetch_row()){
            if($isdate){
               array_push($this->presetVals,date($value));
            }else{
              array_push($this->presetVals,$value);
            }
         }
         $fieldResult->free();
      }
      
      private function addNewField(mysqli $labelsdb){
         $addQuery="INSERT INTO polylabels_fields_names () VALUES()";
         
         if($labelsdb->query($addQuery)) $this->id=$labelsdb->insert_id;
      }
   }
?>
