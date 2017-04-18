function checkTime(field)
  {
    var errorMsg = "";

    // regular expression to match required time format
    re = /^(\d{1,2}):(\d{2})(:00)?([ap]m)?$/;

    if(field != '') {
      if(regs = field.match(re)) {
        if(regs[4]) {
          // 12-hour time format with am/pm
          if(regs[1] < 1 || regs[1] > 12) {
            errorMsg = "Invalid value for hours: " + regs[1];
          }
        } else {
          // 24-hour time format
          if(regs[1] > 23) {
            errorMsg = "Invalid value for hours: " + regs[1];
          }
        }
        if(!errorMsg && regs[2] > 59) {
          errorMsg = "Invalid value for minutes: " + regs[2];
        }
      } else {
        errorMsg = "Invalid time format: " + field.value;
      }
    }

    if(errorMsg != "") {
      //alert(errorMsg);
      return false;
    }

    return true;
  }