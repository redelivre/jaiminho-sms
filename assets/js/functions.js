// محاسبه تعداد کاراکتر های باقیمانده پیامک
var smsCount = 1;
function smsLeftChar(txtSms, lblLeft, lblSms, lblMax, txtSign) {

    var smsBody = $('#' + txtSms).val(); //+ $('#' + txtSign).val();

    var maxLen = 0;
    var msgLen = smsBody.length;
    var currentLen = msgLen;

    var charLeft = 0;

    if (isUnicode(smsBody)) {
        maxLen = 70;
    }
    else {
        maxLen = 160;
    }

    if (currentLen > maxLen) {

        while (msgLen > maxLen) {
            msgLen -= maxLen;
        }

        if ((msgLen % maxLen) != 0) {
            smsCount = parseInt(Math.floor(currentLen / maxLen)) + 1;

        }
        else {
            smsCount = parseInt(currentLen / maxLen);
        }

    }
    else {
        smsCount = 1;
    }

    $('#' + lblLeft).html(maxLen - msgLen);
    $('#' + lblSms).html(smsCount);
    $('#' + lblMax).html(maxLen);

}

function checkSMSLength(textarea, counterSpan, partSpan, maxSpan, def) {


    var text = document.getElementById(textarea).value;
    var ucs2 = text.search(/[^\x00-\x7E]/) != -1;
    var unitLength = 160;
    var msgLen = text.length;
    var count = 1;

    if (!ucs2) {
      text = text.replace(/([[\]{}~^|\\])/g, "\\$1");
    }
    else {
      unitLength = 70;

      if (msgLen > unitLength) {
        document.getElementById(textarea).value =
          text.substring(0, text.length-1);
        unitLength = 160;
      }
    }

    document.getElementById(maxSpan).innerHTML = unitLength;
    document.getElementById(counterSpan).innerHTML = (unitLength * count - msgLen);
    document.getElementById(partSpan).innerHTML = count;

    document.getElementById(textarea).maxLength = unitLength;
}

// تشخیص یونیکد بودن متن
function isUnicode(str) {
    var letters = [];
    for (var i = 1; i <= str.length; i++) {
        letters[i] = str.substring((i - 1), i);
        if (letters[i].charCodeAt() > 255) { return true; }
    }
    return false;
}
