function validateTime(time){
           // regular expression to match required time format
          re = /^\d{1,2}:\d{2}([ap]m)?$/;
          if($(time).val() == null || $.trim($(time).val()) == '') {
            return false;
          }else{
            if(time == "#timepicker2" && $.trim($(time).val()).toLowerCase() == "not sure"){//if time is not sure for timepicker 2 than accept it
              return true;
            }else{
              if($(time).val().match(re)){
                return true;
              }else{
                return false;
              }
            }
          }
        }

 function isNull(id){
          if($(id).val() == null || $(id).val().trim() == ""){
            return true;
          }else{
            return false;
          }
        }
