<?php
   function startForm($action,$method,$upload=FALSE){
      if($upload){
         return "<form action=\"$action\" method=\"$method\" enctype=\"multipart/form-data\">\n";
      }else{
         return "<form action=\"$action\" method=\"$method\">\n";
      }
   }
   
   function endForm(){
      return "</form>\n";
   }

   function genTextBox($fieldname,$default=""){
      $textbox="<input type=\"text\" size=\"30\" name=\"$fieldname\" id=\"$fieldname\" value=\"$default\">\n";
      return $textbox;
   }

   function genTextArea($fieldname,$rows="10",$cols="50",$default_text=""){
      $textarea="<textarea rows=\"$rows\" cols=\"$cols\" name=\"$fieldname\">$default_text</textarea>";
      return $textarea;
   }

   function genDropBox($fieldname,$options,$default=""){
      if(empty($options)){
         return;
      }
      
      $dropbox = "<select name=\"$fieldname\" id=\"$fieldname\" style=\"width: 250px\">\n";
      foreach($options as $option){
         if(!strcmp($option,$default)){
            $dropbox .= "<option value=\"$option\" selected>$option</option>\n";
         }else{
            $dropbox .= "<option value=\"$option\">$option</option>\n";
         }
      }
      $dropbox .= "</select>\n";
      return $dropbox;
   }
   
   function genButton($value,$name="submit"){
      $button="<input type=\"submit\" name=\"$name\" value=\"$value\">\n";
      return $button;
   }
   
   function genHidden($fieldname,$value){
      return "<input type=\"hidden\" name=\"$fieldname\" value=\"$value\">\n";
   }
   
   function genUpload($fieldname,$value=""){
      return "<input type=\"file\" name=\"$fieldname\" id=\"$fieldname\" value=\"$value\">";
   }
?>
