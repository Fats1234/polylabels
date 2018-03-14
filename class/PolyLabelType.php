<?php

   class PolyLabelType{
      protected $id;
      protected $name;
      protected $labelsPerPage;
      
      public function __construct(mysqli $labelsdb, $labelTypeID=""){
         if(!empty($labelTypeID)){
            $this->id = $labelTypeID;
           
            //query database for various properties
            $query = "SELECT label_type_name, labels_per_page 
                        FROM polylabels_label_types WHERE label_type_id=$labelTypeID";
                        
            $result=$labelsdb->query($query);
            
            list($this->name,$this->labelsPerPage) = $result->fetch_row();
            
            $result->free();
        }else{
           $this->id=0;
           $this->name="Label Type Not Set";
           $this->labelsPerPage=0;
        }
      }
      
      function __get($propName){
         $allowedVars=array("id","name","labelsPerPage");
         if (in_array($propName,$allowedVars)){
            return $this->$propName;
         }else{
            echo "ERROR! No such property $propName or property cannot be accessed directly in PolyLabelType Object!";
         }
      }
      
      public function isStamp(){
         if ($this->id == 1)
            return TRUE;
         else
            return FALSE;
      }
      
      public function isForm(){
         if ($this->id == 2)
            return TRUE;
         else
            return FALSE;
      }
      
      public function getAllLabelTypes(mysqli $labelsdb){
         $query = "SELECT label_type_id, label_type_name FROM polylabels_label_types ORDER BY label_type_id";
         $results=$labelsdb->query($query);
         $labelTypesArray[0]="";
         while($labelType=$results->fetch_assoc()){
            $labelTypesArray[$labelType['label_type_id']]=$labelType['label_type_name'];
         }
         return $labelTypesArray;
      }
      
      public function getAllStampNames(mysqli $labelsdb){
         $query = "SELECT label_type_id FROM polylabels_label_types WHERE label_type_name='stamp'";
         $result=$labelsdb->query($query);
         list($stampTypeID)=$result->fetch_row();
         
         $query = "SELECT labels_id,labels_name FROM polylabels_labels WHERE label_type_id=$stampTypeID ORDER BY labels_id";
         $result=$labelsdb->query($query);
         $labelResults=$labelsdb->query($query);
         $labelsArray[0]="";
         while($label=$labelResults->fetch_assoc()){
            $labelsArray[$label['labels_id']]=$label['labels_name'];
         }
         return $labelsArray;
      }

   }

?>